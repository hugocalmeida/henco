<?php
/** There are some functions that should be grouped in a file or class. Functions like `db_connect` */

// Include necessary files and functions
require_once 'config/utils.php';

// Establish database connection
$mysqli = db_connect();

// Check if client ID is provided via GET request
if (isset($_GET['client_id']) && !empty($_GET['client_id'])) {
    // Sanitize the client ID
    $client_id = intval($_GET['client_id']);

    // Prepare a SQL statement to fetch client details from the database
    if ($stmt = $mysqli->prepare('SELECT address, city, state, zip FROM clients WHERE id = ?')) {
        // Bind the client ID parameter to the statement
        $stmt->bind_param('i', $client_id);
        // Execute the statement
        if ($stmt->execute()) {
            // Bind the result variables
            $stmt->bind_result($address, $city, $state, $zip);
            // Fetch the client details
            if ($stmt->fetch()) {
                // Create an array to store client details
                $client_details = [
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip
                ];

                // Return the client details as a JSON response
                echo json_encode($client_details);
                // Close the statement
                $stmt->close();
                // Exit the script
                exit();
            } else {
                // If no client found, set HTTP response code to 404 (Not Found)
                http_response_code(404);
                // Return an error message as JSON
                echo json_encode(['error' => 'Client not found']);
            }
        } else {
            http_response_code(500); // Set HTTP response code to 500 (Internal Server Error)
            echo json_encode(['error' => 'Database query failed']);
        }
        $stmt->close();
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database prepare failed']);
    }
} else {
    // If client ID is missing or invalid, set HTTP response code to 400 (Bad Request)
    http_response_code(400);
    // Return an error message as JSON
    echo json_encode(['error' => 'Invalid client ID']);
    // Exit the script
    exit();
}
