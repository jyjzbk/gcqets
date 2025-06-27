<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneSchool extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zone_id',
        'school_id',
        'assignment_type',
        'distance',
        'assignment_reason',
        'assigned_by',
        'assigned_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'distance' => 'decimal:2',
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the education zone that owns the relationship.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(EducationZone::class, 'zone_id');
    }

    /**
     * Get the school that belongs to the zone.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'school_id');
    }

    /**
     * Get the user who assigned the school to the zone.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope a query to only include auto-assigned schools.
     */
    public function scopeAutoAssigned($query)
    {
        return $query->where('assignment_type', 'auto');
    }

    /**
     * Scope a query to only include manually assigned schools.
     */
    public function scopeManuallyAssigned($query)
    {
        return $query->where('assignment_type', 'manual');
    }

    /**
     * Calculate the distance between the school and the zone center.
     */
    public function calculateDistance()
    {
        $zone = $this->zone;
        $school = $this->school;
        
        if (!$zone || !$school || !$zone->center_latitude || !$zone->center_longitude || !$school->latitude || !$school->longitude) {
            return $this;
        }

        // Haversine formula to calculate distance between two points on Earth
        $earthRadius = 6371; // Radius of the Earth in kilometers
        
        $latFrom = deg2rad($zone->center_latitude);
        $lonFrom = deg2rad($zone->center_longitude);
        $latTo = deg2rad($school->latitude);
        $lonTo = deg2rad($school->longitude);
        
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;
        
        $this->distance = round($distance, 2);
        
        return $this;
    }

    /**
     * Check if the school is within a certain distance of the zone center.
     */
    public function isWithinDistance($kilometers): bool
    {
        return $this->distance <= $kilometers;
    }
}
