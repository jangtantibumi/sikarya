<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use \App\Models\Concerns\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'system_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
