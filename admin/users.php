<?php
// admin/users.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

$page_title = "Manage Users";
include 'header.php';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Get user details for reference
            $stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // OPTION 1: Set donations to guest/remove user reference
                // Update donations to set donor_id to NULL or a guest user
                $stmt = $pdo->prepare("UPDATE donations SET donor_id = NULL WHERE donor_id = ?");
                $stmt->execute([$user_id]);
                
                // OPTION 2: Or delete donations first (uncomment if you want to delete donations too)
                // $stmt = $pdo->prepare("DELETE FROM donations WHERE donor_id = ?");
                // $stmt->execute([$user_id]);
                
                // Delete user's sessions if they exist
                try {
                    $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                } catch(Exception $e) {
                    // Sessions table might not exist, ignore error
                }
                
                // Delete user from database
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $pdo->commit();
                
                // Log the deletion
                $deleted_user_info = $user['name'] . ' (' . $user['email'] . ')';
                $log_message = "User deleted: ID $user_id - $deleted_user_info";
                error_log($log_message);
                
                $success = "User deleted successfully! Donations have been preserved as guest donations.";
            } else {
                $error = "User not found!";
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to delete user: " . $e->getMessage();
        }
    }
}

// Get users with filters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($role_filter) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM donations WHERE donor_id = u.id) as donation_count,
               (SELECT SUM(amount) FROM donations WHERE donor_id = u.id AND status = 'completed') as total_donated
        FROM users u 
        $where_sql
        ORDER BY u.created_at DESC
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $users = [];
    error_log("Users query error: " . $e->getMessage());
}

// Get stats
$total_users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'];
$total_admins = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC)['count'];
$active_users = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch(PDO::FETCH_ASSOC)['count'];
$regular_users = $total_users - $total_admins;
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Users</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_users; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-users me-1"></i>
                            <span><?php echo $active_users; ?> active</span>
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

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card donations-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Active Users</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $active_users; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-user-check me-1"></i>
                            <span><?php echo number_format(($active_users/$total_users)*100, 1); ?>% active rate</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-user-check text-white"></i>
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
                            Administrators</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_admins; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-user-shield me-1"></i>
                            <span>System administrators</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-user-shield text-white"></i>
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
                            Regular Users</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $regular_users; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-user me-1"></i>
                            <span>Registered donors</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-user text-white"></i>
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
    <div class="card-header bg-gradient-info text-white py-3 border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-filter me-2"></i>Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>User</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Apply Filters</button>
                <a href="users.php" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow border-0">
    <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-users me-2"></i>All Users</h6>
        <span class="badge bg-white text-primary"><?php echo count($users); ?> users</span>
    </div>
    <div class="card-body">
        <?php if(empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Users Found</h5>
                <p class="text-muted">No users match your current filters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Donations</th>
                            <th>Total Donated</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr class="hover-effect">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=4361ee&color=fff" 
                                         class="rounded-circle me-3" width="45" height="45">
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($user['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark"><?php echo htmlspecialchars($user['email']); ?></div>
                                <?php if($user['phone']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?> rounded-pill">
                                    <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user'; ?> me-1"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark"><?php echo $user['donation_count']; ?></span>
                            </td>
                            <td class="text-success fw-bold">
                                ৳<?php echo number_format($user['total_donated'] ?? 0, 0); ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-start" data-bs-toggle="modal" data-bs-target="#viewUserModal" 
                                            data-user='<?php echo json_encode($user); ?>'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <?php if($user['status'] == 'active'): ?>
                                            <button type="button" class="btn btn-outline-secondary" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'inactive')">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success" onclick="toggleUserStatus(<?php echo $user['id']; ?>, 'active')">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <!-- DELETE BUTTON -->
                                        <button type="button" class="btn btn-outline-danger rounded-end" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', <?php echo $user['donation_count']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-secondary rounded-end" disabled title="Cannot modify your own account">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- Content will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> This action cannot be undone. 
                    <?php /* Optional: Add warning about donations if needed */ ?>
                </div>
                <div class="card border-warning mb-3">
                    <div class="card-body">
                        <p><strong>User Name:</strong> <span id="deleteUserName"></span></p>
                        <p><strong>Email:</strong> <span id="deleteUserEmail"></span></p>
                        <p><strong>Donations Made:</strong> <span id="deleteUserDonations"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteUserForm">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// View User Details
document.addEventListener('DOMContentLoaded', function() {
    const viewModal = document.getElementById('viewUserModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const user = JSON.parse(button.getAttribute('data-user'));
            
            const modalBody = document.getElementById('userDetails');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=4361ee&color=fff&size=200" 
                             class="rounded-circle mb-3" width="120" height="120">
                        <h5 class="text-dark">${user.name}</h5>
                        <span class="badge bg-${user.role === 'admin' ? 'danger' : 'primary'} rounded-pill">
                            ${user.role}
                        </span>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Personal Information</h6>
                                        <p><strong>Email:</strong> ${user.email}</p>
                                        <p><strong>Phone:</strong> ${user.phone || 'N/A'}</p>
                                        <p><strong>Status:</strong> <span class="badge bg-${user.status === 'active' ? 'success' : 'secondary'}">${user.status}</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-chart-bar me-2"></i>Donation Stats</h6>
                                        <p><strong>Total Donations:</strong> <span class="fw-bold">${user.donation_count}</span></p>
                                        <p><strong>Total Amount:</strong> <span class="text-success fw-bold">৳${parseFloat(user.total_donated || 0).toLocaleString()}</span></p>
                                        <p><strong>Member Since:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ${user.address ? `
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                                        <p class="mb-0">${user.address}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
    }
});

function toggleUserStatus(userId, status) {
    const action = status === 'active' ? 'activate' : 'deactivate';
    if(confirm(`Are you sure you want to ${action} this user?`)) {
        window.location.href = `update_user.php?id=${userId}&status=${status}`;
    }
}

function editUser(userId) {
    // Redirect to edit user page
    window.location.href = 'edit_user.php?id=' + userId;
}

// Delete user confirmation
function confirmDelete(userId, userName, userEmail, donationCount) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserEmail').textContent = userEmail;
    document.getElementById('deleteUserDonations').textContent = donationCount;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
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
    align-items-center: center;
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
</style>

<?php include 'footer.php'; ?>