<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_secret',
        'updated_by',
    ];

    protected $hidden = [
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'encrypted',
            'is_secret' => 'boolean',
        ];
    }
}
