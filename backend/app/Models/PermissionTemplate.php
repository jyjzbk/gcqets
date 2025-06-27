<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionTemplate extends Model
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
        'description',
        'created_by',
        'level',
        'is_system',
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level' => 'integer',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the user who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the permissions associated with the template.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'template_permissions')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system templates.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include custom templates.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope a query to filter by level.
     */
    public function scopeLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Apply the template to an organization.
     */
    public function applyToOrganization(Organization $organization)
    {
        // Get all permissions from the template
        $permissions = $this->permissions;
        
        // Apply permissions to the organization
        foreach ($permissions as $permission) {
            // Logic to apply permission to organization
            // This would depend on how permissions are structured in your system
        }
        
        return true;
    }

    /**
     * Check if the template contains a specific permission.
     */
    public function hasPermission($permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
}
