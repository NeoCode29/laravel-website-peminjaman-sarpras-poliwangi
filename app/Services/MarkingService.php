<?php

namespace App\Services;

use App\Models\Marking;
use App\Models\MarkingItem;
use App\Models\SystemSetting;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkingService
{
    /**
     * Create a new marking.
     *
     * @param array $data
     * @return Marking
     */
    public function createMarking(array $data)
    {
        DB::beginTransaction();
        try {
            // Calculate expiration time
            $markingDuration = SystemSetting::getValue('marking_duration_days', 3);
            $expiresAt = now()->addDays($markingDuration);
            
            // If planned_submit_by is set and earlier than expires_at, use that as expires_at
            if (!empty($data['planned_submit_by'])) {
                $plannedSubmitBy = Carbon::parse($data['planned_submit_by']);
                if ($plannedSubmitBy->lt($expiresAt)) {
                    $expiresAt = $plannedSubmitBy;
                }
            }

            // Create marking
            $marking = Marking::create([
                'user_id' => Auth::id(),
                'ukm_id' => $data['ukm_id'] ?? null,
                'prasarana_id' => $data['prasarana_id'] ?? null,
                'lokasi_custom' => $data['lokasi_custom'] ?? null,
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'jumlah_peserta' => $data['jumlah_peserta'] ?? null,
                'expires_at' => $expiresAt,
                'planned_submit_by' => $data['planned_submit_by'] ?? null,
                'status' => Marking::STATUS_ACTIVE,
                'event_name' => $data['event_name'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create marking items
            if (!empty($data['sarana_items'])) {
                foreach ($data['sarana_items'] as $saranaId) {
                    MarkingItem::create([
                        'marking_id' => $marking->id,
                        'sarana_id' => $saranaId,
                    ]);
                }
            }

            // Create notification for marking expiration (if expires in less than 24 hours)
            if ($expiresAt->diffInHours(now()) <= 24) {
                $this->createExpirationNotification($marking);
            }

            DB::commit();
            return $marking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating marking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing marking.
     *
     * @param Marking $marking
     * @param array $data
     * @return Marking
     */
    public function updateMarking(Marking $marking, array $data)
    {
        DB::beginTransaction();
        try {
            // Update marking
            $marking->update([
                'ukm_id' => $data['ukm_id'] ?? null,
                'prasarana_id' => $data['prasarana_id'] ?? null,
                'lokasi_custom' => $data['lokasi_custom'] ?? null,
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'jumlah_peserta' => $data['jumlah_peserta'] ?? null,
                'planned_submit_by' => $data['planned_submit_by'] ?? null,
                'event_name' => $data['event_name'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Update marking items
            $marking->items()->delete();
            if (!empty($data['sarana_items'])) {
                foreach ($data['sarana_items'] as $saranaId) {
                    MarkingItem::create([
                        'marking_id' => $marking->id,
                        'sarana_id' => $saranaId,
                    ]);
                }
            }

            DB::commit();
            return $marking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating marking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel a marking (soft delete).
     *
     * @param Marking $marking
     * @return Marking
     */
    public function cancelMarking(Marking $marking)
    {
        DB::beginTransaction();
        try {
            // Update status to cancelled
            $marking->update(['status' => Marking::STATUS_CANCELLED]);

            DB::commit();
            return $marking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling marking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extend marking expiration.
     *
     * @param Marking $marking
     * @param int $days
     * @return Marking
     */
    public function extendMarking(Marking $marking, int $days)
    {
        DB::beginTransaction();
        try {
            $newExpiresAt = $marking->expires_at->addDays($days);
            $marking->update(['expires_at' => $newExpiresAt]);

            // Create notification for marking expiration (if expires in less than 24 hours)
            if ($newExpiresAt->diffInHours(now()) <= 24) {
                $this->createExpirationNotification($marking);
            }

            DB::commit();
            return $marking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error extending marking: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert marking to "converted" status.
     *
     * @param Marking $marking
     * @return Marking
     */
    public function markAsConverted(Marking $marking)
    {
        DB::beginTransaction();
        try {
            $marking->update(['status' => Marking::STATUS_CONVERTED]);

            DB::commit();
            return $marking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking as converted: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check for conflicts with existing markings.
     *
     * @param array $data
     * @param int|null $excludeId
     * @return string|null
     */
    public function checkConflicts(array $data, $excludeId = null)
    {
        $query = Marking::where('status', Marking::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_datetime', [$data['start_datetime'], $data['end_datetime']])
                  ->orWhereBetween('end_datetime', [$data['start_datetime'], $data['end_datetime']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('start_datetime', '<=', $data['start_datetime'])
                         ->where('end_datetime', '>=', $data['end_datetime']);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Check prasarana conflicts
        if (!empty($data['prasarana_id'])) {
            $conflict = $query->where('prasarana_id', $data['prasarana_id'])->first();
            if ($conflict) {
                return "Prasarana sudah di-marking pada periode tersebut oleh {$conflict->user->name}.";
            }
        }

        // Check custom location conflicts (same location)
        if (!empty($data['lokasi_custom'])) {
            $conflict = $query->where('lokasi_custom', $data['lokasi_custom'])->first();
            if ($conflict) {
                return "Lokasi custom sudah di-marking pada periode tersebut oleh {$conflict->user->name}.";
            }
        }

        return null;
    }

    /**
     * Create notification for marking expiration.
     *
     * @param Marking $marking
     * @return void
     */
    private function createExpirationNotification(Marking $marking)
    {
        try {
            Notification::create([
                'user_id' => $marking->user_id,
                'title' => 'Marking Akan Segera Berakhir',
                'message' => "Marking untuk '{$marking->event_name}' akan berakhir pada {$marking->expires_at->format('d/m/Y H:i')}. Silakan konversi menjadi pengajuan resmi sebelum berakhir.",
                'type' => 'marking_expiring',
                'action_url' => route('marking.show', $marking->id),
                'is_clickable' => true,
                'expires_at' => $marking->expires_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating marking expiration notification: ' . $e->getMessage());
        }
    }

    /**
     * Auto expire markings that have passed their expiration date.
     *
     * @return int Number of markings expired
     */
    public function autoExpireMarkings()
    {
        $expiredCount = 0;
        
        try {
            $expiredMarkings = Marking::where('status', Marking::STATUS_ACTIVE)
                ->where('expires_at', '<=', now())
                ->get();
            
            foreach ($expiredMarkings as $marking) {
                $marking->update(['status' => Marking::STATUS_EXPIRED]);
                
                // Create notification for expired marking
                Notification::create([
                    'user_id' => $marking->user_id,
                    'title' => 'Marking Telah Berakhir',
                    'message' => "Marking untuk '{$marking->event_name}' telah berakhir dan tidak dapat digunakan lagi.",
                    'type' => 'marking_expired',
                    'action_url' => route('marking.show', $marking->id),
                    'is_clickable' => true,
                    'expires_at' => now()->addDays(7),
                ]);
                
                $expiredCount++;
            }
        } catch (\Exception $e) {
            Log::error('Error auto expiring markings: ' . $e->getMessage());
        }
        
        return $expiredCount;
    }

    /**
     * Send notifications for markings that are about to expire.
     *
     * @param int $hoursThreshold Hours before expiration to send notification
     * @return int Number of notifications sent
     */
    public function sendExpirationReminders(int $hoursThreshold = 24)
    {
        $notificationCount = 0;
        
        try {
            $expiringMarkings = Marking::where('status', Marking::STATUS_ACTIVE)
                ->where('expires_at', '<=', now()->addHours($hoursThreshold))
                ->where('expires_at', '>', now())
                ->get();
            
            foreach ($expiringMarkings as $marking) {
                // Check if notification already exists
                $existingNotification = Notification::where('user_id', $marking->user_id)
                    ->where('type', 'marking_expiring')
                    ->where('action_url', route('marking.show', $marking->id))
                    ->whereNull('read_at')
                    ->exists();
                
                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $marking->user_id,
                        'title' => 'Marking Akan Segera Berakhir',
                        'message' => "Marking untuk '{$marking->event_name}' akan berakhir dalam " . $marking->expires_at->diffInHours(now()) . " jam. Silakan konversi menjadi pengajuan resmi sebelum berakhir.",
                        'type' => 'marking_expiring',
                        'action_url' => route('marking.show', $marking->id),
                        'is_clickable' => true,
                        'expires_at' => $marking->expires_at,
                    ]);
                    
                    $notificationCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending marking expiration reminders: ' . $e->getMessage());
        }
        
        return $notificationCount;
    }
}







