<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class MetricSnapshot extends Model
{
    use BelongsToCompany;
    protected $fillable = [
        'subject_type', 'subject_id', 'snapshot_date', 'data'
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'data' => 'array',
    ];

    public function subject()
    {
        return $this->morphTo();
    }
}
