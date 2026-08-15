<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class SupplierContact extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'name',
        'position',
        'phone',
        'email',
        'is_primary',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
