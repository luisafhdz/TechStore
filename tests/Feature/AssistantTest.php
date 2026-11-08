<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

/**
 * Integración tienda <-> microservicio de IA (el microservicio se simula con Http::fake).
 */
class AssistantTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    public function test_assistant_returns_reply_and_product_cards(): void
    {
        $product = $this->makeProduct(['name' => 'Audifonos Prueba', 'price' => 349]);

        Http::fake([
            '*' => Http::response([
                'reply'           => 'Encontré 1 opción que puede servirte:',
                'intent'          => 'recommendation',
                'recommendations' => [['id' => $product->id, 'score' => 3.0, 'reason' => 'Coincide con: audifonos']],
            ], 200),
        ]);

        $this->postJson(route('assistant.ask'), ['message' => 'audifonos baratos'])
            ->assertOk()
            ->assertJsonPath('reply', 'Encontré 1 opción que puede servirte:')
            ->assertJsonPath('products.0.name', 'Audifonos Prueba')
            ->assertJsonPath('products.0.price', '349.00')
            ->assertJsonPath('products.0.reason', 'Coincide con: audifonos');

        Http::assertSent(function ($request) use ($product) {
            return str_ends_with($request->url(), '/recommend')
                && $request['message'] === 'audifonos baratos'
                && collect($request['products'])->pluck('id')->contains($product->id);
        });
    }

    public function test_assistant_only_sends_active_products_with_stock(): void
    {
        $available = $this->makeProduct(['stock' => 5]);
        $this->makeProduct(['status' => 'inactive']);
        $this->makeProduct(['stock' => 0]);

        Http::fake(['*' => Http::response(['reply' => 'Sin resultados', 'intent' => 'no_results', 'recommendations' => []], 200)]);

        $this->postJson(route('assistant.ask'), ['message' => 'algo'])->assertOk();

        Http::assertSent(function ($request) use ($available) {
            return collect($request['products'])->pluck('id')->all() === [$available->id];
        });
    }

    public function test_assistant_ignores_recommendations_for_unknown_products(): void
    {
        Http::fake([
            '*' => Http::response([
                'reply'           => 'Resultado',
                'intent'          => 'recommendation',
                'recommendations' => [['id' => 99999, 'score' => 1.0, 'reason' => 'x']],
            ], 200),
        ]);

        $this->postJson(route('assistant.ask'), ['message' => 'algo'])
            ->assertOk()
            ->assertJsonPath('products', []);
    }

    public function test_assistant_degrades_gracefully_when_service_returns_error(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);

        $this->postJson(route('assistant.ask'), ['message' => 'hola'])
            ->assertStatus(503)
            ->assertJsonPath('products', [])
            ->assertJsonPath('reply', 'El asistente no está disponible en este momento. Intenta de nuevo en unos minutos.');
    }

    public function test_assistant_degrades_gracefully_when_service_is_unreachable(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Servicio apagado');
        });

        $this->postJson(route('assistant.ask'), ['message' => 'hola'])
            ->assertStatus(503)
            ->assertJsonPath('products', []);
    }

    public function test_assistant_validates_message(): void
    {
        Http::fake();

        $this->postJson(route('assistant.ask'), ['message' => ''])->assertStatus(422);
        $this->postJson(route('assistant.ask'), ['message' => str_repeat('a', 301)])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_assistant_is_available_without_login(): void
    {
        Http::fake(['*' => Http::response(['reply' => 'Hola', 'intent' => 'greeting', 'recommendations' => []], 200)]);

        $this->assertGuest();
        $this->postJson(route('assistant.ask'), ['message' => 'hola'])->assertOk();
    }
}
