<?php

namespace App\Http\Controllers;

use App\Models\LiquidatedForm;
use App\Models\FinancialReport;
use App\Models\Project;
use App\Models\User;
use App\Models\DailyExpenditure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiquidatedFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:finance')->only(['financeIndex', 'financeShow', 'financeEdit', 'financeUpdate', 'financeExportCSV', 'flaggedReport']);
        $this->middleware('role:pm')->only(['pmIndex', 'pmShow']);
        $this->middleware('role:admin')->only([
            'adminIndex', 'adminShow', 'adminEdit', 'adminUpdate', 'adminDestroy', 
            'requestRevision', 'requestRevisionForm', 'approveRevision', 'approveRevisionForm',
            'requestClarification', 'requestClarificationForm', 'acceptClarification', 'acceptClarificationForm',
            'adminFlag', 'adminFlagForm', 'adminUnflag', 'adminUnflagForm',
            'revisionHistory', 'showRevision', 'adminBulkAction'
        ]);
        // Allow both finance and admin to access flag functionality and suspicious activities
        $this->middleware('role:finance,admin')->only([
            'flag', 'unflag', 'bulkFlag', 'bulkUnflag', 'suspiciousActivities',
            'flagForm', 'unflagForm', 'print', 'markPrinted', 'bulkPrint'
        ]);
    }

    // Finance Methods
    public function financeIndex(Request $request)
    {
        $query = LiquidatedForm::with(['financialReport', 'project', 'preparer', 'reviewer']);

        // Debug: Log the user and their role
        $user = auth()->user();
        \Log::info('Finance index accessed by user: ' . $user->id . ' (' . $user->email . ') with role: ' . $user->role);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('preparer_id')) {
            $query->where('prepared_by', $request->preparer_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('liquidation_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('period_from') && $request->filled('period_to')) {
            $query->whereBetween('period_covered_start', [$request->period_from, $request->period_to])
                  ->orWhereBetween('period_covered_end', [$request->period_from, $request->period_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('form_number', 'like', "%{$search}%");
            });
        }

        $liquidatedForms = $query->orderBy('created_at', 'desc')
                                ->paginate($request->get('per_page', 15));

        // Debug: Log the forms being loaded
        foreach ($liquidatedForms as $form) {
            \Log::info('Form ID: ' . $form->id . ', Status: ' . $form->status . ', Can be flagged: ' . ($form->canBeFlagged() ? 'YES' : 'NO'));
        }

        $projects = Project::all();
        $preparers = User::whereIn('role', ['finance', 'admin'])->get();

        return view('finance.liquidated-forms.index', compact('liquidatedForms', 'projects', 'preparers'));
    }

    public function suspiciousActivities(Request $request)
    {
        // Get ALL flagged forms and pending forms that could be flagged
        $suspiciousForms = LiquidatedForm::with(['project', 'preparer', 'financialReport'])
            ->whereIn('status', ['flagged', 'pending']) // Show all flagged and pending forms
            ->orderBy('status', 'desc') // Show flagged forms first
            ->orderBy('variance_amount', 'desc')
            ->get();

        // Calculate statistics for flagged and pending forms only
        $highVarianceCount = LiquidatedForm::whereIn('status', ['flagged', 'pending'])
            ->whereRaw('(total_amount > 0 AND ABS(variance_amount) > (total_amount * 0.2))')->count();
        $missingReceiptsCount = LiquidatedForm::whereIn('status', ['flagged', 'pending'])
            ->whereRaw('(total_amount > 0 AND (total_receipts / total_amount) < 0.5)')->count();
        $unusualPatternsCount = LiquidatedForm::whereIn('status', ['flagged', 'pending'])
            ->where('variance_amount', '<', 0)->count();
        $flaggedCount = LiquidatedForm::where('status', 'flagged')->count();

        return view('finance.liquidated-forms.flag-suspicious', compact(
            'suspiciousForms',
            'highVarianceCount',
            'missingReceiptsCount',
            'unusualPatternsCount',
            'flaggedCount'
        ));
    }

    public function financeShow(LiquidatedForm $liquidatedForm)
    {
        $liquidatedForm->load([
            'financialReport',
            'project',
            'preparer',
            'reviewer',
            'flaggedBy',
            'clarificationRequestedBy',
            'printedBy',
            'expenditures.submitter',
            'expenditures.receipts',
            'revisions.requester',
            'revisions.addressedBy'
        ]);

        return view('finance.liquidated-forms.show', compact('liquidatedForm'));
    }

    public function financeEdit(LiquidatedForm $liquidatedForm)
    {
        if (!$liquidatedForm->canBeEdited()) {
            return redirect()->route('finance.liquidated-forms.index')
                ->with('error', 'This form cannot be edited');
        }

        $liquidatedForm->load(['project', 'expenditures']);
        $projects = Project::all();
        $expenditures = DailyExpenditure::where('status', 'approved')->get();

        return view('finance.liquidated-forms.edit', compact('liquidatedForm', 'projects', 'expenditures'));
    }

    public function financeUpdate(Request $request, LiquidatedForm $liquidatedForm)
    {
        if (!$liquidatedForm->canBeEdited()) {
            return redirect()->route('finance.liquidated-forms.index')
                ->with('error', 'This form cannot be edited');
        }

        $validator = Validator::make($request->all(), [
            'form_number' => 'required|string|max:255|unique:liquidated_forms,form_number,' . $liquidatedForm->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'liquidation_date' => 'required|date',
            'period_covered_start' => 'required|date',
            'period_covered_end' => 'required|date|after_or_equal:period_covered_start',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $liquidatedForm->update($request->all());

        return redirect()->route('finance.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Liquidated form updated successfully');
    }

    public function flagForm(LiquidatedForm $liquidatedForm)
    {
        // GET method to show flag form
        return view('finance.liquidated-forms.flag', compact('liquidatedForm'));
    }

    public function flag(Request $request, LiquidatedForm $liquidatedForm)
    {
        try {
            \Log::info('=== FLAG REQUEST START ===');
            \Log::info('Flag request received for liquidated form: ' . $liquidatedForm->id);
            \Log::info('Request data: ' . json_encode($request->all()));
            \Log::info('User: ' . auth()->user()->email . ' (Role: ' . auth()->user()->role . ')');
            \Log::info('Form status: ' . $liquidatedForm->status);
            \Log::info('Can be flagged: ' . ($liquidatedForm->canBeFlagged() ? 'YES' : 'NO'));

            $validator = Validator::make($request->all(), [
                'flag_reason' => 'required|string|max:1000',
                'flag_priority' => 'nullable|in:low,medium,high,critical',
                'flag_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed: ' . json_encode($validator->errors()));
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user = Auth::user();
            \Log::info('User attempting to flag: ' . $user->id . ' (' . $user->email . ')');

            if (!$liquidatedForm->canBeFlagged()) {
                \Log::warning('Form cannot be flagged. Status: ' . $liquidatedForm->status);
                
                return redirect()->back()
                    ->with('error', 'This form cannot be flagged. Current status: ' . $liquidatedForm->status);
            }

            $result = $liquidatedForm->flag($user, $request->flag_reason, $request->flag_priority ?? 'medium', $request->flag_notes);

            if (!$result) {
                \Log::error('Flag method returned false');
                
                return redirect()->back()
                    ->with('error', 'Failed to flag the form');
            }

            // Send notification to Admin when Finance flags a form
            if ($user->role === 'finance') {
                $notificationData = [
                    'form_number' => $liquidatedForm->form_number,
                    'flagger_name' => $user->name,
                    'flag_reason' => $request->flag_reason,
                    'flag_priority' => $request->flag_priority ?? 'medium',
                    'view_url' => route('admin.liquidated-forms.show', $liquidatedForm->id)
                ];

                \App\Notifications\ExpenseLiquidationNotification::sendToAdmin('liquidated_form_flagged', $notificationData);
            }

            \Log::info('Form flagged successfully');
            \Log::info('=== FLAG REQUEST END ===');
            
            return redirect()->route('finance.liquidated-forms.show', $liquidatedForm)
                ->with('success', 'Liquidated form flagged successfully');
        } catch (\Exception $e) {
            \Log::error('=== FLAG REQUEST ERROR ===');
            \Log::error('Error flagging liquidated form: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== FLAG REQUEST ERROR END ===');
            
            return redirect()->back()
                ->with('error', 'An error occurred while flagging the form: ' . $e->getMessage());
        }
    }

    public function unflagForm(LiquidatedForm $liquidatedForm)
    {
        // GET method to confirm unflag action
        return view('finance.liquidated-forms.unflag', compact('liquidatedForm'));
    }

    public function unflag(Request $request, LiquidatedForm $liquidatedForm)
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission to unflag (finance or admin)
            if (!in_array($user->role, ['finance', 'admin'])) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to unflag forms');
            }

            if ($liquidatedForm->status !== 'flagged') {
                return redirect()->back()
                    ->with('error', 'This form is not flagged. Current status: ' . $liquidatedForm->status);
            }

            $result = $liquidatedForm->unflag();

            if (!$result) {
                return redirect()->back()
                    ->with('error', 'Failed to unflag the form');
            }

            // Log the unflag reason if provided
            if ($request->filled('unflag_reason')) {
                \Log::info('Form unflagged with reason', [
                    'form_id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'unflagged_by' => $user->id,
                    'unflag_reason' => $request->unflag_reason
                ]);
            }

            return redirect()->route('finance.liquidated-forms.show', $liquidatedForm)
                ->with('success', 'Liquidated form unflagged successfully');
        } catch (\Exception $e) {
            \Log::error('Error unflagging liquidated form: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'An error occurred while unflagging the form');
        }
    }

    public function bulkFlag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'liquidated_form_ids' => 'required|array',
            'liquidated_form_ids.*' => 'exists:liquidated_forms,id',
            'flag_reason' => 'required|string|max:1000',
            'flag_priority' => 'nullable|in:low,medium,high,critical'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        // Check if user has permission to flag (finance or admin)
        if (!in_array($user->role, ['finance', 'admin'])) {
            return redirect()->back()
                ->with('error', 'You do not have permission to flag forms');
        }

        // Get forms that can be flagged (pending, under_review, approved)
        $liquidatedForms = LiquidatedForm::whereIn('id', $request->liquidated_form_ids)
                                        ->whereIn('status', ['pending', 'under_review', 'approved'])
                                        ->get();

        $flagged = 0;
        $failed = 0;
        $results = [];
        
        foreach ($liquidatedForms as $liquidatedForm) {
            if ($liquidatedForm->flag($user, $request->flag_reason, $request->flag_priority ?? 'medium')) {
                $flagged++;
                $results[] = [
                    'form_id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'status' => 'success'
                ];
            } else {
                $failed++;
                $results[] = [
                    'form_id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'status' => 'failed',
                    'reason' => 'Form could not be flagged'
                ];
            }
        }

        // Send notification to Admin when Finance bulk flags forms
        if ($user->role === 'finance' && $flagged > 0) {
            $notificationData = [
                'action' => 'bulk_flag',
                'total_items' => count($request->liquidated_form_ids),
                'successful_count' => $flagged,
                'failed_count' => $failed,
                'success_rate' => round(($flagged / count($request->liquidated_form_ids)) * 100, 2),
                'flag_reason' => $request->flag_reason,
                'flag_priority' => $request->flag_priority ?? 'medium'
            ];

            \App\Notifications\ExpenseLiquidationNotification::sendToAdmin('bulk_action_completed', $notificationData);
        }

        return redirect()->back()
            ->with('success', "Successfully flagged {$flagged} forms" . ($failed > 0 ? " ({$failed} failed)" : ''));
    }

    public function bulkUnflag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'liquidated_form_ids' => 'required|array',
            'liquidated_form_ids.*' => 'exists:liquidated_forms,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        // Check if user has permission to unflag (finance or admin)
        if (!in_array($user->role, ['finance', 'admin'])) {
            return redirect()->back()
                ->with('error', 'You do not have permission to unflag forms');
        }

        $liquidatedForms = LiquidatedForm::whereIn('id', $request->liquidated_form_ids)
                                        ->where('status', 'flagged')
                                        ->get();

        $unflagged = 0;
        $failed = 0;
        $results = [];
        
        foreach ($liquidatedForms as $liquidatedForm) {
            if ($liquidatedForm->unflag()) {
                $unflagged++;
                $results[] = [
                    'form_id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'status' => 'success'
                ];
            } else {
                $failed++;
                $results[] = [
                    'form_id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'status' => 'failed',
                    'reason' => 'Form could not be unflagged'
                ];
            }
        }

        return redirect()->back()
            ->with('success', "Successfully unflagged {$unflagged} forms" . ($failed > 0 ? " ({$failed} failed)" : ''));
    }

    public function print(LiquidatedForm $liquidatedForm)
    {
        if (!$liquidatedForm->canBePrinted()) {
            return redirect()->route('finance.liquidated-forms.index')
                ->with('error', 'This form cannot be printed');
        }

        $liquidatedForm->load([
            'financialReport',
            'project',
            'preparer',
            'reviewer',
            'flaggedBy',
            'printedBy',
            'expenditures.submitter',
            'expenditures.receipts'
        ]);

        return view('finance.liquidated-forms.print', compact('liquidatedForm'));
    }

    public function markPrinted(LiquidatedForm $liquidatedForm)
    {
        return $this->print($liquidatedForm);
    }

    public function bulkPrint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'liquidated_form_ids' => 'required|array',
            'liquidated_form_ids.*' => 'exists:liquidated_forms,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $liquidatedForms = LiquidatedForm::whereIn('id', $request->liquidated_form_ids)
                                        ->where('status', 'pending')
                                        ->get();

        $printed = 0;
        foreach ($liquidatedForms as $liquidatedForm) {
            if ($liquidatedForm->markPrinted($user)) {
                $printed++;
            }
        }

        return redirect()->back()
            ->with('success', "Successfully marked {$printed} forms as printed");
    }

    public function financeExportCSV(Request $request): JsonResponse
    {
        $query = LiquidatedForm::with(['financialReport', 'project', 'preparer', 'reviewer']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('liquidation_date', [$request->date_from, $request->date_to]);
        }

        $liquidatedForms = $query->orderBy('created_at', 'desc')->get();

        $filename = 'liquidated_forms_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Generate CSV content
        $csvContent = "Form Number,Title,Project,Preparer,Status,Liquidation Date,Period Covered,Total Amount,Total Receipts,Variance,Flagged,Printed\n";
        
        foreach ($liquidatedForms as $form) {
            $csvContent .= implode(',', [
                $form->form_number,
                '"' . str_replace('"', '""', $form->title) . '"',
                $form->project ? $form->project->name : 'N/A',
                $form->preparer ? $form->preparer->name : 'N/A',
                $form->status,
                $form->liquidation_date->format('Y-m-d'),
                $form->formatted_period,
                $form->total_amount,
                $form->total_receipts,
                $form->variance_amount,
                $form->is_flagged ? 'Yes' : 'No',
                $form->is_printed ? 'Yes' : 'No'
            ]) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function flaggedReport(Request $request)
    {
        $query = LiquidatedForm::with(['project', 'preparer', 'flaggedBy'])
                               ->where('status', 'flagged');

        // Apply filters
        if ($request->filled('flag_priority')) {
            $query->where('flag_priority', $request->flag_priority);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('flagged_by')) {
            $query->where('flagged_by', $request->flagged_by);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('flagged_at', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('amount_min')) {
            $query->where('total_amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total_amount', '<=', $request->amount_max);
        }

        $flaggedForms = $query->orderBy('flagged_at', 'desc')->paginate(15);

        // Summary statistics
        $flaggedFormsCount = LiquidatedForm::where('status', 'flagged')->count();
        $highPriorityCount = LiquidatedForm::where('status', 'flagged')
            ->where('flag_priority', 'high')
            ->orWhere('flag_priority', 'critical')
            ->count();
        $totalAmountFlagged = LiquidatedForm::where('status', 'flagged')->sum('total_amount');
        $resolvedThisMonth = LiquidatedForm::where('status', '!=', 'flagged')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->where('updated_at', '<=', now()->endOfMonth())
            ->count();

        // Data for charts
        $flagAnalysis = [
            'by_priority' => LiquidatedForm::where('status', 'flagged')
                ->selectRaw('flag_priority, count(*) as count')
                ->groupBy('flag_priority')
                ->pluck('count', 'flag_priority')
                ->toArray(),
            'by_project' => LiquidatedForm::where('status', 'flagged')
                ->with('project')
                ->get()
                ->groupBy('project.name')
                ->map(function($group) {
                    return $group->count();
                })
                ->toArray()
        ];

        $projects = Project::all();
        $users = User::all();

        return view('finance.liquidated-forms.reports.flagged', compact(
            'flaggedForms', 
            'flaggedFormsCount', 
            'highPriorityCount', 
            'totalAmountFlagged', 
            'resolvedThisMonth',
            'flagAnalysis',
            'projects',
            'users'
        ));
    }



    // Admin Methods
    public function adminIndex(Request $request)
    {
        $query = LiquidatedForm::with(['financialReport', 'project', 'preparer', 'reviewer']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('preparer_id')) {
            $query->where('prepared_by', $request->preparer_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('liquidation_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('form_number', 'like', "%{$search}%");
            });
        }

        $liquidatedForms = $query->orderBy('created_at', 'desc')
                                ->paginate($request->get('per_page', 15));

        $statusOptions = LiquidatedForm::getStatusOptions();
        $projects = Project::all();
        $preparers = User::all();

        return view('admin.liquidated-forms.index', compact('liquidatedForms', 'statusOptions', 'projects', 'preparers'));
    }

    public function adminShow(LiquidatedForm $liquidatedForm)
    {
        $liquidatedForm->load([
            'financialReport',
            'financialReport.directReceipts',
            'project',
            'preparer',
            'reviewer',
            'flaggedBy',

            'printedBy',
            'expenditures.submitter',
            'expenditures.receipts',
            'revisions.requester',
            'revisions.addressedBy'
        ]);

        return view('admin.liquidated-forms.show', compact('liquidatedForm'));
    }

    public function adminEdit(LiquidatedForm $liquidatedForm)
    {
        $liquidatedForm->load(['project', 'expenditures']);
        $statusOptions = LiquidatedForm::getStatusOptions();
        $projects = Project::all();
        $users = User::all();

        return view('admin.liquidated-forms.edit', compact('liquidatedForm', 'statusOptions', 'projects', 'users'));
    }

    public function adminCreate()
    {
        $statusOptions = LiquidatedForm::getStatusOptions();
        $projects = Project::all();
        $users = User::all();

        return view('admin.liquidated-forms.create', compact('statusOptions', 'projects', 'users'));
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'prepared_by' => 'required|exists:users,id',
            'liquidation_date' => 'required|date',
            'period_covered_start' => 'required|date',
            'period_covered_end' => 'required|date|after_or_equal:period_covered_start',
            'total_amount' => 'required|numeric|min:0.01',
            'total_receipts' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_keys(LiquidatedForm::getStatusOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $liquidatedForm = LiquidatedForm::create($request->all());

        // Recalculate variance
        $liquidatedForm->recalculateTotals();

        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Liquidated form created successfully');
    }

    public function adminUpdate(Request $request, LiquidatedForm $liquidatedForm)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'prepared_by' => 'required|exists:users,id',
            'liquidation_date' => 'required|date',
            'period_covered_start' => 'required|date',
            'period_covered_end' => 'required|date|after_or_equal:period_covered_start',
            'total_amount' => 'required|numeric|min:0.01',
            'total_receipts' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_keys(LiquidatedForm::getStatusOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $liquidatedForm->update($request->all());

        // Recalculate variance
        $liquidatedForm->recalculateTotals();

        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Liquidated form updated successfully');
    }

    public function adminDestroy(LiquidatedForm $liquidatedForm)
    {
        $liquidatedForm->delete();

        return redirect()->route('admin.liquidated-forms.index')
            ->with('success', 'Liquidated form deleted successfully');
    }

    public function requestRevision(Request $request, LiquidatedForm $liquidatedForm)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (!$liquidatedForm->canRequestRevision()) {
            return redirect()->back()
                ->with('error', 'This form cannot be requested for revision. Only pending or flagged forms can be revised. Current status: ' . $liquidatedForm->status);
        }

        $result = $liquidatedForm->requestRevision($user, $request->reason, $request->revision_notes ?? null);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to request revision');
        }

        // Send notification to finance users
        $notificationData = [
            'form_number' => $liquidatedForm->form_number,
            'requester_name' => $user->name,
            'revision_reason' => $request->reason,
            'view_url' => route('finance.liquidated-forms.show', $liquidatedForm->id)
        ];

        // Send notification to all finance users
        \App\Notifications\ExpenseLiquidationNotification::sendToFinance('revision_requested', $notificationData);

        $statusMessage = $liquidatedForm->status === 'revision_requested' 
            ? 'Revision requested successfully. The form status has been updated to "Revision Requested". Finance team has been notified.'
            : 'Revision requested successfully. The preparer has been notified. Finance team has been notified.';
            
        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', $statusMessage);
    }





    public function adminUnflag(Request $request, LiquidatedForm $liquidatedForm)
    {
        if ($liquidatedForm->status !== 'flagged') {
            return redirect()->back()
                ->with('error', 'This form is not flagged. Current status: ' . $liquidatedForm->status);
        }

        $result = $liquidatedForm->unflag();

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to unflag the form');
        }

        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Liquidated form unflagged successfully');
    }



    public function adminBulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selected_forms' => 'required|array',
            'selected_forms.*' => 'exists:liquidated_forms,id',
            'action' => 'required|in:delete,flag,export,request-revision',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = Auth::user();
        $liquidatedForms = LiquidatedForm::whereIn('id', $request->selected_forms)->get();
        $processed = 0;

        switch ($request->action) {
            case 'delete':
                foreach ($liquidatedForms as $liquidatedForm) {
                    $liquidatedForm->delete();
                    $processed++;
                }
                $message = "Successfully deleted {$processed} liquidated form(s)";
                break;

            case 'flag':
                foreach ($liquidatedForms as $liquidatedForm) {
                    if ($liquidatedForm->flag($user, $request->notes)) {
                        $processed++;
                    }
                }
                $message = "Successfully flagged {$processed} liquidated form(s)";
                break;



            case 'request-revision':
                foreach ($liquidatedForms as $liquidatedForm) {
                    if ($liquidatedForm->canRequestRevision()) {
                        if ($liquidatedForm->requestRevision($user, $request->notes ?? 'Bulk revision request by admin')) {
                            $processed++;
                            
                            // Send notification to finance users for each form
                            $notificationData = [
                                'form_number' => $liquidatedForm->form_number,
                                'requester_name' => $user->name,
                                'revision_reason' => $request->notes ?? 'Bulk revision request by admin',
                                'view_url' => route('finance.liquidated-forms.show', $liquidatedForm->id)
                            ];
                            \App\Notifications\ExpenseLiquidationNotification::sendToFinance('revision_requested', $notificationData);
                        }
                    }
                }
                $message = "Successfully requested revision for {$processed} liquidated form(s). Finance team has been notified.";
                break;

            case 'export':
                // Handle export logic here
                $message = "Export functionality will be implemented";
                break;

            default:
                $message = "No action performed";
        }

        return redirect()->route('admin.liquidated-forms.index')
            ->with('success', $message);
    }







    private function getStatusColor($status): string
    {
        $colors = [
            'pending' => 'warning',
            'flagged' => 'danger'
        ];

        return $colors[$status] ?? 'secondary';
    }

    // Admin Form Methods for GET requests
    public function requestRevisionForm(LiquidatedForm $liquidatedForm)
    {
        return view('admin.liquidated-forms.request-revision', compact('liquidatedForm'));
    }

    public function approveRevisionForm(LiquidatedForm $liquidatedForm)
    {
        return view('admin.liquidated-forms.approve-revision', compact('liquidatedForm'));
    }



    public function adminFlagForm(LiquidatedForm $liquidatedForm)
    {
        return view('admin.liquidated-forms.admin-flag', compact('liquidatedForm'));
    }

    public function adminUnflagForm(LiquidatedForm $liquidatedForm)
    {
        return view('admin.liquidated-forms.admin-unflag', compact('liquidatedForm'));
    }

    // Missing POST methods
    public function approveRevision(Request $request, LiquidatedForm $liquidatedForm)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string|max:1000',
            'approved_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = Auth::user();
        
        if (!$liquidatedForm->canApproveRevision()) {
            return redirect()->back()
                ->with('error', 'This form cannot be approved for revision at this time.');
        }

        $result = $liquidatedForm->approveRevision($user, $request->approval_notes, $request->approved_amount);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to approve revision');
        }

        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Revision approved successfully');
    }



    public function adminFlag(Request $request, LiquidatedForm $liquidatedForm)
    {
        $validator = Validator::make($request->all(), [
            'flag_reason' => 'required|string|max:1000',
            'flag_priority' => 'required|in:low,medium,high,critical'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = Auth::user();
        
        if ($liquidatedForm->status === 'flagged') {
            return redirect()->back()
                ->with('error', 'This form is already flagged');
        }

        $result = $liquidatedForm->flag($user, $request->flag_reason, $request->flag_priority);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to flag the form');
        }

        return redirect()->route('admin.liquidated-forms.show', $liquidatedForm)
            ->with('success', 'Liquidated form flagged successfully');
    }

    public function revisionHistory(LiquidatedForm $liquidatedForm)
    {
        $revisions = $liquidatedForm->revisions()->with(['createdBy', 'approvedBy'])->orderBy('created_at', 'desc')->get();
        
        return view('admin.liquidated-forms.revision-history', compact('liquidatedForm', 'revisions'));
    }

    public function showRevision(LiquidatedForm $liquidatedForm, $revisionId)
    {
        $revision = $liquidatedForm->revisions()->with(['createdBy', 'approvedBy'])->findOrFail($revisionId);
        
        return view('admin.liquidated-forms.show-revision', compact('liquidatedForm', 'revision'));
    }



    // ====================================================================
    // PROJECT MANAGER METHODS
    // ====================================================================

    public function pmIndex(Request $request)
    {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        $query = LiquidatedForm::with(['financialReport', 'project', 'preparer', 'reviewer'])
            ->whereIn('project_id', $managedProjectIds);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('preparer_id')) {
            $query->where('prepared_by', $request->preparer_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('liquidation_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('period_from') && $request->filled('period_to')) {
            $query->whereBetween('period_covered_start', [$request->period_from, $request->period_to])
                  ->orWhereBetween('period_covered_end', [$request->period_from, $request->period_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('form_number', 'like', "%{$search}%");
            });
        }

        $liquidatedForms = $query->orderBy('created_at', 'desc')
                                ->paginate($request->get('per_page', 15));

        $projects = Project::whereIn('id', $managedProjectIds)->get();
        $preparers = User::whereIn('role', ['finance', 'admin'])->get();

        return view('pm.liquidated-forms.index', compact('liquidatedForms', 'projects', 'preparers'));
    }

    public function pmShow(LiquidatedForm $liquidatedForm)
    {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        // Check if PM has access to this form's project
        if (!in_array($liquidatedForm->project_id, $managedProjectIds)) {
            abort(403, 'Access denied. You can only view liquidated forms for projects you manage.');
        }

        $liquidatedForm->load(['financialReport', 'financialReport.directReceipts', 'project', 'preparer', 'reviewer', 'flaggedBy', 'printedBy', 'clarificationRequestedBy', 'expenditures', 'receipts', 'revisions']);

        return view('pm.liquidated-forms.show', compact('liquidatedForm'));
    }





}
