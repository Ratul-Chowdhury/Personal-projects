<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: rooms.php');
    exit;
}

$room_id = (int)($_POST['room_id'] ?? 0);
$room_number = $_POST['room_number'] ?? '';
$room_type = $_POST['room_type'] ?? '';
$price = (int)($_POST['price'] ?? 0);
$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
$children = (int)($_POST['children'] ?? 0);
$adults = $guests - $children;

$stmt = mysqli_prepare($conn, "SELECT amenities, description FROM rooms WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$room_details = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$room_images = [
    '101' => 'room-101.jpg',
    '102' => 'room-102.jpg',
    '201' => 'room-201.jpg',
    '202' => 'room-202.jpg',
    '301' => 'room-301.jpg'
];

$room_image = $room_images[$room_number] ?? '';

$booking_confirmed = false;

if (isset($_POST['confirm_booking'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method && $check_in && $check_out) {
        $stmt = mysqli_prepare($conn, "INSERT INTO bookings (user_id, room_id, room_number, room_type, amount, method, guests, adults, children, check_in, check_out) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissisiisss", $_SESSION['user_id'], $room_id, $room_number, $room_type, $price, $payment_method, $guests, $adults, $children, $check_in, $check_out);
        
        if (mysqli_stmt_execute($stmt)) {
            $booking_id = mysqli_insert_id($conn);
            $booking_confirmed = true;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Booking - Grand Hotel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="support-bar">For Bookings, Support, or Password Recovery, Call: +1-800-555-0199</div>
    
    <div class="header">
        <h1>GRAND HOTEL</h1>
    </div>

    <div class="container">
        <div class="card">
            <div class="nav">
                <span>Welcome, <?= e($_SESSION['username'] ?? 'User') ?></span>
                <div style="display:flex; gap:10px;">
                    <a href="admin/dashboard.php" class="secondary-btn" style="width:auto; padding:10px 20px;">My Dashboard</a>
                    <form method="post" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn" style="width:auto; padding:10px 20px;">Log out</button>
                    </form>
                </div>
            </div>

            <?php if ($booking_confirmed): ?>
                <h2 style="color:#27ae60; text-align:center;">✓ Booking Confirmed!</h2>
                <p style="text-align:center; font-size:16px;">Your room has been successfully booked.</p>
                
                <div style="background:#e8f5e9; padding:25px; border-radius:8px; margin:25px 0;">
                    <h3>Booking Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Room</strong>
                            <span><?= e($room_number) ?> (<?= e($room_type) ?>)</span>
                        </div>
                        <div class="info-item">
                            <strong>Check-in</strong>
                            <span><?= e($check_in) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Check-out</strong>
                            <span><?= e($check_out) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Total Guests</strong>
                            <span><?= e($guests) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Adults</strong>
                            <span><?= e($adults) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Children</strong>
                            <span><?= e($children) ?></span>
                        </div>
                    </div>
                    <p><strong>Payment Method:</strong> <?= e($payment_method) ?></p>
                    <p><strong>Total Amount:</strong> ৳<?= number_format($price) ?></p>
                </div>

                <div style="text-align:center; margin-top:25px;">
                    <a href="admin/dashboard.php" class="secondary-btn" style="width:auto; padding:12px 30px; display:inline-block;">Go to My Dashboard</a>
                </div>

            <?php else: ?>
                <h2>Complete Your Booking</h2>
                
                <?php if (!empty($room_image)): ?>
                    <img src="<?= e($room_image) ?>" alt="Room <?= e($room_number) ?>" class="booking-image">
                <?php endif; ?>
                
                <div class="room-info-section">
                    <h3>Room Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Room Number</strong>
                            <span><?= e($room_number) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Room Type</strong>
                            <span><?= e($room_type) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Price Per Night</strong>
                            <span>৳<?= number_format($price) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Check-in</strong>
                            <span><?= e($check_in) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Check-out</strong>
                            <span><?= e($check_out) ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Total Guests</strong>
                            <span><?= e($guests) ?></span>
                        </div>
                    </div>
                    
                    <div class="guest-breakdown">
                        <strong>Guest Breakdown:</strong><br>
                        Adults: <?= e($adults) ?> | Children: <?= e($children) ?>
                    </div>
                    
                    <?php if (!empty($room_details['amenities'])): ?>
                        <h3 style="margin-top:25px;">Room Amenities</h3>
                        <div class="amenities-list">
                            <?php 
                            $amenities = explode(',', $room_details['amenities']);
                            foreach ($amenities as $amenity): 
                            ?>
                                <span><?= e(trim($amenity)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($room_details['description'])): ?>
                        <h3 style="margin-top:25px;">Description</h3>
                        <p style="line-height:1.6; color:#555;"><?= nl2br(e($room_details['description'])) ?></p>
                    <?php endif; ?>
                </div>

                <div class="payment-section">
                    <h3>Payment Information</h3>
                    <form method="post" action="booking.php">
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <input type="hidden" name="room_number" value="<?= e($room_number) ?>">
                        <input type="hidden" name="room_type" value="<?= e($room_type) ?>">
                        <input type="hidden" name="price" value="<?= $price ?>">
                        <input type="hidden" name="check_in" value="<?= e($check_in) ?>">
                        <input type="hidden" name="check_out" value="<?= e($check_out) ?>">
                        <input type="hidden" name="guests" value="<?= e($guests) ?>">
                        <input type="hidden" name="children" value="<?= e($children) ?>">
                        
                        <label>Payment Method:</label>
                        <select name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="Bkash">Bkash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Visa/Mastercard">Visa/Mastercard</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>

                        <button type="submit" name="confirm_booking" onclick="return confirm('Confirm this booking?')">Confirm Booking - ৳<?= number_format($price) ?></button>
                    </form>
                </div>
                
                <div style="text-align:center; margin-top:20px;">
                    <a href="rooms.php" class="secondary-btn" style="width:auto; padding:10px 25px; display:inline-block;">Back to Search</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>