<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpDocument extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'document_type',
        'document_number',
        'title',
        'owner_user_id',
        'created_by_id',
        'certificate_template_id',
        'supervisor_user_id',
        'status',
        'issued_at',
        'content',
        'verification_token',
        'document_hash',
        'signed_at',
        'revoked_at',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'content' => 'array',
            'signed_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class, 'document_id');
    }
}
