<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class LiquidatedForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'financial_report_id',
        'form_number',
        'title',
        'description',
        'project_id',
        'prepared_by',
        'reviewed_by',
        'liquidation_date',
        'period_covered_start',
        'period_covered_end',
        'total_amount',
        'total_receipts',
        'variance_amount',
        'status',
        'flagged_at',
        'flagged_by',
        'flag_reason',
        'flag_priority',
        'printed_at',
        'printed_by',
        'clarification_requested_by',
        'clarification_requested_at',
        'clarification_notes',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'liquidation_date' => 'date',
        'period_covered_start' => 'date',
        'period_covered_end' => 'date',
        'total_amount' => 'decimal:2',
        'total_receipts' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'flag_priority' => 'string',
        'flagged_at' => 'datetime',
        'printed_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'liquidation_date',
        'period_covered_start',
        'period_covered_end',
        'flagged_at',
        'printed_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function financialReport(): BelongsTo
    {
        return $this->belongsTo(FinancialReport::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function flaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function clarificationRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clarification_requested_by');
    }

    public function expenditures(): BelongsToMany
    {
        return $this->belongsToMany(DailyExpenditure::class, 'liquidated_form_expenditures')
                    ->withPivot('amount_allocated', 'notes')
                    ->withTimestamps();
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function linkedReceipts(): HasMany
    {
        return $this->hasMany(Receipt::class)->whereNotNull('financial_report_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(LiquidatedFormRevision::class);
    }

    // Scopes
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByPreparer($query, $userId)
    {
        return $query->where('prepared_by', $userId);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_covered_start', [$startDate, $endDate])
                    ->orWhereBetween('period_covered_end', [$startDate, $endDate]);
    }

    public function scopeByLiquidationDate($query, $startDate, $endDate)
    {
        return $query->whereBetween('liquidation_date', [$startDate, $endDate]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFlagged($query)
    {
        return $query->where('status', 'flagged');
    }

    public function scopeCanBeRevised($query)
    {
        return $query->whereIn('status', ['pending', 'flagged']);
    }

    public function scopePrinted($query)
    {
        return $query->whereNotNull('printed_at');
    }

    public function scopeNotPrinted($query)
    {
        return $query->whereNull('printed_at');
    }

    // Accessors
    public function getFormattedTotalAmountAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getFormattedTotalReceiptsAttribute(): string
    {
        return '₱' . number_format($this->total_receipts, 2);
    }

    public function getFormattedVarianceAmountAttribute(): string
    {
        return '₱' . number_format($this->variance_amount, 2);
    }

    public function getFormattedLiquidationDateAttribute(): string
    {
        return $this->liquidation_date->format('M d, Y');
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->period_covered_start->format('M d, Y') . ' - ' . $this->period_covered_end->format('M d, Y');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'flagged' => 'danger',
            'revision_requested' => 'info',
            'clarification_requested' => 'primary',
            default => 'secondary'
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getVariancePercentageAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->variance_amount / $this->total_amount) * 100;
    }

    public function getIsVariancePositiveAttribute(): bool
    {
        return $this->variance_amount >= 0;
    }

    public function getDaysSinceCreationAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getDaysSinceFlaggedAttribute(): int
    {
        if (!$this->flagged_at) return 0;
        return $this->flagged_at->diffInDays(now());
    }

    public function getIsPrintedAttribute(): bool
    {
        return !is_null($this->printed_at);
    }

    public function getIsFlaggedAttribute(): bool
    {
        return $this->status === 'flagged';
    }

    public function getExpendituresCountAttribute(): int
    {
        return $this->expenditures()->count();
    }

    public function getReceiptsCountAttribute(): int
    {
        return $this->receipts()->count();
    }

    public function getRevisionsCountAttribute(): int
    {
        return $this->revisions()->count();
    }

    public function getReceiptsCoverageAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->total_receipts / $this->total_amount) * 100;
    }

    public function getFlagPriorityColorAttribute(): string
    {
        $colors = [
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger'
        ];

        return $colors[$this->flag_priority ?? 'medium'] ?? 'warning';
    }

    // Methods
    public function canBePrinted(): bool
    {
        return true; // Any form can be printed now
    }

    public function canBeEdited(): bool
    {
        return true; // Any form can be edited now
    }

    public function canBeDeleted(): bool
    {
        return true; // Any form can be deleted now
    }

    public function canBeFlagged(): bool
    {
        // Only allow flagging of pending forms
        // When unflagged, forms go back to pending status
        return $this->status === 'pending';
    }



    public function canRequestRevision(): bool
    {
        // Admin can request revision for pending or flagged forms
        return in_array($this->status, ['pending', 'flagged']);
    }

    public function flag(User $flagger, string $reason, string $priority = 'medium', string $notes = null): bool
    {
        if (!$this->canBeFlagged()) {
            return false;
        }

        $this->update([
            'status' => 'flagged',
            'flagged_by' => $flagger->id,
            'flagged_at' => now(),
            'flag_reason' => $reason,
            'flag_priority' => $priority,
            'notes' => $notes ? ($this->notes ? $this->notes . "\n\nFlag Notes: " . $notes : "Flag Notes: " . $notes) : $this->notes
        ]);

        return true;
    }

    public function unflag(): bool
    {
        \Log::info('=== MODEL UNFLAG METHOD ===');
        \Log::info('Form ID: ' . $this->id);
        \Log::info('Form Number: ' . $this->form_number);
        \Log::info('Current Status: ' . $this->status);
        
        if ($this->status !== 'flagged') {
            \Log::warning('Cannot unflag - Form is not flagged. Current status: ' . $this->status);
            return false;
        }

        try {
            \Log::info('Updating form status to pending...');
            
            // Just update the status - the trigger will handle clearing flag-related fields
            $this->update(['status' => 'pending']);
            
            \Log::info('Form updated successfully');
            \Log::info('New status: ' . $this->status);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error updating form during unflag: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function markPrinted(User $printer): bool
    {
        $this->update([
            'printed_at' => now(),
            'printed_by' => $printer->id
        ]);

        return true;
    }





    public function addExpenditure(DailyExpenditure $expenditure, float $amountAllocated = null, string $notes = null): bool
    {
        $amount = $amountAllocated ?? $expenditure->amount;
        
        $this->expenditures()->attach($expenditure->id, [
            'amount_allocated' => $amount,
            'notes' => $notes
        ]);

        // Recalculate totals
        $this->recalculateTotals();

        return true;
    }

    public function removeExpenditure(DailyExpenditure $expenditure): bool
    {
        $this->expenditures()->detach($expenditure->id);
        
        // Recalculate totals
        $this->recalculateTotals();

        return true;
    }

    public function recalculateTotals(): void
    {
        $totalAmount = $this->expenditures()->sum('liquidated_form_expenditures.amount_allocated');
        $totalReceipts = $this->receipts()->where('status', 'verified')->sum('amount');
        $variance = $totalAmount - $totalReceipts;

        $this->update([
            'total_amount' => $totalAmount,
            'total_receipts' => $totalReceipts,
            'variance_amount' => $variance
        ]);
    }

    // Static methods
    public static function generateFormNumber(): string
    {
        $prefix = 'LF';
        $year = date('Y');
        $month = date('m');
        
        $lastForm = self::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->orderBy('id', 'desc')
                        ->first();
        
        $sequence = $lastForm ? (intval(substr($lastForm->id, -4)) + 1) : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'flagged' => 'Flagged',
            'revision_requested' => 'Revision Requested',

        ];
    }

    // Admin-specific methods
    public function canApproveRevision(): bool
    {
        return $this->status === 'revision_requested' && 
               $this->revisions()->where('status', 'pending')->exists();
    }



    public function approveRevision(User $approver, string $notes = null, float $approvedAmount = null): bool
    {
        if (!$this->canApproveRevision()) {
            return false;
        }

        $latestRevision = $this->revisions()->where('status', 'pending')->latest()->first();
        
        if (!$latestRevision) {
            return false;
        }

        $latestRevision->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'approved_amount' => $approvedAmount
        ]);

        $this->update([
            'status' => 'pending',
            'total_amount' => $approvedAmount ?? $this->total_amount
        ]);

        return true;
    }



    public function requestRevision(User $requester, string $reason, string $notes = null): bool
    {
        if (!$this->canRequestRevision()) {
            return false;
        }

        // Create revision record
        $revisionNumber = $this->revisions()->count() + 1;
        $this->revisions()->create([
            'revision_number' => $revisionNumber,
            'requested_by' => $requester->id,
            'reason' => $reason,
            'status' => 'pending',
            'notes' => $notes
        ]);

        // Update form status to revision_requested
        // Note: This will override the flagged status, which is appropriate for admin revision requests
        $this->update(['status' => 'revision_requested']);

        return true;
    }
}
