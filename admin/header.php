<?php
if (!isset($page_title)) $page_title = "Admin Dashboard";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['sidebar_collapsed'])) $_SESSION['sidebar_collapsed'] = false;
$sidebarCollapsed = $_SESSION['sidebar_collapsed'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars("TrueManTrueHelp - Admin - " . $page_title); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root {
  --sidebar-w: 260px;
  --sidebar-collapsed: 74px;
  --topbar-h: 64px;
  --bg-dark: #0f172a;
  --bg-dark2: #1e293b;
  --muted: rgba(255,255,255,0.7);
  --accent: #4f46e5;
}
body {
  margin: 0;
  font-family: "Inter", Poppins, sans-serif;
  background: var(--bg-dark);
  color: #f1f5f9;
  overflow-x: hidden;
}

/* SIDEBAR */
.sidebar {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: var(--sidebar-w);
  background: linear-gradient(180deg, var(--bg-dark2), var(--bg-dark));
  transition: width .3s ease, transform .3s ease;
  z-index: 1100;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 12px rgba(0,0,0,0.4);
}
body.sidebar-collapsed .sidebar { width: var(--sidebar-collapsed); }

.brand {
  display: flex; align-items: center; gap: 10px;
  padding: 18px; border-bottom: 1px solid rgba(255,255,255,0.05);
}
.logo-icon {
  width: 42px; height: 42px; border-radius: 10px;
  background: linear-gradient(135deg,#6366f1,#4338ca);
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: 18px;
}
.brand-name { font-weight: 700; font-size: 1.05rem; }
body.sidebar-collapsed .brand-name { display: none; }

/* Menu */
.menu {
  flex: 1; overflow-y: auto; padding: 10px 0;
}
.menu-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 18px; margin: 4px 10px;
  text-decoration: none; color: var(--muted);
  border-radius: 10px; transition: all .25s;
}
.menu-item:hover { background: rgba(255,255,255,0.08); color: #fff; transform: translateX(4px); }
.menu-item.active { background: rgba(255,255,255,0.12); color: #fff; }
.menu-item i { width: 24px; text-align: center; }
body.sidebar-collapsed .menu-text { display: none; }

/* ============================================= */
/* MODAL FIXES - ADD THIS TO ADMIN/HEADER.PHP */
/* ============================================= */

.modal {
    z-index: 1060 !important;
    position: fixed !important;
}

.modal-backdrop {
    z-index: 1040 !important;
}

.modal-dialog {
    margin-top: 80px !important;
    z-index: 1061 !important;
}

.modal-content {
    z-index: 1062 !important;
}

.topbar {
    z-index: 1030 !important;
}

.sidebar {
    z-index: 1020 !important;
}

/* Ensure modals are fully visible on mobile */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 60px 10px 20px !important;
    }
    
    .modal-content {
        max-height: calc(100vh - 80px);
        overflow-y: auto;
    }
}
/* Sidebar footer */
.sidebar-footer {
  border-top: 1px solid rgba(255,255,255,0.05);
  padding: 12px 16px;
  display: flex; align-items: center; gap: 10px;
}
.user-avatar {
  width: 42px; height: 42px; border-radius: 50%;
  background: linear-gradient(135deg,#60a5fa,#3b82f6);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
}
.user-info { flex: 1; }
.user-name { font-weight: 600; }
.user-role { font-size: 13px; color: var(--muted); }
body.sidebar-collapsed .user-info { display: none; }

/* Toggle button */
.sidebar-toggle-edge {
  position: absolute; right: -18px; top: 20px;
  width: 40px; height: 40px; border-radius: 8px;
  background: hsla(0, 0%, 0%, 0.91); color: #fff;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid rgba(212, 127, 47, 1);
  cursor: pointer; z-index: 1500;
  transition: transform .2s, background .2s;
  
}

.sidebar-toggle-edge:hover { background: rgba(239, 101, 15, 0.25); transform: scale(1.05); }

/* TOPBAR */
.topbar {
  position: fixed; top: 0; right: 0; left: var(--sidebar-w);
  height: var(--topbar-h);
  background: linear-gradient(90deg,var(--bg-dark2),var(--bg-dark));
  border-bottom: 1px solid rgba(255,255,255,0.08);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; transition: left .3s ease;
  z-index: 1080; box-shadow: 0 2px 8px rgba(0,0,0,0.4);
}
body.sidebar-collapsed .topbar { left: var(--sidebar-collapsed); }

.page-title { font-weight: 700; color: #fff; }
.search-box {
  max-width: 340px; display: flex; align-items: center;
  background: rgba(255,255,255,0.1); border-radius: 10px; padding: 6px 10px;
}
.search-box input {
  background: transparent; border: none; outline: none; color: #fff; width: 100%;
}
.search-box i { color: var(--muted); margin-right: 8px; }

.top-actions { display: flex; align-items: center; gap: 12px; }
.top-action-btn {
  background: rgba(255,255,255,0.08); border: none; color: var(--muted);
  border-radius: 8px; padding: 8px 10px; transition: .2s;
}
.top-action-btn:hover { color: #fff; background: rgba(255,255,255,0.15); }

.dropdown-menu {
  background: var(--bg-dark2);
  border: 1px solid rgba(255,255,255,0.1);
  display: none; /* Start hidden */
}
.dropdown-menu.show {
  display: block; /* Show when has .show class */
}
.dropdown-item { color: #cbd5e1; }
.dropdown-item:hover { background: rgba(255,255,255,0.08); color: #fff; }

/* MAIN CONTENT */
.main-content {
  margin-left: var(--sidebar-w); margin-top: var(--topbar-h);
  padding: 20px; transition: margin-left .3s ease;
}
body.sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed); }

/* MOBILE */
@media (max-width: 991px) {
  .sidebar { transform: translateX(-100%); }
  .sidebar.mobile-open { transform: translateX(0); }
  .topbar { left: 0; }
  .main-content { margin-left: 0; }
  
  /* Mobile menu toggle button */
  .mobile-menu-toggle {
    display: inline-flex !important;
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 8px 12px;
    margin-right: 15px;
    transition: background .2s;
  }
  
  .mobile-menu-toggle:hover {
    background: rgba(255,255,255,0.2);
  }
  
  /* Adjust page title for mobile */
  .page-title {
    font-size: 1.1rem;
  }
  
  /* Hide edge toggle on mobile */
  .sidebar-toggle-edge {
    display: none;
  }
}

/* Desktop - hide mobile toggle */
.mobile-menu-toggle {
  display: none;
}

/* Overlay */
.mobile-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: none; z-index: 1000;
}
.mobile-overlay.active { display: block; }
</style>
</head>

<body class="<?php echo $sidebarCollapsed ? 'sidebar-collapsed' : ''; ?>">
<div class="mobile-overlay"></div>

<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="logo-icon"><i class="fas fa-hands-helping"></i></div>
    <div class="brand-name">TMTH</div>
  </div>
  <button id="sidebarEdgeToggle" class="sidebar-toggle-edge">
    <i class="fas <?php echo $sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'; ?>" id="edgeToggleIcon"></i>
  </button>

  <nav class="menu">
    <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i><span class="menu-text">Dashboard</span></a>
    <a href="categories.php" class="menu-item"><i class="fas fa-tags"></i><span class="menu-text">Categories</span></a>
    <a href="campaigns.php" class="menu-item"><i class="fas fa-hands-helping"></i><span class="menu-text">Campaigns</span></a>
    <a href="donations.php" class="menu-item"><i class="fas fa-donate"></i><span class="menu-text">Donations</span></a>
    <a href="applications.php" class="menu-item"><i class="fas fa-file-alt"></i><span class="menu-text">Applications</span></a>
    
    <a href="reports.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reports</span></a>
    <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Users</span></a>
     
  </nav>

  <div class="sidebar-footer">
    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'AD',0,2)); ?></div>
    <div class="user-info">
      <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></div>
      
    </div>
    <a href="../logout.php" class="btn btn-sm text-light" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
  </div>
</aside>

<header class="topbar">
  <!-- Mobile Menu Toggle Button -->
  <button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
  </button>
  
  <h4 class="page-title mb-0"><?php echo htmlspecialchars($page_title); ?></h4>

  <div class="top-actions d-flex align-items-center">
    <div class="search-box d-none d-md-flex">
      <i class="fas fa-search"></i>
      <input type="text" placeholder="Search..." aria-label="Search" />
    </div>

    <button class="top-action-btn" type="button" title="Notifications"><i class="fas fa-bell"></i></button>

    <div class="dropdown ms-2">
      <button id="adminDropdownBtn" class="top-action-btn dropdown-toggle" type="button">
        <i class="fas fa-user-circle"></i>
        <span class="d-none d-md-inline ms-1" style="color:var(--muted);font-weight:600;">
          <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:200px;z-index:1500;">
        <li><a class="dropdown-item" href="../index.php"><i class="fas fa-home me-2"></i> View Site</a></li>
        <li><a class="dropdown-item" href="../dashboard.php"><i class="fas fa-user me-2"></i> My Account</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-light" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</header>

<main class="main-content">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Simple manual dropdown that works
  const adminBtn = document.getElementById('adminDropdownBtn');
  if (adminBtn) {
    adminBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const menu = this.nextElementSibling;
      menu.classList.toggle('show');
    });
  }
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
      });
    }
  });

  // Sidebar toggle functionality
  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('sidebarEdgeToggle');
  const mobileToggleBtn = document.getElementById('mobileMenuToggle');
  const toggleIcon = document.getElementById('edgeToggleIcon');
  const overlay = document.querySelector('.mobile-overlay');

  const isMobile = () => window.innerWidth <= 991;

  // Desktop sidebar toggle
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      if (isMobile()) {
        const isOpen = sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active', isOpen);
        toggleIcon.className = isOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right';
      } else {
        const collapsed = body.classList.toggle('sidebar-collapsed');
        toggleIcon.className = collapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        // Save sidebar state
        fetch('sidebar_toggle.php', {
          method: 'POST',
          body: new URLSearchParams({ collapsed: collapsed ? '1' : '0' })
        });
      }
    });
  }

  // Mobile menu toggle
  if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', () => {
      const isOpen = sidebar.classList.toggle('mobile-open');
      overlay.classList.toggle('active', isOpen);
      
      // Update edge toggle icon if it exists
      if (toggleIcon) {
        toggleIcon.className = isOpen ? 'fas fa-chevron-left' : 'fas fa-chevron-right';
      }
    });
  }

  // Overlay click to close sidebar
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('mobile-open');
      overlay.classList.remove('active');
      if (toggleIcon) {
        toggleIcon.className = 'fas fa-chevron-right';
      }
    });
  }

  // Close sidebar when clicking on a menu item in mobile view
  if (isMobile()) {
    document.querySelectorAll('.menu-item').forEach(item => {
      item.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
        if (toggleIcon) {
          toggleIcon.className = 'fas fa-chevron-right';
        }
      });
    });
  }

  // Handle window resize
  window.addEventListener('resize', () => {
    if (!isMobile() && sidebar.classList.contains('mobile-open')) {
      sidebar.classList.remove('mobile-open');
      overlay.classList.remove('active');
      if (toggleIcon) {
        toggleIcon.className = 'fas fa-chevron-right';
      }
    }
  });
});
</script>
</body>
</html>