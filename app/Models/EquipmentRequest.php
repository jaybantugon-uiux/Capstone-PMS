<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentRequest extends Model
{
    use HasFactory;

    protected $table = 'equipment_requests';

    protected $fillable = [
        'user_id',
        'project_id',
        'monitored_equipment_id',
        'equipment_name',
        'equipment_description',
        'usage_type',
        'quantity',
        'estimated_cost',
        'justification',
        'urgency_level',
        'additional_notes',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
        'decline_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // ====================================================================
    // RELATIONSHIPS
    // ====================================================================

    /**
     * Request belongs to a user (site coordinator)
     * FIXED: Ensure this loads the Site Coordinator properly
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ADDED: Alias for getting the Site Coordinator specifically
     */
    public function siteCoordinator()
    {
        return $this->belongsTo(User::class, 'user_id')->where('role', 'sc');
    }

    /**
     * Request belongs to a project (if project site usage)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Request approved by admin
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Request has associated monitored equipment
     */
    public function monitoredEquipment()
    {
        return $this->hasOne(MonitoredEquipment::class, 'equipment_request_id');
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
            'approved' => 'success',
            'pending' => 'warning',
            'declined' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get urgency badge color
     */
    public function getUrgencyBadgeColorAttribute()
    {
        return match($this->urgency_level) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary'
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
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'declined' => 'Declined',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get formatted urgency level
     */
    public function getFormattedUrgencyAttribute()
    {
        return ucfirst($this->urgency_level);
    }

    /**
     * Check if request is pending
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is urgent (high or critical)
     */
    public function getIsUrgentAttribute()
    {
        return in_array($this->urgency_level, ['high', 'critical']);
    }

    /**
     * Check if request is declined
     */
    public function getIsDeclinedAttribute()
    {
        return $this->status === 'declined';
    }

    /**
     * Get days since request was created
     */
    public function getDaysSinceCreatedAttribute()
    {
        return $this->created_at ? $this->created_at->diffInDays(now()) : 0;
    }

    /**
     * Check if request is overdue (based on urgency level)
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $daysSince = $this->days_since_created;
        
        return match($this->urgency_level) {
            'critical' => $daysSince > 1,
            'high' => $daysSince > 2,
            'medium' => $daysSince > 5,
            'low' => $daysSince > 10,
            default => false
        };
    }

    /**
     * ADDED: Check if the requester is indeed a Site Coordinator
     */
    public function getIsFromSiteCoordinatorAttribute()
    {
        return $this->user && $this->user->role === 'sc';
    }

    // ====================================================================
    // SCOPES
    // ====================================================================

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for declined requests
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope for personal use requests
     */
    public function scopePersonalUse($query)
    {
        return $query->where('usage_type', 'personal');
    }

    /**
     * Scope for project site requests
     */
    public function scopeProjectSite($query)
    {
        return $query->where('usage_type', 'project_site');
    }

    /**
     * Scope for urgent requests
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('urgency_level', ['high', 'critical']);
    }

    /**
     * Scope for overdue requests
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->where('urgency_level', 'critical')
                        ->where('created_at', '<', now()->subDay());
                })->orWhere(function($subQ) {
                    $subQ->where('urgency_level', 'high')
                        ->where('created_at', '<', now()->subDays(2));
                })->orWhere(function($subQ) {
                    $subQ->where('urgency_level', 'medium')
                        ->where('created_at', '<', now()->subDays(5));
                })->orWhere(function($subQ) {
                    $subQ->where('urgency_level', 'low')
                        ->where('created_at', '<', now()->subDays(10));
                });
            });
    }

    /**
     * Scope for recent requests
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * ADDED: Scope for Site Coordinator requests only
     */
    public function scopeBySiteCoordinators($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('role', 'sc');
        });
    }

    // ====================================================================
    // METHODS
    // ====================================================================

    /**
     * Get validation rules for equipment requests
     */
    public static function getValidationRules($isUpdate = false)
    {
        $rules = [
            'equipment_name' => 'required|string|max:255',
            'equipment_description' => 'required|string|max:1000',
            'usage_type' => 'required|in:personal,project_site',
            'project_id' => 'nullable|exists:projects,id|required_if:usage_type,project_site',
            'quantity' => 'required|integer|min:1|max:100',
            'estimated_cost' => 'nullable|numeric|min:0|max:999999.99',
            'justification' => 'required|string|max:1000',
            'urgency_level' => 'required|in:low,medium,high,critical',
            'additional_notes' => 'nullable|string|max:1000',
        ];
        
        if ($isUpdate) {
            // For updates, make some fields optional
            $rules['equipment_name'] = 'sometimes|' . $rules['equipment_name'];
            $rules['equipment_description'] = 'sometimes|' . $rules['equipment_description'];
            $rules['usage_type'] = 'sometimes|' . $rules['usage_type'];
            $rules['justification'] = 'sometimes|' . $rules['justification'];
        }
        
        return $rules;
    }
    
    /**
     * Get custom validation messages
     */
    public static function getValidationMessages()
    {
        return [
            'equipment_name.required' => 'Equipment name is required.',
            'equipment_description.required' => 'Equipment description is required.',
            'usage_type.required' => 'Please select a usage type.',
            'project_id.required_if' => 'Project is required for project site equipment.',
            'project_id.exists' => 'Selected project does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity cannot exceed 100.',
            'justification.required' => 'Justification is required.',
            'urgency_level.required' => 'Please select an urgency level.',
            'estimated_cost.min' => 'Estimated cost cannot be negative.',
            'estimated_cost.max' => 'Estimated cost cannot exceed ₱999,999.99.',
        ];
    }

    /**
     * ADDED: Helper method to get the requester's full name
     */
    public function getRequesterNameAttribute()
    {
        return $this->user ? $this->user->full_name : 'Unknown User';
    }

    /**
     * ADDED: Helper method to check if notifications should be sent
     */
    public function shouldSendNotifications()
    {
        return $this->user && 
               $this->user->role === 'sc' && 
               $this->user->status === 'active';
    }
}