<?php
// admin/applications.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin())
    redirect('../login.php');

$page_title = "Manage Applications";
include 'header.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $app_id = $_POST['app_id'];
        $new_status = $_POST['status'];
        $notes = trim($_POST['notes'] ?? '');

        try {
            $stmt = $pdo->prepare("UPDATE applications SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $notes, $app_id]);
            $success = "Application status updated to " . ucfirst($new_status) . "!";
        } catch (PDOException $e) {
            $error = "Failed to update status: " . $e->getMessage();
        }
    }

    if (isset($_POST['delete_app'])) {
        $app_id = $_POST['app_id'];

        try {
            // Get file names to delete
            $stmt = $pdo->prepare("SELECT id_card, photo, proof FROM applications WHERE id = ?");
            $stmt->execute([$app_id]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete files
            if ($app) {
                $upload_dir = '../assets/images/application/';
                $files = [$app['id_card'], $app['photo'], $app['proof']];
                foreach ($files as $file) {
                    if ($file && file_exists($upload_dir . $file)) {
                        unlink($upload_dir . $file);
                    }
                }
            }

            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$app_id]);
            $success = "Application deleted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to delete application: " . $e->getMessage();
        }
    }

    // ========== NEW: Handle Edit Application ==========
    if (isset($_POST['edit_app'])) {
        $app_id = $_POST['app_id'];
        $name = trim($_POST['name']);
        $age = !empty($_POST['age']) ? $_POST['age'] : null;
        $mobile = trim($_POST['mobile']);
        $address = trim($_POST['address']);
        $ref_name = trim($_POST['ref_name']);
        $fund_purpose = $_POST['fund_purpose'];
        $status = $_POST['status'];
        $story = trim($_POST['story']);
        $urgent = isset($_POST['urgent']) ? 1 : 0;

        if (empty($name) || empty($mobile)) {
            $error = "Name and Mobile are required.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE applications SET name = ?, age = ?, mobile = ?, address = ?, ref_name = ?, fund_purpose = ?, status = ?, story = ?, urgent = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $age, $mobile, $address, $ref_name, $fund_purpose, $status, $story, $urgent, $app_id]);
                $success = "Application updated successfully!";
            } catch (PDOException $e) {
                $error = "Failed to update application: " . $e->getMessage();
            }
        }
    }
}

// Get applications with filters
$status_filter = $_GET['status'] ?? '';
$urgent_filter = $_GET['urgent'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($urgent_filter === '1') {
    $where_conditions[] = "a.urgent = 1";
}

if ($search) {
    $where_conditions[] = "(a.name LIKE ? OR a.mobile LIKE ? OR a.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               CASE 
                   WHEN DATEDIFF(NOW(), a.application_date) <= 1 THEN 'bg-success'
                   WHEN DATEDIFF(NOW(), a.application_date) <= 3 THEN 'bg-warning'
                   ELSE 'bg-secondary'
               END as date_badge
        FROM applications a 
        $where_sql
        ORDER BY 
            CASE WHEN a.urgent = 1 THEN 0 ELSE 1 END,
            a.application_date DESC
    ");
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $applications = [];
    error_log("Applications query error: " . $e->getMessage());
}

// Get stats
$total_apps = $pdo->query("SELECT COUNT(*) as count FROM applications")->fetch(PDO::FETCH_ASSOC)['count'];
$pending_apps = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch(PDO::FETCH_ASSOC)['count'];
$approved_apps = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE status = 'approved'")->fetch(PDO::FETCH_ASSOC)['count'];
$urgent_apps = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE urgent = 1")->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Applications</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_apps; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-clock me-1"></i>
                            <span><?php echo $pending_apps; ?> pending</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-file-alt text-white"></i>
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
                            Pending</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $pending_apps; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-hourglass-half me-1"></i>
                            <span>Awaiting review</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-hourglass-half text-white"></i>
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
                            Approved</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $approved_apps; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Applications approved</span>
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
                            Urgent Cases</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $urgent_apps; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <span>Need immediate attention</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div><?php echo $success; ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
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
    <div class="card-header bg-gradient-info text-white py-3 border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-filter me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Urgent Only</label>
                <select name="urgent" class="form-select">
                    <option value="">All Applications</option>
                    <option value="1" <?php echo $urgent_filter == '1' ? 'selected' : ''; ?>>Urgent Cases Only</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, mobile, or address"
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Apply</button>
                <a href="applications.php" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="card shadow border-0">
    <div
        class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-file-alt me-2"></i>All Applications</h6>
        <span class="badge bg-white text-primary"><?php echo count($applications); ?> applications</span>
    </div>
    <div class="card-body">
        <?php if (empty($applications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Applications Found</h5>
                <p class="text-muted">No applications match your current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="applicationsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Applicant</th>
                            <th>Mobile</th>
                            <th>Purpose</th>
                            <th>Status</th>          <!-- Fixed header: removed extra Ref_name -->
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr class="<?php echo $app['urgent'] ? 'bg-urgent-light' : ''; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($app['photo']): ?>
                                            <img src="../assets/images/application/<?php echo htmlspecialchars($app['photo']); ?>"
                                                class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                        <?php else: ?>
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($app['name'] ?? 'A'); ?>&background=4361ee&color=fff"
                                                class="rounded-circle me-3" width="40" height="40">
                                        <?php endif; ?>
                                        <div>
                                            <strong
                                                class="text-dark"><?php echo htmlspecialchars($app['name'] ?? 'Unknown'); ?></strong>
                                            <?php if ($app['urgent']): ?>
                                                <span class="badge bg-danger ms-1">Urgent</span>
                                            <?php endif; ?>
                                            <br>
                                            <small class="text-muted">Age: <?php echo $app['age'] ?? 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($app['mobile']); ?>" class="text-dark">
                                        <?php echo htmlspecialchars($app['mobile']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span
                                        class="text-dark"><?php echo ucfirst($app['fund_purpose'] ?? 'Not specified'); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_badges = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $badge_class = $status_badges[$app['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?> rounded-pill">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($app['application_date'])); ?>
                                    </small>
                                    <br>
                                    <span class="badge <?php echo $app['date_badge']; ?>">
                                        <?php
                                        $days = floor((time() - strtotime($app['application_date'])) / (60 * 60 * 24));
                                        echo $days == 0 ? 'Today' : $days . ' days ago';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View Button -->
                                        <button type="button" class="btn btn-outline-primary rounded-start"
                                            data-bs-toggle="modal" data-bs-target="#viewAppModal"
                                            data-app='<?php echo json_encode($app); ?>'>
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- NEW: Edit Button -->
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editAppModal"
                                            data-app='<?php echo json_encode($app); ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <?php if ($app['status'] == 'pending'): ?>
                                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal" data-app-id="<?php echo $app['id']; ?>"
                                                data-app-name="<?php echo htmlspecialchars($app['name'] ?? 'Applicant'); ?>"
                                                data-status="approved">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal" data-app-id="<?php echo $app['id']; ?>"
                                                data-app-name="<?php echo htmlspecialchars($app['name'] ?? 'Applicant'); ?>"
                                                data-status="rejected">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($app['status'] == 'approved'): ?>
                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                                data-bs-target="#updateStatusModal" data-app-id="<?php echo $app['id']; ?>"
                                                data-app-name="<?php echo htmlspecialchars($app['name'] ?? 'Applicant'); ?>"
                                                data-status="pending">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-outline-danger rounded-end"
                                            onclick="confirmDelete(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['name'] ?? 'Applicant')); ?>')">
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

<!-- View Application Modal (unchanged) -->
<div class="modal fade" id="viewAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Application Details</h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appDetails">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal (unchanged) -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Application Status</h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <p>Update status for: <strong id="appNameLabel"></strong></p>
                    <input type="hidden" name="app_id" id="appId">
                    <input type="hidden" name="status" id="statusInput">
                    <input type="hidden" name="update_status" value="1">

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="alert alert-info" id="statusDisplay"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Add any notes or comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== NEW: Edit Application Modal ========== -->
<div class="modal fade" id="editAppModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Application</h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_app" value="1">
                    <input type="hidden" name="app_id" id="edit_app_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-control" name="age" id="edit_age">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile *</label>
                            <input type="text" class="form-control" name="mobile" id="edit_mobile" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference Person</label>
                            <input type="text" class="form-control" name="ref_name" id="edit_ref_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fund Purpose</label>
                            <select class="form-select" name="fund_purpose" id="edit_fund_purpose">
                                <option value="medical">Medical Treatment</option>
                                <option value="education">Education Fees</option>
                                <option value="emergency">Family Emergency</option>
                                <option value="food">Food & Basic Needs</option>
                                <option value="other">Other Help Needed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Story/Situation</label>
                        <textarea class="form-control" name="story" id="edit_story" rows="4"></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="urgent" id="edit_urgent" value="1">
                        <label class="form-check-label" for="edit_urgent">Mark as Urgent</label>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Documents cannot be edited here. To change documents, please delete and reapply.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (unchanged) -->
<div class="modal fade" id="deleteAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteAppForm">
                <div class="modal-body">
                    <p>Are you sure you want to delete this application?</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will permanently delete the application and all uploaded files.
                    </div>
                    <p>Applicant: <strong id="deleteAppName"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <input type="hidden" name="delete_app" value="1">
                    <input type="hidden" name="app_id" id="deleteAppId">
                    <button type="submit" class="btn btn-danger">Delete Application</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // View Application Details (unchanged)
    document.addEventListener('DOMContentLoaded', function () {
        const viewModal = document.getElementById('viewAppModal');
        if (viewModal) {
            viewModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const app = JSON.parse(button.getAttribute('data-app'));

                const modalBody = document.getElementById('appDetails');

                // Format purpose display
                const purposes = {
                    'medical': 'Medical Treatment',
                    'education': 'Education Fees',
                    'emergency': 'Family Emergency',
                    'food': 'Food & Basic Needs',
                    'other': 'Other Help Needed'
                };

                const purposeText = purposes[app.fund_purpose] || app.fund_purpose || 'Not specified';

                // Build HTML (same as before)
                modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-user me-2"></i>Applicant Information</h6>
                                <p><strong>Name:</strong> ${app.name || 'Not provided'}</p>
                                <p><strong>Age:</strong> ${app.age || 'Not provided'}</p>
                                <p><strong>Mobile:</strong> <a href="tel:${app.mobile}">${app.mobile}</a></p>
                                <p><strong>Address:</strong> ${app.address || 'Not provided'}</p>
                                ${app.ref_name ? `<p><strong>Reference Person:</strong> ${app.ref_name}</p>` : ''}
                                <p><strong>Applied:</strong> ${new Date(app.application_date).toLocaleString()}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-${app.status === 'pending' ? 'warning' : app.status === 'approved' ? 'success' : 'danger'}">
                                        ${app.status}
                                    </span>
                                    ${app.urgent ? '<span class="badge bg-danger ms-1">Urgent</span>' : ''}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Application Details</h6>
                                <p><strong>Purpose:</strong> ${purposeText}</p>
                                <p><strong>Situation:</strong></p>
                                <div class="alert alert-light border">
                                    <p class="mb-0">${app.story || 'No description provided'}</p>
                                </div>
                                ${app.notes ? `
                                <p><strong>Admin Notes:</strong></p>
                                <div class="alert alert-info border">
                                    <p class="mb-0">${app.notes}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documents Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-file-upload me-2"></i>Uploaded Documents</h6>
                                <div class="row">
                                    ${app.id_card ? `
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-id-card fa-2x text-primary mb-2"></i>
                                                <h6>ID Card</h6>
                                                <a href="../assets/images/application/${app.id_card}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    
                                    ${app.photo ? `
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-camera fa-2x text-primary mb-2"></i>
                                                <h6>Photo</h6>
                                                <a href="../assets/images/application/${app.photo}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    
                                    ${app.proof ? `
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fas fa-file-medical fa-2x text-primary mb-2"></i>
                                                <h6>Proof</h6>
                                                <a href="../assets/images/application/${app.proof}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    ` : ''}
                                    
                                    ${!app.id_card && !app.photo && !app.proof ? `
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No documents uploaded by applicant
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });
        }

        // Update Status Modal (unchanged)
        const statusModal = document.getElementById('updateStatusModal');
        if (statusModal) {
            statusModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const appId = button.getAttribute('data-app-id');
                const appName = button.getAttribute('data-app-name');
                const status = button.getAttribute('data-status');

                document.getElementById('appId').value = appId;
                document.getElementById('statusInput').value = status;
                document.getElementById('appNameLabel').textContent = appName;

                const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                const statusColors = {
                    'pending': 'warning',
                    'approved': 'success',
                    'rejected': 'danger'
                };

                const statusDisplay = document.getElementById('statusDisplay');
                statusDisplay.innerHTML = `
                <span class="badge bg-${statusColors[status]}">
                    ${statusText}
                </span>
                <p class="mb-0 mt-2">Change status to: <strong>${statusText}</strong></p>
            `;
            });
        }

        // ========== NEW: Populate Edit Modal ==========
        const editModal = document.getElementById('editAppModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const app = JSON.parse(button.getAttribute('data-app'));

                document.getElementById('edit_app_id').value = app.id;
                document.getElementById('edit_name').value = app.name || '';
                document.getElementById('edit_age').value = app.age || '';
                document.getElementById('edit_mobile').value = app.mobile || '';
                document.getElementById('edit_ref_name').value = app.ref_name || '';
                document.getElementById('edit_address').value = app.address || '';
                document.getElementById('edit_fund_purpose').value = app.fund_purpose || 'medical';
                document.getElementById('edit_status').value = app.status || 'pending';
                document.getElementById('edit_story').value = app.story || '';
                document.getElementById('edit_urgent').checked = app.urgent == 1;
            });
        }
    });

    // Delete confirmation (unchanged)
    function confirmDelete(appId, appName) {
        document.getElementById('deleteAppId').value = appId;
        document.getElementById('deleteAppName').textContent = appName;

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteAppModal'));
        deleteModal.show();
    }

    // Export to Excel/PDF (unchanged)
    function exportApplications(format) {
        alert(format + ' export functionality would be implemented here');
    }
</script>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stats-card {
        color: white;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
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

    .bg-urgent-light {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }

    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Make modal scrollable on mobile */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    }
</style>

<?php include 'footer.php'; ?>