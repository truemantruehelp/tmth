<?php
// campaigns.php
require_once 'includes/config.php';
$page_title = "All Campaigns";

// Get filter parameters
$category_id = $_GET['category'] ?? '';
$parent_category_id = $_GET['parent_category'] ?? '';

// Get campaigns based on filters
try {
    if ($category_id) {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' AND c.category_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$category_id]);
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current_category = getCategoryFullName($category_id);
    } elseif ($parent_category_id) {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' AND cat.parent_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$parent_category_id]);
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current_category = getCategoryFullName($parent_category_id);
    } else {
        $stmt = $pdo->query("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' 
            ORDER BY c.created_at DESC
        ");
        $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $current_category = 'All Campaigns';
    }
} catch(PDOException $e) {
    $campaigns = [];
    $current_category = 'All Campaigns';
    error_log("Campaigns page error: " . $e->getMessage());
}

$categories = getCategoryHierarchy();

include 'includes/header.php';
?>

<!-- Page Header Section - Reduced Height -->
<section class="page-header-section py-4" style="margin-top: 70px; background: var(--gradient-primary);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="text-white mb-2" data-aos="fade-right"><?php echo htmlspecialchars($current_category); ?></h2>
                <p class="text-white mb-0" data-aos="fade-right" data-aos-delay="100">
                    <?php echo count($campaigns); ?> campaign<?php echo count($campaigns) !== 1 ? 's' : ''; ?> found
                </p>
            </div>
            <div class="col-md-4 text-md-end" data-aos="fade-left">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-md-end mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Campaigns</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Campaigns Section -->
<section class="campaigns-section py-4">
    <div class="container">
        <!-- Mobile Filter Toggle -->
        <div class="d-lg-none mb-4" data-aos="fade-down">
            <button class="btn btn-primary w-100 d-flex align-items-center justify-content-between" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#mobileCategorySidebar"
                    aria-expanded="false" 
                    aria-controls="mobileCategorySidebar">
                <span>
                    <i class="fas fa-filter me-2"></i>Filter Categories
                </span>
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>

        <div class="row">
            <!-- Sidebar with categories - Collapsible on Mobile -->
            <div class="col-lg-3 mb-4" data-aos="fade-right">
                <div class="category-sidebar card border-0 shadow-sm collapse d-lg-block" id="mobileCategorySidebar">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>Filter Categories
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="campaigns.php" 
                               class="list-group-item list-group-item-action d-flex align-items-center <?php echo !$category_id && !$parent_category_id ? 'active' : ''; ?>">
                                <i class="fas fa-layer-group me-2"></i>
                                All Campaigns
                                <span class="badge bg-primary ms-auto"><?php echo getTotalCampaignsCount(); ?></span>
                            </a>
                            <?php foreach($categories as $parent): ?>
                            <div class="parent-category">
                                <div class="list-group-item list-group-item-light fw-bold text-primary py-2">
                                    <i class="fas fa-folder me-2"></i><?php echo htmlspecialchars($parent['name']); ?>
                                </div>
                                <?php foreach($parent['children'] as $child): ?>
                                <a href="campaigns.php?category=<?php echo $child['id']; ?>" 
                                   class="list-group-item list-group-item-action d-flex align-items-center ps-4 category-link <?php echo $category_id == $child['id'] ? 'active' : ''; ?>"
                                   data-category-id="<?php echo $child['id']; ?>">
                                    <i class="fas fa-folder-open me-2"></i>
                                    <?php echo htmlspecialchars($child['name']); ?>
                                    <span class="badge bg-light text-dark ms-auto"><?php echo getCategoryCampaignsCount($child['id']); ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="quick-stats card border-0 shadow-sm mt-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body text-center">
                        <h6 class="card-title text-primary mb-3">
                            <i class="fas fa-chart-line me-2"></i>Quick Stats
                        </h6>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <h4 class="text-primary mb-1"><?php echo getTotalCampaignsCount(); ?></h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <h4 class="text-success mb-1"><?php echo getActiveCampaignsCount(); ?></h4>
                                    <small class="text-muted">Active</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Campaigns listing -->
            <div class="col-lg-9" data-aos="fade-up">
                <?php if(empty($campaigns)): ?>
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Campaigns Found</h4>
                        <p class="text-muted mb-4">We couldn't find any campaigns in this category.</p>
                        <a href="campaigns.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo me-2"></i>View All Campaigns
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Sort and View Options -->
                    <div class="campaign-options d-flex justify-content-between align-items-center mb-4">
                        <div class="sort-options">
                            <select class="form-select form-select-sm" id="sortCampaigns">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="most-funded">Most Funded</option>
                                <option value="least-funded">Least Funded</option>
                            </select>
                        </div>
                        <div class="view-options">
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="viewMode" id="gridView" checked>
                                <label class="btn btn-outline-primary" for="gridView">
                                    <i class="fas fa-th"></i>
                                </label>
                                <input type="radio" class="btn-check" name="viewMode" id="listView">
                                <label class="btn btn-outline-primary" for="listView">
                                    <i class="fas fa-list"></i>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Campaigns Grid -->
                    <div class="row" id="campaignsGrid">
                        <?php foreach($campaigns as $campaign): ?>
                        <div class="col-xl-4 col-lg-6 mb-4 campaign-item">
                            <div class="campaign-card-hover h-100">
                                <div class="campaign-image">
                                    <img src="<?php echo $campaign['image'] ?: 'assets/images/default-campaign.jpg'; ?>" 
                                         class="img-fluid" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                    <div class="campaign-overlay">
                                        <span class="badge campaign-badge">
                                            <?php 
                                            if(isset($campaign['parent_category_name']) && isset($campaign['category_name'])) {
                                                echo htmlspecialchars($campaign['category_name']);
                                            } else {
                                                echo 'General';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="campaign-content">
                                    <h5 class="campaign-title"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                                    <p class="campaign-description"><?php echo substr($campaign['description'], 0, 100); ?>...</p>
                                    
                                    <!-- Animated Progress Bar -->
                                    <div class="progress-container">
                                        <div class="progress-labels">
                                            <span class="progress-raised">৳<?php echo number_format($campaign['raised_amount']); ?> raised</span>
                                            <span class="progress-goal">৳<?php echo number_format($campaign['goal_amount']); ?> goal</span>
                                        </div>
                                        <div class="progress progress-animated">
                                            <div class="progress-bar bg-gradient-success" 
                                                 data-width="<?php 
                                                 $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                                 echo min(100, $progress); 
                                                 ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="campaign-meta d-flex justify-content-between text-sm text-muted mb-3">
                                        <span>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($campaign['created_at'])); ?>
                                        </span>
                                        <span>
                                            <?php 
                                            $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                            echo number_format(min(100, $progress), 1); ?>%
                                        </span>
                                    </div>
                                    
                                    <div class="campaign-actions">
                                        <a href="campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary btn-hover-lift w-100">
                                            <i class="fas fa-gift me-2"></i>Donate Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Load More Button (if needed) -->
                    <?php if(count($campaigns) >= 9): ?>
                    <div class="text-center mt-5">
                        <button class="btn btn-outline-primary btn-lg" id="loadMoreBtn">
                            <i class="fas fa-plus me-2"></i>Load More Campaigns
                        </button>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
/* Additional Styles for Campaigns Page */
.page-header-section {
    position: relative;
    overflow: hidden;
}

.page-header-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}

.breadcrumb {
    background: transparent;
    padding: 0;
}

.breadcrumb-item a {
    text-decoration: none;
}

.category-sidebar .list-group-item {
    border: none;
    border-radius: 0;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.category-sidebar .list-group-item.active {
    background: var(--gradient-primary);
    border-color: var(--primary);
    color: white;
}

.category-sidebar .list-group-item:not(.active):hover {
    background: rgba(26, 58, 95, 0.05);
    transform: translateX(5px);
}

.parent-category .list-group-item-light {
    background: #f8f9fa;
    font-size: 0.9rem;
}

.quick-stats .stat-item h4 {
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.quick-stats .stat-item small {
    font-size: 0.75rem;
}
@media (max-width: 991.98px) {
    .quick-stats {
        display: none !important;
    }
}
.empty-state {
    background: white;
    border-radius: 15px;
    padding: 60px 40px;
    box-shadow: var(--shadow-soft);
}

.empty-icon {
    opacity: 0.7;
}

.campaign-options {
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: var(--shadow-soft);
}

.form-select-sm {
    border-radius: 25px;
    border: 1px solid #e9ecef;
}

.btn-group-sm .btn {
    border-radius: 25px;
    margin: 0 2px;
}

/* Mobile Filter Toggle Button */
.mobile-filter-toggle {
    background: var(--gradient-primary);
    border: none;
    border-radius: 10px;
    padding: 12px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.mobile-filter-toggle:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

/* List View Styles */
.campaigns-grid.list-view .campaign-item {
    width: 100%;
    flex: 0 0 100%;
    max-width: 100%;
}

.campaigns-grid.list-view .campaign-card-hover {
    display: flex;
    flex-direction: row;
}

.campaigns-grid.list-view .campaign-image {
    width: 300px;
    height: 200px;
    flex-shrink: 0;
}

.campaigns-grid.list-view .campaign-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Enhanced Mobile Responsive Design */
@media (max-width: 992px) {
    .page-header-section {
        margin-top: 70px;
        padding: 30px 0;
    }
    
    .campaigns-section {
        padding: 30px 0;
    }
    
    .campaign-options {
        flex-direction: column;
        gap: 15px;
        padding: 12px 15px;
    }
    
    .sort-options, .view-options {
        width: 100%;
    }
    
    .sort-options .form-select {
        width: 100%;
    }
    
    .campaigns-grid.list-view .campaign-card-hover {
        flex-direction: column;
    }
    
    .campaigns-grid.list-view .campaign-image {
        width: 100%;
        height: 200px;
    }
    
    /* Mobile category sidebar improvements */
    .category-sidebar {
        margin-top: 15px;
        border-radius: 10px !important;
    }
    
    .category-sidebar .card-body {
        max-height: 400px;
        overflow-y: auto;
    }
    
    /* Mobile campaign grid improvements */
    .campaign-item {
        margin-bottom: 20px;
    }
    
    .campaign-card-hover {
        border-radius: 15px;
    }
    
    .campaign-content {
        padding: 20px !important;
    }
    
    .campaign-title {
        font-size: 1.2rem;
    }
    
    .campaign-description {
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .page-header-section {
        margin-top: 60px;
        padding: 25px 0;
    }
    
    .page-header-section h1 {
        font-size: 1.8rem;
    }
    
    .campaigns-section {
        padding: 25px 0;
    }
    
    .campaign-options {
        padding: 10px 12px;
    }
    
    /* Single column layout for mobile */
    .campaign-item {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .campaign-image {
        height: 200px !important;
    }
    
    .progress-labels {
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    .page-header-section {
        margin-top: 60px;
        padding: 20px 0;
    }
    
    .page-header-section h1 {
        font-size: 1.6rem;
    }
    
    .campaigns-section {
        padding: 20px 0;
    }
    
    .empty-state {
        padding: 40px 20px;
    }
    
    .campaign-actions .btn {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
    
    /* Improved mobile navigation for categories */
    .category-link {
        padding: 10px 12px !important;
        font-size: 0.9rem;
    }
    
    .parent-category .list-group-item-light {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
}

/* Smooth scrolling for mobile category selection */
html {
    scroll-behavior: smooth;
}

/* Enhanced mobile category sidebar animation */
#mobileCategorySidebar.collapsing {
    transition: height 0.3s ease;
}

/* Active state for mobile filter button */
.mobile-filter-toggle[aria-expanded="true"] {
    background: var(--gradient-secondary);
}

/* Improved focus states for mobile */
.category-link:focus {
    outline: 2px solid var(--primary);
    outline-offset: -2px;
}

/* Better touch targets for mobile */
@media (max-width: 992px) {
    .category-sidebar .list-group-item {
        padding: 14px 16px;
        min-height: 50px;
    }
    
    .btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-select {
        min-height: 44px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = width + '%';
        }, 500);
    });

    // Sort functionality
    const sortSelect = document.getElementById('sortCampaigns');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const campaignItems = Array.from(document.querySelectorAll('.campaign-item'));
            
            campaignItems.sort((a, b) => {
                const aRaised = parseFloat(a.querySelector('.progress-raised').textContent.replace(/[^0-9.]/g, ''));
                const bRaised = parseFloat(b.querySelector('.progress-raised').textContent.replace(/[^0-9.]/g, ''));
                const aDate = new Date(a.querySelector('.campaign-meta span:first-child').textContent);
                const bDate = new Date(b.querySelector('.campaign-meta span:first-child').textContent);
                
                switch(sortValue) {
                    case 'newest':
                        return bDate - aDate;
                    case 'oldest':
                        return aDate - bDate;
                    case 'most-funded':
                        return bRaised - aRaised;
                    case 'least-funded':
                        return aRaised - bRaised;
                    default:
                        return 0;
                }
            });
            
            const campaignsGrid = document.getElementById('campaignsGrid');
            campaignsGrid.innerHTML = '';
            campaignItems.forEach(item => campaignsGrid.appendChild(item));
        });
    }

    // View mode toggle
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const campaignsGrid = document.getElementById('campaignsGrid');

    if (gridView && listView && campaignsGrid) {
        gridView.addEventListener('change', function() {
            campaignsGrid.classList.remove('list-view');
            campaignsGrid.classList.add('grid-view');
        });

        listView.addEventListener('change', function() {
            campaignsGrid.classList.remove('grid-view');
            campaignsGrid.classList.add('list-view');
        });
    }

    // Load more functionality
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Simulate loading more campaigns (in a real app, this would be an AJAX call)
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-plus me-2"></i>Load More Campaigns';
                this.disabled = false;
                // In a real implementation, you would append new campaign items here
            }, 1500);
        });
    }

    // Initialize with grid view
    if (campaignsGrid) {
        campaignsGrid.classList.add('grid-view');
    }

    // Enhanced Mobile Category Handling
    const categoryLinks = document.querySelectorAll('.category-link');
    const mobileCategorySidebar = document.getElementById('mobileCategorySidebar');
    
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const categoryId = this.getAttribute('data-category-id');
            
            // On mobile devices, collapse the sidebar and scroll to campaigns
            if (window.innerWidth < 992) {
                // Close the mobile sidebar
                const bsCollapse = new bootstrap.Collapse(mobileCategorySidebar);
                bsCollapse.hide();
                
                // Add a small delay to allow sidebar to collapse before scrolling
                setTimeout(() => {
                    // Scroll to the campaigns section smoothly
                    document.querySelector('.campaigns-section').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            }
            
            // Optional: Add loading state for better UX
            this.classList.add('loading');
            setTimeout(() => {
                this.classList.remove('loading');
            }, 1000);
        });
    });

    // Improved mobile filter toggle button behavior
    const mobileFilterToggle = document.querySelector('[data-bs-target="#mobileCategorySidebar"]');
    if (mobileFilterToggle) {
        mobileFilterToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const icon = this.querySelector('.fa-chevron-down, .fa-chevron-up');
            
            if (icon) {
                if (isExpanded) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            }
        });
    }

    // Auto-close mobile sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992 && mobileCategorySidebar.classList.contains('show')) {
            if (!e.target.closest('.category-sidebar') && !e.target.closest('[data-bs-toggle="collapse"]')) {
                const bsCollapse = new bootstrap.Collapse(mobileCategorySidebar);
                bsCollapse.hide();
                
                // Reset toggle button icon
                const toggleBtn = document.querySelector('[data-bs-target="#mobileCategorySidebar"]');
                const icon = toggleBtn?.querySelector('.fa-chevron-up');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        }
    });

    // Handle browser back/forward buttons with mobile sidebar state
    window.addEventListener('popstate', function() {
        if (window.innerWidth < 992 && mobileCategorySidebar.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(mobileCategorySidebar);
            bsCollapse.hide();
        }
    });
});

// Additional mobile optimization: Prevent horizontal scroll
window.addEventListener('load', function() {
    document.body.style.overflowX = 'hidden';
});

// Handle orientation change for better mobile experience
window.addEventListener('orientationchange', function() {
    setTimeout(() => {
        // Re-trigger any animations or recalculate layouts if needed
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.getAttribute('data-width');
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width + '%';
            }, 100);
        });
    }, 300);
});
</script>

<?php include 'includes/footer.php'; ?>