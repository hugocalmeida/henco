<?php
include 'header.php';

// Iniciar sessão e verificar se o usuário está autenticado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'You need to be logged in to view the order details.';
    header('Location: index.php'); // Redireciona para a página de login
    exit();
}

// Verifica se o parâmetro 'order_id' foi passado corretamente
if (!isset($_GET['order_id'])) {
    $_SESSION['error_message'] = 'Invalid order.';
    header('Location: order_history.php');
    exit();
}

$order_id = intval($_GET['order_id']);

// Conexão com o banco de dados
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die('Database connection error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Obtém o ID do usuário e o role_id
$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// Verifica se a encomenda pertence ao usuário ou se o usuário é administrador
if ($role_id == 1) {
    // O usuário é administrador - pode ver qualquer encomenda
    $stmt_order = $mysqli->prepare('
        SELECT o.*, c.name AS client_name
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        WHERE o.id = ?
    ');
    $stmt_order->bind_param('i', $order_id);
} else {
    // O usuário não é administrador - pode ver apenas as suas próprias encomendas
    $stmt_order = $mysqli->prepare('
        SELECT o.*, c.name AS client_name
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        WHERE o.id = ? AND o.user_id = ?
    ');
    $stmt_order->bind_param('ii', $order_id, $user_id);
}

$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();
$stmt_order->close();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found or you do not have permission to view it.';
    header('Location: order_history.php');
    exit();
}

// Obtém os itens da encomenda
$stmt_items = $mysqli->prepare('
    SELECT oi.*, p.name 
    FROM order_items oi 
    INNER JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
');
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

$mysqli->close();
include 'template.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>

            <p><strong>Client Name:</strong> <?php echo htmlspecialchars($order['client_name']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></p>
            <p><strong>Total Amount:</strong> &euro; <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></p>

            <h4 class="mt-4">Order Items</h4>
            <div class="table-responsive">
                <table id="Data_Table_4" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <?php $subtotal = $item['price'] * $item['quantity']; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>&euro; <?php echo htmlspecialchars(number_format($item['price'], 2, ',', '.')); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td>&euro; <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Link to go back to the order history -->
            <div class="mt-4">
                <a href="order_history.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Orders
                </a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
