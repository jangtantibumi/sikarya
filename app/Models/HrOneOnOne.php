<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrOneOnOne extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'manager_id',
        'employee_id',
        'meeting_date',
        'notes',
        'action_items',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
