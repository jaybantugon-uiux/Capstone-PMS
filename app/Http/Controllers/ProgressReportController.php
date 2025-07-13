<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\ProgressReportView;
use App\Models\User;
use App\Models\Project;
use App\Notifications\ProgressReportShared;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Arr;

class ProgressReportController extends Controller
{
    /**
     * Display a listing of progress reports for admin/PM
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Base query - admins see all, PMs see only their own
        $query = ProgressReport::with(['client', 'project', 'creator']);
        
        if ($user->role === 'pm') {
            // PMs can only see reports they created
            $query->where('created_by', $user->id);
        }
        
        $query->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('creator_role') && $user->role === 'admin') {
            // Only admins can filter by creator role
            $query->where('created_by_role', $request->creator_role);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate(15);

        // Get filter options
        $clients = User::where('role', 'client')->orderBy('first_name')->get();
        $projects = Project::active()->orderBy('name')->get();

        return view('admin.progress-reports.index', compact('reports', 'clients', 'projects'));
    }

    /**
     * Show the form for creating a new progress report
     */
    public function create()
    {
        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();
            
        $projects = Project::active()->orderBy('name')->get();

        return view('admin.progress-reports.create', compact('clients', 'projects'));
    }

    /**
     * Store a newly created progress report
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Verify user has permission to create reports
        if (!in_array($user->role, ['admin', 'pm'])) {
            abort(403, 'You do not have permission to create progress reports.');
        }

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:5000',
                'client_id' => 'required|exists:users,id',
                'project_id' => 'nullable|exists:projects,id',
                'attachment' => [
                    'nullable',
                    'file',
                    'max:51200', // 50MB in KB
                    'mimes:pdf,doc,docx,jpg,jpeg,png,gif'
                ],
            ]);

            Log::info('Progress report validation passed', [
                'validated_data' => Arr::except($validatedData, ['attachment'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Progress report validation failed', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'input' => $request->except(['attachment', 'password'])
            ]);
            
            return back()->withErrors($e->errors())->withInput();
        }

        // Verify client role
        $client = User::findOrFail($validatedData['client_id']);
        if ($client->role !== 'client') {
            Log::warning('Invalid client selected for progress report', [
                'user_id' => $user->id,
                'selected_user_id' => $client->id,
                'selected_user_role' => $client->role
            ]);
            
            return back()->withErrors(['client_id' => 'Selected user must be a client.'])->withInput();
        }

        // For PMs, verify they have access to the project if specified
        if ($user->role === 'pm' && $validatedData['project_id']) {
            $project = Project::findOrFail($validatedData['project_id']);
            if (!$user->canManageProject($project->id)) {
                Log::warning('PM attempted to create report for unauthorized project', [
                    'user_id' => $user->id,
                    'project_id' => $project->id
                ]);
                
                return back()->withErrors(['project_id' => 'You do not have access to this project.'])->withInput();
            }
        }

        DB::beginTransaction();
        
        try {
            $reportData = [
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'created_by' => $user->id,
                'created_by_role' => $user->role,
                'client_id' => $validatedData['client_id'],
                'project_id' => $validatedData['project_id'],
                'status' => $request->has('save_as_draft') ? 'draft' : 'sent',
                'sent_at' => $request->has('save_as_draft') ? null : now(),
                'view_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Log::info('Preparing to create progress report', [
                'user_id' => $user->id,
                'report_data' => $reportData
            ]);

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                Log::info('Processing file upload', [
                    'user_id' => $user->id,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);

                // Validate file
                if (!$file->isValid()) {
                    throw new \Exception('Uploaded file is not valid');
                }

                // Check file size (50MB max)
                if ($file->getSize() > 50 * 1024 * 1024) {
                    throw new \Exception('File size exceeds 50MB limit');
                }

                // Generate unique filename with creator role prefix
                $rolePrefix = $user->role === 'admin' ? 'admin' : 'pm';
                $timestamp = time();
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                
                // Sanitize filename
                $sanitizedBaseName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $baseName);
                $filename = $rolePrefix . '_' . $timestamp . '_' . $sanitizedBaseName . '.' . $extension;
                
                try {
                    // Store file in private storage
                    $path = $file->storeAs('progress-reports', $filename, 'private');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store uploaded file');
                    }

                    $reportData['attachment_path'] = $path;
                    $reportData['original_filename'] = $file->getClientOriginalName();
                    $reportData['file_size'] = $file->getSize();
                    $reportData['mime_type'] = $file->getMimeType();

                    Log::info('File uploaded successfully', [
                        'user_id' => $user->id,
                        'path' => $path,
                        'filename' => $filename
                    ]);

                } catch (\Exception $e) {
                    Log::error('File upload failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                        'file_info' => [
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime' => $file->getMimeType()
                        ]
                    ]);
                    throw new \Exception('Failed to upload attachment: ' . $e->getMessage());
                }
            }

            // Create the progress report
            $report = ProgressReport::create($reportData);

            if (!$report) {
                throw new \Exception('Failed to create progress report record');
            }

            Log::info('Progress report created successfully', [
                'user_id' => $user->id,
                'report_id' => $report->id,
                'client_id' => $client->id
            ]);

            // Send notifications only if not draft
            if (!$request->has('save_as_draft')) {
                try {
                    $client->notify(new ProgressReportShared($report));
                    
                    Log::info('Progress report notification sent', [
                        'report_id' => $report->id,
                        'client_id' => $client->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send progress report notification', [
                        'report_id' => $report->id,
                        'client_id' => $client->id,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the entire operation for notification failure
                }
            }

            DB::commit();

            // Log the successful action
            Log::info('Progress report operation completed successfully', [
                'report_id' => $report->id,
                'creator_id' => $user->id,
                'creator_role' => $user->role,
                'client_id' => $client->id,
                'title' => $report->title,
                'has_attachment' => !empty($report->attachment_path),
                'is_draft' => $request->has('save_as_draft')
            ]);

            $message = $request->has('save_as_draft') 
                ? 'Progress report saved as draft successfully!'
                : 'Progress report created and sent to ' . $client->first_name . ' ' . $client->last_name . ' successfully!';

            return redirect()->route('admin.progress-reports.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to create progress report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'creator_id' => $user->id,
                'creator_role' => $user->role,
                'client_id' => $request->client_id,
                'input_data' => $request->except(['attachment'])
            ]);

            // Clean up uploaded file if it exists
            if (isset($path) && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
                Log::info('Cleaned up uploaded file after error', ['path' => $path]);
            }

            return back()->withInput()
                ->withErrors(['general' => 'Failed to create progress report: ' . $e->getMessage()]);
        }
    }
    /**
     * Display the specified progress report (Admin/PM view)
     */
    public function show(ProgressReport $progressReport)
    {
        $user = auth()->user();
        
        // PMs can only view reports they created, admins can view all
        if ($user->role === 'pm' && $progressReport->created_by !== $user->id) {
            abort(403, 'You can only view progress reports you created.');
        }
        
        $progressReport->load(['client', 'project', 'creator', 'views.client']);
        
        // Get view analytics
        $viewStats = [
            'total_views' => $progressReport->view_count,
            'unique_days' => $progressReport->views()->selectRaw('DATE(viewed_at) as view_date')->distinct()->count(),
            'recent_views' => $progressReport->views()->where('viewed_at', '>=', now()->subDays(7))->count(),
            'latest_view' => $progressReport->views()->latest('viewed_at')->first(),
        ];

        return view('admin.progress-reports.show', compact('progressReport', 'viewStats'));
    }

    /**
     * Download the attachment for admin/PM
     */
    public function downloadAttachment(ProgressReport $progressReport)
    {
        $user = auth()->user();
        
        // PMs can only download attachments from reports they created, admins can download all
        if ($user->role === 'pm' && $progressReport->created_by !== $user->id) {
            abort(403, 'You can only download attachments from progress reports you created.');
        }
        
        if (!$progressReport->hasAttachment()) {
            abort(404, 'Attachment not found');
        }

        return Storage::download($progressReport->attachment_path, $progressReport->original_filename);
    }

    /**
     * Update the specified progress report
     */
    public function update(Request $request, ProgressReport $progressReport)
    {
        $user = auth()->user();
        
        // PMs can only update reports they created, admins can update all
        if ($user->role === 'pm' && $progressReport->created_by !== $user->id) {
            abort(403, 'You can only update progress reports you created.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'status' => 'required|in:draft,sent,viewed,archived',
        ]);

        $progressReport->update($request->only(['title', 'description', 'status']));

        return redirect()->route('admin.progress-reports.show', $progressReport)
            ->with('success', 'Progress report updated successfully!');
    }

    /**
     * Remove the specified progress report
     */
    public function destroy(ProgressReport $progressReport)
    {
        $user = auth()->user();
        
        // PMs can only delete reports they created, admins can delete all
        if ($user->role === 'pm' && $progressReport->created_by !== $user->id) {
            abort(403, 'You can only delete progress reports you created.');
        }
        
        try {
            $clientName = $progressReport->client->first_name . ' ' . $progressReport->client->last_name;
            $progressReport->delete();

            return redirect()->route('admin.progress-reports.index')
                ->with('success', 'Progress report deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to delete progress report', [
                'report_id' => $progressReport->id,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Failed to delete progress report.']);
        }
    }

    // ============================================
    // CLIENT METHODS
    // ============================================

    /**
     * Display progress reports for the authenticated client
     */
    public function clientIndex(Request $request)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403, 'Access denied. This section is for clients only.');
        }

        $query = ProgressReport::forClient($client->id)
            ->with(['admin', 'project'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $reports = $query->paginate(10);

        // Get client's projects for filter
        $projects = Project::whereHas('clients', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_reports' => ProgressReport::forClient($client->id)->count(),
            'unread_reports' => ProgressReport::forClient($client->id)->where('status', 'sent')->count(),
            'recent_reports' => ProgressReport::forClient($client->id)->recent(7)->count(),
            'reports_with_attachments' => ProgressReport::forClient($client->id)->withAttachments()->count(),
        ];

        return view('client.reports.index', compact('reports', 'projects', 'stats'));
    }

    /**
     * Display the specified progress report for client
     */
    public function clientShow(Request $request, ProgressReport $progressReport)
    {
        $client = auth()->user();
        
        // Ensure the client can only view their own reports
        if ($client->role !== 'client' || $progressReport->client_id !== $client->id) {
            abort(403, 'You do not have permission to view this report.');
        }

        // Track the view
        $progressReport->markAsViewed(
            $client,
            $request->ip(),
            $request->userAgent()
        );

        $progressReport->load(['admin', 'project']);

        return view('client.reports.show', compact('progressReport'));
    }

    /**
     * Download attachment for client
     */
    public function clientDownloadAttachment(ProgressReport $progressReport)
    {
        $client = auth()->user();
        
        // Ensure the client can only download attachments from their own reports
        if ($client->role !== 'client' || $progressReport->client_id !== $client->id) {
            abort(403, 'You do not have permission to download this attachment.');
        }

        if (!$progressReport->hasAttachment()) {
            abort(404, 'Attachment not found');
        }

        // Track download as a view
        $progressReport->markAsViewed(
            $client,
            request()->ip(),
            request()->userAgent()
        );

        return Storage::download($progressReport->attachment_path, $progressReport->original_filename);
    }

    /**
     * Mark all reports as read for client
     */
    public function markAllAsRead()
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403);
        }

        // Update all sent reports to viewed status
        ProgressReport::forClient($client->id)
            ->where('status', 'sent')
            ->update(['status' => 'viewed']);

        return back()->with('success', 'All reports marked as read.');
    }

    /**
     * Export client reports to PDF (optional feature)
     */
    public function exportClientReports(Request $request)
    {
        $client = auth()->user();
        
        if ($client->role !== 'client') {
            abort(403);
        }

        $reports = ProgressReport::forClient($client->id)
            ->with(['admin', 'project'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV export
        $filename = 'progress_reports_' . $client->username . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reports) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date Received', 'Title', 'Project', 'Admin', 'Status', 
                'Has Attachment', 'Views', 'Description'
            ]);
            
            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->created_at->format('M d, Y'),
                    $report->title,
                    $report->project ? $report->project->name : 'General',
                    $report->admin->first_name . ' ' . $report->admin->last_name,
                    $report->formatted_status,
                    $report->hasAttachment() ? 'Yes' : 'No',
                    $report->view_count,
                    strip_tags($report->description)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get AJAX data for admin/PM dashboard widgets
     */
    public function getReportStats()
    {
        $user = auth()->user();
        
        // Base query - admins see all, PMs see only their own
        $query = ProgressReport::query();
        if ($user->role === 'pm') {
            $query->where('created_by', $user->id);
        }
        
        $stats = [
            'total_reports' => $query->count(),
            'recent_reports' => $query->where('created_at', '>=', now()->subDays(7))->count(),
            'unread_reports' => $query->where('status', 'sent')->count(),
            'reports_with_attachments' => $query->whereNotNull('attachment_path')->count(),
        ];
        
        // Most active clients (only for current user's reports if PM)
        $mostActiveQuery = ProgressReport::select('client_id', DB::raw('count(*) as report_count'))
            ->with('client:id,first_name,last_name');
            
        if ($user->role === 'pm') {
            $mostActiveQuery->where('created_by', $user->id);
        }
        
        $stats['most_active_clients'] = $mostActiveQuery
            ->groupBy('client_id')
            ->orderBy('report_count', 'desc')
            ->limit(5)
            ->get();
        
        // Recent activity (only for current user's reports if PM)
        $recentActivityQuery = ProgressReport::with(['client:id,first_name,last_name', 'creator:id,first_name,last_name']);
        
        if ($user->role === 'pm') {
            $recentActivityQuery->where('created_by', $user->id);
        }
        
        $stats['recent_activity'] = $recentActivityQuery
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($stats);
    }
    public function pmIndex(Request $request)
{
    $user = auth()->user();
    
    // Ensure only PMs can access this
    if ($user->role !== 'pm') {
        abort(403, 'Access denied. This section is for Project Managers only.');
    }

    // Base query - PM sees only reports they created
    $query = ProgressReport::with(['client', 'project'])
        ->where('created_by', $user->id)
        ->orderBy('created_at', 'desc');

    // Apply filters
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('client_id')) {
        $query->where('client_id', $request->client_id);
    }

    if ($request->filled('project_id')) {
        // Ensure PM can only filter by projects they created
        $project = Project::findOrFail($request->project_id);
        if ($project->created_by === $user->id) {
            $query->where('project_id', $request->project_id);
        }
    }

    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $reports = $query->paginate(15);

    // Get filter options - only clients and projects this PM has access to
    $clients = User::where('role', 'client')
        ->where('status', 'active')
        ->orderBy('first_name')
        ->get();
    
    // Get projects this PM created
    $projects = Project::where('created_by', $user->id)
        ->orderBy('name')
        ->get();

    return view('pm.progress-reports.index', compact('reports', 'clients', 'projects'));
}
}