<?php

namespace App\Http\Controllers;

use App\Models\Receipt;

use App\Models\Project;
use App\Models\User;
use App\Models\FinancialReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:finance')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy', 'upload', 'bulkUpload', 'validateReceipt']);
        $this->middleware('role:admin')->only(['adminIndex', 'adminShow', 'adminEdit', 'adminUpdate', 'adminDestroy', 'adminBulkAction', 'adminCreate', 'adminStore', 'adminRequestClarificationForm', 'adminRequestClarification', 'adminResolveClarificationForm', 'adminResolveClarification']);
        $this->middleware('role:finance,admin,pm')->only(['download']);
    }

    public function index(Request $request)
    {
        $query = Receipt::with(['liquidatedForm', 'uploader']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('receipt_type')) {
            $query->where('receipt_type', $request->receipt_type);
        }

        if ($request->filled('uploader_id')) {
            $query->where('uploaded_by', $request->uploader_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('receipt_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('amount_min') && $request->filled('amount_max')) {
            $query->whereBetween('amount', [$request->amount_min, $request->amount_max]);
        }

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhere('original_file_name', 'like', "%{$search}%");
            });
        }

        $receipts = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));



        return view('finance.receipts.index', [
            'receipts' => $receipts,
            'statusOptions' => Receipt::getStatusOptions(),
            'receiptTypeOptions' => Receipt::getReceiptTypeOptions(),
            'uploaders' => User::whereIn('role', ['pm', 'finance', 'admin'])->get(),
            'financial_reports' => FinancialReport::whereIn('status', ['generated', 'approved', 'liquidated'])->get(),
            'filters' => $request->only(['status', 'receipt_type', 'uploader_id', 'date_from', 'date_to', 'amount_min', 'amount_max', 'vendor_name', 'search'])
        ]);
    }

    public function create(Request $request)
    {
        $data = [
            'receipt_type_options' => Receipt::getReceiptTypeOptions(),
            'financial_reports' => FinancialReport::whereIn('status', ['generated', 'approved', 'liquidated'])->get()
        ];

        return view('finance.receipts.create', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'financial_report_id' => 'nullable|exists:financial_reports,id',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'notes' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $file = $request->file('file');
        
        // Generate unique filename
        $fileName = Receipt::generateFileName($file->getClientOriginalName(), $user->id);
        $filePath = $file->storeAs('receipts', $fileName, 'public');

        $receipt = Receipt::create([
            'financial_report_id' => $request->financial_report_id,
            'uploaded_by' => $user->id,
            'file_name' => $fileName,
            'original_file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'receipt_number' => $request->receipt_number,
            'receipt_date' => $request->receipt_date,
            'vendor_name' => $request->vendor_name,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'description' => $request->description,
            'receipt_type' => $request->receipt_type,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        return redirect()->route('finance.receipts.index')
                        ->with('success', 'Receipt uploaded successfully');
    }

    public function show(Receipt $receipt, Request $request)
    {
        $receipt->load(['liquidatedForm', 'uploader']);

        return view('finance.receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt, Request $request)
    {
        if (!$receipt->canBeEdited()) {
            return back()->with('error', 'This receipt cannot be edited');
        }

        $data = [
            'receipt' => $receipt->load(['financialReport']),
            'receipt_type_options' => Receipt::getReceiptTypeOptions(),
            'financial_reports' => FinancialReport::whereIn('status', ['generated', 'approved', 'liquidated'])->get()
        ];

        return view('finance.receipts.edit', $data);
    }

    public function update(Request $request, Receipt $receipt)
    {
        if (!$receipt->canBeEdited()) {
            return back()->with('error', 'This receipt cannot be edited');
        }

        $validator = Validator::make($request->all(), [
            'financial_report_id' => 'nullable|exists:financial_reports,id',
            'receipt_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $receipt->update($request->all());

        return redirect()->route('finance.receipts.show', $receipt)
                        ->with('success', 'Receipt updated successfully');
    }

    public function destroy(Receipt $receipt, Request $request)
    {
        if (!$receipt->canBeDeleted()) {
            return back()->with('error', 'This receipt cannot be deleted');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($receipt->file_path)) {
            Storage::disk('public')->delete($receipt->file_path);
        }

        $receipt->delete();

        return redirect()->route('finance.receipts.index')
                        ->with('success', 'Receipt deleted successfully');
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'daily_expenditure_id' => 'nullable|exists:daily_expenditures,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $file = $request->file('file');
        
        // Generate unique filename
        $fileName = Receipt::generateFileName($file->getClientOriginalName(), $user->id);
        $filePath = $file->storeAs('receipts', $fileName, 'public');

        $receipt = Receipt::create([
            'daily_expenditure_id' => $request->daily_expenditure_id,
            'uploaded_by' => $user->id,
            'file_name' => $fileName,
            'original_file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'receipt_date' => $request->receipt_date,
            'vendor_name' => $request->vendor_name,
            'amount' => $request->amount,
            'receipt_type' => $request->receipt_type,
            'status' => 'pending'
        ]);

        return redirect()->route('finance.receipts.index')
                        ->with('success', 'Receipt uploaded successfully');
    }

    public function bulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'financial_report_id' => 'nullable|exists:financial_reports,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();
        $uploadedReceipts = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->file('files') as $index => $file) {
                try {
                    // Generate unique filename
                    $fileName = Receipt::generateFileName($file->getClientOriginalName(), $user->id);
                    $filePath = $file->storeAs('receipts', $fileName, 'public');

                    $receipt = Receipt::create([
                        'financial_report_id' => $request->financial_report_id,
                        'uploaded_by' => $user->id,
                        'file_name' => $fileName,
                        'original_file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'receipt_date' => $request->receipt_date,
                        'vendor_name' => $request->vendor_name,
                        'amount' => 0, // Will need to be updated manually
                        'receipt_type' => $request->receipt_type,
                        'status' => 'pending'
                    ]);

                    $uploadedReceipts[] = $receipt;

                } catch (\Exception $e) {
                    $errors[] = "File {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            DB::commit();

            return back()->with('success', "Successfully uploaded " . count($uploadedReceipts) . " receipts");

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->with('error', 'Bulk upload failed: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'exists:receipts,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $receipts = Receipt::whereIn('id', $request->receipt_ids)
                          ->where('status', 'pending')
                          ->get();

        $deleted = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($receipts as $receipt) {
                try {
                    // Delete file from storage
                    if (Storage::disk('public')->exists($receipt->file_path)) {
                        Storage::disk('public')->delete($receipt->file_path);
                    }

                    $receipt->delete();
                    $deleted++;

                } catch (\Exception $e) {
                    $errors[] = "Receipt {$receipt->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            return back()->with('success', "Successfully deleted {$deleted} receipts");

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }

    public function download(Receipt $receipt)
    {
        // Access control for PMs - they can only download receipts for projects they manage
        $user = auth()->user();
        if ($user->role === 'pm') {
            $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();
            
            // Check if PM has access to this receipt's project (if it's linked to a financial report)
            if ($receipt->financialReport && !in_array($receipt->financialReport->project_id, $managedProjectIds)) {
                abort(403, 'Access denied. You can only download receipts for projects you manage.');
            }
        }

        if (!Storage::disk('public')->exists($receipt->file_path)) {
            return back()->with('error', 'File not found');
        }

        return Storage::disk('public')->download($receipt->file_path, $receipt->original_file_name);
    }



    public function matchToFinancialReport(Request $request, Receipt $receipt)
    {
        $validator = Validator::make($request->all(), [
            'financial_report_id' => 'required|exists:financial_reports,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $financialReport = FinancialReport::find($request->financial_report_id);

        if ($receipt->matchToFinancialReport($financialReport)) {
            return back()->with('success', 'Receipt matched to financial report successfully');
        }

        return back()->with('error', 'Failed to match receipt to financial report');
    }







    public function validateReceipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        
        // Basic validation
        $validation = [
            'is_valid' => true,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'warnings' => [],
            'errors' => []
        ];

        // Check file size
        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB
            $validation['warnings'][] = 'File size is large';
        }

        // Check if it's an image and validate dimensions
        if (in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                $validation['image_width'] = $imageInfo[0];
                $validation['image_height'] = $imageInfo[1];
                
                if ($imageInfo[0] < 800 || $imageInfo[1] < 600) {
                    $validation['warnings'][] = 'Image resolution is low';
                }
            }
        }

        return back()->with('success', 'Receipt validation completed successfully');
    }

    // Admin Methods
    public function adminIndex(Request $request)
    {
        $query = Receipt::with(['liquidatedForm', 'uploader']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('receipt_type')) {
            $query->where('receipt_type', $request->receipt_type);
        }

        if ($request->filled('uploader_id')) {
            $query->where('uploaded_by', $request->uploader_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('receipt_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('amount_min') && $request->filled('amount_max')) {
            $query->whereBetween('amount', [$request->amount_min, $request->amount_max]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $receipts = $query->with(['uploader', 'clarificationRequester'])
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 15));

        // Get statistics for all receipts (not just current page)
        $statisticsQuery = Receipt::query();
        
        // Apply the same filters for statistics
        if ($request->filled('status')) {
            $statisticsQuery->where('status', $request->status);
        }
        if ($request->filled('receipt_type')) {
            $statisticsQuery->where('receipt_type', $request->receipt_type);
        }
        if ($request->filled('uploader_id')) {
            $statisticsQuery->where('uploaded_by', $request->uploader_id);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $statisticsQuery->whereBetween('receipt_date', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('amount_min') && $request->filled('amount_max')) {
            $statisticsQuery->whereBetween('amount', [$request->amount_min, $request->amount_max]);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $statisticsQuery->where(function($q) use ($search) {
                $q->where('vendor_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        // Get all filtered receipts for statistics
        $allReceipts = $statisticsQuery->get();

        $statistics = [
            'total' => $allReceipts->count(),
            'active' => $allReceipts->where('status', 'active')->count(),
            'pending' => $allReceipts->where('status', 'pending')->count(),
            'archived' => $allReceipts->where('status', 'archived')->count(),
            'total_amount' => $allReceipts->sum('amount'),
            'clarification_needed' => $allReceipts->where('clarification_status', 'requested')->count(),
        ];

        return view('admin.receipts.index', [
            'receipts' => $receipts,
            'statistics' => $statistics,
            'statusOptions' => Receipt::getStatusOptions(),
            'receiptTypeOptions' => Receipt::getReceiptTypeOptions(),
            'uploaders' => User::all(),
            'filters' => $request->only(['status', 'receipt_type', 'uploader_id', 'date_from', 'date_to', 'amount_min', 'amount_max', 'search'])
        ]);
    }

    public function adminShow(Receipt $receipt)
    {
        return view('admin.receipts.show', [
            'receipt' => $receipt->load(['liquidatedForm', 'uploader', 'financialReport'])
        ]);
    }

    public function adminEdit(Receipt $receipt)
    {
        return view('admin.receipts.edit', [
            'receipt' => $receipt,
            'receiptTypeOptions' => Receipt::getReceiptTypeOptions(),
            'statusOptions' => Receipt::getStatusOptions(),
            'users' => User::all()
        ]);
    }

    public function adminUpdate(Request $request, Receipt $receipt)
    {
        $validator = Validator::make($request->all(), [
            'receipt_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'status' => 'required|in:' . implode(',', array_keys(Receipt::getStatusOptions())),
            'notes' => 'nullable|string|max:1000',
            'uploaded_by' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $receipt->update($request->all());

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('success', 'Receipt updated successfully');
    }

    public function adminDestroy(Receipt $receipt)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($receipt->file_path)) {
            Storage::disk('public')->delete($receipt->file_path);
        }

        $receipt->delete();

        return redirect()->route('admin.receipts.index')
            ->with('success', 'Receipt deleted successfully');
    }

    public function adminCreate()
    {
        return view('admin.receipts.create', [
            'receiptTypeOptions' => Receipt::getReceiptTypeOptions(),
            'statusOptions' => Receipt::getStatusOptions(),
            'users' => User::all()
        ]);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'receipt_type' => 'required|in:' . implode(',', array_keys(Receipt::getReceiptTypeOptions())),
            'status' => 'required|in:' . implode(',', array_keys(Receipt::getStatusOptions())),
            'notes' => 'nullable|string|max:1000',
            'uploaded_by' => 'nullable|exists:users,id',
            'receipt_file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['uploaded_by'] = $data['uploaded_by'] ?? auth()->user()->id;

        // Handle file upload
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('receipts', $fileName, 'public');
            
            $data['file_path'] = $filePath;
            $data['original_file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $file->getMimeType();
        }

        $receipt = Receipt::create($data);

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('success', 'Receipt created successfully');
    }







    public function adminBulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt_ids' => 'required|array',
            'receipt_ids.*' => 'exists:receipts,id',
            'action' => 'required|in:delete,update_status,export,request_clarification',
            'status' => 'required_if:action,update_status|in:' . implode(',', array_keys(Receipt::getStatusOptions())),
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $receipts = Receipt::whereIn('id', $request->receipt_ids)->get();
        $processed = 0;

        DB::beginTransaction();
        try {
            foreach ($receipts as $receipt) {
                switch ($request->action) {
                    case 'update_status':
                        $receipt->update([
                            'status' => $request->status,
                            'notes' => $request->notes
                        ]);
                        $processed++;
                        break;

                    case 'delete':
                        if (Storage::disk('public')->exists($receipt->file_path)) {
                            Storage::disk('public')->delete($receipt->file_path);
                        }
                        $receipt->delete();
                        $processed++;
                        break;

                    case 'export':
                        // Handle export logic here if needed
                        $processed++;
                        break;

                    case 'request_clarification':
                        if ($receipt->canRequestClarification()) {
                            if ($receipt->requestClarification($user, $request->notes ?? 'Bulk clarification request by admin')) {
                                $processed++;
                            }
                        }
                        break;
                }
            }

            DB::commit();

            // Send notifications for bulk clarification requests
            if ($request->action === 'request_clarification' && $processed > 0) {
                $notificationData = [
                    'type' => 'clarification_requested',
                    'message' => "Admin {$user->name} has requested clarification for {$processed} receipt(s) in bulk.",
                    'requester_name' => $user->name,
                    'clarification_question' => $request->notes ?? 'Bulk clarification request by admin',
                    'view_url' => route('finance.receipts.index')
                ];

                // Send notification to all finance users
                \App\Notifications\ExpenseLiquidationNotification::sendToFinance('clarification_requested', $notificationData);
            }

            return redirect()->route('admin.receipts.index')
                ->with('success', "Successfully processed {$processed} receipts");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    // Admin clarification management methods
    public function adminRequestClarificationForm(Receipt $receipt)
    {
        return view('admin.receipts.request-clarification', [
            'receipt' => $receipt->load(['uploader', 'liquidatedForm', 'financialReport'])
        ]);
    }

    public function adminRequestClarification(Request $request, Receipt $receipt)
    {
        $validator = Validator::make($request->all(), [
            'clarification_notes' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (!$receipt->canRequestClarification()) {
            return redirect()->back()
                ->with('error', 'This receipt cannot be requested for clarification. Only pending receipts without existing clarification requests can be clarified.');
        }

        $result = $receipt->requestClarification($user, $request->clarification_notes);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to request clarification');
        }

        // Send notification to finance users
        $notificationData = [
            'type' => 'clarification_requested',
            'receipt_number' => $receipt->receipt_number,
            'vendor_name' => $receipt->vendor_name,
            'requester_name' => $user->name,
            'clarification_question' => $request->clarification_notes,
            'view_url' => route('finance.receipts.show', $receipt)
        ];

        // Send notification to all finance users
        \App\Notifications\ExpenseLiquidationNotification::sendToFinance('clarification_requested', $notificationData);

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('success', 'Clarification requested successfully. The finance team has been notified.');
    }

    public function adminResolveClarificationForm(Receipt $receipt)
    {
        if ($receipt->clarification_status !== 'requested') {
            return redirect()->route('admin.receipts.show', $receipt)
                ->with('error', 'This receipt does not have a pending clarification request.');
        }

        return view('admin.receipts.resolve-clarification', [
            'receipt' => $receipt->load(['uploader', 'liquidatedForm', 'financialReport', 'clarificationRequester'])
        ]);
    }

    public function adminResolveClarification(Request $request, Receipt $receipt)
    {
        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if ($receipt->clarification_status !== 'requested') {
            return redirect()->back()
                ->with('error', 'This receipt does not have a pending clarification request.');
        }

        $result = $receipt->resolveClarification($user, $request->resolution_notes);

        if (!$result) {
            return redirect()->back()
                ->with('error', 'Failed to resolve clarification');
        }

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('success', 'Clarification resolved successfully.');
    }

    // ====================================================================
    // PROJECT MANAGER METHODS
    // ====================================================================

    public function pmShow(Receipt $receipt)
    {
        $user = auth()->user();
        $managedProjectIds = $user->getManagedProjects()->pluck('id')->toArray();

        // Check if PM has access to this receipt's project (if it's linked to a financial report)
        if ($receipt->financialReport && !in_array($receipt->financialReport->project_id, $managedProjectIds)) {
            abort(403, 'Access denied. You can only view receipts for projects you manage.');
        }

        $receipt->load(['uploader', 'financialReport', 'liquidatedForm']);

        return view('pm.receipts.show', compact('receipt'));
    }
}
