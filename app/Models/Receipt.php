<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Receipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'liquidated_form_id',
        'financial_report_id',
        'uploaded_by',
        'file_name',
        'original_file_name',
        'file_path',
        'file_size',
        'file_type',
        'receipt_number',
        'receipt_date',
        'vendor_name',
        'amount',
        'tax_amount',
        'description',
        'receipt_type',
        'status',
        'notes',
        'metadata',
        'clarification_requested_by',
        'clarification_requested_at',
        'clarification_notes',
        'clarification_status'
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'clarification_requested_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'file_size' => 'integer',
        'metadata' => 'array'
    ];

    protected $dates = [
        'receipt_date',
        'clarification_requested_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];



    public function liquidatedForm(): BelongsTo
    {
        return $this->belongsTo(LiquidatedForm::class);
    }

    public function financialReport(): BelongsTo
    {
        return $this->belongsTo(FinancialReport::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function clarificationRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clarification_requested_by');
    }



    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByUploader($query, $userId)
    {
        return $query->where('uploaded_by', $userId);
    }



    public function scopeByFinancialReport($query, $financialReportId)
    {
        return $query->where('financial_report_id', $financialReportId);
    }

    public function scopeByLiquidatedForm($query, $liquidatedFormId)
    {
        return $query->where('liquidated_form_id', $liquidatedFormId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('receipt_date', [$startDate, $endDate]);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('receipt_type', $type);
    }

    public function scopeByVendor($query, $vendorName)
    {
        return $query->where('vendor_name', 'like', '%' . $vendorName . '%');
    }

    public function scopeByAmountRange($query, $minAmount, $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return '₱' . number_format($this->tax_amount, 2);
    }

    public function getFormattedReceiptDateAttribute(): string
    {
        return $this->receipt_date->format('M d, Y');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'active' => 'success',
            'archived' => 'secondary',
            default => 'secondary'
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_file_name, PATHINFO_EXTENSION);
    }

    public function getIsImageAttribute(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($this->file_extension), $imageExtensions);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    public function getDaysSinceUploadAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->days_since_upload > 7;
    }

    public function getTotalAmountWithTaxAttribute(): float
    {
        return $this->amount + $this->tax_amount;
    }

    public function getFormattedTotalAmountWithTaxAttribute(): string
    {
        return '₱' . number_format($this->total_amount_with_tax, 2);
    }

    // Methods
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'active']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'pending';
    }



    public function matchToFinancialReport(FinancialReport $financialReport): bool
    {
        if ($this->financial_report_id) {
            return false; // Already matched
        }

        $this->update([
            'financial_report_id' => $financialReport->id
        ]);

        return true;
    }

    public function isMatched(): bool
    {
        return !is_null($this->financial_report_id);
    }

    public function isOrphaned(): bool
    {
        return !$this->isMatched() && $this->status === 'active';
    }



    // Static methods
    public static function getReceiptTypeOptions(): array
    {
        return [
            'official_receipt' => 'Official Receipt',
            'sales_invoice' => 'Sales Invoice',
            'delivery_receipt' => 'Delivery Receipt',
            'payment_voucher' => 'Payment Voucher',
            'cash_receipt' => 'Cash Receipt',
            'bank_deposit_slip' => 'Bank Deposit Slip',
            'other' => 'Other'
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'archived' => 'Archived'
        ];
    }

    public static function getStatusBadgeColor(string $status): string
    {
        return match($status) {
            'pending' => 'warning',
            'active' => 'success',
            'archived' => 'secondary',
            default => 'secondary'
        };
    }

    public static function generateFileName(string $originalName, int $userId): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $timestamp = now()->format('Ymd_His');
        $randomString = substr(md5(uniqid()), 0, 8);
        
        return "receipt_{$userId}_{$timestamp}_{$randomString}.{$extension}";
    }





    // Clarification methods
    public function canRequestClarification(): bool
    {
        return $this->status === 'pending' && $this->clarification_status === 'none';
    }

    public function requestClarification(User $requester, string $notes): bool
    {
        if (!$this->canRequestClarification()) {
            return false;
        }

        $this->update([
            'clarification_requested_by' => $requester->id,
            'clarification_requested_at' => now(),
            'clarification_notes' => $notes,
            'clarification_status' => 'requested'
        ]);

        return true;
    }

    public function resolveClarification(User $resolver, string $notes = null): bool
    {
        if ($this->clarification_status !== 'requested') {
            return false;
        }

        $this->update([
            'clarification_status' => 'resolved',
            'notes' => $notes ? ($this->notes ? $this->notes . "\n\nClarification resolved: " . $notes : "Clarification resolved: " . $notes) : $this->notes
        ]);

        return true;
    }

    public function getClarificationStatusBadgeColorAttribute(): string
    {
        return match($this->clarification_status) {
            'none' => 'secondary',
            'requested' => 'warning',
            'resolved' => 'success',
            default => 'secondary'
        };
    }

    public function getFormattedClarificationStatusAttribute(): string
    {
        return match($this->clarification_status) {
            'none' => 'No Clarification',
            'requested' => 'Clarification Requested',
            'resolved' => 'Clarification Resolved',
            default => 'Unknown'
        };
    }
}
