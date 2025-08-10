<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EquipmentMaintenance extends Model
{
    use HasFactory;

    protected $table = 'equipment_maintenance';

    protected $fillable = [
        'monitored_equipment_id',
        'maintenance_type',
        'scheduled_date',
        'completed_date',
        'estimated_duration',
        'actual_duration',
        'description',
        'priority',
        'status',
        'performed_by',
        'cost',
        'notes',
        'completion_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
        'cost' => 'decimal:2',
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Maintenance belongs to monitored equipment
     */
    public function monitoredEquipment()
    {
        return $this->belongsTo(MonitoredEquipment::class, 'monitored_equipment_id');
    }

    /**
     * Maintenance performed by user
     */
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
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
            'scheduled' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityBadgeColorAttribute()
    {
        return match($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get formatted maintenance type
     */
    public function getFormattedMaintenanceTypeAttribute()
    {
        return match($this->maintenance_type) {
            'routine' => 'Routine Maintenance',
            'repair' => 'Repair',
            'inspection' => 'Inspection',
            'calibration' => 'Calibration',
            'replacement' => 'Replacement',
            default => ucfirst($this->maintenance_type)
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'overdue' => 'Overdue',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get formatted priority
     */
    public function getFormattedPriorityAttribute()
    {
        return ucfirst($this->priority);
    }

    /**
     * Check if maintenance is overdue
     */
    public function getIsOverdueAttribute()
    {
        return $this->status === 'scheduled' && $this->scheduled_date < now();
    }

    /**
     * Check if maintenance is upcoming (within 7 days)
     */
    public function getIsUpcomingAttribute()
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_date >= now() && 
               $this->scheduled_date <= now()->addDays(7);
    }

    /**
     * Get estimated duration in hours
     */
    public function getEstimatedDurationHoursAttribute()
    {
        return round($this->estimated_duration / 60, 2);
    }

    /**
     * Get actual duration in hours
     */
    public function getActualDurationHoursAttribute()
    {
        return $this->actual_duration ? round($this->actual_duration / 60, 2) : null;
    }

    /**
     * Get days until scheduled maintenance
     */
    public function getDaysUntilScheduledAttribute()
    {
        if ($this->status !== 'scheduled') {
            return null;
        }
        
        return now()->diffInDays($this->scheduled_date, false);
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope for scheduled maintenance
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for completed maintenance
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for overdue maintenance
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_date', '<', now());
    }

    /**
     * Scope for upcoming maintenance (within specified days)
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'scheduled')
                    ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope for high priority maintenance
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    /**
     * Scope for specific maintenance type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('maintenance_type', $type);
    }

    // ====================================================================
    // METHODS
    // ====================================================================

    /**
     * Mark maintenance as completed
     */
    public function markAsCompleted($performedBy, $actualDuration = null, $cost = null, $completionNotes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'performed_by' => $performedBy,
            'actual_duration' => $actualDuration,
            'cost' => $cost,
            'completion_notes' => $completionNotes,
        ]);


        // Update equipment's last maintenance date
        $this->monitoredEquipment->update([
            'last_maintenance_date' => now(),
            'next_maintenance_date' => $this->calculateNextMaintenanceDate(),
        ]);
    }

    /**
     * Calculate next maintenance date based on type
     */
    private function calculateNextMaintenanceDate()
    {
        $intervals = [
            'routine' => 90, // 3 months
            'inspection' => 180, // 6 months
            'calibration' => 365, // 1 year
            'repair' => null, // No automatic scheduling
            'replacement' => null, // No automatic scheduling
        ];

        $days = $intervals[$this->maintenance_type] ?? null;
        
        return $days ? now()->addDays($days) : null;
    }

    /**
     * Cancel maintenance
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'completion_notes' => $reason,
        ]);
    }
}
