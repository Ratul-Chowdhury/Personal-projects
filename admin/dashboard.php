<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stmt = mysqli_prepare($conn, "SELECT full_name, email FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Grand Hotel</title>
    <link rel="stylesheet" href="../style.css">
    
</head>
<body data-role="<?= $role ?>">
    <div class="support-bar">For Bookings, Support, or Password Recovery, Call: +1-800-555-0199</div>
    <div class="header">
        <h1>GRAND HOTEL</h1>
    </div>

    <div class="container">
        <div class="card">
            <div class="nav">
                <span>Welcome, <?= e($user['full_name']) ?> (<?= strtoupper($role) ?>)</span>
                <form method="post" action="../logout.php" class="m-0">
                    <button type="submit" class="logout-btn">Log out</button>
                </form>
            </div>

            <div id="flashMessage"></div>

            <?php if ($role === 'admin'): ?>
                <h2>Admin Dashboard</h2>
                <div class="dashboard-tabs">
                    <button class="tab-btn active" onclick="showTab('admin_bookings')">Bookings & Stats</button>
                    <button class="tab-btn" onclick="showTab('admin_services')">Services Log</button>
                    <button class="tab-btn" onclick="showTab('admin_damages')">Product Damage</button>
                    <button class="tab-btn" onclick="showTab('admin_reviews')">Reviews</button>
                    <button class="tab-btn" onclick="showTab('admin_users')">Users (CRUD)</button>
                </div>

                <div id="admin_bookings" class="tab-content active">
                    <h3>System Statistics</h3>
                    <div id="statsGrid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:15px; margin-bottom:20px;"></div>
                    <h3>All Bookings & Payment System Check</h3>
                    <button class="action-btn" style="margin-bottom:10px;" onclick="deleteSelectedBookings()">Delete Selected</button>
                    <div id="bookingsTable"></div>
                </div>

                <div id="admin_services" class="tab-content">
                    <h3>Services Provided by Staff (Reception & Room Service)</h3>
                    <div id="requestsTable"></div>
                </div>

                <div id="admin_damages" class="tab-content">
                    <h3>Product Damage Reports</h3>
                    <div id="damagesTable"></div>
                </div>

                <div id="admin_reviews" class="tab-content">
                    <h3>Customer Reviews</h3>
                    <div id="reviewsTable"></div>
                </div>

                <div id="admin_users" class="tab-content">
                    <h3>Manage Users</h3>
                    <input type="text" id="customerSearch" placeholder="Search by name, email, or username..." onkeyup="searchCustomers()" style="margin-bottom:15px; width:100%; max-width:400px;">
                    <div id="usersTable"></div>
                </div>

            <?php elseif ($role === 'customer'): ?>
                <h2>Customer Dashboard</h2>
                <div class="dashboard-tabs">
                    <button class="tab-btn active" onclick="showTab('cust_book')">My Bookings</button>
                    <button class="tab-btn" onclick="showTab('cust_req')">Special Requests</button>
                    <button class="tab-btn" onclick="showTab('cust_rev')">My Reviews</button>
                </div>

                <div id="cust_book" class="tab-content active">
                    <h3>Check Room Availability</h3>
                    <a href="../rooms.php" class="secondary-btn d-inline-block" style="width:auto; padding:10px 20px; margin-bottom:20px;">Search & Book Rooms</a>
                    <h3>My Bookings</h3>
                    <div id="bookingsTable"></div>
                </div>

                <div id="cust_req" class="tab-content">
                    <h3>Submit Special Request</h3>
                    <form onsubmit="submitRequest(event, 'special')" class="service-card" style="max-width:600px;">
                        <label>Your Request</label>
                        <textarea name="text" rows="3" required></textarea>
                        <button type="submit">Send Request</button>
                    </form>
                    <h3 style="margin-top:30px;">My Past Requests</h3>
                    <div id="requestsTable"></div>
                </div>

                <div id="cust_rev" class="tab-content">
                    <h3>My Reviews</h3>
                    <div id="reviewsTable"></div>
                </div>

            <?php elseif ($role === 'receptionist'): ?>
                <h2>Receptionist Dashboard</h2>
                <div class="dashboard-tabs">
                    <button class="tab-btn active" onclick="showTab('rec_services')">Services</button>
                    <button class="tab-btn" onclick="showTab('rec_messages')">Messages</button>
                    <button class="tab-btn" onclick="showTab('rec_requests')">Requests</button>
                </div>

                <div id="rec_services" class="tab-content active">
                    <div class="service-grid">
                        <div class="service-card">
                            <h4>Wheelchair Service</h4>
                            <form onsubmit="submitRequest(event, 'wheelchair')">
                                <label>Room Number</label>
                                <input type="text" name="text" placeholder="Room Number" required>
                                <button type="submit">Assign Wheelchair</button>
                            </form>
                        </div>
                        <div class="service-card">
                            <h4>Airport Pickup</h4>
                            <form onsubmit="submitRequest(event, 'transport')">
                                <label>Room Number & Time</label>
                                <input type="text" name="text" placeholder="Room 101 at 3:00 PM" required>
                                <button type="submit">Schedule Pickup</button>
                            </form>
                        </div>
                        <div class="service-card">
                            <h4>Generate Invoice</h4>
                            <form onsubmit="generateInvoice(event)">
                                <label>Room Number</label>
                                <input type="text" id="invRoom" required>
                                <label>Total Amount</label>
                                <input type="number" id="invAmount" step="0.01" required>
                                <button type="submit">Generate Invoice</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="rec_messages" class="tab-content">
                    <h3>Messages from Room Service</h3>
                    <div id="messagesTable"></div>
                </div>

                <div id="rec_requests" class="tab-content">
                    <h3>All Customer Requests</h3>
                    <div id="requestsTable"></div>
                </div>

            <?php elseif ($role === 'roomservice'): ?>
                <h2>Room Service Dashboard</h2>
                <div class="dashboard-tabs">
                    <button class="tab-btn active" onclick="showTab('rs_services')">Services</button>
                    <button class="tab-btn" onclick="showTab('rs_message')">Message Receptionist</button>
                    <button class="tab-btn" onclick="showTab('rs_requests')">Requests</button>
                </div>

                <div id="rs_services" class="tab-content active">
                    <div class="service-grid">
                        <div class="service-card">
                            <h4>Food Delivery</h4>
                            <form onsubmit="submitRequest(event, 'food')">
                                <label>Room & Food Item</label>
                                <input type="text" name="text" placeholder="Room 101: Burger and Fries" required>
                                <button type="submit">Log Order</button>
                            </form>
                        </div>
                        <div class="service-card">
                            <h4>Laundry Service</h4>
                            <form onsubmit="submitRequest(event, 'laundry')">
                                <label>Room & Items</label>
                                <input type="text" name="text" placeholder="Room 101: 5 items" required>
                                <button type="submit">Log Laundry</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="rs_message" class="tab-content">
                    <h3>Send Message to Receptionist</h3>
                    <form onsubmit="sendMessage(event, 2)" class="service-card" style="max-width:600px;">
                        <label>Your Message</label>
                        <textarea name="text" rows="4" required></textarea>
                        <button type="submit">Send Message</button>
                    </form>
                </div>

                <div id="rs_requests" class="tab-content">
                    <h3>All Service Requests</h3>
                    <div id="requestsTable"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
        <div class="card" style="max-width:500px; width:90%;">
            <h3>Write a Review</h3>
            <p id="reviewBookingInfo" style="color:#555; margin-bottom:15px;"></p>
            <form onsubmit="submitReview(event)">
                <input type="hidden" id="reviewBookingId">
                <label>Rating (1-5)</label>
                <input type="number" id="reviewRating" min="1" max="5" value="5" required>
                <label>Your Review</label>
                <textarea id="reviewText" rows="3" required></textarea>
                <button type="submit" style="margin-top:10px;">Submit Review</button>
                <button type="button" class="secondary-btn" onclick="closeModal()" style="margin-top:10px; width:auto; padding:10px 20px;">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../script.js"></script>
</body>
</html>