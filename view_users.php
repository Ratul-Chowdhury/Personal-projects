
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>All Users in Database</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Full Name</th></tr>";

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id");

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $row['full_name'] . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><a href='check.php'>Back</a>";
?>