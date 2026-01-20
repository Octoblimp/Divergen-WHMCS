<?php
/**
 * Admin Fraud Settings Page
 */
$this->layout = 'admin.layouts.main';
$title = 'Fraud Protection Settings';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-shield-alt text-danger me-2"></i> Fraud Protection</h1>
</div>

<?php $this->flashMessages(); ?>

<form method="POST" action="<?php echo ADMIN_URL; ?>/settings/fraud/save">
    <div class="row">
        <!-- Main Settings -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Fraud Detection Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="fraud_enabled" name="fraud_enabled" 
                               <?php echo ($settings['fraud_enabled'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="fraud_enabled">
                            <strong>Enable Fraud Protection</strong>
                        </label>
                        <div class="text-muted small">Automatically screen all new orders for fraud</div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Checks to Perform</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_email" name="fraud_check_email" 
                                       <?php echo ($settings['fraud_check_email'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_email">
                                    <i class="fas fa-envelope text-primary me-1"></i> Email Verification
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_ip" name="fraud_check_ip" 
                                       <?php echo ($settings['fraud_check_ip'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_ip">
                                    <i class="fas fa-network-wired text-primary me-1"></i> IP Address Check
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_country" name="fraud_check_country" 
                                       <?php echo ($settings['fraud_check_country'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_country">
                                    <i class="fas fa-globe text-primary me-1"></i> Country Match
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_proxy" name="fraud_check_proxy" 
                                       <?php echo ($settings['fraud_check_proxy'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_proxy">
                                    <i class="fas fa-mask text-primary me-1"></i> Proxy/VPN Detection
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_address" name="fraud_check_address" 
                                       <?php echo ($settings['fraud_check_address'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_address">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i> Address Verification
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_phone" name="fraud_check_phone" 
                                       <?php echo ($settings['fraud_check_phone'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_phone">
                                    <i class="fas fa-phone text-primary me-1"></i> Phone Verification
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_check_order_frequency" name="fraud_check_order_frequency" 
                                       <?php echo ($settings['fraud_check_order_frequency'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_check_order_frequency">
                                    <i class="fas fa-clock text-primary me-1"></i> Order Frequency
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_block_vpn" name="fraud_block_vpn" 
                                       <?php echo ($settings['fraud_block_vpn'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="fraud_block_vpn">
                                    <i class="fas fa-ban text-danger me-1"></i> Block VPN/Proxy
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Risk Thresholds</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Auto-Review Threshold</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="fraud_auto_review_threshold" 
                                           value="<?php echo $settings['fraud_auto_review_threshold'] ?? 50; ?>" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="text-muted small">Orders with risk score above this require manual review</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Auto-Reject Threshold</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="fraud_auto_reject_threshold" 
                                           value="<?php echo $settings['fraud_auto_reject_threshold'] ?? 80; ?>" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="text-muted small">Orders with risk score above this are automatically rejected</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- API Integrations -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">API Integrations</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-key me-1"></i> IPQualityScore API Key
                        </label>
                        <input type="text" class="form-control" name="fraud_ipqualityscore_key" 
                               value="<?php echo htmlspecialchars($settings['fraud_ipqualityscore_key'] ?? ''); ?>" 
                               placeholder="Enter API key">
                        <div class="text-muted small">
                            Get a free API key at <a href="https://www.ipqualityscore.com/" target="_blank">ipqualityscore.com</a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-globe me-1"></i> MaxMind License Key
                        </label>
                        <input type="text" class="form-control" name="fraud_maxmind_license" 
                               value="<?php echo htmlspecialchars($settings['fraud_maxmind_license'] ?? ''); ?>" 
                               placeholder="Enter license key">
                        <div class="text-muted small">
                            For GeoIP country detection. Get key at <a href="https://www.maxmind.com/" target="_blank">maxmind.com</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Blacklists -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Blacklists</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Blocked Countries</label>
                                <textarea class="form-control" name="fraud_blocked_countries" rows="5" 
                                          placeholder="US&#10;RU&#10;CN"><?php echo htmlspecialchars($settings['fraud_blocked_countries'] ?? ''); ?></textarea>
                                <div class="text-muted small">Two-letter country codes, one per line</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Blocked Emails/Domains</label>
                                <textarea class="form-control" name="fraud_blocked_emails" rows="5" 
                                          placeholder="spam@example.com&#10;@tempmail.com"><?php echo htmlspecialchars($settings['fraud_blocked_emails'] ?? ''); ?></textarea>
                                <div class="text-muted small">Email addresses or domains, one per line</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Blocked IP Addresses</label>
                                <textarea class="form-control" name="fraud_blocked_ips" rows="5" 
                                          placeholder="192.168.1.100&#10;10.0.0.0/8&#10;*.*.*.1"><?php echo htmlspecialchars($settings['fraud_blocked_ips'] ?? ''); ?></textarea>
                                <div class="text-muted small">IPs, CIDR ranges, or wildcards</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Risk Score Legend</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-success px-3 py-2">0-24%</span>
                        <span>Minimal Risk</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-info px-3 py-2">25-49%</span>
                        <span>Low Risk</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning px-3 py-2">50-74%</span>
                        <span>Medium Risk - Review</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-danger px-3 py-2">75-100%</span>
                        <span>High Risk - Reject</span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> About Fraud Detection</h5>
                </div>
                <div class="card-body">
                    <p class="small">The fraud detection system analyzes orders based on multiple factors:</p>
                    <ul class="small">
                        <li>Email validity and history</li>
                        <li>IP address reputation</li>
                        <li>Geographic mismatches</li>
                        <li>Proxy/VPN usage</li>
                        <li>Address verification</li>
                        <li>Order velocity</li>
                        <li>Client history</li>
                    </ul>
                    <p class="small mb-0">Each factor adds to the risk score. High scores trigger manual review or automatic rejection.</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Recent Fraud Checks -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Fraud Checks</h5>
        <a href="<?php echo ADMIN_URL; ?>/fraud/logs" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order</th>
                    <th>Email</th>
                    <th>IP</th>
                    <th>Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentChecks)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No fraud checks recorded yet</td>
                </tr>
                <?php else: ?>
                    <?php foreach (array_slice($recentChecks, 0, 10) as $check): ?>
                    <tr>
                        <td><?php echo date('M j, g:i a', strtotime($check['created_at'])); ?></td>
                        <td><a href="<?php echo ADMIN_URL; ?>/orders/view/<?php echo $check['order_id']; ?>">#<?php echo $check['order_id']; ?></a></td>
                        <td><?php echo htmlspecialchars($check['email']); ?></td>
                        <td><code><?php echo htmlspecialchars($check['ip_address']); ?></code></td>
                        <td>
                            <?php
                            $score = $check['score'];
                            $class = 'success';
                            if ($score >= 75) $class = 'danger';
                            elseif ($score >= 50) $class = 'warning';
                            elseif ($score >= 25) $class = 'info';
                            ?>
                            <span class="badge bg-<?php echo $class; ?>"><?php echo $score; ?>%</span>
                        </td>
                        <td>
                            <?php
                            $actionClass = [
                                'allow' => 'success',
                                'review' => 'warning',
                                'reject' => 'danger'
                            ][$check['action']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $actionClass; ?>"><?php echo ucfirst($check['action']); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/templates/admin/layouts/main.php';
?>
