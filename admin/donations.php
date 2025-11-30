<?php
// admin/donations.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

$page_title = "Manage Donations";
include 'header.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $donation_id = $_POST['donation_id'];
    $new_status = $_POST['status'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get current donation details
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$donation_id]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($donation) {
            $old_status = $donation['status'];
            $amount = $donation['amount'];
            $campaign_id = $donation['campaign_id'];
            
            // Update donation status
            $stmt = $pdo->prepare("UPDATE donations SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $donation_id]);
            
            // Update campaign raised amount based on status change
            if ($campaign_id) {
                if ($old_status == 'completed' && $new_status != 'completed') {
                    // Remove amount from raised amount
                    $stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount - ? WHERE id = ?");
                    $stmt->execute([$amount, $campaign_id]);
                } elseif ($new_status == 'completed' && $old_status != 'completed') {
                    // Add amount to raised amount
                    $stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?");
                    $stmt->execute([$amount, $campaign_id]);
                }
            }
            
            $pdo->commit();
            $success = "Donation status updated successfully!";
        } else {
            $error = "Donation not found!";
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to update donation status: " . $e->getMessage();
    }
}

// Handle donation deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_donation'])) {
    $donation_id = $_POST['donation_id'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get donation details before deletion
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$donation_id]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($donation) {
            $amount = $donation['amount'];
            $campaign_id = $donation['campaign_id'];
            $status = $donation['status'];
            
            // If donation was completed and has a campaign, subtract from raised amount
            if ($status == 'completed' && $campaign_id) {
                $stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount - ? WHERE id = ?");
                $stmt->execute([$amount, $campaign_id]);
            }
            
            // Delete the donation
            $stmt = $pdo->prepare("DELETE FROM donations WHERE id = ?");
            $stmt->execute([$donation_id]);
            
            $pdo->commit();
            $success = "Donation deleted successfully!";
        } else {
            $error = "Donation not found!";
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = "Failed to delete donation: " . $e->getMessage();
    }
}

// Get donations with filters
$status_filter = $_GET['status'] ?? '';
$payment_filter = $_GET['payment_method'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "d.status = ?";
    $params[] = $status_filter;
}

if ($payment_filter) {
    $where_conditions[] = "d.payment_method = ?";
    $params[] = $payment_filter;
}

if ($search) {
    $where_conditions[] = "(d.donor_name LIKE ? OR d.donor_email LIKE ? OR c.title LIKE ? OR d.transaction_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    $stmt = $pdo->prepare("
        SELECT d.*, c.title as campaign_title, u.name as user_name
        FROM donations d 
        LEFT JOIN campaigns c ON d.campaign_id = c.id 
        LEFT JOIN users u ON d.donor_id = u.id 
        $where_sql
        ORDER BY d.created_at DESC
    ");
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $donations = [];
    error_log("Donations query error: " . $e->getMessage());
}

// Get stats for summary
$total_donations = $pdo->query("SELECT COUNT(*) as count FROM donations")->fetch(PDO::FETCH_ASSOC)['count'];
$total_amount = $pdo->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
$pending_bikash = $pdo->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending' AND payment_method = 'bikash'")->fetch(PDO::FETCH_ASSOC)['count'];
$completed_donations = $pdo->query("SELECT COUNT(*) as count FROM donations WHERE status = 'completed'")->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Donations</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_donations; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-heart me-1"></i>
                            <span><?php echo $completed_donations; ?> completed</span>
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
        <div class="card stats-card donations-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Amount</div>
                        <div class="h2 mb-0 font-weight-bold text-white">৳<?php echo number_format($total_amount, 0); ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-bangladeshi-taka-sign me-1"></i>
                            <span>From all donations</span>
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
        <div class="card stats-card campaigns-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Completed</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $completed_donations; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-check-circle me-1"></i>
                            <span><?php echo $total_donations > 0 ? number_format(($completed_donations/$total_donations)*100, 1) : 0; ?>% success rate</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-check-circle text-white"></i>
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
                            Pending Bikash</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $pending_bikash; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-mobile-alt me-1"></i>
                            <span>Awaiting verification</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-mobile-alt text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if(isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div><?php echo $success; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div><?php echo $error; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card shadow border-0 mb-4">
    <div class="card-header bg-gradient-info text-black py-3 border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-filter me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="refunded" <?php echo $status_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash" <?php echo $payment_filter == 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="bikash" <?php echo $payment_filter == 'bikash' ? 'selected' : ''; ?>>Bikash</option>
                    <option value="stripe" <?php echo $payment_filter == 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                    <option value="paypal" <?php echo $payment_filter == 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                    <option value="bank_transfer" <?php echo $payment_filter == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, campaign, or transaction ID" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Apply</button>
                <a href="donations.php" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Donations Table -->
<div class="card shadow border-0">
    <div class="card-header bg-gradient-success text-black py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-donate me-2"></i>All Donations</h6>
        <span class="badge bg-white text-success"><?php echo count($donations); ?> records</span>
    </div>
    <div class="card-body">
        <?php if(empty($donations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-donate fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Donations Found</h5>
                <p class="text-muted">No donations match your current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="donationsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Donor</th>
                            <th>Campaign</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($donations as $donation): ?>
                        <tr class="hover-effect">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($donation['donor_name']); ?>&background=4361ee&color=fff" 
                                         class="rounded-circle me-3" width="40" height="40">
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($donation['donor_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($donation['donor_email']); ?></small>
                                        <?php if($donation['is_anonymous']): ?>
                                            <span class="badge bg-secondary ms-1">Anonymous</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($donation['campaign_title']): ?>
                                    <span class="text-dark"><?php echo htmlspecialchars($donation['campaign_title']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">General Donation</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-success">
                                ৳<?php echo number_format($donation['amount'], 0); ?>
                            </td>
                            <td>
                                <?php 
                                $payment_badges = [
                                    'cash' => 'secondary',
                                    'bikash' => 'danger',
                                    'stripe' => 'primary',
                                    'paypal' => 'info',
                                    'bank_transfer' => 'warning'
                                ];
                                $badge_class = $payment_badges[$donation['payment_method']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?> text-capitalize rounded-pill">
                                    <i class="fas fa-<?php echo getPaymentMethodIcon($donation['payment_method']); ?> me-1"></i>
                                    <?php echo str_replace('_', ' ', $donation['payment_method']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($donation['transaction_id']): ?>
                                    <code class="text-dark"><?php echo htmlspecialchars($donation['transaction_id']); ?></code>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status_badges = [
                                    'completed' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    'refunded' => 'secondary'
                                ];
                                $badge_class = $status_badges[$donation['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $badge_class; ?> rounded-pill">
                                    <?php echo ucfirst($donation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($donation['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-start" data-bs-toggle="modal" data-bs-target="#viewDonationModal" 
                                            data-donation='<?php echo json_encode($donation); ?>'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($donation['status'] == 'pending'): ?>
                                        <button type="button" class="btn btn-outline-success" onclick="updateStatus(<?php echo $donation['id']; ?>, 'completed')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="updateStatus(<?php echo $donation['id']; ?>, 'failed')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif($donation['status'] == 'completed'): ?>
                                        <button type="button" class="btn btn-outline-warning" onclick="updateStatus(<?php echo $donation['id']; ?>, 'refunded')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary" disabled>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-outline-danger rounded-end" onclick="confirmDelete(<?php echo $donation['id']; ?>, '<?php echo htmlspecialchars($donation['donor_name']); ?>', <?php echo $donation['amount']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Donation Modal -->
<div class="modal fade" id="viewDonationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="fas fa-donate me-2"></i>Donation Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="donationDetails">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger text-black">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-gradient-danger text-black">
                <p>Are you sure you want to delete this donation?</p>
                <div class="alert alert-warning bg-gradient-danger text-black">
                    <strong>Warning:</strong> This action cannot be undone. If this donation was completed and associated with a campaign, the campaign's raised amount will be adjusted accordingly.
                </div>
                <p><strong>Donor:</strong> <span id="deleteDonorName"></span></p>
                <p><strong>Amount:</strong> ৳<span id="deleteAmount"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_donation" value="1">
                    <input type="hidden" name="donation_id" id="deleteDonationId">
                    <button type="submit" class="btn btn-danger">Delete Donation</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// View Donation Details
document.addEventListener('DOMContentLoaded', function() {
    const viewModal = document.getElementById('viewDonationModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const donation = JSON.parse(button.getAttribute('data-donation'));
            
            const modalBody = document.getElementById('donationDetails');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-user me-2"></i>Donor Information</h6>
                                <p><strong>Name:</strong> ${donation.donor_name}</p>
                                <p><strong>Email:</strong> ${donation.donor_email}</p>
                                ${donation.donor_phone ? `<p><strong>Phone:</strong> ${donation.donor_phone}</p>` : ''}
                                <p><strong>User Account:</strong> ${donation.user_name ? donation.user_name : 'Guest Donation'}</p>
                                <p><strong>Anonymous:</strong> <span class="badge ${donation.is_anonymous ? 'bg-secondary' : 'bg-light text-dark'}">${donation.is_anonymous ? 'Yes' : 'No'}</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-receipt me-2"></i>Donation Details</h6>
                                <p><strong>Amount:</strong> <span class="fw-bold text-success">৳${parseInt(donation.amount).toLocaleString()}</span></p>
                                <p><strong>Payment Method:</strong> <span class="badge bg-${donation.payment_method === 'bikash' ? 'danger' : donation.payment_method === 'cash' ? 'secondary' : 'primary'}">${donation.payment_method.replace('_', ' ')}</span></p>
                                <p><strong>Status:</strong> <span class="badge bg-${donation.status === 'completed' ? 'success' : donation.status === 'pending' ? 'warning' : 'danger'}">${donation.status}</span></p>
                                <p><strong>Date:</strong> ${new Date(donation.created_at).toLocaleString()}</p>
                                ${donation.transaction_id ? `<p><strong>Transaction ID:</strong> <code class="bg-dark text-white p-1 rounded">${donation.transaction_id}</code></p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                ${donation.campaign_title ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-hands-helping me-2"></i>Campaign</h6>
                                <p><strong>Title:</strong> ${donation.campaign_title}</p>
                                <p><strong>Campaign ID:</strong> ${donation.campaign_id}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : '<div class="row mt-3"><div class="col-12"><div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>General donation (not assigned to specific campaign)</div></div></div>'}
                ${donation.message ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-comment me-2"></i>Donor Message</h6>
                                <div class="alert alert-light border">
                                    <p class="mb-0">${donation.message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ` : '<div class="row mt-3"><div class="col-12"><p class="text-muted text-center"><i class="fas fa-comment-slash me-1"></i>No message from donor</p></div></div>'}
            `;
        });
    }
});

function updateStatus(donationId, status) {
    const statusText = status === 'completed' ? 'approve' : status === 'failed' ? 'reject' : status;
    const icon = status === 'completed' ? 'fa-check' : status === 'failed' ? 'fa-times' : 'fa-undo';
    
    if(confirm(`Are you sure you want to ${statusText} this donation?`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        
        const updateStatusInput = document.createElement('input');
        updateStatusInput.type = 'hidden';
        updateStatusInput.name = 'update_status';
        updateStatusInput.value = '1';
        
        const donationIdInput = document.createElement('input');
        donationIdInput.type = 'hidden';
        donationIdInput.name = 'donation_id';
        donationIdInput.value = donationId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(updateStatusInput);
        form.appendChild(donationIdInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Delete donation confirmation
function confirmDelete(donationId, donorName, amount) {
    document.getElementById('deleteDonationId').value = donationId;
    document.getElementById('deleteDonorName').textContent = donorName;
    document.getElementById('deleteAmount').textContent = amount.toLocaleString();
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
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

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-weight: 500;
}

.btn-close-white {
    filter: invert(1) grayscale(100%) brightness(200%);
}

/* Fix for header overlap */
.main-content {
    margin-top: 80px !important;
}
</style>

<?php
// Helper function for payment method icons
function getPaymentMethodIcon($method) {
    $icons = [
        'cash' => 'money-bill',
        'bikash' => 'mobile-alt',
        'stripe' => 'credit-card',
        'paypal' => 'paypal',
        'bank_transfer' => 'university'
    ];
    return $icons[$method] ?? 'money-bill';
}
?>

<?php include 'footer.php'; ?>