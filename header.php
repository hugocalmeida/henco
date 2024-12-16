<?php
// Start the session before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Connect to the database
include 'dbconnect.php';

$page_title = "Henco";

?>