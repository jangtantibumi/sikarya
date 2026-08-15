<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'sender_id',
        'channel',
        'type',
        'message',
        'attachment_name',
        'attachment_path',
        'attachment_mime',
        'attachment_size',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'attachment_size' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
