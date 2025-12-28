<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_item extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'seller_id',
        'commission_amount',
        'seller_earnings',
    ];

    public function order()
    {
        return $this->belongsTo(order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
