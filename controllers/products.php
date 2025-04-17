<?php
include 'header.php';
$page_title = 'Products';
include 'utils.php';

$mysqli = connectToDatabase();

// Check if the user is logged in and is an admin
checkAdminAccess();

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add a product
    if (isset($_POST['add_product'])) {
        $result = handleAddProduct($mysqli, $_POST);
        if ($result) {
            header('Location: products.php');
            exit();
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Sanitize inputs
        $name = htmlspecialchars($_POST['name']);
        $reference = htmlspecialchars($_POST['reference']);
        $description = htmlspecialchars($_POST['description']);
        $price = floatval($_POST['price']);
        $pricevat = floatval($_POST['pricevat']);
        $stock = intval($_POST['stock']);
        $category_id = intval($_POST['category_id']);

        // Validate inputs (basic validation for demonstration)
        if (empty($name) || empty($reference) || $price <= 0 || $pricevat <= 0 || $stock < 0) {
            display_error("Invalid input values. Please check the form.");
        } else {
            $sql = "INSERT INTO products (`name`, `reference`, `description`, `price`, `pricevat`, `stock`, `category_id`)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssdidi", $name, $reference, $description, $price, $pricevat, $stock, $category_id);
                if ($stmt->execute()) {
                    header('Location: products.php');
                    exit();
                } else {
                    display_error("Error adding product: " . $stmt->error);
                }
                $stmt->close();
            } else {
                display_error("Error preparing statement: " . $mysqli->error);
            }
        }
    } elseif (isset($_POST['edit_product'])) {
        // Sanitize inputs
        $product_id = intval($_POST['product_id']);
        $name = htmlspecialchars($_POST['name']);
        $reference = htmlspecialchars($_POST['reference']);
        $description = htmlspecialchars($_POST['description']);
        $price = floatval($_POST['price']);
        $pricevat = floatval($_POST['pricevat']);
        $stock = intval($_POST['stock']);
        $category_id = intval($_POST['category_id']);

        // Validate inputs
        if ($product_id <= 0 || empty($name) || empty($reference) || $price <= 0 || $pricevat <= 0 || $stock < 0) {
            display_error("Invalid input values. Please check the form.");
        } else {
            $sql = "UPDATE products
                    SET `name` = ?, `reference` = ?, `description` = ?, `price` = ?, `pricevat` = ?, `stock` = ?, `category_id` = ?
                    WHERE `id` = ?";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssdidii", $name, $reference, $description, $price, $pricevat, $stock, $category_id, $product_id);
                if ($stmt->execute()) {
                    header('Location: products.php');
                    exit();
                } else {
                    display_error("Error updating product: " . $stmt->error);
                }
                $stmt->close();
            } else {
                display_error("Error preparing statement: " . $mysqli->error);
            }
        }
    }
        
       if (isset($_POST['delete_product'])) {
        $product_id = $mysqli->real_escape_string($_POST['product_id']);

        $sql = "DELETE FROM products WHERE id = '$product_id'";
        $stmt = $mysqli->prepare($sql);
        if($stmt){
             $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                header('Location: products.php');
                exit();
            } else {
                display_error("Error deleting product: " . $stmt->error);
            }
        } else {
            display_error("Error preparing statement: " . $mysqli->error);
        }
      }          
}
        

// Get the list of all products with their categories
$sql = 'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC';
$result_products = $mysqli->query($sql);

if ($result_products) {
    $products = $result_products->fetch_all(MYSQLI_ASSOC);
} else {
    display_error("Error retrieving products: " . $mysqli->error);
    $products = []; // Ensure $products is always defined
}

// Get the list of categories
$result_categories = $mysqli->query('SELECT * FROM categories');
if ($result_categories) {
    $categories = $result_categories->fetch_all(MYSQLI_ASSOC);
} else {
    display_error("Error retrieving categories: " . $mysqli->error);
    $categories = []; // Ensure $categories is always defined
}
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
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <button type="button" class="btn btn-danger" data-translate="delete" onclick="confirmDelete(<?php echo htmlspecialchars($product['id']); ?>)">Delete</button>
                            </form>
                            <a href="product_details.php?product_id=<?php echo htmlspecialchars($product['id']); ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>    
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
                     <div class="mb-3">
                        <label for="edit_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_reference" class="form-label" data-translate="reference">Reference</label>
                        <input type="text" class="form-control" id="edit_reference" name="reference" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label" data-translate="description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label" data-translate="price">Price</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_pricevat" class="form-label" data-translate="priceWvat">Price with VAT</label>
                        <input type="number" class="form-control" id="edit_pricevat" name="pricevat" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stock" class="form-label" data-translate="stock">Stock</label>
                        <input type="number" class="form-control" id="edit_stock" name="stock" required>
                    </div>
                     <div class="mb-3">
                        <label for="edit_category_id" class="form-label" data-translate="category">Category</label>
                         <select class="form-control" id="edit_category_id" name="category_id"></select>
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
    document.addEventListener('DOMContentLoaded', function() {
    var editCategorySelect = document.getElementById('edit_category_id');

    // Populate the category dropdown for the edit modal
    function populateCategories(categories) {
        editCategorySelect.innerHTML = ''; // Clear existing options
        categories.forEach(function(category) {
            var option = document.createElement('option');
            option.value = category.id;
            option.text = category.name;
            editCategorySelect.appendChild(option);
        });
    }

        // Fetch categories and populate the dropdown
    fetch('get_categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching categories:', data.error);
            } else {
                populateCategories(data);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    function populateEditModal(product) {
        document.getElementById('edit_product_id').value = product.id || '';
        document.getElementById('edit_name').value = product.name || ''; 
        document.getElementById('edit_reference').value = product.reference || ''; 
        document.getElementById('edit_description').value = product.description || ''; 
        document.getElementById('edit_price').value = product.price || ''; 
        document.getElementById('edit_pricevat').value = product.pricevat || ''; 
        document.getElementById('edit_stock').value = product.stock || ''; 
        document.getElementById('edit_category_id').value = product.category_id || ''; 
    }

    function confirmDelete(productId) {
        if (confirm('Are you sure you want to delete this product?')) {
            // If confirmed, submit the form with the product ID
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Current page

            let deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_product';
            deleteInput.value = '1';
            form.appendChild(deleteInput);

            let productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            form.appendChild(productIdInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php
include 'footer.php';
?>