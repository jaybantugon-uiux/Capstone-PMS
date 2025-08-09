<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'attachment_path',
        'original_filename',
        'file_size',
        'mime_type',
        'created_by',
        'created_by_role',
        'client_id',
        'project_id',
        'status',
        'sent_at',
        'first_viewed_at',
        'view_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'first_viewed_at' => 'datetime',
        'view_count' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the creator (admin or PM) who created this report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Legacy method for backward compatibility - use creator() instead
     * @deprecated Use creator() method instead
     */
    public function admin(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Get the client who receives this report
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the associated project (optional)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all view records for this report
     */
    public function views(): HasMany
    {
        return $this->hasMany(ProgressReportView::class);
    }

    /**
     * Get the recent views (last 30 days)
     */
    public function recentViews()
    {
        return $this->hasMany(ProgressReportView::class)
            ->where('viewed_at', '>=', now()->subDays(30))
            ->orderBy('viewed_at', 'desc');
    }

    /**
     * Check if the report has an attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path) && Storage::exists($this->attachment_path);
    }

    /**
     * Get the attachment URL
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return Storage::url($this->attachment_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the attachment file extension
     */
    public function getFileExtensionAttribute(): ?string
    {
        if (!$this->original_filename) {
            return null;
        }

        return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if attachment is an image
     */
    public function isImage(): bool
    {
        if (!$this->mime_type) {
            return false;
        }

        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the creator is an admin
     */
    public function isCreatedByAdmin(): bool
    {
        return $this->created_by_role === 'admin';
    }

    /**
     * Check if the creator is a project manager
     */
    public function isCreatedByPM(): bool
    {
        return $this->created_by_role === 'pm';
    }

    /**
     * Get formatted creator role
     */
    public function getFormattedCreatorRoleAttribute(): string
    {
        return match($this->created_by_role) {
            'admin' => 'Administrator',
            'pm' => 'Project Manager',
            default => ucfirst($this->created_by_role)
        };
    }

    /**
     * Get creator role badge color
     */
    public function getCreatorRoleBadgeColorAttribute(): string
    {
        return match($this->created_by_role) {
            'admin' => 'danger',
            'pm' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Get the attachment icon class for display
     */
    public function getAttachmentIconAttribute(): string
    {
        if ($this->isImage()) {
            return 'fas fa-image text-success';
        } elseif ($this->isPdf()) {
            return 'fas fa-file-pdf text-danger';
        } elseif (str_starts_with($this->mime_type ?? '', 'application/')) {
            return 'fas fa-file-alt text-primary';
        } else {
            return 'fas fa-file text-secondary';
        }
    }

    /**
     * Mark the report as viewed by the client
     */
    public function markAsViewed(User $client, string $ipAddress = null, string $userAgent = null): void
    {
        // Create view record
        $this->views()->create([
            'client_id' => $client->id,
            'viewed_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Update status if this is the first view
        if ($this->status === 'sent') {
            $this->update(['status' => 'viewed']);
        }
    }

    /**
     * Get formatted status for display
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'sent' => 'Sent',
            'viewed' => 'Viewed',
            'archived' => 'Archived',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'viewed' => 'success',
            'sent' => 'warning',
            'archived' => 'secondary',
            'draft' => 'info',
            default => 'primary'
        };
    }

    /**
     * Get days since sent
     */
    public function getDaysSinceSentAttribute(): ?int
    {
        if (!$this->sent_at) {
            return null;
        }

        return $this->sent_at->diffInDays(now());
    }

    /**
     * Check if report is recent (sent within last 7 days)
     */
    public function isRecent(): bool
    {
        return $this->sent_at && $this->sent_at->isAfter(now()->subDays(7));
    }

    /**
     * Scope for reports visible to a specific client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Check if attachment is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Scope for reports created by a specific user (admin or PM)
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope for reports created by admins
     */
    public function scopeByAdmins($query)
    {
        return $query->where('created_by_role', 'admin');
    }

    /**
     * Scope for reports created by project managers
     */
    public function scopeByPMs($query)
    {
        return $query->where('created_by_role', 'pm');
    }

    /**
     * Scope for reports created by a specific role
     */
    public function scopeByCreatorRole($query, $role)
    {
        return $query->where('created_by_role', $role);
    }

    /**
     * Scope for reports with specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for reports with attachments
     */
    public function scopeWithAttachments($query)
    {
        return $query->whereNotNull('attachment_path');
    }

    /**
     * Delete the report and its attachment
     */
    public function delete()
    {
        // Delete the attachment file if it exists
        if ($this->hasAttachment()) {
            Storage::delete($this->attachment_path);
        }

        return parent::delete();
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            // Set sent_at timestamp when creating if status is sent
            if ($report->status === 'sent' && !$report->sent_at) {
                $report->sent_at = now();
            }
        });

        static::updating(function ($report) {
            // Set sent_at timestamp when status changes to sent
            if ($report->isDirty('status') && $report->status === 'sent' && !$report->sent_at) {
                $report->sent_at = now();
            }
        });
    }
}