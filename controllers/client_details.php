<?php
// Consider grouping functions like `isUserLoggedIn`, `isAdmin`, `displayErrorMessage`, 
// `getClientDetails`, `getOrdersByClientId`, `prepareOrdersChartData` and `getTranslation` 
// into a dedicated file or class for better organization and maintainability.
include 'header.php'; // Include the header file
$page_title = 'Client Details'; // Set the page title, which will be translated

// Check if user is logged in and has admin role
if (!isUserLoggedIn() || !isAdmin()) {
    header('Location: home.php'); // Redirect to home page if not logged in or not admin
    exit();
}

// Check if client ID is provided
if (empty($_GET['client_id'])) {
    displayErrorMessage('Invalid client.'); // Display error message
    header('Location: clients.php'); // Redirect to clients page
    exit();
}

$client_id = intval($_GET['client_id']); // Get and validate client ID

// Fetch client details
$client = getClientDetails($mysqli, $client_id);
if (!$client) {
    displayErrorMessage('Client not found.'); // Display error message if client not found
    header('Location: clients.php'); // Redirect to clients page
    exit();
}

// Fetch orders for the client
$orders = getOrdersByClientId($mysqli, $client_id);
// Prepare chart data for orders per month
$chart_data = prepareOrdersChartData($orders);
include 'template.php'; // Include the template file
?>

<div class="row">
    <h1 data-translate="client">Client</h1> <!-- Display client heading -->

    <div class="row g-4 mt-4">
        <!-- Client Information -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">  
                    <h5 class="card-title"><?php echo htmlspecialchars($client['name']); ?></h5>
                    <p><strong data-translate="email">Email:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
                    <p><strong data-translate="phone">Phone:</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
                    <p><strong data-translate="address">Address:</strong> <?php echo htmlspecialchars($client['address']); ?></p>
                    <p><strong data-translate="city">City:</strong> <?php echo htmlspecialchars($client['city']); ?></p>
                    <p><strong data-translate="state">State:</strong> <?php echo htmlspecialchars($client['state']); ?></p>
                    <p><strong data-translate="zip">ZIP:</strong> <?php echo htmlspecialchars($client['zip']); ?></p>
                </div>
            </div>
        </div>

        <!-- Orders per Month Chart -->
        <div class="col-md-12 col-lg-8">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" data-translate="ordersPerMonth">Orders per Month</h5>
                    <div class="chart-container mt-4">
                        <canvas id="ordersChart"></canvas> <!-- Canvas for the chart -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Orders List -->
    <div class="row mt-4">
        <h2 class="mt-3" data-translate="clientOrders">Client Orders</h2> <!-- Display client orders heading -->
        <div class="table-responsive">
            <table id="Data_Table_8" class="table table-striped table-bordered zero-configuration dataTable table-hover">
                <thead>
                    <tr>
                        <th data-translate="orderId">Order ID</th> <!-- Column for order ID -->
                        <th data-translate="orderDate">Order Date</th> <!-- Column for order date -->
                        <th data-translate="totalAmount">Total Amount</th> <!-- Column for total amount -->
                        <th data-translate="detail">Detail</th> <!-- Column for details button -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td> <!-- Display order ID -->
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?></td> <!-- Display order date -->
                            <td>€ <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?></td> <!-- Display total amount -->
                            <td><a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a></td> <!-- Link to order details -->
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="4" class="text-center" data-translate="noOrdersFound">No orders found for this client.</td> <!-- Message for no orders -->
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Link to go back to clients list -->
    <div class="mt-4">
        <a href="clients.php" class="btn btn-secondary" data-translate="back"> <!-- Back button -->
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
<script>
 document.addEventListener('DOMContentLoaded', async function () {
    // Get the configured language dynamically
    const locale = document.body.getAttribute('data-locale') || 'en-EN'; 

    // Load translations based on the language
    await loadTranslations(locale); 

    // Map months to data-translate attributes
    const months = <?php echo json_encode($chart_data['months']); ?>.map(month => getTranslation(month.toLowerCase()));

    // Load chart data
    const ordersCount = <?php echo json_encode($chart_data['orders_count']); ?>;

    // Initialize the chart
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months, // Months translated dynamically
            datasets: [{
                label: getTranslation('ordersPerMonth'), // Translated chart title
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
<?php
include 'footer.php'; // Include the footer file
?>
