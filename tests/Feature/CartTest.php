<?php

namespace Tests\Feature;

use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    public function test_guest_cannot_add_to_cart(): void
    {
        $product = $this->makeProduct();

        $this->post(route('cart.store', $product))->assertRedirect(route('login'));
    }

    public function test_customer_adds_product_to_cart(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        $this->actingAs($customer)
            ->post(route('cart.store', $product), ['quantity' => 2])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $customer->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_adding_the_same_product_twice_sums_quantities(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 10]);

        $this->actingAs($customer)->post(route('cart.store', $product), ['quantity' => 1]);
        $this->actingAs($customer)->post(route('cart.store', $product), ['quantity' => 2]);

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 3]);

        $this->actingAs($customer)
            ->post(route('cart.store', $product), ['quantity' => 5])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_add_inactive_or_sold_out_products(): void
    {
        $customer = $this->makeCustomer();
        $inactive = $this->makeProduct(['status' => 'inactive']);
        $soldOut = $this->makeProduct(['stock' => 0]);

        $this->actingAs($customer)->post(route('cart.store', $inactive))->assertSessionHas('error');
        $this->actingAs($customer)->post(route('cart.store', $soldOut))->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_updates_quantity(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 10]);
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->patch(route('cart.update', $item), ['quantity' => 4])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);
    }

    public function test_cannot_update_quantity_above_stock(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 2]);
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->patch(route('cart.update', $item), ['quantity' => 9])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_customer_removes_item(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)->delete(route('cart.destroy', $item));

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_customer_cannot_touch_another_customers_cart(): void
    {
        $owner = $this->makeCustomer();
        $intruder = $this->makeCustomer();
        $product = $this->makeProduct();
        $item = CartItem::create(['user_id' => $owner->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($intruder)->delete(route('cart.destroy', $item))->assertForbidden();
        $this->actingAs($intruder)->patch(route('cart.update', $item), ['quantity' => 2])->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }
}
