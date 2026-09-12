
<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'delete_user') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin/dashboard.php');
    exit;
}

if ($action === 'delete_multiple_users') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types, ...$ids);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin/dashboard.php');
    exit;
}

if ($action === 'delete_booking') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin/dashboard.php');
    exit;
}

if ($action === 'delete_multiple_bookings') {
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id IN ($placeholders)");
        mysqli_stmt_bind_param($stmt, $types, ...$ids);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin/dashboard.php');
    exit;
}

if ($action === 'delete_damage') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM damages WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin/dashboard.php');
    exit;
}

header('Location: admin/dashboard.php');
exit;
?>