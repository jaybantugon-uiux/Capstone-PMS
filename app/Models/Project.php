<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'created_by',
        'archived'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'archived' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Accessor for formatted dates
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('M d, Y') : null;
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('M d, Y') : null;
    }

    // Check if project is overdue
    public function getIsOverdueAttribute()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    // Get project status
    public function getStatusAttribute()
    {
        if ($this->archived) {
            return 'archived';
        }

        $totalTasks = $this->tasks()->where('archived', false)->count();
        
        if ($totalTasks === 0) {
            return 'no_tasks';
        }

        $completedTasks = $this->tasks()->where('archived', false)->where('status', 'completed')->count();
        
        if ($completedTasks === $totalTasks) {
            return 'completed';
        }

        $inProgressTasks = $this->tasks()->where('archived', false)->where('status', 'in_progress')->count();
        
        if ($inProgressTasks > 0 || $completedTasks > 0) {
            return 'in_progress';
        }

        return 'pending';
    }

    // Get completion percentage
    public function getCompletionPercentageAttribute()
    {
        $totalTasks = $this->tasks()->where('archived', false)->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('archived', false)->where('status', 'completed')->count();
        
        return round(($completedTasks / $totalTasks) * 100);
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
                return 'secondary';
            case 'archived':
                return 'dark';
            case 'no_tasks':
            default:
                return 'light';
        }
    }

    // Get formatted status
    public function getFormattedStatusAttribute()
    {
        switch ($this->status) {
            case 'no_tasks':
                return 'No Tasks';
            case 'in_progress':
                return 'In Progress';
            default:
                return ucfirst($this->status);
        }
    }

    // Scope for active projects
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    // Scope for archived projects
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    // Scope for overdue projects
    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', Carbon::now())
                    ->where('archived', false);
    }

    // Scope for projects created by a specific user
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}