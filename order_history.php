<?php
include 'header.php';

// Verifique se o usuário está logado e se ele é administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Conexão com o banco de dados
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Erro de conexão com o banco de dados (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Manipular marcar como expedida ou reverter o status de envio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_shipped'])) {
    $order_id = intval($_POST['order_id']);
    $mark_shipped = intval($_POST['mark_shipped']);
    $shipped_at = $mark_shipped ? date('Y-m-d H:i:s') : null;

    $stmt_update = $mysqli->prepare('UPDATE orders SET shipped = ?, shipped_at = ? WHERE id = ?');
    $stmt_update->bind_param('isi', $mark_shipped, $shipped_at, $order_id);
    if ($stmt_update->execute()) {
        $_SESSION['success_message'] = $mark_shipped ? 'Order marked as shipped successfully!' : 'Shipment status reverted successfully!';
    } else {
        $_SESSION['error_message'] = 'Failed to update shipment status. Please try again.';
    }
    $stmt_update->close();
    header('Location: order_history.php');
    exit();
}

// Obter lista de pedidos
$sql = 'SELECT o.*, u.username FROM orders o INNER JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC';
$result = $mysqli->query($sql);
$orders = $result->fetch_all(MYSQLI_ASSOC);
$result->free();
$mysqli->close();
include 'template.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" data-translate="successMessage">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" data-translate="errorMessage">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row">
    <h1 data-translate="orderHistory">Order History</h1>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table id="Data_Table_6" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                <thead>
                    <tr>
                        <th data-translate="orderNumber">Order Number</th>
                        <th data-translate="user">User</th>
                        <th data-translate="date">Date</th>
                        <th data-translate="totalValue">Total Value</th>
                        <th data-translate="shipped">Shipped</th>
                        <th data-translate="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                            <td>€ <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td>
                            <td>
                                <?php if ($order['shipped'] == 1): ?>
                                    <span class="badge bg-success" data-translate="shippedAt">Shipped at <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['shipped_at']))); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning" data-translate="pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>
                                <form method="POST" action="" class="d-inline-block">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <?php if ($order['shipped'] == 1): ?>
                                        <button type="submit" name="mark_shipped" value="0" class="btn btn-danger" onclick="return confirm('Are you sure you want to revert the shipment status?');" data-translate="revertShipment">Revert Shipment</button>
                                    <?php else: ?>
                                        <button type="submit" name="mark_shipped" value="1" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this order as shipped?');" data-translate="markAsShipped">Mark as Shipped</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center" data-translate="noOrdersFound">No orders found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>