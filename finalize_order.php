<?php
include 'header.php';
$page_title = 'Finalize Order';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicializa as variáveis para evitar erros de "undefined variable"
$cart_items = [];
$total_amount = 0;

// Obtém os produtos do carrinho antes do processamento do pedido
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];

        // Obtém os detalhes dos produtos
        $stmt = $mysqli->prepare('SELECT name, price FROM products WHERE id = ?');
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $stmt->bind_result($name, $price);
        $stmt->fetch();
        $stmt->close();

        $subtotal = $price * $quantity;
        $total_amount += $subtotal;

        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// Process the order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_order'])) {
    // Check if client_id is set
    if (empty($_POST['client_id'])) {
        $_SESSION['error_message'] = 'Please select a client before proceeding.';
        header('Location: finalize_order.php');
        exit();
    }

    // Atualiza os detalhes do cliente (morada, cidade, etc.) se todos os campos estiverem preenchidos
    $client_id = intval($_POST['client_id']);
    $client_address = trim($_POST['address']);
    $client_city = trim($_POST['city']);
    $client_state = trim($_POST['state']);
    $client_zip = trim($_POST['zip']);

    // Verifique se todos os campos de endereço estão preenchidos
    if ($client_address === '' || $client_city === '' || $client_state === '' || $client_zip === '') {
        $_SESSION['error_message'] = 'All address fields are required.';
        header('Location: finalize_order.php');
        exit();
    }

    $stmt_update_client = $mysqli->prepare('UPDATE clients SET address = ?, city = ?, state = ?, zip = ? WHERE id = ?');
    $stmt_update_client->bind_param('ssssi', $client_address, $client_city, $client_state, $client_zip, $client_id);
    $stmt_update_client->execute();
    $stmt_update_client->close();

    // Start the transaction
    $mysqli->begin_transaction();

    try {
        $user_id = $_SESSION['user_id'];

        // Get updated client details
        $stmt_client = $mysqli->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt_client->bind_param('i', $client_id);
        $stmt_client->execute();
        $client_result = $stmt_client->get_result();
        $client = $client_result->fetch_assoc();
        $stmt_client->close();

        if (!$client) {
            throw new Exception('Client not found.');
        }

        // Insert the order into the 'orders' table
        $stmt_order = $mysqli->prepare('INSERT INTO orders (user_id, client_id, total_amount) VALUES (?, ?, ?)');
        $stmt_order->bind_param('iid', $user_id, $client_id, $total_amount);
        $stmt_order->execute();
        $order_id = $stmt_order->insert_id;
        $stmt_order->close();

        // Insert order items into 'order_items' table and update product stock
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            // Insert the item into 'order_items' table
            $stmt_item = $mysqli->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stmt_item->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            $stmt_item->execute();
            $stmt_item->close();

            // Update product stock
            $stmt_update_stock = $mysqli->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            $stmt_update_stock->bind_param('ii', $quantity, $product_id);
            $stmt_update_stock->execute();
            $stmt_update_stock->close();
        }

        // Fetch the manager email from the settings table
        $stmt_get_email = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $setting_key = 'manager_email';
        $stmt_get_email->bind_param('s', $setting_key);
        $stmt_get_email->execute();
        $stmt_get_email->bind_result($manager_email);
        $stmt_get_email->fetch();
        $stmt_get_email->close();

        if (empty($manager_email)) {
            throw new Exception('Manager email is not set in the system settings.');
        }

         // Fetch the currency setting from the settings table
        $stmt_get_currency = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $setting_key = 'currency';
        $stmt_get_currency->bind_param('s', $setting_key);
        $stmt_get_currency->execute();
        $stmt_get_currency->bind_result($currency);
        $stmt_get_currency->fetch();
        $stmt_get_currency->close();

        // Set a default currency if not defined
        if (empty($currency)) {
            $currency = '€';
        }           
            
// Fetch the current language from the settings table
$stmt_get_language = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$setting_key = 'language';
$stmt_get_language->bind_param('s', $setting_key);
$stmt_get_language->execute();
$stmt_get_language->bind_result($language);
$stmt_get_language->fetch();
$stmt_get_language->close();

// Load translations for the selected language
$translations = [];
$translation_file = "locales/{$language}.json"; // Assuming translations are stored in JSON files
if (file_exists($translation_file)) {
    $translations = json_decode(file_get_contents($translation_file), true);
}

// Function to fetch translation
function translate($key, $translations) {
    return $translations[$key] ?? $key;
}            
            
            
        // Send email to the store manager
        $subject = translate('new_order', $translations). " #" . $order_id . ' | ' . htmlspecialchars($client['name']);

// Fetch the currency setting from the settings table
$stmt_get_currency = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$setting_key = 'currency';
$stmt_get_currency->bind_param('s', $setting_key);
$stmt_get_currency->execute();
$stmt_get_currency->bind_result($currency);
$stmt_get_currency->fetch();
$stmt_get_currency->close();

// Set a default currency if not defined
if (empty($currency)) {
    $currency = '€';
}



// Prepare the email message in HTML format
$message = "<h3>" . translate('new_order_placed', $translations) . "</h3>";
$message .= "<p><strong>" . translate('client_name', $translations) . ":</strong> " . htmlspecialchars($client['name']) . "<br>";
$message .= "<strong>" . translate('client_address', $translations) . ":</strong> " . htmlspecialchars($client['address']) . ", " . htmlspecialchars($client['city']) . ", " . htmlspecialchars($client['state']) . " " . htmlspecialchars($client['zip']) . "<br>";
$message .= "<strong>" . translate('client_email', $translations) . ":</strong> " . htmlspecialchars($client['email']) . "<br>";
$message .= "<strong>" . translate('client_phone', $translations) . ":</strong> " . htmlspecialchars($client['phone']) . "</p>";

$message .= "<h4>" . translate('order_details', $translations) . "</h4>";
$message .= "<table border='1' cellpadding='5' cellspacing='0'>";
$message .= "<thead><tr><th>" . translate('product', $translations) . "</th><th>" . translate('unit_price', $translations) . "</th><th>" . translate('quantity', $translations) . "</th><th>" . translate('subtotal', $translations) . "</th></tr></thead>";
$message .= "<tbody>";

foreach ($cart_items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $message .= "<tr>";
    $message .= "<td>" . htmlspecialchars($item['name']) . "</td>";
    $message .= "<td>" . htmlspecialchars($currency) . " " . number_format($item['price'], 2, ',', '.') . "</td>";
    $message .= "<td>" . htmlspecialchars($item['quantity']) . "</td>";
    $message .= "<td>" . htmlspecialchars($currency) . " " . number_format($subtotal, 2, ',', '.') . "</td>";
    $message .= "</tr>";
}

$message .= "<tr><td colspan='3' style='text-align:right;'><strong>" . translate('total', $translations) . ":</strong></td>";
$message .= "<td><strong>" . htmlspecialchars($currency) . " " . number_format($total_amount, 2, ',', '.') . "</strong></td></tr>";
$message .= "</tbody></table>";

$message .= "<p><strong>" . translate('order_number', $translations) . ":</strong> " . $order_id . "<br>";
$message .= "<strong>" . translate('user', $translations) . ":</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";

// Fetch "from" email address from the settings table
$stmt_get_send_email = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
$setting_key = 'send_email';
$stmt_get_send_email->bind_param('s', $setting_key);
$stmt_get_send_email->execute();
$stmt_get_send_email->bind_result($send_email);
$stmt_get_send_email->fetch();
$stmt_get_send_email->close();

// Validate the email
if (empty($send_email) || !filter_var($send_email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception(translate('invalid_email', $translations));
}

// Set headers for HTML email
$headers = "From: " . $send_email . "\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Send the email
if (!mail($manager_email, translate('email_subject', $translations), $message, $headers)) {
    throw new Exception(translate('email_send_failed', $translations));
}


        // Clear the cart
        $_SESSION['cart'] = [];

        // Commit the transaction
        $mysqli->commit();

        $_SESSION['success_message'] = 'Order successfully placed! Order number: ' . $order_id;
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $mysqli->rollback();
        $_SESSION['error_message'] = 'Error processing order: ' . $e->getMessage();
        header('Location: finalize_order.php');
        exit();
    }
}

$result_clients = $mysqli->query('SELECT id, name FROM clients');
$clients = $result_clients->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
include 'template.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?> 

<h1 data-translate="finalizeOrder">Finalize Order</h1>
<div class="mb-4"> <a href="cart.php" class="btn btn-secondary mt-3" data-translate="backToCart"><i class="fas fa-arrow-left"></i> Back to Cart</a></div>
<div class="row">
<form method="POST" action="">
    <!-- Dropdown to select the client -->
    <div class="mb-4">
        <label for="clientSelect" class="form-label" data-translate="selectClient"> Select Client:</label>
        <select id="clientSelect" name="client_id" class="form-select form-select-lg mb-3"  required>
            <option value="" data-translate="chooseClient">Choose a client</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?php echo htmlspecialchars($client['id']); ?>">
                    <?php echo htmlspecialchars($client['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>       

    <!-- Client Address Fields (Editable) -->
    <div id="clientDetails" style="display: none;">
        <div class="mb-3">
            <label for="address" class="form-label" data-translate="address">Address:</label>
            <input type="text" id="address" name="address" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="city" class="form-label"  data-translate="city">City:</label>
            <input type="text" id="city" name="city" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="state" class="form-label"  data-translate="state">State:</label>
            <input type="text" id="state" name="state" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="zip" class="form-label"  data-translate="zip">ZIP:</label>
            <input type="text" id="zip" name="zip" class="form-control" required>
        </div>
    </div>

    <h4 class="mt-4" data-translate="orderSummary">Order Summary</h4>
    <div class="table-responsive">    
        <table id="Data_Table2" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
					<th data-translate="product">Product</th>
                    <th data-translate="priceunit">Unit Price</th>
                    <th data-translate="quantity">Quantity</th>
                    <th data-translate="subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>€ <?php echo htmlspecialchars(number_format((float)$item['price'], 2, ',', '.')); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>€ <?php echo htmlspecialchars(number_format((float)$item['subtotal'], 2, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td><strong>€ <?php echo htmlspecialchars(number_format((float)$total_amount, 2, ',', '.')); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Button to process the order -->
    <div class="mt-4">
        <button class="btn btn-success" type="submit" name="process_order"  data-translate="processOrder">Process Order</button>
    </div>
</form>

</div>
<?php include 'footer.php'; ?>
<script>
        $('#clientSelect').val();
    $(document).ready(function() {
        // Handle client selection
        $('#clientSelect').change(function() {
            var clientId = $(this).val();
            if (clientId) {
                // Make an AJAX call to get the client details
                $.ajax({
                    url: 'get_client_details.php',
                    type: 'GET',
                    data: { client_id: clientId },
                    success: function(response) {
                        var client = JSON.parse(response);
                        $('#address').val(client.address);
                        $('#city').val(client.city);
                        $('#state').val(client.state);
                        $('#zip').val(client.zip);
                        $('#clientDetails').show();
                    },
                    error: function() {
                        alert('Unable to fetch client details.');
                    }
                });
            } else {
                $('#clientDetails').hide();
            }
        });
    });
</script>

