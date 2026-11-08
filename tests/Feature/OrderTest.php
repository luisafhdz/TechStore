<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    private function shipping(array $override = []): array
    {
        return array_merge([
            'shipping_name'    => 'Cliente Prueba',
            'shipping_phone'   => '0000000000',
            'shipping_address' => 'Calle Falsa 123, Chihuahua',
            'payment_method'   => 'card',
        ], $override);
    }

    // ------------------------------------------------------------ checkout

    public function test_checkout_creates_order_discounts_stock_and_empties_cart(): void
    {
        $customer = $this->makeCustomer();
        $p1 = $this->makeProduct(['price' => 100, 'stock' => 10]);
        $p2 = $this->makeProduct(['price' => 49.50, 'stock' => 5]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $p1->id, 'quantity' => 2]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $p2->id, 'quantity' => 1]);

        $response = $this->actingAs($customer)->post(route('orders.store'), $this->shipping());

        $order = Order::first();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame('pending', $order->status);
        $this->assertEquals(249.50, (float) $order->total);
        $this->assertCount(2, $order->items);
        $this->assertSame(8, $p1->fresh()->stock);
        $this->assertSame(4, $p2->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_is_rejected_when_stock_is_not_enough(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 5]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 3]);

        // Otra persona compra antes y el inventario baja
        $product->update(['stock' => 1]);

        $this->actingAs($customer)
            ->post(route('orders.store'), $this->shipping())
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_checkout_requires_shipping_data(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($customer)
            ->post(route('orders.store'), [])
            ->assertSessionHasErrors(['shipping_name', 'shipping_address', 'payment_method']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_with_empty_cart_redirects_to_cart(): void
    {
        $this->actingAs($this->makeCustomer())
            ->get(route('orders.checkout'))
            ->assertRedirect(route('cart.index'));
    }

    // ------------------------------------------------------------ cliente

    public function test_customer_sees_own_orders(): void
    {
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = $this->makeCustomer();
        $order = $this->makeOrder($owner);

        $this->actingAs($this->makeCustomer())
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_customer_cancels_pending_order_and_stock_returns(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 7]);       // ya descontado al comprar 3
        $order = $this->makeOrder($customer, 'pending', [[$product, 3]]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $order))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_customer_cannot_cancel_an_order_in_process(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct(['stock' => 7]);
        $order = $this->makeOrder($customer, 'processing', [[$product, 3]]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $order))
            ->assertSessionHas('error');

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(7, $product->fresh()->stock);
    }

    // ------------------------------------------------------------ administrador

    public function test_admin_moves_order_through_the_full_flow(): void
    {
        $admin = $this->makeAdmin();
        $order = $this->makeOrder($this->makeCustomer());

        foreach (['processing', 'shipped', 'delivered'] as $status) {
            $this->actingAs($admin)
                ->patch(route('admin.orders.status', $order), ['status' => $status])
                ->assertSessionHas('success');
        }

        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_admin_cannot_skip_states(): void
    {
        $order = $this->makeOrder($this->makeCustomer());

        $this->actingAs($this->makeAdmin())
            ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_final_states_cannot_change(): void
    {
        $admin = $this->makeAdmin();
        $delivered = $this->makeOrder($this->makeCustomer(), 'delivered');
        $cancelled = $this->makeOrder($this->makeCustomer(), 'cancelled');

        $this->actingAs($admin)->patch(route('admin.orders.status', $delivered), ['status' => 'processing'])
            ->assertSessionHas('error');
        $this->actingAs($admin)->patch(route('admin.orders.status', $cancelled), ['status' => 'pending'])
            ->assertSessionHas('error');

        $this->assertSame('delivered', $delivered->fresh()->status);
        $this->assertSame('cancelled', $cancelled->fresh()->status);
    }

    public function test_admin_cancellation_restores_stock(): void
    {
        $product = $this->makeProduct(['stock' => 3]);
        $order = $this->makeOrder($this->makeCustomer(), 'processing', [[$product, 2]]);

        $this->actingAs($this->makeAdmin())
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cancelling_twice_does_not_restore_stock_twice(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['stock' => 3]);
        $order = $this->makeOrder($this->makeCustomer(), 'pending', [[$product, 2]]);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customer_cannot_use_admin_order_routes(): void
    {
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($customer)
            ->patch(route('admin.orders.status', $order), ['status' => 'processing'])
            ->assertForbidden();
    }

    public function test_admin_filters_orders_by_status(): void
    {
        $customer = $this->makeCustomer();
        $pending = $this->makeOrder($customer, 'pending');
        $shipped = $this->makeOrder($customer, 'shipped');

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.orders.index', ['status' => 'shipped']))
            ->assertOk()
            ->assertSee($shipped->number)
            ->assertDontSee($pending->number);
    }

    public function test_admin_sees_customer_info_in_order_detail(): void
    {
        $customer = $this->makeCustomer(['name' => 'Laura Cliente', 'email' => 'laura@example.test']);
        $order = $this->makeOrder($customer);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Laura Cliente')
            ->assertSee('laura@example.test');
    }
}
