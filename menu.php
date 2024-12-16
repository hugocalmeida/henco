<?php
// Get the current page script name
$current_page = basename($_SERVER['PHP_SELF']);
$company_name = $_SESSION['company_name'];
?>
<div class="nav-header">
    <div class="brand-logo">
        <a href="dashboard.php">
            <b class="logo-abbr company-name"><?php echo htmlspecialchars(mb_substr($company_name ?: 'Your Company', 0, 1), ENT_QUOTES, 'UTF-8'); ?></b>
            <span class="logo-compact company-name"><?php echo htmlspecialchars(mb_substr($company_name ?: 'Your Company', 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="brand-title company-name"><?php echo htmlspecialchars($company_name ?: 'Your Company', ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
    </div>
</div>

<div class="header">    
    <div class="header-content clearfix">           
        <div class="nav-control">
                    <div class="hamburger is-active">
                        <span class="toggle-icon"><i class="icon-menu"></i></span>
                    </div>
                </div>    
        <div class="header-right"> 
            <ul class="clearfix">
                <li class="icons"><a class="nav-link" href="cart.php"><i id="cart" class="ti-shopping-cart"></i><span data-translate="cart"> Cart</span></a></li>
                <li class="icons"><a href="profile.php" data-translate="guest"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></a></li> 
                <li class="icons"><a href="logout.php" data-translate="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>         
            </ul>
        </div>
    </div>
</div>

<div class="nk-sidebar">
    <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 100%;">
        <div class="nk-nav-scroll" style="overflow: hidden; width: auto; height: 100%;">
            <ul class="metismenu" id="menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'order_products.php') ? 'active' : ''; ?>" href="order_products.php">
                        <i class="fa-solid fa-shop"></i><span class="nav-text" data-translate="orderProducts"> Order Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'myorders.php') ? 'active' : ''; ?>" href="myorders.php">
                        <i class="ti-email"></i><span class="nav-text" data-translate="myOrders"> My Orders</span>
                    </a>
                </li>
                <?php if (isset($is_admin) && $is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'products.php') ? 'active' : ''; ?>" href="products.php">
                            <i class="ti-dropbox-alt"></i><span id="products" class="nav-text" data-translate="products"> Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>" href="categories.php">
                            <i class="fa fa-list"></i><span class="nav-text" data-translate="categories"> Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'clients.php') ? 'active' : ''; ?>" href="clients.php">
                            <i class="fa-solid fa-user-tie"></i><span class="nav-text" data-translate="clients"> Clients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'order_history.php') ? 'active' : ''; ?>" href="order_history.php">
                            <i class="fas fa-history"></i><span class="nav-text" data-translate="orderHistory"> Order History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'users_settings.php') ? 'active' : ''; ?>" href="users_settings.php">
                            <i class="fas fa-users-cog"></i><span class="nav-text" data-translate="users"> Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                            <i class="fas fa-cog"></i><span class="nav-text" data-translate="settings"> Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'upload_products.php') ? 'active' : ''; ?>" href="upload_products.php">
                            <i class="fas fa-upload"></i><span class="nav-text" data-translate="importProducts"> Import Products</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="slimScrollBar" style="background: transparent; width: 5px; position: absolute; top: -1px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 2680.54px;"></div>
        <div class="slimScrollRail" style="width: 5px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div>
    </div>
</div>      
<div class="content-body" style="min-height: 1100px;">
<div class="container-fluid">
<div class="card">
<div class="card-body">
