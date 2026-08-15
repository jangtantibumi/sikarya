<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'name', 'slug', 'industry', 'timezone', 'currency', 'status', 'branding'];

    protected function casts(): array
    {
        return ['branding' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            $company->uuid ??= (string) Str::uuid();
        });
    }

    public function features()
    {
        return $this->hasMany(CompanyFeature::class);
    }

    public function memberships()
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_memberships')
            ->withPivot(['role', 'is_owner', 'is_active'])
            ->withTimestamps();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
