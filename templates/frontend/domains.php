<?php
$title = 'Domain Registration - ' . COMPANY_NAME;
ob_start();
?>

<!-- Hero Section -->
<section class="hero" style="padding: 10rem 0 4rem;">
    <div class="container text-center">
        <h1>Find Your Perfect Domain</h1>
        <p>Register your domain name starting at just $9.99/year</p>
        
        <!-- Domain Search -->
        <div class="domain-search mt-4 mx-auto" style="max-width: 700px;">
            <form id="domainSearchForm" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control" id="domainInput" name="domain" placeholder="Enter your domain name..." required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </form>
            
            <!-- Search Results -->
            <div id="domainResults" class="text-start mt-4" style="display: none;">
                <div class="card">
                    <div class="card-body" id="resultsContent">
                        <!-- Results will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Pricing -->
<section class="pricing" style="margin-top: -3rem;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Domain Pricing</h2>
            <p class="section-subtitle">Transparent pricing with no hidden fees</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Extension</th>
                                    <th class="text-center">Registration</th>
                                    <th class="text-center">Renewal</th>
                                    <th class="text-center">Transfer</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pricing as $tld): ?>
                                <tr>
                                    <td>
                                        <strong class="fs-5"><?php echo htmlspecialchars($tld['tld']); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success fw-bold">$<?php echo number_format($tld['register_price'], 2); ?></span>
                                        <small class="text-muted">/year</small>
                                    </td>
                                    <td class="text-center">
                                        <span>$<?php echo number_format($tld['renew_price'], 2); ?></span>
                                        <small class="text-muted">/year</small>
                                    </td>
                                    <td class="text-center">
                                        <span>$<?php echo number_format($tld['transfer_price'], 2); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-primary search-tld" data-tld="<?php echo htmlspecialchars($tld['tld']); ?>">
                                            Search
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Features -->
<section class="features py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Every Domain Includes</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="feature-card">
                    <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                        <i class="fas fa-lock" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="mt-3">WHOIS Privacy</h5>
                    <p class="text-muted small">Free privacy protection to keep your personal information private.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-card">
                    <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                        <i class="fas fa-server" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="mt-3">DNS Management</h5>
                    <p class="text-muted small">Full control over your DNS records with our easy-to-use manager.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-card">
                    <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                        <i class="fas fa-exchange-alt" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="mt-3">Easy Transfers</h5>
                    <p class="text-muted small">Transfer domains to us with no downtime and free extension.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-card">
                    <div class="feature-icon mx-auto" style="width: 60px; height: 60px;">
                        <i class="fas fa-envelope" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="mt-3">Email Forwarding</h5>
                    <p class="text-muted small">Forward emails from your domain to any existing email address.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('domainSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const domain = document.getElementById('domainInput').value;
    const resultsDiv = document.getElementById('domainResults');
    const resultsContent = document.getElementById('resultsContent');
    
    resultsContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Checking availability...</p></div>';
    resultsDiv.style.display = 'block';
    
    fetch('<?php echo BASE_URL; ?>/domains/check?domain=' + encodeURIComponent(domain))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultsContent.innerHTML = '<div class="alert alert-danger mb-0">' + data.error + '</div>';
            } else if (data.available) {
                resultsContent.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                            <strong class="fs-5">${data.domain}</strong>
                            <span class="badge bg-success ms-2">Available!</span>
                        </div>
                        <div>
                            <span class="fs-4 fw-bold text-primary me-3">$${data.pricing.register}/year</span>
                            <form action="<?php echo BASE_URL; ?>/cart/add-domain" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="domain" value="${data.domain}">
                                <input type="hidden" name="type" value="register">
                                <input type="hidden" name="period" value="1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                `;
            } else {
                resultsContent.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                            <strong class="fs-5">${data.domain}</strong>
                            <span class="badge bg-danger ms-2">Not Available</span>
                        </div>
                        <div>
                            <a href="#" class="btn btn-outline-primary">Try Different Extension</a>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultsContent.innerHTML = '<div class="alert alert-danger mb-0">Error checking domain. Please try again.</div>';
        });
});

// Search TLD buttons
document.querySelectorAll('.search-tld').forEach(btn => {
    btn.addEventListener('click', function() {
        const tld = this.dataset.tld;
        document.getElementById('domainInput').value = 'example' + tld;
        document.getElementById('domainInput').focus();
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
?>
