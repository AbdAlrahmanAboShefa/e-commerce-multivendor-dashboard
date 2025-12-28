<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart_item extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'seller_id', 'price'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
