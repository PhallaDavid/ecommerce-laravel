<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'delivery_latitude',
        'delivery_longitude',
        'payment_method',
        'billing_address',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
        'total' => 'float',
        'billing_address' => 'array',
        'shipping_address' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
