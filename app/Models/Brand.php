<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
