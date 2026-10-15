<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [categoría, nombre, marca, descripción, precio, existencia]
        $products = [
            ['Teclados', 'Teclado Mecánico K552', 'Redragon', 'Teclado mecánico compacto con switches rojos e iluminación RGB.', 899.00, 25],
            ['Teclados', 'Teclado Inalámbrico K380', 'Logitech', 'Teclado Bluetooth delgado para hasta 3 dispositivos.', 649.00, 40],
            ['Mouse', 'Mouse Gamer G203', 'Logitech', 'Mouse con sensor de 8000 DPI e iluminación personalizable.', 549.00, 30],
            ['Mouse', 'Mouse Inalámbrico M185', 'Logitech', 'Mouse inalámbrico compacto con receptor USB.', 249.00, 60],
            ['Monitores', 'Monitor 24" Full HD', 'Samsung', 'Monitor IPS de 24 pulgadas, 75 Hz, entrada HDMI.', 2799.00, 12],
            ['Monitores', 'Monitor Curvo 27"', 'LG', 'Monitor curvo de 27 pulgadas, 165 Hz, ideal para gaming.', 4999.00, 8],
            ['Cargadores', 'Cargador Rápido 20W USB-C', 'Anker', 'Cargador de pared USB-C con carga rápida de 20W.', 299.00, 80],
            ['Protectores', 'Protector de Pantalla Cristal Templado', 'Spigen', 'Cristal templado 9H con kit de instalación.', 199.00, 100],
            ['Audífonos', 'Audífonos Bluetooth Tune 510BT', 'JBL', 'Audífonos on-ear inalámbricos con 40 horas de batería.', 799.00, 35],
            ['Audífonos', 'Audífonos In-Ear con Micrófono', 'Sony', 'Audífonos con cable, graves potentes y micrófono integrado.', 349.00, 50],
            ['Bocinas', 'Bocina Portátil Flip 6', 'JBL', 'Bocina Bluetooth resistente al agua con 12 horas de reproducción.', 2199.00, 15],
            ['Micrófonos', 'Micrófono USB Condensador', 'Fifine', 'Micrófono USB para streaming y podcasts con soporte de mesa.', 899.00, 18],
            ['Cables', 'Cable HDMI 2.0 de 2 m', 'Steren', 'Cable HDMI 4K a 60 Hz con conectores dorados.', 149.00, 120],
            ['Adaptadores', 'Adaptador USB-C a HDMI', 'UGREEN', 'Adaptador compatible con laptops y celulares con salida de video.', 329.00, 45],
            ['Hubs USB', 'Hub USB 3.0 de 4 Puertos', 'TP-Link', 'Hub USB 3.0 de alta velocidad con 4 puertos.', 279.00, 55],
            ['Soportes', 'Soporte Ajustable para Laptop', 'Satechi', 'Soporte de aluminio plegable y ajustable en altura.', 699.00, 22],
            ['Baterías', 'Power Bank 10,000 mAh', 'Xiaomi', 'Batería externa con doble salida USB y carga rápida.', 499.00, 38],
            ['Cámaras', 'Cámara de Seguridad Wi-Fi 1080p', 'TP-Link Tapo', 'Cámara interior con visión nocturna y detección de movimiento.', 599.00, 20],
            ['Focos', 'Foco Inteligente LED RGB', 'Philips Hue', 'Foco Wi-Fi regulable con millones de colores.', 249.00, 70],
            ['Dispositivos inteligentes', 'Enchufe Inteligente Wi-Fi', 'TP-Link Kasa', 'Enchufe controlable desde el celular con programación horaria.', 299.00, 0],
        ];

        foreach ($products as [$categoryName, $name, $brand, $description, $price, $stock]) {
            $categoryId = Category::where('name', $categoryName)->value('id');

            Product::updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categoryId,
                    'brand'       => $brand,
                    'description' => $description,
                    'price'       => $price,
                    'stock'       => $stock,
                    'status'      => 'active',
                ]
            );
        }
    }
}
