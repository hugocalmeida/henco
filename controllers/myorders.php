<?php
include 'header.php'; // Include header to set up page and utils

$page_title = translate('myOrders', $translations); // Use translation for page title

// Get user ID from session or redirect to login if not logged in
$user_id = $_SESSION['user_id'] ?? null; // Use null coalescing for brevity

if (!$user_id) {
    header('Location: index.php'); // Redirect to login
    exit(); // Ensure no further execution after redirect
}

// Fetch user's orders from the database
$orders = fetch_user_orders($mysqli, $user_id);

// Include template for consistent layout
include 'template.php'; // Include template after setting up content
?>

<div class="row">
    <h1 data-translate="myOrders"><?php echo htmlspecialchars(translate('myOrders', $translations)); ?></h1>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table id="Data_Table_3" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                <thead>
                    <tr>
                        <th data-translate="orderNumber"><?php echo htmlspecialchars(translate('orderNumber', $translations)); ?></th>
                        <th data-translate="date"><?php echo htmlspecialchars(translate('date', $translations)); ?></th>
                        <th data-translate="totalAmount"><?php echo htmlspecialchars(translate('totalAmount', $translations)); ?></th>
                        <th data-translate="actions"><?php echo htmlspecialchars(translate('actions', $translations)); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td> <!-- Format date for display -->
                            <td>
                                <?php
                                    $currency = get_setting($mysqli, 'currency') ?? '€'; // Use a function to get currency
                                    echo htmlspecialchars($currency . ' ' . number_format($order['total_amount'], 2, ',', '.')); // Format total amount with currency
                                ?>
                            </td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">
                                    <?php echo htmlspecialchars(translate('viewDetails', $translations)); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <?php display_message(translate('noOrders', $translations), 'info'); ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?> <!-- Include footer for closing tags and scripts -->

<?php
/**
 * Fetches orders for a specific user from the database.
 *
 * @param mysqli $mysqli Database connection object.
 * @param int $user_id User ID to fetch orders for.
 * @return array Returns an array of orders or an empty array if none are found or an error occurs.
 */
function fetch_user_orders(mysqli $mysqli, int $user_id): array {
    $sql = 'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC';
    $stmt = $mysqli->prepare($sql); // Prepare the SQL statement
    if (!$stmt) {
        error_log('Database error: ' . $mysqli->error); // Log the error for debugging
        return []; // Return an empty array if statement preparation fails
    }

    $stmt->bind_param('i', $user_id); // Bind the user ID parameter
    if (!$stmt->execute()) {
        error_log('Database error: ' . $stmt->error); // Log the error if execution fails
        $stmt->close();
        return []; // Return an empty array if execution fails
    }

    $result = $stmt->get_result(); // Get the result set from the executed statement
    $orders = $result->fetch_all(MYSQLI_ASSOC); // Fetch all orders as an associative array
    $stmt->close(); // Close the statement
    return $orders; // Return the array of orders
}
?>