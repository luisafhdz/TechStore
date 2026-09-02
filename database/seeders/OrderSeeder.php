<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Order;
 
class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::create([
            'invoice_number' => 12,
            'customer_number' => 33,
            'customer_name' => 'Roberto Rodriguez',
            'fiscal_data' => 'HWIWEOO1223',
            'address' => 'DIRECCION ORDEN EJEMPLO1',
            'status' => 'ACTIVE',
            'price' => 10.56,
            'notes' => 'EJEMPLO1.'
        ]);
 
        Order::create([
            'invoice_number' => 11,
            'customer_number' => 43,
            'customer_name' => 'Roberta Martinez',
            'fiscal_data' => 'HWIWEOO1223',
            'address' => 'DIRECCION ORDEN EJEMPLO2',
            'status' => 'ACTIVE',
            'price' => 16.78,
            'notes' => 'EJEMPLO2.'
        ]);
 
        Order::create([
            'invoice_number' => 56,
            'customer_number' => 323,
            'customer_name' => 'Juanita Perez',
            'fiscal_data' => 'HWIWE78823',
            'address' => 'DIRECCION ORDEN EJEMPLO3',
            'status' => 'ACTIVE',
            'price' => 20.56,
            'notes' => 'EJEMPLO3.'
            
        ]);

    }
}