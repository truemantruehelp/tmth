<?php
// admin/categories.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) redirect('../login.php');

$page_title = "Manage Categories";
include 'header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'create') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = $_POST['parent_id'] ?? null;
        
        // Fix: Convert empty string to NULL for parent_id
        if ($parent_id === '') {
            $parent_id = null;
        }
        
        // Validate parent_id is either NULL or a valid integer
        if ($parent_id !== null && !is_numeric($parent_id)) {
            $error = "Invalid parent category selected.";
        } elseif (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, parent_id) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $parent_id]);
                $success = "Category created successfully!";
            } catch(PDOException $e) {
                // More specific error message
                if (strpos($e->getMessage(), 'parent_id') !== false) {
                    $error = "Error creating category: Invalid parent category. Please select a valid parent or leave as 'No Parent'.";
                } else {
                    $error = "Error creating category: " . $e->getMessage();
                }
            }
        } else {
            $error = "Category name is required.";
        }
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = $_POST['parent_id'] ?? null;
        $status = $_POST['status'] ?? 'active';
        
        // Fix: Convert empty string to NULL for parent_id
        if ($parent_id === '') {
            $parent_id = null;
        }
        
        // Validate parent_id is either NULL or a valid integer
        if ($parent_id !== null && !is_numeric($parent_id)) {
            $error = "Invalid parent category selected.";
        } elseif (!empty($name)) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, parent_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $description, $parent_id, $status, $id]);
                $success = "Category updated successfully!";
            } catch(PDOException $e) {
                // More specific error message
                if (strpos($e->getMessage(), 'parent_id') !== false) {
                    $error = "Error updating category: Invalid parent category. Please select a valid parent or leave as 'No Parent'.";
                } else {
                    $error = "Error updating category: " . $e->getMessage();
                }
            }
        } else {
            $error = "Category name is required.";
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Category deleted successfully!";
        } catch(PDOException $e) {
            $error = "Error deleting category: " . $e->getMessage();
        }
    }
}

// Get all categories with hierarchy
try {
    $stmt = $pdo->query("
        SELECT c1.*, 
               c2.name as parent_name,
               (SELECT COUNT(*) FROM categories c3 WHERE c3.parent_id = c1.id) as subcategory_count,
               (SELECT COUNT(*) FROM campaigns WHERE category_id = c1.id) as campaign_count
        FROM categories c1 
        LEFT JOIN categories c2 ON c1.parent_id = c2.id 
        ORDER BY c1.parent_id IS NULL DESC, c1.parent_id, c1.name
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
    error_log("Categories query error: " . $e->getMessage());
}

// Get parent categories for dropdown
$parent_categories = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get stats
$total_categories = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch(PDO::FETCH_ASSOC)['count'];
$parent_categories_count = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE parent_id IS NULL")->fetch(PDO::FETCH_ASSOC)['count'];
$subcategories_count = $total_categories - $parent_categories_count;
$active_categories = $pdo->query("SELECT COUNT(*) as count FROM categories WHERE status = 'active'")->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!-- Stats Cards with Gradient Backgrounds -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card revenue-card shadow-lg border-0">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Total Categories</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $total_categories; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-tags me-1"></i>
                            <span><?php echo $active_categories; ?> active</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-tags text-white"></i>
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
                            Parent Categories</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $parent_categories_count; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-folder me-1"></i>
                            <span>Main categories</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-folder text-white"></i>
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
                            Subcategories</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $subcategories_count; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-folder-open me-1"></i>
                            <span>Child categories</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="icon-circle bg-white-20">
                            <i class="fas fa-folder-open text-white"></i>
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
                            Active Categories</div>
                        <div class="h2 mb-0 font-weight-bold text-white"><?php echo $active_categories; ?></div>
                        <div class="mt-2 text-white-50 small">
                            <i class="fas fa-check-circle me-1"></i>
                            <span><?php echo number_format(($active_categories/$total_categories)*100, 1); ?>% active rate</span>
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

<!-- Categories Table -->
<div class="card shadow border-0">
    <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-sitemap me-2"></i>Category Hierarchy</h6>
        <div>
            <span class="badge bg-white text-primary me-2"><?php echo count($categories); ?> categories</span>
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="fas fa-plus me-1"></i> New Category
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if(empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Categories Found</h5>
                <p class="text-muted">Create your first category to get started.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fas fa-plus me-1"></i> Create Category
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover" id="categoriesTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Category Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Subcategories</th>
                            <th>Campaigns</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                        <tr class="hover-effect <?php echo $category['parent_id'] ? 'table-active' : 'bg-light'; ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="category-icon me-3">
                                        <?php if($category['parent_id']): ?>
                                            <i class="fas fa-folder-open text-info fa-lg"></i>
                                        <?php else: ?>
                                            <i class="fas fa-folder text-primary fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($category['name']); ?></strong>
                                        <?php if($category['parent_name']): ?>
                                            <br>
                                            <small class="text-muted">Parent: <?php echo htmlspecialchars($category['parent_name']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($category['parent_id']): ?>
                                    <span class="badge bg-info rounded-pill">Subcategory</span>
                                <?php else: ?>
                                    <span class="badge bg-primary rounded-pill">Parent Category</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $category['description'] ? 
                                    '<span class="text-dark">' . htmlspecialchars($category['description']) . '</span>' : 
                                    '<span class="text-muted">No description</span>'; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill"><?php echo $category['subcategory_count']; ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning rounded-pill"><?php echo $category['campaign_count']; ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?> rounded-pill">
                                    <?php echo ucfirst($category['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-warning rounded-start" 
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                            data-category='<?php echo json_encode($category); ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-end" 
                                            onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
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

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-gradient-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Category</h5>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter category name (e.g., Education, Healthcare)" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category (optional)"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label bg-gradient-warning text-dark">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">No Parent (New Main Category)</option>
                            <?php foreach($parent_categories as $parent): ?>
                                <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Leave as "No Parent" to create a main category</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-gradient-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editCategoryForm">
                    <!-- Content will be loaded via JavaScript -->
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Category
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const category = JSON.parse(button.getAttribute('data-category'));
            
            const modalBody = document.getElementById('editCategoryForm');
            modalBody.innerHTML = `
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="${category.id}">
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Category Name *</label>
                    <input type="text" name="name" class="form-control" value="${category.name}" placeholder="Enter category name (e.g., Education, Healthcare)" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category (optional)">${category.description || ''}</textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Parent Category</label>
                    <select name="parent_id" class="form-select">
                        <option value="">No Parent (Main Category)</option>
                        <?php foreach($parent_categories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>" ${category.parent_id == <?php echo $parent['id']; ?> ? 'selected' : ''}>
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Leave as "No Parent" to make this a main category</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label bg-gradient-warning text-dark">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" ${category.status == 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${category.status == 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Category</button>
                </div>
            `;
        });
    }
});

function deleteCategory(categoryId, categoryName) {
    if(confirm(`Are you sure you want to delete the category "${categoryName}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${categoryId}">
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

.category-icon {
    width: 30px;
    text-align: center;
}

/* Enhance placeholder visibility */
.form-control::placeholder {
    color: #6c757d;
    opacity: 0.7;
}

.form-control:focus::placeholder {
    color: #adb5bd;
}

/* Style form text hints */
.form-text {
    font-size: 0.875em;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Fix for header overlap */
.main-content {
    margin-top: 80px !important;
}
</style>

<?php include 'footer.php'; ?>