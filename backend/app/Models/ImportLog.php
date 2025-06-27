<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'import_type',
        'status',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'error_details',
        'import_options',
        'started_at',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'error_details' => 'json',
        'import_options' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer'
    ];

    /**
     * Get the user that performed the import.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending imports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing imports.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include completed imports.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed imports.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get the progress percentage of the import.
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_rows <= 0) {
            return 0;
        }

        return min(100, round(($this->processed_rows / $this->total_rows) * 100));
    }

    /**
     * Get the duration of the import in seconds.
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at) {
            return 0;
        }

        $end = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($end);
    }
}
