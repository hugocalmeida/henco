<?php
include 'header.php';
$page_title = 'My Orders';

// Start the session to get the user data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'] ?? null;

// Redirect to login page if the user is not logged in
if (!$user_id) {
    header('Location: index.php');
    exit();
}

// Get the list of orders for the user without pagination
$sql = 'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC';
$stmt_orders = $mysqli->prepare($sql);
$stmt_orders->bind_param('i', $user_id);
$stmt_orders->execute();
$result = $stmt_orders->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 data-translate="myOrders">My Orders</h1>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table id="Data_Table_3" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                <thead>
                    <tr>
                        <th data-translate="orderNumber">Order Number</th>
                        <th data-translate="date">Date</th>
                        <th data-translate="totalAmount">Total Amount</th>
                        <th data-translate="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                            <td>€ <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert" data-translate="noOrders">
            You have not placed any orders yet.
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>