<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientInflow extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'date',
        'client_name',
        'domicile',
        'client_no',
        'start_project',
        'package',
        'notes',
        'project_value',
        'termin_no',
        'total_termin',
        'payment_amount',
        'remaining_balance',
        'payment_status',
        'invoice_file',
        'pj_survey',
        'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
