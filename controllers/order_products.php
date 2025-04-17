--- a/order_products.php
+++ b/order_products.php

<?php
// Include utility functions for common operations
include 'utils.php';

// Include header for page setup
include 'header.php';
$page_title = 'Order Products';

// Check if the request method is POST and the 'add_to_cart' button is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Retrieve product ID from the form and ensure it's an integer
    $product_id = intval($_POST['product_id']);
    // Retrieve quantity from the form and ensure it's an integer
    $quantity = intval($_POST['quantity']);

    // Get product details by ID from the database
    $product = get_product_by_id($product_id);

    // Check if the product exists
    if (isset($product) && $product) {
        // Update the cart in the session
        // If the cart is not yet initialized, create an empty array.
        // Then, update the cart with the new product and quantity.
        $_SESSION['cart'] = update_cart($_SESSION['cart'] ?? [], $product, $quantity);
        // Display a success message to the user
        display_message('Product successfully added to cart!', 'success');
    } else {
        // Display an error message if the product was not found
        display_message('Product not found.', 'error');
    }

    // Redirect the user after adding to the cart.
    header('Location: order_products.php');
    exit();
}

// Get the list of products from the database
$products = get_products_with_categories();

// Include the template for the overall page layout
include 'template.php';
?>

<div class="row">
    <!-- Page title -->
    <h1 data-translate="orderProducts">Order Products</h1>
    <!-- Link to the shopping cart -->
    <div class="mt-3 mb-3">
        <a href="cart.php" class="btn btn-primary" data-translate="checkout"><i class="fas fa-shopping-cart"></i>Checkout</a>
    </div>

    <!-- Dropdown to filter products by category -->
    <div class="mb-4">
        <!-- Label for the category filter dropdown -->
        <label for="categoryFilter" class="form-label" data-translate="filterByCategory">Filter by Category:</label>
        <select id="categoryFilter" class="form-select">
            <!-- Default option to show all categories -->
            <option value="" data-translate="allCategories">All Categories</option>
            <?php
            // Populate the dropdown with categories from the database
            $categories = get_categories();
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    // Output each category as an option in the dropdown
                    echo '<option value="' . htmlspecialchars($category['name']) . '">' . htmlspecialchars($category['name']) . '</option>';
                }
            } else {
                // Display an error message if categories cannot be loaded
                echo '<option value="">Error loading categories</option>';
            }
            ?>
        </select>
    </div>

    <div class="table-responsive">
        <!-- Table to display the products -->
        <table id="Data_Table_0" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <!-- Table header for product category -->
                    <th data-translate="category">Category</th>
                    <!-- Table header for product name -->
                    <th data-translate="name">Name</th>
                    <!-- Table header for product reference -->
                    <th data-translate="reference">Reference</th>
                    <!-- Table header for product price -->
                    <th data-translate="price">Price</th>
                    <!-- Table header for product stock -->
                    <th data-translate="stock">Stock</th>
                    <!-- Table header for quantity input -->
                    <th data-translate="quantity">Quantity</th>
                    <!-- Table header for add to cart action -->
                    <th data-translate="action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <!-- Display the product category -->
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <!-- Display the product name -->
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <!-- Display the product reference -->
                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                        <!-- Display the product price with currency symbol -->
                        <td>€ <?php echo htmlspecialchars(number_format($product['price'], 2, ',', '.')); ?></td>
                        <!-- Display the product stock -->
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td>
                            <!-- Form to add the product to the cart -->
                            <form method="POST" action="" class="d-inline-block">
                                <!-- Hidden input to store the product ID -->
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <!-- Input field for the quantity to add, with validation -->
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                        </td>
                        <td>
                                <!-- Submit button to add the product to the cart -->
                                <button type="submit" name="add_to_cart" class="btn btn-primary" data-translate="add">Add to Cart</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr>
                        <!-- Display a message if no products are found -->
                        <td colspan="7" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Apply category filter when dropdown changes
    document.getElementById('categoryFilter').addEventListener('change', function() {
        var selectedCategory = this.value;
        var table = document.getElementById('Data_Table_0');
        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (var i = 0; i < rows.length; i++) {
            var categoryCell = rows[i].getElementsByTagName('td')[0];
            if (categoryCell) {
                var category = categoryCell.textContent || categoryCell.innerText;
                if (selectedCategory === '' || category === selectedCategory) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });
</script>

<?php
// Include the footer for closing tags and scripts
include 'footer.php';
?>

