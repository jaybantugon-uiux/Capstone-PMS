<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Carbon\Carbon;

class FinancialReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'report_type',
        'report_period_start',
        'report_period_end',
        'project_id',
        'created_by',
        'status',
        'total_expenditures',
        'total_receipts',
        'variance_amount',
        'currency',
        'generated_at',
        'report_data',
        'summary',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'report_period_start' => 'date',
        'report_period_end' => 'date',
        'total_expenditures' => 'decimal:2',
        'total_receipts' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'generated_at' => 'datetime',
        'report_data' => 'array',
        'summary' => 'array',
        'metadata' => 'array'
    ];

    protected $dates = [
        'report_period_start',
        'report_period_end',
        'generated_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    public function liquidatedForm(): HasOne
    {
        return $this->hasOne(LiquidatedForm::class);
    }

    public function expenditures(): HasMany
    {
        return $this->hasMany(DailyExpenditure::class, 'project_id', 'project_id')
                    ->whereBetween('expense_date', [$this->report_period_start, $this->report_period_end]);
    }

    public function receipts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Receipt::class,
            DailyExpenditure::class,
            'project_id', // Foreign key on daily_expenditures table
            'daily_expenditure_id', // Foreign key on receipts table
            'project_id', // Local key on financial_reports table
            'id' // Local key on daily_expenditures table
        )->whereBetween('receipts.receipt_date', [$this->report_period_start, $this->report_period_end]);
    }

    public function directReceipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }



    public function scopeLiquidated($query)
    {
        return $query->where('status', 'liquidated');
    }



    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_period_start', [$startDate, $endDate])
                    ->orWhereBetween('report_period_end', [$startDate, $endDate]);
    }

    public function scopeGeneratedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('generated_at', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedTotalExpendituresAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_expenditures, 2);
    }

    public function getFormattedTotalReceiptsAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->total_receipts, 2);
    }

    public function getFormattedVarianceAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->variance_amount, 2);
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->report_period_start->format('M d, Y') . ' - ' . $this->report_period_end->format('M d, Y');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'generated' => 'info',
            'liquidated' => 'primary',
            default => 'secondary'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status_badge_color;
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getVariancePercentageAttribute(): float
    {
        if ($this->total_expenditures == 0) return 0;
        return ($this->variance_amount / $this->total_expenditures) * 100;
    }

    public function getIsVariancePositiveAttribute(): bool
    {
        return $this->variance_amount >= 0;
    }

    public function getDaysSinceGenerationAttribute(): int
    {
        if (!$this->generated_at) return 0;
        return $this->generated_at->diffInDays(now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'generated' && 
               $this->generated_at && 
               $this->generated_at->diffInDays(now()) > 14;
    }

    // Methods
    public function canBeGenerated(): bool
    {
        return $this->status === 'draft';
    }



    public function canBeLiquidated(): bool
    {
        return $this->status === 'generated';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'generated']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    public function generate(): bool
    {
        if (!$this->canBeGenerated()) {
            return false;
        }

        // Calculate totals from related expenditures and receipts
        $this->calculateTotals();

        $this->update([
            'status' => 'generated',
            'generated_at' => now()
        ]);

        // Automatically create liquidated form when report is generated
        $this->createLiquidatedForm();

        return true;
    }



    public function liquidate(): bool
    {
        if (!$this->canBeLiquidated()) {
            return false;
        }

        $this->update([
            'status' => 'liquidated'
        ]);

        return true;
    }

    public function calculateTotals(): void
    {
        $totalExpenditures = $this->expenditures()->where('daily_expenditures.status', 'approved')->sum('daily_expenditures.amount');
        $totalReceipts = $this->receipts()->where('receipts.status', 'verified')->sum('receipts.amount');
        $variance = $totalExpenditures - $totalReceipts;

        $this->update([
            'total_expenditures' => $totalExpenditures,
            'total_receipts' => $totalReceipts,
            'variance_amount' => $variance
        ]);
    }

    public function createLiquidatedForm(): ?LiquidatedForm
    {
        if ($this->status !== 'generated') {
            return null;
        }

        $liquidatedForm = LiquidatedForm::create([
            'financial_report_id' => $this->id,
            'form_number' => LiquidatedForm::generateFormNumber(),
            'title' => 'Liquidated Form - ' . $this->title,
            'description' => 'Liquidated form for financial report: ' . $this->title,
            'project_id' => $this->project_id,
            'prepared_by' => $this->created_by,
            'liquidation_date' => now()->toDateString(),
            'period_covered_start' => $this->report_period_start,
            'period_covered_end' => $this->report_period_end,
            'total_amount' => $this->total_expenditures,
            'total_receipts' => $this->total_receipts,
            'variance_amount' => $this->variance_amount,
            'status' => 'pending'
        ]);

        // Link approved expenditures to the liquidated form
        $approvedExpenditures = $this->expenditures()->where('status', 'approved')->get();
        foreach ($approvedExpenditures as $expenditure) {
            $liquidatedForm->expenditures()->attach($expenditure->id, [
                'amount_allocated' => $expenditure->amount,
                'notes' => 'Auto-allocated from financial report'
            ]);
        }

        return $liquidatedForm;
    }

    // Static methods
    public static function getReportTypeOptions(): array
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'annual' => 'Annual',
            'project_summary' => 'Project Summary',
            'custom' => 'Custom'
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'generated' => 'Generated',
            'liquidated' => 'Liquidated'
        ];
    }

    public static function getCurrencyOptions(): array
    {
        return [
            'PHP' => 'Philippine Peso (₱)',
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'JPY' => 'Japanese Yen (¥)'
        ];
    }
}
