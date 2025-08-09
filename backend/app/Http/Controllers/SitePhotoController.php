<?php

namespace App\Http\Controllers;

use App\Models\SitePhoto;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\SitePhotoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Notifications\SitePhotoSubmitted;
use App\Notifications\SitePhotoApproved;
use App\Notifications\SitePhotoRejected;
use App\Notifications\SitePhotoCommentAdded;

class SitePhotoController extends Controller
{
    /**
     * Display a listing of site photos for site coordinators
     */
public function index(Request $request)
    {
        $user = auth()->user();
        
        // Build query for site coordinator's photos
        $query = SitePhoto::where('user_id', $user->id)
            ->with(['project', 'task', 'reviewer']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('submission_status', $request->status);
        }
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('category')) {
            $query->where('photo_category', $request->category);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('photo_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('photo_date', '<=', $request->date_to);
        }
        
        $photos = $query->orderBy('photo_date', 'desc')->paginate(12);
        
        // Get projects for filter dropdown
        $projects = Project::whereHas('tasks', function($q) use ($user) {
            $q->where('assigned_to', $user->id);
        })->orderBy('name')->get();
        
        // Get statistics
        $stats = SitePhoto::getSummaryStats(SitePhoto::where('user_id', $user->id)->get());
        
        return view('site-photos.index', compact('photos', 'projects', 'stats'));
    }

    /**
     * Show the form for creating a new site photo
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Get projects where user has tasks
        $projects = Project::whereHas('tasks', function($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->with('tasks')->orderBy('name')->get();
        
        // If project_id is provided, get tasks for that project
        $selectedProjectId = $request->get('project_id');
        $tasks = collect();
        
        if ($selectedProjectId) {
            $tasks = Task::where('project_id', $selectedProjectId)
                ->where('assigned_to', $user->id)
                ->orderBy('task_name')
                ->get();
        }
        
        return view('site-photos.create', compact('projects', 'tasks', 'selectedProjectId'));
    }

    /**
     * Store a newly created site photo
     */
   public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:51200', // 50MB max
            'photo_date' => 'required|date|before_or_equal:today',
            'location' => 'nullable|string|max:255',
            'weather_conditions' => 'nullable|in:sunny,cloudy,rainy,stormy,windy',
            'photo_category' => 'required|in:progress,quality,safety,equipment,materials,workers,documentation,issues,completion,other',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $user = auth()->user();
        
        // Verify user has access to this project
        $hasAccess = Task::where('project_id', $request->project_id)
            ->where('assigned_to', $user->id)
            ->exists();
            
        if (!$hasAccess) {
            return back()->withErrors(['project_id' => 'You do not have access to this project.']);
        }
        
        // Verify task belongs to project if provided
        if ($request->task_id) {
            $taskBelongsToProject = Task::where('id', $request->task_id)
                ->where('project_id', $request->project_id)
                ->where('assigned_to', $user->id)
                ->exists();
                
            if (!$taskBelongsToProject) {
                return back()->withErrors(['task_id' => 'Selected task does not belong to the project or you do not have access.']);
            }
        }
        
        DB::beginTransaction();
        
        try {
            // Ensure storage directories exist
            $this->ensureStorageDirectoriesExist();
            
            // Handle file upload
            $file = $request->file('photo');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = 'site-photos/originals/' . date('Y/m');
            
            // Store the file using Laravel's storage system
            $storedPath = $file->storeAs($path, $filename, 'public');
            
            if (!$storedPath) {
                throw new \Exception('Failed to store the uploaded file');
            }
            
            // Create thumbnail
            $this->createThumbnail($storedPath);
            
            // Extract image metadata
            $cameraInfo = $this->extractImageMetadata($file);
            
            // Create site photo record
            $sitePhoto = SitePhoto::create([
                'project_id' => $request->project_id,
                'task_id' => $request->task_id,
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'photo_path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'photo_date' => $request->photo_date,
                'location' => $request->location,
                'weather_conditions' => $request->weather_conditions,
                'photo_category' => $request->photo_category,
                'camera_info' => $cameraInfo,
                'tags' => $request->tags ? array_filter($request->tags) : null,
                'submission_status' => 'submitted', // Auto-submit individual photos
                'submitted_at' => now(),
            ]);
            
            // Notify admins and PMs
            $adminsAndPMs = User::where('role', 'admin')
                ->orWhere('role', 'pm')
                ->where('status', 'active')
                ->get();
                
            foreach ($adminsAndPMs as $admin) {
                try {
                    $admin->notify(new SitePhotoSubmitted($sitePhoto));
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification: ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            // Fixed: Use the correct route name for site coordinator
            return redirect()->route('site-photos.show', $sitePhoto)
                ->with('success', 'Photo uploaded and submitted for review successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Site photo upload failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'project_id' => $request->project_id,
                'error' => $e->getTraceAsString()
            ]);
            
            // Clean up uploaded files if they exist
            if (isset($storedPath) && Storage::disk('public')->exists($storedPath)) {
                Storage::disk('public')->delete($storedPath);
            }
            
            return back()->withErrors(['photo' => 'Failed to upload photo: ' . $e->getMessage()])
                ->withInput();
        }
    }
    /**
     * Display the specified site photo
     */
    public function show(SitePhoto $sitePhoto)
    {
        $user = auth()->user();
        
        // Check authorization
        if ($user->role === 'sc' && $sitePhoto->user_id !== $user->id) {
            abort(403);
        }
        
        $sitePhoto->load(['project', 'task', 'uploader', 'reviewer', 'comments.user']);
        
        return view('site-photos.show', compact('sitePhoto'));
    }

    /**
     * Show the form for editing the specified site photo
     */
    public function edit(SitePhoto $sitePhoto)
    {
        $user = auth()->user();
        
        // Check authorization - only photo owner can edit if not reviewed or rejected
        if ($user->id !== $sitePhoto->user_id || 
            !in_array($sitePhoto->submission_status, ['draft', 'rejected'])) {
            abort(403);
        }
        
        // Get projects where user has tasks
        $projects = Project::whereHas('tasks', function($query) use ($user) {
            $query->where('assigned_to', $user->id);
        })->orderBy('name')->get();
        
        // Get tasks for the current project
        $tasks = Task::where('project_id', $sitePhoto->project_id)
            ->where('assigned_to', $user->id)
            ->orderBy('task_name')
            ->get();
        
        return view('site-photos.edit', compact('sitePhoto', 'projects', 'tasks'));
    }

    /**
     * Update the specified site photo
     */
    public function update(Request $request, SitePhoto $sitePhoto)
    {
        $user = auth()->user();
        
        // Check authorization
        if ($user->id !== $sitePhoto->user_id || 
            !in_array($sitePhoto->submission_status, ['draft', 'rejected'])) {
            abort(403);
        }
        
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // 50MB max
            'photo_date' => 'required|date|before_or_equal:today',
            'location' => 'nullable|string|max:255',
            'weather_conditions' => 'nullable|in:sunny,cloudy,rainy,stormy,windy',
            'photo_category' => 'required|in:progress,quality,safety,equipment,materials,workers,documentation,issues,completion,other',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);
        
        DB::beginTransaction();
        
        try {
            $updateData = [
                'project_id' => $request->project_id,
                'task_id' => $request->task_id,
                'title' => $request->title,
                'description' => $request->description,
                'photo_date' => $request->photo_date,
                'location' => $request->location,
                'weather_conditions' => $request->weather_conditions,
                'photo_category' => $request->photo_category,
                'tags' => $request->tags ? array_filter($request->tags) : null,
            ];
            
            // Handle new photo upload if provided
            if ($request->hasFile('photo')) {
                // Ensure storage directories exist
                $this->ensureStorageDirectoriesExist();
                
                // Delete old photo files
                if (Storage::disk('public')->exists($sitePhoto->photo_path)) {
                    Storage::disk('public')->delete($sitePhoto->photo_path);
                }
                
                $thumbnailPath = str_replace('/originals/', '/thumbnails/', $sitePhoto->photo_path);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
                
                // Upload new photo
                $file = $request->file('photo');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = 'site-photos/originals/' . date('Y/m');
                
                $storedPath = $file->storeAs($path, $filename, 'public');
                
                if (!$storedPath) {
                    throw new \Exception('Failed to store the uploaded file');
                }
                
                $this->createThumbnail($storedPath);
                
                $updateData['photo_path'] = $storedPath;
                $updateData['original_filename'] = $file->getClientOriginalName();
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
                $updateData['camera_info'] = $this->extractImageMetadata($file);
            }
            
            // Reset review status and resubmit
            $updateData['submission_status'] = 'submitted';
            $updateData['submitted_at'] = now();
            $updateData['reviewed_by'] = null;
            $updateData['reviewed_at'] = null;
            $updateData['admin_comments'] = null;
            $updateData['admin_rating'] = null;
            $updateData['rejection_reason'] = null;
            
            $sitePhoto->update($updateData);
            
            // Notify admins and PMs
            $adminsAndPMs = User::where('role', 'admin')
                ->orWhere('role', 'pm')
                ->where('status', 'active')
                ->get();
                
            foreach ($adminsAndPMs as $admin) {
                try {
                    $admin->notify(new SitePhotoSubmitted($sitePhoto));
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification: ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return redirect()->route('site-photos.show', $sitePhoto)
                ->with('success', 'Photo updated and resubmitted for review successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Site photo update failed: ' . $e->getMessage());
            
            return back()->withErrors(['photo' => 'Failed to update photo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified site photo
     */
    public function destroy(SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Check authorization
    if ($user->role === 'sc' && $sitePhoto->user_id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Site coordinators can only delete draft or rejected photos
    if ($user->role === 'sc' && !in_array($sitePhoto->submission_status, ['draft', 'rejected'])) {
        if (request()->expectsJson()) {
            return response()->json(['error' => 'Cannot delete photo that has been submitted or approved.'], 422);
        }
        return back()->withErrors(['error' => 'Cannot delete photo that has been submitted or approved.']);
    }
    
    // Admins can delete any photo but with confirmation for approved ones
    if ($user->role === 'admin' && $sitePhoto->submission_status === 'approved' && !request()->has('confirm_delete')) {
        if (request()->expectsJson()) {
            return response()->json(['error' => 'This approved photo requires confirmation to delete.'], 422);
        }
        return back()->withErrors(['error' => 'This approved photo requires confirmation to delete.']);
    }
    
    try {
        DB::beginTransaction();
        
        // Store photo details for response
        $photoTitle = $sitePhoto->title;
        $photoId = $sitePhoto->id;
        
        // Delete photo files from storage
        $this->deletePhotoFiles($sitePhoto);
        
        // Remove from any collections first (to avoid foreign key issues)
        $sitePhoto->collections()->detach();
        
        // Delete photo record (this will cascade delete comments via database constraints)
        $sitePhoto->delete();
        
        DB::commit();
        
        // Log the deletion
        Log::info('Site photo deleted', [
            'photo_id' => $photoId,
            'photo_title' => $photoTitle,
            'deleted_by' => $user->id,
            'user_role' => $user->role
        ]);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully.',
                'redirect' => route('site-photos.index')
            ]);
        }
        
        return redirect()->route('site-photos.index')
            ->with('success', 'Photo "' . $photoTitle . '" deleted successfully.');
            
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Site photo deletion failed: ' . $e->getMessage(), [
            'photo_id' => $sitePhoto->id,
            'user_id' => $user->id,
            'error' => $e->getTraceAsString()
        ]);
        
        if (request()->expectsJson()) {
            return response()->json(['error' => 'Failed to delete photo. Please try again.'], 500);
        }
        
        return back()->withErrors(['error' => 'Failed to delete photo. Please try again.']);
    }
}


    /**
     * Add comment to site photo
     */
    public function addComment(Request $request, SitePhoto $sitePhoto)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $user = auth()->user();
        
        // Check if user can comment on this photo
        if ($user->role === 'sc' && $sitePhoto->user_id !== $user->id) {
            abort(403);
        }
        
        $comment = SitePhotoComment::create([
            'photo_id' => $sitePhoto->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'is_internal' => false, // Site coordinators can only add external comments
        ]);
        
        // Notify relevant users
        if ($user->id !== $sitePhoto->user_id) {
            try {
                $sitePhoto->uploader->notify(new SitePhotoCommentAdded($sitePhoto, $comment, false));
            } catch (\Exception $e) {
                Log::warning('Failed to send comment notification: ' . $e->getMessage());
            }
        }
        
        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Get project tasks via AJAX
     */
    public function getProjectTasks(Request $request)
    {
        $user = auth()->user();
        $projectId = $request->get('project_id');
        
        if (!$projectId) {
            return response()->json([]);
        }
        
        $tasks = Task::where('project_id', $projectId)
            ->where('assigned_to', $user->id)
            ->select('id', 'task_name')
            ->orderBy('task_name')
            ->get();
        
        return response()->json($tasks);
    }

    /**
     * Admin index for managing all site photos
     */
    public function adminIndex(Request $request)
    {
        // Build query for all photos with filters
        $query = SitePhoto::with(['project', 'task', 'uploader', 'reviewer']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('submission_status', $request->status);
        }
        
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('category')) {
            $query->where('photo_category', $request->category);
        }
        
        if ($request->filled('uploader_id')) {
            $query->where('user_id', $request->uploader_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('photo_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('photo_date', '<=', $request->date_to);
        }
        
        $photos = $query->orderBy('submitted_at', 'desc')->paginate(15);
        
        // Get filter options
        $projects = Project::orderBy('name')->get();
        $uploaders = User::where('role', 'sc')->orderBy('first_name')->get();
        
        // Get statistics
        $stats = SitePhoto::getSummaryStats(SitePhoto::all());
        
        return view('admin.site-photos.index', compact('photos', 'projects', 'uploaders', 'stats'));
    }

    /**
     * Admin show photo details
     */
    public function adminShow(SitePhoto $sitePhoto)
    {
        $sitePhoto->load(['project', 'task', 'uploader', 'reviewer', 'comments.user']);
        
        return view('admin.site-photos.show', compact('sitePhoto'));
    }

    /**
     * Update photo review status (Admin/PM only)
     */
    public function updateReview(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Check access for PM
    if ($user->role === 'pm' && !$user->canManageProject($sitePhoto->project_id)) {
        return back()->withErrors(['error' => 'You do not have permission to review this photo.']);
    }
    
    $request->validate([
        'action' => 'required|in:approve,reject',
        'admin_comments' => 'nullable|string|max:1000',
        'admin_rating' => 'nullable|integer|min:1|max:5',
        'rejection_reason' => 'required_if:action,reject|string|max:1000',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
    ]);
    
    if ($sitePhoto->submission_status !== 'submitted') {
        return back()->withErrors(['error' => 'Photo is not in submitted status.']);
    }
    
    DB::beginTransaction();
    
    try {
        if ($request->action === 'approve') {
            $sitePhoto->update([
                'submission_status' => 'approved',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'admin_comments' => $request->admin_comments,
                'admin_rating' => $request->admin_rating,
                'is_featured' => $request->boolean('is_featured'),
                'is_public' => $request->boolean('is_public'),
                'rejection_reason' => null,
            ]);
            
            // Notify uploader
            try {
                $sitePhoto->uploader->notify(new \App\Notifications\SitePhotoApproved($sitePhoto, $user->first_name . ' ' . $user->last_name));
            } catch (\Exception $e) {
                Log::warning('Failed to send approval notification: ' . $e->getMessage());
            }
            $message = 'Photo approved successfully.';
            
        } else {
            $sitePhoto->update([
                'submission_status' => 'rejected',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'rejection_reason' => $request->rejection_reason,
                'admin_comments' => $request->admin_comments,
                'is_featured' => false,
                'is_public' => false,
            ]);
            
            // Notify uploader
            try {
                $sitePhoto->uploader->notify(new \App\Notifications\SitePhotoRejected($sitePhoto, $user->first_name . ' ' . $user->last_name));
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection notification: ' . $e->getMessage());
            }
            $message = 'Photo rejected successfully.';
        }
        
        DB::commit();
        
        return back()->with('success', $message);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Photo review update failed: ' . $e->getMessage());
        
        return back()->withErrors(['error' => 'Failed to update photo review. Please try again.']);
    }
}
    /**
     * Add admin comment to photo
     */
    public function addAdminComment(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Check access for PM
    if ($user->role === 'pm' && !$user->canManageProject($sitePhoto->project_id)) {
        return back()->withErrors(['error' => 'You do not have permission to comment on this photo.']);
    }
    
    $request->validate([
        'comment' => 'required|string|max:1000',
        'is_internal' => 'boolean',
    ]);
    
    $comment = SitePhotoComment::create([
        'photo_id' => $sitePhoto->id,
        'user_id' => $user->id,
        'comment' => $request->comment,
        'is_internal' => $request->boolean('is_internal'),
    ]);
    
    // Notify uploader if external comment
    if (!$request->boolean('is_internal') && $user->id !== $sitePhoto->user_id) {
        try {
            $sitePhoto->uploader->notify(new \App\Notifications\SitePhotoCommentAdded($sitePhoto, $comment, !$request->boolean('is_internal')));
        } catch (\Exception $e) {
            Log::warning('Failed to send comment notification: ' . $e->getMessage());
        }
    }
    
    return back()->with('success', 'Comment added successfully.');
}


    /**
     * Bulk actions for photos (Admin only)
     */
    public function bulkAction(Request $request)
{
    $user = auth()->user();
    
    $request->validate([
        'action' => 'required|in:approve,reject,feature,unfeature,make_public,make_private',
        'photo_ids' => 'required|array',
        'photo_ids.*' => 'exists:site_photos,id',
        'bulk_rejection_reason' => 'required_if:action,reject|string|max:1000',
    ]);
    
    $photoIds = $request->photo_ids;
    $action = $request->action;
    
    // For PM, filter photos to only those from managed projects
    if ($user->role === 'pm') {
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        $validPhotoIds = SitePhoto::whereIn('id', $photoIds)
            ->whereIn('project_id', $managedProjectIds)
            ->pluck('id')
            ->toArray();
        
        if (count($validPhotoIds) !== count($photoIds)) {
            return back()->withErrors(['error' => 'Some photos are not from your managed projects.']);
        }
        
        $photoIds = $validPhotoIds;
    }
    
    DB::beginTransaction();
    
    try {
        $photos = SitePhoto::whereIn('id', $photoIds)->get();
        $processedCount = 0;
        
        foreach ($photos as $photo) {
            switch ($action) {
                case 'approve':
                    if ($photo->submission_status === 'submitted') {
                        $photo->update([
                            'submission_status' => 'approved',
                            'reviewed_by' => $user->id,
                            'reviewed_at' => now(),
                        ]);
                        try {
                            $photo->uploader->notify(new \App\Notifications\SitePhotoApproved($photo, $user->first_name . ' ' . $user->last_name));
                        } catch (\Exception $e) {
                            Log::warning('Failed to send bulk approval notification: ' . $e->getMessage());
                        }
                        $processedCount++;
                    }
                    break;
                    
                case 'reject':
                    if ($photo->submission_status === 'submitted') {
                        $photo->update([
                            'submission_status' => 'rejected',
                            'reviewed_by' => $user->id,
                            'reviewed_at' => now(),
                            'rejection_reason' => $request->bulk_rejection_reason,
                        ]);
                        try {
                            $photo->uploader->notify(new \App\Notifications\SitePhotoRejected($photo, $user->first_name . ' ' . $user->last_name));
                        } catch (\Exception $e) {
                            Log::warning('Failed to send bulk rejection notification: ' . $e->getMessage());
                        }
                        $processedCount++;
                    }
                    break;
                    
                case 'feature':
                    if ($photo->submission_status === 'approved') {
                        $photo->update(['is_featured' => true]);
                        $processedCount++;
                    }
                    break;
                    
                case 'unfeature':
                    $photo->update(['is_featured' => false]);
                    $processedCount++;
                    break;
                    
                case 'make_public':
                    if ($photo->submission_status === 'approved') {
                        $photo->update(['is_public' => true]);
                        $processedCount++;
                    }
                    break;
                    
                case 'make_private':
                    $photo->update(['is_public' => false]);
                    $processedCount++;
                    break;
            }
        }
        
        DB::commit();
        
        return back()->with('success', "Bulk action completed. {$processedCount} photos processed.");
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Bulk photo action failed: ' . $e->getMessage());
        
        return back()->withErrors(['error' => 'Bulk action failed. Please try again.']);
    }
}

    /**
     * Ensure storage directories exist
     */
    private function ensureStorageDirectoriesExist()
    {
        $directories = [
            'site-photos',
            'site-photos/originals',
            'site-photos/thumbnails',
            'site-photos/originals/' . date('Y'),
            'site-photos/originals/' . date('Y/m'),
            'site-photos/thumbnails/' . date('Y'),
            'site-photos/thumbnails/' . date('Y/m'),
        ];
        
        foreach ($directories as $directory) {
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
        }
    }

    /**
     * Create thumbnail for uploaded photo
     */
    private function createThumbnail($originalPath)
    {
        try {
            $thumbnailPath = str_replace('/originals/', '/thumbnails/', $originalPath);
            $thumbnailDir = dirname($thumbnailPath);
            
            // Ensure thumbnail directory exists
            if (!Storage::disk('public')->exists($thumbnailDir)) {
                Storage::disk('public')->makeDirectory($thumbnailDir);
            }
            
            // Get the full path to the original file
            $originalFullPath = storage_path('app/public/' . $originalPath);
            $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);
            
            // Create thumbnail using GD library (simple approach)
            $this->createThumbnailWithGD($originalFullPath, $thumbnailFullPath);
            
        } catch (\Exception $e) {
            Log::warning('Failed to create thumbnail: ' . $e->getMessage());
            // Continue without thumbnail - not critical
        }
    }

    /**
     * Create thumbnail using GD library
     */
    private function createThumbnailWithGD($originalPath, $thumbnailPath, $maxWidth = 300, $maxHeight = 300)
    {
        $imageInfo = getimagesize($originalPath);
        if (!$imageInfo) {
            throw new \Exception('Invalid image file');
        }
        
        $mimeType = $imageInfo['mime'];
        
        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($originalPath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($originalPath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($originalPath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }
        
        if (!$sourceImage) {
            throw new \Exception('Failed to create image resource');
        }
        
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        // Create thumbnail image
        $thumbnailImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnailImage, false);
            imagesavealpha($thumbnailImage, true);
            $transparent = imagecolorallocatealpha($thumbnailImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbnailImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize the image
        imagecopyresampled(
            $thumbnailImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );
        
        // Ensure thumbnail directory exists
        $thumbnailDir = dirname($thumbnailPath);
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        // Save thumbnail based on original type
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbnailImage, $thumbnailPath, 85);
                break;
            case 'image/png':
                imagepng($thumbnailImage, $thumbnailPath, 6);
                break;
            case 'image/gif':
                imagegif($thumbnailImage, $thumbnailPath);
                break;
        }
        
        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnailImage);
    }

    /**
     * Extract image metadata
     */
    private function extractImageMetadata($file)
    {
        $cameraInfo = [];
        
        try {
            $imageInfo = getimagesize($file->getPathname());
            
            if ($imageInfo) {
                $cameraInfo['width'] = $imageInfo[0];
                $cameraInfo['height'] = $imageInfo[1];
                $cameraInfo['mime_type'] = $imageInfo['mime'];
                
                // Extract EXIF data if available
                if (function_exists('exif_read_data') && in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg'])) {
                    $exif = @exif_read_data($file->getPathname());
                    if ($exif) {
                        $cameraInfo['camera_make'] = $exif['Make'] ?? null;
                        $cameraInfo['camera_model'] = $exif['Model'] ?? null;
                        $cameraInfo['datetime'] = $exif['DateTime'] ?? null;
                        $cameraInfo['gps_latitude'] = $this->getGpsCoordinate($exif, 'GPSLatitude', 'GPSLatitudeRef') ?? null;
                        $cameraInfo['gps_longitude'] = $this->getGpsCoordinate($exif, 'GPSLongitude', 'GPSLongitudeRef') ?? null;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract image metadata: ' . $e->getMessage());
        }
        
        return $cameraInfo;
    }

    private function deletePhotoFiles(SitePhoto $sitePhoto)
{
    try {
        // Delete original photo
        if (Storage::disk('public')->exists($sitePhoto->photo_path)) {
            Storage::disk('public')->delete($sitePhoto->photo_path);
        }
        
        // Delete thumbnail
        $thumbnailPath = str_replace('/originals/', '/thumbnails/', $sitePhoto->photo_path);
        if (Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }
        
        // Clean up empty directories (optional)
        $this->cleanupEmptyDirectories($sitePhoto->photo_path);
        
    } catch (\Exception $e) {
        Log::warning('Failed to delete some photo files: ' . $e->getMessage(), [
            'photo_path' => $sitePhoto->photo_path,
            'photo_id' => $sitePhoto->id
        ]);
        // Continue with database deletion even if file deletion fails
    }
}

private function cleanupEmptyDirectories($photoPath)
{
    try {
        $directory = dirname($photoPath);
        $fullPath = storage_path('app/public/' . $directory);
        
        // Only clean up if directory exists and is empty
        if (is_dir($fullPath) && count(scandir($fullPath)) <= 2) { // . and .. entries
            Storage::disk('public')->deleteDirectory($directory);
        }
        
        // Also check parent directory (year folder)
        $parentDirectory = dirname($directory);
        $parentFullPath = storage_path('app/public/' . $parentDirectory);
        
        if (is_dir($parentFullPath) && count(scandir($parentFullPath)) <= 2) {
            Storage::disk('public')->deleteDirectory($parentDirectory);
        }
        
    } catch (\Exception $e) {
        // Silently fail - cleanup is not critical
        Log::debug('Directory cleanup failed: ' . $e->getMessage());
    }
}

public function bulkDelete(Request $request)
{
    $request->validate([
        'photo_ids' => 'required|array|min:1',
        'photo_ids.*' => 'exists:site_photos,id',
        'confirm_bulk_delete' => 'required|accepted'
    ]);
    
    $user = auth()->user();
    
    // Only admins can bulk delete
    if ($user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $photoIds = $request->photo_ids;
    $deletedCount = 0;
    $errors = [];
    
    DB::beginTransaction();
    
    try {
        $photos = SitePhoto::whereIn('id', $photoIds)->get();
        
        foreach ($photos as $photo) {
            try {
                $this->deletePhotoFiles($photo);
                $photo->collections()->detach();
                $photo->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to delete photo '{$photo->title}': " . $e->getMessage();
                Log::error('Bulk delete failed for photo: ' . $e->getMessage(), [
                    'photo_id' => $photo->id
                ]);
            }
        }
        
        DB::commit();
        
        Log::info('Bulk photo deletion completed', [
            'deleted_count' => $deletedCount,
            'total_requested' => count($photoIds),
            'deleted_by' => $user->id
        ]);
        
        $message = "Successfully deleted {$deletedCount} photo(s).";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " deletion(s) failed.";
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted_count' => $deletedCount,
            'errors' => $errors
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Bulk photo deletion failed: ' . $e->getMessage());
        
        return response()->json(['error' => 'Bulk deletion failed. Please try again.'], 500);
    }
}

    /**
     * Extract GPS coordinates from EXIF data
     */
    private function getGpsCoordinate($exif, $coordKey, $refKey)
    {
        if (!isset($exif[$coordKey]) || !isset($exif[$refKey])) {
            return null;
        }
        
        $coord = $exif[$coordKey];
        $ref = $exif[$refKey];
        
        if (is_array($coord) && count($coord) >= 3) {
            $degrees = $this->gpsToDecimal($coord[0]);
            $minutes = $this->gpsToDecimal($coord[1]);
            $seconds = $this->gpsToDecimal($coord[2]);
            
            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
            
            if ($ref === 'S' || $ref === 'W') {
                $decimal = -$decimal;
            }
            
            return $decimal;
        }
        
        return null;
    }

    /**
     * Convert GPS fraction to decimal
     */
    private function gpsToDecimal($fraction)
    {
        if (strpos($fraction, '/') !== false) {
            $parts = explode('/', $fraction);
            return count($parts) === 2 ? $parts[0] / $parts[1] : 0;
        }
        
        return floatval($fraction);
    }
    public function pmIndex(Request $request)
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    
    // Build query for PM's managed projects
    $query = SitePhoto::whereIn('project_id', $managedProjectIds)
        ->with(['project', 'task', 'uploader', 'reviewer']);
    
    // Apply filters
    if ($request->filled('status')) {
        $query->where('submission_status', $request->status);
    }
    
    if ($request->filled('project_id') && in_array($request->project_id, $managedProjectIds)) {
        $query->where('project_id', $request->project_id);
    }
    
    if ($request->filled('category')) {
        $query->where('photo_category', $request->category);
    }
    
    if ($request->filled('uploader_id')) {
        $query->where('user_id', $request->uploader_id);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('photo_date', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('photo_date', '<=', $request->date_to);
    }
    
    $photos = $query->orderBy('submitted_at', 'desc')->paginate(15);
    
    // Get filter options for PM's projects only
    $projects = $user->getManagedProjects();
    $uploaders = User::where('role', 'sc')
        ->whereHas('tasks', function($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })
        ->orderBy('first_name')
        ->get();
    
    // Get statistics for PM's projects
    $stats = [
        'total' => SitePhoto::whereIn('project_id', $managedProjectIds)->count(),
        'submitted' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'submitted')->count(),
        'approved' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'approved')->count(),
        'rejected' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'rejected')->count(),
        'featured' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_featured', true)->count(),
        'public' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_public', true)->count(),
        'overdue_reviews' => SitePhoto::whereIn('project_id', $managedProjectIds)
            ->where('submission_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count(),
        'projects_with_photos' => Project::whereIn('id', $managedProjectIds)
            ->whereHas('sitePhotos')
            ->count(),
    ];
    
    return view('pm.site-photos.index', compact('photos', 'projects', 'uploaders', 'stats'));
}

/**
 * PM show photo details with management options
 */
public function pmShow(SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Check if PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        abort(403, 'You do not have permission to manage photos from this project.');
    }
    
    $sitePhoto->load(['project', 'task', 'uploader', 'reviewer', 'comments.user']);
    
    return view('pm.site-photos.show', compact('sitePhoto'));
}

/**
 * API endpoint for gallery photos
 */
public function apiGallery(Request $request)
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    
    $query = SitePhoto::whereIn('project_id', $managedProjectIds)
        ->where('submission_status', 'approved')
        ->with(['project', 'uploader']);
    
    // Apply filters
    if ($request->boolean('featured')) {
        $query->where('is_featured', true);
    }
    
    if ($request->boolean('public')) {
        $query->where('is_public', true);
    }
    
    if ($request->boolean('recent')) {
        $query->where('created_at', '>=', now()->subDays(30));
    }
    
    if ($request->filled('category')) {
        $query->where('photo_category', $request->category);
    }
    
    $photos = $query->orderBy('photo_date', 'desc')
        ->limit(24)
        ->get()
        ->map(function($photo) {
            return [
                'id' => $photo->id,
                'title' => $photo->title,
                'photo_path' => $photo->photo_path,
                'project_name' => $photo->project->name,
                'uploader_name' => $photo->uploader->first_name . ' ' . $photo->uploader->last_name,
                'formatted_photo_date' => $photo->formatted_photo_date,
                'formatted_category' => $photo->formatted_photo_category,
                'is_featured' => $photo->is_featured,
                'is_public' => $photo->is_public,
                'admin_rating' => $photo->admin_rating,
            ];
        });
    
    return response()->json([
        'success' => true,
        'photos' => $photos
    ]);
}

/**
 * API endpoint for single photo details
 */
public function apiPhoto(SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Check if PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        return response()->json(['success' => false, 'message' => 'Access denied'], 403);
    }
    
    $sitePhoto->load(['project', 'task', 'uploader', 'reviewer']);
    
    $photoData = [
        'id' => $sitePhoto->id,
        'title' => $sitePhoto->title,
        'description' => $sitePhoto->description,
        'photo_path' => $sitePhoto->photo_path,
        'project_name' => $sitePhoto->project->name,
        'task_name' => $sitePhoto->task ? $sitePhoto->task->task_name : null,
        'uploader_name' => $sitePhoto->uploader->first_name . ' ' . $sitePhoto->uploader->last_name,
        'formatted_photo_date' => $sitePhoto->formatted_photo_date,
        'formatted_category' => $sitePhoto->formatted_photo_category,
        'formatted_status' => $sitePhoto->formatted_submission_status,
        'status_color' => $sitePhoto->submission_status_badge_color,
        'submission_status' => $sitePhoto->submission_status,
        'location' => $sitePhoto->location,
        'weather_conditions' => $sitePhoto->weather_conditions,
        'formatted_weather' => $sitePhoto->formatted_weather_conditions,
        'is_featured' => $sitePhoto->is_featured,
        'is_public' => $sitePhoto->is_public,
        'admin_rating' => $sitePhoto->admin_rating,
        'tags' => $sitePhoto->tags,
    ];
    
    return response()->json([
        'success' => true,
        'photo' => $photoData
    ]);
}

/**
 * Quick approve photo from PM dashboard or gallery
 */
public function quickApprove(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Ensure PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        return response()->json(['error' => 'Access denied'], 403);
    }
    
    if ($sitePhoto->submission_status !== 'submitted') {
        return response()->json(['error' => 'Photo is not in submitted status.'], 422);
    }
    
    $sitePhoto->update([
        'submission_status' => 'approved',
        'reviewed_by' => $user->id,
        'reviewed_at' => now(),
        'admin_comments' => $request->get('comments', 'Quick approved by PM'),
        'admin_rating' => $request->get('rating'),
        'is_public' => $request->boolean('make_public', false),
        'is_featured' => $request->boolean('make_featured', false),
    ]);
    
    // Notify uploader
    try {
        $sitePhoto->uploader->notify(new SitePhotoApproved($sitePhoto, $user->full_name));
    } catch (\Exception $e) {
        Log::warning('Failed to send approval notification: ' . $e->getMessage());
    }
    
    return response()->json(['success' => true, 'message' => 'Photo approved successfully']);
}

/**
 * Quick reject photo from PM dashboard or gallery
 */
public function quickReject(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Ensure PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        return response()->json(['error' => 'Access denied'], 403);
    }
    
    $request->validate(['reason' => 'required|string|max:500']);
    
    if ($sitePhoto->submission_status !== 'submitted') {
        return response()->json(['error' => 'Photo is not in submitted status.'], 422);
    }
    
    $sitePhoto->update([
        'submission_status' => 'rejected',
        'reviewed_by' => $user->id,
        'reviewed_at' => now(),
        'rejection_reason' => $request->reason,
        'admin_comments' => $request->get('comments'),
    ]);
    
    // Notify uploader
    try {
        $sitePhoto->uploader->notify(new SitePhotoRejected($sitePhoto, $user->full_name));
    } catch (\Exception $e) {
        Log::warning('Failed to send rejection notification: ' . $e->getMessage());
    }
    
    return response()->json(['success' => true, 'message' => 'Photo rejected successfully']);
}

/**
 * Toggle featured status
 */
public function toggleFeature(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Ensure PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        return response()->json(['error' => 'Access denied'], 403);
    }
    
    $request->validate(['is_featured' => 'required|boolean']);
    
    $sitePhoto->update(['is_featured' => $request->boolean('is_featured')]);
    
    return response()->json([
        'success' => true,
        'message' => $request->boolean('is_featured') ? 'Photo marked as featured' : 'Photo unmarked as featured',
        'is_featured' => $sitePhoto->is_featured
    ]);
}

/**
 * Toggle public visibility
 */
public function togglePublic(Request $request, SitePhoto $sitePhoto)
{
    $user = auth()->user();
    
    // Ensure PM manages this project
    if (!$user->canManageProject($sitePhoto->project_id)) {
        return response()->json(['error' => 'Access denied'], 403);
    }
    
    $request->validate(['is_public' => 'required|boolean']);
    
    $sitePhoto->update(['is_public' => $request->boolean('is_public')]);
    
    return response()->json([
        'success' => true,
        'message' => $request->boolean('is_public') ? 'Photo made public' : 'Photo made private',
        'is_public' => $sitePhoto->is_public
    ]);
}

/**
 * API endpoint for PM dashboard stats
 */
public function apiStats()
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    
    return response()->json([
        'total' => SitePhoto::whereIn('project_id', $managedProjectIds)->count(),
        'pending_review' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'submitted')->count(),
        'approved' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'approved')->count(),
        'rejected' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'rejected')->count(),
        'featured' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_featured', true)->count(),
        'public' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_public', true)->count(),
        'overdue_reviews' => SitePhoto::whereIn('project_id', $managedProjectIds)
            ->where('submission_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count(),
    ]);
}

/**
 * Export photos data for PM
 */
public function pmExport(Request $request)
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    $filename = 'pm_site_photos_' . date('Y-m-d_H-i-s') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];
    
    $callback = function() use ($request, $managedProjectIds) {
        $file = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($file, [
            'ID', 'Title', 'Project', 'Task', 'Uploader', 'Category', 
            'Photo Date', 'Status', 'Submitted At', 'Reviewed At', 
            'Rating', 'Featured', 'Public', 'File Size', 'Location'
        ]);
        
        // Query with PM restrictions
        $query = SitePhoto::whereIn('project_id', $managedProjectIds)
            ->with(['project', 'task', 'uploader']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('submission_status', $request->status);
        }
        if ($request->filled('project_id') && in_array($request->project_id, $managedProjectIds)) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('category')) {
            $query->where('photo_category', $request->category);
        }
        
        // Export data
        $query->orderBy('photo_date', 'desc')->chunk(1000, function($photos) use ($file) {
            foreach ($photos as $photo) {
                fputcsv($file, [
                    $photo->id,
                    $photo->title,
                    $photo->project->name,
                    $photo->task ? $photo->task->task_name : '',
                    $photo->uploader->first_name . ' ' . $photo->uploader->last_name,
                    ucfirst($photo->photo_category),
                    $photo->photo_date ? $photo->photo_date->format('M d, Y') : '',
                    ucfirst($photo->submission_status),
                    $photo->submitted_at ? $photo->submitted_at->format('M d, Y g:i A') : '',
                    $photo->reviewed_at ? $photo->reviewed_at->format('M d, Y g:i A') : '',
                    $photo->admin_rating ?? '',
                    $photo->is_featured ? 'Yes' : 'No',
                    $photo->is_public ? 'Yes' : 'No',
                    $photo->file_size ? number_format($photo->file_size / 1024, 2) . ' KB' : '',
                    $photo->location ?? ''
                ]);
            }
        });
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}

/**
 * Get recent site photos for PM dashboard
 */
public function getRecentForDashboard($limit = 5)
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    
    return SitePhoto::whereIn('project_id', $managedProjectIds)
        ->with(['project', 'uploader'])
        ->orderBy('submitted_at', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($photo) {
            return [
                'id' => $photo->id,
                'title' => $photo->title,
                'project_name' => $photo->project->name,
                'uploader_name' => $photo->uploader->first_name . ' ' . $photo->uploader->last_name,
                'submission_status' => $photo->submission_status,
                'submission_status_badge_color' => $photo->submission_status_badge_color,
                'formatted_submission_status' => $photo->formatted_submission_status,
                'photo_category' => $photo->photo_category,
                'is_featured' => $photo->is_featured,
                'is_public' => $photo->is_public,
                'submitted_at' => $photo->submitted_at,
                'created_at' => $photo->created_at,
            ];
        });
}

/**
 * Helper method to update dashboard data for PM
 */
public function getSitePhotosStats()
{
    $user = auth()->user();
    $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
    
    return [
        'total' => SitePhoto::whereIn('project_id', $managedProjectIds)->count(),
        'submitted' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'submitted')->count(),
        'approved' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'approved')->count(),
        'rejected' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('submission_status', 'rejected')->count(),
        'featured' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_featured', true)->count(),
        'public' => SitePhoto::whereIn('project_id', $managedProjectIds)->where('is_public', true)->count(),
        'overdue_reviews' => SitePhoto::whereIn('project_id', $managedProjectIds)
            ->where('submission_status', 'submitted')
            ->where('submitted_at', '<', now()->subDays(3))
            ->count(),
        'projects_with_photos' => Project::whereIn('id', $managedProjectIds)
            ->whereHas('sitePhotos')
            ->count(),
    ];
}
}