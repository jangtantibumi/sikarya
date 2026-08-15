<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'rate_per_hour',
    ];

    protected $casts = [
        'rate_per_hour' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
