<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'key',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'key');
    }
}
