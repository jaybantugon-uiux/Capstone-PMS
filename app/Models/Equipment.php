<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'name', 
        'description', 
        'stock', 
        'min_stock_level',
        'archived'
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock_level' => 'integer',
        'archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'stock' => 0,
        'min_stock_level' => 10,
        'archived' => false,
    ];

    // Validation rules
    public static function validationRules($isUpdate = false, $equipmentId = null)
    {
        $uniqueRule = $isUpdate && $equipmentId 
            ? "unique:equipment,name,{$equipmentId}" 
            : 'unique:equipment,name';
            
        return [
            'name' => "required|string|max:255|{$uniqueRule}",
            'description' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0|max:10000',
            'min_stock_level' => 'nullable|integer|min:0|max:1000',
        ];
    }

    // Relationship with stock logs
    public function stockLogs()
    {
        return $this->hasMany(EquipmentStockLog::class);
    }

    // Get recent stock logs
    public function recentLogs($limit = 10)
    {
        return $this->stockLogs()
            ->with('user:id,first_name,last_name,username')
            ->latest()
            ->limit($limit);
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->stock <= ($this->min_stock_level ?? 10);
    }

    // Get total stock changes
    public function getTotalRestockedAttribute()
    {
        return $this->stockLogs()->where('change', '>', 0)->sum('change');
    }

    public function getTotalUsedAttribute()
    {
        return abs($this->stockLogs()->where('change', '<', 0)->sum('change'));
    }

    // Scopes for filtering
    public function scopeActive(Builder $query)
    {
        return $query->where('archived', false);
    }

    public function scopeArchived(Builder $query)
    {
        return $query->where('archived', true);
    }

    public function scopeLowStock(Builder $query, $threshold = null)
    {
        $threshold = $threshold ?? 10;
        return $query->where(function($q) use ($threshold) {
            $q->where('stock', '<=', $threshold)
              ->orWhereRaw('stock <= COALESCE(min_stock_level, 10)');
        });
    }

    public function scopeInStock(Builder $query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeOutOfStock(Builder $query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeByName(Builder $query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    // Get stock status
    public function getStockStatusAttribute()
    {
        if ($this->archived) {
            return 'archived';
        } elseif ($this->stock <= 0) {
            return 'out-of-stock';
        } elseif ($this->isLowStock()) {
            return 'low-stock';
        } else {
            return 'in-stock';
        }
    }

    // Get stock status color for UI
    public function getStockStatusColorAttribute()
    {
        switch ($this->stock_status) {
            case 'archived':
                return 'secondary';
            case 'out-of-stock':
                return 'danger';
            case 'low-stock':
                return 'warning';
            default:
                return 'success';
        }
    }

    // Get stock status badge
    public function getStockBadgeAttribute()
    {
        switch ($this->stock_status) {
            case 'archived':
                return '<span class="badge bg-secondary"><i class="fas fa-archive me-1"></i>Archived</span>';
            case 'out-of-stock':
                return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Out of Stock</span>';
            case 'low-stock':
                return '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle me-1"></i>Low Stock</span>';
            default:
                return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>In Stock</span>';
        }
    }

    // Check if can use/withdraw stock
    public function canUse($amount)
    {
        return !$this->archived && $this->stock >= $amount;
    }

    // Check if can restock (within limits)
    public function canRestock($amount)
    {
        return !$this->archived && ($this->stock + $amount) <= 10000;
    }

    // Check if can be modified
    public function canModify()
    {
        return !$this->archived;
    }

    // Archive equipment
    public function archive()
    {
        $this->update(['archived' => true]);
        return $this;
    }

    // Restore equipment
    public function restore()
    {
        $this->update(['archived' => false]);
        return $this;
    }

    // Safely increment stock
    public function safeIncrement($amount, $note = null, $user_id = null)
    {
        if (!$this->canRestock($amount)) {
            throw new \Exception('Cannot restock: equipment is archived or would exceed maximum stock limit.');
        }

        $this->increment('stock', $amount);

        if ($user_id) {
            $this->stockLogs()->create([
                'user_id' => $user_id,
                'change' => $amount,
                'note' => $note ?: 'Stock incremented',
            ]);
        }

        return $this;
    }

    // Safely decrement stock
    public function safeDecrement($amount, $note = null, $user_id = null)
    {
        if (!$this->canUse($amount)) {
            throw new \Exception('Cannot use: equipment is archived or insufficient stock available.');
        }

        $this->decrement('stock', $amount);

        if ($user_id) {
            $this->stockLogs()->create([
                'user_id' => $user_id,
                'change' => -$amount,
                'note' => $note ?: 'Stock decremented',
            ]);
        }

        return $this;
    }
}