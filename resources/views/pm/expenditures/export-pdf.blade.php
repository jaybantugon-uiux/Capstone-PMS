<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expenditures Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .filters p {
            margin: 2px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-draft {
            background-color: #ffc107;
            color: #000;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-submitted {
            background-color: #28a745;
            color: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            body {
                margin: 0;
                font-size: 10px;
            }
            .header {
                margin-bottom: 20px;
            }
            th, td {
                padding: 6px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Expenditures Report</h1>
        <p>Generated for: {{ $user->name }}</p>
        <p>Generated on: {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(isset($filters['status']) && $filters['status'])
            <p><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
        @endif
        @if(isset($filters['category']) && $filters['category'])
            <p><strong>Category:</strong> {{ ucfirst($filters['category']) }}</p>
        @endif
        @if(isset($filters['date_range']) && $filters['date_range'])
            <p><strong>Date Range:</strong> {{ ucfirst($filters['date_range']) }}</p>
        @endif
        @if(isset($filters['project_id']) && $filters['project_id'])
            <p><strong>Project ID:</strong> {{ $filters['project_id'] }}</p>
        @endif
    </div>
    @endif

    @if($expenditures->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Location</th>
                    <th>Vendor</th>
                    <th>Payment Method</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenditures as $expenditure)
                <tr>
                    <td>{{ $expenditure->id }}</td>
                    <td>{{ $expenditure->project ? $expenditure->project->name : 'N/A' }}</td>
                    <td>{{ $expenditure->expense_date->format('M j, Y') }}</td>
                    <td>{{ ucfirst($expenditure->category) }}</td>
                    <td>{{ Str::limit($expenditure->description, 50) }}</td>
                    <td>₱{{ number_format($expenditure->amount, 2) }}</td>
                    <td>{{ $expenditure->location ?: 'N/A' }}</td>
                    <td>{{ $expenditure->vendor_supplier ?: 'N/A' }}</td>
                    <td>{{ ucfirst($expenditure->payment_method) }}</td>
                    <td>{{ $expenditure->reference_number ?: 'N/A' }}</td>
                    <td>
                        <span class="status-{{ $expenditure->status }}">
                            {{ ucfirst($expenditure->status) }}
                        </span>
                    </td>
                    <td>{{ $expenditure->submitted_at ? $expenditure->submitted_at->format('M j, Y g:i A') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <div class="summary-row">
                <span>Total Expenditures:</span>
                <span>{{ $expenditures->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>₱{{ number_format($expenditures->sum('amount'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Draft Expenditures:</span>
                <span>{{ $expenditures->where('status', 'draft')->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Submitted Expenditures:</span>
                <span>{{ $expenditures->where('status', 'submitted')->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Average Amount:</span>
                <span>₱{{ number_format($expenditures->avg('amount'), 2) }}</span>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>No expenditures found</h3>
            <p>No expenditures match the current filters.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Project Management System</p>
        <p>Page generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
