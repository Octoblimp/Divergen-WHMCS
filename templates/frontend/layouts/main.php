<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $title ?? COMPANY_NAME; ?></title>
    <meta name="description" content="<?php echo $description ?? 'Premium web hosting, domain registration, and cloud services.'; ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #3d5fc4;
            --secondary-color: #6c757d;
            --success-color: #1cc88a;
            --accent-color: #764ba2;
            --dark-color: #1a1a2e;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
        }
        
        /* Navbar */
        .navbar {
            padding: 1rem 0;
            background: transparent;
            transition: all 0.3s;
        }
        
        .navbar.scrolled {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: #fff !important;
        }
        
        .navbar.scrolled .navbar-brand {
            color: var(--dark-color) !important;
        }
        
        .navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .navbar.scrolled .nav-link {
            color: #333 !important;
        }
        
        .navbar .nav-link:hover {
            color: #fff !important;
        }
        
        .navbar.scrolled .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .btn-login {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.5);
            color: #fff;
        }
        
        .btn-login:hover {
            background: #fff;
            color: var(--dark-color);
        }
        
        .navbar.scrolled .btn-login {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8rem 0 6rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ffffff' fill-opacity='0.1' d='M0,192L48,176C96,160,192,128,288,133.3C384,139,480,181,576,181.3C672,181,768,139,864,128C960,117,1056,139,1152,149.3C1248,160,1344,160,1392,160L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            background-position: bottom;
            background-repeat: no-repeat;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1.5rem;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
        }
        
        .hero .btn-primary {
            background: #fff;
            color: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .hero .btn-primary:hover {
            background: rgba(255,255,255,0.9);
            transform: translateY(-2px);
        }
        
        .hero .btn-outline-light {
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        /* Domain Search */
        .domain-search {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            margin-top: 2rem;
        }
        
        .domain-search .form-control {
            border: 2px solid #e3e6f0;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            border-radius: 0.5rem;
        }
        
        .domain-search .btn {
            padding: 1rem 2rem;
            font-weight: 600;
        }
        
        /* Features Section */
        .features {
            padding: 6rem 0;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 1rem;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            background: #f8f9fc;
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: #fff;
        }
        
        .feature-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        /* Pricing Section */
        .pricing {
            padding: 6rem 0;
            background: #f8f9fc;
        }
        
        .section-title {
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .section-subtitle {
            color: var(--secondary-color);
            margin-bottom: 3rem;
        }
        
        .pricing-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 2px solid transparent;
            height: 100%;
        }
        
        .pricing-card:hover,
        .pricing-card.featured {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .pricing-card.featured {
            border-color: var(--primary-color);
        }
        
        .pricing-card .badge {
            position: absolute;
            top: -10px;
            right: 20px;
            padding: 0.5rem 1rem;
        }
        
        .pricing-card h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .pricing-card .price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark-color);
        }
        
        .pricing-card .price small {
            font-size: 1rem;
            font-weight: 400;
            color: var(--secondary-color);
        }
        
        .pricing-card ul {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        
        .pricing-card ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .pricing-card ul li:last-child {
            border-bottom: none;
        }
        
        .pricing-card ul li i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 5rem 0;
            text-align: center;
        }
        
        .cta h2 {
            font-weight: 800;
            color: #fff;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta p {
            color: rgba(255,255,255,0.9);
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .cta .btn-light {
            padding: 1rem 2.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        /* Footer */
        footer {
            background: var(--dark-color);
            color: #fff;
            padding: 4rem 0 2rem;
        }
        
        footer h5 {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        footer a:hover {
            color: #fff;
        }
        
        footer ul {
            list-style: none;
            padding: 0;
        }
        
        footer ul li {
            margin-bottom: 0.75rem;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 2rem;
            margin-top: 3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero {
                padding: 6rem 0 4rem;
            }
        }
    </style>
    <?php echo $styles ?? ''; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-server me-2"></i><?php echo COMPANY_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/hosting">Web Hosting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/domains">Domains</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/contact">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="<?php echo BASE_URL; ?>/cart" class="nav-link me-3">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if (!empty($_SESSION['cart']['products']) || !empty($_SESSION['cart']['domains'])): ?>
                        <span class="badge bg-danger rounded-pill">
                            <?php echo count($_SESSION['cart']['products'] ?? []) + count($_SESSION['cart']['domains'] ?? []); ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <?php if ($this->session->isClientLoggedIn()): ?>
                    <a href="<?php echo CLIENT_URL; ?>" class="btn btn-login">
                        <i class="fas fa-user me-2"></i>My Account
                    </a>
                    <?php else: ?>
                    <a href="<?php echo CLIENT_URL; ?>/login" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <?php echo $content; ?>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-server me-2"></i><?php echo COMPANY_NAME; ?></h5>
                    <p class="text-white-50">
                        Premium web hosting solutions with 24/7 support, 99.9% uptime guarantee, and enterprise-grade infrastructure.
                    </p>
                    <div class="mt-3">
                        <a href="#" class="me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#"><i class="fab fa-github fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Services</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/hosting">Web Hosting</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/vps">VPS Hosting</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/domains">Domains</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/ssl">SSL Certificates</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Support</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/knowledgebase">Knowledge Base</a></li>
                        <li><a href="<?php echo CLIENT_URL; ?>/tickets">Open Ticket</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/status">Server Status</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contact">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Company</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/about">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/terms">Terms of Service</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/privacy">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/sla">SLA</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Account</h5>
                    <ul>
                        <li><a href="<?php echo CLIENT_URL; ?>/login">Client Login</a></li>
                        <li><a href="<?php echo CLIENT_URL; ?>/register">Register</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/cart">Shopping Cart</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom text-center text-white-50">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved. Powered by OpenWHM</p>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });
    </script>
    <?php echo $scripts ?? ''; ?>
</body>
</html>
