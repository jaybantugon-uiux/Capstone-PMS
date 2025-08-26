<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidated Form - {{ $liquidatedForm->form_number }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .form-number {
            font-size: 16px;
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            margin-top: 5px;
        }
        
        .amount {
            font-weight: bold;
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        
        .status-flagged { background-color: #f8d7da; color: #721c24; }
        .status-under_review { background-color: #d1ecf1; color: #0c5460; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
        }
        
        .signature-box {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        
        .signature-line {
            height: 50px;
            border-bottom: 1px solid #333;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        @media print {
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <!-- Header -->
    <div class="header">
        <div class="company-name">DESIGN R US</div>
        <div class="form-title">LIQUIDATED FORM</div>
        <div class="form-number">Form #{{ $liquidatedForm->form_number }}</div>
    </div>

    <!-- Form Information -->
    <div class="section">
        <div class="section-title">Form Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Form Number:</div>
                <div class="info-value">{{ $liquidatedForm->form_number }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-{{ $liquidatedForm->status }}">
                        {{ ucfirst(str_replace('_', ' ', $liquidatedForm->status)) }}
                    </span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Title:</div>
                <div class="info-value">{{ $liquidatedForm->title }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Project:</div>
                <div class="info-value">{{ $liquidatedForm->project->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Liquidation Date:</div>
                <div class="info-value">{{ $liquidatedForm->liquidation_date->format('M d, Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Period Covered:</div>
                <div class="info-value">
                    {{ $liquidatedForm->period_covered_start->format('M d, Y') }} - 
                    {{ $liquidatedForm->period_covered_end->format('M d, Y') }}
                </div>
            </div>
        </div>
        
        @if($liquidatedForm->description)
        <div class="info-item" style="margin-top: 20px;">
            <div class="info-label">Description:</div>
            <div class="info-value">{{ $liquidatedForm->description }}</div>
        </div>
        @endif
    </div>

    <!-- Financial Information -->
    <div class="section">
        <div class="section-title">Financial Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Total Amount:</div>
                <div class="info-value amount">₱{{ number_format($liquidatedForm->total_amount, 2) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Receipts:</div>
                <div class="info-value amount">₱{{ number_format($liquidatedForm->total_receipts, 2) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Variance Amount:</div>
                <div class="info-value amount {{ $liquidatedForm->variance_amount >= 0 ? 'text-success' : 'text-danger' }}">
                    ₱{{ number_format($liquidatedForm->variance_amount, 2) }}
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Prepared By:</div>
                <div class="info-value">
                    {{ $liquidatedForm->preparer->first_name ?? 'N/A' }} {{ $liquidatedForm->preparer->last_name ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Included Expenditures -->
    @if($liquidatedForm->expenditures->count() > 0)
    <div class="section">
        <div class="section-title">Included Expenditures</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Purpose</th>
                    <th>Amount</th>
                    <th>Submitter</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($liquidatedForm->expenditures as $expenditure)
                <tr>
                    <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                    <td>{{ $expenditure->purpose }}</td>
                    <td class="text-right">₱{{ number_format($expenditure->amount, 2) }}</td>
                    <td>{{ $expenditure->submitter->first_name ?? 'N/A' }} {{ $expenditure->submitter->last_name ?? '' }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $expenditure->status }}">
                            {{ ucfirst($expenditure->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Review Information -->
    @if($liquidatedForm->reviewer)
    <div class="section">
        <div class="section-title">Review Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Reviewed By:</div>
                <div class="info-value">
                    {{ $liquidatedForm->reviewer->first_name }} {{ $liquidatedForm->reviewer->last_name }}
                    @if($liquidatedForm->reviewed_at)
                        <br><small>{{ $liquidatedForm->reviewed_at->format('M d, Y g:i A') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Flag Information -->
    @if($liquidatedForm->status === 'flagged')
    <div class="section">
        <div class="section-title">Flag Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Flagged By:</div>
                <div class="info-value">
                    {{ $liquidatedForm->flaggedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->flaggedBy->last_name ?? '' }}
                    @if($liquidatedForm->flagged_at)
                        <br><small>{{ $liquidatedForm->flagged_at->format('M d, Y g:i A') }}</small>
                    @endif
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Flag Reason:</div>
                <div class="info-value">{{ $liquidatedForm->flag_reason }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($liquidatedForm->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <div class="info-value">{{ $liquidatedForm->notes }}</div>
    </div>
    @endif

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="text-bold">Prepared By</div>
            <div>{{ $liquidatedForm->preparer->first_name ?? 'N/A' }} {{ $liquidatedForm->preparer->last_name ?? '' }}</div>
            <div>Date: {{ $liquidatedForm->created_at->format('M d, Y') }}</div>
        </div>
        
        @if($liquidatedForm->reviewer)
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="text-bold">Reviewed By</div>
            <div>{{ $liquidatedForm->reviewer->first_name }} {{ $liquidatedForm->reviewer->last_name }}</div>
            <div>Date: {{ $liquidatedForm->reviewed_at ? $liquidatedForm->reviewed_at->format('M d, Y') : 'N/A' }}</div>
        </div>
        @else
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="text-bold">Reviewed By</div>
            <div>_________________</div>
            <div>Date: _____________</div>
        </div>
        @endif
        

    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This document was generated on {{ now()->format('M d, Y g:i A') }}</p>
        <p>Form ID: {{ $liquidatedForm->id }} | Version: 1.0</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
