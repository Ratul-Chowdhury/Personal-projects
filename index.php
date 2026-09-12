
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';


if (is_logged_in()) {
    header('Location: admin/dashboard.php');
    exit;
}

header('Location: login.php');
exit;
?>