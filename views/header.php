<?php
// This file is used to initialize the database connection and manage sessions.

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection file and establish the connection
require_once __DIR__ . "/dbconnect.php";
$mysqli = db_connect();

$page_title = "Henco";

$_SESSION['is_admin'] = getSession('role_id') == 1;


?>