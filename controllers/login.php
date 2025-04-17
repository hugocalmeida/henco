php
<?php
// Include the header file which sets up the database connection and starts the session.
include 'header.php';

// Set the title of the page that will be displayed in the browser tab.
$page_title = 'Login';

// Check if the request method is POST, which means the form has been submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the username entered by the user and sanitize it to prevent HTML injection.
    $username = htmlspecialchars($_POST["username"]);
    // Retrieve the password entered by the user.
    $password = $_POST["password"]; // Password should not be sanitized here to allow for proper verification.

    // SQL query to retrieve the user's ID, role ID, and hashed password based on the provided username.
    $sql = "SELECT user_id, role_id, password FROM users WHERE username = ?";
    // Prepare the SQL statement to prevent SQL injection.
    $stmt = $mysqli->prepare($sql);
    // Bind the username parameter to the prepared statement.
    $stmt->bind_param("s", $username);
    // Execute the prepared statement.
    $stmt->execute();
    // Bind the result variables to store the fetched data.
    $stmt->bind_result($user_id, $role_id, $hashed_password);
    // Fetch the result from the executed statement.
    $stmt->fetch();
    // Close the prepared statement.
    $stmt->close();

    // Check if a user ID was found and if the entered password matches the hashed password in the database.
    if ($user_id && password_verify($password, $hashed_password)) {
        // Store the user's ID in the session to maintain login state.
        $_SESSION["user_id"] = $user_id;
        // Store the user's role ID in the session to manage access control.
        $_SESSION["role_id"] = $role_id;

        // Redirect the user to the home page after successful login.
        header("Location: home.php");
        // Ensure that no further code is executed after redirection.
        exit();
    } else {
        // Set an error message if the login credentials are invalid.
        $error_message = "Invalid username or password.";
    }
}

// Display the error message if login failed.
if (isset($error_message)) {
    // Echo a paragraph with the error message styled in red.
    echo "<p style='color: red;'>$error_message</p>";
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6"> <!-- Use a column width of 6 for medium devices and larger -->
            <h2 class="text-center mb-4">Login</h2> <!-- Center the login heading and add bottom margin -->
            <!-- Login Form --> <!-- Start of the form for user login -->
            <form action="" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label> <!-- Label for the username input field -->
                    <input 
                        type="text" 
                        class="form-control" 
                        id="username" 
                        name="username" 
                        required 
                        placeholder="Enter your username" 
                    /> <!-- Input field for username, styled with Bootstrap, required attribute ensures it must be filled -->
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label> <!-- Label for the password input field -->
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password"> <!-- Input field for password, styled with Bootstrap, required attribute ensures it must be filled, type password obscures input -->
                </div>
                <button type="submit" class="btn btn-primary">Login</button> <!-- Submit button for the login form, styled as a primary button with Bootstrap -->
            </form>
        </div>
    </div>
</div>

<?php
// Include the footer file which typically closes HTML tags and includes JavaScript files.
include 'footer.php';
?>