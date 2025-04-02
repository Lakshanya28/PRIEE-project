<?php
require_once 'backend/auth.php';

if ($current_user['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --danger-color: #f72585;
            --danger-hover: #e5177b;
            --warning-color: #f8961e;
            --warning-hover: #e88a17;
            --success-color: #4cc9f0;
            --success-hover: #3ab7dd;
            --info-color: #7209b7;
            --info-hover: #5e08a0;
            --light-bg: #f8f9fa;
            --border-color: #e0e0e0;
            --text-color: #333;
            --text-light: #6c757d;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background:   #f5f7fa;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .logo {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo i {
            font-size: 24px;
            color: var(--success-color);
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 15px;
        }

        nav li a:hover, nav li.active a {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--success-color);
        }

        nav li a i {
            width: 20px;
            text-align: center;
        }

        .user-profile {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .avatar.admin {
            background: var(--success-color);
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .username {
            font-weight: 500;
            font-size: 14px;
        }

        .role {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
        }

        .header {
            padding: 20px 30px;
            background: white;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: var(--text-color);
        }

        .content {
            padding: 30px;
            flex: 1;
        }

        /* Section Styles */
        .section {
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        /* Form Styles */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 25px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-group select, 
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
            background-color: white;
        }

        .form-group select:focus, 
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* Button Styles */
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin: 0 25px 25px;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 2px;
        }

        .action-btn i {
            font-size: 12px;
        }

        .approve-swap {
            background-color: var(--success-color);
            color: white;
        }

        .approve-swap:hover {
            background-color: var(--success-hover);
        }

        .reject-swap {
            background-color: var(--danger-color);
            color: white;
        }

        .reject-swap:hover {
            background-color: var(--danger-hover);
        }

        .delete-schedule {
            background-color: var(--danger-color);
            color: white;
        }

        .delete-schedule:hover {
            background-color: var(--danger-hover);
        }

        /* Date Range Navigation */
        .date-range {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .date-range button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-light);
            font-size: 16px;
            transition: color 0.2s;
            padding: 5px 10px;
            border-radius: var(--radius-sm);
        }

        .date-range button:hover {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.1);
        }

        .date-range span {
            font-weight: 500;
            min-width: 180px;
            text-align: center;
        }

        /* Table Styles */
        .schedule-table-container {
            overflow-x: auto;
            padding: 0 25px 25px;
        }

        .schedule-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: var(--radius-sm);
            overflow: hidden;
        }

        .schedule-table th {
            background-color: var(--light-bg);
            color: var(--text-color);
            padding: 15px;
            text-align: left;
            font-weight: 500;
            white-space: nowrap;
        }

        .schedule-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .schedule-table tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tr:hover td {
            background-color: rgba(67, 97, 238, 0.05);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-swap_requested {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        /* Loading Animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fa-spinner {
            animation: spin 1s linear infinite;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group {
                min-width: 100%;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .user-profile {
                display: none;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .date-range {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container admin">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
                <span>Shift Scheduler</span>
            </div>
            <nav>
                <ul>
                    <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="manage_schedules.php"><i class="fas fa-calendar-plus"></i> Manage Schedules</a></li>
                    <li><a href="manage_swaps.php"><i class="fas fa-exchange-alt"></i> Manage Swaps</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
            <div class="user-profile">
                <div class="avatar admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($current_user['name']); ?></span>
                    <span class="role">Administrator</span>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="header">
                <h1>Manage Schedules</h1>
            </header>

            <div class="content">
                <div class="section">
                    <div class="section-header">
                        <h2>Add New Schedule</h2>
                    </div>
                    <form id="add-schedule-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="schedule-faculty">Faculty Member</label>
                                <select id="schedule-faculty" required>
                                    <option value="">Select Faculty</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="schedule-shift">Shift</label>
                                <select id="schedule-shift" required>
                                    <option value="">Select Shift</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="schedule-date">Date</label>
                                <input type="date" id="schedule-date" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
                    </form>
                </div>

                <div class="section">
                    <div class="section-header">
                        <h2>Current Schedules</h2>
                        <div class="date-range">
                            <button id="prev-week"><i class="fas fa-chevron-left"></i> Previous</button>
                            <span id="week-range">Loading...</span>
                            <button id="next-week">Next <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="schedule-table-container">
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Faculty</th>
                                    <th>Shift</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="schedule-body">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentWeekOffset = 0;
        const today = new Date();

        // Load faculty and shifts for dropdowns
        function loadDropdowns() {
            Promise.all([
                fetch('../backend/get_users.php'),
                fetch('../backend/get_shifts.php')
            ])
            .then(([usersRes, shiftsRes]) => Promise.all([usersRes.json(), shiftsRes.json()]))
            .then(([usersData, shiftsData]) => {
                const facultySelect = document.getElementById('schedule-faculty');
                if (facultySelect && usersData.success) {
                    facultySelect.innerHTML = '<option value="">Select Faculty</option>';
                    usersData.users.forEach(user => {
                        if (user.role === 'faculty') {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.name;
                            facultySelect.appendChild(option);
                        }
                    });
                }

                const shiftSelect = document.getElementById('schedule-shift');
                if (shiftSelect && shiftsData.success) {
                    shiftSelect.innerHTML = '<option value="">Select Shift</option>';
                    shiftsData.shifts.forEach(shift => {
                        const option = document.createElement('option');
                        option.value = shift.id;
                        option.textContent = `${shift.name} (${shift.start_time.substring(0, 5)} - ${shift.end_time.substring(0, 5)})`;
                        shiftSelect.appendChild(option);
                    });
                }
            });
        }

        // Load schedules for the current week
        function loadSchedules() {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + (currentWeekOffset * 7) - today.getDay() + 1);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);

            document.getElementById('week-range').textContent = 
                `${weekStart.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} - ${weekEnd.toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}`;

            fetch('../backend/get_schedules.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: weekStart.toISOString().split('T')[0],
                    end_date: weekEnd.toISOString().split('T')[0]
                })
            })
            .then(response => response.json())
            .then(data => {
                const scheduleBody = document.getElementById('schedule-body');
                if (scheduleBody) {
                    scheduleBody.innerHTML = '';
                    if (data.success && data.schedules.length > 0) {
                        data.schedules.forEach(schedule => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${new Date(schedule.date).toLocaleDateString('en-US', {weekday: 'short', month: 'short', day: 'numeric'})}</td>
                                <td>${schedule.user_name}</td>
                                <td>${schedule.shift_name}</td>
                                <td>${schedule.start_time.substring(0, 5)} - ${schedule.end_time.substring(0, 5)}</td>
                                <td><span class="status-badge status-${schedule.status.toLowerCase().replace(' ', '_')}">${schedule.status.charAt(0).toUpperCase() + schedule.status.slice(1)}</span></td>
                                <td>
                                    ${schedule.status === 'Swap Requested' ? `
                                    <button class="action-btn approve-swap" data-id="${schedule.swap_id}" data-schedule-id="${schedule.id}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="action-btn reject-swap" data-id="${schedule.swap_id}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    ` : ''}
                                    <button class="action-btn delete-schedule" data-id="${schedule.id}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            `;
                            scheduleBody.appendChild(row);
                        });

                        // Add event listeners to action buttons
                        document.querySelectorAll('.approve-swap').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const swapId = this.getAttribute('data-id');
                                const scheduleId = this.getAttribute('data-schedule-id');
                                handleSwapAction(swapId, 'approved', scheduleId);
                            });
                        });

                        document.querySelectorAll('.reject-swap').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const swapId = this.getAttribute('data-id');
                                handleSwapAction(swapId, 'rejected');
                            });
                        });

                        document.querySelectorAll('.delete-schedule').forEach(btn => {
                            btn.addEventListener('click', function() {
                                if (confirm('Are you sure you want to delete this schedule?')) {
                                    deleteSchedule(this.getAttribute('data-id'));
                                }
                            });
                        });
                    } else {
                        scheduleBody.innerHTML = '<tr><td colspan="6" class="empty-state">No schedules found for this week</td></tr>';
                    }
                }
            });
        }

        // Handle swap approval/rejection
        function handleSwapAction(swapId, action, scheduleId = null) {
            fetch('../backend/approve_swap.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    swap_id: swapId,
                    action: action,
                    schedule_id: scheduleId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Swap request ${action} successfully!`);
                    loadSchedules();
                } else {
                    alert('Error: ' + (data.message || `Failed to ${action} swap`));
                }
            });
        }

        // Delete a schedule
        function deleteSchedule(scheduleId) {
            fetch('../backend/delete_schedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    schedule_id: scheduleId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Schedule deleted successfully!');
                    loadSchedules();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete schedule'));
                }
            });
        }

        // Add new schedule
        document.getElementById('add-schedule-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const facultyId = document.getElementById('schedule-faculty').value;
            const shiftId = document.getElementById('schedule-shift').value;
            const date = document.getElementById('schedule-date').value;

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;

            fetch('../backend/add_schedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: facultyId,
                    shift_id: shiftId,
                    date: date
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Schedule added successfully!');
                    this.reset();
                    loadSchedules();
                } else {
                    alert('Error: ' + (data.message || 'Failed to add schedule'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the schedule');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Week navigation
        document.getElementById('prev-week').addEventListener('click', function() {
            currentWeekOffset--;
            loadSchedules();
        });

        document.getElementById('next-week').addEventListener('click', function() {
            currentWeekOffset++;
            loadSchedules();
        });

        // Initial load
        loadDropdowns();
        loadSchedules();
    });
    </script>
</body>
</html>