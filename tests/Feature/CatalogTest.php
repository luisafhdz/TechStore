<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    public function test_catalog_lists_only_active_products(): void
    {
        $this->makeProduct(['name' => 'Mouse Visible']);
        $this->makeProduct(['name' => 'Teclado Oculto', 'status' => 'inactive']);

        $this->get('/catalogo')
            ->assertOk()
            ->assertSee('Mouse Visible')
            ->assertDontSee('Teclado Oculto');
    }

    public function test_filter_by_parent_category_includes_subcategories(): void
    {
        $parent = $this->makeCategory('Computacion');
        $child = $this->makeCategory('Teclados', $parent);
        $other = $this->makeCategory('Audio');

        $this->makeProduct(['name' => 'Teclado Dentro', 'category_id' => $child->id]);
        $this->makeProduct(['name' => 'Bocina Fuera', 'category_id' => $other->id]);

        $this->get('/catalogo?category=' . $parent->id)
            ->assertOk()
            ->assertSee('Teclado Dentro')
            ->assertDontSee('Bocina Fuera');
    }

    public function test_search_filters_by_name(): void
    {
        $this->makeProduct(['name' => 'Mouse Gamer Pro']);
        $this->makeProduct(['name' => 'Cable HDMI Largo']);

        $this->get('/catalogo?q=Gamer')
            ->assertOk()
            ->assertSee('Mouse Gamer Pro')
            ->assertDontSee('Cable HDMI Largo');
    }

    public function test_product_detail_is_visible(): void
    {
        $product = $this->makeProduct(['name' => 'Monitor Detalle', 'brand' => 'Samsung']);

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('Monitor Detalle')
            ->assertSee('Samsung');
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $product = $this->makeProduct(['status' => 'inactive']);

        $this->get(route('catalog.show', $product))->assertNotFound();
    }

    public function test_out_of_stock_product_is_marked_as_sold_out(): void
    {
        $this->makeProduct(['name' => 'Producto Agotado Uno', 'stock' => 0]);

        $this->get('/catalogo')->assertOk()->assertSee('Agotado');
    }
}
