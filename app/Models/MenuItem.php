<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_number',
        'name',
        'price',
    ];

    protected $casts = [
        'item_number' => 'integer',
        'price' => 'decimal:2',
    ];
}
