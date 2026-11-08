<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

/**
 * Datos de ejemplo para las pruebas de TechStore.
 */
trait CreatesShopData
{
    protected function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function makeCustomer(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'customer'], $attributes));
    }

    protected function makeCategory(string $name, ?Category $parent = null): Category
    {
        return Category::create(['name' => $name, 'parent_id' => $parent?->id]);
    }

    protected function makeProduct(array $attributes = []): Product
    {
        if (! isset($attributes['category_id'])) {
            $attributes['category_id'] = $this->makeCategory('Categoria ' . uniqid())->id;
        }

        return Product::create(array_merge([
            'name'        => 'Producto ' . uniqid(),
            'brand'       => 'Marca',
            'description' => 'Descripcion de prueba',
            'price'       => 100.00,
            'stock'       => 10,
            'status'      => 'active',
        ], $attributes));
    }

    /**
     * Crea un pedido con sus partidas: $lines = [[$producto, $cantidad], ...]
     * No modifica el inventario (se asume que ya estaba descontado).
     */
    protected function makeOrder(User $user, string $status = 'pending', array $lines = []): Order
    {
        $order = Order::create([
            'user_id'          => $user->id,
            'status'           => $status,
            'total'            => 0,
            'payment_method'   => 'card',
            'shipping_name'    => 'Cliente Prueba',
            'shipping_phone'   => '0000000000',
            'shipping_address' => 'Calle Falsa 123',
        ]);

        $total = 0;

        foreach ($lines as [$product, $quantity]) {
            $order->items()->create([
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'unit_price'   => $product->price,
                'quantity'     => $quantity,
            ]);
            $total += (float) $product->price * $quantity;
        }

        $order->update(['total' => $total]);

        return $order;
    }
}
