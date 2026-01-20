<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex">
    <title><?php echo $title ?? 'Admin'; ?> - <?php echo COMPANY_NAME; ?> Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
            --sidebar-bg: #212529;
            --sidebar-active: #4e73df;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            font-size: 0.9rem;
        }
        
        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #222831 10%, #30475e 100%);
            z-index: 1000;
            overflow-y: auto;
        }
        
        #sidebar .sidebar-brand {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        #sidebar .sidebar-brand h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }
        
        #sidebar .nav-item {
            position: relative;
        }
        
        #sidebar .nav-link {
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        
        #sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--primary-color);
        }
        
        #sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(78,115,223,0.15);
            border-left-color: var(--primary-color);
        }
        
        #sidebar .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
        }
        
        #sidebar .sidebar-heading {
            padding: 1rem;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: rgba(255,255,255,0.4);
        }
        
        #sidebar hr {
            border-color: rgba(255,255,255,0.15);
            margin: 0;
        }
        
        /* Main Content */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Topbar */
        .topbar {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        /* Content */
        .content-wrapper {
            padding: 1.5rem;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            border-radius: 0.35rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 0.75rem 1.25rem;
        }
        
        .card-header h6 {
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        
        /* Stats Cards */
        .stat-card {
            border-left: 4px solid;
        }
        
        .stat-card.primary { border-left-color: var(--primary-color); }
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.info { border-left-color: var(--info-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        
        .stat-card .stat-icon {
            font-size: 2rem;
            color: #dddfeb;
        }
        
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-card .stat-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
        }
        
        .stat-card.primary .stat-label { color: var(--primary-color); }
        .stat-card.success .stat-label { color: var(--success-color); }
        .stat-card.info .stat-label { color: var(--info-color); }
        .stat-card.warning .stat-label { color: var(--warning-color); }
        .stat-card.danger .stat-label { color: var(--danger-color); }
        
        /* Tables */
        .table th {
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--dark-color);
            border-top: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        /* Badges */
        .badge {
            font-weight: 600;
            padding: 0.4em 0.75em;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        /* Page header */
        .page-header {
            margin-bottom: 1.5rem;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
                transition: margin 0.3s;
            }
            
            #sidebar.show {
                margin-left: 0;
            }
            
            #main-content {
                margin-left: 0;
            }
        }
    </style>
    <?php echo $styles ?? ''; ?>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <h1><i class="fas fa-server me-2"></i>OpenWHM</h1>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <div class="sidebar-heading">Clients</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'clients' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/clients">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </li>
            
            <hr>
            
            <div class="sidebar-heading">Orders & Billing</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'orders' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'invoices' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/invoices">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Invoices</span>
                </a>
            </li>
            
            <hr>
            
            <div class="sidebar-heading">Products & Services</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'services' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/services">
                    <i class="fas fa-cube"></i>
                    <span>Services</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'products' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/products">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'domains' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/domains">
                    <i class="fas fa-globe"></i>
                    <span>Domains</span>
                </a>
            </li>
            
            <hr>
            
            <div class="sidebar-heading">Support</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'tickets' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Support Tickets</span>
                </a>
            </li>
            
            <hr>
            
            <div class="sidebar-heading">System</div>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'servers' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/servers">
                    <i class="fas fa-server"></i>
                    <span>Servers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'extensions' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/extensions">
                    <i class="fas fa-puzzle-piece"></i>
                    <span>Extensions</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page === 'logs' ? 'active' : ''; ?>" href="<?php echo ADMIN_URL; ?>/logs">
                    <i class="fas fa-list-alt"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div id="main-content">
        <!-- Topbar -->
        <nav class="topbar d-flex justify-content-between align-items-center">
            <button class="btn btn-link d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <form class="d-none d-md-flex" style="width: 300px;">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search..." name="search">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <!-- Alerts -->
                <li class="nav-item dropdown mx-2">
                    <a class="nav-link" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-fw"></i>
                        <span class="badge bg-danger position-absolute" style="font-size: 0.6rem;">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow">
                        <h6 class="dropdown-header">Alerts</h6>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <div class="me-3">
                                <div class="icon-circle bg-warning text-white rounded-circle p-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            <div>
                                <span class="small text-muted">December 12, 2024</span>
                                <p class="mb-0">5 pending orders require attention</p>
                            </div>
                        </a>
                    </div>
                </li>
                
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <span class="d-none d-md-inline text-gray-600 small me-2"><?php echo $admin['name'] ?? 'Admin'; ?></span>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-user fa-sm"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow">
                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/profile">
                            <i class="fas fa-user fa-sm fa-fw me-2 text-muted"></i>
                            Profile
                        </a>
                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/settings">
                            <i class="fas fa-cogs fa-sm fa-fw me-2 text-muted"></i>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-muted"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div class="content-wrapper">
            <!-- Flash Messages -->
            <?php if ($flash = ($_SESSION['flash'] ?? null)): unset($_SESSION['flash']); ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $content; ?>
        </div>
        
        <!-- Footer -->
        <footer class="bg-white py-3 px-4 mt-auto border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. Powered by OpenWHM</span>
                <span class="text-muted small">Version 1.0.0</span>
            </div>
        </footer>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom Scripts -->
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
        });
    </script>
    <?php echo $scripts ?? ''; ?>
</body>
</html>
