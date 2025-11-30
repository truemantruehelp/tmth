<!-- Updated admin/sidebar.php -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-4">
            <h5 class="text-white mb-0">
                <i class="fas fa-heart me-2"></i>
                <span class="sidebar-logo-text">CharityAdmin</span>
            </h5>
            <button class="btn btn-sm btn-outline-light sidebar-toggle d-none d-md-block">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'campaigns.php' ? 'active' : ''; ?>" href="campaigns.php">
                    <i class="fas fa-hands-helping me-2"></i>
                    <span class="sidebar-text">Campaigns</span>
                </a>
            </li>
            
            
            <!-- Add other menu items similarly -->
        </ul>
    </div>
</nav>