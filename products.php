<?php
include 'header.php';
$page_title = 'Products';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Create 'categories' and 'products' tables if they don't exist
$create_categories_table_sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
)";
$mysqli->query($create_categories_table_sql);

$create_products_table_sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    reference VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    pricevat DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
)";
$mysqli->query($create_products_table_sql);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $mysqli->real_escape_string($_POST['name']);
        $reference = $mysqli->real_escape_string($_POST['reference']);
        $description = $mysqli->real_escape_string($_POST['description']);
        $price = $mysqli->real_escape_string($_POST['price']);
        $pricevat = $mysqli->real_escape_string($_POST['pricevat']);
        $stock = $mysqli->real_escape_string($_POST['stock']);
        $category_id = $mysqli->real_escape_string($_POST['category_id']);

        $sql = "INSERT INTO products (name, reference, description, price, pricevat, stock, category_id)
                VALUES ('$name', '$reference', '$description', '$price', '$pricevat', '$stock', '$category_id')";
        if (!$mysqli->query($sql)) {
            echo "Error adding product: " . $mysqli->error;
        } else {
            header('Location: products.php');
            exit();
        }
    }

    if (isset($_POST['edit_product'])) {
        $product_id = $mysqli->real_escape_string($_POST['product_id']);
        $name = $mysqli->real_escape_string($_POST['name']);
        $reference = $mysqli->real_escape_string($_POST['reference']);
        $description = $mysqli->real_escape_string($_POST['description']);
        $price = $mysqli->real_escape_string($_POST['price']);
        $pricevat = $mysqli->real_escape_string($_POST['pricevat']);
        $stock = $mysqli->real_escape_string($_POST['stock']);
        $category_id = $mysqli->real_escape_string($_POST['category_id']);

        $sql = "UPDATE products
                SET name = '$name', reference = '$reference', description = '$description',
                    price = '$price', pricevat = '$pricevat', stock = '$stock', category_id = '$category_id'
                WHERE id = '$product_id'";
        if (!$mysqli->query($sql)) {
            echo "Error updating product: " . $mysqli->error;
        } else {
            header('Location: products.php');
            exit();
        }
    }
        
       if (isset($_POST['delete_product'])) {
        $product_id = $mysqli->real_escape_string($_POST['product_id']);

        $sql = "DELETE FROM products WHERE id = '$product_id'";
        if (!$mysqli->query($sql)) {
            echo "Error deleting product: " . $mysqli->error;
        } else {
            header('Location: products.php');
            exit();
        } 
      }          
}
        

// Get the list of all products with their categories
$sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC';
$result_products = $mysqli->query($sql);
$products = $result_products->fetch_all(MYSQLI_ASSOC);

// Get the list of categories
$result_categories = $mysqli->query('SELECT * FROM categories');
$categories = $result_categories->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 data-translate="products">Products</h1>

    <!-- Button to open the add product modal -->
    <button class="btn btn-primary col-1 mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal" data-translate="add">
        <i class="fa fa-plus-circle fa-6" aria-hidden="true"></i> Add
    </button>

    <!-- List of products -->
    <div class="table-responsive">
        <table id="Data_Table_5" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="reference">Reference</th>
                    <th data-translate="description">Description</th>
                    <th data-translate="price">Price</th>
                    <th data-translate="priceWvat">Price with VAT</th>
                    <th data-translate="stock">Stock</th>
                    <th data-translate="category">Category</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>&euro; <?php echo htmlspecialchars(number_format($product['price'], 2, ',', '.')); ?></td>
                        <td>&euro; <?php echo htmlspecialchars(number_format($product['pricevat'], 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>
                            <!-- Button to open the edit modal -->
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal" data-translate="edit" onclick='populateEditModal(<?php echo json_encode($product); ?>)'>Edit</button>
                            <!-- Form to delete product -->
                            <form method="POST" action="" class="d-inline-block">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-danger" data-translate="delete" data-confirm="confirmDelete">Delete</button>
                            </form>
                            <a href="product_details.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>    
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal fade" tabindex="-1" aria-labelledby="addProductModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="add_product" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel" data-translate="add">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for adding a product -->
                    <div class="mb-3">
                        <label for="add_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_reference" class="form-label" data-translate="reference">Reference</label>
                        <input type="text" class="form-control" id="add_reference" name="reference" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label" data-translate="description">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add_price" class="form-label" data-translate="price">Price</label>
                        <input type="number" class="form-control" id="add_price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_pricevat" class="form-label" data-translate="priceWvat">Price with VAT</label>
                        <input type="number" class="form-control" id="add_pricevat" name="pricevat" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_stock" class="form-label" data-translate="stock">Stock</label>
                        <input type="number" class="form-control" id="add_stock" name="stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_category_id" class="form-label" data-translate="category">Category</label>
                        <select class="form-control" id="add_category_id" name="category_id" required>
                            <option value="" disabled selected data-translate="selectCategory">Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="submit" class="btn btn-primary" data-translate="add">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Edit Product Modal -->
<div id="editProductModal" class="modal fade" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="edit_product" value="1">
                <input type="hidden" id="edit_product_id" name="product_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel" data-translate="edit">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for editing a product -->
                    <input type="text" class="form-control mb-3" id="edit_name" name="name" placeholder="Name" required>
                    <input type="text" class="form-control mb-3" id="edit_reference" name="reference" placeholder="Reference" required>
                    <textarea class="form-control mb-3" id="edit_description" name="description" rows="3" placeholder="Description"></textarea>
                    <input type="number" class="form-control mb-3" id="edit_price" name="price" placeholder="Price" step="0.01" required>
                    <input type="number" class="form-control mb-3" id="edit_pricevat" name="pricevat" placeholder="Price with VAT" step="0.01" required>
                    <input type="number" class="form-control mb-3" id="edit_stock" name="stock" placeholder="Stock" required>
                    <select class="form-control mb-3" id="edit_category_id" name="category_id" required>
                        <option value="" disabled selected>Select a Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function populateEditModal(product) {
        document.getElementById('edit_product_id').value = product.id;
        document.getElementById('edit_name').value = product.name || '';
        document.getElementById('edit_reference').value = product.reference || '';
        document.getElementById('edit_description').value = product.description || '';
        document.getElementById('edit_price').value = product.price || '';
        document.getElementById('edit_pricevat').value = product.pricevat || '';
        document.getElementById('edit_stock').value = product.stock || '';
        document.getElementById('edit_category_id').value = product.category_id || '';
    }
</script>

<?php include 'footer.php'; ?>
