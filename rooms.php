<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_rooms'])) {
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = (int)($_POST['guests'] ?? 1);
    $children = (int)($_POST['children'] ?? 0);
    $room_type = $_POST['room_type'] ?? 'any';
    
    $sql = "SELECT * FROM rooms WHERE guests >= ?";
    if ($room_type !== 'any') {
        $sql .= " AND type = ?";
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($room_type !== 'any') {
        mysqli_stmt_bind_param($stmt, "is", $guests, $room_type);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $guests);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    $search_performed = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book a Room - Grand Hotel</title>
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

            <h2>Search & Book Rooms</h2>
            
            <form method="post" action="rooms.php">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                    <div>
                        <label>Check-in Date</label>
                        <input type="date" name="check_in" required>
                    </div>
                    <div>
                        <label>Check-out Date</label>
                        <input type="date" name="check_out" required>
                    </div>
                    <div>
                        <label>Total Guests</label>
                        <input type="number" name="guests" min="1" value="1" required>
                    </div>
                    <div>
                        <label>Number of Children</label>
                        <input type="number" name="children" min="0" value="0">
                        <small style="color:#777; font-size:12px;">Adults calculated automatically</small>
                    </div>
                    <div>
                        <label>Room Type</label>
                        <select name="room_type">
                            <option value="any">Any Type</option>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="Presidential Suite">Presidential Suite</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="search_rooms">Search Available Rooms</button>
            </form>

            <?php if ($search_performed): ?>
                <h3>Available Rooms</h3>
                <?php if (empty($search_results)): ?>
                    <p style="color:#e74c3c; text-align:center;">No rooms available for your criteria.</p>
                <?php else: ?>
                    <div class="rooms-grid">
                        <?php foreach ($search_results as $room): ?>
                            <div class="room-card">
                                <div class="room-card-header">
                                    <h3>Room <?= e($room['number']) ?></h3>
                                    <p><?= e($room['type']) ?></p>
                                </div>
                                <div class="room-card-body">
                                    <div class="room-price">
                                        <?= number_format($room['price']) ?><span>/night</span>
                                    </div>
                                    <p class="room-guests">Sleeps up to <?= e($room['guests']) ?> guests</p>
                                    
                                    <?php if ($room['available'] > 3): ?>
                                        <span class="availability-status available"><?= e($room['available']) ?> rooms available</span>
                                    <?php elseif ($room['available'] > 0): ?>
                                        <span class="availability-status limited">Only <?= e($room['available']) ?> left!</span>
                                    <?php else: ?>
                                        <span class="availability-status" style="background-color:#ffebee; color:#c62828;">Not Available</span>
                                    <?php endif; ?>
                                    
                                    <div class="room-amenities">
                                        <?php 
                                        $amenities = explode(',', $room['amenities']);
                                        foreach ($amenities as $amenity): 
                                        ?>
                                            <span><?= e(trim($amenity)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <form method="post" action="booking.php">
                                        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                        <input type="hidden" name="room_number" value="<?= e($room['number']) ?>">
                                        <input type="hidden" name="room_type" value="<?= e($room['type']) ?>">
                                        <input type="hidden" name="price" value="<?= $room['price'] ?>">
                                        <input type="hidden" name="check_in" value="<?= e($_POST['check_in']) ?>">
                                        <input type="hidden" name="check_out" value="<?= e($_POST['check_out']) ?>">
                                        <input type="hidden" name="guests" value="<?= e($_POST['guests']) ?>">
                                        <input type="hidden" name="children" value="<?= e($_POST['children']) ?>">
                                        <button type="submit" class="book-btn">Book Now</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>