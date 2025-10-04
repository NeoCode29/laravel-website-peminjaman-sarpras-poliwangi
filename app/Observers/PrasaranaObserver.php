<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Prasarana;
use Illuminate\Support\Facades\Auth;

class PrasaranaObserver
{
    public function created(Prasarana $prasarana): void
    {
        $this->log('create', $prasarana, null, $prasarana->toArray());
    }

    public function updated(Prasarana $prasarana): void
    {
        $this->log('update', $prasarana, $prasarana->getOriginal(), $prasarana->getChanges());
    }

    public function deleted(Prasarana $prasarana): void
    {
        $this->log('delete', $prasarana, $prasarana->getOriginal(), null);
    }

    private function log(string $action, Prasarana $model, $old, $new): void
    {
        AuditLog::create([
            'user_id' => optional(Auth::user())->id,
            'action' => $action,
            'model_type' => Prasarana::class,
            'model_id' => $model->id,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'description' => 'Prasarana '.$action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}



