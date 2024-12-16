<?php
include 'header.php';
$page_title = 'Profile';
include 'dbconnect.php';


// Verifica se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Processa a atualização de dados do utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        $stmt = $mysqli->prepare('UPDATE users SET username = ?, email = ? WHERE user_id = ?');
        $stmt->bind_param('ssi', $username, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = 'Dados atualizados com sucesso!';
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verifica a senha atual
        $stmt = $mysqli->prepare('SELECT password FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $hashed_password)) {
            $_SESSION['error_message'] = 'A senha atual está incorreta.';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = 'A nova senha e a confirmação não coincidem.';
        } else {
            // Atualiza a senha
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $stmt->bind_param('si', $new_hashed_password, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = 'Senha alterada com sucesso!';
        }
    }
}

// Obtém os dados do utilizador
$stmt = $mysqli->prepare('SELECT username, email FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

$mysqli->close();
include 'template.php';
?>
<h1>My Profile</h1>
<div class="row">


    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <h2 class="mt-4">Update Profile Information</h2>
    <form method="POST" action="" class="mb-4">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
    </form>

    <h2 class="mt-4">Change Password</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="current_password" class="form-label">Current Password:</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password:</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
    </form>
</div>
<?php include 'footer.php'; ?>
