<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name',
        'description',
        'assigned_to',
        'created_by',
        'project_id',
        'due_date',
        'status',
        'archived'
    ];

    protected $casts = [
        'due_date' => 'date',
        'archived' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function siteCoordinator()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Accessor for formatted due date
    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('M d, Y') : null;
    }

    // Check if task is overdue
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    // Get status badge color
    public function getStatusBadgeColorAttribute()
    {
        switch ($this->status) {
            case 'completed':
                return 'success';
            case 'in_progress':
                return 'warning';
            case 'pending':
            default:
                return 'secondary';
        }
    }

    // Get formatted status
    public function getFormattedStatusAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    // Get priority based on due date
    public function getPriorityAttribute()
    {
        if (!$this->due_date || $this->status === 'completed') {
            return 'normal';
        }

        $daysUntilDue = Carbon::now()->diffInDays($this->due_date, false);

        if ($daysUntilDue < 0) {
            return 'overdue';
        } elseif ($daysUntilDue <= 3) {
            return 'high';
        } elseif ($daysUntilDue <= 7) {
            return 'medium';
        } else {
            return 'normal';
        }
    }

    // Get priority badge color
    public function getPriorityBadgeColorAttribute()
    {
        switch ($this->priority) {
            case 'overdue':
                return 'danger';
            case 'high':
                return 'warning';
            case 'medium':
                return 'info';
            case 'normal':
            default:
                return 'light';
        }
    }

    // Scope for active tasks
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    // Scope for archived tasks
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    // Scope for overdue tasks
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
                    ->where('status', '!=', 'completed')
                    ->where('archived', false);
    }

    // Scope for tasks by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for tasks assigned to a specific user
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Scope for tasks created by a specific user
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}