<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'warehouse_id', 'cashier_id', 'status', 'opening_cash', 'closing_cash', 'opened_at', 'closed_at'];
}
