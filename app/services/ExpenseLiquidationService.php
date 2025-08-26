<?php

namespace App\Services;

use App\Models\FinancialReport;
use App\Models\LiquidatedForm;
use App\Models\DailyExpenditure;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExpenseLiquidationService
{
    /**
     * Automatically create liquidated form from approved financial report
     */
    public function createLiquidatedFormFromFinancialReport(FinancialReport $financialReport): ?LiquidatedForm
    {
        try {
            DB::beginTransaction();

            if ($financialReport->status !== 'approved') {
                throw new \Exception('Financial report must be approved to create liquidated form');
            }

            // Check if liquidated form already exists
            if ($financialReport->liquidatedForm) {
                throw new \Exception('Liquidated form already exists for this financial report');
            }

            $liquidatedForm = $financialReport->createLiquidatedForm();

            if (!$liquidatedForm) {
                throw new \Exception('Failed to create liquidated form');
            }

            DB::commit();

            Log::info('Liquidated form created automatically', [
                'financial_report_id' => $financialReport->id,
                'liquidated_form_id' => $liquidatedForm->id,
                'form_number' => $liquidatedForm->form_number
            ]);

            return $liquidatedForm;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create liquidated form from financial report', [
                'financial_report_id' => $financialReport->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Auto-match receipts to expenditures based on similarity
     */
    public function autoMatchReceiptsToExpenditures(array $receiptIds = null): array
    {
        try {
            DB::beginTransaction();

            $receipts = $receiptIds 
                ? Receipt::whereIn('id', $receiptIds)->whereNull('daily_expenditure_id')->get()
                : Receipt::whereNull('daily_expenditure_id')->where('status', 'verified')->get();

            $expenditures = DailyExpenditure::where('status', 'approved')
                                          ->whereNull('deleted_at')
                                          ->get();

            $matches = [];
            $unmatched = [];

            foreach ($receipts as $receipt) {
                $bestMatch = null;
                $bestScore = 0;

                foreach ($expenditures as $expenditure) {
                    $score = $receipt->getMatchingScore($expenditure);
                    if ($score > $bestScore && $score >= 70) { // Minimum 70% match
                        $bestScore = $score;
                        $bestMatch = $expenditure;
                    }
                }

                if ($bestMatch) {
                    $receipt->matchToExpenditure($bestMatch);
                    $matches[] = [
                        'receipt_id' => $receipt->id,
                        'expenditure_id' => $bestMatch->id,
                        'score' => $bestScore,
                        'receipt_vendor' => $receipt->vendor_name,
                        'expenditure_vendor' => $bestMatch->vendor_supplier,
                        'receipt_amount' => $receipt->amount,
                        'expenditure_amount' => $bestMatch->amount
                    ];
                } else {
                    $unmatched[] = [
                        'receipt_id' => $receipt->id,
                        'receipt_vendor' => $receipt->vendor_name,
                        'receipt_amount' => $receipt->amount,
                        'receipt_date' => $receipt->receipt_date
                    ];
                }
            }

            DB::commit();

            Log::info('Auto-matched receipts to expenditures', [
                'total_receipts' => $receipts->count(),
                'matched_count' => count($matches),
                'unmatched_count' => count($unmatched)
            ]);

            return [
                'matched' => $matches,
                'unmatched' => $unmatched,
                'total_processed' => $receipts->count(),
                'match_rate' => $receipts->count() > 0 ? round((count($matches) / $receipts->count()) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to auto-match receipts to expenditures', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process bulk expenditure approval
     */
    public function bulkApproveExpenditures(array $expenditureIds, User $approver, string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $expenditures = DailyExpenditure::whereIn('id', $expenditureIds)
                                          ->where('status', 'pending')
                                          ->get();

            $approved = [];
            $failed = [];

            foreach ($expenditures as $expenditure) {
                try {
                    if ($expenditure->approve($approver, $notes)) {
                        $approved[] = [
                            'id' => $expenditure->id,
                            'description' => $expenditure->description,
                            'amount' => $expenditure->amount,
                            'project' => $expenditure->project ? $expenditure->project->name : 'N/A'
                        ];
                    } else {
                        $failed[] = [
                            'id' => $expenditure->id,
                            'reason' => 'Cannot be approved in current status'
                        ];
                    }
                } catch (\Exception $e) {
                    $failed[] = [
                        'id' => $expenditure->id,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            Log::info('Bulk expenditure approval completed', [
                'approver_id' => $approver->id,
                'total_requested' => count($expenditureIds),
                'approved_count' => count($approved),
                'failed_count' => count($failed)
            ]);

            return [
                'approved' => $approved,
                'failed' => $failed,
                'total_requested' => count($expenditureIds),
                'success_rate' => count($expenditureIds) > 0 ? round((count($approved) / count($expenditureIds)) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk approve expenditures', [
                'approver_id' => $approver->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process bulk receipt verification
     */
    public function bulkVerifyReceipts(array $receiptIds, User $verifier, string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $receipts = Receipt::whereIn('id', $receiptIds)
                              ->where('status', 'pending')
                              ->get();

            $verified = [];
            $failed = [];

            foreach ($receipts as $receipt) {
                try {
                    if ($receipt->verify($verifier, $notes)) {
                        $verified[] = [
                            'id' => $receipt->id,
                            'vendor_name' => $receipt->vendor_name,
                            'amount' => $receipt->amount,
                            'receipt_type' => $receipt->receipt_type
                        ];
                    } else {
                        $failed[] = [
                            'id' => $receipt->id,
                            'reason' => 'Cannot be verified in current status'
                        ];
                    }
                } catch (\Exception $e) {
                    $failed[] = [
                        'id' => $receipt->id,
                        'reason' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            Log::info('Bulk receipt verification completed', [
                'verifier_id' => $verifier->id,
                'total_requested' => count($receiptIds),
                'verified_count' => count($verified),
                'failed_count' => count($failed)
            ]);

            return [
                'verified' => $verified,
                'failed' => $failed,
                'total_requested' => count($receiptIds),
                'success_rate' => count($receiptIds) > 0 ? round((count($verified) / count($receiptIds)) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk verify receipts', [
                'verifier_id' => $verifier->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate financial report summary
     */
    public function generateFinancialReportSummary(FinancialReport $financialReport): array
    {
        try {
            $expenditures = $financialReport->expenditures()->with(['submitter', 'receipts'])->get();
            $receipts = $financialReport->receipts()->with(['uploader'])->get();

            $summary = [
                'report_info' => [
                    'id' => $financialReport->id,
                    'title' => $financialReport->title,
                    'period' => $financialReport->formatted_period,
                    'status' => $financialReport->status,
                    'created_by' => $financialReport->creator->name
                ],
                'financial_summary' => [
                    'total_expenditures' => $financialReport->total_expenditures,
                    'total_receipts' => $financialReport->total_receipts,
                    'variance_amount' => $financialReport->variance_amount,
                    'variance_percentage' => $financialReport->variance_percentage,
                    'is_variance_positive' => $financialReport->is_variance_positive
                ],
                'expenditures_summary' => [
                    'total_count' => $expenditures->count(),
                    'by_category' => $expenditures->groupBy('category')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('amount'),
                            'percentage' => round(($group->sum('amount') / $expenditures->sum('amount')) * 100, 2)
                        ];
                    }),
                    'by_payment_method' => $expenditures->groupBy('payment_method')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('amount')
                        ];
                    }),
                    'by_submitter' => $expenditures->groupBy('submitted_by')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('amount'),
                            'submitter' => $group->first()->submitter->name
                        ];
                    })
                ],
                'receipts_summary' => [
                    'total_count' => $receipts->count(),
                    'by_type' => $receipts->groupBy('receipt_type')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('amount')
                        ];
                    }),
                    'by_vendor' => $receipts->groupBy('vendor_name')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('amount')
                        ];
                    })
                ],
                'coverage_analysis' => [
                    'expenditures_with_receipts' => $expenditures->filter(function($expenditure) {
                        return $expenditure->receipts->count() > 0;
                    })->count(),
                    'expenditures_without_receipts' => $expenditures->filter(function($expenditure) {
                        return $expenditure->receipts->count() == 0;
                    })->count(),
                    'receipt_coverage_percentage' => $expenditures->count() > 0 
                        ? round(($expenditures->filter(function($expenditure) {
                            return $expenditure->receipts->count() > 0;
                        })->count() / $expenditures->count()) * 100, 2) 
                        : 0
                ]
            ];

            return $summary;

        } catch (\Exception $e) {
            Log::error('Failed to generate financial report summary', [
                'financial_report_id' => $financialReport->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate liquidated form summary
     */
    public function generateLiquidatedFormSummary(LiquidatedForm $liquidatedForm): array
    {
        try {
            $expenditures = $liquidatedForm->expenditures()->with(['submitter', 'receipts'])->get();
            $revisions = $liquidatedForm->revisions()->with(['requester', 'addressedBy'])->get();

            $summary = [
                'form_info' => [
                    'id' => $liquidatedForm->id,
                    'form_number' => $liquidatedForm->form_number,
                    'title' => $liquidatedForm->title,
                    'status' => $liquidatedForm->status,
                    'liquidation_date' => $liquidatedForm->formatted_liquidation_date,
                    'period_covered' => $liquidatedForm->formatted_period,
                    'prepared_by' => $liquidatedForm->preparer->name
                ],
                'financial_summary' => [
                    'total_amount' => $liquidatedForm->total_amount,
                    'total_receipts' => $liquidatedForm->total_receipts,
                    'variance_amount' => $liquidatedForm->variance_amount,
                    'variance_percentage' => $liquidatedForm->variance_percentage,
                    'is_variance_positive' => $liquidatedForm->is_variance_positive
                ],
                'expenditures_summary' => [
                    'total_count' => $expenditures->count(),
                    'total_allocated_amount' => $expenditures->sum('pivot.amount_allocated'),
                    'by_category' => $expenditures->groupBy('category')->map(function($group) {
                        return [
                            'count' => $group->count(),
                            'total_amount' => $group->sum('pivot.amount_allocated')
                        ];
                    })
                ],
                'revision_history' => [
                    'total_revisions' => $revisions->count(),
                    'pending_revisions' => $revisions->where('status', 'pending')->count(),
                    'addressed_revisions' => $revisions->where('status', 'addressed')->count(),
                    'rejected_revisions' => $revisions->where('status', 'rejected')->count()
                ],
                'status_timeline' => [
                    'created_at' => $liquidatedForm->created_at->format('Y-m-d H:i:s'),
                    'reviewed_at' => $liquidatedForm->reviewer ? 'Reviewed by ' . $liquidatedForm->reviewer->name : null,
                    'approved_at' => null,
                    'flagged_at' => $liquidatedForm->flagged_at ? $liquidatedForm->flagged_at->format('Y-m-d H:i:s') : null,
                    'printed_at' => $liquidatedForm->printed_at ? $liquidatedForm->printed_at->format('Y-m-d H:i:s') : null
                ]
            ];

            return $summary;

        } catch (\Exception $e) {
            Log::error('Failed to generate liquidated form summary', [
                'liquidated_form_id' => $liquidatedForm->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate compliance score for liquidated forms
     */
    public function calculateComplianceScore($liquidatedForms): float
    {
        if ($liquidatedForms->count() == 0) return 0;

        $totalForms = $liquidatedForms->count();
        $compliantForms = $liquidatedForms->filter(function($form) {
            return $form->status === 'approved' && 
                   $form->total_receipts > 0 && 
                   abs($form->variance_amount) <= ($form->total_amount * 0.1) &&
                   $form->revisions->where('status', 'pending')->count() == 0;
        })->count();

        return round(($compliantForms / $totalForms) * 100, 2);
    }

    /**
     * Generate audit trail for expense liquidation
     */
    public function generateAuditTrail($startDate = null, $endDate = null): array
    {
        try {
            $query = LiquidatedForm::with(['project', 'preparer', 'reviewer']);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            $liquidatedForms = $query->get();

            $auditTrail = [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'summary' => [
                    'total_forms' => $liquidatedForms->count(),
                    'total_amount' => $liquidatedForms->sum('total_amount'),
                    'average_processing_time' => $liquidatedForms->whereNotNull('approved_at')->avg(function($form) {
                        return $form->created_at->diffInDays($form->approved_at);
                    })
                ],
                'by_status' => $liquidatedForms->groupBy('status')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total_amount' => $group->sum('total_amount'),
                        'percentage' => round(($group->count() / $liquidatedForms->count()) * 100, 2)
                    ];
                }),
                'by_preparer' => $liquidatedForms->groupBy('prepared_by')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total_amount' => $group->sum('total_amount'),
                        'preparer' => $group->first()->preparer->name
                    ];
                }),
                'compliance_metrics' => [
                    'flagged_forms' => $liquidatedForms->where('status', 'flagged')->count(),
                    'revision_requests' => $liquidatedForms->where('status', 'revision_requested')->count(),
                    'clarification_requests' => $liquidatedForms->where('status', 'clarification_requested')->count(),
                    'printed_forms' => $liquidatedForms->whereNotNull('printed_at')->count(),
                    'compliance_score' => $this->calculateComplianceScore($liquidatedForms)
                ]
            ];

            return $auditTrail;

        } catch (\Exception $e) {
            Log::error('Failed to generate audit trail', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
