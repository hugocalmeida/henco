<?php
// Start session, if necessary
// session_start();

// Database connection
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$message = '';
$message_class = '';

if (isset($_GET['code'])) {
    $activation_code = $_GET['code'];

    // Sanitize the activation code
    $activation_code = $mysqli->real_escape_string($activation_code);

    if ($mysqli->connect_error) {
        $message = 'Could not connect to the database. Please try again later.';
        $message_class = 'error';
    } else {
        // Check if the activation code exists and the account is not yet activated
        $stmt = $mysqli->prepare('SELECT user_id FROM users WHERE activation_code = ? AND is_active = 0');
        if ($stmt) {
            $stmt->bind_param('s', $activation_code);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                // Update the account to activated
                $stmt_update = $mysqli->prepare('UPDATE users SET is_active = 1, activation_code = NULL WHERE activation_code = ?');
                if ($stmt_update) {
                    $stmt_update->bind_param('s', $activation_code);
                    if ($stmt_update->execute()) {
                        $message = 'Your account has been successfully activated! You can now log in.';
                        $message_class = 'success';
                    } else {
                        $message = 'Error activating the account. Please try again.';
                        $message_class = 'error';
                    }
                    $stmt_update->close();
                } else {
                    $message = 'Error preparing account activation. Please try again.';
                    $message_class = 'error';
                }
            } else {
                $message = 'Invalid activation code or account already activated.';
                $message_class = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Error preparing the activation code verification. Please try again.';
            $message_class = 'error';
        }
        $mysqli->close();
    }
} else {
    $message = 'Activation code not provided.';
    $message_class = 'error';
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register - System</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="h-100">

<!-- Preloader -->
<div id="preloader" style="display: none;">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
        </svg>
    </div>
</div>
<!-- End Preloader -->
        
<div class="login-form-bg h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100">
                <div class="col-xl-6">
                    <div class="error-content">
                        <div class="card mb-0">
                            <div class="card-body text-center">
                                <h1 class="error-text text-primary">Thank You!</h1>
                                <h4 class="mt-4"><i class="fa fa-thumbs-up"></i> You're in!</h4>
                                    <div class="activation-container">
                                        <h2>Activation Successful</h2>
                                        <p class="message <?php echo htmlspecialchars($message_class); ?>">
                                            <?php echo htmlspecialchars($message); ?>
                                        </p>
                                        <?php if ($message_class === 'success') : ?>
                                            <a href="index.php" class="login-link">Click here to log in</a>
                                        <?php endif; ?>
                                        </div>
                                <form class="mt-5 mb-5">
                                    
                                    <div class="text-center mb-4 mt-4"><a href="index.php" class="btn btn-primary">Sign in here</a>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

</body>
</html>
