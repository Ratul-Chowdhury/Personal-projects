
<?php
require_once 'config.php';

header('Content-Type: application/json');

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
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $booking_id = (int)$_POST['booking_id'];
            $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $booking_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Booking history deleted!'];
            break;

        case 'delete_multiple_bookings':
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $ids = $_POST['ids'] ?? [];
            foreach ($ids as $id) {
                $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', (int)$id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            $response = ['success' => true, 'message' => 'Selected bookings deleted!'];
            break;

        case 'delete_user_account':
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $target_email = trim($_POST['email']);
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE email = ? AND role = 'customer'");
            mysqli_stmt_bind_param($stmt, 's', $target_email);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            if ($affected > 0) {
                $response = ['success' => true, 'message' => 'User account deleted!'];
            } else {
                $response = ['success' => false, 'message' => 'User not found.'];
            }
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
            $stmt = mysqli_prepare($conn, "INSERT INTO damages (user_id, room_number, item, description) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $user_id, $room_number, $item, $description);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Damage reported!'];
            break;

        case 'read_damages':
            if ($role === 'admin') {
                $result = mysqli_query($conn, "SELECT d.*, u.username FROM damages d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.id DESC");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT * FROM damages WHERE user_id = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $damages = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'damages' => $damages];
            break;

        case 'update_damage_status':
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $damage_id = (int)$_POST['damage_id'];
            $status = trim($_POST['status']);
            $stmt = mysqli_prepare($conn, "UPDATE damages SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $damage_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Status updated!'];
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
            if ($role === 'admin' || $role === 'receptionist') {
                $result = mysqli_query($conn, "SELECT r.*, u.username FROM requests r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.id DESC");
            } else {
                $stmt = mysqli_prepare($conn, "SELECT * FROM requests WHERE user_id = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($stmt, 'i', $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
            }
            $requests = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'requests' => $requests];
            break;

        case 'update_request_status':
            if ($role !== 'admin' && $role !== 'receptionist') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $request_id = (int)$_POST['request_id'];
            $status = trim($_POST['status']);
            $stmt = mysqli_prepare($conn, "UPDATE requests SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $request_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $response = ['success' => true, 'message' => 'Request status updated!'];
            break;

        case 'create_message':
            $receiver_id = (int)$_POST['receiver_id'];
            $text = trim($_POST['text']);
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
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $keyword = '%' . trim($_GET['keyword'] ?? '') . '%';
            $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, username, role, created_at FROM users WHERE (full_name LIKE ? OR email LIKE ? OR username LIKE ?) AND role = 'customer'");
            mysqli_stmt_bind_param($stmt, 'sss', $keyword, $keyword, $keyword);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'customers' => $customers];
            break;

        case 'read_all_users':
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
            $result = mysqli_query($conn, "SELECT id, full_name, email, username, role, created_at FROM users ORDER BY id DESC");
            $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $response = ['success' => true, 'users' => $users];
            break;

        case 'get_stats':
            if ($role !== 'admin') {
                $response['message'] = 'Unauthorized';
                break;
            }
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

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>