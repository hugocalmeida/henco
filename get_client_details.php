<?php
include 'header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['client_id'])) {
    $client_id = intval($_GET['client_id']);

    // Get client details from the database
    $stmt = $mysqli->prepare('SELECT address, city, state, zip FROM clients WHERE id = ?');
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $stmt->bind_result($address, $city, $state, $zip);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    // Create an array with client details
    $client_details = [
        'address' => $address,
        'city' => $city,
        'state' => $state,
        'zip' => $zip
    ];

    // Return the client details as JSON
    echo json_encode($client_details);
    exit();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid client ID']);
    exit();
}
