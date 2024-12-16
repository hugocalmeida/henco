<?php
include 'header.php';
$page_title = 'Product Details';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Create the 'products' table if it does not exist
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

// Create the 'product_images' table if it does not exist
$create_product_images_table_sql = "CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";
$mysqli->query($create_product_images_table_sql);

// Handle product details update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_product'])) {
        $product_id = intval($_POST['product_id']);
        $name = trim($_POST['name']);
        $reference = trim($_POST['reference']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $pricevat = trim($_POST['pricevat']);
        $stock = intval($_POST['stock']);
        $category_id = intval($_POST['category_id']);
        
        $stmt_edit = $mysqli->prepare('UPDATE products SET name = ?, reference = ?, description = ?, price = ?, pricevat = ?, stock = ?, category_id = ? WHERE id = ?');
        $stmt_edit->bind_param('ssssdiis', $name, $reference, $description, $price, $pricevat, $stock, $category_id, $product_id);
        $stmt_edit->execute();
        $stmt_edit->close();
    }

    // Handle product image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $product_id = intval($_POST['product_id']);
        $upload_dir = 'uploads/product_images/';
        $image_name = basename($_FILES['product_image']['name']);
        $image_path = $upload_dir . time() . '_' . $image_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path)) {
            $stmt_image = $mysqli->prepare('INSERT INTO product_images (product_id, image_path) VALUES (?, ?)');
            $stmt_image->bind_param('is', $product_id, $image_path);
            $stmt_image->execute();
            $stmt_image->close();
        }
    }
}

// Fetch product details
$product_id = intval($_GET['product_id']);
$stmt_product = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
$stmt_product->bind_param('i', $product_id);
$stmt_product->execute();
$result = $stmt_product->get_result();
$product = $result->fetch_assoc();
$stmt_product->close();

// Fetch product images
$stmt_images = $mysqli->prepare('SELECT * FROM product_images WHERE product_id = ?');
$stmt_images->bind_param('i', $product_id);
$stmt_images->execute();
$images_result = $stmt_images->get_result();
$product_images = $images_result->fetch_all(MYSQLI_ASSOC);
$stmt_images->close();

// get categories
$get_categories_sql = "SELECT id, name FROM categories ORDER BY name ASC";
$result = $mysqli->query($get_categories_sql);

// Verificar se há categorias e armazená-las em um array
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}


$mysqli->close();
include 'template.php';
?>
<div class="row">
<h1 data-translate="productDetails">Product Details</h1>       
    <div class="card shadow-sm p-4">

        <form method="POST" action="" enctype="multipart/form-data" class="row g-4">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">

            <!-- General Information -->
            <div class="col-lg-6">
                <div class="card p-3 border-0">

                    <div class="mb-3">
                        <label for="product_name" class="form-label" data-translate="name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_reference" class="form-label" data-translate="reference">Reference</label>
                        <input type="text" class="form-control" id="product_reference" name="reference" value="<?php echo htmlspecialchars($product['reference']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_description" class="form-label" data-translate="description">Description</label>
                        <textarea class="form-control" id="product_description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                                        <div class="mb-3">
                        <label for="product_category" class="form-label" data-translate="category">Product Category</label>
                        <select class="form-control" id="product_category" name="category_id" required>
                            <option value="" disabled selected data-translate="selectCategory">Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>    
                                  
                    <div class="mb-3">
                        <label for="product_price" class="form-label" data-translate="price">Base Price</label>
                        <input type="number" class="form-control" id="product_price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_pricevat" class="form-label" data-translate="priceWvat">Price with VAT</label>
                        <input type="number" class="form-control" id="product_pricevat" name="pricevat" value="<?php echo htmlspecialchars($product['pricevat']); ?>" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_stock" class="form-label" data-translate="stock">Stock</label>
                        <input type="number" class="form-control" id="product_stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    </div>

                        
                </div>
            </div>

            <!-- Upload Image -->
            <div class="col-lg-6">
                <div class="card p-3 border-0">
                    <h5 class="mb-3" data-translate="uploadImage">Upload Image</h5>
                    <div class="mb-3">
                        <input type="file" class="form-control" id="product_image" name="product_image">
                    </div>
                    <div class="row">
                        <?php foreach ($product_images as $image): ?>
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="img-fluid img-thumbnail" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>                


            <!-- Save Button -->
            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary" name="edit_product" data-translate="saveChanges">
                    <i class="fa fa-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>


<?php include 'footer.php'; ?>
