<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'code', 'name', 'email', 'phone', 'address', 'is_active'];

    public function contacts()
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function ratings()
    {
        return $this->hasMany(SupplierRating::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
