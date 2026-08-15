<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'product_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class); // The raw material
    }

    public function material()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
