<?php
// This file contains the code for generating the navigation menu of the application.
// There are some functions that should be grouped in a file or class. Functions like `generateMenuItem`
// There are some functions that should be grouped in a file or class. Functions like `generateMenuItem`
// This file generates the menu and its links.
include_once 'utils.php';

// Get the current page's script name to determine the active menu item.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="nk-sidebar">
    <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 100%;">
        <div class="nk-nav-scroll" style="overflow: hidden; width: auto; height: 100%;">
            <ul class="metismenu" id="menu">
                <?php // Generate menu items using the generateMenuItem function. ?>
                <?php // Dashboard menu item. ?>
                <?php
                echo generateMenuItem($current_page == 'home.php', 'dashboard.php', 'icon-speedometer menu-icon', 'Dashboard');
                ?>
                <?php // Order Products menu item. ?>
                <?php
                echo generateMenuItem($current_page == 'order_products.php', 'order_products.php', 'fa-solid fa-shop', 'Order Products', true);
                ?>
                <?php // My Orders menu item. ?>
                <?php
                echo generateMenuItem($current_page == 'myorders.php', 'myorders.php', 'ti-email', 'My Orders', true);
                ?>

                <?php // Check if the user is an admin to display admin-specific menu items. ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <?php // Products menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'products.php', 'products.php', 'ti-dropbox-alt', 'Products', true);
                    ?>
                    <?php // Categories menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'categories.php', 'categories.php', 'fa fa-list', 'Categories', true);
                    ?>
                    <?php // Clients menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'clients.php', 'clients.php', 'fa-solid fa-user-tie', 'Clients', true);
                    ?>
                    <?php // Order History menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'order_history.php', 'order_history.php', 'fas fa-history', 'Order History', true);
                    ?>
                    <?php // Users menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'users_settings.php', 'users_settings.php', 'fas fa-users-cog', 'Users', true);
                    ?>
                    <?php // Settings menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'settings.php', 'settings.php', 'fas fa-cog', 'Settings', true);
                    ?>
                    <?php // Import Products menu item (admin only). ?>
                    <?php
                    echo generateMenuItem($current_page == 'upload_products.php', 'upload_products.php', 'fas fa-upload', 'Import Products', true);
                    ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="slimScrollBar" style="background: transparent; width: 5px; position: absolute; top: -1px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 2680.54px;"></div>
        <div class="slimScrollRail" style="width: 5px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div>
    </div>
</div>
</div>
<div class="content-body" style="min-height: 1100px;">
<div class="container-fluid">
<div class="card">
<div class="card-body">
