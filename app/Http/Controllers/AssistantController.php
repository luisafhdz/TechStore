<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AssistantController extends Controller
{
    // Recibe el mensaje del chat, consulta al microservicio de IA y devuelve respuesta + productos
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:300',
        ]);

        $catalog = Product::with('category.parent')
            ->active()
            ->where('stock', '>', 0)
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'brand'           => $p->brand,
                'category'        => $p->category->name,
                'parent_category' => optional($p->category->parent)->name,
                'price'           => (float) $p->price,
                'stock'           => $p->stock,
                'description'     => $p->description,
            ])
            ->values()
            ->all();

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->post(rtrim(config('ia.url'), '/') . '/recommend', [
                    'message'  => $data['message'],
                    'products' => $catalog,
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('El microservicio de IA respondió con error.');
            }

            $result = $response->json();
        } catch (\Throwable $e) {
            return response()->json([
                'reply'    => 'El asistente no está disponible en este momento. Intenta de nuevo en unos minutos.',
                'products' => [],
            ], 503);
        }

        $recommendations = collect($result['recommendations'] ?? []);
        $products = Product::whereIn('id', $recommendations->pluck('id'))->get()->keyBy('id');

        $cards = $recommendations->map(function ($rec) use ($products) {
            $p = $products->get($rec['id']);

            if (! $p) {
                return null;
            }

            return [
                'id'     => $p->id,
                'name'   => $p->name,
                'brand'  => $p->brand,
                'price'  => number_format($p->price, 2),
                'url'    => route('catalog.show', $p),
                'image'  => $p->image ? asset('storage/' . $p->image) : null,
                'reason' => $rec['reason'] ?? null,
            ];
        })->filter()->values();

        return response()->json([
            'reply'    => $result['reply'] ?? 'No pude procesar tu mensaje.',
            'products' => $cards,
        ]);
    }
}
