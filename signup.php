<?php
$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_plain = $_POST['password'] ?? '';

    // Check if fields are not empty
    if ($name === '' || $email === '' || $password_plain === '') {
        $message = 'All fields are required.';
        $message_class = 'error';
    } else {
        $password = password_hash($password_plain, PASSWORD_BCRYPT);
        $activation_code = bin2hex(random_bytes(16));

        // Database connection
        require_once 'config/config.php';
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($mysqli->connect_error) {
            $message = 'Could not connect to the database. Please try again later.';
            $message_class = 'error';
        } else {
            // Check if the email already exists
            $stmt_check = $mysqli->prepare('SELECT email FROM users WHERE email = ?');
            if ($stmt_check) {
                $stmt_check->bind_param('s', $email);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    $message = 'Email is already in use. Please try another.';
                    $message_class = 'error';
                } else {
                    // Insert new user
                    $stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role_id, activation_code) VALUES (?, ?, ?, ?, ?)');
                    if ($stmt) {
                        $role_id = 2; // Assuming 2 is the ID for "regular user"
                        $stmt->bind_param('sssis', $name, $email, $password, $role_id, $activation_code);
                        if ($stmt->execute()) {
                            // Send confirmation email using mail()
                            $to = $email;
                            $subject = 'Registration Confirmation';
                            $activation_link = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/activate.php?code=' . $activation_code;
                            $message_body = 'Hello ' . htmlspecialchars($name) . ",\n\n";
                            $message_body .= 'Thank you for registering. Please click the link below to activate your account:' . "\n\n";
                            $message_body .= $activation_link . "\n\n";
                            $message_body .= 'If you cannot click the link, copy and paste it into your browser.' . "\n\n";
                            $message_body .= 'Thank you!' . "\n";
                            $message_body .= 'System Team';

                            // Email headers
                            $headers = 'From: no-reply@harmrecords.com' . "\r\n" .
                                'Reply-To: no-reply@harmrecords.com' . "\r\n" .
                                'X-Mailer: PHP/' . phpversion();

                            if (mail($to, $subject, $message_body, $headers)) {
                                $message = 'Registration successful! Please check your email to activate your account.';
                                $message_class = 'success';
                            } else {
                                $message = 'Error sending confirmation email. Please try again later.';
                                $message_class = 'error';
                            }
                        } else {
                            $message = 'Error registering user. Please try again.';
                            $message_class = 'error';
                        }
                        $stmt->close();
                    } else {
                        $message = 'Error preparing user insertion. Please try again.';
                        $message_class = 'error';
                    }
                }
                $stmt_check->close();
            } else {
                $message = 'Error preparing user verification. Please try again.';
                $message_class = 'error';
            }
            $mysqli->close();
        }
    }
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
    <link href="css/style.css" rel="stylesheet">
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
                <div class="form-input-content">
                    <div class="card login-form mb-0">
                        <div class="card-body pt-5">
                            <a class="text-center" href="index.html"> <h4>Henco</h4></a>
                            
                            <!-- Displaying Message -->
                            <?php if (!empty($message)) : ?>
                                <div class="alert <?php echo $message_class == 'error' ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($message); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" class="mt-5 mb-5 login-input">
                                <div class="form-group mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                                </div>
                                <div class="form-group mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="form-group mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <button type="submit" class="btn login-form__btn submit w-100">Sign Up</button>
                            </form>
                            <p class="mt-5 login-form__footer">Already have an account? <a href="index.php" class="text-primary">Sign In</a> now</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notification = document.querySelector('.alert');
        if (notification) {
            setTimeout(() => {
                const alert = new bootstrap.Alert(notification);
                alert.close();
            }, 5000);
        }
    });
</script>
</body>
</html>
