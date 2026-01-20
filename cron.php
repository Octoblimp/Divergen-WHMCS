<?php
/**
 * Cron Job Handler
 * 
 * Run this file via cron every 5 minutes:
 * * /5 * * * * php /path/to/cron.php
 */

define('OPENWH', true);
define('ROOT_PATH', __DIR__);

// Load configuration
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/core/Autoloader.php';

use OpenWHM\Core\Database;
use OpenWHM\Core\Hooks;
use OpenWHM\Models\Invoice;
use OpenWHM\Models\Service;
use OpenWHM\Models\Domain;

// Initialize
$db = Database::getInstance();
$hooks = Hooks::getInstance();

echo "OpenWHM Cron - " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";

/**
 * Task 1: Mark overdue invoices
 */
echo "Checking for overdue invoices... ";
$overdueCount = 0;

$invoices = $db->fetchAll(
    "SELECT id FROM {$db->table('invoices')} 
     WHERE status = 'unpaid' AND due_date < CURDATE()"
);

foreach ($invoices as $invoice) {
    $db->update('invoices', ['status' => 'overdue'], 'id = ?', [$invoice['id']]);
    $hooks->execute('InvoiceOverdue', ['invoice_id' => $invoice['id']]);
    $overdueCount++;
}

echo "$overdueCount marked as overdue.\n";

/**
 * Task 2: Generate recurring invoices
 */
echo "Generating recurring invoices... ";
$generatedCount = 0;

// Services due for renewal in next 14 days
$services = $db->fetchAll(
    "SELECT s.*, p.name as product_name 
     FROM {$db->table('services')} s
     LEFT JOIN {$db->table('products')} p ON s.product_id = p.id
     WHERE s.status = 'active' 
     AND s.next_due <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
     AND s.next_due >= CURDATE()
     AND NOT EXISTS (
         SELECT 1 FROM {$db->table('invoices')} i
         JOIN {$db->table('invoice_items')} ii ON i.id = ii.invoice_id
         WHERE ii.service_id = s.id AND i.status IN ('unpaid', 'paid') 
         AND i.created_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     )"
);

$invoiceModel = new Invoice();

foreach ($services as $service) {
    // Create invoice for this service
    $invoiceId = $invoiceModel->create([
        'client_id' => $service['client_id'],
        'status' => 'unpaid',
        'due_date' => $service['next_due']
    ]);
    
    $invoiceModel->addItem($invoiceId, [
        'description' => $service['product_name'] . ' - ' . ucfirst($service['billing_cycle']),
        'amount' => $service['amount'],
        'service_id' => $service['id']
    ]);
    
    $hooks->execute('InvoiceCreated', ['invoice_id' => $invoiceId]);
    $generatedCount++;
}

echo "$generatedCount invoices generated.\n";

/**
 * Task 3: Suspend overdue services
 */
echo "Checking services for suspension... ";
$suspendedCount = 0;

// Services with unpaid invoices overdue by more than 3 days
$toSuspend = $db->fetchAll(
    "SELECT DISTINCT s.id 
     FROM {$db->table('services')} s
     JOIN {$db->table('invoice_items')} ii ON s.id = ii.service_id
     JOIN {$db->table('invoices')} i ON ii.invoice_id = i.id
     WHERE s.status = 'active'
     AND i.status = 'overdue'
     AND i.due_date < DATE_SUB(CURDATE(), INTERVAL 3 DAY)"
);

$serviceModel = new Service();

foreach ($toSuspend as $service) {
    $serviceModel->suspend($service['id'], 'Overdue payment');
    $suspendedCount++;
}

echo "$suspendedCount services suspended.\n";

/**
 * Task 4: Domain expiry reminders
 */
echo "Checking domain expirations... ";
$expiringCount = 0;

// Domains expiring in 30, 14, 7 days
$expiringDomains = $db->fetchAll(
    "SELECT d.*, c.email, c.firstname 
     FROM {$db->table('domains')} d
     JOIN {$db->table('clients')} c ON d.client_id = c.id
     WHERE d.status = 'active'
     AND d.expiry_date IN (
         DATE_ADD(CURDATE(), INTERVAL 30 DAY),
         DATE_ADD(CURDATE(), INTERVAL 14 DAY),
         DATE_ADD(CURDATE(), INTERVAL 7 DAY)
     )"
);

foreach ($expiringDomains as $domain) {
    $daysUntilExpiry = (strtotime($domain['expiry_date']) - time()) / 86400;
    $hooks->execute('DomainExpiryReminder', [
        'domain_id' => $domain['id'],
        'days_until_expiry' => round($daysUntilExpiry)
    ]);
    $expiringCount++;
}

echo "$expiringCount domain reminders sent.\n";

/**
 * Task 5: Sync domain status with registrar
 */
echo "Syncing domain status... ";
$syncedCount = 0;

// Only sync domains not synced in last 24 hours
$domainsToSync = $db->fetchAll(
    "SELECT * FROM {$db->table('domains')} 
     WHERE status = 'active'
     AND (last_sync IS NULL OR last_sync < DATE_SUB(NOW(), INTERVAL 24 HOUR))
     LIMIT 50"
);

$domainModel = new Domain();

foreach ($domainsToSync as $domain) {
    $domainModel->sync($domain['id']);
    $syncedCount++;
}

echo "$syncedCount domains synced.\n";

/**
 * Task 6: Auto-terminate cancelled services
 */
echo "Processing termination requests... ";
$terminatedCount = 0;

$toTerminate = $db->fetchAll(
    "SELECT cr.*, s.status as service_status
     FROM {$db->table('cancellation_requests')} cr
     JOIN {$db->table('services')} s ON cr.service_id = s.id
     WHERE cr.status = 'approved'
     AND cr.termination_date <= CURDATE()
     AND s.status != 'terminated'"
);

foreach ($toTerminate as $request) {
    $serviceModel->terminate($request['service_id'], 'Cancellation request');
    $db->update('cancellation_requests', ['status' => 'completed'], 'id = ?', [$request['id']]);
    $terminatedCount++;
}

echo "$terminatedCount services terminated.\n";

/**
 * Task 7: Clean up old logs
 */
echo "Cleaning up old logs... ";
$db->query(
    "DELETE FROM {$db->table('logs')} WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
);
echo "Done.\n";

/**
 * Task 8: Update currency exchange rates
 */
echo "Updating exchange rates... ";
// This would call an exchange rate API in production
echo "Skipped.\n";

echo "========================================\n";
echo "Cron completed successfully.\n";

// Fire cron complete hook
$hooks->execute('CronCompleted', [
    'overdue_invoices' => $overdueCount,
    'generated_invoices' => $generatedCount,
    'suspended_services' => $suspendedCount,
    'expiring_domains' => $expiringCount
]);
