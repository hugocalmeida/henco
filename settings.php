<?php
include 'header.php';
$page_title = 'Settings';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: home.php');
    exit();
}

// Create the 'settings' table if it does not exist
$create_settings_table_sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$mysqli->query($create_settings_table_sql);

// Handle form submission to update the settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update manager email
    if (isset($_POST['update_manager_email'])) {
        $manager_email = trim($_POST['manager_email']);
        if (filter_var($manager_email, FILTER_VALIDATE_EMAIL)) {
            update_setting($mysqli, 'manager_email', $manager_email);
            $_SESSION['success_message'] = 'Manager email updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Please enter a valid email address.';
        }
    }

    // Update send email
    if (isset($_POST['update_send_email'])) {
        $send_email = trim($_POST['send_email']);
        if (filter_var($send_email, FILTER_VALIDATE_EMAIL)) {
            update_setting($mysqli, 'send_email', $send_email);
            $_SESSION['success_message'] = 'Send email updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Please enter a valid email address.';
        }
    }

    // Update currency
    if (isset($_POST['update_currency'])) {
        $currency = trim($_POST['currency']);
        if (!empty($currency)) {
            update_setting($mysqli, 'currency', $currency);
            $_SESSION['success_message'] = 'Currency updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Currency cannot be empty.';
        }
    }

    // Update locale
    if (isset($_POST['update_locale'])) {
        $locale = trim($_POST['locale']);
        if (!empty($locale)) {
            update_setting($mysqli, 'locale', $locale);
            $_SESSION['success_message'] = 'Locale updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Locale cannot be empty.';
        }
    }

    // Update company name
    if (isset($_POST['update_company_name'])) {
        $company_name = trim($_POST['company_name']);
        if (!empty($company_name)) {
            update_setting($mysqli, 'company_name', $company_name);
            $_SESSION['company_name'] = $company_name;
            $_SESSION['success_message'] = 'Company name updated successfully!';
        } else {
            $_SESSION['error_message'] = 'Company name cannot be empty.';
        }
    }
}

// Function to update or insert settings
function update_setting($mysqli, $key, $value) {
    $stmt_check = $mysqli->prepare('SELECT id FROM settings WHERE setting_key = ?');
    $stmt_check->bind_param('s', $key);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_update = $mysqli->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
        $stmt_update->bind_param('ss', $value, $key);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        $stmt_insert = $mysqli->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt_insert->bind_param('ss', $key, $value);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
            
    $stmt_check->close();
}



// Get the current settings
$manager_email = get_setting($mysqli, 'manager_email') ?? '';
$send_email = get_setting($mysqli, 'send_email') ?? '';
$currency = get_setting($mysqli, 'currency') ?? '€';
$locale = get_setting($mysqli, 'locale') ?? 'en_US';
$company_name = get_setting($mysqli, 'company_name') ?? '';

// Function to get settings value
function get_setting($mysqli, $key) {
    $stmt = $mysqli->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return $value;
}

// Close the database connection
$mysqli->close();
include 'template.php';
?>

<div class="col-md-10">

    <h1>Settings</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Form to update manager email -->
    <form method="POST" action="">
        <div class="mb-4">
            <label for="managerEmail" class="form-label">Manager Email:</label>
            <input type="email" id="managerEmail" name="manager_email" class="form-control" value="<?php echo htmlspecialchars($manager_email, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" name="update_manager_email" class="btn btn-primary">Update Manager Email</button>
    </form>

    <!-- Form to update send email -->
    <form method="POST" action="" class="mt-4">
        <div class="mb-4">
            <label for="sendEmail" class="form-label">Send Email (From):</label>
            <input type="email" id="sendEmail" name="send_email" class="form-control" value="<?php echo htmlspecialchars($send_email, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" name="update_send_email" class="btn btn-primary">Update Send Email</button>
    </form>

    <!-- Form to update currency -->
    <form method="POST" action="" class="mt-4">
        <div class="mb-4">
            <label for="currency" class="form-label">Currency:</label>
            <input type="text" id="currency" name="currency" class="form-control" value="<?php echo htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" name="update_currency" class="btn btn-primary">Update Currency</button>
    </form>

    <!-- Form to update locale -->
    <form method="POST" action="" class="mt-4">
        <div class="mb-4">
            <label for="locale" class="form-label">Locale:</label>
            <input type="text" id="locale" name="locale" class="form-control" value="<?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" name="update_locale" class="btn btn-primary">Update Locale</button>
    </form>

    <!-- Form to update company name -->
    <form method="POST" action="" class="mt-4">
        <div class="mb-4">
            <label for="companyName" class="form-label">Company Name:</label>
            <input type="text" id="companyName" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <button type="submit" name="update_company_name" class="btn btn-primary">Update Company Name</button>
    </form>

</div>
<?php include 'footer.php'; ?>
