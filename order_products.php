<?php
include 'header.php';
$page_title = 'Order Products';

// Inicializa o carrinho na sessão se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Processa a adição de produtos ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Verifica se o produto existe
    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Adiciona ou atualiza o produto no carrinho
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $_SESSION['success_message'] = 'Product successfully added to cart!';
    } else {
        $_SESSION['error_message'] = 'Product not found.';
    }

    // Redirecionar o usuário após a adição ao carrinho
    header('Location: order_products.php');
    exit(); // Importante para impedir qualquer outra saída
}

// Obtém a lista de produtos
$result_products = $mysqli->query('
    SELECT products.*, categories.name AS category_name 
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
');
$products = $result_products->fetch_all(MYSQLI_ASSOC);

include 'template.php';
?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

<div class="row">
    <h1 data-translate="orderProducts">Order Products</h1>
    <div class="mt-3 mb-3">
        <a href="cart.php" class="btn btn-primary" data-translate="checkout"><i class="fas fa-shopping-cart"></i>Checkout</a>
    </div>

    <!-- Dropdown para filtrar por categoria -->
    <div class="mb-4">
        <label for="categoryFilter" class="form-label" data-translate="filterByCategory">Filter by Category:</label>
        <select id="categoryFilter" class="form-select">
            <option value="" data-translate="allCategories">All Categories</option>
            <?php
            // Gerar as opções do dropdown com base nas categorias
            $result_categories = $mysqli->query('SELECT id, name FROM categories ORDER BY name ASC');
            while ($category = $result_categories->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($category['name']) . '">' . htmlspecialchars($category['name']) . '</option>';
            }
            ?>
        </select>
    </div>

    <div class="table-responsive">
        <table id="Data_Table_0" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="category">Category</th>
                    <th data-translate="name">Name</th>
                    <th data-translate="reference">Reference</th>
                    <th data-translate="price">Price</th>
                    <th data-translate="stock">Stock</th>
                    <th data-translate="quantity">Quantity</th>
                    <th data-translate="action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['reference']); ?></td>
                        <td>€ <?php echo htmlspecialchars(number_format($product['price'], 2, ',', '.')); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td>
                            <form method="POST" action="" class="d-inline-block">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                        </td>
                        <td>
                                <button type="submit" name="add_to_cart" class="btn btn-primary" data-translate="add">Add to Cart</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'footer.php'; ?>
