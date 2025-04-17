<?php
// Include the header file which contains the database connection and other necessary setup
include 'header.php';

// Check if the user is logged in, if not, redirect to the login page
checkUserLogin();

// Set the page title to 'Order History'
$page_title = translate('order_history');

// Retrieve the user ID from the session
$user_id = getSession('user_id');

// Retrieve the user's role ID from the session
$role_id = getSession('role_id');

// Check if the user is an admin (role_id == 1)
$is_admin = $role_id == 1;

// Function to get order items

// Get the orders for the current user
$orders = getUserOrders($mysqli, $user_id);

// Include the template file which contains the header and footer
include 'template.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- Display the main heading for the page -->
            <h1><?php echo translate('order_history'); ?></h1>
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])) : ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Display a table of orders if there are any -->
            <?php if ($orders) : ?>
                <table id="ordersTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?php echo translate('order_id'); ?></th>
                            <th><?php echo translate('date'); ?></th>
                            <th><?php echo translate('total'); ?></th>
                            <th><?php echo translate('status'); ?></th>
                            <th><?php echo translate('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Loop through each order and display its details
                        foreach ($orders as $order) :
                            $order_id = htmlspecialchars($order['id']);
                            $order_date = date('d/m/Y H:i', strtotime($order['created_at']));
                            $order_total = formatCurrency($order['total_amount']);
                            $order_status = htmlspecialchars($order['status']);
                        ?>
                            <tr>
                                <td><?php echo $order_id; ?></td>
                                <td><?php echo $order_date; ?></td>
                                <td><?php echo $order_total; ?></td>
                                <td><?php echo translate($order_status); ?></td>
                                <td>
                                    <!-- Link to view the details of the order -->
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-eye"></i> <?php echo translate('view'); ?>
                                    </a>
                                    <?php if ($is_admin && $order['status'] != 'shipped') : ?>
                                        <form method="post" action="mark_order_shipped.php" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fa fa-truck"></i> <?php echo translate('mark_shipped'); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <!-- Display a message if no orders are found -->
                <p><?php echo translate('no_orders_found'); ?></p>
            <?php endif; ?>

        </div>
    </div>
</div>
