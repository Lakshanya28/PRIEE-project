document.addEventListener('DOMContentLoaded', function() {
    // Common functions for both admin and faculty
    const isAdmin = document.body.classList.contains('admin');
    
    // Modal handling
    const modals = document.querySelectorAll('.modal');
    const closeModalButtons = document.querySelectorAll('.close-modal');
    
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
    
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    
    // Date handling
    function getWeekRange(date) {
        const day = date.getDay();
        const diff = date.getDate() - day + (day === 0 ? -6 : 1); // Adjust for Sunday
        const monday = new Date(date.setDate(diff));
        const sunday = new Date(date.setDate(diff + 6));
        
        const formatOptions = { month: 'short', day: 'numeric' };
        return `${monday.toLocaleDateString('en-US', formatOptions)} - ${sunday.toLocaleDateString('en-US', formatOptions)}`;
    }
    
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    function formatDisplayDate(dateString) {
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    }
    
    function formatStatus(status) {
        return status.split('_').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    let currentWeekOffset = 0;
    const today = new Date();
    
    // Faculty dashboard functionality
    if (!isAdmin) {
        // Faculty-specific code
        const weekRangeElement = document.getElementById('week-range');
        const scheduleBody = document.getElementById('schedule-body');
        const prevWeekBtn = document.getElementById('prev-week');
        const nextWeekBtn = document.getElementById('next-week');
        
        function updateWeekDisplay() {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + (currentWeekOffset * 7) - today.getDay() + 1);
            if (weekRangeElement) {
                weekRangeElement.textContent = getWeekRange(new Date(weekStart));
            }
            loadScheduleData();
        }
        
        function loadScheduleData() {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + (currentWeekOffset * 7) - today.getDay() + 1);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            
            fetch('backend/get_schedules.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: formatDate(weekStart),
                    end_date: formatDate(weekEnd),
                    user_id: document.body.getAttribute('data-user-id') || ''
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (scheduleBody) {
                    scheduleBody.innerHTML = '';
                    if (data.success && data.schedules && data.schedules.length > 0) {
                        data.schedules.forEach(schedule => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${formatDisplayDate(schedule.date)}</td>
                                <td>${schedule.shift_name || ''}</td>
                                <td>${schedule.start_time || ''} - ${schedule.end_time || ''}</td>
                                <td><span class="status-badge status-${schedule.status || 'scheduled'}">${formatStatus(schedule.status || 'scheduled')}</span></td>
                                <td>
                                    ${schedule.status === 'scheduled' ? `<button class="action-btn request-swap" data-id="${schedule.id}" data-date="${schedule.date}" data-shift="${schedule.shift_name || ''}">
                                        <i class="fas fa-exchange-alt"></i> Request Swap
                                    </button>` : ''}
                                </td>
                            `;
                            scheduleBody.appendChild(row);
                        });
                        
                        // Add event listeners to swap buttons
                        document.querySelectorAll('.request-swap').forEach(button => {
                            button.addEventListener('click', function() {
                                const swapModal = document.getElementById('swap-modal');
                                if (swapModal) {
                                    document.getElementById('swap-schedule-id').value = this.getAttribute('data-id') || '';
                                    document.getElementById('swap-date').value = this.getAttribute('data-date') || '';
                                    document.getElementById('swap-shift').value = this.getAttribute('data-shift') || '';
                                    loadFacultyForSwap();
                                    openModal('swap-modal');
                                }
                            });
                        });
                    } else {
                        scheduleBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No shifts scheduled for this week</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (scheduleBody) {
                    scheduleBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Error loading schedule data</td></tr>';
                }
            });
        }
        
        function loadFacultyForSwap() {
            fetch('backend/get_users.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const select = document.getElementById('swap-faculty');
                if (select) {
                    select.innerHTML = '<option value="">Select Faculty</option>';
                    if (data.success && data.users && data.users.length > 0) {
                        data.users.forEach(user => {
                            if (user.id != document.body.getAttribute('data-user-id')) {
                                const option = document.createElement('option');
                                option.value = user.id;
                                option.textContent = user.name;
                                select.appendChild(option);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Swap request form submission
        const swapForm = document.getElementById('swap-form');
        if (swapForm) {
            swapForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const scheduleId = document.getElementById('swap-schedule-id').value;
                const facultyId = document.getElementById('swap-faculty').value;
                const reason = document.getElementById('swap-reason').value;
                
                if (!scheduleId || !facultyId || !reason) {
                    alert('Please fill all fields');
                    return;
                }
                
                fetch('backend/request_swap.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        schedule_id: scheduleId,
                        requested_id: facultyId,
                        reason: reason
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Swap request submitted successfully!');
                        closeModal('swap-modal');
                        loadScheduleData();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the request');
                });
            });
        }
        
        // Pending requests button
        const pendingRequestsBtn = document.getElementById('pending-requests-btn');
        if (pendingRequestsBtn) {
            pendingRequestsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loadPendingRequests();
                openModal('requests-modal');
            });
        }
        
        function loadPendingRequests() {
            fetch('backend/get_swaps.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const requestsList = document.getElementById('requests-list');
                if (requestsList) {
                    requestsList.innerHTML = '';
                    
                    if (data.success && data.swaps && data.swaps.length > 0) {
                        data.swaps.forEach(swap => {
                            const requestItem = document.createElement('div');
                            requestItem.className = 'request-item';
                            requestItem.innerHTML = `
                                <div class="request-header">
                                    <span class="request-date">${formatDisplayDate(swap.date)}</span>
                                    <span class="request-shift">${swap.shift_name || ''}</span>
                                    <span class="request-status">Pending</span>
                                </div>
                                <div class="request-details">
                                    <p><strong>Requested to:</strong> ${swap.requested_name || ''}</p>
                                    <p><strong>Reason:</strong> ${swap.reason || ''}</p>
                                </div>
                            `;
                            requestsList.appendChild(requestItem);
                        });
                    } else {
                        requestsList.innerHTML = '<p style="text-align: center;">No pending swap requests</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Initialize
        updateWeekDisplay();
        
        // Week navigation
        if (prevWeekBtn) {
            prevWeekBtn.addEventListener('click', function() {
                currentWeekOffset--;
                updateWeekDisplay();
            });
        }
        
        if (nextWeekBtn) {
            nextWeekBtn.addEventListener('click', function() {
                currentWeekOffset++;
                updateWeekDisplay();
            });
        }
        
        // Swap request button
        const requestSwapBtn = document.getElementById('request-swap-btn');
        if (requestSwapBtn) {
            requestSwapBtn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Please select a shift to request a swap');
            });
        }
    } else {
        // Admin-specific code
        const adminWeekRangeElement = document.getElementById('admin-week-range');
        const adminScheduleBody = document.getElementById('admin-schedule-body');
        const adminPrevWeekBtn = document.getElementById('admin-prev-week');
        const adminNextWeekBtn = document.getElementById('admin-next-week');
        
        function updateAdminWeekDisplay() {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + (currentWeekOffset * 7) - today.getDay() + 1);
            if (adminWeekRangeElement) {
                adminWeekRangeElement.textContent = getWeekRange(new Date(weekStart));
            }
            loadAdminScheduleData();
        }
        
        function loadAdminScheduleData() {
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() + (currentWeekOffset * 7) - today.getDay() + 1);
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            
            fetch('backend/get_schedules.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    start_date: formatDate(weekStart),
                    end_date: formatDate(weekEnd)
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (adminScheduleBody) {
                    adminScheduleBody.innerHTML = '';
                    if (data.success && data.schedules && data.schedules.length > 0) {
                        data.schedules.forEach(schedule => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${formatDisplayDate(schedule.date)}</td>
                                <td>${schedule.user_name || ''}</td>
                                <td>${schedule.shift_name || ''}</td>
                                <td>${schedule.start_time || ''} - ${schedule.end_time || ''}</td>
                                <td><span class="status-badge status-${schedule.status || 'scheduled'}">${formatStatus(schedule.status || 'scheduled')}</span></td>
                                <td>
                                    ${schedule.status === 'swap_requested' ? `
                                    <button class="action-btn approve-swap" data-id="${schedule.swap_id || ''}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="action-btn reject-swap" data-id="${schedule.swap_id || ''}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    ` : ''}
                                    <button class="action-btn delete-schedule" data-id="${schedule.id || ''}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            `;
                            adminScheduleBody.appendChild(row);
                        });
                        
                        // Add event listeners to action buttons
                        document.querySelectorAll('.approve-swap').forEach(button => {
                            button.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'approved');
                            });
                        });
                        
                        document.querySelectorAll('.reject-swap').forEach(button => {
                            button.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'rejected');
                            });
                        });
                        
                        document.querySelectorAll('.delete-schedule').forEach(button => {
                            button.addEventListener('click', function() {
                                if (confirm('Are you sure you want to delete this schedule?')) {
                                    deleteSchedule(this.getAttribute('data-id'));
                                }
                            });
                        });
                    } else {
                        adminScheduleBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No shifts scheduled for this week</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (adminScheduleBody) {
                    adminScheduleBody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Error loading schedule data</td></tr>';
                }
            });
        }
        
        function handleSwapAction(swapId, action) {
            if (!swapId) return;
            
            fetch('backend/approve_swap.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    swap_id: swapId,
                    action: action
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(`Swap request ${action} successfully!`);
                    loadAdminScheduleData();
                    loadAdminSwaps();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the request');
            });
        }
        
        function deleteSchedule(scheduleId) {
            if (!scheduleId) return;
            
            // You would need to implement this endpoint
            console.log('Delete schedule', scheduleId);
            alert('Delete functionality would be implemented here');
        }
        
        // Admin dashboard sections
        const sectionButtons = {
            'manage-schedules-btn': 'manage-schedules-section',
            'manage-swaps-btn': 'manage-swaps-section',
            'manage-users-btn': 'manage-users-section'
        };
        
        Object.entries(sectionButtons).forEach(([buttonId, sectionId]) => {
            const button = document.getElementById(buttonId);
            if (button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Hide all sections
                    document.querySelectorAll('.section').forEach(section => {
                        section.classList.add('hidden');
                    });
                    // Show selected section
                    const section = document.getElementById(sectionId);
                    if (section) {
                        section.classList.remove('hidden');
                    }
                });
            }
        });
        
        // By default, show upcoming shifts
        document.querySelectorAll('.section').forEach(section => {
            if (section.id !== 'upcoming-shifts-section') {
                section.classList.add('hidden');
            }
        });
        
        // Load faculty and shifts for schedule form
        function loadFacultyAndShifts() {
            Promise.all([
                fetch('backend/get_users.php').then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                }),
                fetch('backend/get_shifts.php').then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
            ])
            .then(([usersData, shiftsData]) => {
                const facultySelect = document.getElementById('schedule-faculty');
                if (facultySelect) {
                    facultySelect.innerHTML = '<option value="">Select Faculty</option>';
                    if (usersData.success && usersData.users && usersData.users.length > 0) {
                        usersData.users.forEach(user => {
                            if (user.role === 'faculty') {
                                const option = document.createElement('option');
                                option.value = user.id;
                                option.textContent = user.name;
                                facultySelect.appendChild(option);
                            }
                        });
                    }
                }
                
                const shiftSelect = document.getElementById('schedule-shift');
                if (shiftSelect) {
                    shiftSelect.innerHTML = '<option value="">Select Shift</option>';
                    if (shiftsData.success && shiftsData.shifts && shiftsData.shifts.length > 0) {
                        shiftsData.shifts.forEach(shift => {
                            const option = document.createElement('option');
                            option.value = shift.id;
                            option.textContent = `${shift.name} (${shift.start_time} - ${shift.end_time})`;
                            shiftSelect.appendChild(option);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Add schedule form
        const addScheduleForm = document.getElementById('add-schedule-form');
        if (addScheduleForm) {
            addScheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const facultyId = document.getElementById('schedule-faculty').value;
                const shiftId = document.getElementById('schedule-shift').value;
                const date = document.getElementById('schedule-date').value;
                
                if (!facultyId || !shiftId || !date) {
                    alert('Please fill all fields');
                    return;
                }
                
                fetch('backend/add_schedule.php', {
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Schedule added successfully!');
                        this.reset();
                        loadAdminScheduleData();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the schedule');
                });
            });
        }
        
        // Load admin swaps
        function loadAdminSwaps() {
            fetch('backend/get_swaps.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const swapsList = document.getElementById('admin-swaps-list');
                if (swapsList) {
                    swapsList.innerHTML = '';
                    
                    if (data.success && data.swaps && data.swaps.length > 0) {
                        data.swaps.forEach(swap => {
                            const swapItem = document.createElement('div');
                            swapItem.className = 'swap-item';
                            swapItem.innerHTML = `
                                <div class="swap-header">
                                    <span class="swap-date">${formatDisplayDate(swap.date)}</span>
                                    <span class="swap-shift">${swap.shift_name || ''}</span>
                                    <span class="swap-status">${(swap.status || '').charAt(0).toUpperCase() + (swap.status || '').slice(1)}</span>
                                </div>
                                <div class="swap-details">
                                    <p><strong>From:</strong> ${swap.requester_name || ''}</p>
                                    <p><strong>To:</strong> ${swap.requested_name || ''}</p>
                                    <p><strong>Reason:</strong> ${swap.reason || ''}</p>
                                </div>
                                ${swap.status === 'pending' ? `
                                <div class="swap-actions">
                                    <button class="btn-primary approve-swap" data-id="${swap.id || ''}">Approve</button>
                                    <button class="btn-danger reject-swap" data-id="${swap.id || ''}">Reject</button>
                                </div>
                                ` : ''}
                            `;
                            swapsList.appendChild(swapItem);
                        });
                        
                        // Add event listeners to action buttons
                        document.querySelectorAll('.approve-swap').forEach(button => {
                            button.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'approved');
                            });
                        });
                        
                        document.querySelectorAll('.reject-swap').forEach(button => {
                            button.addEventListener('click', function() {
                                handleSwapAction(this.getAttribute('data-id'), 'rejected');
                            });
                        });
                    } else {
                        swapsList.innerHTML = '<p style="text-align: center;">No swap requests found</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Load users list
        function loadUsersList() {
            fetch('backend/get_users.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const usersList = document.getElementById('users-list');
                if (usersList) {
                    usersList.innerHTML = '';
                    
                    if (data.success && data.users && data.users.length > 0) {
                        data.users.forEach(user => {
                            if (user.role === 'faculty') {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${user.name || ''}</td>
                                    <td>${user.username || ''}</td>
                                    <td>${user.email || ''}</td>
                                    <td>
                                        <button class="action-btn edit-user" data-id="${user.id || ''}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn delete-user" data-id="${user.id || ''}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                `;
                                usersList.appendChild(row);
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Add user modal
        const addUserBtn = document.getElementById('add-user-btn');
        if (addUserBtn) {
            addUserBtn.addEventListener('click', function() {
                openModal('add-user-modal');
            });
        }
        
        // Add user form
        const addUserForm = document.getElementById('add-user-form');
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('new-user-name').value;
                const username = document.getElementById('new-user-username').value;
                const email = document.getElementById('new-user-email').value;
                const password = document.getElementById('new-user-password').value;
                
                if (!name || !username || !email || !password) {
                    alert('Please fill all fields');
                    return;
                }
                
                fetch('backend/add_user.php', {
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Faculty member added successfully!');
                        this.reset();
                        closeModal('add-user-modal');
                        loadUsersList();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the user');
                });
            });
        }
        
        // Initialize admin dashboard
        updateAdminWeekDisplay();
        loadFacultyAndShifts();
        loadAdminSwaps();
        loadUsersList();
        
        // Week navigation
        if (adminPrevWeekBtn) {
            adminPrevWeekBtn.addEventListener('click', function() {
                currentWeekOffset--;
                updateAdminWeekDisplay();
            });
        }
        
        if (adminNextWeekBtn) {
            adminNextWeekBtn.addEventListener('click', function() {
                currentWeekOffset++;
                updateAdminWeekDisplay();
            });
        }
    }
    // Add these functions to your existing admin code section

// Function to load admin dashboard statistics
function loadAdminStats() {
    Promise.all([
        fetch('backend/get_stats.php').then(res => res.json()),
        fetch('backend/get_swaps.php').then(res => res.json())
    ])
    .then(([statsData, swapsData]) => {
        if (statsData.success) {
            document.getElementById('total-faculty').textContent = statsData.total_faculty || 0;
            document.getElementById('total-shifts').textContent = statsData.total_shifts || 0;
        }
        if (swapsData.success) {
            const pendingSwaps = swapsData.swaps.filter(swap => swap.status === 'pending').length;
            document.getElementById('pending-swaps').textContent = pendingSwaps;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

// Function to delete a user
function deleteUser(userId) {
    if (!userId || !confirm('Are you sure you want to delete this user?')) return;

    fetch('backend/delete_user.php', {
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
            loadUsersList();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete user'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the user');
    });
}

// Function to edit a user (open edit modal)
function openEditUserModal(userId) {
    fetch('backend/get_user.php?id=' + userId)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.user) {
            const user = data.user;
            const modal = document.getElementById('edit-user-modal');
            if (!modal) {
                // Create edit modal if it doesn't exist
                const editModal = document.createElement('div');
                editModal.className = 'modal';
                editModal.id = 'edit-user-modal';
                editModal.innerHTML = `
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h2>Edit Faculty Member</h2>
                        <form id="edit-user-form">
                            <input type="hidden" id="edit-user-id" value="${user.id}">
                            <div class="form-group">
                                <label for="edit-user-name">Full Name</label>
                                <input type="text" id="edit-user-name" value="${user.name}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-user-email">Email</label>
                                <input type="email" id="edit-user-email" value="${user.email}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-user-password">New Password (leave blank to keep current)</label>
                                <input type="password" id="edit-user-password">
                            </div>
                            <button type="submit" class="btn-primary">Update Faculty</button>
                        </form>
                    </div>
                `;
                document.body.appendChild(editModal);
                
                // Add form submit handler
                document.getElementById('edit-user-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    updateUser();
                });
            }
            
            // Populate fields
            document.getElementById('edit-user-id').value = user.id;
            document.getElementById('edit-user-name').value = user.name;
            document.getElementById('edit-user-email').value = user.email;
            document.getElementById('edit-user-password').value = '';
            
            openModal('edit-user-modal');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Function to update user details
function updateUser() {
    const userId = document.getElementById('edit-user-id').value;
    const name = document.getElementById('edit-user-name').value;
    const email = document.getElementById('edit-user-email').value;
    const password = document.getElementById('edit-user-password').value;

    fetch('backend/update_user.php', {
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
            closeModal('edit-user-modal');
            loadUsersList();
        } else {
            alert('Error: ' + (data.message || 'Failed to update user'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the user');
    });
}

// Function to delete a schedule
function deleteSchedule(scheduleId) {
    if (!scheduleId || !confirm('Are you sure you want to delete this schedule?')) return;

    fetch('backend/delete_schedule.php', {
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
            loadAdminScheduleData();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete schedule'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the schedule');
    });
}

// Add event listeners for edit/delete user buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-user')) {
        const userId = e.target.closest('.edit-user').getAttribute('data-id');
        openEditUserModal(userId);
    }
    else if (e.target.closest('.delete-user')) {
        const userId = e.target.closest('.delete-user').getAttribute('data-id');
        deleteUser(userId);
    }
});

// Initialize admin dashboard with stats
loadAdminStats();
});
