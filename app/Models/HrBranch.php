<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
