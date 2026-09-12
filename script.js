var currentRole = document.body.getAttribute('data-role') || 'guest';

function showFlash(message, type) {
    var flash = document.getElementById('flashMessage');
    if (flash) {
        var className = type === 'error' ? 'error' : 'flash';
        flash.innerHTML = '<div class="' + className + '">' + message + '</div>';
        setTimeout(function() { flash.innerHTML = ''; }, 4000);
    }
}

function loadDashboard() {
    if (currentRole === 'admin') {
        loadStats();
        loadAllUsers();
        loadBookings();
        loadDamages();
        loadReviews();
        loadRequests();
    } else if (currentRole === 'receptionist') {
        loadBookings();
        loadRequests();
        loadMessages();
    } else if (currentRole === 'customer') {
        loadBookings();
        loadReviews();
        loadRequests();
    } else if (currentRole === 'roomservice') {
        loadRequests();
        loadMessages();
    }
}

function loadStats() {
    fetch('../models.php?action=get_stats')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var s = data.stats;
                document.getElementById('statsGrid').innerHTML =
                    '<div class="stat-card"><h4>Total Bookings</h4><p class="stat-value">' + s.total_bookings + '</p></div>' +
                    '<div class="stat-card"><h4>Total Customers</h4><p class="stat-value">' + s.total_customers + '</p></div>' +
                    '<div class="stat-card"><h4>Total Revenue</h4><p class="stat-value">' + s.total_revenue + '</p></div>' +
                    '<div class="stat-card"><h4>Pending Damages</h4><p class="stat-value">' + s.pending_damages + '</p></div>';
            }
        });
}

function loadAllUsers() {
    fetch('../models.php?action=read_all_users')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Created</th><th>Action</th></tr>';
                data.users.forEach(function(u) {
                    html += '<tr><td>' + u.id + '</td><td>' + u.full_name + '</td><td>' + u.email + '</td><td>' + u.username + '</td><td>' + u.role + '</td><td>' + u.created_at + '</td>';
                    if (u.role === 'customer') {
                        html += '<td><button class="action-btn" onclick="deleteUserAccount(\'' + u.email + '\')">Delete</button></td>';
                    } else {
                        html += '<td>-</td>';
                    }
                    html += '</tr>';
                });
                html += '</table>';
                document.getElementById('usersTable').innerHTML = html;
            }
        });
}

function searchCustomers() {
    var keyword = document.getElementById('customerSearch').value;
    fetch('../models.php?action=search_customers&keyword=' + encodeURIComponent(keyword))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Action</th></tr>';
                data.customers.forEach(function(u) {
                    html += '<tr><td>' + u.id + '</td><td>' + u.full_name + '</td><td>' + u.email + '</td><td>' + u.username + '</td>';
                    html += '<td><button class="action-btn" onclick="deleteUserAccount(\'' + u.email + '\')">Delete</button></td></tr>';
                });
                html += '</table>';
                document.getElementById('usersTable').innerHTML = html;
            }
        });
}

function deleteUserAccount(email) {
    if (!confirm('Delete user account for ' + email + '? They will need to register again.')) return;
    var formData = new FormData();
    formData.append('action', 'delete_user_account');
    formData.append('email', email);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) loadAllUsers();
        });
}

function loadBookings() {
    fetch('../models.php?action=read_bookings')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr>';
                if (currentRole === 'admin') {
                    html += '<th class="checkbox-col"><input type="checkbox" id="selectAllBookings" onchange="toggleSelectAll(this)"></th>';
                }
                html += '<th>ID</th><th>Customer</th><th>Room</th><th>Type</th><th>Amount</th><th>Method</th><th>Guests</th><th>Check-In</th><th>Check-Out</th><th>Status</th><th>Action</th></tr>';
                data.bookings.forEach(function(b) {
                    html += '<tr>';
                    if (currentRole === 'admin') {
                        html += '<td class="checkbox-col"><input type="checkbox" class="booking-checkbox" value="' + b.id + '"></td>';
                    }
                    var customerName = b.username || b.user_id;
                    html += '<td>' + b.id + '</td><td>' + customerName + '</td><td>' + b.room_number + '</td><td>' + b.room_type + '</td><td>' + b.amount + '</td><td>' + b.method + '</td><td>' + b.guests + '</td><td>' + b.check_in + '</td><td>' + b.check_out + '</td><td>' + b.status + '</td><td>';
                    if (currentRole === 'customer') {
                        html += '<button class="action-btn" onclick="cancelBooking(' + b.id + ')">Cancel</button> ';
                        html += '<button class="secondary-btn" style="width:auto; padding:5px 10px; font-size:12px;" onclick="openReviewModal(' + b.id + ', \'' + b.room_number + '\')">Review</button>';
                    } else if (currentRole === 'admin') {
                        html += '<button class="action-btn" onclick="deleteBookingHistory(' + b.id + ')">Delete</button>';
                    }
                    html += '</td></tr>';
                });
                html += '</table>';
                var el = document.getElementById('bookingsTable');
                if (el) el.innerHTML = html;
            }
        });
}

function toggleSelectAll(el) {
    var checkboxes = document.querySelectorAll('.booking-checkbox');
    checkboxes.forEach(function(cb) { cb.checked = el.checked; });
}

function deleteSelectedBookings() {
    var checkboxes = document.querySelectorAll('.booking-checkbox:checked');
    if (checkboxes.length === 0) {
        showFlash('Please select bookings to delete.', 'error');
        return;
    }
    if (!confirm('Delete ' + checkboxes.length + ' booking(s)?')) return;
    var formData = new FormData();
    formData.append('action', 'delete_multiple_bookings');
    checkboxes.forEach(function(cb) { formData.append('ids[]', cb.value); });
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) loadBookings();
        });
}

function deleteBookingHistory(id) {
    if (!confirm('Delete this booking history?')) return;
    var formData = new FormData();
    formData.append('action', 'delete_booking_history');
    formData.append('booking_id', id);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) loadBookings();
        });
}

function cancelBooking(id) {
    if (!confirm('Cancel this booking?')) return;
    var formData = new FormData();
    formData.append('action', 'delete_booking');
    formData.append('booking_id', id);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) loadBookings();
        });
}

function searchRooms() {
    var type = document.getElementById('roomType').value;
    var guests = document.getElementById('totalGuests').value;
    fetch('../models.php?action=search_rooms&type=' + type + '&guests=' + guests)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '';
                data.rooms.forEach(function(room) {
                    var statusClass = room.available > 3 ? 'available' : 'limited';
                    var statusText = room.available > 3 ? room.available + ' rooms available' : 'Only ' + room.available + ' left!';
                    html += '<div class="room-card"><div class="room-card-header"><h3>Room ' + room.number + '</h3><p>' + room.type + '</p></div>';
                    html += '<div class="room-card-body"><div class="room-price">' + room.price.toLocaleString() + '<span>/night</span></div>';
                    html += '<p class="room-guests">Sleeps up to ' + room.guests + ' guests</p>';
                    html += '<span class="availability-status ' + statusClass + '">' + statusText + '</span>';
                    html += '<div class="room-amenities">';
                    room.amenities.split(',').forEach(function(am) {
                        html += '<span>' + am.trim() + '</span>';
                    });
                    html += '</div>';
                    html += '<button class="book-btn" onclick="bookRoom(' + room.id + ')">Book Now</button></div></div>';
                });
                document.getElementById('roomsGrid').innerHTML = html;
            }
        });
}

function bookRoom(roomId) {
    var checkin = document.getElementById('checkin').value;
    var checkout = document.getElementById('checkout').value;
    if (!checkin || !checkout) {
        showFlash('Please select check-in and check-out dates.', 'error');
        return;
    }
    var method = prompt('Select payment method (Bkash, Nagad, Visa, PayPal, Bank Transfer):', 'Bkash');
    if (!method) return;
    var guests = document.getElementById('totalGuests').value;
    var children = prompt('Number of children (0 if none):', '0');
    if (children === null) return;

    var formData = new FormData();
    formData.append('action', 'create_booking');
    formData.append('room_id', roomId);
    formData.append('method', method);
    formData.append('guests', guests);
    formData.append('children', children);
    formData.append('check_in', checkin);
    formData.append('check_out', checkout);

    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showFlash(data.message, 'success');
                openReviewModal(data.booking_id, 'Room ' + roomId);
            } else {
                showFlash(data.message, 'error');
            }
        });
}

function openReviewModal(bookingId, roomInfo) {
    document.getElementById('reviewBookingId').value = bookingId;
    document.getElementById('reviewBookingInfo').textContent = 'Booking ID: ' + bookingId + ' - ' + roomInfo;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

function submitReview(e) {
    e.preventDefault();
    
    var rating = parseInt(document.getElementById('reviewRating').value);
    var text = document.getElementById('reviewText').value.trim();
    
    if (isNaN(rating) || rating < 1 || rating > 5) {
        showFlash('Rating must be between 1 and 5.', 'error');
        return;
    }
    
    if (text.length < 5) {
        showFlash('Review must be at least 5 characters long.', 'error');
        return;
    }

    var formData = new FormData();
    formData.append('action', 'create_review');
    formData.append('booking_id', document.getElementById('reviewBookingId').value);
    formData.append('rating', rating);
    formData.append('text', text);
    
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            closeModal();
            if (data.success) {
                document.getElementById('reviewText').value = '';
                document.getElementById('reviewRating').value = '5';
                loadReviews();
            }
        });
}

function loadReviews() {
    fetch('../models.php?action=read_reviews')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>Author</th><th>Rating</th><th>Review</th><th>Date</th><th>Action</th></tr>';
                data.reviews.forEach(function(r) {
                    html += '<tr><td>' + r.id + '</td><td>' + (r.username || 'You') + '</td><td>' + r.rating + '/5</td><td>' + r.text + '</td><td>' + r.created_at + '</td><td>';
                    if (currentRole === 'customer') {
                        html += '<button class="action-btn" onclick="deleteReview(' + r.id + ')">Delete</button>';
                    }
                    html += '</td></tr>';
                });
                html += '</table>';
                var el = document.getElementById('reviewsTable');
                if (el) el.innerHTML = html;
            }
        });
}

function deleteReview(id) {
    if (!confirm('Delete this review?')) return;
    var formData = new FormData();
    formData.append('action', 'delete_review');
    formData.append('review_id', id);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) loadReviews();
        });
}

function loadDamages() {
    fetch('../models.php?action=read_damages')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>Reporter</th><th>Room</th><th>Item</th><th>Description</th><th>Status</th><th>Action</th></tr>';
                data.damages.forEach(function(d) {
                    html += '<tr><td>' + d.id + '</td><td>' + (d.username || 'Unknown') + '</td><td>' + d.room_number + '</td><td>' + d.item + '</td><td>' + d.description + '</td><td>' + d.status + '</td><td>';
                    if (currentRole === 'admin') {
                        html += '<select onchange="updateDamageStatus(' + d.id + ', this.value)"><option value="Pending"' + (d.status==='Pending'?' selected':'') + '>Pending</option><option value="In Progress"' + (d.status==='In Progress'?' selected':'') + '>In Progress</option><option value="Resolved"' + (d.status==='Resolved'?' selected':'') + '>Resolved</option></select>';
                    }
                    html += '</td></tr>';
                });
                html += '</table>';
                document.getElementById('damagesTable').innerHTML = html;
            }
        });
}

function updateDamageStatus(id, status) {
    var formData = new FormData();
    formData.append('action', 'update_damage_status');
    formData.append('damage_id', id);
    formData.append('status', status);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
        });
}

function loadRequests() {
    fetch('../models.php?action=read_requests')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>User</th><th>Type</th><th>Details</th><th>Status</th><th>Date</th><th>Action</th></tr>';
                data.requests.forEach(function(r) {
                    html += '<tr><td>' + r.id + '</td><td>' + (r.username || 'You') + '</td><td>' + r.type + '</td><td>' + r.text + '</td><td>' + r.status + '</td><td>' + r.created_at + '</td><td>';
                    if (currentRole === 'admin' || currentRole === 'receptionist') {
                        html += '<select onchange="updateRequestStatus(' + r.id + ', this.value)"><option value="Pending"' + (r.status==='Pending'?' selected':'') + '>Pending</option><option value="In Progress"' + (r.status==='In Progress'?' selected':'') + '>In Progress</option><option value="Completed"' + (r.status==='Completed'?' selected':'') + '>Completed</option></select>';
                    }
                    html += '</td></tr>';
                });
                html += '</table>';
                var el = document.getElementById('requestsTable');
                if (el) el.innerHTML = html;
            }
        });
}

function updateRequestStatus(id, status) {
    var formData = new FormData();
    formData.append('action', 'update_request_status');
    formData.append('request_id', id);
    formData.append('status', status);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
        });
}

function submitRequest(e, type) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData();
    formData.append('action', 'create_request');
    formData.append('type', type);
    formData.append('text', form.text.value);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                form.reset();
                loadRequests();
            }
        });
}

function generateInvoice(e) {
    e.preventDefault();
    var room = document.getElementById('invRoom').value;
    var amount = document.getElementById('invAmount').value;
    var formData = new FormData();
    formData.append('action', 'create_request');
    formData.append('type', 'invoice');
    formData.append('text', 'Invoice for Room ' + room + ' - Amount: ' + amount);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById('invRoom').value = '';
                document.getElementById('invAmount').value = '';
            }
        });
}

function loadMessages() {
    fetch('../models.php?action=read_messages')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var html = '<table><tr><th>ID</th><th>From</th><th>Message</th><th>Date</th></tr>';
                data.messages.forEach(function(m) {
                    html += '<tr><td>' + m.id + '</td><td>' + m.sender_name + '</td><td>' + m.text + '</td><td>' + m.created_at + '</td></tr>';
                });
                html += '</table>';
                var el = document.getElementById('messagesTable');
                if (el) el.innerHTML = html;
            }
        });
}

function sendMessage(e, receiverId) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData();
    formData.append('action', 'create_message');
    formData.append('receiver_id', receiverId);
    formData.append('text', form.text.value);
    fetch('../models.php', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            showFlash(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                form.reset();
                loadMessages();
            }
        });
}

function showTab(tabId) {
    var tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(function(t) { t.style.display = 'none'; });
    document.getElementById(tabId).style.display = 'block';
    var btns = document.querySelectorAll('.tab-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();

    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            var username = document.getElementById('loginUsername').value.trim();
            var password = document.getElementById('loginPassword').value;
            var valid = true;
            if (username === '') {
                document.getElementById('usernameError').textContent = 'Username is required.';
                valid = false;
            } else {
                document.getElementById('usernameError').textContent = '';
            }
            if (password === '') {
                document.getElementById('passwordError').textContent = 'Password is required.';
                valid = false;
            } else {
                document.getElementById('passwordError').textContent = '';
            }
            if (!valid) e.preventDefault();
        });
    }

    var signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            var pass = document.getElementById('signupPassword').value;
            var confirm = document.getElementById('signupConfirm').value;
            if (pass !== confirm) {
                document.getElementById('confirmError').textContent = 'Passwords do not match.';
                e.preventDefault();
            } else {
                document.getElementById('confirmError').textContent = '';
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
    
    
});