<?php
// There are some functions that should be grouped in a file or class. Functions like `display_message`
// Include utility functions and database connection
include 'utils.php';

// Start the session to manage user data
session_start();

// Include the header file which contains the database connection and other necessary setup
include 'header.php';

// Set the page title
$page_title = 'Cart';

// Check if the request method is POST and if the 'remove_from_cart' button was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    // Sanitize and validate the product ID from the form
    $product_id = intval($_POST['product_id']);

    // Remove the product from the cart stored in the session
    unset($_SESSION['cart'][$product_id]);

    // Set a success message to be displayed to the user
    $_SESSION['success_message'] = 'Product removed from cart successfully!';

    // Redirect the user back to the cart page to reflect the changes
    header('Location: cart.php');

    // Stop further execution of the script
    exit();
}

// Check if the request method is POST and if the 'update_cart' button was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    // Loop through each product and its quantity submitted in the form to update the cart
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        // Sanitize and validate the product ID and quantity
        $product_id = intval($product_id);
        $quantity = intval($quantity);

        // Check if the quantity is greater than 0
        if ($quantity > 0) {
            // Update the quantity of the product in the cart in the session
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            // If the quantity is 0 or less, remove the product from the cart in the session
            unset($_SESSION['cart'][$product_id]);
        }
    }

    // Set a success message to be displayed to the user
    $_SESSION['success_message'] = 'Cart updated successfully!';

    // Redirect the user back to the cart page to reflect the changes
    header('Location: cart.php');

    // Stop further execution of the script
    exit();
}

// Initialize an empty array to store the products in the cart
$cart_items = [];

// Check if the cart is not empty in the session
if (!empty($_SESSION['cart'])) {
    // Get the product IDs from the cart stored in the session
    $product_ids = array_keys($_SESSION['cart']);

    // Prepare placeholders and types for the SQL query
    // Create placeholders for the prepared statement based on the number of products
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    // Create a string of types for the prepared statement (all integers in this case)
    $types = str_repeat('i', count($product_ids));

    // Merge the types and product IDs into a single array for the bind_param function
    $params = array_merge([$types], $product_ids);

    // Fetch product details from the database
    // Prepare the SQL statement to fetch product details from the database
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id IN (' . $placeholders . ')');

    // Check if the statement was prepared successfully
    if ($stmt) {
        // Bind the parameters to the prepared statement
        $stmt->bind_param(...$params);
        // Execute the prepared statement
        $stmt->execute();
        // Get the result of the query
        $result = $stmt->get_result();
        // Close the prepared statement
        $stmt->close(); 

        // Fetch each product from the result set
        while ($product = $result->fetch_assoc()) {
            $product_id = $product['id'];

            // Add the product details to the cart items array
            $cart_items[] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                // Get the quantity from the session cart
                'quantity' => $_SESSION['cart'][$product_id]['quantity'] 
            ];
        }
    } else {
        // If the statement preparation failed, set an error message
        $_SESSION['error_message'] = 'Failed to prepare product query.';
    }
} 
?>

<!-- Display the checkout title -->
<h1 data-translate="checkout">Checkout</h1> 

<?php 
// Check if there is a success message in the session
if (isset($_SESSION['success_message'])) { 
    // Display the success message
    display_message($_SESSION['success_message'], 'success');

    // Remove the success message from the session to prevent it from being displayed again
    unset($_SESSION['success_message']); 
} 
 
// Check if there is an error message in the session 
if (isset($_SESSION['error_message'])) { 
    // Display the error message
    display_message($_SESSION['error_message'], 'error');

    // Remove the error message from the session to prevent it from being displayed again
    unset($_SESSION['error_message']);
} 
?> 

<?php if (!empty($cart_items)): ?>
    <form method="POST" action="">
            
          <!-- Button to update the cart -->
        <div class="mb-3"> 
            <!-- Button to update the quantities of products in the cart -->
            <button type="submit" name="update_cart" class="btn btn-warning"  data-translate="updateCart">Update Cart</button>
        </div>          

        <!-- Display the items in the cart in a table -->
        <table id="Data_Table_1" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th data-translate="product">Product</th>
                    <th data-translate="priceunit">Unit Price</th>
                    <th data-translate="quantity">Quantity</th>
                    <th data-translate="subtotal">Subtotal</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody> 
                <!-- Initialize the total cost of items in the cart -->
                <?php $total = 0; ?>

                <!-- Loop through each item in the cart -->
                <?php foreach ($cart_items as $item): ?>
                    <?php
                    // Calculate the subtotal for the current item based on price and quantity
                    // Calculate the subtotal for the current item
                    $subtotal = $item['price'] * $item['quantity'];

                    // Add the subtotal to the total cost
                    $total += $subtotal;
                    ?>
                    <tr>
                        <!-- Display the name of the product -->
                        <!-- Display the product name -->
                        <td><?php echo htmlspecialchars($item['name']); ?></td>

                        <!-- Display the unit price of the product, formatted with currency symbol and decimal places -->
                        <!-- Display the product price, formatted with currency symbol and decimal places -->
                        <td>€ <?php echo htmlspecialchars(number_format($item['price'], 2, ',', '.')); ?></td>

                        <!-- Display an input field to update the quantity of the product in the cart -->
                        <td>
                            <!-- Input field for quantity with current quantity as value, minimum 1, and maximum equal to product stock -->
                            <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control" required>
                        </td>

                        <!-- Display the subtotal for the current item, formatted with currency symbol and decimal places -->
                        <td>€ <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?></td>

                        <!-- Display a button to remove the item from the cart -->
                        <td>
                            <!-- Form to remove the item from the cart when the button is clicked -->
                            <form method="POST" action="" style="display:inline-block;">
                                <!-- Hidden input field to store the product ID of the item to be removed -->
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">

                                <!-- Button to trigger the removal of the item from the cart -->
                                <button type="submit" name="remove_from_cart" class="btn btn-danger" data-translate="remove">Remove</button>

                            <!-- Button to remove item -->
                            <form method="POST" action="" style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" name="remove_from_cart" class="btn btn-danger" data-translate="remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- Display the total cost of all items in the cart -->
                <tr>
                    <!-- Display the total cost of the items in the cart -->
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td colspan="2"><strong>€ <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </form>
    <!-- Button to proceed to finalize the order -->
    <div class="mt-3">
        <a href="finalize_order.php" class="btn btn-success"  data-translate="finalizeOrder">Finalize Order</a>
        <!-- Button to proceed to the order finalization page -->
    </div>
<?php else: ?>
    <!-- Display a message if the cart is empty -->
    <!-- Display a message if the cart is empty -->
    <p data-translate="emptyCart">Your cart is empty.</p>
<?php endif; ?>

<!-- Link to continue shopping -->
<div class="mt-4">
    <a href="order_products.php" class="btn btn-secondary"  data-translate="continueShopping"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
</div>
<?php include 'template.php'; ?>