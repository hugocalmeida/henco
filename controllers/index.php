<?php
require_once 'config/utils.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']) ?? '';
    $password = $_POST['password'];

    // Connect to the database
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        $message = 'Could not connect to the database. Please try again later.';
        $message_class = 'error';
    } else {
        // Verify if the user exists
        $stmt = $mysqli->prepare('SELECT user_id, username, password, is_active, role_id FROM users WHERE email = ?');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $username, $hashed_password, $is_active, $role_id);
                $stmt->fetch();
                if ($is_active) {
                    if (password_verify($password, $hashed_password)) {
                        // Successful login
                        $_SESSION['user_id'] = $id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role_id'] = $role_id; // Store role to verify admin access
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $message = 'Incorrect password. Please try again.';
                        $message_class = 'error';
                    }
                } else {
                    $message = 'The account has not been activated yet. Please check your email.';
                    $message_class = 'error';
                }
            } else {
                $message = 'No account found with that email.';
                $message_class = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Error preparing the query. Please try again.';
            $message_class = 'error';
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - System</title>
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
                                <a class="text-center" href="index.php">
                                    <h4>Henco</h4>
                                </a>

                                <?php
                                if (!empty($message)) {
                                    display_message($message, $message_class);
                                }
                                ?>

                                <form method="POST" action="" class="mt-5 mb-5 login-input">
                                    <div class="form-group mb-3">
                                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                                    </div>
                                    <button type="submit" class="btn login-form__btn submit w-100">Sign In</button>
                                </form>
                                <p class="mt-5 login-form__footer">Don't have an account? <a href="signup.php" class="text-primary">Sign Up</a> now</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
