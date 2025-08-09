<?php

namespace App\Policies;

use App\Models\SitePhoto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePhotoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any site photos.
     */
    public function viewAny(User $user)
    {
        // Admins, PMs, and SCs can view site photos
        return in_array($user->role, ['admin', 'pm', 'sc']);
    }

    /**
     * Determine whether the user can view the site photo.
     */
    public function view(User $user, SitePhoto $sitePhoto)
    {
        // Admins and PMs can view all photos
        if (in_array($user->role, ['admin', 'pm'])) {
            return true;
        }

        // Site coordinators can view their own photos
        if ($user->role === 'sc' && $sitePhoto->user_id === $user->id) {
            return true;
        }

        // Site coordinators can view approved photos from projects they're involved in
        if ($user->role === 'sc' && $sitePhoto->submission_status === 'approved') {
            return $user->tasks()->whereHas('project', function($query) use ($sitePhoto) {
                $query->where('id', $sitePhoto->project_id);
            })->exists();
        }

        // Clients can view public, approved photos from their projects
        if ($user->role === 'client' && $sitePhoto->is_public && $sitePhoto->submission_status === 'approved') {
            // This would need project-client relationship implementation
            return true; // Simplified for now
        }

        return false;
    }

    /**
     * Determine whether the user can create site photos.
     */
    public function create(User $user)
    {
        // Only site coordinators can upload photos
        return $user->role === 'sc';
    }

    /**
     * Determine whether the user can update the site photo.
     */
    public function update(User $user, SitePhoto $sitePhoto)
    {
        // Admins can always update
        if ($user->role === 'admin') {
            return true;
        }

        // Site coordinators can update their own photos if not yet reviewed or if rejected
        if ($user->role === 'sc' && $sitePhoto->user_id === $user->id) {
            return in_array($sitePhoto->submission_status, ['draft', 'rejected']);
        }

        // PMs can update for review purposes
        if ($user->role === 'pm') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the site photo.
     */
    public function delete(User $user, SitePhoto $sitePhoto)
{
    // Admins can always delete (with confirmation for approved photos)
    if ($user->role === 'admin') {
        return true;
    }
    
    // PMs can delete photos in their projects
    if ($user->role === 'pm') {
        // Check if PM has access to the project
        $hasProjectAccess = $user->managedProjects()->where('id', $sitePhoto->project_id)->exists();
        return $hasProjectAccess;
    }

    // Site coordinators can delete their own photos if not yet submitted or if rejected
    if ($user->role === 'sc' && $sitePhoto->user_id === $user->id) {
        return in_array($sitePhoto->submission_status, ['draft', 'rejected']);
    }

    return false;
}

public function forceDelete(User $user, SitePhoto $sitePhoto)
{
    // Only super admins can force delete
    return $user->role === 'admin' && $user->hasPermission('force_delete_photos');
}

/**
 * Determine whether the user can perform bulk delete operations
 */
public function bulkDelete(User $user)
{
    // Only admins and PMs can bulk delete
    return in_array($user->role, ['admin', 'pm']);
}

/**
 * Determine whether the user can view deletion impact/safety check
 */
public function viewDeletionImpact(User $user, SitePhoto $sitePhoto)
{
    // Users who can delete can also view deletion impact
    return $this->delete($user, $sitePhoto);
}

/**
 * Determine whether the user can archive photos (soft delete)
 */
public function archive(User $user, SitePhoto $sitePhoto)
{
    // Admins and PMs can archive photos
    if (in_array($user->role, ['admin', 'pm'])) {
        return true;
    }
    
    // Site coordinators can archive their own photos
    if ($user->role === 'sc' && $sitePhoto->user_id === $user->id) {
        return true;
    }
    
    return false;
}

/**
 * Check if user needs confirmation for deletion
 */
public function needsConfirmationForDeletion(User $user, SitePhoto $sitePhoto)
{
    // Always need confirmation for approved photos
    if ($sitePhoto->submission_status === 'approved') {
        return true;
    }
    
    // Need confirmation if photo is featured
    if ($sitePhoto->is_featured) {
        return true;
    }
    
    // Need confirmation if photo has comments
    if ($sitePhoto->comments()->count() > 0) {
        return true;
    }
    
    // Need confirmation if photo is in collections
    if ($sitePhoto->collections()->count() > 0) {
        return true;
    }
    
    return false;
}

    /**
     * Determine whether the user can review site photos.
     */
    public function review(User $user, SitePhoto $sitePhoto)
    {
        // Only admins and PMs can review photos
        return in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Determine whether the user can feature/unfeature photos.
     */
    public function feature(User $user, SitePhoto $sitePhoto)
    {
        // Only admins and PMs can feature photos
        return in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Determine whether the user can make photos public/private.
     */
    public function changeVisibility(User $user, SitePhoto $sitePhoto)
    {
        // Only admins and PMs can change photo visibility
        return in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Determine whether the user can add comments to the photo.
     */
    public function comment(User $user, SitePhoto $sitePhoto)
    {
        // Users who can view the photo can also comment on it
        return $this->view($user, $sitePhoto);
    }

    /**
     * Determine whether the user can add internal comments.
     */
    public function addInternalComment(User $user, SitePhoto $sitePhoto)
    {
        // Only admins and PMs can add internal comments
        return in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Determine whether the user can export site photos.
     */
    public function export(User $user)
    {
        // Only admins and PMs can export photo data
        return in_array($user->role, ['admin', 'pm']);
    }

    /**
     * Determine whether the user can perform bulk actions.
     */
    public function bulkAction(User $user)
    {
        // Only admins and PMs can perform bulk actions
        return in_array($user->role, ['admin', 'pm']);
    }
}