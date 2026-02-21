<?php
// includes/header.php
// Check if current page is homepage
$is_homepage = (basename($_SERVER['PHP_SELF']) == 'index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NAME') ? SITE_NAME : 'TrueManTrueHelp'; ?> -
        <?php echo isset($page_title) ? $page_title : 'Home'; ?>
    </title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Join TrueManTrueHelp in making a difference. Support campaigns that change lives through education, healthcare, and emergency relief.">
    <meta name="keywords" content="charity, donation, help, support, education, healthcare, emergency relief">
    <meta name="author" content="TrueManTrueHelp">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png" />

  <!-- ===== Preload Critical Resources ===== -->
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="https://unpkg.com/aos@2.3.1/dist/aos.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">

  <!-- ===== Non-Critical CSS (fallbacks) ===== -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1a3a5f;
            --primary-light: #2c5282;
            --primary-dark: #0f2a4a;
            --secondary: #e53e3e;
            --secondary-light: #fc8181;
            --secondary-dark: #c53030;
            --accent: #f6ad55;
            --accent-light: #fbd38d;
            --success: #38a169;
            --warning: #d69e2e;
            --light: #f7fafc;
            --dark: #2d3748;
            --text-light: #718096;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            --gradient-success: linear-gradient(135deg, var(--success) 0%, #48bb78 100%);
            --gradient-warning: linear-gradient(135deg, var(--warning) 0%, var(--accent) 100%);
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 15px 40px rgba(0, 0, 0, 0.12);
            --shadow-large: 0 25px 60px rgba(0, 0, 0, 0.15);
            --shadow-hover: 0 30px 70px rgba(0, 0, 0, 0.2);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            overflow-x: hidden;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.7;
            padding-top: 0;
        }
        /* Page Loader - Only for Home Page */
        <?php if ($is_homepage): ?>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .loader-content {
            text-align: center;
            color: white;
        }
        .loader-logo {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 2rem;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .loader-text {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }
        .loader-word {
            font-size: 2rem;
            font-weight: 700;
            opacity: 0;
            animation: fadeInWord 0.5s ease forwards;
        }
        .loader-word:nth-child(1) {
            color: #e53e3e;
            animation-delay: 0.2s;
        }
        .loader-word:nth-child(2) {
            color: #f6ad55;
            animation-delay: 0.4s;
        }
        .loader-word:nth-child(3) {
            color: #38a169;
            animation-delay: 0.6s;
        }
        .loader-word:nth-child(4) {
            color: #eff1f3ff;
            animation-delay: 0.8s;
        }
        @keyframes fadeInWord {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .loader-progress {
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            overflow: hidden;
            margin: 0 auto;
        }
        .loader-progress-bar {
            height: 100%;
            background: var(--gradient-secondary);
            width: 0%;
            animation: progressLoad 2s ease-in-out forwards;
        }
        @keyframes progressLoad {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        <?php endif; ?>
        /* Enhanced Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }
        .display-title {
            font-size: 3.5rem;
            font-weight: 900;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--gradient-secondary);
            border-radius: 2px;
        }
        .section-title.center::after {
            left: 50%;
            transform: translateX(-50%);
        }
        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto 2rem;
        }
      
   
        .header-contact {
            margin: 0;
            padding: 0;
        }
        .header-contact li {
            margin-right: 25px;
        }
        .header-contact li:last-child {
            margin-right: 0;
        }
        .header-contact a {
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 400;
            font-size: 0.85rem;
        }
        .header-contact a:hover {
            color: var(--accent-light);
            transform: translateY(-1px);
        }
        .header-contact i {
            color: var(--accent-light);
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }
        .header-social {
            margin: 0;
            padding: 0;
        }
        .header-social li {
            margin-left: 12px;
        }
        .header-social li:first-child {
            margin-left: 0;
        }
        .header-social a {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            border-radius: 50%;
            transition: all 0.3s ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }
        .header-social a:hover {
            background: var(--accent);
            color: white;
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(230, 62, 62, 0.3);
        }
        /* Enhanced Main Navbar - FIXED POSITION */
        .navbar-main {
            background: rgba(26, 58, 95, 0.95) !important;
            backdrop-filter: blur(15px);
            box-shadow: var(--shadow-medium);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 8px 0;
            transition: all 0.4s ease;
            z-index: 1030;
            height: 70px;
        }
        .navbar-main.scrolled {
            background: rgba(26, 58, 95, 0.98) !important;
            padding: 6px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            margin-right: 8px;
            font-size: 1.6rem;
        }
        .navbar-nav .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            margin: 0 3px;
            padding: 6px 12px !important;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            font-size: 0.95rem;
        }
        .navbar-nav .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .navbar-nav .nav-link:hover::before,
        .navbar-nav .nav-link.active::before {
            width: 70%;
        }
        
        /* Mobile Menu Button Fixes */
        .navbar-toggler {
            border: none;
            outline: none;
            box-shadow: none;
            padding: 0.5rem;
            margin: 0;
            position: relative;
            z-index: 1050;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }
        
        .navbar-toggler:focus .navbar-toggler-icon {
            outline: 2px solid var(--accent);
            border-radius: 4px;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.9)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 8h24M4 16h24M4 24h24'/%3E%3C/svg%3E");
            width: 24px;
            height: 24px;
            background-repeat: no-repeat;
            background-position: center;
            background-size: 100%;
            transition: all 0.3s ease;
        }
        
        .navbar-toggler:hover .navbar-toggler-icon {
            opacity: 0.8;
        }
        
        @media (max-width: 991.98px) {
            .navbar-toggler {
                display: block !important;
                background-color: rgba(255, 255, 255, 0.15);
                border-radius: 8px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        }
        
        /* Mobile Menu Dropdown Fixes */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background: rgba(26, 58, 95, 0.98);
                backdrop-filter: blur(15px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                padding: 20px;
                max-height: calc(100vh - 115px);
                overflow-y: auto;
                z-index: 1030;
                transform: translateY(-100%);
                transition: transform 0.4s ease;
            }
            
            .navbar-collapse.show {
                transform: translateY(0);
            }
            
            .navbar-nav {
                flex-direction: column;
                width: 100%;
            }
            
            .navbar-nav .nav-item {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .navbar-nav .nav-link {
                display: block;
                width: 100%;
                text-align: center;
                padding: 10px 15px !important;
                margin: 0 !important;
                border-radius: 8px;
                font-size: 1.1rem;
                font-weight: 600;
            }
            
            .navbar-nav .nav-link::before {
                display: none;
            }
            
            .navbar-nav .nav-link:hover,
            .navbar-nav .nav-link.active {
                background: var(--gradient-secondary);
                color: white;
            }
            
            /* Dropdown menus in mobile view */
            .dropdown-menu {
                position: static !important;
                display: none;
                background: rgba(0, 0, 0, 0.2) !important;
                backdrop-filter: blur(5px);
                transform: none !important;
                padding: 10px !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
            }
            
            .dropdown-menu.show {
                display: block;
            }
            
            .dropdown-item {
                color: rgba(255, 255, 255, 0.9) !important;
                padding: 8px 15px !important;
                border-radius: 5px;
                margin: 5px 0 !important;
            }
            
            .dropdown-item:hover {
                background: rgba(255, 255, 255, 0.1) !important;
            }
            
            /* User account section in mobile menu */
            .navbar-nav:last-child {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .navbar-nav:last-child .nav-link {
                font-size: 1rem;
                font-weight: 500;
            }
            
            /* Donate button in mobile menu */
            .navbar-nav .btn-warning {
                width: 100%;
                margin-top: 15px;
                font-size: 1.1rem;
                font-weight: 600;
                padding: 12px;
            }
        }
        
        /* Enhanced Buttons */
        .btn {
            font-weight: 500;
            border-radius: 10px;
            padding: 10px 25px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: var(--shadow-soft);
            font-size: 0.8rem;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn-primary {
            background: var(--gradient-primary);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        .btn-secondary {
            background: var(--gradient-secondary);
        }
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        .btn-success {
            background: var(--gradient-success);
        }
        .btn-warning {
            background: var(--gradient-warning);
        }
        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        .btn-lg {
            padding: 12px 35px;
            font-size: 1rem;
        }
        .btn-sm {
            padding: 6px 16px;
            font-size: 0.85rem;
        }
        .pulse-animation {
            animation: pulse 2s infinite;
            position: relative;
        }
        .pulse-constant {
            animation: pulse 2s infinite;
            background: var(--gradient-secondary);
            position: relative;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(230, 62, 62, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(230, 62, 62, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(230, 62, 62, 0);
            }
        }
        /* Enhanced Hero Carousel - NO GAP */
        .carousel-home {
            margin-top: 0;
            position: relative;
            z-index: 1;
        }
        .carousel-home .carousel-item {
            height: 100vh;
            min-height: 700px;
            background-position: center;
            background-size: cover;
            position: relative;
        }
        .carousel-home .carousel-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(26, 58, 95, 0.7), rgba(26, 58, 95, 0.4));
            z-index: 1;
        }
        .carousel-home .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .carousel-caption {
            top: 50%;
            bottom: auto;
            transform: translateY(-50%);
            text-align: left;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .carousel-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 1s ease;
            line-height: 1.1;
        }
        .carousel-subtitle {
            font-size: 1.3rem;
            font-weight: 400;
            color: white;
            margin-bottom: 2.5rem;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
            animation: fadeInUp 1s ease 0.3s both;
            max-width: 600px;
        }
        .carousel-home .btn-secondary {
            background: var(--gradient-secondary);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            animation: fadeInUp 1s ease 0.6s both;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(230, 62, 62, 0.3);
        }
        .carousel-home .btn-secondary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(230, 62, 62, 0.4);
        }
        .carousel-indicators {
            bottom: 40px;
        }
        .carousel-indicators button {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin: 0 10px;
            background-color: rgba(255, 255, 255, 0.5);
            border: none;
            transition: all 0.3s ease;
        }
        .carousel-indicators button.active {
            background-color: var(--accent);
            transform: scale(1.2);
        }
        .carousel-control-prev,
        .carousel-control-next {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .carousel:hover .carousel-control-prev,
        .carousel:hover .carousel-control-next {
            opacity: 1;
        }
        .carousel-control-prev {
            left: 30px;
        }
        .carousel-control-next {
            right: 30px;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 25px;
            height: 25px;
        }
        /* Animation Keyframes */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Enhanced Stats Section */
        .stats-section {
            background: var(--gradient-primary);
            position: relative;
            overflow: hidden;
            padding: 80px 0;
        }
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.03)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
            background-size: cover;
        }
        .stat-card {
            padding: 40px 30px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            z-index: -1;
        }
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .stat-icon {
            margin-bottom: 20px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }
        .stat-card:hover .stat-icon {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.1);
        }
        .stat-icon i {
            font-size: 2.5rem;
            color: white;
        }
        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: white;
        }
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            font-weight: 500;
        }
        /* Enhanced Category Cards */
        .category-section {
            position: relative;
            overflow: hidden;
            padding: 80px 0;
        }
        .category-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(247,250,252,0.8)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
            background-size: cover;
            z-index: -1;
        }
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-secondary);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .category-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
        }
        .category-card:hover::before {
            transform: scaleX(1);
        }
        .category-card-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }
        .category-icon {
            width: 90px;
            height: 90px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 25px rgba(26, 58, 95, 0.2);
        }
        .category-card:hover .category-icon {
            background: var(--gradient-secondary);
            transform: scale(1.1) rotate(5deg);
        }
        .category-icon i {
            color: white !important;
            font-size: 2.2rem;
        }
        .category-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.3;
            font-size: 1.3rem;
        }
        .category-parent {
            font-size: 0.9rem;
            margin-bottom: 20px;
            flex-grow: 1;
            color: var(--text-light);
        }
        /* Enhanced Campaign Cards */
        .campaign-card-hover {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s ease;
            background: white;
            position: relative;
        }
        .campaign-card-hover:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
        }
        .campaign-image {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .campaign-card-hover:hover .campaign-image img {
            transform: scale(1.1);
        }
        .campaign-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.7));
            display: flex;
            align-items: flex-end;
            padding: 25px;
        }
        .campaign-badge {
            background: var(--gradient-secondary);
            border: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .campaign-content {
            padding: 30px;
        }
        .campaign-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
            font-size: 1.4rem;
            line-height: 1.4;
        }
        .campaign-description {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        /* Enhanced Progress Bars */
        .progress-container {
            margin-bottom: 25px;
        }
        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .progress-raised {
            color: var(--primary);
            font-weight: 600;
        }
        .progress-goal {
            color: var(--text-light);
        }
        .progress-animated {
            height: 12px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .progress-animated .progress-bar {
            height: 100%;
            border-radius: 10px;
            width: 0;
            transition: width 1.5s ease-in-out;
            background: var(--gradient-success);
            position: relative;
            overflow: hidden;
        }
        .progress-animated .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        /* Enhanced Completed Campaigns */
        .completed-campaigns-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
        }
        .completed-campaign-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s ease;
            position: relative;
            border: 2px solid transparent;
        }
        .completed-campaign-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
            border-color: #28a745;
        }
        .completed-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gradient-success);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: pulse-success 2s infinite;
        }
        @keyframes pulse-success {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        .completed-badge i {
            margin-right: 5px;
        }
        .completed-campaign-card .campaign-image {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        .completed-campaign-card .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }
        .completed-campaign-card:hover .campaign-image img {
            transform: scale(1.1);
        }
        .success-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(32, 201, 151, 0.9));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s ease;
        }
        .completed-campaign-card:hover .success-overlay {
            opacity: 1;
        }
        .success-content {
            text-align: center;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }
        .completed-campaign-card:hover .success-content {
            transform: translateY(0);
        }
        .success-stats {
            border-top: 2px dashed #e9ecef;
            border-bottom: 2px dashed #e9ecef;
            padding: 15px 0;
        }
        .success-stats .stat h4 {
            font-weight: 700;
        }
        .impact-message {
            border-left: 3px solid #28a745;
        }
        /* Enhanced Story Section Styles */
        .story-section-enhanced {
            position: relative;
            overflow: hidden;
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f1f3f4 100%);
        }
        .story-section-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(26,58,95,0.02)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
            background-size: cover;
            z-index: 0;
        }
        .story-content-enhanced {
            position: relative;
            z-index: 1;
        }
        .story-lead {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--dark);
            margin-bottom: 2rem;
            font-weight: 500;
        }
        /* Evolution Card */
        .evolution-card {
            display: flex;
            align-items: flex-start;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
            border-left: 4px solid var(--success);
            transition: all 0.4s ease;
            margin-bottom: 2rem;
        }
        .evolution-card:hover {
            transform: translateX(10px);
            box-shadow: var(--shadow-medium);
        }
        .evolution-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .evolution-icon i {
            color: white;
            font-size: 1.5rem;
        }
        .evolution-content h5 {
            color: var(--dark);
            margin-bottom: 10px;
            font-weight: 600;
        }
        .evolution-content p {
            color: var(--text-light);
            margin-bottom: 0;
            line-height: 1.6;
        }
        /* Trust Promise Card */
        .trust-promise-card {
            border-radius: 15px;
            border: 2px solid transparent;
            background: linear-gradient(white, white) padding-box,
                var(--gradient-primary) border-box;
            transition: all 0.4s ease;
        }
        .trust-promise-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .promise-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .promise-icon i {
            color: white;
            font-size: 1.3rem;
        }
        /* Empowerment & Care Sections */
        .empowerment-section,
        .care-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-soft);
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        .empowerment-section {
            border-left-color: var(--warning);
        }
        .care-section {
            border-left-color: var(--danger);
        }
        .empowerment-section:hover,
        .care-section:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        .empowerment-title,
        .care-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
        }
        .empowerment-text,
        .care-text {
            color: var(--text-light);
            line-height: 1.7;
            margin-bottom: 0;
        }
        /* Vision Statement */
        .vision-statement {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .vision-statement::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
            background-size: cover;
        }
        .vision-icon {
            opacity: 0.9;
        }
        .vision-title {
            font-weight: 700;
            color: white;
        }
        .vision-text {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        /* Enhanced 3D Image Container */
        .story-image-container-enhanced {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .story-image-3d {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            cursor: pointer;
        }
        .story-image-3d:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1.02);
            box-shadow:
                0 35px 70px -12px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }
        .story-image-main {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: all 0.6s ease;
        }
        .story-image-3d:hover .story-image-main {
            transform: scale(1.1);
        }
        .image-overlay-3d {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                    rgba(26, 58, 95, 0.9) 0%,
                    rgba(230, 62, 62, 0.7) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s ease;
        }
        .story-image-3d:hover .image-overlay-3d {
            opacity: 1;
        }
        .overlay-content {
            text-align: center;
            transform: translateY(20px);
            transition: all 0.4s ease;
        }
        .story-image-3d:hover .overlay-content {
            transform: translateY(0);
        }
        /* Floating Stats */
        .floating-stats {
            position: absolute;
            top: 50%;
            right: -30px;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 2;
        }
        .stat-floating {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: var(--shadow-medium);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 140px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        .stat-floating:hover {
            transform: translateX(-10px) scale(1.05);
            box-shadow: var(--shadow-hover);
        }
        .stat-floating .stat-icon {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .stat-floating .stat-icon i {
            color: white;
            font-size: 1.2rem;
        }
        .stat-content h5 {
            margin: 0;
            font-weight: 700;
            color: var(--dark);
            font-size: 1.3rem;
        }
        .stat-content small {
            color: var(--text-light);
            font-weight: 500;
        }
        /* Impact Highlights */
        .impact-highlights {
            margin-top: 50px;
        }
        .highlight-card {
            background: white;
            padding: 30px 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s ease;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .highlight-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        .highlight-icon-wrapper {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.4s ease;
        }
        .highlight-card:hover .highlight-icon-wrapper {
            background: var(--gradient-secondary);
            transform: scale(1.1) rotate(5deg);
        }
        .highlight-icon-wrapper i {
            color: white;
            font-size: 2rem;
        }
        .highlight-card h5 {
            color: var(--dark);
            margin-bottom: 15px;
            font-weight: 600;
        }
        .highlight-card p {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 0;
        }
        /* Responsive Design */
        @media (max-width: 1200px) {
            .floating-stats {
                right: -20px;
            }
            .stat-floating {
                min-width: 130px;
                padding: 12px;
            }
        }
        @media (max-width: 992px) {
            .story-section-enhanced {
                padding: 80px 0;
            }
            .story-image-3d {
                transform: none;
                margin-top: 40px;
            }
            .floating-stats {
                position: static;
                transform: none;
                flex-direction: row;
                justify-content: center;
                margin-top: 30px;
                gap: 15px;
            }
            .stat-floating {
                min-width: auto;
                flex: 1;
                max-width: 150px;
            }
            .evolution-card {
                flex-direction: column;
                text-align: center;
            }
            .evolution-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
        @media (max-width: 768px) {
            .story-section-enhanced {
                padding: 60px 0;
            }
            .story-lead {
                font-size: 1.1rem;
            }
            .story-image-main {
                height: 400px;
            }
            .floating-stats {
                flex-wrap: wrap;
            }
            .stat-floating {
                min-width: 120px;
            }
            .highlight-card {
                padding: 25px 20px;
                margin-bottom: 20px;
            }
            .highlight-icon-wrapper {
                width: 70px;
                height: 70px;
            }
            .highlight-icon-wrapper i {
                font-size: 1.8rem;
            }
        }
        @media (max-width: 576px) {
            .story-section-enhanced {
                padding: 50px 0;
            }
            .story-image-main {
                height: 300px;
            }
            .floating-stats {
                flex-direction: column;
                align-items: center;
            }
            .stat-floating {
                max-width: 200px;
                width: 100%;
            }
            .evolution-card,
            .trust-promise-card,
            .empowerment-section,
            .care-section {
                padding: 20px;
            }
            .vision-statement {
                padding: 25px 20px;
            }
        }
        /* Animation Keyframes */
        @keyframes floatInFromRight {
            from {
                opacity: 0;
                transform: translateX(50px) rotateY(15deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotateY(0);
            }
        }
        @keyframes floatInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-50px) rotateY(-15deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotateY(0);
            }
        }
        .story-content-enhanced [data-aos="fade-right"] {
            animation: floatInFromLeft 0.8s ease-out;
        }
        .story-image-container-enhanced [data-aos="fade-left"] {
            animation: floatInFromRight 0.8s ease-out;
        }
        .highlight-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }
        .highlight-item:hover {
            transform: translateX(10px);
            box-shadow: var(--shadow-medium);
        }
        .highlight-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .highlight-icon i {
            color: white;
            font-size: 1.5rem;
        }
        .highlight-content h5 {
            color: var(--primary);
            margin-bottom: 8px;
        }
        .highlight-content p {
            color: var(--text-light);
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 6s ease-in-out infinite;
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .floating-element i {
            font-size: 1.8rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .element-1 {
            top: 10%;
            left: -5%;
            animation-delay: 0s;
        }
        .element-2 {
            top: 30%;
            right: -5%;
            animation-delay: 2s;
        }
        .element-3 {
            bottom: 20%;
            left: 10%;
            animation-delay: 4s;
        }
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0);
            }
        }
        /* Responsive Design */
        @media (max-width: 1200px) {
            .display-title {
                font-size: 3rem;
            }
            .carousel-title {
                font-size: 3rem;
            }
        }
        @media (max-width: 992px) {
            .display-title {
                font-size: 2.5rem;
            }
            .section-title {
                font-size: 2rem;
            }
            .carousel-title {
                font-size: 2.5rem;
            }
            .carousel-subtitle {
                font-size: 1.2rem;
            }
            .story-image {
                transform: none;
                margin-top: 40px;
            }
            .navbar-main {
                padding: 6px 0;
            }
        }
        @media (max-width: 768px) {
            
            .navbar-main {
                top: 0;
                height: 60px;
                padding: 4px 0;
            }
            .navbar-brand {
                font-size: 1.4rem;
            }
            .header-contact li {
                margin-right: 15px;
            }
            .header-social a {
                width: 28px;
                height: 28px;
                line-height: 28px;
                font-size: 0.8rem;
            }
            .carousel-home .carousel-item {
                height: 70vh;
                min-height: 500px;
            }
            .carousel-title {
                font-size: 2.2rem;
            }
            .carousel-subtitle {
                font-size: 1.1rem;
            }
            .carousel-home .btn-secondary {
                padding: 12px 30px;
                font-size: 1rem;
            }
            .carousel-control-prev,
            .carousel-control-next {
                width: 50px;
                height: 50px;
            }
            .carousel-control-prev {
                left: 15px;
            }
            .carousel-control-next {
                right: 15px;
            }
            .stat-number {
                font-size: 2.8rem;
            }
            .category-card {
                padding: 30px 20px;
            }
            .category-icon {
                width: 80px;
                height: 80px;
            }
            .category-icon i {
                font-size: 2rem;
            }
            .campaign-image {
                height: 220px;
            }
            .section-title {
                font-size: 1.8rem;
            }
        }
        @media (max-width: 576px) {
           
            .header-contact li {
                margin-right: 10px;
            }
            .header-social a {
                width: 25px;
                height: 25px;
                line-height: 25px;
                font-size: 0.7rem;
            }
            .header-contact li {
                display: block;
                margin: 1px 0;
            }
            .navbar-main {
                top: 0px;
                height: 60px;
            }
            .navbar-brand {
                font-size: 1.2rem;
            }
            .carousel-home .carousel-item {
                height: 60vh;
                min-height: 400px;
            }
            .carousel-title {
                font-size: 1.8rem;
            }
            .carousel-subtitle {
                font-size: 1rem;
            }
            .carousel-caption {
                padding: 0 15px;
                text-align: center;
            }
            .display-title {
                font-size: 1.8rem;
            }
            .section-title {
                font-size: 1.6rem;
            }
            .btn-lg {
                padding: 10px 25px;
                font-size: 0.9rem;
            }
            .stat-card {
                padding: 30px 20px;
            }
            .stat-number {
                font-size: 2.5rem;
            }
            .category-card {
                padding: 25px 15px;
            }
            .category-icon {
                width: 70px;
                height: 70px;
            }
            .category-icon i {
                font-size: 1.8rem;
            }
            .campaign-content {
                padding: 20px;
            }
            .campaign-title {
                font-size: 1.2rem;
            }
            .timeline {
                padding-left: 30px;
            }
            .timeline-item {
                padding: 20px;
            }
            .timeline-marker {
                left: -33px;
            }
        }
        /* Category Carousel Styles */
        .category-carousel-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }
        .category-carousel-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.3)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
            background-size: cover;
            z-index: 0;
        }
        #categoryCarousel {
            position: relative;
            z-index: 1;
        }
        .carousel-inner {
            padding: 20px 0;
        }
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: var(--shadow-soft);
            transition: all 0.4s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-secondary);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .category-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
        }
        .category-card:hover::before {
            transform: scaleX(1);
        }
        .category-card-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }
        .category-icon {
            width: 90px;
            height: 90px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 25px rgba(26, 58, 95, 0.2);
        }
        .category-card:hover .category-icon {
            background: var(--gradient-secondary);
            transform: scale(1.1) rotate(5deg);
        }
        .category-icon i {
            color: white !important;
            font-size: 2.2rem;
        }
        .category-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            line-height: 1.3;
            font-size: 1.3rem;
        }
        .category-parent {
            font-size: 0.9rem;
            margin-bottom: 20px;
            flex-grow: 1;
            color: var(--text-light);
        }
        /* Carousel Controls */
        .category-carousel-control {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        #categoryCarousel:hover .category-carousel-control {
            opacity: 1;
        }
        .category-carousel-control:hover {
            background: var(--gradient-secondary);
            transform: translateY(-50%) scale(1.1);
        }
        .carousel-control-prev {
            left: 30px;
        }
        .carousel-control-next {
            right: 30px;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 25px;
            height: 25px;
        }
        /* Carousel Indicators */
        .carousel-indicators-container {
            display: flex;
            justify-content: center;
        }
        .carousel-indicators {
            position: static;
            margin: 0;
            display: flex;
            gap: 10px;
        }
        .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(26, 58, 95, 0.3);
            border: none;
            transition: all 0.3s ease;
            margin: 0;
        }
        .carousel-indicators button.active {
            background-color: var(--primary);
            transform: scale(1.2);
        }
        .carousel-indicators button:hover {
            background-color: var(--primary-light);
        }
        /* Auto Carousel Animation */
        .carousel-item {
            transition: transform 0.8s ease-in-out;
        }
        /* Responsive Design */
        @media (max-width: 1200px) {
            .category-card {
                padding: 35px 25px;
            }
            .category-icon {
                width: 80px;
                height: 80px;
            }
            .category-icon i {
                font-size: 2rem;
            }
        }
        @media (max-width: 992px) {
            .category-carousel-section {
                padding: 60px 0;
            }
            .category-card {
                padding: 30px 20px;
            }
            .category-icon {
                width: 70px;
                height: 70px;
            }
            .category-icon i {
                font-size: 1.8rem;
            }
            .category-title {
                font-size: 1.1rem;
            }
            .category-carousel-control {
                width: 50px;
                height: 50px;
                opacity: 0.7;
            }
            .carousel-control-prev {
                left: 10px;
            }
            .carousel-control-next {
                right: 10px;
            }
        }
        @media (max-width: 768px) {
            .category-carousel-section {
                padding: 50px 0;
            }
            .category-carousel-control {
                width: 45px;
                height: 45px;
                opacity: 0.8;
            }
            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                width: 20px;
                height: 20px;
            }
            .category-card {
                padding: 25px 15px;
                margin: 0 10px;
            }
            .category-icon {
                width: 65px;
                height: 65px;
            }
            .category-icon i {
                font-size: 1.6rem;
            }
            .category-title {
                font-size: 1rem;
            }
        }
        @media (max-width: 576px) {
            .category-carousel-section {
                padding: 40px 0;
            }
            .category-carousel-control {
                display: none;
            }
            .category-card {
                padding: 20px 15px;
                margin: 0 5px;
            }
            .category-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 15px;
            }
            .category-icon i {
                font-size: 1.5rem;
            }
            .category-parent {
                font-size: 0.8rem;
                margin-bottom: 15px;
            }
        }
        /* Animation for carousel items */
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
        .carousel-item.active .category-card {
            animation: fadeInUp 0.6s ease-out;
        }
        .carousel-item.active .category-card:nth-child(1) {
            animation-delay: 0.1s;
        }
        .carousel-item.active .category-card:nth-child(2) {
            animation-delay: 0.2s;
        }
        .carousel-item.active .category-card:nth-child(3) {
            animation-delay: 0.3s;
        }
    </style>
    <!-- ===== JS Libraries (loaded via CDN, not from /assets/libs/) ===== -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
  
</head>
<body>
    <!-- Page Loader - Only for Home Page -->
    <?php if ($is_homepage): ?>
    <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-logo">
                <i class="fas fa-hands-helping"></i> TrueManTrueHelp
            </div>
            <div class="loader-text">
                <span class="loader-word">True</span>
                <span class="loader-word">Man</span>
                <span class="loader-word">True</span>
                <span class="loader-word">Help</span>
            </div>
            <div class="loader-progress">
                <div class="loader-progress-bar"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-main">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hands-helping"></i>
                <?php echo defined('SITE_NAME') ? SITE_NAME : 'TrueManTrueHelp'; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_homepage ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php">Campaigns</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php">Gallery</a>
                    </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="#footer-contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="application.php">Application</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-user me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="campaigns.php" class="btn btn-warning pulse-constant mt-2 mt-lg-0">
                            Donate 
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    