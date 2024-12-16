<?php
include 'header.php';
$page_title = 'Client Details'; // Página usa o título original, mas será traduzido via `data-translate`

// Verificar sessão e permissões do administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Verificar se o parâmetro 'client_id' foi passado
if (!isset($_GET['client_id'])) {
    $_SESSION['error_message'] = 'Invalid client.';
    header('Location: clients.php');
    exit();
}

$client_id = intval($_GET['client_id']);

// Buscar informações do cliente
$stmt_client = $mysqli->prepare('SELECT * FROM clients WHERE id = ?');
$stmt_client->bind_param('i', $client_id);
$stmt_client->execute();
$result_client = $stmt_client->get_result();
$client = $result_client->fetch_assoc();
$stmt_client->close();

if (!$client) {
    $_SESSION['error_message'] = 'Client not found.';
    header('Location: clients.php');
    exit();
}

// Buscar encomendas do cliente
$stmt_orders = $mysqli->prepare('SELECT * FROM orders WHERE client_id = ? ORDER BY created_at DESC');
$stmt_orders->bind_param('i', $client_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

// Preparar dados para o gráfico de encomendas por mês
$orders_by_month = [];
foreach ($orders as $order) {
    $month = date('Y-m', strtotime($order['created_at']));
    if (!isset($orders_by_month[$month])) {
        $orders_by_month[$month] = 0;
    }
    $orders_by_month[$month]++;
}

// Inicializar arrays para o gráfico
$months = [];
$orders_count = [];
$currentDate = new DateTime();

for ($i = 5; $i >= 0; $i--) {
    $date = (clone $currentDate)->modify("-$i month");
    $month_key = $date->format('Y-m');
    
    // Usar o formato do mês curto em inglês (padrão para traduções consistentes)
    $month_name = $date->format('F'); // Ex: January, February, etc.

    $months[] = strtolower($month_name); // Garantir consistência nas traduções
    $orders_count[] = $orders_by_month[$month_key] ?? 0;
}

$mysqli->close();
include 'template.php';
?>

<div class="row">
    <h1 data-translate="client">Client</h1>

    <div class="row g-4 mt-4">
        <!-- Informações do cliente -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">  
                    <p><strong data-translate="email">Email</strong> <?php echo htmlspecialchars($client['email']); ?></p>
                    <p><strong data-translate="phone">Phone</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
                    <p><strong data-translate="city">City</strong> <?php echo htmlspecialchars($client['city']); ?></p>
                    <p><strong data-translate="saddress">State</strong> <?php echo htmlspecialchars($client['state']); ?></p>
                </div>
            </div>
        </div>

        <!-- Gráfico de Encomendas por Mês -->
        <div class="col-md-12 col-lg-8">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" data-translate="ordersPerMonth">Orders per Month</h5>
                    <div class="chart-container mt-4">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Encomendas do Cliente -->
    <div class="row mt-4">
        <h2 class="mt-3" data-translate="clientOrders">Client Orders</h2>
        <div class="table-responsive">
            <table id="Data_Table_8" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                <thead>
                    <tr>
                        <th data-translate="orderId">Order ID</th>
                        <th data-translate="orderDate">Order Date</th>
                        <th data-translate="totalAmount">Total Amount</th>
                        <th data-translate="detail">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td>
                            <td>&euro; <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td>
                            <td><a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="4" class="text-center" data-translate="noOrdersFound">No orders found for this client.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Link para voltar à lista de clientes -->
    <div class="mt-4">
        <a href="clients.php" class="btn btn-secondary" data-translate="back">
            <i class="fas fa-arrow-left"></i><span>Back</span>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', async function () {
    // Obter o idioma configurado dinamicamente
    const locale = document.body.getAttribute('data-locale') || 'en-EN';

    // Carregar traduções com base no idioma
    await loadTranslations(locale);

    // Mapear os meses para os atributos data-translate
    const months = <?php echo json_encode($months); ?>.map(month => getTranslation(month.toLowerCase()));

    // Carregar os dados do gráfico
    const ordersCount = <?php echo json_encode($orders_count); ?>;

    // Inicializar o gráfico
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months, // Meses traduzidos dinamicamente
            datasets: [{
                label: getTranslation('ordersPerMonth'), // Título do gráfico traduzido
                data: ordersCount,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

</script>
<?php include 'footer.php'; ?>
