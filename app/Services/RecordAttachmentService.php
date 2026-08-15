<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RecordAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordAttachmentService
{
    public function store(
        Model $record,
        UploadedFile $file,
        User $actor,
        string $category = 'supporting_document',
    ): RecordAttachment {
        $extension = Str::lower($file->getClientOriginalExtension());
        $storedName = Str::uuid().($extension ? ".{$extension}" : '');
        $path = $file->storeAs('record-attachments/'.now()->format('Y/m'), $storedName);

        return $record->attachments()->create([
            'uploaded_by_id' => $actor->id,
            'category' => $category,
            'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
        ]);
    }

    public function deleteStoredFile(RecordAttachment $attachment): void
    {
        Storage::disk('local')->delete($attachment->stored_path);
    }
}
