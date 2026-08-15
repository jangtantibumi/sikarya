<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Support\Facades\Auth;

class InventoryAuditObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating($model): void
    {
        if (Auth::check() && empty($model->created_by)) {
            $model->created_by = Auth::id();
        }
    }

    /**
     * Handle the Model "updating" event.
     */
    public function updating($model): void
    {
        if (Auth::check()) {
            $model->updated_by = Auth::id();
        }
    }

    /**
     * Handle the Model "deleting" event.
     */
    public function deleting($model): void
    {
        if (Auth::check()) {
            $model->deleted_by = Auth::id();
            // We use silent save so it doesn't trigger updating event again
            $model->saveQuietly();
        }
    }
}
