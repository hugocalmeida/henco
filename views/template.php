<?php
// This file includes the common HTML structure for all pages, including header elements, navigation, and preloader.
// Include database connection
include_once 'header.php';

// Retrieve company name and locale settings from the database or use default values
$company_name = get_setting('company_name') ?: 'Default Company Name'; // Gets company name from settings, defaults if not set
$locale = get_setting('locale') ?: 'en'; // Gets locale from settings, defaults to 'en' if not set
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"> <!-- Specifies character encoding for the HTML document -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Ensures compatibility with different versions of Internet Explorer -->
    <meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Sets the viewport to control layout on different screen sizes -->
    <title><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></title> <!-- Sets the title of the HTML page, escaping HTML entities for security -->

    <!-- Linking external CSS stylesheets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> <!-- Font Awesome CSS -->
    
    <!-- Linking external JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JavaScript bundle -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> <!-- DataTables CSS for table styling -->

    <!-- Including locale settings for internationalization -->
    <script> const locale = '<?php echo $locale; ?>';</script> <!-- Passes the locale setting to JavaScript -->
    <script src="js/locales.js"></script> <!-- Includes JavaScript file for handling locale-specific content -->

    <!-- Linking custom stylesheet -->
    <link href="css/style.css" rel="stylesheet"> <!-- Includes custom CSS for additional styling -->
</head>

<body data-locale="<?php echo $locale; ?>"> <!-- Sets the locale as a data attribute for use in JavaScript -->
    
<!-- Preloader section displayed while the page is loading -->
<div id="preloader">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
        </svg>
    </div>
</div>

<!-- Main content wrapper for the entire page -->
<div id="main-wrapper">

    <!-- Include the sidebar menu -->
    <?php include 'menu.php'; ?> <!-- Sidebar navigation menu -->

    <!-- Main content area -->
    <div class="">
        <div class="container-fluid">
            <!-- Placeholder for dynamic page content -->
            <!-- Content from other pages will be loaded here -->
        </div>
    </div>

    <!-- Closing body and html tags -->
</body>
</html>
