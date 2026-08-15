<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrExitClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'resignation_request_id',
        'user_id',
        'department_name',
        'clearance_item',
        'is_cleared',
        'cleared_by',
        'cleared_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function resignationRequest()
    {
        return $this->belongsTo(ResignationRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clearedBy()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }
}
