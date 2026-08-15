<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmSegment extends Model
{
    use HasFactory;

    protected $table = 'crm_segments';

    protected $fillable = [
        'name',
        'description',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    public function customers()
    {
        return $this->hasMany(CrmCustomer::class, 'segment_id');
    }
}
