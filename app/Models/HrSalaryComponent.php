<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'is_default',
        'default_amount',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
