<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report - {{ $financialReport->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 16px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .summary-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .summary-value.positive {
            color: #27ae60;
        }
        .summary-value.negative {
            color: #e74c3c;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .description h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .description p {
            margin: 0;
            color: #555;
            line-height: 1.5;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #f39c12; color: white; }
        .status-generated { background: #3498db; color: white; }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <div class="subtitle">{{ $financialReport->title }}</div>
        <div class="subtitle">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <div class="info-grid">
        <div class="info-section">
            <h3>Report Information</h3>
            <div class="info-item">
                <span class="info-label">Report Type:</span>
                <span class="info-value">{{ ucfirst($financialReport->report_type) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Period:</span>
                <span class="info-value">{{ $financialReport->period }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ strtolower($financialReport->status) }}">
                        {{ ucfirst($financialReport->status) }}
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Created:</span>
                <span class="info-value">{{ $financialReport->created_at->format('M j, Y') }}</span>
            </div>
            @if($financialReport->generated_at)
            <div class="info-item">
                <span class="info-label">Generated:</span>
                <span class="info-value">{{ $financialReport->generated_at->format('M j, Y g:i A') }}</span>
            </div>
            @endif
        </div>

        <div class="info-section">
            <h3>Project Information</h3>
            @if($financialReport->project)
            <div class="info-item">
                <span class="info-label">Project:</span>
                <span class="info-value">{{ $financialReport->project->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Client:</span>
                <span class="info-value">{{ $financialReport->project->client_name ?? 'N/A' }}</span>
            </div>
            @else
            <div class="info-item">
                <span class="info-label">Project:</span>
                <span class="info-value">General Report</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">Currency:</span>
                <span class="info-value">{{ strtoupper($financialReport->currency) }}</span>
            </div>
            @if($financialReport->exchange_rate && $financialReport->exchange_rate != 1)
            <div class="info-item">
                <span class="info-label">Exchange Rate:</span>
                <span class="info-value">{{ number_format($financialReport->exchange_rate, 4) }}</span>
            </div>
            @endif
        </div>
    </div>



    @if($financialReport->description || $financialReport->notes)
    <div class="section">
        <h3>Description & Notes</h3>
        @if($financialReport->description)
        <div class="description">
            <h4>Description</h4>
            <p>{{ $financialReport->description }}</p>
        </div>
        @endif
        @if($financialReport->notes)
        <div class="description">
            <h4>Notes</h4>
            <p>{{ $financialReport->notes }}</p>
        </div>
        @endif
    </div>
    @endif

    @if($financialReport->expenditures->count() > 0)
    <div class="section">
        <h3>Expenditures ({{ $financialReport->expenditures->count() }})</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financialReport->expenditures as $expenditure)
                <tr>
                    <td>{{ $expenditure->expense_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($expenditure->category) }}</td>
                    <td>{{ Str::limit($expenditure->description, 50) }}</td>
                    <td>{{ $expenditure->project->name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($expenditure->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($financialReport->receipts->count() > 0)
    <div class="section">
        <h3>Receipts ({{ $financialReport->receipts->count() }})</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Vendor</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financialReport->receipts as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($receipt->receipt_type) }}</td>
                    <td>{{ Str::limit($receipt->description, 50) }}</td>
                    <td>{{ $receipt->vendor_name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($receipt->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($financialReport->liquidatedForm)
    <div class="section">
        <h3>Linked Liquidated Form</h3>
        <div class="info-section">
            <div class="info-item">
                <span class="info-label">Form ID:</span>
                <span class="info-value">{{ $financialReport->liquidatedForm->id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ strtolower($financialReport->liquidatedForm->status) }}">
                        {{ ucfirst($financialReport->liquidatedForm->status) }}
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Amount:</span>
                <span class="info-value">{{ number_format($financialReport->liquidatedForm->total_amount, 2) }} {{ strtoupper($financialReport->currency) }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated by the Project Management System</p>
        <p>Report ID: {{ $financialReport->id }} | Page 1 of 1</p>
    </div>
</body>
</html>
