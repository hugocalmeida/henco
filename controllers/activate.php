<?php
// Database connection
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$message = '';
$message_class = '';

// Error handling function
function handle_error($message) {
    return ['message' => $message, 'message_class' => 'error'];
}

function handle_success($message) {
    return ['message' => $message, 'message_class' => 'success'];
}

if (isset($_GET['code'])) {
    $activation_code = $_GET['code'];

    // Sanitize the activation code
    $activation_code = $mysqli->real_escape_string($activation_code);

    if ($mysqli->connect_error) {
        $error = handle_error('Could not connect to the database. Please try again later.');
    } else {
        // Check if the activation code exists and the account is not yet activated
        $stmt = $mysqli->prepare('SELECT user_id FROM users WHERE activation_code = ? AND is_active = 0');
        if (!$stmt) {
            $error = handle_error('Error preparing the activation code verification. Please try again.');
        } else {
            if (!$stmt->bind_param('s', $activation_code) || !$stmt->execute() || $stmt->store_result() === false) {
                $error = handle_error('Error during activation process. Please try again.');
            } elseif ($stmt->num_rows !== 1) {
                $error = handle_error('Invalid activation code or account already activated.');
            } else {
                $stmt_update = $mysqli->prepare('UPDATE users SET is_active = 1, activation_code = NULL WHERE activation_code = ?');
                if (!$stmt_update) {
                    $error = handle_error('Error preparing account activation. Please try again.');
                } else {
                    if (!$stmt_update->bind_param('s', $activation_code) || !$stmt_update->execute()) {
                        $error = handle_error('Error activating the account. Please try again.');
                    } else {
                        $success = handle_success('Your account has been successfully activated! You can now log in.');
                    }
                    $stmt_update->close();
                }
            }
            $stmt->close();            
        }
        $mysqli->close();
    }
} else {
    $error = handle_error('Activation code not provided.');
}

// Determine message and class based on success or error
if (isset($success)) {
    $message = $success['message'];
    $message_class = $success['message_class'];
} elseif (isset($error)) {
    $message = $error['message'];
    $message_class = $error['message_class'];
}

if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}

?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Activation - System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">  
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Account Activation</h3>
                    </div>
                    <div class="card-body">
                                    <div class="activation-container">
                                        <p class="text-<?php echo ($message_class === 'success') ? 'success' : 'danger'; ?>"><?php echo htmlspecialchars($message); ?></p>
                                        <?php if ($message_class === 'success'): ?>
                                            <a href="index.php" class="btn btn-success">Go to Login</a>
                                        <?php endif; ?>
                                    </div>
                    </div>
                    <div class="card-footer text-center">
                        <p>&copy; <?php echo date("Y"); ?> Your Company. All rights reserved.</p>
                    </div>
                </div>                
            </div>
        </div>
    </div>

</body>
</html>
