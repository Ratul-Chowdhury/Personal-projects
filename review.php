<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 5);
    $review_text = trim($_POST['review_text'] ?? '');
    
    if ($review_text !== '' && $booking_id > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO reviews (user_id, booking_id, text, rating) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisi", $_SESSION['user_id'], $booking_id, $review_text, $rating);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash'] = 'Review submitted successfully!';
        }
        
        mysqli_stmt_close($stmt);
    }
}

header('Location: admin/dashboard.php');
exit;
?>