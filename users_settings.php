<?php
include 'header.php';
$page_title = 'Users';

// Verifica se o utilizador está autenticado e é administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Conexão com o banco de dados
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Erro ao conectar ao banco de dados (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Processa a atualização ou exclusão de utilizadores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $edit_user_id = intval($_POST['user_id']);

    // Atualiza o papel do utilizador
    if (isset($_POST['role_id'])) {
        $new_role_id = intval($_POST['role_id']);
        if ($edit_user_id != $_SESSION['user_id']) {
            $stmt_update = $mysqli->prepare('UPDATE users SET role_id = ? WHERE user_id = ?');
            $stmt_update->bind_param('ii', $new_role_id, $edit_user_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    // Exclui o utilizador
    if (isset($_POST['delete_user'])) {
        if ($edit_user_id != $_SESSION['user_id']) {
            $stmt_delete = $mysqli->prepare('DELETE FROM users WHERE user_id = ?');
            $stmt_delete->bind_param('i', $edit_user_id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    }
}

// Obtém todos os utilizadores
$sql = 'SELECT user_id, username, email, role_id FROM users';
$result = $mysqli->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
$result->free();
$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 data-translate="users">Users</h1>
    <div class="table-responsive">
        <table id="Data_Table_4" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="email">Email</th>
                    <th data-translate="role">Role</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td data-translate="<?php echo $user['role_id'] == 1 ? 'admin' : 'user'; ?>">
                            <?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?>
                        </td>
                        <td>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="" class="d-inline-block">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="role_id" value="<?php echo $user['role_id'] == 1 ? 2 : 1; ?>">
                                    <button type="submit" class="btn btn-warning" data-translate="<?php echo $user['role_id'] == 1 ? 'revokeAdmin' : 'makeAdmin'; ?>">
                                        <?php echo $user['role_id'] == 1 ? 'Revoke Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>
                                <form method="POST" action="" class="d-inline-block ms-2">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <button type="submit" class="btn btn-danger" data-translate="delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted" data-translate="na">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="4" class="text-center" data-translate="noUsersFound">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>