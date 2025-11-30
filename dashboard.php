<?php
// dashboard.php
require_once 'includes/config.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = "Dashboard";
include 'includes/header.php';

// Get user stats
$user_id = $_SESSION['user_id'];
$total_donations = 0;
$total_amount = 0;
$recent_donations = [];

try {
    // Get total donations count and amount
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_donations, SUM(amount) as total_amount 
        FROM donations 
        WHERE donor_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_donations = $stats['total_donations'] ?? 0;
    $total_amount = $stats['total_amount'] ?? 0;

    // Get recent donations
    $stmt = $pdo->prepare("
        SELECT d.*, c.title as campaign_title, c.image as campaign_image
        FROM donations d 
        LEFT JOIN campaigns c ON d.campaign_id = c.id 
        WHERE d.donor_id = ? 
        ORDER BY d.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<!-- Dashboard Header -->
<div class="dashboard-header bg-primary text-white py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h3 mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
                <p class="mb-0 opacity-75">Here's your donation activity and impact summary</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="campaigns.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Make New Donation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Stats Cards -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Donations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_donations; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Amount Donated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">৳<?php echo number_format($total_amount, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Campaigns Supported</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $unique_campaigns = array_unique(array_column($recent_donations, 'campaign_id'));
                                echo count($unique_campaigns);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hands-helping fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Member Since</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);
                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                echo date('M Y', strtotime($user['created_at']));
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Donations -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Donations</h6>
                    <a href="donation_history.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if(empty($recent_donations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Donations Yet</h5>
                            <p class="text-muted">Make your first donation to see it here!</p>
                            <a href="campaigns.php" class="btn btn-primary">Browse Campaigns</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Campaign</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_donations as $donation): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $donation['campaign_image'] ?: 'assets/images/default-campaign.jpg'; ?>" 
                                                     class="rounded me-3" width="40" height="40" style="object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($donation['campaign_title']); ?></h6>
                                                    <small class="text-muted"><?php echo $donation['payment_method']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold text-success">৳<?php echo number_format($donation['amount'], 2); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($donation['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                        <td>
                                            <a href="campaign.php?id=<?php echo $donation['campaign_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card bg-gradient-primary text-white shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-search fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Find Causes</h5>
                                    <p class="card-text">Discover new campaigns to support</p>
                                    <a href="campaigns.php" class="btn btn-light btn-sm">Browse Campaigns</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card bg-gradient-success text-white shadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Your Profile</h5>
                                    <p class="card-text">Update your personal information</p>
                                    <a href="profile.php" class="btn btn-light btn-sm">Edit Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- Impact Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Impact</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-hand-holding-heart fa-3x text-primary mb-3"></i>
                        <h4 class="text-primary">You're Making a Difference!</h4>
                        <p class="text-muted">Your donations are helping create positive change in the world.</p>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Impact:</span>
                                <strong>৳<?php echo number_format($total_amount, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Lives Touched:</span>
                                <strong><?php echo $total_donations * 10; ?>+</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Communities Helped:</span>
                                <strong><?php echo count($unique_campaigns); ?>+</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="activity-feed">
                        <?php if(empty($recent_donations)): ?>
                            <p class="text-muted text-center">No recent activity</p>
                        <?php else: ?>
                            <?php foreach(array_slice($recent_donations, 0, 3) as $donation): ?>
                            <div class="activity-item mb-3">
                                <div class="activity-content">
                                    <small class="text-muted"><?php echo date('M j', strtotime($donation['created_at'])); ?></small>
                                    <p class="mb-1">Donated <strong>৳<?php echo number_format($donation['amount'], 2); ?></strong> to 
                                    <strong><?php echo htmlspecialchars($donation['campaign_title']); ?></strong></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
}

.card {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.activity-item {
    border-left: 3px solid #4e73df;
    padding-left: 15px;
}

.activity-item:last-child {
    margin-bottom: 0 !important;
}
</style>

<?php include 'includes/footer.php'; ?>