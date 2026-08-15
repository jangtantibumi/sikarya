<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmLoyalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_name',
        'spending_amount',
        'points_awarded',
        'is_active',
    ];
}
