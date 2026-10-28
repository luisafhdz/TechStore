<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const STATUSES = [
        'pending'    => 'Pendiente',
        'processing' => 'En proceso',
        'shipped'    => 'Enviado',
        'delivered'  => 'Entregado',
        'cancelled'  => 'Cancelado',
    ];

    // Cambios de estado permitidos
    public const TRANSITIONS = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    public const PAYMENT_METHODS = [
        'card'     => 'Tarjeta (simulado)',
        'transfer' => 'Transferencia (simulado)',
        'cod'      => 'Pago contra entrega',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'payment_method',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return ['total' => 'decimal:2'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** TS-00012 */
    public function getNumberAttribute(): string
    {
        return 'TS-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function nextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canChangeTo(string $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    public function isCancellableByCustomer(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Cambia el estado respetando las transiciones permitidas.
     * Si el pedido se cancela, las piezas regresan al inventario.
     */
    public function changeStatus(string $newStatus): bool
    {
        return DB::transaction(function () use ($newStatus) {
            $order = self::whereKey($this->id)->lockForUpdate()->first();

            if (! $order || ! $order->canChangeTo($newStatus)) {
                return false;
            }

            if ($newStatus === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update(['status' => $newStatus]);
            $this->status = $newStatus;

            return true;
        });
    }
}
