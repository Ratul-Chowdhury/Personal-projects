<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!function_exists('is_logged_in')) {
    function is_logged_in() { return isset($_SESSION['user_id']); }
}
if (!function_exists('get_user_id')) {
    function get_user_id() { return $_SESSION['user_id'] ?? 0; }
}
if (!function_exists('get_user_role')) {
    function get_user_role() { return $_SESSION['role'] ?? 'guest'; }
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = get_user_id();
$role = get_user_role();

$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'search_rooms':
            $type = $_GET['type'] ?? 'any';
            $guests = (int)($_GET['guests'] ?? 1);
            $sql = "SELECT * FROM rooms WHERE guests >= ?";
            $params = [$guests];
            $types = 'i';
            if ($type !== 'any') {
                $sql .= " AND type = ?";
                $params[] = $type;
                $types .= 's';
            }
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'rooms' => $rooms];
            break;

        case 'create_booking':
            $room_id = (int)$_POST['room_id'];
            $method = trim($_POST['method']);
            $guests = (int)$_POST['guests'];
            $children = (int)$_POST['children'];
            $adults = $guests - $children;
            $checkin = $_POST['check_in'];
            $checkout = $_POST['check_out'];

            $stmt = mysqli_prepare($conn, "SELECT number, type, price FROM rooms WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $room_id);
            mysqli_stmt_execute($stmt);
            $room = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($room) {
                $stmt = mysqli_prepare($conn, "INSERT INTO bookings (user_id, room_id, room_number, room_type, amount, method, guests, adults, children, check_in, check_out) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'iissisiisss', $user_id, $room_id, $room['number'], $room['type'], $room['price'], $method, $guests, $adults, $children, $checkin, $checkout);
                mysqli_stmt_execute($stmt);
                $booking_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                $response = ['success' => true, 'message' => 'Booking confirmed!', 'booking_id' => $booking_id];
            }
            break;

        case 'read_bookings':
            if ($role === 'admin' || $role === 'receptionist') {
                $result = mysqli_query($conn, "SELECT b.*, u.username, u.email FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.id DESC");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'bookings' => $bookings];
            break;

        case 'update_booking':
            $booking_id = (int)$_POST['booking_id'];
            $checkin = $_POST['check_in'];
            $checkout = $_POST['check_out'];
            if ($role === 'customer') {
                $stmt = mysqli_prepare($conn, "UPDATE bookings SET check_in = ?, check_out = ? WHERE id = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, 'ssii', $checkin, $checkout, $booking_id, $user_id);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE bookings SET check_in = ?, check_out = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ssi', $checkin, $checkout, $booking_id);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Booking updated!'];
            break;

        case 'delete_booking':
            $booking_id = (int)$_POST['booking_id'];
            if ($role === 'customer') {
                $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $user_id);
            } else {
                $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $booking_id);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Booking deleted!'];
            break;

        case 'delete_booking_history':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $booking_id = (int)$_POST['booking_id'];
            $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $booking_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Booking history deleted!'];
            break;

        case 'delete_multiple_bookings':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $safe_id = (int)$id; 
                    $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'i', $safe_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            $response = ['success' => true, 'message' => 'Selected bookings deleted!'];
            break;

        case 'delete_user_account':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $target_email = trim($_POST['email']);
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE email = ? AND role = 'customer'");
            mysqli_stmt_bind_param($stmt, 's', $target_email);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            if ($affected > 0) { $response = ['success' => true, 'message' => 'User account deleted!']; }
            else { $response = ['success' => false, 'message' => 'User not found.']; }
            break;

        case 'create_review':
            $booking_id = (int)$_POST['booking_id'];
            $text = trim($_POST['text']);
            $rating = (int)($_POST['rating'] ?? 5);
            $stmt = mysqli_prepare($conn, "INSERT INTO reviews (user_id, booking_id, text, rating) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iisi', $user_id, $booking_id, $text, $rating);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Review submitted!'];
            break;

        case 'read_reviews':
            if ($role === 'admin' || $role === 'receptionist') {
                $result = mysqli_query($conn, "SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.id DESC");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT * FROM reviews WHERE user_id = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'reviews' => $reviews];
            break;

        case 'update_review':
            $review_id = (int)$_POST['review_id'];
            $text = trim($_POST['text']);
            $stmt = mysqli_prepare($conn, "UPDATE reviews SET text = ? WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, 'sii', $text, $review_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Review updated!'];
            break;

        case 'delete_review':
            $review_id = (int)$_POST['review_id'];
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $review_id, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Review deleted!'];
            break;

        case 'create_damage':
            $room_number = trim($_POST['room_number']);
            $item = trim($_POST['item']);
            $description = trim($_POST['description']);
            
            if (empty($description) || $description === '0') {
                $description = 'No description provided';
            }
            
            $booking_id = 0;
            $stmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE room_number = ? ORDER BY id DESC LIMIT 1");
            mysqli_stmt_bind_param($stmt, 's', $room_number);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($res)) {
                $booking_id = (int)$row['id'];
            }
            mysqli_stmt_close($stmt);
            
            if ($booking_id == 0) {
                $stmt = mysqli_prepare($conn, "SELECT b.id FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE r.number = ? ORDER BY b.id DESC LIMIT 1");
                mysqli_stmt_bind_param($stmt, 's', $room_number);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $booking_id = (int)$row['id'];
                }
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO damages (user_id, room_number, item, description, booking_id) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isssi', $user_id, $room_number, $item, $description, $booking_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($booking_id > 0) {
                $response = ['success' => true, 'message' => 'Damage reported and linked to booking #' . $booking_id . '!'];
            } else {
                $response = ['success' => true, 'message' => 'Damage reported! (No booking found - Admin can manually link it when adding charges)'];
            }
            break;

        case 'read_damages':
            if ($role === 'admin') {
                $result = mysqli_query($conn, "SELECT d.*, u.username FROM damages d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.id DESC");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT d.*, u.username FROM damages d LEFT JOIN users u ON d.user_id = u.id WHERE d.user_id = ? ORDER BY d.id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $damages = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'damages' => $damages];
            break;

        case 'update_damage_status':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $damage_id = (int)$_POST['damage_id'];
            $status = trim($_POST['status']);
            $stmt = mysqli_prepare($conn, "UPDATE damages SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $damage_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Status updated!'];
            break;

        case 'update_damage_charges':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $damage_id = (int)$_POST['damage_id'];
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            $extra_charges = floatval($_POST['extra_charges']);
            $reason = trim($_POST['reason']);
            
            $stmt = mysqli_prepare($conn, "UPDATE damages SET extra_charges = ?, reason = ?, status = 'Resolved' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'dsi', $extra_charges, $reason, $damage_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            if ($booking_id > 0) {
                $stmt = mysqli_prepare($conn, "SELECT amount FROM bookings WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $booking_id);
                mysqli_stmt_execute($stmt);
                $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);
                
                if ($booking) {
                    $new_amount = $booking['amount'] + $extra_charges;
                    $stmt = mysqli_prepare($conn, "UPDATE bookings SET amount = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'di', $new_amount, $booking_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $response = ['success' => true, 'message' => 'Charges applied successfully! New booking total: ' . $new_amount];
                } else {
                    $response = ['success' => false, 'message' => 'Booking ID #' . $booking_id . ' not found. Charges were saved to the damage report but not applied to a bill.'];
                }
            } else {
                $response = ['success' => true, 'message' => 'Damage marked as resolved. No booking ID provided, so no charges were applied to a bill.'];
            }
            break;

        case 'create_request':
            $type = trim($_POST['type']);
            $text = trim($_POST['text']);
            $stmt = mysqli_prepare($conn, "INSERT INTO requests (user_id, type, text) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iss', $user_id, $type, $text);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Request sent!'];
            break;

        case 'read_requests':
            if ($role === 'admin') {
                $result = mysqli_query($conn, "SELECT r.*, u.username FROM requests r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.id DESC");
            } elseif ($role === 'receptionist') {
                $stmt = mysqli_prepare($conn, "SELECT r.*, u.username FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.type != 'food' AND r.type != 'laundry' AND NOT (r.type = 'service_instruction' AND r.user_id = ?) ORDER BY r.id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            } elseif ($role === 'roomservice') {
                $stmt = mysqli_prepare($conn, "SELECT r.*, u.username FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.type = 'service_instruction' ORDER BY r.id DESC");
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            } else {
                $stmt = mysqli_prepare($conn, "SELECT r.*, u.username FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.user_id = ? ORDER BY r.id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $requests = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'requests' => $requests];
            break;

        case 'update_request_status':
            if ($role !== 'admin' && $role !== 'receptionist' && $role !== 'roomservice') { $response['message'] = 'Unauthorized'; break; }
            $request_id = (int)$_POST['request_id'];
            $status = trim($_POST['status']);
            $stmt = mysqli_prepare($conn, "UPDATE requests SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $request_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Request status updated!'];
            break;

        case 'create_message':
            $text = trim($_POST['text']);
            $receiver_id = (int)($_POST['receiver_id'] ?? 0);
            if ($role === 'roomservice') {
                $res = mysqli_query($conn, "SELECT id FROM users WHERE role = 'receptionist' LIMIT 1");
                $rec = mysqli_fetch_assoc($res);
                if ($rec) { $receiver_id = $rec['id']; }
                else { $response = ['success' => false, 'message' => 'No receptionist found.']; break; }
            }
            $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, text) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iis', $user_id, $receiver_id, $text);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Message sent!'];
            break;

        case 'read_messages':
            $stmt = mysqli_prepare($conn, "SELECT m.*, u.username as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY m.id DESC");
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'messages' => $messages];
            break;

        case 'search_customers':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $keyword = '%' . trim($_GET['keyword'] ?? '') . '%';
            $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, username, role, created_at FROM users WHERE (full_name LIKE ? OR email LIKE ? OR username LIKE ?) AND role = 'customer'");
            mysqli_stmt_bind_param($stmt, 'sss', $keyword, $keyword, $keyword);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'customers' => $customers];
            break;

        case 'read_all_users':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $result = mysqli_query($conn, "SELECT id, full_name, email, username, role, created_at FROM users ORDER BY id DESC");
            $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'users' => $users];
            break;

        case 'get_stats':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
            $total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'customer'"))['count'];
            $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM bookings WHERE status = 'Verified'"))['total'];
            $pending_damages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM damages WHERE status = 'Pending'"))['count'];
            $response = ['success' => true, 'stats' => [
                'total_bookings' => $total_bookings,
                'total_customers' => $total_customers,
                'total_revenue' => $total_revenue ?? 0,
                'pending_damages' => $pending_damages
            ]];
            break;

        case 'delete_multiple_requests':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $safe_id = (int)$id; 
                    $stmt = mysqli_prepare($conn, "DELETE FROM requests WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'i', $safe_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            $response = ['success' => true, 'message' => 'Selected service logs deleted!'];
            break;

        case 'delete_multiple_damages':
            if ($role !== 'admin') { $response['message'] = 'Unauthorized'; break; }
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $safe_id = (int)$id; 
                    $stmt = mysqli_prepare($conn, "DELETE FROM damages WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'i', $safe_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            $response = ['success' => true, 'message' => 'Selected damage reports deleted!'];
            break;

        // --- NEW: Delete Multiple Users ---
        case 'delete_multiple_users':
            if ($role !== 'admin') { 
                $response['message'] = 'Unauthorized'; 
                break; 
            }
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $deleted_count = 0;
                foreach ($ids as $id) {
                    $safe_id = (int)$id;
                    // Prevent deleting yourself
                    if ($safe_id == $user_id) {
                        continue; 
                    }
                    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, 'i', $safe_id);
                    mysqli_stmt_execute($stmt);
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $deleted_count++;
                    }
                    mysqli_stmt_close($stmt);
                }
                $response = ['success' => true, 'message' => $deleted_count . ' user account(s) deleted successfully!'];
            } else {
                $response = ['success' => false, 'message' => 'No users selected.'];
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>