<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidatedFormRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'liquidated_form_id',
        'revision_number',
        'requested_by',
        'reason',
        'status',
        'addressed_by',
        'addressed_at',
        'response',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'revision_number' => 'integer',
        'addressed_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'addressed_at',
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function liquidatedForm(): BelongsTo
    {
        return $this->belongsTo(LiquidatedForm::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function addressedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAddressed($query)
    {
        return $query->where('status', 'addressed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByForm($query, $formId)
    {
        return $query->where('liquidated_form_id', $formId);
    }

    public function scopeByRequester($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    public function scopeByAddressedBy($query, $userId)
    {
        return $query->where('addressed_by', $userId);
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'addressed' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getDaysSinceRequestAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getDaysSinceAddressedAttribute(): int
    {
        if (!$this->addressed_at) return 0;
        return $this->addressed_at->diffInDays(now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->days_since_request > 7;
    }

    // Methods
    public function canBeAddressed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }

    public function address(User $addressedBy, string $response, string $notes = null): bool
    {
        if (!$this->canBeAddressed()) {
            return false;
        }

        $this->update([
            'status' => 'addressed',
            'addressed_by' => $addressedBy->id,
            'addressed_at' => now(),
            'response' => $response,
            'notes' => $notes
        ]);

        return true;
    }

    public function reject(User $addressedBy, string $notes = null): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'addressed_by' => $addressedBy->id,
            'addressed_at' => now(),
            'notes' => $notes
        ]);

        return true;
    }

    // Static methods
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'addressed' => 'Addressed',
            'rejected' => 'Rejected'
        ];
    }
}
