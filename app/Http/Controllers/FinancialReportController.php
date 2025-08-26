<?php

namespace App\Http\Controllers;

use App\Models\FinancialReport;
use App\Models\Project;
use App\Models\User;
use App\Models\DailyExpenditure;
use App\Models\LiquidatedForm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:finance')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'generate', 'createLiquidatedForm', 'exportPDF', 'exportExcel']);
        $this->middleware('role:admin')->only(['adminIndex', 'adminCreate', 'adminStore', 'adminShow', 'adminEdit', 'adminUpdate', 'adminDestroy', 'forceGenerate', 'adminBulkAction']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FinancialReport::with(['project', 'creator']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('report_period_start', [$request->date_from, $request->date_to])
                  ->orWhereBetween('report_period_end', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        $projects = Project::all();
        $statusOptions = FinancialReport::getStatusOptions();
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();

        return view('finance.financial-reports.index', compact('reports', 'projects', 'statusOptions', 'reportTypeOptions', 'currencyOptions'));
    }

    public function create()
    {
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();
        $projects = Project::all();

        return view('finance.financial-reports.create', compact('reportTypeOptions', 'currencyOptions', 'projects'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:' . implode(',', array_keys(FinancialReport::getReportTypeOptions())),
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'project_id' => 'nullable|exists:projects,id',
            'currency' => 'required|in:' . implode(',', array_keys(FinancialReport::getCurrencyOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        $report = FinancialReport::create([
            'title' => $request->title,
            'description' => $request->description,
            'report_type' => $request->report_type,
            'report_period_start' => $request->report_period_start,
            'report_period_end' => $request->report_period_end,
            'project_id' => $request->project_id,
            'created_by' => $user->id,
            'currency' => $request->currency,
            'notes' => $request->notes,
            'status' => 'draft'
        ]);

        return redirect()->route('finance.financial-reports.show', $report)
            ->with('success', 'Financial report created successfully');
    }

    public function show(FinancialReport $financialReport)
    {
        $financialReport->load([
            'project', 
            'creator', 
            'liquidatedForm',
            'expenditures.submitter',
            'expenditures.receipts',
            'receipts.uploader'
        ]);

        return view('finance.financial-reports.show', compact('financialReport'));
    }

    public function edit(FinancialReport $financialReport)
    {
        if (!$financialReport->canBeEdited()) {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'This report cannot be edited');
        }

        $financialReport->load(['project']);
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();
        $projects = Project::all();

        return view('finance.financial-reports.edit', compact('financialReport', 'reportTypeOptions', 'currencyOptions', 'projects'));
    }

    public function update(Request $request, FinancialReport $financialReport)
    {
        if (!$financialReport->canBeEdited()) {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'This report cannot be edited');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:' . implode(',', array_keys(FinancialReport::getReportTypeOptions())),
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'project_id' => 'nullable|exists:projects,id',
            'currency' => 'required|in:' . implode(',', array_keys(FinancialReport::getCurrencyOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $financialReport->update($request->all());

        return redirect()->route('finance.financial-reports.show', $financialReport)
            ->with('success', 'Financial report updated successfully');
    }

    public function destroy(FinancialReport $financialReport)
    {
        if (!$financialReport->canBeDeleted()) {
            return redirect()->route('finance.financial-reports.index')
                ->with('error', 'This report cannot be deleted');
        }

        $financialReport->delete();

        return redirect()->route('finance.financial-reports.index')
            ->with('success', 'Financial report deleted successfully');
    }

    public function generate(FinancialReport $financialReport)
    {
        if (!$financialReport->canBeGenerated()) {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'This report cannot be generated');
        }

        try {
            DB::beginTransaction();

            // Calculate totals from related expenditures and receipts
            $financialReport->calculateTotals();

            // Generate report data
            $reportData = $this->generateReportData($financialReport);

            $financialReport->update([
                'status' => 'generated',
                'generated_at' => now(),
                'report_data' => $reportData
            ]);

            DB::commit();

            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('success', 'Financial report generated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'Failed to generate financial report: ' . $e->getMessage());
        }
    }

    public function createLiquidatedForm(FinancialReport $financialReport)
    {
        if (!$financialReport->canBeLiquidated()) {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'This report cannot be liquidated');
        }

        try {
            DB::beginTransaction();

            $liquidatedForm = $financialReport->createLiquidatedForm();

            if (!$liquidatedForm) {
                throw new \Exception('Failed to create liquidated form');
            }

            DB::commit();

            return redirect()->route('finance.liquidated-forms.show', $liquidatedForm)
                ->with('success', 'Liquidated form created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'Failed to create liquidated form: ' . $e->getMessage());
        }
    }

    public function exportPDF(FinancialReport $financialReport)
    {
        if ($financialReport->status !== 'generated') {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'Report must be generated to export');
        }

        $financialReport->load([
            'project', 
            'creator',
            'expenditures.submitter',
            'expenditures.receipts'
        ]);

        return view('finance.financial-reports.export-pdf', compact('financialReport'));
    }

    public function exportExcel(FinancialReport $financialReport)
    {
        if ($financialReport->status !== 'generated') {
            return redirect()->route('finance.financial-reports.show', $financialReport)
                ->with('error', 'Report must be generated to export');
        }

        $expenditures = $financialReport->expenditures()->with(['submitter', 'receipts'])->get();
        $receipts = $financialReport->receipts()->with(['uploader'])->get();

        $filename = 'financial_report_' . $financialReport->id . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Generate Excel content structure
        $excelData = [
            'report_info' => [
                'title' => $financialReport->title,
                'period' => $financialReport->formatted_period,
                'total_expenditures' => $financialReport->formatted_total_expenditures,
                'total_receipts' => $financialReport->formatted_total_receipts,
                'variance' => $financialReport->formatted_variance_amount
            ],
            'expenditures' => $expenditures,
            'receipts' => $receipts,

        ];

        return view('finance.financial-reports.export-excel', compact('financialReport', 'excelData', 'filename'));
    }

    private function generateReportData(FinancialReport $financialReport): array
    {
        $expenditures = $financialReport->expenditures()->with(['submitter', 'receipts'])->get();
        $receipts = $financialReport->receipts()->with(['uploader'])->get();

        return [
            'expenditures_by_category' => $expenditures->groupBy('category')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount'),
                    'items' => $group->map(function($item) {
                        return [
                            'id' => $item->id,
                            'description' => $item->description,
                            'amount' => $item->amount,
                            'submitter' => $item->submitter->full_name,
                            'receipts_count' => $item->receipts->count()
                        ];
                    })
                ];
            }),
            'expenditures_by_payment_method' => $expenditures->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount')
                ];
            }),
            'receipts_by_type' => $receipts->groupBy('receipt_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount')
                ];
            }),
            'receipts_by_vendor' => $receipts->groupBy('vendor_name')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount')
                ];
            }),
            'top_expenditures' => $expenditures->sortByDesc('amount')->take(10)->map(function($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'category' => $item->category,
                    'submitter' => $item->submitter->full_name
                ];
            }),
            'top_receipts' => $receipts->sortByDesc('amount')->take(10)->map(function($item) {
                return [
                    'id' => $item->id,
                    'vendor_name' => $item->vendor_name,
                    'amount' => $item->amount,
                    'receipt_type' => $item->receipt_type,
                    'uploader' => $item->uploader->full_name
                ];
            }),
            'daily_totals' => $expenditures->groupBy(function($item) {
                return $item->expense_date->format('Y-m-d');
            })->map(function($group) {
                return [
                    'date' => $group->first()->expense_date->format('Y-m-d'),
                    'expenditures_count' => $group->count(),
                    'expenditures_amount' => $group->sum('amount'),
                    'receipts_amount' => $group->sum(function($item) {
                        return $item->receipts->sum('amount');
                    })
                ];
            })->sortBy('date'),
            'generated_at' => now()->toISOString(),
            'generated_by' => Auth::user()->full_name
        ];
    }

    // ====================================================================
    // ADMIN METHODS - Full Administrative Control
    // ====================================================================

    public function adminIndex(Request $request)
    {
        $query = FinancialReport::with(['project', 'creator']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('report_period_start', [$request->date_from, $request->date_to])
                  ->orWhereBetween('report_period_end', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        $projects = Project::all();
        $statusOptions = FinancialReport::getStatusOptions();
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();

        return view('admin.financial-reports.index', compact('reports', 'projects', 'statusOptions', 'reportTypeOptions', 'currencyOptions'));
    }

    public function adminCreate()
    {
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();
        $projects = Project::all();

        return view('admin.financial-reports.create', compact('reportTypeOptions', 'currencyOptions', 'projects'));
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:' . implode(',', array_keys(FinancialReport::getReportTypeOptions())),
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'project_id' => 'nullable|exists:projects,id',
            'currency' => 'required|in:' . implode(',', array_keys(FinancialReport::getCurrencyOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        
        $report = FinancialReport::create([
            'title' => $request->title,
            'description' => $request->description,
            'report_type' => $request->report_type,
            'report_period_start' => $request->report_period_start,
            'report_period_end' => $request->report_period_end,
            'project_id' => $request->project_id,
            'created_by' => $user->id,
            'status' => 'draft',
            'currency' => $request->currency,
            'notes' => $request->notes
        ]);

        return redirect()->route('admin.financial-reports.show', $report)
            ->with('success', 'Financial report created successfully');
    }

    public function adminShow(FinancialReport $financialReport)
    {
        $financialReport->load([
            'project', 
            'creator',
            'expenditures.submitter',
            'expenditures.receipts',
            'liquidatedForm'
        ]);

        return view('admin.financial-reports.show', compact('financialReport'));
    }

    public function adminEdit(FinancialReport $financialReport)
    {
        $reportTypeOptions = FinancialReport::getReportTypeOptions();
        $currencyOptions = FinancialReport::getCurrencyOptions();
        $projects = Project::all();

        return view('admin.financial-reports.edit', compact('financialReport', 'reportTypeOptions', 'currencyOptions', 'projects'));
    }

    public function adminUpdate(Request $request, FinancialReport $financialReport)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|in:' . implode(',', array_keys(FinancialReport::getReportTypeOptions())),
            'report_period_start' => 'required|date',
            'report_period_end' => 'required|date|after_or_equal:report_period_start',
            'project_id' => 'nullable|exists:projects,id',
            'currency' => 'required|in:' . implode(',', array_keys(FinancialReport::getCurrencyOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $financialReport->update([
            'title' => $request->title,
            'description' => $request->description,
            'report_type' => $request->report_type,
            'report_period_start' => $request->report_period_start,
            'report_period_end' => $request->report_period_end,
            'project_id' => $request->project_id,
            'currency' => $request->currency,
            'notes' => $request->notes
        ]);

        return redirect()->route('admin.financial-reports.show', $financialReport)
            ->with('success', 'Financial report updated successfully');
    }

    public function adminDestroy(FinancialReport $financialReport)
    {
        if (!$financialReport->canBeDeleted()) {
            return redirect()->route('admin.financial-reports.index')
                ->with('error', 'Cannot delete this financial report');
        }

        $financialReport->delete();

        return redirect()->route('admin.financial-reports.index')
            ->with('success', 'Financial report deleted successfully');
    }

    public function forceGenerate(FinancialReport $financialReport)
    {
        try {
            DB::beginTransaction();

            $reportData = $this->generateReportData($financialReport);

            $financialReport->update([
                'status' => 'generated',
                'generated_at' => now(),
                'report_data' => $reportData
            ]);

            DB::commit();

            return redirect()->route('admin.financial-reports.show', $financialReport)
                ->with('success', 'Financial report generated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.financial-reports.show', $financialReport)
                ->with('error', 'Failed to generate financial report: ' . $e->getMessage());
        }
    }

    public function adminBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,generate,export',
            'selected_reports' => 'required|array|min:1',
            'selected_reports.*' => 'exists:financial_reports,id'
        ]);

        $reports = FinancialReport::whereIn('id', $request->selected_reports)->get();

        switch ($request->action) {
            case 'delete':
                $deletableReports = $reports->filter(function($report) {
                    return $report->canBeDeleted();
                });
                
                $deletableReports->each(function($report) {
                    $report->delete();
                });

                $message = "Deleted " . $deletableReports->count() . " financial report(s)";
                break;

            case 'generate':
                $generatableReports = $reports->filter(function($report) {
                    return $report->canBeGenerated();
                });

                foreach ($generatableReports as $report) {
                    $this->forceGenerate($report);
                }

                $message = "Generated " . $generatableReports->count() . " financial report(s)";
                break;

            case 'export':
                // Handle export logic here
                $message = "Export functionality will be implemented";
                break;
        }

        return redirect()->route('admin.financial-reports.index')
            ->with('success', $message);
    }

    // ====================================================================
    // PROJECT MANAGER METHODS
    // ====================================================================

    public function pmShow(FinancialReport $financialReport)
    {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        // Check if PM has access to this report's project
        if (!in_array($financialReport->project_id, $managedProjectIds)) {
            abort(403, 'Access denied. You can only view financial reports for projects you manage.');
        }

        $financialReport->load(['project', 'creator', 'liquidatedForm', 'expenditures', 'receipts']);

        return view('pm.financial-reports.show', compact('financialReport'));
    }
}
