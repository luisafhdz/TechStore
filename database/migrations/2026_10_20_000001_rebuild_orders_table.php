<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reemplaza la tabla "orders" del CRUD de ejemplo por la de pedidos reales de la tienda
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // pending | processing | shipped | delivered | cancelled
            $table->string('status', 20)->default('pending');
            $table->decimal('total', 10, 2)->default(0);
            // card | transfer | cod  (el pago es simulado)
            $table->string('payment_method', 20);
            $table->string('shipping_name');
            $table->string('shipping_phone', 20)->nullable();
            $table->text('shipping_address');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
