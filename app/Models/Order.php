<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [

        'invoice_number',
        'customer_number',
        'customer_name',
        'fiscal_data',
        'address',
        'status',
        'price',
        'notes',
        'image'

        ];
}
