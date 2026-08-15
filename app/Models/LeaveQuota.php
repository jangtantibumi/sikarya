<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveQuota extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'year',
        'total_quota',
        'used_quota',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function remaining(): int
    {
        return $this->total_quota - $this->used_quota;
    }
}
