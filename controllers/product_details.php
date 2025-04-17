<?php
include_once 'header.php';
include_once 'utils.php';
$page_title = translate('productDetails', $translations);

// Function to display error messages
function display_error($message) {
    echo "<div class='alert alert-danger' role='alert'>$message</div>";
}

// Check if user is logged in and if the user is admin
if (!isAdmin()) {
    header('Location: home.php');
    exit();
}

$mysqli = connectToDatabase();

// Handle product details update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_product'])) {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $reference = isset($_POST['reference']) ? trim($_POST['reference']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $price = isset($_POST['price']) ? floatval(trim($_POST['price'])) : 0.00;
        $pricevat = isset($_POST['pricevat']) ? floatval(trim($_POST['pricevat'])) : 0.00;
        $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        if ($product_id > 0 && !empty($name) && !empty($reference) && $price > 0 && $pricevat > 0 && $stock >= 0) {
            if (updateProduct($mysqli, $product_id, $name, $reference, $description, $price, $pricevat, $stock, $category_id)) {
                $_SESSION['success_message'] = translate('productUpdatedSuccessfully', $translations);
            } else {
                $_SESSION['error_message'] = translate('failedToUpdateProduct', $translations);
            }
        } else {
            $_SESSION['error_message'] = translate('invalidInputValues', $translations);
        }

        // Redirect after processing to avoid form resubmission
        header("Location: product_details.php?product_id=$product_id");
        exit();
    }

    // Handle product image upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if ($product_id > 0) {
            $upload_dir = 'uploads/product_images/';
            $image_name = basename($_FILES['product_image']['name']);
            $image_path = $upload_dir . time() . '_' . $image_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path) && addProductImage($mysqli, $product_id, $image_path)) {
                $_SESSION['success_message'] = translate('imageUploadedSuccessfully', $translations);
            } else {
                $_SESSION['error_message'] = translate('failedToUploadImage', $translations);
            }
        } else {
            $_SESSION['error_message'] = translate('invalidProductId', $translations);
        }
    }

    // Redirect after processing to avoid form resubmission
    if (isset($product_id)) {
        header("Location: product_details.php?product_id=$product_id");
        exit();
    } else {
        header("Location: products.php"); // Or handle as appropriate
        exit();
    }
}

// Fetch product details
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id > 0) {
    $product = getProductById($mysqli, $product_id);
    if (!$product) {
        $_SESSION['error_message'] = translate('productNotFound', $translations);
        header('Location: products.php');
        exit();
    }
    // Fetch product images
    $product_images = getProductImages($mysqli, $product_id);
} else {
    $_SESSION['error_message'] = translate('invalidProductId', $translations);
    header('Location: products.php');
    exit();
}

$categories = getAllCategories($mysqli);

$mysqli->close();
include 'template.php';
?>
<div class="row">
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
        unset($_SESSION['success_message']); // Clear the message after displaying it
    }
    if (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
        unset($_SESSION['error_message']); // Clear the message after displaying it
    }
    ?>
    <h1 data-translate="productDetails">Product Details</h1>
    <div class="card shadow-sm p-4">

        <form method="POST" action="" enctype="multipart/form-data" class="row g-4">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">

            <!-- General Information -->
            <div class="col-lg-6">
                <div class="card p-3 border-0">

                    <div class="mb-3">
                        <label for="product_name" class="form-label"
                               data-translate="name">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="name"
                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_reference" class="form-label"
                               data-translate="reference">Reference</label>
                        <input type="text" class="form-control" id="product_reference" name="reference"
                               value="<?php echo htmlspecialchars($product['reference']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_description" class="form-label" data-translate="description">Description</label>
                        <textarea class="form-control" id="product_description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                                        <div class="mb-3">
                        <label for="product_category" class="form-label" data-translate="category">Product Category</label>
                        <select class="form-control" id="product_category" name="category_id">
                            <option value="" disabled selected
                                    data-translate="selectCategory">Select a Category
                            </option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?> >
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="product_price" class="form-label"
                               data-translate="price">Base Price</label>
                        <input type="number" class="form-control" id="product_price" name="price"
                               value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_pricevat" class="form-label" data-translate="priceWvat">Price
                            with VAT</label>
                        <input type="number" class="form-control" id="product_pricevat" name="pricevat"
                               value="<?php echo htmlspecialchars($product['pricevat']); ?>" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="product_stock" class="form-label" data-translate="stock">Stock</label>
                        <input type="number" class="form-control" id="product_stock" name="stock"
                               value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                    </div>


                </div>
            </div>

            <!-- Upload Image -- >
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
