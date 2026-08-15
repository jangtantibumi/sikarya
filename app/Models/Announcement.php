<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'title',
        'content',
        'is_active',
        'created_by_id',
        'target_type',
        'target_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function reads()
    {
        return $this->belongsToMany(User::class, 'announcement_reads')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
}
