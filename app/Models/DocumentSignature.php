<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_id',
        'signer_id',
        'signer_role',
        'signature_method',
        'signature_hash',
        'image_path',
        'metadata',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'signed_at' => 'datetime',
        ];
    }

    public function document()
    {
        return $this->belongsTo(ErpDocument::class, 'document_id');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
