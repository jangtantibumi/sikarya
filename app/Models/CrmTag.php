<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmTag extends Model
{
    use HasFactory;

    protected $table = 'crm_tags';

    protected $fillable = [
        'name',
        'color',
    ];

    public function customers()
    {
        return $this->belongsToMany(CrmCustomer::class, 'crm_customer_tag', 'tag_id', 'customer_id');
    }
}
