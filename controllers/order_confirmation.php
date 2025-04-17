<?php
include 'header.php';
$page_title = 'Order Confirmation';
/**
 * Handles removing a product from the cart.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = intval($_POST['product_id']);
    unset($_SESSION['cart'][$product_id]);
    $_SESSION['success_message'] = 'Produto removido do carrinho com sucesso!';
    header('Location: cart.php');
    exit();
}
/**
 * Handles updating the quantities of products in the cart.
 */
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    $_SESSION['success_message'] = 'Carrinho atualizado com sucesso!';
    header('Location: cart.php');
    exit();
}
/**
 * Retrieves products from the cart to display.
 */
}
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id IN (' . $placeholders . ')');
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($product = $result->fetch_assoc()) {
        $product_id = $product['id'];
        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'quantity' => $_SESSION['cart'][$product_id]['quantity']
        ];
    }
}
/**
 * Closes the database connection and includes the template.
 */
include 'template.php';
?>

 <div class="row">
    <h1 data-translate="shoppingCart">Shopping Cart</h1>

    <!-- Display success message if any -->
    <?php if (isset($_SESSION['success_message'])): ?>

        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <!-- Display error message if any -->
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (!empty($cart_items)): ?>
        <form method="POST" action="">
            <table id="cartTable" class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                    <th data-translate="product">Product</th>
                    <th data-translate="priceunit">Unit Price</th>
                    <th data-translate="quantity">Quantity</th>
                    <th data-translate="subtotal">Subtotal</th>
                    <th data-translate="actions">Actions</th>
                    </tr>
                </thead>
                <!-- Table body to display cart items -->
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <?php
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>€ <?php echo htmlspecialchars(number_format($item['price'], 2, ',', '.')); ?></td>
                            <!-- Input field for updating product quantity -->
                            <td>
                                <input type="number" name="quantities[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control" required>
                            </td>
                            <td>€ <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline-block;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_from_cart" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this product from the cart?');">Remove</button>
                                </form>
                                <!-- Button to remove product from cart -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>€ <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></strong></td>
                    </tr>
                </tbody>
            </table>
            <!-- Buttons to update cart and finalize order -->
            <div class="d-flex justify-content-end mt-4">
                <!-- Button to update cart quantities -->
                <button type="submit" name="update_cart" class="btn btn-primary me-2">Update Cart</button>
                <a href="finalize_order.php" class="btn btn-success">Finalize Order</a>
            </div>
        </form>
    <?php else: ?>
        <p data-translate="emptyCart">Your cart is empty.</p>
    <?php endif; ?>

    <!-- Link to continue shopping -->
    <!-- Link to continue shopping -->
    <!-- Link to continue shopping -->
    <div class="mt-4">
        <a href="order_products.php" class="btn btn-secondary" data-translate="continueShopping"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
    </div>
</div>
<?php include 'footer.php'; ?>