<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock',
        'image',
        'seller_id',
    ];
    protected $appends = ['image_url'];

public function getImageUrlAttribute()
{
    return $this->image
        ? asset('storage/' . $this->image)
        : null;
}
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
