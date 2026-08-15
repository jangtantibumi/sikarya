<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'inv_categories';

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
