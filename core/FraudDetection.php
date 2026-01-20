<?php
/**
 * Fraud Detection & Security System
 * Multi-layer fraud prevention like WHMCS
 */

namespace OpenWHM\Core;

class FraudDetection
{
    protected $db;
    protected $config = [];
    protected $risks = [];
    protected $score = 0;
    protected $maxScore = 100;
    protected $details = [];
    
    // Risk thresholds
    const RISK_LOW = 25;
    const RISK_MEDIUM = 50;
    const RISK_HIGH = 75;
    
    // Actions
    const ACTION_ALLOW = 'allow';
    const ACTION_REVIEW = 'review';
    const ACTION_REJECT = 'reject';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadConfig();
    }
    
    /**
     * Load fraud settings
     */
    protected function loadConfig(): void
    {
        $settings = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'fraud_%'"
        );
        
        foreach ($settings as $s) {
            $key = str_replace('fraud_', '', $s['setting_key']);
            $this->config[$key] = $s['setting_value'];
        }
        
        // Defaults
        $this->config = array_merge([
            'enabled' => true,
            'check_email' => true,
            'check_ip' => true,
            'check_country' => true,
            'check_proxy' => true,
            'check_address' => true,
            'check_phone' => true,
            'check_order_frequency' => true,
            'block_free_email' => false,
            'block_vpn' => false,
            'auto_review_threshold' => 50,
            'auto_reject_threshold' => 80,
            'blocked_countries' => '',
            'blocked_emails' => '',
            'blocked_ips' => '',
            'maxmind_license' => '',
            'ipqualityscore_key' => ''
        ], $this->config);
    }
    
    /**
     * Check order for fraud
     */
    public function checkOrder(array $orderData): array
    {
        if (!$this->config['enabled']) {
            return [
                'passed' => true,
                'score' => 0,
                'action' => self::ACTION_ALLOW,
                'risks' => [],
                'details' => []
            ];
        }
        
        $this->score = 0;
        $this->risks = [];
        $this->details = [];
        
        $clientData = [
            'email' => $orderData['email'] ?? '',
            'firstname' => $orderData['firstname'] ?? '',
            'lastname' => $orderData['lastname'] ?? '',
            'address1' => $orderData['address1'] ?? '',
            'address2' => $orderData['address2'] ?? '',
            'city' => $orderData['city'] ?? '',
            'state' => $orderData['state'] ?? '',
            'postcode' => $orderData['postcode'] ?? '',
            'country' => $orderData['country'] ?? '',
            'phone' => $orderData['phone'] ?? '',
            'ip_address' => $orderData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
            'client_id' => $orderData['client_id'] ?? null,
            'total' => $orderData['total'] ?? 0
        ];
        
        // Run all checks
        if ($this->config['check_email']) {
            $this->checkEmail($clientData['email']);
        }
        
        if ($this->config['check_ip']) {
            $this->checkIP($clientData['ip_address']);
        }
        
        if ($this->config['check_country']) {
            $this->checkCountry($clientData['country'], $clientData['ip_address']);
        }
        
        if ($this->config['check_proxy']) {
            $this->checkProxy($clientData['ip_address']);
        }
        
        if ($this->config['check_address']) {
            $this->checkAddress($clientData);
        }
        
        if ($this->config['check_phone']) {
            $this->checkPhone($clientData['phone'], $clientData['country']);
        }
        
        if ($this->config['check_order_frequency']) {
            $this->checkOrderFrequency($clientData);
        }
        
        // Check against blacklists
        $this->checkBlacklists($clientData);
        
        // Check existing client history
        if ($clientData['client_id']) {
            $this->checkClientHistory($clientData['client_id']);
        }
        
        // Determine action
        $action = $this->determineAction();
        
        // Log the check
        $this->logFraudCheck($orderData, $action);
        
        return [
            'passed' => ($action !== self::ACTION_REJECT),
            'score' => $this->score,
            'action' => $action,
            'risks' => $this->risks,
            'details' => $this->details,
            'level' => $this->getRiskLevel()
        ];
    }
    
    /**
     * Check email for fraud indicators
     */
    protected function checkEmail(string $email): void
    {
        if (empty($email)) {
            $this->addRisk('Invalid Email', 'No email address provided', 20);
            return;
        }
        
        // Check format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addRisk('Invalid Email Format', 'Email address format is invalid', 25);
            return;
        }
        
        $domain = substr($email, strpos($email, '@') + 1);
        
        // Free email providers
        if ($this->config['block_free_email']) {
            $freeProviders = [
                'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
                'aol.com', 'mail.com', 'protonmail.com', 'yandex.com',
                'zoho.com', 'gmx.com', 'icloud.com'
            ];
            
            if (in_array(strtolower($domain), $freeProviders)) {
                $this->addRisk('Free Email', 'Using free email provider', 10);
            }
        }
        
        // Disposable email check
        $disposableProviders = [
            'tempmail.com', 'throwaway.email', 'guerrillamail.com', 'mailinator.com',
            '10minutemail.com', 'temp-mail.org', 'fakeinbox.com', 'trashmail.com',
            'yopmail.com', 'sharklasers.com', 'guerrillamail.info'
        ];
        
        foreach ($disposableProviders as $provider) {
            if (stripos($domain, $provider) !== false) {
                $this->addRisk('Disposable Email', 'Using disposable email service', 40);
                break;
            }
        }
        
        // Check MX records
        if (!checkdnsrr($domain, 'MX')) {
            $this->addRisk('Invalid Email Domain', 'No MX records found for email domain', 30);
        }
        
        // Check if email was used in fraud before
        $fraudRecord = $this->db->fetch(
            "SELECT * FROM fraud_records WHERE email = ? AND status = 'fraud'",
            [$email]
        );
        
        if ($fraudRecord) {
            $this->addRisk('Known Fraud Email', 'Email associated with previous fraud', 50);
        }
        
        $this->details['email'] = [
            'value' => $email,
            'domain' => $domain,
            'checked' => true
        ];
    }
    
    /**
     * Check IP address
     */
    protected function checkIP(string $ip): void
    {
        if (empty($ip)) {
            $this->addRisk('No IP', 'IP address not detected', 15);
            return;
        }
        
        // Check if IP was used in fraud before
        $fraudRecord = $this->db->fetch(
            "SELECT * FROM fraud_records WHERE ip_address = ? AND status = 'fraud' AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)",
            [$ip]
        );
        
        if ($fraudRecord) {
            $this->addRisk('Known Fraud IP', 'IP address associated with recent fraud', 45);
        }
        
        // Check multiple accounts from same IP
        $accountCount = $this->db->fetch(
            "SELECT COUNT(DISTINCT client_id) as count FROM orders WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$ip]
        );
        
        if ($accountCount && $accountCount['count'] > 3) {
            $this->addRisk('Multiple Accounts IP', "IP used by {$accountCount['count']} accounts in 7 days", 25);
        }
        
        $this->details['ip'] = [
            'value' => $ip,
            'checked' => true
        ];
    }
    
    /**
     * Check for country mismatch
     */
    protected function checkCountry(string $billingCountry, string $ip): void
    {
        // Get IP country using external service or database
        $ipCountry = $this->getIPCountry($ip);
        
        if ($ipCountry && $billingCountry && strtoupper($ipCountry) !== strtoupper($billingCountry)) {
            $this->addRisk('Country Mismatch', "IP country ($ipCountry) differs from billing country ($billingCountry)", 25);
        }
        
        // Check blocked countries
        $blocked = array_filter(array_map('trim', explode(',', $this->config['blocked_countries'] ?? '')));
        if (!empty($blocked) && in_array(strtoupper($billingCountry), $blocked)) {
            $this->addRisk('Blocked Country', 'Order from blocked country', 50);
        }
        
        $this->details['country'] = [
            'billing' => $billingCountry,
            'ip' => $ipCountry,
            'match' => ($ipCountry === $billingCountry)
        ];
    }
    
    /**
     * Check for proxy/VPN/Tor
     */
    protected function checkProxy(string $ip): void
    {
        // Use IPQualityScore API if configured
        if (!empty($this->config['ipqualityscore_key'])) {
            $result = $this->checkIPQualityScore($ip);
            
            if ($result) {
                if ($result['proxy'] ?? false) {
                    $this->addRisk('Proxy Detected', 'Connection via proxy server', 20);
                }
                if ($result['vpn'] ?? false) {
                    $level = $this->config['block_vpn'] ? 35 : 15;
                    $this->addRisk('VPN Detected', 'Connection via VPN', $level);
                }
                if ($result['tor'] ?? false) {
                    $this->addRisk('Tor Detected', 'Connection via Tor network', 40);
                }
                if (($result['fraud_score'] ?? 0) > 75) {
                    $this->addRisk('High IP Risk Score', "IPQualityScore: {$result['fraud_score']}", 30);
                }
            }
        }
        
        // Basic proxy header checks
        $proxyHeaders = [
            'HTTP_VIA', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED_FOR',
            'HTTP_X_FORWARDED', 'HTTP_FORWARDED', 'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR_IP', 'VIA', 'X_FORWARDED_FOR', 'FORWARDED_FOR',
            'X_FORWARDED', 'FORWARDED', 'CLIENT_IP', 'FORWARDED_FOR_IP'
        ];
        
        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $this->addRisk('Proxy Headers', 'Proxy-related HTTP headers detected', 10);
                break;
            }
        }
    }
    
    /**
     * Check address validity
     */
    protected function checkAddress(array $data): void
    {
        // Check for test/fake addresses
        $fakePatterns = [
            '/^123\s+main/i', '/^test\s+/i', '/^fake\s+/i', '/^asdf/i',
            '/^xxx/i', '/^abc\s*123/i', '/^no\s+address/i'
        ];
        
        $address = $data['address1'] . ' ' . $data['address2'];
        
        foreach ($fakePatterns as $pattern) {
            if (preg_match($pattern, $address)) {
                $this->addRisk('Suspicious Address', 'Address appears to be fake or test data', 30);
                break;
            }
        }
        
        // Check for PO Box (sometimes higher risk for physical goods)
        if (preg_match('/p\.?\s*o\.?\s*box/i', $address)) {
            $this->addRisk('PO Box', 'Using PO Box address', 5);
        }
        
        // Check postal code format based on country
        $this->validatePostalCode($data['postcode'], $data['country']);
        
        $this->details['address'] = [
            'address' => $address,
            'city' => $data['city'],
            'state' => $data['state'],
            'postcode' => $data['postcode'],
            'country' => $data['country']
        ];
    }
    
    /**
     * Validate postal code format
     */
    protected function validatePostalCode(string $postcode, string $country): void
    {
        $patterns = [
            'US' => '/^\d{5}(-\d{4})?$/',
            'CA' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/i',
            'UK' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            'GB' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            'AU' => '/^\d{4}$/',
            'DE' => '/^\d{5}$/',
            'FR' => '/^\d{5}$/'
        ];
        
        $country = strtoupper($country);
        
        if (isset($patterns[$country]) && !preg_match($patterns[$country], $postcode)) {
            $this->addRisk('Invalid Postal Code', 'Postal code format incorrect for country', 15);
        }
    }
    
    /**
     * Check phone number
     */
    protected function checkPhone(string $phone, string $country): void
    {
        if (empty($phone)) {
            $this->addRisk('No Phone', 'No phone number provided', 10);
            return;
        }
        
        // Remove non-digits
        $digits = preg_replace('/\D/', '', $phone);
        
        // Check minimum length
        if (strlen($digits) < 7) {
            $this->addRisk('Invalid Phone', 'Phone number too short', 15);
        }
        
        // Check for obvious fakes
        if (preg_match('/^(1234567|0000000|1111111|9999999)/', $digits)) {
            $this->addRisk('Fake Phone', 'Phone number appears fake', 25);
        }
        
        $this->details['phone'] = [
            'value' => $phone,
            'digits' => $digits
        ];
    }
    
    /**
     * Check order frequency
     */
    protected function checkOrderFrequency(array $data): void
    {
        // Check orders from same IP in last hour
        $recentOrders = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$data['ip_address']]
        );
        
        if ($recentOrders && $recentOrders['count'] > 3) {
            $this->addRisk('Rapid Orders', "Multiple orders in short period", 30);
        }
        
        // Check for similar email patterns
        $emailDomain = substr($data['email'], strpos($data['email'] ?? '', '@') + 1);
        $similarEmails = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE email LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            ['%@' . $emailDomain]
        );
        
        if ($similarEmails && $similarEmails['count'] > 5) {
            $this->addRisk('Email Pattern', "Multiple orders from same email domain", 15);
        }
    }
    
    /**
     * Check against blacklists
     */
    protected function checkBlacklists(array $data): void
    {
        // Blocked emails
        $blockedEmails = array_filter(array_map('trim', explode("\n", $this->config['blocked_emails'] ?? '')));
        foreach ($blockedEmails as $blocked) {
            if (stripos($data['email'], $blocked) !== false) {
                $this->addRisk('Blacklisted Email', 'Email on block list', 50);
                break;
            }
        }
        
        // Blocked IPs
        $blockedIPs = array_filter(array_map('trim', explode("\n", $this->config['blocked_ips'] ?? '')));
        foreach ($blockedIPs as $blocked) {
            if ($this->ipMatches($data['ip_address'], $blocked)) {
                $this->addRisk('Blacklisted IP', 'IP address on block list', 50);
                break;
            }
        }
    }
    
    /**
     * Check if IP matches pattern (supports CIDR)
     */
    protected function ipMatches(string $ip, string $pattern): bool
    {
        if (strpos($pattern, '/') !== false) {
            // CIDR notation
            list($subnet, $mask) = explode('/', $pattern);
            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $mask);
            return ($ip & $mask) === ($subnet & $mask);
        }
        
        // Wildcard support
        if (strpos($pattern, '*') !== false) {
            $pattern = str_replace('.', '\.', $pattern);
            $pattern = str_replace('*', '\d+', $pattern);
            return preg_match('/^' . $pattern . '$/', $ip);
        }
        
        return $ip === $pattern;
    }
    
    /**
     * Check client history
     */
    protected function checkClientHistory(int $clientId): void
    {
        // Check for previous fraud
        $fraudOrders = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE client_id = ? AND fraud_status = 'fraud'",
            [$clientId]
        );
        
        if ($fraudOrders && $fraudOrders['count'] > 0) {
            $this->addRisk('Previous Fraud', 'Client has previous fraudulent orders', 45);
        }
        
        // Check for chargebacks
        $chargebacks = $this->db->fetch(
            "SELECT COUNT(*) as count FROM transactions WHERE client_id = ? AND type = 'chargeback'",
            [$clientId]
        );
        
        if ($chargebacks && $chargebacks['count'] > 0) {
            $this->addRisk('Chargeback History', "Client has {$chargebacks['count']} chargeback(s)", 40);
        }
        
        // Check failed payments
        $failedPayments = $this->db->fetch(
            "SELECT COUNT(*) as count FROM transactions WHERE client_id = ? AND status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$clientId]
        );
        
        if ($failedPayments && $failedPayments['count'] > 3) {
            $this->addRisk('Payment Failures', 'Multiple failed payments recently', 20);
        }
    }
    
    /**
     * Get country from IP address
     */
    protected function getIPCountry(string $ip): ?string
    {
        // Try MaxMind if configured
        if (!empty($this->config['maxmind_license'])) {
            $country = $this->getMaxMindCountry($ip);
            if ($country) return $country;
        }
        
        // Fallback to free API
        $url = "http://ip-api.com/json/{$ip}?fields=countryCode";
        $response = @file_get_contents($url);
        
        if ($response) {
            $data = json_decode($response, true);
            return $data['countryCode'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Check IP using IPQualityScore
     */
    protected function checkIPQualityScore(string $ip): ?array
    {
        $apiKey = $this->config['ipqualityscore_key'];
        $url = "https://ipqualityscore.com/api/json/ip/{$apiKey}/{$ip}?strictness=1&allow_public_access_points=true";
        
        $response = @file_get_contents($url);
        
        if ($response) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Get MaxMind country
     */
    protected function getMaxMindCountry(string $ip): ?string
    {
        // Implementation would require MaxMind GeoIP2 library
        return null;
    }
    
    /**
     * Add a fraud risk
     */
    protected function addRisk(string $title, string $description, int $score): void
    {
        $this->risks[] = [
            'title' => $title,
            'description' => $description,
            'score' => $score
        ];
        
        $this->score = min($this->maxScore, $this->score + $score);
    }
    
    /**
     * Determine action based on score
     */
    protected function determineAction(): string
    {
        if ($this->score >= ($this->config['auto_reject_threshold'] ?? 80)) {
            return self::ACTION_REJECT;
        }
        
        if ($this->score >= ($this->config['auto_review_threshold'] ?? 50)) {
            return self::ACTION_REVIEW;
        }
        
        return self::ACTION_ALLOW;
    }
    
    /**
     * Get risk level string
     */
    protected function getRiskLevel(): string
    {
        if ($this->score >= self::RISK_HIGH) return 'high';
        if ($this->score >= self::RISK_MEDIUM) return 'medium';
        if ($this->score >= self::RISK_LOW) return 'low';
        return 'minimal';
    }
    
    /**
     * Log fraud check
     */
    protected function logFraudCheck(array $orderData, string $action): void
    {
        $this->db->insert('fraud_checks', [
            'order_id' => $orderData['order_id'] ?? null,
            'client_id' => $orderData['client_id'] ?? null,
            'email' => $orderData['email'] ?? '',
            'ip_address' => $orderData['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
            'score' => $this->score,
            'action' => $action,
            'risks' => json_encode($this->risks),
            'details' => json_encode($this->details),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Mark order as fraud
     */
    public function markAsFraud(int $orderId, string $reason = ''): bool
    {
        $order = $this->db->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
        
        if (!$order) return false;
        
        // Update order
        $this->db->update('orders', [
            'fraud_status' => 'fraud',
            'fraud_notes' => $reason
        ], 'id = ?', [$orderId]);
        
        // Add to fraud records
        $this->db->insert('fraud_records', [
            'order_id' => $orderId,
            'client_id' => $order['client_id'],
            'email' => $order['email'] ?? '',
            'ip_address' => $order['ip_address'] ?? '',
            'reason' => $reason,
            'status' => 'fraud',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log admin action
        $this->db->insert('admin_log', [
            'admin_id' => $_SESSION['admin']['id'] ?? 0,
            'action' => 'fraud_marked',
            'description' => "Order #{$orderId} marked as fraud: {$reason}",
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    /**
     * Clear fraud status
     */
    public function clearFraud(int $orderId): bool
    {
        $this->db->update('orders', [
            'fraud_status' => 'cleared',
            'fraud_notes' => 'Manually cleared by admin'
        ], 'id = ?', [$orderId]);
        
        return true;
    }
}
