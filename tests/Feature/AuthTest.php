<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, CreatesShopData;

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/registro', [
            'name'                  => 'Ana Prueba',
            'email'                 => 'ana@example.test',
            'password'              => 'Secreta123',
            'password_confirmation' => 'Secreta123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.test', 'role' => 'customer']);
    }

    public function test_register_cannot_create_an_admin(): void
    {
        $this->post('/registro', [
            'name'                  => 'Intruso',
            'email'                 => 'intruso@example.test',
            'password'              => 'Secreta123',
            'password_confirmation' => 'Secreta123',
            'role'                  => 'admin',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'intruso@example.test', 'role' => 'customer']);
    }

    public function test_register_validates_data(): void
    {
        $this->post('/registro', [
            'name'                  => '',
            'email'                 => 'no-es-un-correo',
            'password'              => 'corta',
            'password_confirmation' => 'distinta',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->makeCustomer(['email' => 'cliente@example.test', 'password' => 'Correcta123']);

        $this->post('/login', ['email' => 'cliente@example.test', 'password' => 'Incorrecta'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_login_redirects_to_catalog(): void
    {
        $this->makeCustomer(['email' => 'cliente@example.test', 'password' => 'Correcta123']);

        $this->post('/login', ['email' => 'cliente@example.test', 'password' => 'Correcta123'])
            ->assertRedirect(route('catalog.index'));

        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        $admin = $this->makeAdmin();
        $admin->update(['email' => 'admin@example.test', 'password' => 'Admin12345']);

        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'Admin12345'])
            ->assertRedirect(route('admin.products.index'));
    }

    public function test_logout_closes_the_session(): void
    {
        $this->actingAs($this->makeCustomer())->post('/logout');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_cart(): void
    {
        $this->get('/carrito')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->makeCustomer())->get('/admin/products')->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $this->actingAs($this->makeAdmin())->get('/admin/products')->assertOk();
    }
}
