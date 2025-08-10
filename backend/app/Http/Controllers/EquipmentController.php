<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\EquipmentStockLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentController extends Controller
{
    // Show all active equipment with optimized query
    public function index()
    {
        try {
            $equipment = Equipment::active()
                ->select('id', 'name', 'description', 'stock', 'min_stock_level', 'updated_at')
                ->orderBy('name')
                ->get();
            
            return view('equipment.index', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load equipment list.');
        } 
    }

    // Show archived equipment
    public function archived()
    {
        try {
            $equipment = Equipment::archived()
                ->select('id', 'name', 'description', 'stock', 'min_stock_level', 'updated_at')
                ->orderBy('name')
                ->get();
            
            return view('equipment.archived', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment archived error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load archived equipment list.');
        }
    }

    // Show form to create new equipment
    public function create()
    {
        return view('equipment.create');
    }

    // Store new equipment
    public function store(Request $request)
    {
        $request->validate(Equipment::validationRules(), [
            'name.unique' => 'This equipment name already exists.',
            'name.required' => 'Equipment name is required.',
            'stock.required' => 'Initial stock amount is required.',
            'stock.integer' => 'Stock must be a valid number.',
            'stock.min' => 'Stock cannot be negative.',
            'stock.max' => 'Stock cannot exceed 10,000 units.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $equipment = Equipment::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'stock' => $request->stock,
                    'min_stock_level' => $request->min_stock_level ?? 10,
                ]);

                // Only create log if initial stock is greater than 0
                if ($request->stock > 0) {
                    EquipmentStockLog::create([
                        'equipment_id' => $equipment->id,
                        'user_id' => Auth::id(),
                        'change' => $request->stock,
                        'note' => 'Initial stock addition when equipment was created',
                    ]);
                }
            });

            return redirect()->route('equipment.index')->with('success', 'Equipment added successfully!');
        } catch (\Exception $e) {
            Log::error('Equipment creation error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add equipment. Please try again.');
        }
    }

    // Show equipment details
    public function show($id)
    {
        try {
            $equipment = Equipment::with(['stockLogs' => function($query) {
                $query->with('user:id,first_name,last_name,username')->latest()->limit(10);
            }])->findOrFail($id);
            
            return view('equipment.show', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment show error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Equipment not found.');
        }
    }

    // Show form to edit equipment
    public function edit($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            
            if ($equipment->archived) {
                return redirect()->route('equipment.index')
                    ->with('error', 'Cannot edit archived equipment. Please restore it first.');
            }
            
            return view('equipment.edit', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment edit form error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Equipment not found.');
        }
    }

    // Update equipment
    public function update(Request $request, $id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            
            if ($equipment->archived) {
                return redirect()->route('equipment.index')
                    ->with('error', 'Cannot update archived equipment.');
            }
            
            $request->validate(Equipment::validationRules(true, $id), [
                'name.unique' => 'This equipment name already exists.',
                'name.required' => 'Equipment name is required.',
                'stock.required' => 'Stock amount is required.',
                'stock.integer' => 'Stock must be a valid number.',
                'stock.min' => 'Stock cannot be negative.',
                'stock.max' => 'Stock cannot exceed 10,000 units.',
            ]);

            DB::transaction(function () use ($request, $equipment) {
                $oldStock = $equipment->stock;
                $newStock = $request->stock;
                
                $equipment->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'stock' => $newStock,
                    'min_stock_level' => $request->min_stock_level ?? 10,
                ]);

                // Log stock change if stock was manually adjusted
                $stockDiff = $newStock - $oldStock;
                if ($stockDiff != 0) {
                    EquipmentStockLog::create([
                        'equipment_id' => $equipment->id,
                        'user_id' => Auth::id(),
                        'change' => $stockDiff,
                        'note' => 'Stock adjusted during equipment update',
                    ]);
                }
            });

            return redirect()->route('equipment.index')->with('success', 'Equipment updated successfully!');
        } catch (\Exception $e) {
            Log::error('Equipment update error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update equipment. Please try again.');
        }
    }

    // Archive equipment
    public function archive($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            
            if ($equipment->archived) {
                return redirect()->back()->with('error', 'Equipment is already archived.');
            }
            
            $equipment->archive();
            
            return redirect()->route('equipment.index')
                ->with('success', 'Equipment archived successfully!');
        } catch (\Exception $e) {
            Log::error('Equipment archive error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to archive equipment.');
        }
    }

    // Restore archived equipment
    public function restore($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            
            if (!$equipment->archived) {
                return redirect()->back()->with('error', 'Equipment is not archived.');
            }
            
            $equipment->restore();
            
            return redirect()->route('equipment.index')
                ->with('success', 'Equipment restored successfully!');
        } catch (\Exception $e) {
            Log::error('Equipment restore error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore equipment.');
        }
    }

    // Show restock form
    public function restockForm($id)
    {
        try {
            $equipment = Equipment::select('id', 'name', 'stock', 'description', 'archived')
                ->findOrFail($id);
            
            if ($equipment->archived) {
                return redirect()->route('equipment.index')
                    ->with('error', 'Cannot restock archived equipment.');
            }
            
            return view('equipment.restock', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment restock form error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Equipment not found.');
        }
    }

    // Handle restocking with transaction
    public function restock(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:10000',
            'note' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Restock amount is required.',
            'amount.integer' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',
            'amount.max' => 'Cannot restock more than 10,000 units at once.',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $equipment = Equipment::lockForUpdate()->findOrFail($id);
                
                if ($equipment->archived) {
                    throw new \Exception('Cannot restock archived equipment.');
                }
                
                // Check if new stock would exceed maximum
                $newStock = $equipment->stock + $request->amount;
                if ($newStock > 10000) {
                    throw new \Exception('Total stock would exceed maximum limit of 10,000 units.');
                }
                
                $equipment->increment('stock', $request->amount);

                EquipmentStockLog::create([
                    'equipment_id' => $equipment->id,
                    'user_id' => Auth::id(),
                    'change' => $request->amount,
                    'note' => $request->note ?: 'Equipment restocked',
                ]);
            });

            return redirect()->route('equipment.index')
                ->with('success', 'Equipment restocked successfully!');
                
        } catch (\Exception $e) {
            Log::error('Equipment restock error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Failed to restock equipment. Please try again.');
        }
    }

    // Show equipment use form
    public function useForm($id)
    {
        try {
            $equipment = Equipment::select('id', 'name', 'stock', 'archived')->findOrFail($id);
            
            if ($equipment->archived) {
                return redirect()->route('equipment.index')
                    ->with('error', 'Cannot use archived equipment.');
            }
            
            return view('equipment.use', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Equipment use form error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Equipment not found.');
        }
    }

    // Equipment usage/withdrawal
    public function useEquipment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:10000',
            'note' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Usage amount is required.',
            'amount.integer' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $equipment = Equipment::lockForUpdate()->findOrFail($id);
                
                if ($equipment->archived) {
                    throw new \Exception('Cannot use archived equipment.');
                }
                
                if ($equipment->stock < $request->amount) {
                    throw new \Exception('Insufficient stock available. Current stock: ' . $equipment->stock);
                }
                
                $equipment->decrement('stock', $request->amount);

                EquipmentStockLog::create([
                    'equipment_id' => $equipment->id,
                    'user_id' => Auth::id(),
                    'change' => -$request->amount,
                    'note' => $request->note ?: 'Equipment usage recorded',
                ]);
            });

            return redirect()->route('equipment.index')
                ->with('success', 'Equipment usage recorded successfully!');
                
        } catch (\Exception $e) {
            Log::error('Equipment usage error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Failed to record equipment usage.');
        }
    }

    // Show stock logs with pagination and optimized query
    public function logs($id)
    {
        try {
            $equipment = Equipment::select('id', 'name')->findOrFail($id);
            
            $logs = EquipmentStockLog::with(['user:id,first_name,last_name,username'])
                ->where('equipment_id', $id)
                ->select('id', 'user_id', 'change', 'note', 'created_at')
                ->latest()
                ->paginate(20);
            
            return view('equipment.logs', compact('equipment', 'logs'));
        } catch (\Exception $e) {
            Log::error('Equipment logs error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Unable to load equipment logs.');
        }
    }

    // Bulk restock multiple equipment
    public function bulkRestockForm()
    {
        try {
            $equipment = Equipment::active()
                ->select('id', 'name', 'stock')
                ->orderBy('name')
                ->get();
            
            return view('equipment.bulk-restock', compact('equipment'));
        } catch (\Exception $e) {
            Log::error('Bulk restock form error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Unable to load bulk restock form.');
        }
    }

    // Handle bulk restocking
    public function bulkRestock(Request $request)
    {
        $request->validate([
            'equipment' => 'required|array|min:1',
            'equipment.*.id' => 'required|exists:equipment,id',
            'equipment.*.amount' => 'required|integer|min:1|max:10000',
            'note' => 'nullable|string|max:255',
        ], [
            'equipment.required' => 'Please select at least one equipment to restock.',
            'equipment.*.amount.min' => 'Amount must be at least 1.',
            'equipment.*.amount.max' => 'Cannot restock more than 10,000 units at once.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $userId = Auth::id();
                $note = $request->note ?: 'Bulk restock operation';
                
                foreach ($request->equipment as $item) {
                    if (isset($item['amount']) && $item['amount'] > 0) {
                        $equipment = Equipment::lockForUpdate()->findOrFail($item['id']);
                        
                        if ($equipment->archived) {
                            throw new \Exception("Equipment '{$equipment->name}' is archived and cannot be restocked.");
                        }
                        
                        // Check if new stock would exceed maximum
                        $newStock = $equipment->stock + $item['amount'];
                        if ($newStock > 10000) {
                            throw new \Exception("Equipment '{$equipment->name}' would exceed maximum stock limit.");
                        }
                        
                        // Update stock
                        $equipment->increment('stock', $item['amount']);
                        
                        // Log the change
                        EquipmentStockLog::create([
                            'equipment_id' => $item['id'],
                            'user_id' => $userId,
                            'change' => $item['amount'],
                            'note' => $note,
                        ]);
                    }
                }
            });

            return redirect()->route('equipment.index')
                ->with('success', 'Equipment bulk restocked successfully!');
                
        } catch (\Exception $e) {
            Log::error('Bulk restock error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Failed to bulk restock equipment. Please try again.');
        }
    }

    // Low stock alert
    public function lowStock($threshold = 10)
    {
        try {
            $threshold = max(1, min(100, (int)$threshold)); // Limit threshold between 1-100
            
            $lowStockEquipment = Equipment::active()
                ->select('id', 'name', 'stock', 'min_stock_level')
                ->lowStock($threshold)
                ->orderBy('stock')
                ->get();
            
            return view('equipment.low-stock', compact('lowStockEquipment', 'threshold'));
        } catch (\Exception $e) {
            Log::error('Low stock view error: ' . $e->getMessage());
            return redirect()->route('equipment.index')->with('error', 'Unable to load low stock report.');
        }
    }

    public function apiIndex()
    {
        try {
            $equipment = Equipment::select('id', 'name', 'description', 'stock as quantity', 'archived')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 'success',
                'equipment' => $equipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to load equipment list.'
            ], 500);
        }
    }

    public function apiRestock(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:10000',
            'note' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Restock amount is required.',
            'amount.integer' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',
            'amount.max' => 'Cannot restock more than 10,000 units at once.',
        ]);

        try {
            DB::transaction(function () use ($request, $id, &$equipment) {
                $equipment = Equipment::lockForUpdate()->findOrFail($id);

                if ($equipment->archived) {
                    throw new \Exception('Cannot restock archived equipment.');
                }

                $newStock = $equipment->stock + $request->amount;
                if ($newStock > 10000) {
                    throw new \Exception('Total stock would exceed maximum limit of 10,000 units.');
                }

                $equipment->increment('stock', $request->amount);

                EquipmentStockLog::create([
                    'equipment_id' => $equipment->id,
                    'user_id' => Auth::id(),
                    'change' => $request->amount,
                    'note' => $request->note ?: 'Equipment restocked',
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Equipment restocked successfully!',
                'equipment' => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'description' => $equipment->description,
                    'quantity' => $equipment->stock,
                    'archived' => $equipment->archived
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Equipment restock error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Failed to restock equipment.'
            ], 500);
        }
    }


    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:equipment,name',
                'quantity' => 'required|integer|min:0|max:10000',
                'description' => 'nullable|string|max:1000'
            ], [
                'name.required' => 'Equipment name is required.',
                'name.unique' => 'This equipment name already exists.',
                'quantity.required' => 'Initial stock amount is required.',
                'quantity.integer' => 'Stock must be a valid number.',
                'quantity.min' => 'Stock cannot be negative.',
                'quantity.max' => 'Stock cannot exceed 10,000 units.',
            ]);

            DB::transaction(function () use ($request, &$equipment) {
                $equipment = Equipment::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'stock' => $request->quantity,
                    'min_stock_level' => 10, // Default minimum stock level
                    'archived' => false
                ]);

                // Only create log if initial stock is greater than 0
                if ($request->quantity > 0) {
                    EquipmentStockLog::create([
                        'equipment_id' => $equipment->id,
                        'user_id' => auth()->id(),
                        'change' => $request->quantity,
                        'note' => 'Initial stock addition when equipment was created via API',
                    ]);
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Equipment added successfully!',
                'equipment' => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'description' => $equipment->description,
                    'quantity' => $equipment->stock,
                    'archived' => $equipment->archived
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Equipment creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add equipment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string'
        ]);

        try {
            $equipment = Equipment::findOrFail($id);
            $equipment->update([
                'name' => $request->name,
                'description' => $request->description,
                'stock' => $request->quantity
            ]);

            return response()->json([
                'status' => 'success',
                'equipment' => $equipment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update equipment.'
            ], 500);
        }
    }

    public function apiActive(Equipment $equipment)
    {
        $equipment->archived = false;
        $equipment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment restored successfully',
        ]);
    }
    
    public function apiArchive($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);

            if ($equipment->archived) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Equipment is already archived.'
                ], 400);
            }

            $equipment->archived = true;
            $equipment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Equipment archived successfully.',
                'equipment' => $equipment
            ]);
        } catch (\Exception $e) {
            Log::error('API archive error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to archive equipment.'
            ], 500);
        }
    }

    public function apiArchived()
    {
        try {
            $equipment = Equipment::where('archived', true)
                ->select('id', 'name', 'description', 'stock as quantity', 'min_stock_level', 'updated_at')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 'success',
                'equipment' => $equipment
            ]);
        } catch (\Exception $e) {
            Log::error('API fetch archived error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to load archived equipment.'
            ], 500);
        }
    }

    public function apiUnarchive($id)
    {
        try {
            $equipment = Equipment::findOrFail($id);

            if (!$equipment->archived) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Equipment is already active.',
                    'equipment' => $equipment
                ]);
            }

            $equipment->archived = false;
            $equipment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Equipment unarchived successfully.',
                'equipment' => $equipment
            ]);
        } catch (\Exception $e) {
            Log::error('API unarchive error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to unarchive equipment.'
            ], 500);
        }
    }


}