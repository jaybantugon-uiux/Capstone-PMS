<?php

namespace App\Http\Controllers;

use App\Models\DailyExpenditure;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DailyExpenditureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:pm')->only(['pmIndex', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'submit', 'bulkSubmit', 'bulkDelete', 'export', 'exportCSV', 'exportPDF', 'summaryReport', 'monthlyReport']);
        $this->middleware('role:finance')->only(['financeIndex', 'financeShow', 'financeExportCSV']);
        $this->middleware('role:admin')->only(['adminIndex', 'adminCreate', 'adminStore', 'adminShow', 'adminEdit', 'adminUpdate', 'adminDestroy']);
    }

    // Project Manager Methods
    public function pmIndex(Request $request)
    {
        $user = Auth::user();
        $query = DailyExpenditure::with(['project', 'submitter'])
                                ->where('submitted_by', $user->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor_supplier', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenditures = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        // Calculate statistics
        $stats = [
            'total' => DailyExpenditure::where('submitted_by', $user->id)->count(),
            'draft' => DailyExpenditure::where('submitted_by', $user->id)->where('status', 'draft')->count(),
            'submitted' => DailyExpenditure::where('submitted_by', $user->id)->where('status', 'submitted')->count(),
            'total_amount' => DailyExpenditure::where('submitted_by', $user->id)->sum('amount')
        ];

        return view('pm.expenditures.index', [
            'expenditures' => $expenditures,
            'stats' => $stats,
            'categories' => DailyExpenditure::getCategoryOptions(),
            'projects' => Project::where('created_by', $user->id)->get()
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        
        return view('pm.expenditures.create', [
            'categories' => DailyExpenditure::getCategoryOptions(),
            'paymentMethods' => DailyExpenditure::getPaymentMethodOptions(),
            'projects' => Project::where('created_by', $user->id)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getCategoryOptions())),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'location' => 'nullable|string|max:255',
            'vendor_supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getPaymentMethodOptions())),
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        // Determine status based on action
        $status = $request->input('action') === 'submit' ? 'submitted' : 'draft';
        
        $expenditure = DailyExpenditure::create([
            'project_id' => $request->project_id,
            'submitted_by' => $user->id,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'location' => $request->location,
            'vendor_supplier' => $request->vendor_supplier,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'status' => $status,
            'submitted_at' => $status === 'submitted' ? now() : null
        ]);

        // Handle file uploads if any
        if ($request->hasFile('receipt_files')) {
            foreach ($request->file('receipt_files') as $file) {
                $path = $file->store('receipts', 'public');
                $expenditure->receipts()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                    'uploaded_by' => $user->id,
                    'status' => 'pending'
                ]);
            }
        }

        // Send notification if expenditure is submitted
        if ($status === 'submitted') {
            // Notify finance and admin users about the new submission
            $financeAndAdminUsers = User::whereIn('role', ['finance', 'admin'])
                ->where('status', 'active')
                ->get();
            
            foreach ($financeAndAdminUsers as $recipient) {
                $recipient->notify(new \App\Notifications\ExpenseLiquidationNotification(
                    'expenditure_submitted',
                    [
                        'expenditure_id' => $expenditure->id,
                        'submitter_name' => $user->full_name,
                        'amount' => $expenditure->amount,
                        'category' => $expenditure->category,
                        'expense_date' => $expenditure->expense_date,
                        'project_name' => $expenditure->project ? $expenditure->project->name : 'No Project',
                        'action_url' => route('finance.expenditures.show', $expenditure->id)
                    ]
                ));
            }
        }

        $message = $status === 'submitted' 
            ? 'Daily expenditure submitted successfully (visible to Finance and Admin)' 
            : 'Daily expenditure saved as draft successfully';

        return redirect()->route('pm.expenditures.show', $expenditure)
            ->with('success', $message);
    }

    public function show(DailyExpenditure $expenditure)
    {
        $user = Auth::user();
        
        if ($expenditure->submitted_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        return view('pm.expenditures.show', [
            'expenditure' => $expenditure->load(['project', 'submitter', 'receipts'])
        ]);
    }

    public function edit(DailyExpenditure $expenditure)
    {
        $user = Auth::user();
        
        if ($expenditure->submitted_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        if (!$expenditure->canBeEdited()) {
            abort(422, 'This expenditure cannot be edited');
        }

        return view('pm.expenditures.edit', [
            'expenditure' => $expenditure->load(['project']),
            'categories' => DailyExpenditure::getCategoryOptions(),
            'paymentMethods' => DailyExpenditure::getPaymentMethodOptions(),
            'projects' => Project::where('created_by', $user->id)->get()
        ]);
    }

    public function update(Request $request, DailyExpenditure $expenditure)
    {
        $user = Auth::user();
        
        if ($expenditure->submitted_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        if (!$expenditure->canBeEdited()) {
            abort(422, 'This expenditure cannot be edited');
        }

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getCategoryOptions())),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'location' => 'nullable|string|max:255',
            'vendor_supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getPaymentMethodOptions())),
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $expenditure->update($request->all());

        return redirect()->route('pm.expenditures.show', $expenditure)
            ->with('success', 'Daily expenditure updated successfully');
    }

    public function destroy(DailyExpenditure $expenditure)
    {
        $user = Auth::user();
        
        if ($expenditure->submitted_by !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        if (!$expenditure->canBeDeleted()) {
            abort(422, 'This expenditure cannot be deleted');
        }

        $expenditure->delete();

        return redirect()->route('pm.expenditures.index')
            ->with('success', 'Daily expenditure deleted successfully');
    }

    public function submit(DailyExpenditure $expenditure)
    {
        $user = Auth::user();
        
        if ($expenditure->submitted_by !== $user->id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        if (!$expenditure->canBeSubmitted()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This expenditure cannot be submitted'
                ], 422);
            }
            abort(422, 'This expenditure cannot be submitted');
        }

        $expenditure->submit();

        // Send notification to finance and admin users
        $financeAndAdminUsers = User::whereIn('role', ['finance', 'admin'])
            ->where('status', 'active')
            ->get();
        
        foreach ($financeAndAdminUsers as $recipient) {
            $recipient->notify(new \App\Notifications\ExpenseLiquidationNotification(
                'expenditure_submitted',
                [
                    'expenditure_id' => $expenditure->id,
                    'submitter_name' => $user->full_name,
                    'amount' => $expenditure->amount,
                    'category' => $expenditure->category,
                    'expense_date' => $expenditure->expense_date,
                    'project_name' => $expenditure->project ? $expenditure->project->name : 'No Project',
                    'action_url' => route('finance.expenditures.show', $expenditure->id)
                ]
            ));
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Daily expenditure submitted successfully'
            ]);
        }

        return redirect()->route('pm.expenditures.show', $expenditure)
            ->with('success', 'Daily expenditure submitted successfully');
    }

    public function bulkSubmit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expenditure_ids' => 'required|array',
            'expenditure_ids.*' => 'exists:daily_expenditures,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $expenditures = DailyExpenditure::whereIn('id', $request->expenditure_ids)
                                      ->where('submitted_by', $user->id)
                                      ->where('status', 'draft')
                                      ->get();

        $submitted = 0;
        $submittedExpenditures = [];
        
        foreach ($expenditures as $expenditure) {
            if ($expenditure->submit()) {
                $submitted++;
                $submittedExpenditures[] = $expenditure;
            }
        }

        // Send notifications for submitted expenditures
        if ($submitted > 0) {
            $financeAndAdminUsers = User::whereIn('role', ['finance', 'admin'])
                ->where('status', 'active')
                ->get();
            
            foreach ($financeAndAdminUsers as $recipient) {
                $recipient->notify(new \App\Notifications\ExpenseLiquidationNotification(
                    'bulk_expenditure_submitted',
                    [
                        'submitter_name' => $user->full_name,
                        'submitted_count' => $submitted,
                        'total_amount' => $submittedExpenditures->sum('amount'),
                        'action_url' => route('finance.expenditures.index')
                    ]
                ));
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully submitted {$submitted} expenditures",
            'data' => ['submitted_count' => $submitted]
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'expenditure_ids' => 'required|array',
            'expenditure_ids.*' => 'exists:daily_expenditures,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $expenditures = DailyExpenditure::whereIn('id', $request->expenditure_ids)
                                      ->where('submitted_by', $user->id)
                                      ->where('status', 'draft')
                                      ->get();

        $deleted = 0;
        foreach ($expenditures as $expenditure) {
            if ($expenditure->delete()) {
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} expenditures",
            'data' => ['deleted_count' => $deleted]
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'csv');
            
            if ($format === 'pdf') {
                return $this->exportPDF($request);
            }
            
            return $this->exportCSV($request);
        } catch (\Exception $e) {
            \Log::error('Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error during export: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = DailyExpenditure::where('submitted_by', $user->id);
        
        // Apply filters if provided
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('date_range')) {
            $dateRange = $request->date_range;
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('expense_date', today());
                    break;
                case 'week':
                    $query->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'quarter':
                    $query->whereBetween('expense_date', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
            }
        }
        
        $stats = [
            'total' => $query->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'submitted' => (clone $query)->where('status', 'submitted')->count(),
            'total_amount' => (clone $query)->sum('amount')
        ];
        
        return response()->json($stats);
    }

    public function exportCSV(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = DailyExpenditure::with(['project', 'submitter'])
                                    ->where('submitted_by', $user->id);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }

            // Handle date_range parameter
            if ($request->filled('date_range')) {
                $dateRange = $request->date_range;
                switch ($dateRange) {
                    case 'today':
                        $query->whereDate('expense_date', today());
                        break;
                    case 'week':
                        $query->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()]);
                        break;
                    case 'quarter':
                        $query->whereBetween('expense_date', [now()->startOfQuarter(), now()->endOfQuarter()]);
                        break;
                }
            }

            $expenditures = $query->orderBy('created_at', 'desc')->get();

            $filename = 'expenditures_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            // Generate CSV content with proper escaping
            $csvContent = "ID,Project,Expense Date,Category,Description,Amount,Location,Vendor,Payment Method,Reference,Status,Submitted At\n";
            
            foreach ($expenditures as $expenditure) {
                $row = [
                    $expenditure->id,
                    $expenditure->project ? str_replace('"', '""', $expenditure->project->name) : 'N/A',
                    $expenditure->expense_date->format('Y-m-d'),
                    $expenditure->category,
                    str_replace('"', '""', $expenditure->description),
                    $expenditure->amount,
                    $expenditure->location ? str_replace('"', '""', $expenditure->location) : 'N/A',
                    $expenditure->vendor_supplier ? str_replace('"', '""', $expenditure->vendor_supplier) : 'N/A',
                    $expenditure->payment_method,
                    $expenditure->reference_number ? str_replace('"', '""', $expenditure->reference_number) : 'N/A',
                    $expenditure->status,
                    $expenditure->submitted_at ? $expenditure->submitted_at->format('Y-m-d H:i:s') : 'N/A'
                ];
                
                // Properly escape fields that contain commas, quotes, or newlines
                $escapedRow = array_map(function($field) {
                    if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                        return '"' . $field . '"';
                    }
                    return $field;
                }, $row);
                
                $csvContent .= implode(',', $escapedRow) . "\n";
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'content' => base64_encode($csvContent)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('CSV Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV export: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPDF(Request $request)
    {
        try {
            $user = Auth::user();
            $query = DailyExpenditure::with(['project', 'submitter'])
                                    ->where('submitted_by', $user->id);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }

            // Handle date_range parameter
            if ($request->filled('date_range')) {
                $dateRange = $request->date_range;
                switch ($dateRange) {
                    case 'today':
                        $query->whereDate('expense_date', today());
                        break;
                    case 'week':
                        $query->whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()]);
                        break;
                    case 'quarter':
                        $query->whereBetween('expense_date', [now()->startOfQuarter(), now()->endOfQuarter()]);
                        break;
                }
            }

            $expenditures = $query->orderBy('created_at', 'desc')->get();
            
            // Generate filename
            $filename = 'expenditures_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            // Generate HTML view for PDF
            $html = view('pm.expenditures.export-pdf', [
                'expenditures' => $expenditures,
                'user' => $user,
                'filters' => $request->all(),
                'generated_at' => now()
            ])->render();
            
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF export: ' . $e->getMessage()
            ], 500);
        }
    }

    public function summaryReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = DailyExpenditure::where('submitted_by', $user->id);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $summary = [
            'total_expenditures' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_status' => $query->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                                ->groupBy('status')
                                ->get(),
            'by_category' => $query->selectRaw('category, COUNT(*) as count, SUM(amount) as total_amount')
                                  ->groupBy('category')
                                  ->get(),
            'by_project' => $query->join('projects', 'daily_expenditures.project_id', '=', 'projects.id')
                                 ->selectRaw('projects.name, COUNT(*) as count, SUM(daily_expenditures.amount) as total_amount')
                                 ->groupBy('projects.id', 'projects.name')
                                 ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    public function monthlyReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = $request->get('year', date('Y'));
        
        $monthlyData = DailyExpenditure::where('submitted_by', $user->id)
                                      ->whereYear('expense_date', $year)
                                      ->selectRaw('MONTH(expense_date) as month, COUNT(*) as count, SUM(amount) as total_amount')
                                      ->groupBy('month')
                                      ->orderBy('month')
                                      ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'monthly_data' => $monthlyData
            ]
        ]);
    }

    // Finance Methods (View Only - No Approval/Rejection)
    public function financeIndex(Request $request)
    {
        $query = DailyExpenditure::with(['project', 'submitter']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('submitter_id')) {
            $query->where('submitted_by', $request->submitter_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor_supplier', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenditures = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        $projects = Project::all();
        $submitters = User::whereIn('role', ['pm', 'admin'])->get();

        return view('finance.expenditures.index', compact('expenditures', 'projects', 'submitters'));
    }

    public function financeShow(DailyExpenditure $expenditure)
    {
        $expenditure->load(['project', 'submitter', 'receipts']);
        return view('finance.expenditures.show', compact('expenditure'));
    }



    public function financeExportCSV(Request $request): JsonResponse
    {
        $query = DailyExpenditure::with(['project', 'submitter']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $expenditures = $query->orderBy('created_at', 'desc')->get();

        $filename = 'finance_expenditures_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Generate CSV content
        $csvContent = "ID,Project,Submitter,Expense Date,Category,Description,Amount,Location,Vendor,Payment Method,Reference,Status,Submitted At\n";
        
        foreach ($expenditures as $expenditure) {
            $csvContent .= implode(',', [
                $expenditure->id,
                $expenditure->project ? $expenditure->project->name : 'N/A',
                $expenditure->submitter ? $expenditure->submitter->full_name : 'N/A',
                $expenditure->expense_date->format('Y-m-d'),
                $expenditure->category,
                '"' . str_replace('"', '""', $expenditure->description) . '"',
                $expenditure->amount,
                $expenditure->location ?: 'N/A',
                $expenditure->vendor_supplier ?: 'N/A',
                $expenditure->payment_method,
                $expenditure->reference_number ?: 'N/A',
                $expenditure->status,
                $expenditure->submitted_at ? $expenditure->submitted_at->format('Y-m-d H:i:s') : 'N/A'
            ]) . "\n";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => $filename,
                'content' => base64_encode($csvContent)
            ]
        ]);
    }



    // Admin Methods
    public function adminIndex(Request $request)
    {
        $query = DailyExpenditure::with(['project', 'submitter']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('submitter_id')) {
            $query->where('submitted_by', $request->submitter_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('vendor_supplier', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $expenditures = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        $data = [
            'expenditures' => $expenditures,
            'status_options' => DailyExpenditure::getStatusOptions(),
            'category_options' => DailyExpenditure::getCategoryOptions(),
            'payment_method_options' => DailyExpenditure::getPaymentMethodOptions(),
            'projects' => Project::all(),
            'submitters' => User::all(),
            'filters' => $request->only(['status', 'project_id', 'submitter_id', 'date_from', 'date_to', 'search', 'per_page'])
        ];

        return view('admin.expenditures.index', $data);
    }

    public function adminCreate()
    {
        $data = [
            'category_options' => DailyExpenditure::getCategoryOptions(),
            'payment_method_options' => DailyExpenditure::getPaymentMethodOptions(),
            'status_options' => DailyExpenditure::getStatusOptions(),
            'projects' => Project::all(),
            'users' => User::all()
        ];

        return view('admin.expenditures.create', $data);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'submitted_by' => 'required|exists:users,id',
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getCategoryOptions())),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'location' => 'nullable|string|max:255',
            'vendor_supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getPaymentMethodOptions())),
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getStatusOptions()))
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $expenditure = DailyExpenditure::create($request->all());

        return redirect()->route('admin.expenditures.index')
            ->with('success', 'Daily expenditure created successfully');
    }

    public function adminShow(DailyExpenditure $expenditure)
    {
        $data = [
            'expenditure' => $expenditure->load(['project', 'submitter', 'receipts'])
        ];

        return view('admin.expenditures.show', $data);
    }

    public function adminEdit(DailyExpenditure $expenditure)
    {
        $data = [
            'expenditure' => $expenditure->load(['project', 'submitter']),
            'category_options' => DailyExpenditure::getCategoryOptions(),
            'payment_method_options' => DailyExpenditure::getPaymentMethodOptions(),
            'status_options' => DailyExpenditure::getStatusOptions(),
            'projects' => Project::all(),
            'users' => User::all()
        ];

        return view('admin.expenditures.edit', $data);
    }

    public function adminUpdate(Request $request, DailyExpenditure $expenditure)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'submitted_by' => 'required|exists:users,id',
            'expense_date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getCategoryOptions())),
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'location' => 'nullable|string|max:255',
            'vendor_supplier' => 'nullable|string|max:255',
            'payment_method' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getPaymentMethodOptions())),
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:' . implode(',', array_keys(DailyExpenditure::getStatusOptions()))
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $expenditure->update($request->all());

        return redirect()->route('admin.expenditures.show', $expenditure)
            ->with('success', 'Daily expenditure updated successfully');
    }

    public function adminDestroy(DailyExpenditure $expenditure): JsonResponse
    {
        $expenditure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Daily expenditure deleted successfully'
        ]);
    }

    // API Methods for external access
    public function getProjectExpenditures(Project $project, Request $request): JsonResponse
    {
        $query = DailyExpenditure::with(['submitter'])
                                ->where('project_id', $project->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $expenditures = $query->orderBy('created_at', 'desc')
                             ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $expenditures
        ]);
    }

    public function searchExpenditures(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $search = $request->search;
        $expenditures = DailyExpenditure::with(['project', 'submitter'])
                                      ->where(function($query) use ($search) {
                                          $query->where('description', 'like', "%{$search}%")
                                                ->orWhere('vendor_supplier', 'like', "%{$search}%")
                                                ->orWhere('reference_number', 'like', "%{$search}%");
                                      })
                                      ->limit(10)
                                      ->get();

        return response()->json([
            'success' => true,
            'data' => $expenditures
        ]);
    }















    // Additional helper methods
    public function getUserStatistics(User $user): JsonResponse
    {
        $stats = [
            'total_expenditures' => DailyExpenditure::where('submitted_by', $user->id)->count(),
            'total_amount' => DailyExpenditure::where('submitted_by', $user->id)->sum('amount'),
            'by_status' => DailyExpenditure::where('submitted_by', $user->id)
                                          ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
                                          ->groupBy('status')
                                          ->get(),
            'by_category' => DailyExpenditure::where('submitted_by', $user->id)
                                            ->selectRaw('category, COUNT(*) as count, SUM(amount) as total_amount')
                                            ->groupBy('category')
                                            ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function getCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => DailyExpenditure::getCategoryOptions()
        ]);
    }

    // Admin bulk action method
    public function adminBulkAction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,export,approve,reject',
            'expenditure_ids' => 'required|array',
            'expenditure_ids.*' => 'exists:daily_expenditures,id',
            'reason' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $action = $request->action;
        $expenditures = DailyExpenditure::whereIn('id', $request->expenditure_ids)->get();

        switch ($action) {
            case 'delete':
                $deleted = 0;
                foreach ($expenditures as $expenditure) {
                    if ($expenditure->delete()) {
                        $deleted++;
                    }
                }
                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deleted} expenditures",
                    'data' => ['deleted_count' => $deleted]
                ]);

            case 'approve':
                $approved = 0;
                foreach ($expenditures as $expenditure) {
                    $expenditure->update([
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'approval_notes' => $request->reason
                    ]);
                    $approved++;
                }
                return response()->json([
                    'success' => true,
                    'message' => "Successfully approved {$approved} expenditures",
                    'data' => ['approved_count' => $approved]
                ]);

            case 'reject':
                if (!$request->reason) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rejection reason is required'
                    ], 422);
                }
                
                $rejected = 0;
                foreach ($expenditures as $expenditure) {
                    $expenditure->update([
                        'status' => 'rejected',
                        'rejection_reason' => $request->reason
                    ]);
                    $rejected++;
                }
                return response()->json([
                    'success' => true,
                    'message' => "Successfully rejected {$rejected} expenditures",
                    'data' => ['rejected_count' => $rejected]
                ]);



            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ], 422);
        }
    }

    // Detailed report method
    public function detailedReport(Request $request)
    {
        $query = DailyExpenditure::with(['project', 'submitter']);

        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('submitter_id')) {
            $query->where('submitted_by', $request->submitter_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $expenditures = $query->orderBy('created_at', 'desc')->get();

        $data = [
            'expenditures' => $expenditures,
            'total_expenditures' => $expenditures->count(),
            'total_amount' => $expenditures->sum('amount'),
            'by_category' => $expenditures->groupBy('category'),
            'by_project' => $expenditures->groupBy('project_id'),
            'category_options' => DailyExpenditure::getCategoryOptions(),
            'projects' => Project::all(),
            'submitters' => User::all(),
            'filters' => $request->only(['project_id', 'submitter_id', 'date_from', 'date_to'])
        ];

        return view('admin.expenditures.reports.detailed', $data);
    }

    // Analytics method
    public function analytics(Request $request)
    {
        $query = DailyExpenditure::query();

        // Apply filters
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $data = [
            'total_expenditures' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'average_amount' => $query->avg('amount'),
            'by_category' => $query->selectRaw('category, COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as avg_amount')
                                  ->groupBy('category')
                                  ->get(),
            'by_month' => $query->selectRaw('YEAR(expense_date) as year, MONTH(expense_date) as month, COUNT(*) as count, SUM(amount) as total_amount')
                               ->groupBy('year', 'month')
                               ->orderBy('year', 'desc')
                               ->orderBy('month', 'desc')
                               ->limit(12)
                               ->get(),
            'top_projects' => $query->join('projects', 'daily_expenditures.project_id', '=', 'projects.id')
                                   ->selectRaw('projects.name, COUNT(*) as count, SUM(daily_expenditures.amount) as total_amount')
                                   ->groupBy('projects.id', 'projects.name')
                                   ->orderBy('total_amount', 'desc')
                                   ->limit(10)
                                   ->get(),
                        'top_submitters' => $query->join('users', 'daily_expenditures.submitted_by', '=', 'users.id')
                                      ->selectRaw('CONCAT(users.first_name, " ", users.last_name) as name, COUNT(*) as count, SUM(daily_expenditures.amount) as total_amount')
                                      ->groupBy('users.id', 'users.first_name', 'users.last_name')
                                      ->orderBy('total_amount', 'desc')
                                      ->limit(10)
                                      ->get(),
            'filters' => $request->only(['date_from', 'date_to'])
        ];

        return view('admin.expenditures.reports.analytics', $data);
    }
}
