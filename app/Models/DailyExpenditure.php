<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class DailyExpenditure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'submitted_by',
        'expense_date',
        'category',
        'description',
        'amount',
        'location',
        'vendor_supplier',
        'payment_method',
        'reference_number',
        'notes',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'metadata'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array'
    ];

    protected $dates = [
        'expense_date',
        'submitted_at',
        'approved_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function liquidatedForms(): BelongsToMany
    {
        return $this->belongsToMany(LiquidatedForm::class, 'liquidated_form_expenditures')
                    ->withPivot('amount_allocated', 'notes')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted'); // Changed from 'pending' to 'submitted'
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeBySubmitter($query, $userId)
    {
        return $query->where('submitted_by', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSubmittedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('submitted_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getFormattedExpenseDateAttribute(): string
    {
        return $this->expense_date->format('M d, Y');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    public function getHasReceiptsAttribute(): bool
    {
        return $this->receipts()->exists();
    }

    public function getReceiptsCountAttribute(): int
    {
        return $this->receipts()->count();
    }

    public function getTotalReceiptsAmountAttribute(): float
    {
        return $this->receipts()->sum('amount');
    }

    public function getReceiptsCoverageAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return ($this->total_receipts_amount / $this->amount) * 100;
    }

    // Methods
    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }



    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'submitted']); // Changed from 'pending' to 'submitted'
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    public function submit(): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        $this->update([
            'status' => 'submitted', // Changed from 'pending' to 'submitted' to indicate no approval needed
            'submitted_at' => now()
        ]);

        return true;
    }



    public function isOverdue(): bool
    {
        return $this->status === 'submitted' && 
               $this->submitted_at && 
               $this->submitted_at->diffInDays(now()) > 7;
    }

    public function getDaysSinceSubmissionAttribute(): int
    {
        if (!$this->submitted_at) return 0;
        return $this->submitted_at->diffInDays(now());
    }

    public function getIsLiquidatedAttribute(): bool
    {
        return $this->liquidatedForms()->exists();
    }

    public function getLiquidatedAmountAttribute(): float
    {
        return $this->liquidatedForms()->sum('liquidated_form_expenditures.amount_allocated');
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->liquidated_amount;
    }

    public function getFormattedSubmittedDateAttribute(): string
    {
        return $this->submitted_at ? $this->submitted_at->format('M d, Y') : 'N/A';
    }

    public function getFormattedCategoryAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getFormattedPaymentMethodAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    // Static methods
    public static function generateFormNumber(): string
    {
        $prefix = 'EXP';
        $year = date('Y');
        $month = date('m');
        
        $lastExpenditure = self::whereYear('created_at', $year)
                               ->whereMonth('created_at', $month)
                               ->orderBy('id', 'desc')
                               ->first();
        
        $sequence = $lastExpenditure ? (intval(substr($lastExpenditure->id, -4)) + 1) : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    public static function getCategoryOptions(): array
    {
        return [
            'materials' => 'Materials',
            'labor' => 'Labor',
            'equipment' => 'Equipment',
            'transportation' => 'Transportation',
            'utilities' => 'Utilities',
            'professional_services' => 'Professional Services',
            'miscellaneous' => 'Miscellaneous'
        ];
    }

    public static function getPaymentMethodOptions(): array
    {
        return [
            'cash' => 'Cash',
            'check' => 'Check',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'petty_cash' => 'Petty Cash',
            'company_card' => 'Company Card'
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];
    }
}
