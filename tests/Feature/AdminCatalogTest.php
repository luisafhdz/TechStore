<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    public function test_admin_creates_a_product(): void
    {
        $category = $this->makeCategory('Audio');

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name'        => 'Bocina Nueva',
                'brand'       => 'JBL',
                'description' => 'Bocina de prueba',
                'price'       => '99.90',
                'stock'       => 5,
                'status'      => 'active',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Bocina Nueva', 'stock' => 5]);
    }

    public function test_product_form_validates_required_fields(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.products.store'), [])
            ->assertSessionHasErrors(['category_id', 'name', 'brand', 'description', 'price', 'stock', 'status']);
    }

    public function test_product_rejects_negative_price_and_stock(): void
    {
        $category = $this->makeCategory('Audio');

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name'        => 'Invalido',
                'brand'       => 'X',
                'description' => 'X',
                'price'       => -5,
                'stock'       => -1,
                'status'      => 'active',
            ])
            ->assertSessionHasErrors(['price', 'stock']);
    }

    public function test_admin_updates_stock(): void
    {
        $product = $this->makeProduct(['stock' => 1]);

        $this->actingAs($this->makeAdmin())
            ->patch(route('admin.products.stock', $product), ['stock' => 42])
            ->assertSessionHas('success');

        $this->assertSame(42, $product->fresh()->stock);
    }

    public function test_admin_deletes_a_product_without_losing_history(): void
    {
        $product = $this->makeProduct();

        $this->actingAs($this->makeAdmin())->delete(route('admin.products.destroy', $product));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_creates_a_subcategory(): void
    {
        $parent = $this->makeCategory('Computacion');

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.categories.store'), ['name' => 'Teclados', 'parent_id' => $parent->id])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Teclados', 'parent_id' => $parent->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = $this->makeCategory('Audio');
        $this->makeProduct(['category_id' => $category->id]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_with_subcategories_cannot_be_deleted(): void
    {
        $parent = $this->makeCategory('Computacion');
        $this->makeCategory('Teclados', $parent);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.categories.destroy', $parent))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = $this->makeCategory('Vacia');

        $this->actingAs($this->makeAdmin())->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_customer_cannot_manage_products(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        $this->actingAs($customer)->get(route('admin.products.create'))->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.products.destroy', $product))->assertForbidden();

        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }
}
