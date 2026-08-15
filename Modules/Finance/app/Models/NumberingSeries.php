<?php

namespace Modules\Finance\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Finance\Database\Factories\NumberingSeriesFactory;

class NumberingSeries extends Model
{
    use BelongsToCompany, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'finance_numbering_series';

    protected $fillable = [
        'company_id',
        'branch_id',
        'module_code',
        'document_type',
        'prefix',
        'suffix',
        'length',
        'current_number',
        'reset_cycle',
        'last_reset_date',
        'sample_number',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'length' => 'integer',
        'current_number' => 'integer',
        'last_reset_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): NumberingSeriesFactory
    {
        return NumberingSeriesFactory::new();
    }

    public function generateSample(): string
    {
        $padded = str_pad((string) ($this->current_number + 1), $this->length, '0', STR_PAD_LEFT);
        $prefix = str_replace(
            ['{YYYY}', '{YY}', '{MM}', '{DD}'],
            [date('Y'), date('y'), date('m'), date('d')],
            $this->prefix ?? ''
        );

        return $prefix.$padded.($this->suffix ?? '');
    }

    public function getNextNumber(): string
    {
        $this->current_number += 1;

        $prefix = str_replace(
            ['{YYYY}', '{YY}', '{MM}', '{DD}'],
            [date('Y'), date('y'), date('m'), date('d')],
            $this->prefix ?? ''
        );

        $padded = str_pad((string) $this->current_number, $this->length, '0', STR_PAD_LEFT);
        $this->sample_number = $prefix.$padded.($this->suffix ?? '');
        $this->save();

        return $this->sample_number;
    }
}
