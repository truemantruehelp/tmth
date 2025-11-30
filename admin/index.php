<?php
// admin/index.php
require_once '../includes/config.php';

// Redirect to login if not admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$page_title = "Dashboard";
include 'header.php';

// Get admin stats
$stats = getAdminStats();

// Temporary direct queries for debugging
try {
    // Get recent donations directly
    $stmt = $pdo->prepare("
        SELECT d.*, c.title as campaign_title, 
               COALESCE(u.name, d.donor_name) as donor_name 
        FROM donations d 
        LEFT JOIN campaigns c ON d.campaign_id = c.id 
        LEFT JOIN users u ON d.donor_id = u.id 
        ORDER BY d.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent campaigns directly  
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name
        FROM campaigns c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        WHERE c.status = 'active'
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "<!-- Database Error: " . $e->getMessage() . " -->";
    $recent_donations = [];
    $recent_campaigns = [];
}

// Get data for charts
try {
    // Monthly revenue data
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as revenue,
            COUNT(*) as donations
        FROM donations 
        WHERE status = 'completed' 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $stmt->execute();
    $monthly_data = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // Payment method distribution
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
    
    // Campaign performance
    $stmt = $pdo->query("
        SELECT title, raised_amount, goal_amount
        FROM campaigns 
        WHERE status = 'active'
        ORDER BY raised_amount DESC
        LIMIT 5
    ");
    $top_campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $monthly_data = [];
    $payment_methods = [];
    $top_campaigns = [];
}

// Demo data for charts if no real data
if (empty($monthly_data)) {
    $monthly_data = [
        ['month' => '2025-04', 'revenue' => 150000, 'donations' => 45],
        ['month' => '2025-05', 'revenue' => 180000, 'donations' => 52],
        ['month' => '2025-06', 'revenue' => 220000, 'donations' => 61],
        ['month' => '2025-07', 'revenue' => 190000, 'donations' => 55],
        ['month' => '2025-08', 'revenue' => 250000, 'donations' => 68],
        ['month' => '2025-09', 'revenue' => 280000, 'donations' => 72]
    ];
}

if (empty($payment_methods)) {
    $payment_methods = [
        ['payment_method' => 'bikash', 'count' => 45, 'total' => 850000],
        ['payment_method' => 'cash', 'count' => 32, 'total' => 420000],
        ['payment_method' => 'bank_transfer', 'count' => 18, 'total' => 280000],
        ['payment_method' => 'stripe', 'count' => 12, 'total' => 150000]
    ];
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
                            Total  Revenue</div>
                        <div class="h2 mb-0 font-weight-bold text-white">৳<?php echo number_format($stats['total_amount'], 0); ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>12.5% increase</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fa-solid fa-bangladeshi-taka-sign text-white"></i>
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
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>8.3% increase</span>
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
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>5.2% increase</span>
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
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>3.7% increase</span>
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

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Revenue Chart -->
    <div class="col-xl-8 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-gradient-primary text-white py-3 border-0">
                <h6 class="m-0 font-weight-bold">Revenue Overview</h6>
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
                <h6 class="m-0 font-weight-bold">Payment Methods</h6>
                <small class="text-white-50">Distribution by method</small>
            </div>
            <div class="card-body">
                <div class="chart-container-pie">
                    <canvas id="paymentChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Donations -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-gradient-success text-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="m-0 font-weight-bold">Recent Donations</h6>
                <a href="donations.php" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($recent_donations)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-donate fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Recent Donations</h5>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($recent_donations as $donation): ?>
                        <div class="list-group-item d-flex align-items-center hover-effect">
                            <div class="flex-shrink-0">
                                <img class="rounded-circle shadow-sm" src="https://ui-avatars.com/api/?name=<?php echo urlencode($donation['donor_name']); ?>&background=4361ee&color=fff" width="45" height="45">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                                <div class="text-muted small">Donated ৳<?php echo number_format($donation['amount'], 0); ?> to <?php echo htmlspecialchars($donation['campaign_title']); ?></div>
                                <div class="text-muted smaller"><?php echo date('M j, Y g:i A', strtotime($donation['created_at'])); ?></div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-pill">Completed</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-gradient-warning text-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="m-0 font-weight-bold">Recent Campaigns</h6>
                <a href="campaigns.php" class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($recent_campaigns)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-hands-helping fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Campaigns</h5>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($recent_campaigns as $campaign): 
                            $progress = $campaign['goal_amount'] > 0 ? min(100, ($campaign['raised_amount'] / $campaign['goal_amount']) * 100) : 0;
                        ?>
                        <div class="list-group-item d-flex align-items-center hover-effect">
                            <div class="flex-shrink-0">
                                <img class="rounded shadow-sm" src="<?php echo '../' . $campaign['image'] ?: '../assets/images/default-campaign.jpg'; ?>" width="50" height="50" style="object-fit: cover;" onerror="this.src='../assets/images/default-campaign.jpg'">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($campaign['title']); ?></div>
                                <div class="text-muted small">
                                    ৳<?php echo number_format($campaign['raised_amount'], 0); ?> raised of ৳<?php echo number_format($campaign['goal_amount'], 0); ?>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : 'primary'; ?>" 
                                         style="width: <?php echo $progress; ?>%">
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo number_format($progress, 1); ?>% Complete</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-gradient-dark text-white py-3 border-0">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 col-6 mb-3">
                        <a href="campaigns.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-primary text-white hover-scale">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <h6 class="mb-0">New Campaign</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="donations.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-success text-white hover-scale">
                                <i class="fas fa-donate fa-2x mb-2"></i>
                                <h6 class="mb-0">View Donations</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="users.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-info text-white hover-scale">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h6 class="mb-0">Manage Users</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="reports.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-warning text-white hover-scale">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <h6 class="mb-0">Reports</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="categories.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-danger text-white hover-scale">
                                <i class="fas fa-tags fa-2x mb-2"></i>
                                <h6 class="mb-0">Categories</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="../campaigns.php" class="text-decoration-none">
                            <div class="p-3 rounded action-card bg-secondary text-white hover-scale">
                                <i class="fas fa-globe fa-2x mb-2"></i>
                                <h6 class="mb-0">View Site</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

.revenue-card {
    background: var(--primary-gradient);
}

.donations-card {
    background: var(--success-gradient);
}

.campaigns-card {
    background: var(--info-gradient);
}

.users-card {
    background: var(--warning-gradient);
}

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
    border: none !important;
}

.hover-effect:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.hover-scale {
    transition: all 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
}

.action-card {
    transition: all 0.3s ease;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.bg-gradient-primary {
    background: var(--primary-gradient) !important;
}

.bg-gradient-success {
    background: var(--success-gradient) !important;
}

.bg-gradient-info {
    background: var(--info-gradient) !important;
}

.bg-gradient-warning {
    background: var(--warning-gradient) !important;
}

.bg-gradient-dark {
    background: var(--dark-gradient) !important;
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

.card {
    border-radius: 15px;
    overflow: hidden;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #eee;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>

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
const paymentCtx = document.getElementById('paymentChart');
if (paymentCtx) {
    const paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_map(function($method) { 
                return ucfirst(str_replace('_', ' ', $method['payment_method'])); 
            }, $payment_methods)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($payment_methods, 'count')); ?>,
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
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Add animation to stats cards on scroll
document.addEventListener('DOMContentLoaded', function() {
    const statsCards = document.querySelectorAll('.stats-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    statsCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        observer.observe(card);
    });
});
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php include 'footer.php'; ?>