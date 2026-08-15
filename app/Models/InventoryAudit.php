<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class InventoryAudit extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'status',
        'scheduled_date',
        'completed_date',
        'created_by_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function lines()
    {
        return $this->hasMany(InventoryAuditLine::class);
    }
}
