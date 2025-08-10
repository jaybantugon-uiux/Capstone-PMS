<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonitoredEquipment extends Model
{
    use HasFactory;

    protected $table = 'monitored_equipment';

    protected $fillable = [
        'user_id',
        'project_id',
        'equipment_request_id',
        'equipment_name',
        'equipment_description',
        'usage_type',
        'quantity',
        'status',
        'availability_status',
        'location',
        'serial_number',
        'purchase_date',
        'warranty_expiry',
        'last_maintenance_date',
        'next_maintenance_date',
        'last_status_update',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'last_status_update' => 'datetime',
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Equipment belongs to a user (site coordinator)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Equipment belongs to a project (if project site usage)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Equipment has an associated request
     */
    public function equipmentRequest()
    {
        return $this->belongsTo(EquipmentRequest::class, 'equipment_request_id');
    }

    /**
     * Equipment has many maintenance schedules
     */
    public function maintenanceSchedules()
    {
        return $this->hasMany(EquipmentMaintenance::class, 'monitored_equipment_id');
    }

    // ====================================================================
    // ACCESSORS & MUTATORS
    // ====================================================================

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'pending_approval' => 'warning',
            'declined' => 'danger',
            'inactive' => 'secondary',
            'maintenance' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get availability badge color
     */
    public function getAvailabilityBadgeColorAttribute()
    {
        return match($this->availability_status) {
            'available' => 'success',
            'in_use' => 'primary',
            'maintenance' => 'warning',
            'out_of_order' => 'danger',
            default => 'secondary'
        };
    }

        /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'pending_approval' => 'Pending Approval',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'maintenance' => 'Under Maintenance',
            'declined' => 'Declined',
            default => ucfirst($this->status)
        };
    }

        /**
     * Get formatted availability status
     */
    public function getFormattedAvailabilityStatusAttribute()
    {
        return match($this->availability_status) {
            'available' => 'Available',
            'in_use' => 'In Use',
            'maintenance' => 'Under Maintenance',
            'out_of_order' => 'Out of Order',
            default => ucfirst(str_replace('_', ' ', $this->availability_status))
        };
    }

    /**
     * Get formatted usage type
     */
    public function getFormattedUsageTypeAttribute()
    {
        return match($this->usage_type) {
            'personal' => 'Personal Use',
            'project_site' => 'Project Site',
            default => ucfirst($this->usage_type)
        };
    }

    /**
     * Check if equipment needs maintenance
     */
    public function getNeedsMaintenanceAttribute()
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        
        return $this->next_maintenance_date <= now()->addDays(7);
    }

    /**
     * Check if equipment is overdue for maintenance
     */
    public function getMaintenanceOverdueAttribute()
    {
        if (!$this->next_maintenance_date) {
            return false;
        }
        
        return $this->next_maintenance_date < now();
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope for active equipment
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for available equipment
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', 'available');
    }

    /**
     * Scope for personal use equipment
     */
    public function scopePersonalUse($query)
    {
        return $query->where('usage_type', 'personal');
    }

    /**
     * Scope for project site equipment
     */
    public function scopeProjectSite($query)
    {
        return $query->where('usage_type', 'project_site');
    }

    /**
     * Scope for equipment needing maintenance
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now()->addDays(7));
    }

    /**
     * Scope for overdue maintenance
     */
    public function scopeMaintenanceOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now());
    }
}
