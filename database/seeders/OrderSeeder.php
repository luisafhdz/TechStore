<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Pedidos de ejemplo del cliente de prueba, uno por cada estado.
     */
    public function run(): void
    {
        if (Order::exists()) {
            return;
        }

        $customer = User::where('email', 'cliente@techstore.test')->first();

        if (! $customer) {
            return;
        }

        $samples = [
            ['pending',    'card',     [['Mouse Gamer G203', 1], ['Cable HDMI 2.0 de 2 m', 2]]],
            ['processing', 'transfer', [['Teclado Mecánico K552', 1]]],
            ['shipped',    'card',     [['Audífonos Bluetooth Tune 510BT', 1]]],
            ['delivered',  'cod',      [['Power Bank 10,000 mAh', 1], ['Cargador Rápido 20W USB-C', 1]]],
            ['cancelled',  'card',     [['Monitor 24" Full HD', 1]]],
        ];

        foreach ($samples as [$status, $payment, $lines]) {
            $order = Order::create([
                'user_id'          => $customer->id,
                'status'           => $status,
                'total'            => 0,
                'payment_method'   => $payment,
                'shipping_name'    => $customer->name,
                'shipping_phone'   => '0000000000',
                'shipping_address' => 'Calle Ejemplo 123, Colonia Centro, Chihuahua, Chih.',
            ]);

            $total = 0;

            foreach ($lines as [$name, $quantity]) {
                $product = Product::where('name', $name)->first();

                if (! $product) {
                    continue;
                }

                $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $quantity,
                ]);

                $total += (float) $product->price * $quantity;
            }

            $order->update(['total' => round($total, 2)]);
        }
    }
}
