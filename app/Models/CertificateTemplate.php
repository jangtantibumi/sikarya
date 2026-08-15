<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'name',
        'background_path',
        'background_mime',
        'file_hash',
        'is_active',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
