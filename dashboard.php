<?php
include 'header.php';
$page_title = 'Home';
include 'template.php';


// Obtém dados para o dashboard e encomendas
// Get user data
$user_id = $_SESSION['user_id'] ?? null;
$is_admin = ($role_id == 1);

// Número total de encomendas e faturção total de acordo com o papel do usuário
if ($is_admin) {
    $result_total_orders = $mysqli->query('SELECT COUNT(*) AS total_orders FROM orders');
    $total_orders = $result_total_orders ? $result_total_orders->fetch_assoc()['total_orders'] : 0;

    $result_total_revenue = $mysqli->query('SELECT SUM(total_amount) AS total_revenue FROM orders');
    $total_revenue = $result_total_revenue ? $result_total_revenue->fetch_assoc()['total_revenue'] : 0;

    $result_orders_by_month = $mysqli->query('
        SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders
        FROM orders
        GROUP BY month
        ORDER BY month ASC
    ');
} else {
    $stmt_total_orders = $mysqli->prepare('SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?');
    $stmt_total_orders->bind_param('i', $user_id);
    $stmt_total_orders->execute();
    $result_total_orders = $stmt_total_orders->get_result();
    $total_orders = $result_total_orders ? $result_total_orders->fetch_assoc()['total_orders'] : 0;

    $stmt_total_revenue = $mysqli->prepare('SELECT SUM(total_amount) AS total_revenue FROM orders WHERE user_id = ?');
    $stmt_total_revenue->bind_param('i', $user_id);
    $stmt_total_revenue->execute();
    $result_total_revenue = $stmt_total_revenue->get_result();
    $total_revenue = $result_total_revenue ? $result_total_revenue->fetch_assoc()['total_revenue'] : 0;

    $stmt_orders_by_month = $mysqli->prepare('
        SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders
        FROM orders
        WHERE user_id = ?
        GROUP BY month
        ORDER BY month ASC
    ');
    $stmt_orders_by_month->bind_param('i', $user_id);
    $stmt_orders_by_month->execute();
    $result_orders_by_month = $stmt_orders_by_month->get_result();
}

// Produto mais vendido (de acordo com o papel do usuário)
if ($is_admin) {
    $result_best_selling = $mysqli->query('
        SELECT p.name, SUM(oi.quantity) AS total_quantity 
        FROM order_items oi 
        INNER JOIN products p ON oi.product_id = p.id 
        GROUP BY oi.product_id 
        ORDER BY total_quantity DESC 
        LIMIT 1
    ');
} else {
    $stmt_best_selling = $mysqli->prepare('
        SELECT p.name, SUM(oi.quantity) AS total_quantity 
        FROM order_items oi 
        INNER JOIN products p ON oi.product_id = p.id 
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ?
        GROUP BY oi.product_id 
        ORDER BY total_quantity DESC 
        LIMIT 1
    ');
    $stmt_best_selling->bind_param('i', $user_id);
    $stmt_best_selling->execute();
    $result_best_selling = $stmt_best_selling->get_result();
}
$best_selling_product = $result_best_selling ? $result_best_selling->fetch_assoc() : null;

// Usuário com mais encomendas (somente se for admin)
$top_user_orders = null;
if ($is_admin) {
    $result_top_user_orders = $mysqli->query('
        SELECT u.username, COUNT(o.id) AS total_orders
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id
        GROUP BY o.user_id
        ORDER BY total_orders DESC
        LIMIT 1
    ');
    $top_user_orders = $result_top_user_orders ? $result_top_user_orders->fetch_assoc() : null;
}

// Usuário com maior faturação (somente se for admin)
$top_user_revenue = null;
if ($is_admin) {
    $result_top_user_revenue = $mysqli->query('
        SELECT u.username, SUM(o.total_amount) AS total_revenue
        FROM orders o
        INNER JOIN users u ON o.user_id = u.user_id
        GROUP BY o.user_id
        ORDER BY total_revenue DESC
        LIMIT 1
    ');
    $top_user_revenue = $result_top_user_revenue ? $result_top_user_revenue->fetch_assoc() : null;
}

// Cliente com maior faturação (somente se for admin)
$top_client_revenue = null;
if ($is_admin) {
    $result_top_client_revenue = $mysqli->query('
        SELECT c.name, SUM(o.total_amount) AS total_revenue
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        GROUP BY o.client_id
        ORDER BY total_revenue DESC
        LIMIT 1
    ');
    $top_client_revenue = $result_top_client_revenue ? $result_top_client_revenue->fetch_assoc() : null;
}

// Encomendas por mês (para gráfico)
$orders_by_month = [];
if ($result_orders_by_month) {
    while ($row = $result_orders_by_month->fetch_assoc()) {
        $orders_by_month[] = $row;
    }
}

// Prepara os dados para o gráfico de encomendas por mês
$orders_map = [];
foreach ($orders_by_month as $data) {
    $orders_map[$data['month']] = (int)$data['total_orders'];
}

// Inicializa os arrays que serão usados no gráfico
$months = [];
$orders = [];

// Obtém a data atual
$currentDate = new DateTime();

// Loop para os últimos 6 meses
for ($i = 5; $i >= 0; $i--) {
    $date = (clone $currentDate)->modify("-$i month");
    $month_key = $date->format('Y-m');

    $formatter = new IntlDateFormatter('pt_PT', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy');
    $month_name = $formatter->format($date);

    $months[] = ucfirst($month_name);

    $orders[] = $orders_map[$month_key] ?? 0;
}


// Consulta para obter os top 5 vendedores com mais vendas
$stmt_top_sellers = $mysqli->prepare('
    SELECT u.username, COUNT(o.id) AS total_sales
    FROM users u
    INNER JOIN orders o ON u.user_id = o.user_id
    GROUP BY u.username
    ORDER BY total_sales DESC
    LIMIT 5
');
$stmt_top_sellers->execute();
$result_top_sellers = $stmt_top_sellers->get_result();
$top_sellers = $result_top_sellers->fetch_all(MYSQLI_ASSOC);
$stmt_top_sellers->close();

// Inicializa arrays para o gráfico
$seller_names = [];
$sales_counts = [];

foreach ($top_sellers as $seller) {
    $seller_names[] = $seller['username'];
    $sales_counts[] = $seller['total_sales'];
}


$mysqli->close();
?>          
<h1 data-translate="dashboard">Dashboard</h1>  
<div class="row">
    <!-- Card 1: Total Orders -->
    <?php if ($total_orders !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-1 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="totalOrders">Total Orders</h3>    
                    <div class="d-inline-block">
                        <h2 class="text-white"><?php echo number_format($total_orders); ?></h2>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-shopping-cart"></i></span>        
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 2: Total Revenue -->
    <?php if ($total_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-2 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="totalRevenue">Total Revenue</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white" data-translate="currency">€</h2> <?php echo number_format($total_revenue, 2, ',', '.'); ?>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-euro-sign"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 3: Best Selling Product -->
    <?php if ($best_selling_product !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-3 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="bestSellingProduct">Best Selling Product</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white"><?php echo htmlspecialchars($best_selling_product['name']); ?></h4>
                        <p class="card-text-sub text-white"><span data-translate="quantitySold">Quantity Sold: </span> <?php echo number_format($best_selling_product['total_quantity']); ?></p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-box"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 4: User with Most Orders (Admin Only) -->
    <?php if ($is_admin && $top_user_orders !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-4 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topUserByOrders">Top User by Orders</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white"><?php echo htmlspecialchars($top_user_orders['username']); ?></h4>
                        <p class="card-text-sub text-white"><span data-translate="totalOrders">Total Orders</span>: <?php echo number_format($top_user_orders['total_orders']); ?></p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-user"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 5: User with Highest Revenue (Admin Only) -->
    <?php if ($is_admin && $top_user_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-5 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topUserByRevenue">Top User by Revenue</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white"><?php echo htmlspecialchars($top_user_revenue['username']); ?></h4>
                        <p class="card-text-sub text-white"><span data-translate="revenue">Revenue</span>: € <?php echo number_format($top_user_revenue['total_revenue'], 2, ',', '.'); ?></p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-money-bill"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Card 6: Client with Highest Revenue (Admin Only) -->
    <?php if ($is_admin && $top_client_revenue !== null): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card gradient-8 h-100">
                <div class="card-body">
                    <h3 class="card-title text-white" data-translate="topClientByRevenue">Top Client by Revenue</h3>
                    <div class="d-inline-block">
                        <h4 class="text-white"><?php echo htmlspecialchars($top_client_revenue['name']); ?></h4>
                        <p class="card-text-sub text-white"><span data-translate="revenue">Revenue</span>: € <?php echo number_format($top_client_revenue['total_revenue'], 2, ',', '.'); ?></p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-hand-holding-usd"></i></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($orders)): ?>
    <div class="row">
        <!-- Orders per Month Chart -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title" data-translate="ordersPerMonth">Orders per Month</h5>
                    <div class="chart-container mt-4">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 5 Sellers Chart -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" data-translate="topSellers">Top 5 Sellers</h5>
                    <div class="chart-container mt-4">
                        <canvas id="topSellersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 document.addEventListener('DOMContentLoaded', async function () {
    const locale = document.body.getAttribute('data-locale') || 'en-EN';

    // Certifique-se de que as traduções estão carregadas
    await loadTranslations(locale);

    // Gráfico de Encomendas por Mês
    const ctxOrders = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ctxOrders, {
        type: 'line',
        data: {
            labels: [
                getTranslation('january'), getTranslation('february'), getTranslation('march'),
                getTranslation('april'), getTranslation('may'), getTranslation('june')
            ],
            datasets: [{
                label: getTranslation('ordersPerMonth'),
                data: [0, 0, 0, 0, 0, 18],
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: getTranslation('ordersPerMonth')
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return getTranslation('quantity') + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: getTranslation('months')
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: getTranslation('quantitySold')
                    }
                }
            }
        }
    });

    // Gráfico de Melhores Vendedores
    const ctxTopSellers = document.getElementById('topSellersChart').getContext('2d');
    const topSellersChart = new Chart(ctxTopSellers, {
        type: 'bar',
        data: {
            labels: ["Hugo Almeida", "Higo"],
            datasets: [{
                label: getTranslation('totalSales'),
                data: [17, 1],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: getTranslation('topSellers')
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return getTranslation('sales') + ': ' + context.raw;
                        }
                    }
                }
            },
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
