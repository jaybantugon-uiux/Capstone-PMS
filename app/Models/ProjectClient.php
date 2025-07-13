<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectClient extends Pivot
{
    protected $table = 'project_clients';

    protected $fillable = [
        'project_id',
        'client_id',
        'access_level',
        'can_view_photos',
        'can_view_reports',
        'can_view_issues',
        'can_receive_notifications',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'can_view_photos' => 'boolean',
        'can_view_reports' => 'boolean',
        'can_view_issues' => 'boolean',
        'can_receive_notifications' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * Get the project this client assignment belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the client user
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the user who assigned this client
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if client has permission for specific feature
     */
    public function hasPermission(string $permission): bool
    {
        return match($permission) {
            'view_photos' => $this->can_view_photos,
            'view_reports' => $this->can_view_reports,
            'view_issues' => $this->can_view_issues,
            'receive_notifications' => $this->can_receive_notifications,
            default => false,
        };
    }

    /**
     * Get access level display name
     */
    public function getAccessLevelDisplayAttribute(): string
    {
        return match($this->access_level) {
            'view' => 'View Only',
            'limited' => 'Limited Access',
            'full' => 'Full Access',
            default => 'Unknown',
        };
    }

    /**
     * Scope for clients with specific access level
     */
    public function scopeWithAccessLevel($query, string $level)
    {
        return $query->where('access_level', $level);
    }

    /**
     * Scope for clients who can receive notifications
     */
    public function scopeCanReceiveNotifications($query)
    {
        return $query->where('can_receive_notifications', true);
    }
}