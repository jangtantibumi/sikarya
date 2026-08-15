<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMembership extends Model
{
    protected $fillable = ['company_id', 'user_id', 'role', 'is_owner', 'is_active'];

    protected function casts(): array
    {
        return ['is_owner' => 'boolean', 'is_active' => 'boolean'];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
