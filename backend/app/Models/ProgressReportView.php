<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressReportView extends Model
{
    use HasFactory;

    protected $fillable = [
        'progress_report_id',
        'client_id',
        'viewed_at',
        'ip_address',
        'user_agent',
        'session_id',
        'view_duration',
        'page_views',
        'engagement_score',
        'referrer',
        'device_type',
        'browser',
        'platform',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'view_duration' => 'integer', // in seconds
        'page_views' => 'integer',
        'engagement_score' => 'decimal:2',
    ];

    /**
     * Get device type from user agent
     */
    public function getDeviceTypeAttribute(): string
    {
        if ($this->attributes['device_type']) {
            return $this->attributes['device_type'];
        }

        if (!$this->user_agent) {
            return 'Unknown';
        }

        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get platform/OS from user agent
     */
    public function getPlatformAttribute(): string
    {
        if ($this->attributes['platform']) {
            return $this->attributes['platform'];
        }

        if (!$this->user_agent) {
            return 'Unknown';
        }

        $userAgent = strtolower($this->user_agent);
        
        if (str_contains($userAgent, 'windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }

    /**
     * Get formatted view duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->view_duration) {
            return 'Quick view';
        }

        $duration = $this->view_duration;
        
        if ($duration < 60) {
            return $duration . ' seconds';
        } elseif ($duration < 3600) {
            return round($duration / 60) . ' minutes';
        } else {
            return round($duration / 3600, 1) . ' hours';
        }
    }

    /**
     * Calculate engagement level based on duration and page views
     */
    public function getEngagementLevelAttribute(): string
    {
        $score = $this->calculateEngagementScore();
        
        if ($score >= 80) {
            return 'High';
        } elseif ($score >= 50) {
            return 'Medium';
        } elseif ($score >= 20) {
            return 'Low';
        } else {
            return 'Minimal';
        }
    }

    /**
     * Calculate engagement score (0-100)
     */
    public function calculateEngagementScore(): float
    {
        if ($this->engagement_score) {
            return $this->engagement_score;
        }

        $score = 0;
        
        // Duration score (0-50 points)
        if ($this->view_duration) {
            if ($this->view_duration >= 300) { // 5+ minutes
                $score += 50;
            } elseif ($this->view_duration >= 120) { // 2+ minutes
                $score += 35;
            } elseif ($this->view_duration >= 60) { // 1+ minute
                $score += 25;
            } elseif ($this->view_duration >= 30) { // 30+ seconds
                $score += 15;
            } else {
                $score += 5;
            }
        }
        
        // Page views score (0-30 points)
        if ($this->page_views) {
            if ($this->page_views >= 3) {
                $score += 30;
            } elseif ($this->page_views >= 2) {
                $score += 20;
            } else {
                $score += 10;
            }
        }
        
        // Bonus points for returning visits (0-20 points)
        $totalViews = static::where('client_id', $this->client_id)
            ->where('progress_report_id', $this->progress_report_id)
            ->count();
            
        if ($totalViews > 1) {
            $score += min(20, $totalViews * 5);
        }
        
        return min(100, $score);
    }

    /**
     * Get color for engagement level
     */
    public function getEngagementColorAttribute(): string
    {
        return match($this->engagement_level) {
            'High' => 'success',
            'Medium' => 'warning',
            'Low' => 'info',
            'Minimal' => 'secondary',
            default => 'light'
        };
    }

    /**
     * Check if this is a returning view
     */
    public function isReturningView(): bool
    {
        return static::where('client_id', $this->client_id)
            ->where('progress_report_id', $this->progress_report_id)
            ->where('viewed_at', '<', $this->viewed_at)
            ->exists();
    }

    /**
     * Get time since view
     */
    public function getTimeSinceViewAttribute(): string
    {
        return $this->viewed_at->diffForHumans();
    }


    public $timestamps = false;

    /**
     * Get the progress report this view belongs to
     */
    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    /**
     * Get the client who viewed the report
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get formatted browser information from user agent
     */
    public function getBrowserInfoAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        // Simple browser detection
        $userAgent = $this->user_agent;
        
        if (str_contains($userAgent, 'Chrome/')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox/')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari/') && !str_contains($userAgent, 'Chrome/')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge/')) {
            return 'Edge';
        } else {
            return 'Unknown Browser';
        }
    }

    /**
     * Scope for views within a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for views by specific device type
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope for views by specific browser
     */
    public function scopeByBrowser($query, $browser)
    {
        return $query->where('browser', $browser);
    }

    /**
     * Scope for engaged views (high engagement score)
     */
    public function scopeEngaged($query, $minScore = 50)
    {
        return $query->where('engagement_score', '>=', $minScore);
    }

    /**
     * Scope for recent views
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for long views (views with significant duration)
     */
    public function scopeLongViews($query, $minDuration = 60)
    {
        return $query->where('view_duration', '>=', $minDuration);
    }

    /**
     * Create a new view record with automatic data extraction
     */
    public static function createView(
        int $progressReportId,
        int $clientId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $sessionId = null,
        ?string $referrer = null
    ): self {
        return static::create([
            'progress_report_id' => $progressReportId,
            'client_id' => $clientId,
            'viewed_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'session_id' => $sessionId,
            'referrer' => $referrer,
            'device_type' => static::extractDeviceType($userAgent),
            'browser' => static::extractBrowser($userAgent),
            'platform' => static::extractPlatform($userAgent),
            'page_views' => 1,
        ]);
    }

    /**
     * Update view with engagement data
     */
    public function updateEngagement(
        ?int $duration = null,
        ?int $pageViews = null,
        ?float $engagementScore = null
    ): bool {
        $data = array_filter([
            'view_duration' => $duration,
            'page_views' => $pageViews,
            'engagement_score' => $engagementScore ?? $this->calculateEngagementScore(),
        ]);

        return $this->update($data);
    }

    /**
     * Extract device type from user agent string
     */
    protected static function extractDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Extract browser from user agent string
     */
    protected static function extractBrowser(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'chrome/') && !str_contains($userAgent, 'edg/')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'firefox/')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'safari/') && !str_contains($userAgent, 'chrome/')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'edg/')) {
            return 'Edge';
        } elseif (str_contains($userAgent, 'opera/') || str_contains($userAgent, 'opr/')) {
            return 'Opera';
        } else {
            return 'Other';
        }
    }

    /**
     * Extract platform from user agent string
     */
    protected static function extractPlatform(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown';
        }

        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'windows')) {
            return 'Windows';
        } elseif (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os')) {
            return 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            return 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            return 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }

    /**
     * Get analytics summary for a progress report
     */
    public static function getAnalyticsSummary(int $progressReportId): array
    {
        $views = static::where('progress_report_id', $progressReportId);
        
        return [
            'total_views' => $views->count(),
            'unique_viewers' => $views->distinct('client_id')->count('client_id'),
            'average_duration' => $views->avg('view_duration') ?? 0,
            'total_duration' => $views->sum('view_duration') ?? 0,
            'average_engagement' => $views->avg('engagement_score') ?? 0,
            'device_breakdown' => $views->groupBy('device_type')
                ->selectRaw('device_type, count(*) as count')
                ->pluck('count', 'device_type')
                ->toArray(),
            'browser_breakdown' => $views->groupBy('browser')
                ->selectRaw('browser, count(*) as count')
                ->pluck('count', 'browser')
                ->toArray(),
            'platform_breakdown' => $views->groupBy('platform')
                ->selectRaw('platform, count(*) as count')
                ->pluck('count', 'platform')
                ->toArray(),
            'engagement_levels' => [
                'high' => $views->where('engagement_score', '>=', 80)->count(),
                'medium' => $views->whereBetween('engagement_score', [50, 79])->count(),
                'low' => $views->whereBetween('engagement_score', [20, 49])->count(),
                'minimal' => $views->where('engagement_score', '<', 20)->count(),
            ],
            'recent_views' => $views->where('viewed_at', '>=', now()->subDays(7))->count(),
            'returning_viewers' => $views->selectRaw('client_id, count(*) as view_count')
                ->groupBy('client_id')
                ->having('view_count', '>', 1)
                ->count(),
        ];
    }

    /**
     * Get top viewers for a progress report
     */
    public static function getTopViewers(int $progressReportId, int $limit = 10): array
    {
        return static::where('progress_report_id', $progressReportId)
            ->with('client:id,first_name,last_name,email')
            ->selectRaw('client_id, count(*) as view_count, max(viewed_at) as last_viewed, avg(view_duration) as avg_duration, avg(engagement_score) as avg_engagement')
            ->groupBy('client_id')
            ->orderByDesc('view_count')
            ->orderByDesc('avg_engagement')
            ->limit($limit)
            ->get()
            ->map(function ($view) {
                return [
                    'client' => $view->client,
                    'view_count' => $view->view_count,
                    'last_viewed' => $view->last_viewed,
                    'avg_duration' => round($view->avg_duration ?? 0),
                    'avg_engagement' => round($view->avg_engagement ?? 0, 1),
                    'engagement_level' => $view->avg_engagement >= 80 ? 'High' : 
                                        ($view->avg_engagement >= 50 ? 'Medium' : 
                                        ($view->avg_engagement >= 20 ? 'Low' : 'Minimal')),
                ];
            })
            ->toArray();
    }

    /**
     * Boot method to set up model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($view) {
            // Auto-calculate engagement score if not provided
            if (!$view->engagement_score) {
                $view->engagement_score = $view->calculateEngagementScore();
            }
        });

        static::updating(function ($view) {
            // Recalculate engagement score if duration or page views changed
            if ($view->isDirty(['view_duration', 'page_views']) && !$view->isDirty('engagement_score')) {
                $view->engagement_score = $view->calculateEngagementScore();
            }
        });
    }
}