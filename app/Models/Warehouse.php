<?php
namespace App\Models;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
class Warehouse extends Model { use BelongsToCompany; protected $fillable=['company_id','code','name','location','is_active']; protected $casts=['is_active'=>'boolean']; 

    public function racks()
    {
        return $this->hasMany(WarehouseRack::class);
    }
}
