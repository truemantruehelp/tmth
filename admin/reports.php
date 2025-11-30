<?php
// admin/reports.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

$page_title = "Reports & Analytics";
include 'header.php';

// Get report type
$report_type = $_GET['type'] ?? 'overview';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get basic stats
$stats = getAdminStats();

// Get monthly revenue data
try {
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as revenue,
            COUNT(*) as donations
        FROM donations 
        WHERE status = 'completed' AND created_at BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute([$start_date, $end_date]);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $monthly_data = [];
}

// Get top campaigns
try {
    $stmt = $pdo->query("
        SELECT c.title, c.goal_amount, c.raised_amount,
               COUNT(d.id) as donation_count
        FROM campaigns c 
        LEFT JOIN donations d ON c.id = d.campaign_id AND d.status = 'completed'
        GROUP BY c.id
        ORDER BY c.raised_amount DESC
        LIMIT 10
    ");
    $top_campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $top_campaigns = [];
}

// Get payment method distribution
try {
    $stmt = $pdo->query("
        SELECT payment_method, 
               COUNT(*) as count,
               SUM(amount) as total
        FROM donations 
        WHERE status = 'completed'
        GROUP BY payment_method
        ORDER BY total DESC
    ");
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $payment_methods = [];
}
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Revenue</div>
                        <div class="h2 mb-0 font-weight-bold text-white">৳<?php echo number_format($stats['total_amount'], 0); ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-chart-line me-1"></i>
                            <span>All time</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-bangladeshi-taka-sign text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card donations-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Donations</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $stats['total_donations']; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-heart me-1"></i>
                            <span>Successful transactions</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-heart text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card campaigns-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Active Campaigns</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $stats['total_campaigns']; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-hands-helping me-1"></i>
                            <span>Currently running</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-hands-helping text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card users-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Users</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $stats['total_users']; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-users me-1"></i>
                            <span>Registered users</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Navigation -->
<div class="card shadow border-0 mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-pills nav-fill bg-light rounded p-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'overview' ? 'active' : ''; ?>" href="reports.php?type=overview">
                    <i class="fas fa-chart-pie me-2"></i>Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'donations' ? 'active' : ''; ?>" href="reports.php?type=donations">
                    <i class="fas fa-donate me-2"></i>Donations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'campaigns' ? 'active' : ''; ?>" href="reports.php?type=campaigns">
                    <i class="fas fa-hands-helping me-2"></i>Campaigns
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $report_type == 'users' ? 'active' : ''; ?>" href="reports.php?type=users">
                    <i class="fas fa-users me-2"></i>Users
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-gradient-info text-white py-3 border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-calendar me-2"></i>Date Range</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="type" value="<?php echo $report_type; ?>">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Apply Filter</button>
                <a href="reports.php?type=<?php echo $report_type; ?>" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if($report_type == 'overview') { ?>
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-xl-8 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-primary text-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-line me-2"></i>Revenue Overview</h6>
                    <small class="text-white-50">Last 6 months performance</small>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Pie Chart -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-info text-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-credit-card me-2"></i>Payment Methods</h6>
                    <small class="text-white-50">Distribution by method</small>
                </div>
                <div class="card-body">
                    <div class="chart-container-pie">
                        <canvas id="paymentMethodChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Campaigns -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0">
                <div class="card-header bg-gradient-success text-white py-3 border-0">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-trophy me-2"></i>Top Performing Campaigns</h6>
                    <small class="text-white-50">Based on total funds raised</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Campaign</th>
                                    <th>Raised Amount</th>
                                    <th>Goal Amount</th>
                                    <th>Progress</th>
                                    <th>Donations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_campaigns as $campaign): 
                                    $progress = min(100, ($campaign['raised_amount'] / $campaign['goal_amount']) * 100);
                                ?>
                                <tr class="hover-effect">
                                    <td><strong><?php echo htmlspecialchars($campaign['title']); ?></strong></td>
                                    <td class="text-success fw-bold">৳<?php echo number_format($campaign['raised_amount'], 0); ?></td>
                                    <td>৳<?php echo number_format($campaign['goal_amount'], 0); ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: <?php echo $progress; ?>%">
                                                <?php echo number_format($progress, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill"><?php echo $campaign['donation_count']; ?></span>
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

<?php } elseif($report_type == 'donations') { ?>
    <!-- Donations Report -->
    <div class="card shadow border-0">
        <div class="card-header bg-gradient-warning text-white py-3 border-0">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-donate me-2"></i>Donations Report</h6>
            <small class="text-white-50">Detailed donation records</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Donor</th>
                            <th>Campaign</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT d.*, c.title as campaign_title
                                FROM donations d 
                                LEFT JOIN campaigns c ON d.campaign_id = c.id 
                                WHERE d.created_at BETWEEN ? AND ?
                                ORDER BY d.created_at DESC
                            ");
                            $stmt->execute([$start_date, $end_date]);
                            $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach($donations as $donation) {
                        ?>
                        <tr class="hover-effect">
                            <td><small class="text-muted"><?php echo date('M j, Y', strtotime($donation['created_at'])); ?></small></td>
                            <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                            <td><?php echo $donation['campaign_title'] ? htmlspecialchars($donation['campaign_title']) : 'N/A'; ?></td>
                            <td class="text-success fw-bold">৳<?php echo number_format($donation['amount'], 0); ?></td>
                            <td>
                                <span class="badge bg-secondary rounded-pill text-capitalize">
                                    <?php echo str_replace('_', ' ', $donation['payment_method']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $donation['status'] == 'completed' ? 'success' : 'warning'; ?> rounded-pill">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php 
                            }
                        } catch(PDOException $e) {
                            echo '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-exclamation-circle me-2"></i>Error loading donations data</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php } elseif($report_type == 'campaigns') { ?>
    <!-- Campaigns Report -->
    <div class="card shadow border-0">
        <div class="card-header bg-gradient-success text-white py-3 border-0">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-hands-helping me-2"></i>Campaigns Performance Report</h6>
            <small class="text-white-50">Campaign analytics and performance metrics</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Campaign</th>
                            <th>Category</th>
                            <th>Goal Amount</th>
                            <th>Raised Amount</th>
                            <th>Progress</th>
                            <th>Donations</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT c.*, cat.name as category_name,
                                       COUNT(d.id) as donation_count
                                FROM campaigns c 
                                LEFT JOIN categories cat ON c.category_id = cat.id 
                                LEFT JOIN donations d ON c.id = d.campaign_id AND d.status = 'completed'
                                WHERE c.created_at BETWEEN ? AND ?
                                GROUP BY c.id
                                ORDER BY c.raised_amount DESC
                            ");
                            $stmt->execute([$start_date, $end_date]);
                            $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach($campaigns as $campaign) {
                                $progress = min(100, ($campaign['raised_amount'] / $campaign['goal_amount']) * 100);
                        ?>
                        <tr class="hover-effect">
                            <td><strong><?php echo htmlspecialchars($campaign['title']); ?></strong></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($campaign['category_name']); ?></span></td>
                            <td>৳<?php echo number_format($campaign['goal_amount'], 0); ?></td>
                            <td class="text-success fw-bold">৳<?php echo number_format($campaign['raised_amount'], 0); ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: <?php echo $progress; ?>%">
                                        <?php echo number_format($progress, 1); ?>%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?php echo $campaign['donation_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $campaign['status'] == 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo ucfirst($campaign['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php 
                            }
                        } catch(PDOException $e) {
                            echo '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-exclamation-circle me-2"></i>Error loading campaigns data</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php } elseif($report_type == 'users') { ?>
    <!-- Users Report -->
    <div class="card shadow border-0">
        <div class="card-header bg-gradient-info text-white py-3 border-0">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-users me-2"></i>Users Activity Report</h6>
            <small class="text-white-50">User statistics and donation activity</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Donations</th>
                            <th>Total Donated</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT u.*, 
                                       COUNT(d.id) as donation_count,
                                       SUM(d.amount) as total_donated
                                FROM users u 
                                LEFT JOIN donations d ON u.id = d.donor_id AND d.status = 'completed'
                                WHERE u.created_at BETWEEN ? AND ?
                                GROUP BY u.id
                                ORDER BY u.created_at DESC
                            ");
                            $stmt->execute([$start_date, $end_date]);
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach($users as $user) {
                        ?>
                        <tr class="hover-effect">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=0D8ABC&color=fff" 
                                         class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($user['name']); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?> rounded-pill">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="text-center fw-bold"><?php echo $user['donation_count']; ?></td>
                            <td class="text-success fw-bold">৳<?php echo number_format($user['total_donated'] ?? 0, 0); ?></td>
                            <td><small class="text-muted"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small></td>
                        </tr>
                        <?php 
                            }
                        } catch(PDOException $e) {
                            echo '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-exclamation-circle me-2"></i>Error loading users data</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_map(function($month) {
                return date('M Y', strtotime($month['month'] . '-01'));
            }, $monthly_data)); ?>,
            datasets: [{
                label: 'Revenue (৳)',
                data: <?php echo json_encode(array_column($monthly_data, 'revenue')); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#667eea',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return '৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '৳' + (value / 1000) + 'k';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Payment Method Chart
const paymentCtx = document.getElementById('paymentMethodChart');
if (paymentCtx) {
    const paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_map(function($method) { 
                return ucfirst(str_replace('_', ' ', $method['payment_method'])); 
            }, $payment_methods)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($payment_methods, 'total')); ?>,
                backgroundColor: [
                    '#667eea', '#4facfe', '#43e97b', '#fa709a', '#ff9a9e'
                ],
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ৳${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function exportReport(format) {
    alert(`Exporting report as ${format.toUpperCase()}...\n\nThis feature will generate and download the ${format.toUpperCase()} file.`);
    // In a real application, this would make an AJAX call to generate the report
}
</script>

<style>
    :root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --dark-gradient: linear-gradient(135deg, #434343 0%, #000000 100%);
}

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}
.stats-card {
    color: white;
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
}

.revenue-card { background: var(--primary-gradient); }
.donations-card { background: var(--success-gradient); }
.campaigns-card { background: var(--info-gradient); }
.users-card { background: var(--warning-gradient); }

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-white-20 {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.hover-effect {
    transition: all 0.3s ease;
}

.hover-effect:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.chart-container {
    position: relative;
    height: 250px;
    width: 100%;
}

.chart-container-pie {
    position: relative;
    height: 250px;
    width: 100%;
}

.nav-pills .nav-link.active {
    background: var(--primary-gradient);
    border: none;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-weight: 500;
}
</style>

<?php include 'footer.php'; ?>