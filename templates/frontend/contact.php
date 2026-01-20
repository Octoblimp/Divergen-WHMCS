<?php
$title = 'Contact Us - ' . COMPANY_NAME;
ob_start();
?>

<!-- Hero Section -->
<section class="hero" style="padding: 10rem 0 4rem;">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p>We're here to help. Get in touch with our team.</p>
    </div>
</section>

<section style="margin-top: -3rem; padding-bottom: 5rem;">
    <div class="container">
        <?php if ($flash = $this->session->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($flash); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($flash = $this->session->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($flash); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Send us a Message</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo BASE_URL; ?>/contact" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" placeholder="How can we help?">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="message" rows="6" required placeholder="Describe your question or issue in detail..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="feature-icon mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-headset" style="font-size: 1.5rem;"></i>
                        </div>
                        <h5>24/7 Support</h5>
                        <p class="text-muted mb-0">Our support team is available around the clock to assist you.</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Contact Information</h5>
                        
                        <div class="d-flex mb-3">
                            <div class="text-primary me-3">
                                <i class="fas fa-envelope fa-lg"></i>
                            </div>
                            <div>
                                <strong>Email</strong><br>
                                <a href="mailto:support@example.com">support@example.com</a>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <div class="text-primary me-3">
                                <i class="fas fa-phone fa-lg"></i>
                            </div>
                            <div>
                                <strong>Phone</strong><br>
                                <a href="tel:+1234567890">+1 (234) 567-890</a>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="text-primary me-3">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <div>
                                <strong>Address</strong><br>
                                123 Business Street<br>
                                New York, NY 10001<br>
                                United States
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-question-circle me-2"></i>Quick Links</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="<?php echo BASE_URL; ?>/knowledgebase" class="text-decoration-none">
                                    <i class="fas fa-book me-2 text-muted"></i>Knowledge Base
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo CLIENT_URL; ?>/tickets" class="text-decoration-none">
                                    <i class="fas fa-ticket-alt me-2 text-muted"></i>Open Support Ticket
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo BASE_URL; ?>/status" class="text-decoration-none">
                                    <i class="fas fa-server me-2 text-muted"></i>Server Status
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo BASE_URL; ?>/faq" class="text-decoration-none">
                                    <i class="fas fa-comments me-2 text-muted"></i>FAQ
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
?>
