<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Categorías definidas en el alcance del proyecto
        $tree = [
            'Computación'         => ['Teclados', 'Mouse', 'Monitores'],
            'Celulares y tablets' => ['Cargadores', 'Protectores'],
            'Audio'               => ['Audífonos', 'Bocinas', 'Micrófonos'],
            'Accesorios'          => ['Cables', 'Adaptadores', 'Hubs USB', 'Soportes', 'Baterías'],
            'Para el hogar'       => ['Cámaras', 'Focos', 'Dispositivos inteligentes'],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(['name' => $parentName], ['parent_id' => null]);

            foreach ($children as $childName) {
                Category::firstOrCreate(['name' => $childName], ['parent_id' => $parent->id]);
            }
        }
    }
}
