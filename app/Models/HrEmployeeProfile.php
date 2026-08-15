<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'ktp_number',
        'birth_place',
        'birth_date',
        'gender',
        'marital_status',
        'religion',
        'address',
        'npwp_number',
        'bpjs_tk_number',
        'bpjs_kes_number',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
