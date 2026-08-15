<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'certificate_name',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'certificate_file',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
