php
<?php

// config/utils.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config.php';

// Function to establish database connection
function getDatabaseConnection() {
    static $mysqli = null;
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if ($mysqli->connect_error) {
            handleError('Database connection error: ' . $mysqli->connect_error);
        }
    }
    return $mysqli;
}

// Function to handle errors and display them
function handleError($message, $isFatal = false) {
    $_SESSION['error_message'] = $message;
    if ($isFatal) {
        //header('Location: error.php');
        exit();
    }
}

// Function to clear error messages
function clearErrorMessage() {
    unset($_SESSION['error_message']);
}

// Function to get setting
function getSetting($mysqli, $key) {
    $stmt = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    if (!$stmt) {
        handleError('Error preparing setting retrieval: ' . $mysqli->error);
        return null;
    }
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return $value;
}
?>