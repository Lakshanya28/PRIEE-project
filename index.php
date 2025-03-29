<?php
require_once 'backend/auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Staff Shift Scheduler</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
                <span>Shift Scheduler</span>
            </div>
            <nav>
                <ul>
                    <li class="active"><a href="index.php"><i class="fas fa-calendar"></i> My Schedule</a></li>
                    <li><a href="#" id="request-swap-btn"><i class="fas fa-exchange-alt"></i> Request Swap</a></li>
                    <li><a href="#" id="pending-requests-btn"><i class="fas fa-clock"></i> Pending Requests</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
            <div class="user-profile">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <span class="username"><?php echo $current_user['name']; ?></span>
                    <span class="role">Faculty</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <h1>My Schedule</h1>
                <div class="date-range">
                    <button id="prev-week"><i class="fas fa-chevron-left"></i></button>
                    <span id="week-range">Loading...</span>
                    <button id="next-week"><i class="fas fa-chevron-right"></i></button>
                </div>
            </header>

            <div class="content">
                <div class="schedule-table-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="schedule-body">
                            <!-- Schedule data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Swap Request Modal -->
    <div class="modal" id="swap-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Request Shift Swap</h2>
            <form id="swap-form">
                <input type="hidden" id="swap-schedule-id">
                <div class="form-group">
                    <label for="swap-date">Shift Date</label>
                    <input type="text" id="swap-date" readonly>
                </div>
                <div class="form-group">
                    <label for="swap-shift">Shift</label>
                    <input type="text" id="swap-shift" readonly>
                </div>
                <div class="form-group">
                    <label for="swap-faculty">Swap With</label>
                    <select id="swap-faculty" required>
                        <option value="">Select Faculty</option>
                        <!-- Faculty options will be loaded via AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="swap-reason">Reason</label>
                    <textarea id="swap-reason" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>
    </div>

    <!-- Pending Requests Modal -->
    <div class="modal" id="requests-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Pending Swap Requests</h2>
            <div class="requests-list" id="requests-list">
                <!-- Requests will be loaded here via AJAX -->
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>