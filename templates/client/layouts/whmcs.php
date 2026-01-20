<?php
/**
 * OpenWHM Client Area Layout - WHMCS Style
 * Matches WHMCS look and feel
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title ?? 'Client Area'; ?> - <?php echo COMPANY_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --whmcs-primary: #5bc0de;
            --whmcs-primary-dark: #31b0d5;
            --whmcs-success: #5cb85c;
            --whmcs-warning: #f0ad4e;
            --whmcs-danger: #d9534f;
            --whmcs-dark: #333;
            --whmcs-gray: #f5f5f5;
            --whmcs-border: #ddd;
            --sidebar-bg: #f9f9f9;
        }
        
        body {
            font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            background: #fff;
            color: #333;
        }
        
        /* Top Navigation */
        .navbar-whmcs {
            background: linear-gradient(to bottom, #5bc0de 0%, #31b0d5 100%);
            border: none;
            border-radius: 0;
            padding: 0;
            min-height: 50px;
        }
        
        .navbar-whmcs .navbar-brand {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            padding: 10px 15px;
        }
        
        .navbar-whmcs .navbar-brand img {
            height: 40px;
        }
        
        .navbar-whmcs .nav-link {
            color: rgba(255,255,255,0.9) !important;
            padding: 15px 15px;
            font-weight: 500;
        }
        
        .navbar-whmcs .nav-link:hover {
            background: rgba(0,0,0,0.1);
            color: #fff !important;
        }
        
        .navbar-whmcs .dropdown-menu {
            border-radius: 0;
            border: 1px solid #ddd;
            margin-top: 0;
        }
        
        .top-bar {
            background: #f5f5f5;
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .top-bar a {
            color: #666;
            text-decoration: none;
        }
        
        .top-bar a:hover {
            color: #333;
        }
        
        /* Main Content Area */
        .main-content {
            display: flex;
            min-height: calc(100vh - 150px);
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--whmcs-border);
            padding: 20px;
            flex-shrink: 0;
        }
        
        .sidebar-panel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .sidebar-panel-header {
            background: #f5f5f5;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-weight: 600;
            font-size: 14px;
        }
        
        .sidebar-panel-header i {
            color: #5bc0de;
            margin-right: 8px;
        }
        
        .sidebar-panel-body {
            padding: 15px;
        }
        
        .available-funds {
            font-size: 13px;
        }
        
        .available-funds .amount {
            color: #5cb85c;
            font-weight: 600;
        }
        
        /* Content Area */
        .content-area {
            flex: 1;
            padding: 20px 30px;
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 300;
            color: #333;
            margin-bottom: 5px;
        }
        
        .breadcrumb-whmcs {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .breadcrumb-whmcs a {
            color: #5bc0de;
            text-decoration: none;
        }
        
        /* Stats Cards */
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-card .stat-icon {
            font-size: 40px;
            color: #ddd;
            margin-bottom: 10px;
        }
        
        .stat-card .stat-number {
            font-size: 36px;
            font-weight: 300;
            color: #5bc0de;
            line-height: 1;
        }
        
        .stat-card .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        
        /* Search Bar */
        .kb-search {
            background: #5bc0de;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        
        .kb-search input {
            border: none;
            border-radius: 0;
            padding: 12px 15px;
            font-size: 14px;
        }
        
        .kb-search input::placeholder {
            color: #999;
        }
        
        /* Panels */
        .panel-whmcs {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .panel-whmcs-header {
            background: #f5f5f5;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-whmcs-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .panel-whmcs-header h4 i {
            color: #5bc0de;
            margin-right: 8px;
        }
        
        .panel-whmcs-body {
            padding: 15px;
        }
        
        .panel-whmcs-body ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .panel-whmcs-body ul li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .panel-whmcs-body ul li:last-child {
            border-bottom: none;
        }
        
        .panel-whmcs-body ul li a {
            color: #333;
            text-decoration: none;
        }
        
        .panel-whmcs-body ul li a:hover {
            color: #5bc0de;
        }
        
        .panel-whmcs-body ul li i {
            width: 20px;
            color: #5bc0de;
        }
        
        /* Badge */
        .badge-whmcs {
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
        }
        
        .badge-articles {
            background: #5bc0de;
            color: #fff;
        }
        
        /* Overdue Invoice Alert */
        .overdue-alert {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        
        .overdue-alert .overdue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .overdue-alert h4 {
            margin: 0;
            font-size: 14px;
            color: #d9534f;
        }
        
        .overdue-alert h4 i {
            margin-right: 8px;
        }
        
        .btn-pay-now {
            background: #d9534f;
            color: #fff;
            border: none;
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 3px;
        }
        
        .btn-pay-now:hover {
            background: #c9302c;
            color: #fff;
        }
        
        /* Domain Search */
        .domain-search-panel {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        
        .domain-search-panel h4 {
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .domain-search-panel h4 i {
            color: #5bc0de;
            margin-right: 8px;
        }
        
        .domain-search-panel .input-group {
            margin-bottom: 0;
        }
        
        .btn-register {
            background: #5cb85c;
            color: #fff;
            border: none;
        }
        
        .btn-transfer {
            background: #f0ad4e;
            color: #fff;
            border: none;
        }
        
        /* News Panel */
        .news-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .news-item:last-child {
            border-bottom: none;
        }
        
        .news-item h5 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .news-item .news-date {
            font-size: 12px;
            color: #999;
        }
        
        /* Partners Carousel */
        .partners-carousel {
            text-align: center;
            padding: 15px;
        }
        
        .partners-carousel img {
            max-height: 50px;
            opacity: 0.7;
        }
        
        .carousel-indicators {
            position: static;
            margin-top: 10px;
        }
        
        .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ddd;
        }
        
        .carousel-indicators button.active {
            background: #5bc0de;
        }
        
        /* Footer */
        footer {
            background: #333;
            color: #fff;
            padding: 30px 0;
            font-size: 13px;
        }
        
        footer a {
            color: #5bc0de;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #ddd;
            }
            
            .main-content {
                flex-direction: column;
            }
            
            .stats-row {
                flex-wrap: wrap;
            }
            
            .stat-card {
                min-width: calc(50% - 10px);
            }
        }
    </style>
    <?php echo $styles ?? ''; ?>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-end gap-4">
                <a href="<?php echo BASE_URL; ?>/cart"><i class="fas fa-shopping-cart me-1"></i> View Cart (<?php echo count($_SESSION['cart']['products'] ?? []) + count($_SESSION['cart']['domains'] ?? []); ?>)</a>
                <a href="#"><i class="fas fa-bell me-1"></i> Notifications (0)</a>
                <a href="#"><i class="fas fa-globe me-1"></i> Choose Language</a>
            </div>
        </div>
    </div>
    
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-whmcs">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <?php echo COMPANY_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Support</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/tickets">Tickets</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/knowledgebase">Knowledgebase</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/announcements">Announcements</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/network-status">Network Status</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo CLIENT_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Services</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/services">My Services</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/hosting">Order Hosting</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Domains</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/domains">My Domains</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/domains">Register Domain</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/domains/transfer">Transfer Domain</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Billing</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/invoices">My Invoices</a></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/quotes">My Quotes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/credit">Add Funds</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($this->session->isClientLoggedIn()): 
                        $clientName = $_SESSION['client']['firstname'] ?? 'User';
                    ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            Hello, <?php echo htmlspecialchars($clientName); ?>!
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/profile"><i class="fas fa-user me-2"></i> My Details</a></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/contacts"><i class="fas fa-users me-2"></i> Contacts/Sub-Accounts</a></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/security"><i class="fas fa-lock me-2"></i> Security Settings</a></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/emails"><i class="fas fa-envelope me-2"></i> Email History</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-comments"></i> Support Chat</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo CLIENT_URL; ?>/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo CLIENT_URL; ?>/register">Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Sidebar -->
        <div class="sidebar">
            <?php if ($this->session->isClientLoggedIn()): 
                $clientCredit = $_SESSION['client']['credit'] ?? 0;
            ?>
            <!-- Available Funds -->
            <div class="sidebar-panel">
                <div class="sidebar-panel-header">
                    <i class="fas fa-dollar-sign"></i> Available Funds
                </div>
                <div class="sidebar-panel-body available-funds">
                    <p>Hello <strong><?php echo htmlspecialchars($_SESSION['client']['firstname'] ?? ''); ?> <?php echo htmlspecialchars($_SESSION['client']['lastname'] ?? ''); ?></strong>!</p>
                    <p>You have <span class="amount">$<?php echo number_format($clientCredit, 2); ?></span> funds on your account.</p>
                    <p><a href="<?php echo CLIENT_URL; ?>/credit">Find out here</a> what you can do with them!</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Our Clients & Partners -->
            <div class="sidebar-panel">
                <div class="sidebar-panel-header">
                    <i class="fas fa-handshake"></i> Our Clients & Partners
                </div>
                <div class="sidebar-panel-body partners-carousel">
                    <div id="partnersCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/150x50?text=Partner+1" alt="Partner">
                            </div>
                            <div class="carousel-item">
                                <img src="https://via.placeholder.com/150x50?text=Partner+2" alt="Partner">
                            </div>
                        </div>
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#partnersCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#partnersCarousel" data-bs-slide-to="1"></button>
                        </div>
                    </div>
                    <p class="mt-3 mb-0"><a href="#">Click here</a> to find out more!</p>
                </div>
            </div>
            
            <!-- Our Websites -->
            <div class="sidebar-panel">
                <div class="sidebar-panel-header">
                    <i class="fas fa-external-link-alt"></i> Our Websites
                </div>
                <div class="sidebar-panel-body">
                    <ul>
                        <li><a href="#"><i class="fas fa-link"></i> Main Website</a></li>
                        <li><a href="#"><i class="fas fa-link"></i> Status Page</a></li>
                        <li><a href="#"><i class="fas fa-link"></i> Documentation</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php echo $content ?? ''; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Powered by <a href="#">OpenWHM</a></p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $scripts ?? ''; ?>
</body>
</html>
