<?php
/**
 * Default Frontend Page Template
 */
$title = $page['meta_title'] ?? $page['title'] ?? 'Page';
$metaDescription = $page['meta_description'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title); ?> - <?php echo COMPANY_NAME; ?></title>
    <?php if ($metaDescription): ?>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #5bc0de;
            --primary-dark: #31b0d5;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
        }
        
        .navbar-main {
            background: linear-gradient(to bottom, var(--primary), var(--primary-dark));
        }
        
        .navbar-main .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        
        .navbar-main .nav-link:hover {
            color: #fff !important;
        }
        
        .hero-section {
            min-height: 400px;
            display: flex;
            align-items: center;
        }
        
        .hero-overlay {
            width: 100%;
            padding: 80px 0;
        }
        
        footer {
            background: #333;
            color: #fff;
            padding: 40px 0;
        }
        
        footer a {
            color: var(--primary);
        }
        
        .page-content {
            padding: 40px 0;
            min-height: 50vh;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-main">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="<?php echo BASE_URL; ?>">
                <?php echo COMPANY_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/hosting">Hosting</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/domains">Domains</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/contact">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo CLIENT_URL; ?>/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo CLIENT_URL; ?>/register">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Page Content -->
    <div class="page-content">
        <div class="container">
            <h1 class="mb-4"><?php echo htmlspecialchars($page['title'] ?? ''); ?></h1>
            
            <?php
            // Render page content
            if (!empty($content)) {
                echo $content;
            } elseif (!empty($page['content'])) {
                echo $page['content'];
            }
            ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo COMPANY_NAME; ?></h5>
                    <p class="text-muted">Your trusted hosting partner.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>/about">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/terms">Terms of Service</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/privacy">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo CLIENT_URL; ?>/tickets">Submit Ticket</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/knowledgebase">Knowledgebase</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contact">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: #555;">
            <div class="text-center text-muted">
                &copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved. Powered by OpenWHM.
            </div>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
