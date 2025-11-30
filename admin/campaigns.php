<?php
// admin/campaigns.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

$page_title = "Manage Campaigns";
include 'header.php';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create') {
        $title = trim($_POST['title']);
        $category_id = $_POST['category_id'];
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description'] ?? '');
        $goal_amount = intval($_POST['goal_amount']); // Convert to integer
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Handle image upload
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['image'], '../assets/images/campaigns/');
            if ($upload_result['success']) {
                $image = 'assets/images/campaigns/' . $upload_result['filename'];
            } else {
                $error = "Image upload failed: " . $upload_result['error'];
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO campaigns (title, description, short_description, goal_amount, category_id, image, start_date, end_date, status, featured, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $title,
                    $description,
                    $short_description,
                    $goal_amount,
                    $category_id,
                    $image,
                    $start_date,
                    $end_date,
                    $status,
                    $featured,
                    $_SESSION['user_id']
                ]);
                
                $success = "Campaign created successfully!";
                
            } catch(PDOException $e) {
                $error = "Failed to create campaign: " . $e->getMessage();
            }
        }
        
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $category_id = $_POST['category_id'];
        $description = trim($_POST['description']);
        $short_description = trim($_POST['short_description'] ?? '');
        $goal_amount = intval($_POST['goal_amount']); // Convert to integer
        $start_date = $_POST['start_date'] ?: null;
        $end_date = $_POST['end_date'] ?: null;
        $status = $_POST['status'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        $current_image = $_POST['current_image'] ?? null;
        
        // Handle image upload
        $image = $current_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = uploadImage($_FILES['image'], '../assets/images/campaigns/');
            if ($upload_result['success']) {
                $image = 'assets/images/campaigns/' . $upload_result['filename'];
                // Delete old image if exists
                if ($current_image && file_exists('../' . $current_image)) {
                    unlink('../' . $current_image);
                }
            } else {
                $error = "Image upload failed: " . $upload_result['error'];
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE campaigns 
                    SET title = ?, description = ?, short_description = ?, goal_amount = ?, 
                        category_id = ?, image = ?, start_date = ?, end_date = ?, 
                        status = ?, featured = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $title,
                    $description,
                    $short_description,
                    $goal_amount,
                    $category_id,
                    $image,
                    $start_date,
                    $end_date,
                    $status,
                    $featured,
                    $id
                ]);
                
                $success = "Campaign updated successfully!";
                
            } catch(PDOException $e) {
                $error = "Failed to update campaign: " . $e->getMessage();
            }
        }
        
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        
        try {
            // Get campaign image to delete it
            $campaign_stmt = $pdo->prepare("SELECT image FROM campaigns WHERE id = ?");
            $campaign_stmt->execute([$id]);
            $campaign = $campaign_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete campaign (donations will be handled by CASCADE DELETE)
            $stmt = $pdo->prepare("DELETE FROM campaigns WHERE id = ?");
            $stmt->execute([$id]);
            
            // Delete image file if exists
            if ($campaign && $campaign['image'] && file_exists('../' . $campaign['image'])) {
                unlink('../' . $campaign['image']);
            }
            
            $success = "Campaign deleted successfully!";
            
        } catch(PDOException $e) {
            $error = "Failed to delete campaign: " . $e->getMessage();
        }
    }
}

// Handle GET actions (edit view)
$edit_campaign = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $edit_campaign = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Failed to load campaign: " . $e->getMessage();
    }
}

// Get all campaigns with category names
try {
    $stmt = $pdo->query("
        SELECT c.*, cat.name as category_name, 
               u.name as creator_name,
               (SELECT COUNT(*) FROM donations WHERE campaign_id = c.id) as donation_count,
               (SELECT SUM(amount) FROM donations WHERE campaign_id = c.id AND status = 'completed') as total_raised
        FROM campaigns c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN users u ON c.created_by = u.id 
        ORDER BY c.created_at DESC
    ");
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $campaigns = [];
    error_log("Campaigns query error: " . $e->getMessage());
}

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$total_campaigns = $pdo->query("SELECT COUNT(*) as count FROM campaigns")->fetch(PDO::FETCH_ASSOC)['count'];
$active_campaigns = $pdo->query("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'")->fetch(PDO::FETCH_ASSOC)['count'];
$completed_campaigns = $pdo->query("SELECT COUNT(*) as count FROM campaigns WHERE status = 'completed'")->fetch(PDO::FETCH_ASSOC)['count'];
$total_raised = $pdo->query("SELECT SUM(raised_amount) as total FROM campaigns")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="row mb-4" style="margin-top: 20px;">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Campaigns</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_campaigns; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-arrow-up me-1"></i>
                            <span>Active: <?php echo $active_campaigns; ?></span>
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
        <div class="card stats-card donations-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Active Campaigns</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $active_campaigns; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-play-circle me-1"></i>
                            <span><?php echo number_format(($active_campaigns/$total_campaigns)*100, 1); ?>% of total</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-play-circle text-white"></i>
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
                            Total Raised</div>
                        <div class="h2 mb-0 font-weight-bold text-white">৳<?php echo number_format($total_raised, 0); ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-taka-sign me-1"></i>
                            <span>Across all campaigns</span>
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
        <div class="card stats-card users-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Completed</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $completed_campaigns; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-check-circle me-1"></i>
                            <span>Successfully completed</span>
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

<!-- Campaigns Table -->
<div class="card shadow border-0">
    <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-hands-helping me-2"></i>All Campaigns</h6>
        <div>
            <span class="badge bg-white text-primary me-2"><?php echo count($campaigns); ?> campaigns</span>
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                <i class="fas fa-plus me-1"></i> New Campaign
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if(empty($campaigns)): ?>
            <div class="text-center py-5">
                <i class="fas fa-hands-helping fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Campaigns Found</h5>
                <p class="text-muted">Create your first campaign to get started.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                    <i class="fas fa-plus me-1"></i> Create Campaign
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="campaignsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Campaign</th>
                            <th>Category</th>
                            <th>Goal</th>
                            <th>Raised</th>
                            <th>Progress</th>
                            <th>Donations</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($campaigns as $campaign): 
                            $progress = $campaign['goal_amount'] > 0 ? min(100, ($campaign['total_raised'] / $campaign['goal_amount']) * 100) : 0;
                        ?>
                        <tr class="hover-effect">
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($campaign['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($campaign['image']); ?>" alt="<?php echo htmlspecialchars($campaign['title']); ?>" class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-gradient-primary text-white rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                            <i class="fas fa-hands-helping"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($campaign['title']); ?></strong>
                                        <?php if($campaign['featured']): ?>
                                            <i class="fas fa-star text-warning ms-1" title="Featured"></i>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">By: <?php echo htmlspecialchars($campaign['creator_name']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($campaign['category_name']); ?></span></td>
                            <td class="text-nowrap fw-bold">৳<?php echo number_format($campaign['goal_amount'], 0); ?></td>
                            <td class="text-nowrap text-success fw-bold">৳<?php echo number_format($campaign['total_raised'], 0); ?></td>
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : 'primary'; ?>" 
                                         style="width: <?php echo $progress; ?>%"
                                         title="<?php echo number_format($progress, 1); ?>%">
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo number_format($progress, 1); ?>%</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill"><?php echo $campaign['donation_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $campaign['status'] == 'active' ? 'success' : 
                                         ($campaign['status'] == 'completed' ? 'primary' : 'secondary'); 
                                ?> rounded-pill">
                                    <?php echo ucfirst($campaign['status']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if($campaign['featured']): ?>
                                    <i class="fas fa-star text-warning" title="Featured"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-muted" title="Not Featured"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../campaign.php?id=<?php echo $campaign['id']; ?>" 
                                       class="btn btn-outline-primary rounded-start" target="_blank" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-warning" 
                                            data-bs-toggle="modal" data-bs-target="#editCampaignModal"
                                            data-campaign='<?php echo json_encode($campaign); ?>'
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-end" 
                                            onclick="deleteCampaign(<?php echo $campaign['id']; ?>, '<?php echo htmlspecialchars(addslashes($campaign['title'])); ?>')"
                                            title="Delete">
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

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-gradient-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Campaign</h5>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Campaign Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2" maxlength="500" placeholder="Brief description (max 500 characters)"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Full Description *</label>
                        <textarea name="description" class="form-control" rows="4" maxlength="800" placeholder="Full description (max 800 characters)" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Goal Amount (৳) *</label>
                            <input type="number" name="goal_amount" class="form-control" min="1" step="1" required placeholder="Enter whole number only">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Campaign Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label bg-gradient-warning text-dark">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="featured" class="form-check-input" id="featuredCreate">
                                <label class="form-check-label bg-gradient-warning text-dark" for="featuredCreate">Featured Campaign</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-gradient-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Campaign</h5>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editCampaignForm">
                    <!-- Content will be loaded via JavaScript -->
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Campaign
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editCampaignModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const campaign = JSON.parse(button.getAttribute('data-campaign'));
            
            const modalBody = document.getElementById('editCampaignForm');
            modalBody.innerHTML = `
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="${campaign.id}">
                <input type="hidden" name="current_image" value="${campaign.image || ''}">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Campaign Title *</label>
                        <input type="text" name="title" class="form-control" value="${campaign.title}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" ${campaign.category_id == <?php echo $category['id']; ?> ? 'selected' : ''}>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Short Description</label>
                    <textarea name="short_description" class="form-control" rows="2" maxlength="500">${campaign.short_description || ''}</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Full Description *</label>
                    <textarea name="description" class="form-control" rows="4" required>${campaign.description}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Goal Amount (৳) *</label>
                        <input type="number" name="goal_amount" class="form-control" value="${campaign.goal_amount}" min="1" step="1" required placeholder="Enter whole number only">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Campaign Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        ${campaign.image ? `<div class="mt-2"><img src="../${campaign.image}" alt="Current image" style="max-width: 100px; max-height: 100px;" class="img-thumbnail"></div>` : ''}
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="${campaign.start_date || ''}">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="${campaign.end_date || ''}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" ${campaign.status == 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${campaign.status == 'inactive' ? 'selected' : ''}>Inactive</option>
                            <option value="completed" ${campaign.status == 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="featured" class="form-check-input" id="featuredEdit" ${campaign.featured ? 'checked' : ''}>
                            <label class="form-check-label bg-gradient-warning text-dark" for="featuredEdit">Featured Campaign</label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Campaign</button>
                </div>
            `;
        });
    }
});

function deleteCampaign(campaignId, campaignName) {
    if(confirm(`Are you sure you want to delete the campaign "${campaignName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${campaignId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
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

/* Fix for header overlap */
.main-content {
    margin-top: 100px !important;
     padding: 20px;
    min-height: calc(100vh - 90px);
}

/* Modal fixes */
.modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.modal {
    z-index: 1060;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
}
.modal-backdrop {
    z-index: 1050;
}

.btn-close-custom {
    background: transparent;
    border: none;
    color: black;
    font-size: 1.2rem;
    opacity: 0.8;
}

.btn-close-custom:hover {
    opacity: 1;
}
</style>

<?php include 'footer.php'; ?>