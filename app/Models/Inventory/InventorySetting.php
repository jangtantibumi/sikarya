<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySetting extends Model
{
    use HasFactory;

    protected $table = 'inv_settings';

    protected $guarded = [];
}
