<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $title ?? 'Client Area'; ?> - <?php echo COMPANY_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(180deg, #222831 10%, #30475e 100%);
            padding: 0.75rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 0.5rem 1rem;
            transition: color 0.2s;
        }
        
        .navbar .nav-link:hover,
        .navbar .nav-link.active {
            color: #fff !important;
        }
        
        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        /* Content */
        .content-wrapper {
            flex: 1;
            padding: 2rem 0;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border-radius: 0.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }
        
        .card-header h5,
        .card-header h6 {
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Stats Cards */
        .stat-card {
            border-left: 4px solid;
            padding: 1.25rem;
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        
        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #333;
        }
        
        .stat-card .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: var(--secondary-color);
        }
        
        /* Sidebar Navigation */
        .sidebar-nav {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            overflow: hidden;
        }
        
        .sidebar-nav .list-group-item {
            border: none;
            border-left: 3px solid transparent;
            padding: 0.75rem 1rem;
            color: #333;
            transition: all 0.2s;
        }
        
        .sidebar-nav .list-group-item:hover {
            background-color: #f8f9fc;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-nav .list-group-item.active {
            background-color: rgba(78, 115, 223, 0.1);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .sidebar-nav .list-group-item i {
            width: 20px;
            margin-right: 0.5rem;
        }
        
        /* Tables */
        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--secondary-color);
            border-top: none;
        }
        
        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.4em 0.75em;
        }
        
        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid #e3e6f0;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 1rem 0;
            }
        }
    </style>
    <?php echo $styles ?? ''; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-server me-2"></i><?php echo COMPANY_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo CLIENT_URL; ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'services' ? 'active' : ''; ?>" href="<?php echo CLIENT_URL; ?>/services">
                            <i class="fas fa-cube me-1"></i>Services
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'domains' ? 'active' : ''; ?>" href="<?php echo CLIENT_URL; ?>/domains">
                            <i class="fas fa-globe me-1"></i>Domains
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'invoices' ? 'active' : ''; ?>" href="<?php echo CLIENT_URL; ?>/invoices">
                            <i class="fas fa-file-invoice-dollar me-1"></i>Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($page ?? '') === 'tickets' ? 'active' : ''; ?>" href="<?php echo CLIENT_URL; ?>/tickets">
                            <i class="fas fa-life-ring me-1"></i>Support
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['client']['firstname'] ?? 'Account'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/profile">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/security">
                                    <i class="fas fa-shield-alt me-2"></i>Security
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo CLIENT_URL; ?>/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Content -->
    <div class="content-wrapper">
        <div class="container">
            <!-- Flash Messages -->
            <?php if ($flash = ($_SESSION['flash'] ?? null)): unset($_SESSION['flash']); ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $content; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <a href="<?php echo BASE_URL; ?>/terms" class="text-muted text-decoration-none me-3">Terms of Service</a>
                    <a href="<?php echo BASE_URL; ?>/privacy" class="text-muted text-decoration-none">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $scripts ?? ''; ?>
</body>
</html>
