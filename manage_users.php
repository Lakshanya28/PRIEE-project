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
    <title>Manage Users - Admin Panel</title>
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
            background:  #f5f7fa;
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

        /* Table Styles */
        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .users-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover td {
            background-color: rgba(67, 97, 238, 0.05);
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
        }

        .action-btn i {
            font-size: 12px;
        }

        .edit-user {
            background-color: var(--warning-color);
            color: white;
        }

        .edit-user:hover {
            background-color: var(--warning-hover);
        }

        .delete-user {
            background-color: var(--danger-color);
            color: white;
        }

        .delete-user:hover {
            background-color: var(--danger-hover);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-md);
            width: 100%;
            max-width: 500px;
            padding: 25px;
            box-shadow: var(--shadow-md);
            position: relative;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--danger-color);
        }

        .modal h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        /* Responsive Adjustments */
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
                    <li><a href="manage_schedules.php"><i class="fas fa-calendar-plus"></i> Manage Schedules</a></li>
                    <li><a href="manage_swaps.php"><i class="fas fa-exchange-alt"></i> Manage Swaps</a></li>
                    <li class="active"><a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a></li>
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
                <h1>Manage Faculty Members</h1>
                <button class="btn-primary" id="add-user-btn">
                    <i class="fas fa-plus"></i> Add New Faculty
                </button>
            </header>

            <div class="content">
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
                        <!-- Loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </main>
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
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Add Faculty
                </button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="edit-user-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Faculty Member</h2>
            <form id="edit-user-form">
                <input type="hidden" id="edit-user-id">
                <div class="form-group">
                    <label for="edit-user-name">Full Name</label>
                    <input type="text" id="edit-user-name" required>
                </div>
                <div class="form-group">
                    <label for="edit-user-username">Username</label>
                    <input type="text" id="edit-user-username" required readonly>
                </div>
                <div class="form-group">
                    <label for="edit-user-email">Email</label>
                    <input type="email" id="edit-user-email" required>
                </div>
                <div class="form-group">
                    <label for="edit-user-password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit-user-password">
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Update Faculty
                </button>
            </form>
        </div>
    </div>

    <script src="../assets/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load all users
        function loadUsers() {
            fetch('../backend/get_users.php')
            .then(response => response.json())
            .then(data => {
                const usersList = document.getElementById('users-list');
                if (usersList) {
                    usersList.innerHTML = '';
                    
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            if (user.role === 'faculty') {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${user.name}</td>
                                    <td>${user.username}</td>
                                    <td>${user.email}</td>
                                    <td>
                                        <button class="action-btn edit-user" data-id="${user.id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn delete-user" data-id="${user.id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                `;
                                usersList.appendChild(row);
                            }
                        });

                        // Add event listeners to action buttons
                        document.querySelectorAll('.edit-user').forEach(btn => {
                            btn.addEventListener('click', function() {
                                openEditModal(this.getAttribute('data-id'));
                            });
                        });

                        document.querySelectorAll('.delete-user').forEach(btn => {
                            btn.addEventListener('click', function() {
                                if (confirm('Are you sure you want to delete this user?')) {
                                    deleteUser(this.getAttribute('data-id'));
                                }
                            });
                        });
                    } else {
                        usersList.innerHTML = '<tr><td colspan="4" class="empty-state">No faculty members found</td></tr>';
                    }
                }
            });
        }

        // Open add user modal
        document.getElementById('add-user-btn').addEventListener('click', function() {
            document.getElementById('add-user-modal').style.display = 'flex';
        });

        // Add new user
        document.getElementById('add-user-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('new-user-name').value;
            const username = document.getElementById('new-user-username').value;
            const email = document.getElementById('new-user-email').value;
            const password = document.getElementById('new-user-password').value;

            fetch('../backend/add_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    username: username,
                    email: email,
                    password: password,
                    role: 'faculty'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Faculty member added successfully!');
                    document.getElementById('add-user-form').reset();
                    document.getElementById('add-user-modal').style.display = 'none';
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to add user'));
                }
            });
        });

        // Open edit modal with user data
        function openEditModal(userId) {
            fetch(`../backend/get_user.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.user) {
                    document.getElementById('edit-user-id').value = data.user.id;
                    document.getElementById('edit-user-name').value = data.user.name;
                    document.getElementById('edit-user-username').value = data.user.username;
                    document.getElementById('edit-user-email').value = data.user.email;
                    document.getElementById('edit-user-password').value = '';
                    document.getElementById('edit-user-modal').style.display = 'flex';
                }
            });
        }

        // Update user
        document.getElementById('edit-user-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('edit-user-id').value;
            const name = document.getElementById('edit-user-name').value;
            const email = document.getElementById('edit-user-email').value;
            const password = document.getElementById('edit-user-password').value;

            fetch('../backend/update_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    name: name,
                    email: email,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    document.getElementById('edit-user-modal').style.display = 'none';
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update user'));
                }
            });
        });

        // Delete user
        function deleteUser(userId) {
            fetch('../backend/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete user'));
                }
            });
        }

        // Close modals when clicking X
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });

        // Initial load
        loadUsers();
    });
    </script>
</body>
</html>