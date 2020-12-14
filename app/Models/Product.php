<?php declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'stock',
        'price',
        'created_at',
        'discontinued_at',
        'updated_at',
    ];
}
