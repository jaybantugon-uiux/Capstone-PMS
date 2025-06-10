<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'archived',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
     public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}