
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing connection...<br>";

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'hotel_db';

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "✓ Database connected!<br>";

// Check if users table exists
$result = mysqli_query($conn, "SELECT COUNT(*) FROM users");
if ($result) {
    echo "✓ Users table exists<br>";
    $count = mysqli_fetch_array($result);
    echo "Total users: " . $count[0] . "<br>";
} else {
    echo "✗ Users table error: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
echo "<br><a href='login.php'>Go to Login</a>";
?>