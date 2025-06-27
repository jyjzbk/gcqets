<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationZone extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'district_id',
        'description',
        'boundary_points',
        'center_longitude',
        'center_latitude',
        'area',
        'school_count',
        'student_capacity',
        'current_students',
        'manager_id',
        'manager_name',
        'manager_phone',
        'status',
        'extra_data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'center_longitude' => 'decimal:7',
        'center_latitude' => 'decimal:7',
        'area' => 'decimal:2',
        'school_count' => 'integer',
        'student_capacity' => 'integer',
        'current_students' => 'integer',
        'extra_data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the district that owns the education zone.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'district_id');
    }

    /**
     * Get the manager of the education zone.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the schools in this education zone.
     */
    public function schools()
    {
        return $this->belongsToMany(Organization::class, 'zone_schools', 'zone_id', 'school_id')
            ->withPivot(['assignment_type', 'distance', 'assignment_reason', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get the zone-school relationships.
     */
    public function zoneSchools(): HasMany
    {
        return $this->hasMany(ZoneSchool::class, 'zone_id');
    }

    /**
     * Scope a query to only include active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive zones.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include planning zones.
     */
    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    /**
     * Get the utilization percentage of the zone.
     */
    public function getUtilizationPercentageAttribute()
    {
        if ($this->student_capacity <= 0) {
            return 0;
        }

        return min(100, round(($this->current_students / $this->student_capacity) * 100));
    }

    /**
     * Get the boundary points as an array of coordinates.
     */
    public function getBoundaryPointsArrayAttribute()
    {
        if (empty($this->boundary_points)) {
            return [];
        }

        $points = explode(';', $this->boundary_points);
        $coordinates = [];

        foreach ($points as $point) {
            $parts = explode(',', $point);
            if (count($parts) === 2) {
                $coordinates[] = [
                    'lng' => (float) $parts[0],
                    'lat' => (float) $parts[1]
                ];
            }
        }

        return $coordinates;
    }

    /**
     * Set the boundary points from an array of coordinates.
     */
    public function setBoundaryPointsFromArray(array $coordinates)
    {
        $points = [];
        foreach ($coordinates as $coordinate) {
            if (isset($coordinate['lng']) && isset($coordinate['lat'])) {
                $points[] = $coordinate['lng'] . ',' . $coordinate['lat'];
            }
        }

        $this->boundary_points = implode(';', $points);
        return $this;
    }

    /**
     * Calculate the center point of the zone.
     */
    public function calculateCenter()
    {
        $points = $this->boundary_points_array;
        
        if (empty($points)) {
            return $this;
        }

        $lngSum = 0;
        $latSum = 0;
        $count = count($points);

        foreach ($points as $point) {
            $lngSum += $point['lng'];
            $latSum += $point['lat'];
        }

        $this->center_longitude = $lngSum / $count;
        $this->center_latitude = $latSum / $count;

        return $this;
    }

    /**
     * Update the school count.
     */
    public function updateSchoolCount()
    {
        $this->school_count = $this->schools()->count();
        return $this;
    }

    /**
     * Update the current students count.
     */
    public function updateCurrentStudents()
    {
        $this->current_students = $this->schools()->sum('student_count');
        return $this;
    }
}
