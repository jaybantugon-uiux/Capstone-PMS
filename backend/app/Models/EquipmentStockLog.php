<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EquipmentStockLog extends Model
{
    use HasFactory;

    protected $fillable = ['equipment_id', 'user_id', 'change', 'note'];

    protected $casts = [
        'change' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRestocks(Builder $query)
    {
        return $query->where('change', '>', 0);
    }

    public function scopeUsage(Builder $query)
    {
        return $query->where('change', '<', 0);
    }

    public function scopeForEquipment(Builder $query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId);
    }

    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getTypeAttribute()
    {
        return $this->change > 0 ? 'restock' : 'usage';
    }

    public function getAbsoluteChangeAttribute()
    {
        return abs($this->change);
    }

    public function getFormattedChangeAttribute()
    {
        return $this->change > 0 ? '+' . $this->change : $this->change;
    }
}