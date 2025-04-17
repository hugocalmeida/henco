<?php
// Include the header file which contains the database connection and other necessary setup
include 'header.php';

// Check if the user is logged in and has the role of administrator (role_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] == false) {
    // If the user is not an administrator, redirect them to the home page
    header('Location: ./home.php');
    exit(); // Stop further execution of the script
}

?>

<!-- 
     Display success message if it exists in the session. 
     The message is wrapped in a Bootstrap alert with a close button.
  -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" data-translate="successMessage">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Unset the success message so it doesn't persist -->
    <?php unset($_SESSION['success_message']); ?> 
<?php endif; ?>

<!-- 
     Display error message if it exists in the session. 
     The message is wrapped in a Bootstrap alert with a close button.
  -->
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" data-translate="errorMessage">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
         <!-- Close button for the alert -->
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row">
    <h1 data-translate="orderHistory">Order History</h1>
    <?php if (!empty($orders)): ?> 
    // Display the orders in a table if orders are found
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

                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                            <td>€ <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td> // Display total amount with euro symbol
                            <td>
                                <?php if ($order['shipped'] == 1): ?>
                                    <span class="badge bg-success" data-translate="shippedAt">Shipped at <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['shipped_at']))); ?></span> // Display shipped date if shipped
                                <?php else: ?>
                                    <span class="badge bg-warning" data-translate="pending">Pending</span> // Display pending if not shipped
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>

                                // Form for handling shipment status updates
                                <form method="POST" action="" class="d-inline-block">

                                    // Hidden input field to pass the order ID
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

                                    // Button to revert shipment status if the order is marked as shipped
                                    <?php if ($order['shipped'] == 1): ?>
                                        <button type="submit" name="mark_shipped" value="0" class="btn btn-danger" onclick="return confirm('Are you sure you want to revert the shipment status?');" data-translate="revertShipment">Revert Shipment</button>

                                    // Button to mark order as shipped if it's not already shipped
                                    <?php else: ?>
                                        <button type="submit" name="mark_shipped" value="1" class="btn btn-success" onclick="return confirm('Are you sure you want to mark this order as shipped?');" data-translate="markAsShipped">Mark as Shipped</button>
                                    <?php endif; ?>
                                </form>
                            </td>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    // Display message if no orders are found in the database
    <?php else: ?>
        <p class="text-center" data-translate="noOrdersFound">No orders found.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

