<?php
// Connect to the database
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'dbconnect.php';

// Verify if function was already declared
if (!function_exists('get_setting')) {
    // Função para buscar configuração
    function get_setting($mysqli, $key) {
        $stmt = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $stmt->bind_result($value);
        $stmt->fetch();
        $stmt->close();
        return $value;
    }
}

// Store company name in session
if (isset($mysqli) && !isset($_SESSION['company_name'])) {
    $_SESSION['company_name'] = get_setting($mysqli, 'company_name') ?: 'Default Company Name';
} else {
    $_SESSION['company_name'] =  'Default Company Name';
}
?>
