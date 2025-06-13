<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Generate Reports</h2>
        
        <form id="reportForm" method="GET">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="report_type" class="form-label">Report Type</label>
                    <select class="form-select" id="report_type" name="report_type" required>
                        <option value="">Select Report Type</option>
                        <option value="project">Project Report</option>
                        <option value="task">Task Report</option>
                        <option value="performance">Performance Report</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" required>
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" required>
                </div>
            </div>
            
            <div class="row mb-3" id="taskFilters" style="display: none;">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all">All</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="all">All</option>
                        <!-- Add site coordinators options here -->
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <button type="button" class="btn btn-success" id="exportBtn" style="display: none;">Export to CSV</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('report_type').addEventListener('change', function() {
            const reportType = this.value;
            const taskFilters = document.getElementById('taskFilters');
            const exportBtn = document.getElementById('exportBtn');
            const form = document.getElementById('reportForm');
            
            // Show/hide task filters
            if (reportType === 'task') {
                taskFilters.style.display = 'block';
            } else {
                taskFilters.style.display = 'none';
            }
            
            // Update form action based on report type
            switch(reportType) {
                case 'project':
                    form.action = '/reports/project';
                    exportBtn.onclick = function() {
                        window.location.href = '/reports/project/export?' + new URLSearchParams(new FormData(form)).toString();
                    };
                    break;
                case 'task':
                    form.action = '/reports/task';
                    exportBtn.onclick = function() {
                        window.location.href = '/reports/task/export?' + new URLSearchParams(new FormData(form)).toString();
                    };
                    break;
                case 'performance':
                    form.action = '/reports/performance';
                    exportBtn.style.display = 'none';
                    break;
                default:
                    form.action = '';
                    exportBtn.style.display = 'none';
            }
            
            if (reportType && reportType !== 'performance') {
                exportBtn.style.display = 'inline-block';
            }
        });
        
        // Set default dates
        const today = new Date();
        const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
        
        document.getElementById('date_to').value = today.toISOString().split('T')[0];
        document.getElementById('date_from').value = lastMonth.toISOString().split('T')[0];
    </script>
</body>
</html>