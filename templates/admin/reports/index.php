<?php
$page = 'reports';
$title = 'Analytics & Reports';
ob_start();
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-chart-line me-2"></i>Analytics & Reports</h1>
        <small class="text-muted">Track your business metrics and performance</small>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Key Metrics -->
<div class="row g-4 mb-4">
    <!-- Total Revenue -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm bg-gradient-primary text-white">
            <div class="card-body">
                <small class="opacity-75">Total Revenue</small>
                <div class="h4 fw-bold">$<?php echo number_format($revenue['total'], 2); ?></div>
                <small class="opacity-75">Avg: $<?php echo number_format($revenue['average_daily'], 2); ?>/day</small>
            </div>
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm bg-gradient-success text-white">
            <div class="card-body">
                <small class="opacity-75">Total Orders</small>
                <div class="h4 fw-bold"><?php echo number_format($orderTrends['total_orders']); ?></div>
                <small class="opacity-75">Value: $<?php echo number_format($orderTrends['total_value'], 2); ?></small>
            </div>
        </div>
    </div>
    
    <!-- New Clients -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm bg-gradient-info text-white">
            <div class="card-body">
                <small class="opacity-75">New Clients</small>
                <div class="h4 fw-bold"><?php echo number_format($clientGrowth['new_clients']); ?></div>
                <small class="opacity-75">Period Growth</small>
            </div>
        </div>
    </div>
    
    <!-- Support Tickets -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm bg-gradient-warning text-white">
            <div class="card-body">
                <small class="opacity-75">Support Tickets</small>
                <div class="h4 fw-bold"><?php echo $ticketMetrics['total']; ?></div>
                <small class="opacity-75"><?php echo $ticketMetrics['closure_rate']; ?>% closed</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Breakdown -->
<div class="row g-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Revenue Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Payment Methods</h6>
            </div>
            <div class="card-body">
                <?php if (empty($paymentMethods)): ?>
                    <p class="text-muted text-center py-4">No payment data</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($paymentMethods as $method): ?>
                        <div class="list-group-item d-flex justify-content-between">
                            <div>
                                <strong><?php echo htmlspecialchars($method['payment_method'] ?: 'N/A'); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $method['count']; ?> orders</small>
                            </div>
                            <div class="text-right fw-bold">$<?php echo number_format($method['total'], 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Service Breakdown -->
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Service Breakdown</h6>
            </div>
            <div class="card-body">
                <?php if (empty($serviceBreakdown)): ?>
                    <p class="text-muted text-center py-4">No service data</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($serviceBreakdown as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name'] ?: 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $service['active']; ?></span>
                                    </td>
                                    <td class="text-right fw-bold"><?php echo $service['count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Clients -->
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Top Clients</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topClients)): ?>
                    <p class="text-muted text-center py-4">No client data</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Client</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-right">Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topClients as $client): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo ADMIN_URL; ?>/clients/<?php echo $client['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($client['email']); ?></small>
                                    </td>
                                    <td class="text-center"><?php echo $client['order_count']; ?></td>
                                    <td class="text-right fw-bold">$<?php echo number_format($client['total_spent'] ?: 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Metrics -->
<div class="row g-4 mt-2">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">Support Metrics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="h3 fw-bold"><?php echo $ticketMetrics['total']; ?></div>
                        <small class="text-muted">Total Tickets</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h3 fw-bold"><?php echo $ticketMetrics['closed']; ?></div>
                        <small class="text-muted">Closed Tickets</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h3 fw-bold"><?php echo $ticketMetrics['avg_response_hours']; ?>h</div>
                        <small class="text-muted">Avg Response Time</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h3 fw-bold text-success"><?php echo $ticketMetrics['closure_rate']; ?>%</div>
                        <small class="text-muted">Closure Rate</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.bg-gradient-success { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
.bg-gradient-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; }
.bg-gradient-warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueData = <?php echo json_encode($revenue['daily_breakdown']); ?>;
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => new Date(d.date).toLocaleDateString()),
            datasets: [{
                label: 'Revenue',
                data: revenueData.map(d => parseFloat(d.total)),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
