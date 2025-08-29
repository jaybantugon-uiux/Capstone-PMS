<?php

namespace App\Http\Controllers;

use App\Models\MonitoredEquipment;
use App\Models\EquipmentRequest;
use App\Models\EquipmentMaintenance;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\EquipmentRequestApproved;
use App\Notifications\EquipmentRequestDeclined;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EquipmentMonitoringController extends Controller
{
    // ====================================================================
    // ADMIN METHODS - MANAGEMENT FUNCTIONALITY
    // ====================================================================

    /**
     * Admin dashboard for equipment monitoring management
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        
        // Get filter parameters
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('type');
        $userFilter = $request->get('user_id');
        $projectFilter = $request->get('project_id');
        
        // Build base query for equipment requests
        $requestsQuery = EquipmentRequest::with(['user', 'project', 'monitoredEquipment'])
            ->orderBy('created_at', 'desc');
            
        // Apply filters
        if ($statusFilter) {
            $requestsQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $requestsQuery->where('usage_type', $typeFilter);
        }
        if ($userFilter) {
            $requestsQuery->where('user_id', $userFilter);
        }
        if ($projectFilter) {
            $requestsQuery->where('project_id', $projectFilter);
        }
        
        $equipmentRequests = $requestsQuery->take(10)->get(); // Limit for dashboard view
        
        // Get comprehensive statistics - FIXED
        $stats = [
            // Equipment Request Statistics
            'total_requests' => EquipmentRequest::count(),
            'pending_requests' => EquipmentRequest::where('status', 'pending')->count(),
            'approved_requests' => EquipmentRequest::where('status', 'approved')->count(),
            'declined_requests' => EquipmentRequest::where('status', 'declined')->count(),
            
            // Monitored Equipment Statistics
            'total_equipment' => MonitoredEquipment::count(),
            'active_equipment' => MonitoredEquipment::where('status', 'active')->count(),
            'pending_equipment' => MonitoredEquipment::where('status', 'pending_approval')->count(),
            'inactive_equipment' => MonitoredEquipment::where('status', 'inactive')->count(),
            
            // Equipment by usage type
            'personal_equipment' => MonitoredEquipment::where('usage_type', 'personal')->count(),
            'project_equipment' => MonitoredEquipment::where('usage_type', 'project_site')->count(),
            
            // Equipment availability
            'equipment_available' => MonitoredEquipment::where('availability_status', 'available')->count(),
            'equipment_in_use' => MonitoredEquipment::where('availability_status', 'in_use')->count(),
            'equipment_maintenance' => MonitoredEquipment::where('availability_status', 'maintenance')->count(),
            'equipment_out_of_order' => MonitoredEquipment::where('availability_status', 'out_of_order')->count(),
            
            // Maintenance Statistics
            'maintenance_scheduled' => EquipmentMaintenance::where('status', 'scheduled')->count(),
            'maintenance_overdue' => EquipmentMaintenance::where('status', 'scheduled')
                ->where('scheduled_date', '<', now())->count(),
            'maintenance_completed' => EquipmentMaintenance::where('status', 'completed')->count(),
            'maintenance_this_week' => EquipmentMaintenance::where('status', 'scheduled')
                ->whereBetween('scheduled_date', [now(), now()->addDays(7)])->count(),
            
            // Recent activity and urgent items
            'recent_requests' => EquipmentRequest::where('created_at', '>=', now()->subDays(7))->count(),
            'urgent_requests' => EquipmentRequest::where('status', 'pending')
                ->whereIn('urgency_level', ['high', 'critical'])->count(),
        ];
        
        // Get users and projects for filters
        $users = User::where('role', 'sc')->where('status', 'active')->get();
        $projects = Project::where('archived', false)->get();
        
        return view('admin.equipment-monitoring.index', compact(
            'equipmentRequests', 'stats', 'users', 'projects',
            'statusFilter', 'typeFilter', 'userFilter', 'projectFilter'
        ));
    }

    // ====================================================================
    // ADMIN METHODS - PERSONAL EQUIPMENT FUNCTIONALITY (Like SC)
    // ====================================================================

    /**
     * Admin personal equipment dashboard
     */
    public function adminMyDashboard()
    {
        $user = Auth::user();
        
        // Get Admin's equipment requests
        $equipmentRequests = EquipmentRequest::where('user_id', $user->id)
            ->with(['monitoredEquipment', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get Admin's equipment
        $personalEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'personal')
            ->with(['equipmentRequest'])
            ->get();
            
        $projectEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'project_site')
            ->where('status', 'active')
            ->with(['equipmentRequest', 'project'])
            ->get();
        
        // Get upcoming maintenance
        $upcomingMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->get();
        
        // Get overdue maintenance
        $overdueMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<', now())
            ->count();
        
        // Get statistics
        $stats = [
            'pending_requests' => EquipmentRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_requests' => EquipmentRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'personal_equipment' => $personalEquipment->count(),
            'project_equipment' => $projectEquipment->count(),
            'upcoming_maintenance' => $upcomingMaintenance->count(),
            'overdue_maintenance' => $overdueMaintenance,
        ];
        
        return view('admin.equipment-monitoring.my-dashboard', compact(
            'equipmentRequests', 'personalEquipment', 'projectEquipment',
            'upcomingMaintenance', 'stats'
        ));
    }

    /**
     * Show form for Admin to create new equipment request
     */
    public function adminCreateRequest()
    {
        $user = Auth::user();
        
        // Get all projects (Admin has access to all)
        $projects = Project::where('archived', false)->get();
        
        return view('admin.equipment-monitoring.create-request', compact('projects'));
    }

    /**
     * Store new equipment request for Admin
     */
    public function adminStoreRequest(Request $request)
    {
        $user = Auth::user();
        
        // Enhanced validation
        try {
            $validated = $request->validate([
                'equipment_name' => 'required|string|max:255',
                'equipment_description' => 'required|string|max:1000',
                'usage_type' => 'required|in:personal,project_site',
                'project_id' => 'nullable|exists:projects,id|required_if:usage_type,project_site',
                'quantity' => 'required|integer|min:1|max:100',
                'estimated_cost' => 'nullable|numeric|min:0|max:999999.99',
                'justification' => 'required|string|max:1000',
                'urgency_level' => 'required|in:low,medium,high,critical',
                'additional_notes' => 'nullable|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed for admin equipment request', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'input' => $request->except(['_token'])
            ]);
            throw $e;
        }
        
        // Start transaction
        DB::beginTransaction();
        try {
            // ADMIN AUTO-APPROVAL: All admin requests are automatically approved
            $status = 'approved';
            $approvedBy = $user->id;
            $approvedAt = now();
            
            // Create equipment request
            $equipmentRequestData = [
                'user_id' => $user->id,
                'project_id' => $validated['usage_type'] === 'project_site' ? $validated['project_id'] : null,
                'equipment_name' => $validated['equipment_name'],
                'equipment_description' => $validated['equipment_description'],
                'usage_type' => $validated['usage_type'],
                'quantity' => $validated['quantity'],
                'estimated_cost' => $validated['estimated_cost'],
                'justification' => $validated['justification'],
                'urgency_level' => $validated['urgency_level'],
                'additional_notes' => $validated['additional_notes'] ?? null,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'admin_notes' => 'Auto-approved for Admin user',
            ];
            
            Log::info('Creating auto-approved admin equipment request', [
                'user_id' => $user->id,
                'usage_type' => $validated['usage_type'],
                'equipment_name' => $validated['equipment_name']
            ]);
            
            $equipmentRequest = EquipmentRequest::create($equipmentRequestData);
            
            if (!$equipmentRequest) {
                throw new \Exception('Failed to create equipment request record');
            }
            
            // Create monitored equipment entry (active for admin)
            $monitoredEquipmentData = [
                'user_id' => $user->id,
                'project_id' => $validated['usage_type'] === 'project_site' ? $validated['project_id'] : null,
                'equipment_request_id' => $equipmentRequest->id,
                'equipment_name' => $validated['equipment_name'],
                'equipment_description' => $validated['equipment_description'],
                'usage_type' => $validated['usage_type'],
                'quantity' => $validated['quantity'],
                'status' => 'active', // Admin equipment is immediately active
                'availability_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $monitoredEquipment = MonitoredEquipment::create($monitoredEquipmentData);
            
            if (!$monitoredEquipment) {
                throw new \Exception('Failed to create monitored equipment record');
            }
            
            // Update equipment request with monitored equipment ID
            $equipmentRequest->update(['monitored_equipment_id' => $monitoredEquipment->id]);
            
            DB::commit();
            
            $message = 'Equipment request created and automatically approved for Admin.';
            
            Log::info('Admin equipment request auto-approved successfully', [
                'request_id' => $equipmentRequest->id,
                'equipment_id' => $monitoredEquipment->id,
                'user_id' => $user->id,
                'usage_type' => $validated['usage_type']
            ]);
                
            return redirect()->route('admin.equipment-monitoring.my-dashboard')
                            ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error creating admin equipment request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token'])
            ]);
            
            return back()->withErrors(['error' => 'An error occurred while creating the equipment request.'])->withInput();
        }
    }

    /**
     * Show Admin's equipment requests
     */
    public function adminMyRequests(Request $request)
    {
        $user = Auth::user();
        
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('usage_type');
        
        $requestsQuery = EquipmentRequest::where('user_id', $user->id)
            ->with(['monitoredEquipment', 'project', 'approvedBy'])
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) {
            $requestsQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $requestsQuery->where('usage_type', $typeFilter);
        }
        
        $equipmentRequests = $requestsQuery->paginate(15);
        
        return view('admin.equipment-monitoring.my-requests', compact('equipmentRequests', 'statusFilter', 'typeFilter'));
    }

    /**
     * Show Admin's equipment
     */
    public function adminMyEquipment(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('usage_type');
        
        $equipmentQuery = MonitoredEquipment::where('user_id', $user->id)
            ->with(['project', 'equipmentRequest'])
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) $equipmentQuery->where('status', $statusFilter);
        if ($typeFilter) $equipmentQuery->where('usage_type', $typeFilter);
        
        $equipment = $equipmentQuery->paginate(15);
        
        return view('admin.equipment-monitoring.my-equipment', compact('equipment', 'statusFilter', 'typeFilter'));
    }

    /**
     * Show Admin's maintenance schedules
     */
    public function adminMyMaintenance(Request $request)
    {
        $user = Auth::user();
        
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('maintenance_type');
        
        $maintenanceQuery = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['monitoredEquipment.project'])
            ->orderBy('scheduled_date', 'asc');
            
        if ($statusFilter) {
            $maintenanceQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $maintenanceQuery->where('maintenance_type', $typeFilter);
        }
        
        $maintenances = $maintenanceQuery->paginate(15);
        
        return view('admin.equipment-monitoring.my-maintenance', compact('maintenances', 'statusFilter', 'typeFilter'));
    }

    /**
     * Show form for Admin to schedule maintenance
     */
public function adminCreateMaintenance()
{
    $user = Auth::user();
    
    // Get all personal equipment that is active (both admin's and site coordinators')
    $personalEquipment = MonitoredEquipment::where('usage_type', 'personal')
        ->where('status', 'active')
        ->whereIn('availability_status', ['available', 'in_use', 'maintenance'])
        ->with(['user', 'equipmentRequest'])
        ->orderBy('equipment_name')
        ->get();
        
    // Get project site equipment that is active (both admin's and site coordinators')
    $projectEquipment = MonitoredEquipment::where('usage_type', 'project_site')
        ->where('status', 'active')
        ->whereIn('availability_status', ['available', 'in_use', 'maintenance'])
        ->with(['project', 'equipmentRequest', 'user'])
        ->orderBy('equipment_name')
        ->get();
    
    return view('admin.equipment-monitoring.create-maintenance', compact('personalEquipment', 'projectEquipment'));
}

    /**
     * Store maintenance schedule for Admin
     */
public function adminStoreMaintenance(Request $request)
{
    $user = Auth::user();
    
    $request->validate([
        'monitored_equipment_id' => 'required|exists:monitored_equipment,id',
        'maintenance_type' => 'required|in:routine,repair,inspection,calibration,replacement',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'scheduled_time' => 'nullable|date_format:H:i',
        'estimated_duration' => 'required|integer|min:1|max:480',
        'description' => 'required|string|max:1000',
        'priority' => 'required|in:low,medium,high,critical',
        'notes' => 'nullable|string|max:1000',
    ]);
    
    // Verify equipment exists and is active (Admin can schedule maintenance for any equipment)
    $equipment = MonitoredEquipment::where('id', $request->monitored_equipment_id)
        ->where('status', 'active')
        ->first();
        
    if (!$equipment) {
        return back()->withErrors(['monitored_equipment_id' => 'Equipment not found or not available for maintenance.']);
    }
    
    try {
        // Combine scheduled date and time
        $scheduledDateTime = $request->scheduled_date;
        if ($request->scheduled_time) {
            $scheduledDateTime = $request->scheduled_date . ' ' . $request->scheduled_time . ':00';
        }
        
        $maintenance = EquipmentMaintenance::create([
            'monitored_equipment_id' => $request->monitored_equipment_id,
            'maintenance_type' => $request->maintenance_type,
            'scheduled_date' => $scheduledDateTime,
            'estimated_duration' => $request->estimated_duration,
            'description' => $request->description,
            'priority' => $request->priority,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);
        
        return redirect()->route('admin.equipment-monitoring.my-maintenance')
            ->with('success', 'Maintenance scheduled successfully.');
            
    } catch (\Exception $e) {
        Log::error('Failed to schedule admin maintenance', [
            'user_id' => $user->id,
            'equipment_id' => $request->monitored_equipment_id,
            'error' => $e->getMessage(),
        ]);
        
        return back()->withErrors(['error' => 'Failed to schedule maintenance. Please try again.']);
    }
}
    /**
     * Update equipment availability for Admin
     */
    public function adminUpdateAvailability(Request $request, MonitoredEquipment $monitoredEquipment)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($monitoredEquipment->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'availability_status' => 'required|in:available,in_use,maintenance,out_of_order',
        ]);
        
        try {
            $monitoredEquipment->update([
                'availability_status' => $request->availability_status,
                'last_status_update' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Equipment availability updated successfully.',
                'status' => $monitoredEquipment->availability_status,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update admin equipment availability', [
                'equipment_id' => $monitoredEquipment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['error' => 'Failed to update availability'], 500);
        }
    }

    // ====================================================================
    // EXISTING METHODS (Show specific equipment request for admin, etc.)
    // ====================================================================

    /**
     * Show specific equipment request for admin
     */
    public function adminShowRequest(EquipmentRequest $equipmentRequest)
    {
        $equipmentRequest->load(['user', 'project', 'monitoredEquipment', 'approvedBy']);
        
        return view('admin.equipment-monitoring.show-request', compact('equipmentRequest'));
    }

    /**
     * Approve equipment request
     */
    public function approveRequest(Request $request, EquipmentRequest $equipmentRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        if ($equipmentRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending requests can be approved.']);
        }
        
        DB::beginTransaction();
        try {
            // Update request status
            $equipmentRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);
            
            // Update equipment status if it exists
            if ($equipmentRequest->monitoredEquipment) {
                $equipmentRequest->monitoredEquipment->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
            }
            
            // Send notification to Site Coordinator
            $siteCoordinator = $equipmentRequest->user;
            
            if ($siteCoordinator && $siteCoordinator->role === 'sc') {
                try {
                    $siteCoordinator->notify(new EquipmentRequestApproved($equipmentRequest));
                    
                    Log::info('Equipment request approval notification sent', [
                        'request_id' => $equipmentRequest->id,
                        'admin_id' => Auth::id(),
                        'sc_id' => $siteCoordinator->id,
                        'sc_email' => $siteCoordinator->email,
                        'equipment_name' => $equipmentRequest->equipment_name,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send equipment approval notification', [
                        'request_id' => $equipmentRequest->id,
                        'sc_id' => $siteCoordinator->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Equipment request approved', [
                'request_id' => $equipmentRequest->id,
                'admin_id' => Auth::id(),
                'sc_id' => $equipmentRequest->user_id,
                'equipment_name' => $equipmentRequest->equipment_name,
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Equipment request approved successfully. Notification sent to Site Coordinator.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to approve equipment request', [
                'request_id' => $equipmentRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to approve request. Please try again.']);
        }
    }

    /**
     * Decline equipment request
     */
    public function declineRequest(Request $request, EquipmentRequest $equipmentRequest)
    {
        $request->validate([
            'decline_reason' => 'required|string|max:1000',
        ]);
        
        if ($equipmentRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending requests can be declined.']);
        }
        
        DB::beginTransaction();
        try {
            // Update request status
            $equipmentRequest->update([
                'status' => 'declined',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'decline_reason' => $request->decline_reason,
            ]);
            
            // Update equipment status if it exists
            if ($equipmentRequest->monitoredEquipment) {
                $equipmentRequest->monitoredEquipment->update([
                    'status' => 'declined',
                    'updated_at' => now(),
                ]);
            }
            
            // Send notification to Site Coordinator
            $siteCoordinator = $equipmentRequest->user;
            
            if ($siteCoordinator && $siteCoordinator->role === 'sc') {
                try {
                    $siteCoordinator->notify(new EquipmentRequestDeclined($equipmentRequest));
                    
                    Log::info('Equipment request declined notification sent', [
                        'request_id' => $equipmentRequest->id,
                        'admin_id' => Auth::id(),
                        'sc_id' => $siteCoordinator->id,
                        'sc_email' => $siteCoordinator->email,
                        'equipment_name' => $equipmentRequest->equipment_name,
                        'decline_reason' => $request->decline_reason,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send equipment declined notification', [
                        'request_id' => $equipmentRequest->id,
                        'sc_id' => $siteCoordinator->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            Log::info('Equipment request declined', [
                'request_id' => $equipmentRequest->id,
                'admin_id' => Auth::id(),
                'sc_id' => $equipmentRequest->user_id,
                'reason' => $request->decline_reason,
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Equipment request declined. Notification sent to Site Coordinator.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to decline equipment request', [
                'request_id' => $equipmentRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to decline request. Please try again.']);
        }
    }

    /**
     * Admin view of all monitored equipment
     */
    public function adminEquipmentList(Request $request)
    {
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('usage_type');
        $userFilter = $request->get('user_id');
        
        $equipmentQuery = MonitoredEquipment::with(['user', 'project', 'equipmentRequest'])
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) {
            $equipmentQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $equipmentQuery->where('usage_type', $typeFilter);
        }
        if ($userFilter) {
            $equipmentQuery->where('user_id', $userFilter);
        }
        
        $equipment = $equipmentQuery->paginate(20);
        
        $users = User::whereIn('role', ['sc', 'admin'])->where('status', 'active')->get();
        
        return view('admin.equipment-monitoring.equipment-list', compact(
            'equipment', 'users', 'statusFilter', 'typeFilter', 'userFilter'
        ));
    }

    /**
     * Admin view of all maintenance schedules
     */
    public function adminMaintenanceList(Request $request)
    {
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('maintenance_type');
        $userFilter = $request->get('user_id');
        
        $maintenanceQuery = EquipmentMaintenance::with(['monitoredEquipment.user', 'monitoredEquipment.project'])
            ->orderBy('scheduled_date', 'asc');
            
        if ($statusFilter) {
            $maintenanceQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $maintenanceQuery->where('maintenance_type', $typeFilter);
        }
        if ($userFilter) {
            $maintenanceQuery->whereHas('monitoredEquipment', function($q) use ($userFilter) {
                $q->where('user_id', $userFilter);
            });
        }
        
        $maintenances = $maintenanceQuery->paginate(20);
        
        $users = User::whereIn('role', ['sc', 'admin'])->where('status', 'active')->get();
        
        return view('admin.equipment-monitoring.maintenance-list', compact(
            'maintenances', 'users', 'statusFilter', 'typeFilter', 'userFilter'
        ));
    }

    // ====================================================================
    // SITE COORDINATOR (SC) METHODS - EXISTING
    // ====================================================================

    /**
     * SC dashboard for equipment monitoring
     */
    public function scIndex()
    {
        $user = Auth::user();
        
        // Get SC's equipment requests
        $equipmentRequests = EquipmentRequest::where('user_id', $user->id)
            ->with(['monitoredEquipment', 'project'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Get SC's equipment
        $personalEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'personal')
            ->with(['equipmentRequest'])
            ->get();
            
        $projectEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'project_site')
            ->where('status', 'active')
            ->with(['equipmentRequest', 'project'])
            ->get();
        
        // Get upcoming maintenance
        $upcomingMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date', 'asc')
            ->take(5)
            ->get();
        
        // Get overdue maintenance
        $overdueMaintenance = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'scheduled')
            ->where('scheduled_date', '<', now())
            ->count();
        
        // Get statistics
        $stats = [
            'pending_requests' => EquipmentRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_requests' => EquipmentRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
            'personal_equipment' => $personalEquipment->count(),
            'project_equipment' => $projectEquipment->count(),
            'upcoming_maintenance' => $upcomingMaintenance->count(),
            'overdue_maintenance' => $overdueMaintenance,
        ];
        
        return view('sc.equipment-monitoring.index', compact(
            'equipmentRequests', 'personalEquipment', 'projectEquipment',
            'upcomingMaintenance', 'stats'
        ));
    }

    /**
     * Show form to create new equipment request
     */
    public function scCreateRequest()
    {
        $user = Auth::user();
        
        // Get projects where SC has tasks
        $projects = Project::whereHas('tasks', function($q) use ($user) {
            $q->where('assigned_to', $user->id);
        })->where('archived', false)->get();
        
        return view('sc.equipment-monitoring.create-request', compact('projects'));
    }

    /**
     * Store new equipment request
     */
    public function scStoreRequest(Request $request)
    {
        $user = Auth::user();
        
        // Enhanced validation with better error messages
        try {
            $validated = $request->validate([
                'equipment_name' => 'required|string|max:255',
                'equipment_description' => 'required|string|max:1000',
                'usage_type' => 'required|in:personal,project_site',
                'project_id' => 'nullable|exists:projects,id|required_if:usage_type,project_site',
                'quantity' => 'required|integer|min:1|max:100',
                'estimated_cost' => 'nullable|numeric|min:0|max:999999.99',
                'justification' => 'required|string|max:1000',
                'urgency_level' => 'required|in:low,medium,high,critical',
                'additional_notes' => 'nullable|string|max:1000',
            ], [
                'equipment_name.required' => 'Equipment name is required.',
                'equipment_description.required' => 'Equipment description is required.',
                'usage_type.required' => 'Please select a usage type.',
                'project_id.required_if' => 'Project is required for project site equipment.',
                'project_id.exists' => 'Selected project does not exist.',
                'quantity.required' => 'Quantity is required.',
                'quantity.min' => 'Quantity must be at least 1.',
                'quantity.max' => 'Quantity cannot exceed 100.',
                'justification.required' => 'Justification is required.',
                'urgency_level.required' => 'Please select an urgency level.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed for equipment request', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'input' => $request->except(['_token'])
            ]);
            throw $e;
        }
        
        // Validate project access for SC if project_site
        if ($validated['usage_type'] === 'project_site' && $validated['project_id']) {
            $hasAccess = Task::where('assigned_to', $user->id)
                ->where('project_id', $validated['project_id'])
                ->where('archived', false)
                ->exists();
                
            if (!$hasAccess) {
                Log::warning('SC attempted to access unauthorized project', [
                    'user_id' => $user->id,
                    'project_id' => $validated['project_id']
                ]);
                return back()->withErrors(['project_id' => 'You do not have access to this project.'])
                             ->withInput();
            }
        }
        
        // Start transaction with better error handling
        DB::beginTransaction();
        try {
            // Determine status based on usage type
            $status = $validated['usage_type'] === 'personal' ? 'approved' : 'pending';
            $approvedBy = $validated['usage_type'] === 'personal' ? $user->id : null;
            $approvedAt = $validated['usage_type'] === 'personal' ? now() : null;
            
            // Create equipment request WITHOUT monitored_equipment_id first
            $equipmentRequestData = [
                'user_id' => $user->id,
                'project_id' => $validated['usage_type'] === 'project_site' ? $validated['project_id'] : null,
                'equipment_name' => $validated['equipment_name'],
                'equipment_description' => $validated['equipment_description'],
                'usage_type' => $validated['usage_type'],
                'quantity' => $validated['quantity'],
                'estimated_cost' => $validated['estimated_cost'],
                'justification' => $validated['justification'],
                'urgency_level' => $validated['urgency_level'],
                'additional_notes' => $validated['additional_notes'] ?? null,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ];
            
            Log::info('Creating equipment request', [
                'user_id' => $user->id,
                'usage_type' => $validated['usage_type'],
                'equipment_name' => $validated['equipment_name']
            ]);
            
            // Create equipment request first
            $equipmentRequest = EquipmentRequest::create($equipmentRequestData);
            
            if (!$equipmentRequest) {
                throw new \Exception('Failed to create equipment request record');
            }
            
            Log::info('Equipment request created successfully', [
                'request_id' => $equipmentRequest->id,
                'user_id' => $user->id
            ]);
            
            // Now create monitored equipment entry
            $monitoredEquipmentData = [
                'user_id' => $user->id,
                'project_id' => $validated['usage_type'] === 'project_site' ? $validated['project_id'] : null,
                'equipment_request_id' => $equipmentRequest->id,
                'equipment_name' => $validated['equipment_name'],
                'equipment_description' => $validated['equipment_description'],
                'usage_type' => $validated['usage_type'],
                'quantity' => $validated['quantity'],
                'status' => $validated['usage_type'] === 'personal' ? 'active' : 'pending_approval',
                'availability_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            Log::info('Creating monitored equipment', [
                'request_id' => $equipmentRequest->id,
                'equipment_name' => $validated['equipment_name']
            ]);
            
            $monitoredEquipment = MonitoredEquipment::create($monitoredEquipmentData);
            
            if (!$monitoredEquipment) {
                throw new \Exception('Failed to create monitored equipment record');
            }
            
            Log::info('Monitored equipment created successfully', [
                'equipment_id' => $monitoredEquipment->id,
                'request_id' => $equipmentRequest->id
            ]);
            
            // Update equipment request with monitored equipment ID
            $equipmentRequest->update(['monitored_equipment_id' => $monitoredEquipment->id]);
            
            // Send notifications for project site requests
            if ($validated['usage_type'] === 'project_site') {
                try {
                    $admins = User::where('role', 'admin')
                        ->where('status', 'active')
                        ->get();
                    
                    foreach ($admins as $admin) {
                        if ($admin->role !== 'client') {
                            try {
                                if (class_exists('\App\Notifications\EquipmentRequestSubmitted')) {
                                    $admin->notify(new \App\Notifications\EquipmentRequestSubmitted($equipmentRequest));
                                }
                            } catch (\Exception $e) {
                                Log::warning('Failed to send notification to admin', [
                                    'admin_id' => $admin->id,
                                    'request_id' => $equipmentRequest->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send admin notifications', [
                        'request_id' => $equipmentRequest->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            $message = $validated['usage_type'] === 'personal' 
                ? 'Personal equipment request created and automatically approved.' 
                : 'Project site equipment request submitted for admin approval.';
                
            Log::info('Equipment request process completed successfully', [
                'request_id' => $equipmentRequest->id,
                'equipment_id' => $monitoredEquipment->id,
                'user_id' => $user->id,
                'usage_type' => $validated['usage_type']
            ]);
                
            return redirect()->route('sc.equipment-monitoring.index')
                            ->with('success', $message);
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            
            Log::error('Database error creating equipment request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            
            $errorMessage = 'Database error occurred. Please try again.';
            
            if ($e->getCode() === '23000') {
                if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                    $errorMessage = 'Invalid project or user reference. Please refresh the page and try again.';
                } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $errorMessage = 'A similar equipment request already exists. Please check your existing requests.';
                } else {
                    $errorMessage = 'Data constraint violation. Please check your input and try again.';
                }
            } elseif ($e->getCode() === '42S22') {
                $errorMessage = 'Database structure issue. Please contact support.';
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('General error creating equipment request', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token', 'password'])
            ]);
            
            $errorMessage = 'An unexpected error occurred: ' . $e->getMessage();
            
            if (app()->environment('production')) {
                $errorMessage = 'An unexpected error occurred. Please try again or contact support if the issue persists.';
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    /**
     * Show SC's equipment requests
     */
    public function scRequests(Request $request)
    {
        $user = Auth::user();
        
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('usage_type');
        
        $requestsQuery = EquipmentRequest::where('user_id', $user->id)
            ->with(['monitoredEquipment', 'project', 'approvedBy'])
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) {
            $requestsQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $requestsQuery->where('usage_type', $typeFilter);
        }
        
        $equipmentRequests = $requestsQuery->paginate(15);
        
        return view('sc.equipment-monitoring.requests', compact('equipmentRequests', 'statusFilter', 'typeFilter'));
    }

    /**
     * Show form to schedule maintenance
     */
    public function scCreateMaintenance()
    {
        $user = Auth::user();
        
        $personalEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'personal')
            ->where('status', 'active')
            ->whereIn('availability_status', ['available', 'in_use', 'maintenance'])
            ->orderBy('equipment_name')
            ->get();
            
        $projectEquipment = MonitoredEquipment::where('user_id', $user->id)
            ->where('usage_type', 'project_site')
            ->where('status', 'active')
            ->whereIn('availability_status', ['available', 'in_use', 'maintenance'])
            ->where(function($query) {
                $query->whereHas('equipmentRequest', function($q) {
                    $q->where('status', 'approved');
                })->orWhereNull('equipment_request_id');
            })
            ->with(['project', 'equipmentRequest'])
            ->orderBy('equipment_name')
            ->get();
        
        Log::info('Equipment for maintenance scheduling', [
            'user_id' => $user->id,
            'personal_equipment_count' => $personalEquipment->count(),
            'project_equipment_count' => $projectEquipment->count(),
            'personal_equipment' => $personalEquipment->pluck('equipment_name', 'id'),
            'project_equipment' => $projectEquipment->pluck('equipment_name', 'id'),
        ]);
        
        return view('sc.equipment-monitoring.create-maintenance', compact('personalEquipment', 'projectEquipment'));
    }

    /**
     * Store maintenance schedule
     */
    public function scStoreMaintenance(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'monitored_equipment_id' => 'required|exists:monitored_equipment,id',
            'maintenance_type' => 'required|in:routine,repair,inspection,calibration,replacement',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'required|integer|min:1|max:480',
            'description' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Verify equipment belongs to SC
        $equipment = MonitoredEquipment::where('id', $request->monitored_equipment_id)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$equipment) {
            return back()->withErrors(['monitored_equipment_id' => 'Equipment not found or access denied.']);
        }
        
        // Additional check for project site equipment
        if ($equipment->usage_type === 'project_site') {
            $hasApprovedRequest = $equipment->equipmentRequest && $equipment->equipmentRequest->status === 'approved';
            if (!$hasApprovedRequest) {
                return back()->withErrors(['monitored_equipment_id' => 'Can only schedule maintenance for approved project site equipment.']);
            }
        }
        
        try {
            // Combine scheduled date and time
            $scheduledDateTime = $request->scheduled_date;
            if ($request->scheduled_time) {
                $scheduledDateTime = $request->scheduled_date . ' ' . $request->scheduled_time . ':00';
            }
            
            $maintenance = EquipmentMaintenance::create([
                'monitored_equipment_id' => $request->monitored_equipment_id,
                'maintenance_type' => $request->maintenance_type,
                'scheduled_date' => $scheduledDateTime,
                'estimated_duration' => $request->estimated_duration,
                'description' => $request->description,
                'priority' => $request->priority,
                'notes' => $request->notes,
                'status' => 'scheduled',
            ]);
            
            return redirect()->route('sc.equipment-monitoring.maintenance')
                ->with('success', 'Maintenance scheduled successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to schedule maintenance', [
                'user_id' => $user->id,
                'equipment_id' => $request->monitored_equipment_id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to schedule maintenance. Please try again.']);
        }
    }

    /**
     * Show SC's maintenance schedules
     */
    public function scMaintenance(Request $request)
    {
        $user = Auth::user();
        
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('maintenance_type');
        
        $maintenanceQuery = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['monitoredEquipment.project'])
            ->orderBy('scheduled_date', 'asc');
            
        if ($statusFilter) {
            $maintenanceQuery->where('status', $statusFilter);
        }
        if ($typeFilter) {
            $maintenanceQuery->where('maintenance_type', $typeFilter);
        }
        
        $maintenances = $maintenanceQuery->paginate(15);
        
        return view('sc.equipment-monitoring.maintenance', compact('maintenances', 'statusFilter', 'typeFilter'));
    }

    /**
     * Update equipment availability
     */
    public function scUpdateAvailability(Request $request, MonitoredEquipment $monitoredEquipment)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($monitoredEquipment->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $request->validate([
            'availability_status' => 'required|in:available,in_use,maintenance,out_of_order',
        ]);
        
        try {
            $monitoredEquipment->update([
                'availability_status' => $request->availability_status,
                'last_status_update' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Equipment availability updated successfully.',
                'status' => $monitoredEquipment->availability_status,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update equipment availability', [
                'equipment_id' => $monitoredEquipment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['error' => 'Failed to update availability'], 500);
        }
    }

    // ====================================================================
    // PROJECT MANAGER (PM) METHODS - VIEW ONLY
    // ====================================================================

    /**
     * PM view of equipment monitoring data
     */
    public function pmIndex(Request $request)
    {
        $user = Auth::user();
        
        // Get projects managed by PM
        $managedProjects = $user->getManagedProjects();
        $managedProjectIds = $managedProjects->pluck('id')->toArray();
        
        $statusFilter = $request->get('status');
        $projectFilter = $request->get('project_id');
        
        // Get equipment requests for managed projects
        $requestsQuery = EquipmentRequest::with(['user', 'project', 'monitoredEquipment'])
            ->where(function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds)
                  ->orWhere('usage_type', 'personal');
            })
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) {
            $requestsQuery->where('status', $statusFilter);
        }
        if ($projectFilter && in_array($projectFilter, $managedProjectIds)) {
            $requestsQuery->where('project_id', $projectFilter);
        }
        
        $equipmentRequests = $requestsQuery->paginate(20);
        
        // Get statistics
        $stats = [
            'total_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->count(),
            'pending_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'pending')->count(),
            'approved_requests' => EquipmentRequest::whereIn('project_id', $managedProjectIds)->where('status', 'approved')->count(),
            'total_equipment' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->count(),
            'active_equipment' => MonitoredEquipment::whereIn('project_id', $managedProjectIds)->where('status', 'active')->count(),
            'scheduled_maintenance' => EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })->where('status', 'scheduled')->count(),
        ];
        
        return view('pm.equipment-monitoring.index', compact(
            'equipmentRequests', 'stats', 'managedProjects',
            'statusFilter', 'projectFilter'
        ));
    }

    /**
     * PM view of equipment list
     */
    public function pmEquipmentList(Request $request)
    {
        $user = Auth::user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        
        $statusFilter = $request->get('status');
        $projectFilter = $request->get('project_id');
        
        $equipmentQuery = MonitoredEquipment::with(['user', 'project', 'equipmentRequest'])
            ->whereIn('project_id', $managedProjectIds)
            ->orderBy('created_at', 'desc');
            
        if ($statusFilter) {
            $equipmentQuery->where('status', $statusFilter);
        }
        if ($projectFilter && in_array($projectFilter, $managedProjectIds)) {
            $equipmentQuery->where('project_id', $projectFilter);
        }
        
        $equipment = $equipmentQuery->paginate(20);
        $managedProjects = $user->getManagedProjects();
        
        return view('pm.equipment-monitoring.equipment-list', compact(
            'equipment', 'managedProjects', 'statusFilter', 'projectFilter'
        ));
    }

    /**
     * PM view of maintenance schedules
     */
    public function pmMaintenanceList(Request $request)
    {
        $user = Auth::user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
        
        $statusFilter = $request->get('status');
        $projectFilter = $request->get('project_id');
        
        $maintenanceQuery = EquipmentMaintenance::whereHas('monitoredEquipment', function($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            })
            ->with(['monitoredEquipment.user', 'monitoredEquipment.project'])
            ->orderBy('scheduled_date', 'asc');
            
        if ($statusFilter) {
            $maintenanceQuery->where('status', $statusFilter);
        }
        if ($projectFilter && in_array($projectFilter, $managedProjectIds)) {
            $maintenanceQuery->whereHas('monitoredEquipment', function($q) use ($projectFilter) {
                $q->where('project_id', $projectFilter);
            });
        }
        
        $maintenances = $maintenanceQuery->paginate(20);
        $managedProjects = $user->getManagedProjects();
        
        return view('pm.equipment-monitoring.maintenance-list', compact(
            'maintenances', 'managedProjects', 'statusFilter', 'projectFilter'
        ));
    }
}