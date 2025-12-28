<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelerRequest extends Model
{
    protected $fillable = [
        'status','user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
