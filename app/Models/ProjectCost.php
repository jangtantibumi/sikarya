<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCost extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'project_id',
        'cost_date',
        'category',
        'description',
        'amount',
        'vendor',
        'created_by_id',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'cost_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function attachments()
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }
}
