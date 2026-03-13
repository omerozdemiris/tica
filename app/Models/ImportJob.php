<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $table = 'import_jobs';

    protected $fillable = [
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'is_locked',
        'locked_at',
        'error',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public const STATUS_VALIDATING = 'validating';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const LOCK_TIMEOUT_SECONDS = 60;

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_VALIDATING,
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED
        ]);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_VALIDATING,
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    public function progress(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }

    /**
     * Lock mekanizması - race condition önleme
     */
    public function acquireLock(): bool
    {
        if ($this->is_locked) {
            if ($this->locked_at && $this->locked_at->diffInSeconds(now()) > self::LOCK_TIMEOUT_SECONDS) {
                $this->releaseLock();
            } else {
                return false;
            }
        }

        $updated = self::where('id', $this->id)
            ->where('is_locked', false)
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
            ]);

        if ($updated) {
            $this->refresh();
            return true;
        }

        return false;
    }

    public function releaseLock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsCompleted(?string $error = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'error' => $error,
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error' => $error,
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'error' => 'Kullanıcı tarafından iptal edildi.',
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }
}