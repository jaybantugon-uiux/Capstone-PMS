<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePhotoCollectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'photo_id',
        'sort_order',
        'added_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'added_at' => 'datetime',
    ];

    protected $dates = [
        'added_at',
    ];

    // Disable Laravel's automatic timestamps since we're using added_at
    public $timestamps = false;

    // RELATIONSHIPS
    /**
     * Get the collection this item belongs to
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(SitePhotoCollection::class, 'collection_id');
    }

    /**
     * Get the photo in this collection item
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(SitePhoto::class, 'photo_id');
    }

    // ACCESSORS
    /**
     * Get formatted added date
     */
    public function getFormattedAddedAtAttribute()
    {
        return $this->added_at ? $this->added_at->format('M d, Y g:i A') : null;
    }

    // SCOPES
    /**
     * Scope for items in a specific collection
     */
    public function scopeInCollection($query, $collectionId)
    {
        return $query->where('collection_id', $collectionId);
    }

    /**
     * Scope for items ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope for recent items
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('added_at', '>=', now()->subDays($days));
    }

    // HELPER METHODS
    /**
     * Move item up in sort order
     */
    public function moveUp()
    {
        $previousItem = static::where('collection_id', $this->collection_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousItem) {
            $tempOrder = $this->sort_order;
            $this->update(['sort_order' => $previousItem->sort_order]);
            $previousItem->update(['sort_order' => $tempOrder]);
        }
    }

    /**
     * Move item down in sort order
     */
    public function moveDown()
    {
        $nextItem = static::where('collection_id', $this->collection_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($nextItem) {
            $tempOrder = $this->sort_order;
            $this->update(['sort_order' => $nextItem->sort_order]);
            $nextItem->update(['sort_order' => $tempOrder]);
        }
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Set added_at timestamp when creating
        static::creating(function ($item) {
            if (!$item->added_at) {
                $item->added_at = now();
            }
        });

        // Update collection photo count when item is created
        static::created(function ($item) {
            $item->collection->updatePhotoCount();
        });

        // Update collection photo count when item is deleted
        static::deleted(function ($item) {
            if ($item->collection) {
                $item->collection->updatePhotoCount();
                $item->collection->updateCoverPhotoIfNeeded();
            }
        });
    }
}