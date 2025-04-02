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
    <title>Manage Swaps - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
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
                    <li><a href="manage_schedules.php"><i class="fas fa-calendar-plus"></i> Manage Schedules</a></li>
                    <li class="active"><a href="manage_swaps.php"><i class="fas fa-exchange-alt"></i> Manage Swaps</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
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

        <main class="main-content">
            <header class="header">
                <h1>Manage Swap Requests</h1>
            </header>

            <div class="content">
                <div class="swaps-list" id="swaps-list">
                    <!-- Loaded via AJAX -->
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load all swap requests
        function loadSwaps() {
            fetch('../backend/get_swaps.php')
            .then(response => response.json())
            .then(data => {
                const swapsList = document.getElementById('swaps-list');
                if (swapsList) {
                    swapsList.innerHTML = '';
                    
                    if (data.success && data.swaps.length > 0) {
                        data.swaps.forEach(swap => {
                            const swapItem = document.createElement('div');
                            swapItem.className = 'swap-item';
                            swapItem.innerHTML = `
                                <div class="swap-header">
                                    <span class="swap-date">${new Date(swap.date).toLocaleDateString('en-US', {weekday: 'short', month: 'short', day: 'numeric'})}</span>
                                    <span class="swap-shift">${swap.shift_name}</span>
                                    <span class="swap-status">${swap.status.charAt(0).toUpperCase() + swap.status.slice(1)}</span>
                                </div>
                                <div class="swap-details">
                                    <p><strong>From:</strong> ${swap.requester_name}</p>
                                    <p><strong>To:</strong> ${swap.requested_name}</p>
                                    <p><strong>Reason:</strong> ${swap.reason}</p>
                                </div>
                                ${swap.status === 'pending' ? `
                                <div class="swap-actions">
                                    <button class="btn-primary approve-swap" data-id="${swap.id}">Approve</button>
                                    <button class="btn-danger reject-swap" data-id="${swap.id}">Reject</button>
                                </div>
                                ` : ''}
                            `;
                            swapsList.appendChild(swapItem);
                        });

                        // Add event listeners to action buttons
                        document.querySelectorAll('.approve-swap').forEach(btn => {
                            btn.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'approved');
                            });
                        });

                        document.querySelectorAll('.reject-swap').forEach(btn => {
                            btn.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'rejected');
                            });
                        });
                    } else {
                        swapsList.innerHTML = '<p style="text-align: center;">No swap requests found</p>';
                    }
                }
            });
        }

        // Handle swap approval/rejection
        function handleSwapAction(swapId, action) {
            fetch('../backend/approve_swap.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    swap_id: swapId,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Swap request ${action} successfully!`);
                    loadSwaps();
                } else {
                    alert('Error: ' + (data.message || `Failed to ${action} swap`));
                }
            });
        }

        // Initial load
        loadSwaps();
    });
    </script>
</body>
</html>