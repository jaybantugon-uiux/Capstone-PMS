<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SitePhoto;
use App\Models\ProgressReport;
use App\Models\ProjectUpdate;
use App\Models\ClientActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClientProjectController extends Controller
{
    /**
     * Display a listing of projects accessible to the client
     */
    public function index(Request $request)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Get client's accessible projects with filters
        $query = $client->clientProjects()
            ->with([
                'projectClients' => function($query) use ($client) {
                    $query->where('client_id', $client->id);
                },
                'creator:id,first_name,last_name,email'
            ]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('archived', false);
                    break;
                case 'completed':
                    $query->where('archived', false)
                          ->whereHas('tasks', function($q) {
                              $q->selectRaw('project_id, COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                                ->groupBy('project_id')
                                ->havingRaw('total = completed AND total > 0');
                          });
                    break;
                case 'in_progress':
                    $query->where('archived', false)
                          ->whereHas('tasks', function($q) {
                              $q->where('status', 'in_progress');
                          });
                    break;
            }
        }

        $projects = $query->paginate(12);

        // Get project statistics for each project
        foreach ($projects as $project) {
            $project->load([
                'sitePhotos' => function($query) {
                    $query->where('is_public', true)
                          ->where('submission_status', 'approved')
                          ->orderBy('photo_date', 'desc')
                          ->limit(3);
                }
            ]);

            // Calculate client-specific completion percentage
            $project->client_completion_percentage = $this->calculateClientCompletionPercentage($project);
            $project->client_health_status = $this->getClientHealthStatus($project);
            $project->client_health_color = $this->getClientHealthColor($project->client_health_status);
        }

        // Get overall statistics
        $stats = [
            'total_projects' => $client->clientProjects()->count(),
            'active_projects' => $client->clientProjects()->where('archived', false)->count(),
            'completed_projects' => $this->getCompletedProjectsCount($client),
            'photos_this_month' => SitePhoto::whereIn('project_id', $client->clientProjects()->pluck('id'))
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        return view('client.projects.index', compact('projects', 'stats'));
    }

    /**
     * Display the specified project for the client
     */
    public function show(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        // Load project relationships
        $project->load([
            'creator:id,first_name,last_name,email',
            'projectClients' => function($query) use ($client) {
                $query->where('client_id', $client->id);
            }
        ]);

        // Get client's access level for this project
        $clientAccess = $project->projectClients->first();

        // Get approved public photos for this project
        $photos = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->with(['uploader:id,first_name,last_name'])
            ->orderBy('photo_date', 'desc')
            ->paginate(8, ['*'], 'photos');

        // Get recent progress reports for this project
        $recentReports = ProgressReport::forClient($client->id)
            ->where('project_id', $project->id)
            ->with(['creator:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get project updates if available
        $projectUpdates = collect();
        if (method_exists($project, 'publicUpdates')) {
            $projectUpdates = $project->publicUpdates()
                ->orderBy('posted_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Calculate project metrics
        $project->client_completion_percentage = $this->calculateClientCompletionPercentage($project);
        $project->client_health_status = $this->getClientHealthStatus($project);
        $project->client_health_color = $this->getClientHealthColor($project->client_health_status);
        $project->client_visible_photos_count = $photos->total();
        $project->recent_updates_count = $projectUpdates->count();
        $project->recent_client_activity_count = $this->getRecentClientActivityCount($project);

        // Log the project view
        $this->logProjectView($client, $project, $request);

        return view('client.projects.show', compact(
            'project', 
            'photos', 
            'recentReports', 
            'projectUpdates',
            'clientAccess'
        ));
    }

    /**
     * Display photos for a specific project
     */
    public function photos(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        // Build photos query
        $query = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->with(['uploader:id,first_name,last_name', 'task:id,task_name']);

        // Apply filters
        if ($request->filled('category')) {
            $query->where('photo_category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->where('photo_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('photo_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $photos = $query->orderBy('photo_date', 'desc')->paginate(20);

        // Get photo categories available for this project
        $categories = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->distinct()
            ->pluck('photo_category')
            ->filter()
            ->sort()
            ->values();

        return view('client.projects.photos', compact('project', 'photos', 'categories'));
    }

    /**
     * Display project progress timeline
     */
    public function progress(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        // Get progress reports for this project
        $progressReports = ProgressReport::forClient($client->id)
            ->where('project_id', $project->id)
            ->with(['creator:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get project updates timeline
        $projectUpdates = collect();
        if (method_exists($project, 'publicUpdates')) {
            $projectUpdates = $project->publicUpdates()
                ->orderBy('posted_at', 'desc')
                ->get();
        }

        // Get photo milestones (featured photos)
        $photoMilestones = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->where('is_featured', true)
            ->with(['uploader:id,first_name,last_name'])
            ->orderBy('photo_date', 'desc')
            ->get();

        // Calculate progress metrics
        $progressMetrics = $this->calculateProgressMetrics($project);

        return view('client.projects.progress', compact(
            'project', 
            'progressReports', 
            'projectUpdates', 
            'photoMilestones',
            'progressMetrics'
        ));
    }

    /**
     * Mark project as viewed (AJAX)
     */
    public function markAsViewed(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            // Log the view
            $this->logProjectView($client, $project, $request);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to mark project as viewed', [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to mark as viewed'], 500);
        }
    }

    /**
     * Get project updates (AJAX)
     */
    public function getUpdates(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $updates = collect();
        
        // Get project updates
        if (method_exists($project, 'publicUpdates')) {
            $projectUpdates = $project->publicUpdates()
                ->orderBy('posted_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($update) {
                    return [
                        'type' => 'project_update',
                        'title' => $update->title,
                        'description' => $update->description,
                        'date' => $update->posted_at,
                        'icon' => 'fas fa-bullhorn',
                        'color' => 'primary'
                    ];
                });
            
            $updates = $updates->merge($projectUpdates);
        }

        // Get recent progress reports
        $recentReports = ProgressReport::forClient($client->id)
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($report) {
                return [
                    'type' => 'progress_report',
                    'title' => $report->title,
                    'description' => 'Progress report from ' . $report->creator->first_name,
                    'date' => $report->created_at,
                    'icon' => 'fas fa-file-alt',
                    'color' => 'success',
                    'url' => route('client.reports.show', $report->id)
                ];
            });

        $updates = $updates->merge($recentReports);

        // Get recent photos
        $recentPhotos = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->where('created_at', '>=', now()->subDays(30))
            ->with(['uploader:id,first_name,last_name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($photo) {
                return [
                    'type' => 'photo_upload',
                    'title' => $photo->title,
                    'description' => 'Photo uploaded by ' . $photo->uploader->first_name,
                    'date' => $photo->created_at,
                    'icon' => 'fas fa-camera',
                    'color' => 'info'
                ];
            });

        $updates = $updates->merge($recentPhotos);

        // Sort by date and return
        $sortedUpdates = $updates->sortByDesc('date')->take(20)->values();

        return response()->json([
            'success' => true,
            'updates' => $sortedUpdates
        ]);
    }

    /**
     * Calculate client-specific completion percentage
     */
    private function calculateClientCompletionPercentage(Project $project): int
    {
        // Cache the result for 5 minutes
        return Cache::remember("client_completion_{$project->id}", 300, function() use ($project) {
            // Check if there are major milestones
            if (method_exists($project, 'projectUpdates')) {
                $majorMilestones = $project->projectUpdates()
                    ->where('is_major_milestone', true)
                    ->where('visibility', '!=', 'internal')
                    ->count();

                if ($majorMilestones > 0) {
                    $completedMilestones = $project->projectUpdates()
                        ->where('is_major_milestone', true)
                        ->where('update_type', 'completion')
                        ->where('visibility', '!=', 'internal')
                        ->count();

                    return round(($completedMilestones / $majorMilestones) * 100);
                }
            }

            // Fallback to task-based calculation
            $totalTasks = $project->tasks()->where('archived', false)->count();
            
            if ($totalTasks === 0) {
                return 0;
            }

            $completedTasks = $project->tasks()
                ->where('archived', false)
                ->where('status', 'completed')
                ->count();
            
            return round(($completedTasks / $totalTasks) * 100);
        });
    }

    /**
     * Get client health status
     */
    private function getClientHealthStatus(Project $project): string
    {
        $completion = $this->calculateClientCompletionPercentage($project);
        
        if ($completion >= 100) {
            return 'completed';
        }
        
        if ($project->end_date && $project->end_date->isPast()) {
            return 'delayed';
        }
        
        if ($completion >= 80) {
            return 'on_track';
        }
        
        if ($completion >= 50) {
            return 'progressing';
        }
        
        return 'early_stage';
    }

    /**
     * Get health status color
     */
    private function getClientHealthColor(string $status): string
    {
        return match($status) {
            'completed' => 'success',
            'on_track' => 'success',
            'progressing' => 'primary',
            'early_stage' => 'info',
            'delayed' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get completed projects count for client
     */
    private function getCompletedProjectsCount($client): int
    {
        return $client->clientProjects()
            ->where('archived', false)
            ->whereHas('tasks', function($query) {
                $query->selectRaw('project_id, COUNT(*) as total, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                      ->groupBy('project_id')
                      ->havingRaw('total = completed AND total > 0');
            })
            ->count();
    }

    /**
     * Get recent client activity count
     */
    private function getRecentClientActivityCount(Project $project): int
    {
        $activityCount = 0;

        // Count recent progress reports
        $activityCount += ProgressReport::where('project_id', $project->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Count recent public photos
        $activityCount += SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Count recent project updates
        if (method_exists($project, 'publicUpdates')) {
            $activityCount += $project->publicUpdates()
                ->where('posted_at', '>=', now()->subDays(7))
                ->count();
        }

        return $activityCount;
    }

    /**
     * Calculate progress metrics for project
     */
    private function calculateProgressMetrics(Project $project): array
    {
        $metrics = [
            'overall_completion' => $this->calculateClientCompletionPercentage($project),
            'days_elapsed' => 0,
            'days_remaining' => 0,
            'total_duration' => 0,
            'is_on_schedule' => true,
            'photos_count' => 0,
            'reports_count' => 0,
            'milestones_completed' => 0,
            'milestones_total' => 0
        ];

        // Calculate time metrics
        if ($project->start_date) {
            $metrics['days_elapsed'] = $project->start_date->diffInDays(now());
            
            if ($project->end_date) {
                $metrics['total_duration'] = $project->start_date->diffInDays($project->end_date);
                $metrics['days_remaining'] = max(0, now()->diffInDays($project->end_date, false));
                
                // Check if on schedule
                $expectedProgress = $metrics['total_duration'] > 0 
                    ? ($metrics['days_elapsed'] / $metrics['total_duration']) * 100 
                    : 0;
                
                $metrics['is_on_schedule'] = $metrics['overall_completion'] >= ($expectedProgress - 10); // 10% tolerance
            }
        }

        // Count photos
        $metrics['photos_count'] = SitePhoto::where('project_id', $project->id)
            ->where('is_public', true)
            ->where('submission_status', 'approved')
            ->count();

        // Count progress reports
        $metrics['reports_count'] = ProgressReport::where('project_id', $project->id)->count();

        // Count milestones if available
        if (method_exists($project, 'projectUpdates')) {
            $metrics['milestones_total'] = $project->projectUpdates()
                ->where('is_major_milestone', true)
                ->where('visibility', '!=', 'internal')
                ->count();

            $metrics['milestones_completed'] = $project->projectUpdates()
                ->where('is_major_milestone', true)
                ->where('update_type', 'completion')
                ->where('visibility', '!=', 'internal')
                ->count();
        }

        return $metrics;
    }

    /**
     * Log project view activity
     */
    private function logProjectView($client, Project $project, Request $request): void
    {
        try {
            // Create activity log entry
            ClientActivityLog::create([
                'user_id' => $client->id,
                'activity_type' => 'project_view',
                'description' => "Viewed project: {$project->name}",
                'related_model' => 'Project',
                'related_id' => $project->id,
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'project_name' => $project->name,
                    'view_source' => $request->get('source', 'direct'),
                    'timestamp' => now()->toISOString()
                ],
                'created_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::warning('Failed to log project view activity', [
                'client_id' => $client->id,
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get project summary for dashboard widget
     */
    public function getProjectSummary(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $summary = [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'completion_percentage' => $this->calculateClientCompletionPercentage($project),
            'health_status' => $this->getClientHealthStatus($project),
            'health_color' => $this->getClientHealthColor($this->getClientHealthStatus($project)),
            'start_date' => $project->start_date?->format('M d, Y'),
            'end_date' => $project->end_date?->format('M d, Y'),
            'photos_count' => SitePhoto::where('project_id', $project->id)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->count(),
            'recent_activity_count' => $this->getRecentClientActivityCount($project),
            'latest_update' => null
        ];

        // Get latest update
        $latestReport = ProgressReport::forClient($client->id)
            ->where('project_id', $project->id)
            ->latest('created_at')
            ->first();

        if ($latestReport) {
            $summary['latest_update'] = [
                'type' => 'progress_report',
                'title' => $latestReport->title,
                'date' => $latestReport->created_at->format('M d, Y'),
                'url' => route('client.reports.show', $latestReport->id)
            ];
        }

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    /**
     * Search projects for client
     */
    public function search(Request $request)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $projects = $client->clientProjects()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'description']);

        $results = $projects->map(function($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description ? substr($project->description, 0, 100) . '...' : '',
                'url' => route('client.projects.show', $project->id)
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Export project data for client
     */
    public function export(Request $request, Project $project)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        // Check if client has access to this project
        if (!$client->clientProjects()->where('project_id', $project->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }

        $filename = 'project_' . str_slug($project->name) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($project, $client) {
            $file = fopen('php://output', 'w');
            
            // Project overview
            fputcsv($file, ['Project Overview']);
            fputcsv($file, ['Name', $project->name]);
            fputcsv($file, ['Description', $project->description]);
            fputcsv($file, ['Start Date', $project->start_date?->format('M d, Y')]);
            fputcsv($file, ['End Date', $project->end_date?->format('M d, Y')]);
            fputcsv($file, ['Completion', $this->calculateClientCompletionPercentage($project) . '%']);
            fputcsv($file, ['Health Status', ucfirst(str_replace('_', ' ', $this->getClientHealthStatus($project)))]);
            fputcsv($file, []);

            // Progress Reports
            fputcsv($file, ['Progress Reports']);
            fputcsv($file, ['Date', 'Title', 'Status', 'Views']);
            
            $reports = ProgressReport::forClient($client->id)
                ->where('project_id', $project->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->created_at->format('M d, Y'),
                    $report->title,
                    ucfirst($report->status),
                    $report->view_count
                ]);
            }
            
            fputcsv($file, []);

            // Public Photos
            fputcsv($file, ['Public Photos']);
            fputcsv($file, ['Date', 'Title', 'Category', 'Uploader']);
            
            $photos = SitePhoto::where('project_id', $project->id)
                ->where('is_public', true)
                ->where('submission_status', 'approved')
                ->with('uploader')
                ->orderBy('photo_date', 'desc')
                ->get();

            foreach ($photos as $photo) {
                fputcsv($file, [
                    $photo->photo_date->format('M d, Y'),
                    $photo->title,
                    ucfirst($photo->photo_category),
                    $photo->uploader->first_name . ' ' . $photo->uploader->last_name
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}