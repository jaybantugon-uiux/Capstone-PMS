<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report Excel Export - {{ $financialReport->title }}</title>
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
        .export-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #27ae60;
        }
        .export-info h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .export-info p {
            margin: 5px 0;
            color: #555;
        }
        .data-section {
            margin-bottom: 30px;
        }
        .data-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 15px;
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
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-item {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
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
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .download-section {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 30px;
        }
        .download-section h3 {
            margin: 0 0 15px 0;
            color: #27ae60;
        }
        .download-btn {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
        }
        .download-btn:hover {
            background: #229954;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report Excel Export</h1>
        <div class="subtitle">{{ $financialReport->title }}</div>
        <div class="subtitle">Prepared for Excel Export on {{ now()->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <div class="export-info">
        <h3>Export Information</h3>
        <p><strong>Report ID:</strong> {{ $financialReport->id }}</p>
        <p><strong>Report Type:</strong> {{ ucfirst($financialReport->report_type) }}</p>
        <p><strong>Period:</strong> {{ $financialReport->period }}</p>
        <p><strong>Currency:</strong> {{ strtoupper($financialReport->currency) }}</p>
        <p><strong>Filename:</strong> {{ $filename }}</p>
        <p><strong>Total Records:</strong> {{ $excelData['total_records'] ?? 0 }}</p>
    </div>

    <div class="download-section">
        <h3>Download Excel File</h3>
        <p>The Excel file contains all financial data in a structured format suitable for analysis and reporting.</p>
        <a href="#" class="download-btn" onclick="downloadExcel()">
            <i class="fas fa-download"></i> Download Excel File
        </a>
        <a href="{{ route('finance.financial-reports.show', $financialReport) }}" class="download-btn" style="background: #3498db;">
            <i class="fas fa-arrow-left"></i> Back to Report
        </a>
    </div>



    @if(isset($excelData['expenditures']) && count($excelData['expenditures']) > 0)
    <div class="data-section">
        <h3>Expenditures Data ({{ count($excelData['expenditures']) }} records)</h3>
        <p>This data will be exported to the "Expenditures" worksheet in the Excel file.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($excelData['expenditures'], 0, 10) as $expenditure)
                <tr>
                    <td>{{ $expenditure['id'] }}</td>
                    <td>{{ $expenditure['expense_date'] }}</td>
                    <td>{{ ucfirst($expenditure['category']) }}</td>
                    <td>{{ Str::limit($expenditure['description'], 50) }}</td>
                    <td>{{ $expenditure['project_name'] ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($expenditure['amount'], 2) }}</td>
                    <td>{{ ucfirst($expenditure['payment_method']) }}</td>
                    <td>{{ ucfirst($expenditure['status']) }}</td>
                </tr>
                @endforeach
                @if(count($excelData['expenditures']) > 10)
                <tr>
                    <td colspan="8" style="text-align: center; font-style: italic; color: #7f8c8d;">
                        ... and {{ count($excelData['expenditures']) - 10 }} more records
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($excelData['receipts']) && count($excelData['receipts']) > 0)
    <div class="data-section">
        <h3>Receipts Data ({{ count($excelData['receipts']) }} records)</h3>
        <p>This data will be exported to the "Receipts" worksheet in the Excel file.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Vendor</th>
                    <th>Amount</th>
                    <th>Reference</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($excelData['receipts'], 0, 10) as $receipt)
                <tr>
                    <td>{{ $receipt['id'] }}</td>
                    <td>{{ $receipt['receipt_date'] }}</td>
                    <td>{{ ucfirst($receipt['receipt_type']) }}</td>
                    <td>{{ Str::limit($receipt['description'], 50) }}</td>
                    <td>{{ $receipt['vendor_name'] ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($receipt['amount'], 2) }}</td>
                    <td>{{ $receipt['reference_number'] ?? 'N/A' }}</td>
                    <td>{{ ucfirst($receipt['status']) }}</td>
                </tr>
                @endforeach
                @if(count($excelData['receipts']) > 10)
                <tr>
                    <td colspan="8" style="text-align: center; font-style: italic; color: #7f8c8d;">
                        ... and {{ count($excelData['receipts']) - 10 }} more records
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($excelData['liquidated_form']))
    <div class="data-section">
        <h3>Linked Liquidated Form Data</h3>
        <p>This data will be exported to the "Liquidated Form" worksheet in the Excel file.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Form ID</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Created Date</th>
                    <th>Submitted Date</th>
                    
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $excelData['liquidated_form']['id'] }}</td>
                    <td>{{ ucfirst($excelData['liquidated_form']['status']) }}</td>
                    <td class="amount">{{ number_format($excelData['liquidated_form']['total_amount'], 2) }}</td>
                    <td>{{ $excelData['liquidated_form']['created_date'] }}</td>
                    <td>{{ $excelData['liquidated_form']['submitted_date'] ?? 'N/A' }}</td>
                    
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="data-section">
        <h3>Excel File Structure</h3>
        <p>The Excel file will contain the following worksheets:</p>
        <ul>
            
            @if(isset($excelData['expenditures']) && count($excelData['expenditures']) > 0)
            <li><strong>Expenditures:</strong> Detailed expenditure records</li>
            @endif
            @if(isset($excelData['receipts']) && count($excelData['receipts']) > 0)
            <li><strong>Receipts:</strong> Detailed receipt records</li>
            @endif
            @if(isset($excelData['liquidated_form']))
            <li><strong>Liquidated Form:</strong> Linked liquidated form data</li>
            @endif
        </ul>
    </div>

    <div class="footer">
        <p>This Excel export was generated by the Project Management System</p>
        <p>Report ID: {{ $financialReport->id }} | Export Date: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <script>
        function downloadExcel() {
            // This would typically trigger the actual Excel download
            // For now, we'll show an alert
            alert('Excel download functionality would be implemented here.\n\nIn a real implementation, this would generate and download an Excel file with all the financial data.');
        }
    </script>
</body>
</html>
