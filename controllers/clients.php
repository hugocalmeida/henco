<?php
// There are some functions that should be grouped in a file or class. Functions like `handleMessage`, `addClient`, `editClient`, `deleteClient`, `fetchClients`
include 'header.php'; // Includes database connection and session management.
$page_title = 'Clients'; // Sets the title of the page.

// Check if the user is an admin. If not, redirects to the dashboard.
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit;
}

// Function to handle messages (success or error).
function handleMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Handles adding a new client to the database.
function addClient($mysqli) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $zip = htmlspecialchars(trim($_POST['zip']));
    $stmt_add = $mysqli->prepare('INSERT INTO clients (name, email, phone, address, city, state, zip) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt_add->bind_param('sssssss', $name, $email, $phone, $address, $city, $state, $zip);

    if ($stmt_add->execute()) {
        handleMessage('Client added successfully.');
    } else {
        handleMessage('Failed to add client: ' . $stmt_add->error, 'error');
    }

    $stmt_add->close();
}

// Handles editing an existing client's information.
function editClient($mysqli) {
    $client_id = intval($_POST['client_id']) ?? 0;
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $zip = htmlspecialchars(trim($_POST['zip']));
    $stmt_edit = $mysqli->prepare('UPDATE clients SET name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip = ? WHERE id = ?');
    $stmt_edit->bind_param('sssssssi', $name, $email, $phone, $address, $city, $state, $zip, $client_id);

    if ($stmt_edit->execute()) {
        handleMessage('Client updated successfully.');
    } else {
        handleMessage('Failed to update client: ' . $stmt_edit->error, 'error');
    }    

    $stmt_edit->close();
}

// Handles deleting a client from the database.
function deleteClient($mysqli) {
    $client_id = intval($_POST['client_id']);
    $stmt_delete = $mysqli->prepare('DELETE FROM clients WHERE id = ?');
    $stmt_delete->bind_param('i', $client_id);

    if ($stmt_delete->execute()) {
        handleMessage('Client deleted successfully.');
    } else {
        handleMessage('Failed to delete client: ' . $stmt_delete->error, 'error');
    }

    $stmt_delete->close();
}

// Process actions based on the HTTP request method.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_client'])) {
        addClient($mysqli);
    } elseif (isset($_POST['edit_client'])) {
        editClient($mysqli);
    } elseif (isset($_POST['delete_client'])) {
        deleteClient($mysqli);
    }

    // Redirect to refresh the page and show the updated client list.
    header('Location: clients.php');
    exit;
}

// Fetch all clients from the database, ordered by name.
function fetchClients($mysqli) {
    $result = $mysqli->query('SELECT * FROM clients ORDER BY name ASC');
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        handleMessage('Failed to retrieve clients: ' . $mysqli->error, 'error');
        return []; // Return an empty array in case of failure.
    }
}

$clients = fetchClients($mysqli);

include 'template.php'; // Includes the HTML template for the page.
?>

<div class="row">
    <h1 data-translate="clients">Clients</h1>

    <!-- Add Client Button -->
    <button class="btn btn-primary col-1 mb-3" data-bs-toggle="modal" data-bs-target="#addClientModal" data-translate="add">
        <i class="fa fa-plus-circle" aria-hidden="true"></i> Add
    </button>

    <!-- Display any messages (success or error) -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] == 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
        // Clear the message after displaying it.
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="Data_Table_7" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="email">Email</th>
                    <th data-translate="phone">Phone</th>
                    <th data-translate="city">City</th>
                    <th data-translate="state">State</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['name']); ?></td>
                    <td><?= htmlspecialchars($client['email']); ?></td>
                    <td><?= htmlspecialchars($client['phone']); ?></td>
                    <td><?= htmlspecialchars($client['city']); ?></td>
                    <td><?= htmlspecialchars($client['state']); ?></td>
                    <td>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editClientModal" onclick='populateEditModalClient(<?= json_encode($client); ?>)' data-translate="edit">Edit</button>
                        <form method="POST" action="" class="d-inline-block">
                            <input type="hidden" name="delete_client" value="1">
                            <input type="hidden" name="client_id" value="<?= $client['id']; ?>">
                            <button type="submit" class="btn btn-danger" data-translate="delete">Delete</button>
                        </form>
                        <a href="client_details.php?client_id=<?= $client['id']; ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="6" class="text-center" data-translate="noClientsFound">No clients found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Client Modal -->
<div id="addClientModal" class="modal fade" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="add_client" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel" data-translate="addClient">Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="client_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="client_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_email" class="form-label" data-translate="email">Email</label>
                        <input type="email" class="form-control" id="client_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_phone" class="form-label" data-translate="phone">Phone</label>
                        <input type="text" class="form-control" id="client_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="client_address" class="form-label" data-translate="address">Address</label>
                        <input type="text" class="form-control" id="client_address" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="client_city" class="form-label" data-translate="city">City</label>
                        <input type="text" class="form-control" id="client_city" name="city">
                    </div>
                    <div class="mb-3">
                        <label for="client_state" class="form-label" data-translate="state">State</label>
                        <input type="text" class="form-control" id="client_state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="client_zip" class="form-label" data-translate="zip">ZIP</label>
                        <input type="text" class="form-control" id="client_zip" name="zip">
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

<!-- Edit Client Modal -->
<div id="editClientModal" class="modal fade" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="edit_client" value="1">
                <input type="hidden" id="edit_client_id" name="client_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel" data-translate="editClient">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label" data-translate="name">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label" data-translate="email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label" data-translate="phone">Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label" data-translate="address">Address</label>
                        <input type="text" class="form-control" id="edit_address" name="address">
                    </div>
                    <div class="mb-3">
                        <label for="edit_city" class="form-label" data-translate="city">City</label>
                        <input type="text" class="form-control" id="edit_city" name="city">
                    </div>
                    <div class="mb-3">
                        <label for="edit_state" class="form-label" data-translate="state">State</label>
                        <input type="text" class="form-control" id="edit_state" name="state">
                    </div>
                    <div class="mb-3">
                        <label for="edit_zip" class="form-label" data-translate="zip">ZIP</label>
                        <input type="text" class="form-control" id="edit_zip" name="zip">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="submit" class="btn btn-primary" data-translate="saveChanges">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php include 'footer.php'; ?>