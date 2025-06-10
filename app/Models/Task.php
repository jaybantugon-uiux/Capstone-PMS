<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name', 'description', 'assigned_to', 'project_id', 'status'
    ];

    // Relationship to the User (Site Coordinator)
    public function siteCoordinator()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relationship to the Project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}