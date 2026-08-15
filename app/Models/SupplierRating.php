<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SupplierRating extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'rater_id',
        'rating',
        'review',
        'lead_time_score',
        'quality_score',
        'price_score',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }
}
