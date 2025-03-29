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
    <title>Admin Panel - Staff Shift Scheduler</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="dashboard-container admin">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
                <span>Shift Scheduler</span>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#" id="manage-schedules-btn"><i class="fas fa-calendar-plus"></i> Manage Schedules</a></li>
                    <li><a href="#" id="manage-swaps-btn"><i class="fas fa-exchange-alt"></i> Manage Swaps</a></li>
                    <li><a href="#" id="manage-users-btn"><i class="fas fa-users"></i> Manage Users</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
            <div class="user-profile">
                <div class="avatar admin">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <span class="username"><?php echo $current_user['name']; ?></span>
                    <span class="role">Administrator</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1>Admin Dashboard</h1>
                <div class="admin-stats">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="stat-value" id="total-faculty">0</span>
                            <span class="stat-label">Faculty Members</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <span class="stat-value" id="total-shifts">0</span>
                            <span class="stat-label">Shifts This Week</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-exchange-alt"></i>
                        <div>
                            <span class="stat-value" id="pending-swaps">0</span>
                            <span class="stat-label">Pending Swaps</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <!-- Default view - Upcoming Shifts -->
                <div class="section" id="upcoming-shifts-section">
                    <div class="section-header">
                        <h2>Upcoming Shifts</h2>
                        <div class="date-range">
                            <button id="admin-prev-week"><i class="fas fa-chevron-left"></i></button>
                            <span id="admin-week-range">Loading...</span>
                            <button id="admin-next-week"><i class="fas fa-chevron-right"></i></button>
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
                            <tbody id="admin-schedule-body">
                                <!-- Schedule data will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Manage Schedules View (hidden by default) -->
                <div class="section hidden" id="manage-schedules-section">
                    <div class="section-header">
                        <h2>Add New Schedule</h2>
                    </div>
                    <form id="add-schedule-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="schedule-faculty">Faculty Member</label>
                                <select id="schedule-faculty" required>
                                    <option value="">Select Faculty</option>
                                    <!-- Faculty options will be loaded via AJAX -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="schedule-shift">Shift</label>
                                <select id="schedule-shift" required>
                                    <option value="">Select Shift</option>
                                    <!-- Shift options will be loaded via AJAX -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="schedule-date">Date</label>
                                <input type="date" id="schedule-date" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Add Schedule</button>
                    </form>
                </div>

                <!-- Manage Swaps View (hidden by default) -->
                <div class="section hidden" id="manage-swaps-section">
                    <div class="section-header">
                        <h2>Pending Swap Requests</h2>
                    </div>
                    <div class="swaps-list" id="admin-swaps-list">
                        <!-- Swap requests will be loaded here via AJAX -->
                    </div>
                </div>

                <!-- Manage Users View (hidden by default) -->
                <div class="section hidden" id="manage-users-section">
                    <div class="section-header">
                        <h2>Faculty Members</h2>
                        <button class="btn-primary" id="add-user-btn">Add New Faculty</button>
                    </div>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-list">
                            <!-- Users will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Add User Modal -->
                <div class="modal" id="add-user-modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h2>Add New Faculty Member</h2>
                        <form id="add-user-form">
                            <div class="form-group">
                                <label for="new-user-name">Full Name</label>
                                <input type="text" id="new-user-name" required>
                            </div>
                            <div class="form-group">
                                <label for="new-user-username">Username</label>
                                <input type="text" id="new-user-username" required>
                            </div>
                            <div class="form-group">
                                <label for="new-user-email">Email</label>
                                <input type="email" id="new-user-email" required>
                            </div>
                            <div class="form-group">
                                <label for="new-user-password">Password</label>
                                <input type="password" id="new-user-password" required>
                            </div>
                            <button type="submit" class="btn-primary">Add Faculty</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>