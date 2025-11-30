<?php
// includes/functions.php

// =============================================
// AUTHENTICATION & USER FUNCTIONS
// =============================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

/**
 * Redirect to specified URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user by phone number
 */
function getUserByPhone($phone) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Login user with email and password
 */
function loginUser($email, $password) {
    $user = getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

/**
 * Login user with either email or phone
 */
function loginUserWithEmailOrPhone($login, $password) {
    global $pdo;
    
    // Try email first
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        // Check if this is a guest user (has default password or is_guest flag)
        if (password_verify('123456', $user['password']) || (isset($user['is_guest']) && $user['is_guest'] == 1)) {
            $_SESSION['guest_user'] = true;
        }
        
        return true;
    }
    return false;
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    session_destroy();
    redirect('login.php');
}

/**
 * Register new user
 */
function registerUser($name, $email, $password) {
    global $pdo;
    
    // Check if email already exists
    $existingUser = getUserByEmail($email);
    if ($existingUser) {
        return "Email already registered!";
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    
    try {
        $stmt->execute([$name, $email, $hashed_password]);
        return true;
    } catch(PDOException $e) {
        return "Registration failed: " . $e->getMessage();
    }
}

/**
 * Handle guest donor registration/login
 */
function handleGuestDonor($name, $email, $phone) {
    global $pdo;
    
    // Check if user already exists by email or phone
    $existing_user = null;
    
    if (!empty($email)) {
        $existing_user = getUserByEmail($email);
    }
    
    if (!$existing_user && !empty($phone)) {
        $existing_user = getUserByPhone($phone);
    }
    
    if ($existing_user) {
        // User exists, return user ID
        return $existing_user['id'];
    } else {
        // Create new guest user
        return createGuestUser($name, $email, $phone);
    }
}

/**
 * Handle guest donor with single email/phone field (Enhanced version)
 */
function handleGuestDonorSingleField($name, $email_or_phone) {
    global $pdo;
    
    // Validate inputs
    if (empty($name) || empty($email_or_phone)) {
        error_log("Guest donor: Missing name or email/phone");
        return false;
    }
    
    // Determine if it's email or phone
    $email = null;
    $phone = null;
    
    if (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
        $email = $email_or_phone;
        error_log("Guest donor: Identified as email - $email");
    } else {
        // Clean phone number - remove any non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $email_or_phone);
        error_log("Guest donor: Identified as phone - $phone");
    }
    
    // Check if user already exists by email or phone
    $existing_user = null;
    
    if (!empty($email)) {
        $existing_user = getUserByEmail($email);
        if ($existing_user) {
            error_log("Guest donor: Found existing user by email - ID: " . $existing_user['id']);
        }
    }
    
    if (!$existing_user && !empty($phone)) {
        $existing_user = getUserByPhone($phone);
        if ($existing_user) {
            error_log("Guest donor: Found existing user by phone - ID: " . $existing_user['id']);
        }
    }
    
    if ($existing_user) {
        // User exists, return user ID
        error_log("Guest donor: Returning existing user ID: " . $existing_user['id']);
        return $existing_user['id'];
    } else {
        // Create new guest user
        error_log("Guest donor: Creating new guest user");
        return createGuestUser($name, $email, $phone);
    }
}

/**
 * Create guest user with auto-generated password (Enhanced version)
 */
function createGuestUser($name, $email, $phone) {
    global $pdo;
    
    // Validate that we have at least one contact method
    if (empty($email) && empty($phone)) {
        error_log("Guest user creation failed: Both email and phone are empty");
        return false;
    }
    
    // Generate a simple password for guest users
    $default_password = '123456';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, phone, password, role, is_guest, created_at) 
            VALUES (?, ?, ?, ?, 'user', 1, NOW())
        ");
        
        $stmt->execute([
            $name,
            $email ?: null,
            $phone ?: null,
            $hashed_password
        ]);
        
        $user_id = $pdo->lastInsertId();
        error_log("Guest user created successfully - ID: " . $user_id);
        
        return $user_id;
        
    } catch(PDOException $e) {
        error_log("Guest user creation error: " . $e->getMessage());
        
        // Handle duplicate phone/email gracefully
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            error_log("Duplicate entry detected, finding existing user");
            
            // User already exists, try to find them
            $existing_user = null;
            if (!empty($email)) {
                $existing_user = getUserByEmail($email);
            } 
            if (!$existing_user && !empty($phone)) {
                $existing_user = getUserByPhone($phone);
            }
            
            if ($existing_user) {
                error_log("Found existing user after duplicate error - ID: " . $existing_user['id']);
                return $existing_user['id'];
            }
        }
        
        return false;
    }
}

/**
 * Auto-login user after guest donation (Enhanced version)
 */
function autoLoginUser($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['guest_user'] = true; // Mark as guest user
        
        error_log("Auto-login successful for user ID: " . $user['id']);
        return true;
    }
    
    error_log("Auto-login failed: User not found with ID: " . $user_id);
    return false;
}

/**
 * Check if current user is a guest (auto-created)
 */
function isGuestUser() {
    return isset($_SESSION['guest_user']) && $_SESSION['guest_user'] === true;
}

// =============================================
// CATEGORY FUNCTIONS
// =============================================

/**
 * Get all parent categories
 */
function getParentCategories() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Parent categories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get subcategories by parent ID
 */
function getSubcategories($parent_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND status = 'active' ORDER BY name");
        $stmt->execute([$parent_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Subcategories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get complete category hierarchy
 */
function getCategoryHierarchy() {
    global $pdo;
    $hierarchy = [];
    
    try {
        $parent_stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name");
        $parents = $parent_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($parents as $parent) {
            $parent['children'] = getSubcategories($parent['id']);
            $hierarchy[] = $parent;
        }
    } catch(PDOException $e) {
        error_log("Category hierarchy error: " . $e->getMessage());
        return [];
    }
    
    return $hierarchy;
}

/**
 * Get category full name with parent
 */
function getCategoryFullName($category_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT c1.name as subcategory, c2.name as parent 
                          FROM categories c1 
                          LEFT JOIN categories c2 ON c1.parent_id = c2.id 
                          WHERE c1.id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category && $category['parent']) {
            return $category['parent'] . ' > ' . $category['subcategory'];
        }
        return $category['subcategory'] ?? 'General';
    } catch(PDOException $e) {
        error_log("Category full name error: " . $e->getMessage());
        return 'General';
    }
}

/**
 * Display category dropdown with hierarchy
 */
function displayCategoryDropdown($selected = '') {
    $hierarchy = getCategoryHierarchy();
    $html = '';
    
    foreach ($hierarchy as $parent) {
        $html .= '<optgroup label="' . htmlspecialchars($parent['name']) . '">';
        foreach ($parent['children'] as $child) {
            $selected_attr = ($selected == $child['id']) ? 'selected' : '';
            $html .= '<option value="' . $child['id'] . '" ' . $selected_attr . '>';
            $html .= htmlspecialchars($child['name']);
            $html .= '</option>';
        }
        $html .= '</optgroup>';
    }
    
    return $html;
}

/**
 * Get category icon for display
 */
function getCategoryIcon($categoryName) {
    // Convert to lowercase for case-insensitive matching
    $name = strtolower(trim($categoryName));
    
    $iconMap = [
        // Emergency & Disaster
        'emergency' => 'first-aid',
        'relief' => 'first-aid',
        'disaster' => 'house-damage',
        'recovery' => 'house-damage',
        'refugee' => 'users',
        
        // Education
        'education' => 'graduation-cap',
        'school' => 'graduation-cap',
        'learning' => 'graduation-cap',
        'training' => 'tools',
        'vocational' => 'tools',
        
        // Healthcare
        'health' => 'heartbeat',
        'medical' => 'stethoscope',
        'care' => 'heartbeat',
        'hospital' => 'hospital',
        
        // Animals & Environment
        'animal' => 'paw',
        'wildlife' => 'paw',
        'environment' => 'tree',
        'conservation' => 'tree',
        'green' => 'tree',
        
        // Community & Social
        'community' => 'home',
        'development' => 'home',
        'elderly' => 'user-friends',
        'children' => 'child',
        'women' => 'female',
        'empowerment' => 'female',
        
        // Basic Needs
        'food' => 'utensils',
        'hunger' => 'utensils',
        'water' => 'tint',
        'clean' => 'tint',
        'security' => 'shield-alt',
        
        // Default
        'donation' => 'heart',
        'causes' => 'heart'
    ];
    
    // Check for exact matches first
    foreach ($iconMap as $key => $icon) {
        if (strpos($name, $key) !== false) {
            return $icon;
        }
    }
    
    // Default icon
    return 'heart';
}

// =============================================
// CAMPAIGN FUNCTIONS
// =============================================

/**
 * Get featured campaigns
 */
function getFeaturedCampaigns($limit = 6) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' 
            ORDER BY c.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Campaign fetch error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get campaigns by category
 */
function getCampaignsByCategory($category_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' AND c.category_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Campaign by category error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get campaigns by parent category
 */
function getCampaignsByParentCategory($parent_category_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.status = 'active' AND cat.parent_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$parent_category_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Campaign by parent category error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get single campaign by ID
 */
function getCampaignById($campaign_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name, parent.name as parent_category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            LEFT JOIN categories parent ON cat.parent_id = parent.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$campaign_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Campaign by ID error: " . $e->getMessage());
        return null;
    }
}

/**
 * Calculate campaign progress percentage
 */
function getCampaignProgress($raised, $goal) {
    if ($goal == 0) return 0;
    return min(100, ($raised / $goal) * 100);
}

/**
 * Format campaign progress for display
 */
function formatCampaignProgress($raised, $goal) {
    $progress = getCampaignProgress($raised, $goal);
    return number_format($progress, 1) . '%';
}

// Get total campaigns count
function getTotalCampaignsCount() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'active'");
    return $stmt->fetchColumn();
}

// Get active campaigns count
function getActiveCampaignsCount() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'active'");
    return $stmt->fetchColumn();
}

// Get category campaigns count
function getCategoryCampaignsCount($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM campaigns WHERE category_id = ? AND status = 'active'");
    $stmt->execute([$category_id]);
    return $stmt->fetchColumn();
}

// =============================================
// DONATION FUNCTIONS
// =============================================

/**
 * Create new donation (Updated for Bikash support and guest donations)
 */
function createDonation($campaign_id, $donor_name, $donor_email, $amount, $payment_method, $message = '', $is_anonymous = false, $user_id = null, $status = 'completed', $transaction_id = '', $donor_phone = '') {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert donation record with phone
        $stmt = $pdo->prepare("
            INSERT INTO donations (campaign_id, donor_id, donor_name, donor_email, donor_phone, amount, payment_method, message, is_anonymous, status, transaction_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $campaign_id,
            $user_id,
            $donor_name,
            $donor_email,
            $donor_phone,
            $amount,
            $payment_method,
            $message,
            $is_anonymous ? 1 : 0,
            $status,
            $transaction_id
        ]);
        
        $donation_id = $pdo->lastInsertId();
        
        // Only update campaign raised amount if payment is completed
        if ($status == 'completed') {
            $update_stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?");
            $update_stmt->execute([$amount, $campaign_id]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        return $donation_id;
        
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Donation creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update donation status (For admin approval of Bikash payments)
 */
function updateDonationStatus($donation_id, $new_status) {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get current donation details
        $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$donation_id]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$donation) {
            $pdo->rollBack();
            return false;
        }
        
        $old_status = $donation['status'];
        $amount = $donation['amount'];
        $campaign_id = $donation['campaign_id'];
        
        // Update donation status
        $update_stmt = $pdo->prepare("UPDATE donations SET status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $donation_id]);
        
        // Handle campaign amount updates based on status change
        if ($old_status != 'completed' && $new_status == 'completed') {
            // Adding amount to campaign
            $campaign_stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?");
            $campaign_stmt->execute([$amount, $campaign_id]);
        } elseif ($old_status == 'completed' && $new_status != 'completed') {
            // Removing amount from campaign
            $campaign_stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount - ? WHERE id = ?");
            $campaign_stmt->execute([$amount, $campaign_id]);
        }
        
        $pdo->commit();
        return true;
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log("Donation status update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update campaign raised amount
 */
function updateCampaignRaisedAmount($campaign_id, $amount) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?");
        $stmt->execute([$amount, $campaign_id]);
        return true;
    } catch(PDOException $e) {
        error_log("Campaign amount update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get donations by user
 */
function getUserDonations($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, c.title as campaign_title
            FROM donations d 
            LEFT JOIN campaigns c ON d.campaign_id = c.id 
            WHERE d.donor_id = ? 
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("User donations error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get pending Bikash donations count
 */
function getPendingBikashCount() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM donations WHERE status = 'pending' AND payment_method = 'bikash'");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    } catch(PDOException $e) {
        error_log("Pending Bikash count error: " . $e->getMessage());
        return 0;
    }
}

// =============================================
// ADMIN FUNCTIONS
// =============================================

/**
 * Get admin dashboard stats
 */
function getAdminStats() {
    global $pdo;
    
    $stats = [
        'total_campaigns' => 0,
        'total_donations' => 0,
        'total_amount' => 0,
        'total_users' => 0,
        'pending_bikash' => 0
    ];
    
    try {
        // Total campaigns
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM campaigns");
        $stats['total_campaigns'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total donations
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM donations");
        $stats['total_donations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total amount raised
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'");
        $stats['total_amount'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Pending Bikash donations
        $stats['pending_bikash'] = getPendingBikashCount();
        
    } catch(PDOException $e) {
        error_log("Admin stats error: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Get recent donations for admin
 */
function getRecentDonations($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT d.*, c.title as campaign_title 
            FROM donations d 
            LEFT JOIN campaigns c ON d.campaign_id = c.id 
            ORDER BY d.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Recent donations error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent campaigns for admin
 */
function getRecentCampaigns($limit = 4) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, cat.name as category_name
            FROM campaigns c 
            LEFT JOIN categories cat ON c.category_id = cat.id 
            ORDER BY c.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Recent campaigns error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent users for admin
 */
function getRecentUsers($limit = 5) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, email, created_at, role 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Recent users error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get monthly revenue data for charts
 */
function getMonthlyRevenue() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                MONTH(created_at) as month,
                SUM(amount) as revenue
            FROM donations 
            WHERE status = 'completed'
            GROUP BY MONTH(created_at)
            ORDER BY month
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Monthly revenue error: " . $e->getMessage());
        return [];
    }
}

// =============================================
// UTILITY FUNCTIONS
// =============================================

/**
 * Format currency (Updated for Taka)
 */
function formatCurrency($amount) {
    return '৳' . number_format($amount, 2);
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Check if email is valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get base URL
 */
function getBaseUrl() {
    return defined('SITE_URL') ? SITE_URL : 'http://localhost/charity_website';
}

/**
 * Debug function (for development only)
 */
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

/**
 * Get site name
 */
function getSiteName() {
    return defined('SITE_NAME') ? SITE_NAME : 'TrueManTrueHelp';
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'F j, Y') {
    if (!$date) return '';
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Get time ago format
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Validate and upload image
 */
function uploadImage($file, $target_dir = "assets/images/") {
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'File too large. Maximum size is 5MB.'];
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, JPEG, PNG, GIF, WEBP.'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $filename, 'path' => $target_file];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }
}

/**
 * Send email notification
 */
function sendEmail($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n" .
                   "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get pagination parameters
 */
function getPaginationParams($page, $per_page = 10) {
    $page = max(1, intval($page));
    $offset = ($page - 1) * $per_page;
    return ['page' => $page, 'per_page' => $per_page, 'offset' => $offset];
}

/**
 * Generate pagination HTML
 */
function generatePagination($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Get Bikash phone number for donations
 */
function getBikashPhone() {
    return defined('BIKASH_PHONE') ? BIKASH_PHONE : '01XXXXXXXXX';
}

/**
 * Validate Bikash transaction ID format
 */
function isValidBikashTransaction($transaction_id) {
    // Bikash transaction IDs are typically alphanumeric and 8-12 characters long
    $transaction_id = trim($transaction_id);
    if (empty($transaction_id)) {
        return false;
    }
    
    // Basic validation - can be enhanced based on Bikash transaction ID patterns
    if (strlen($transaction_id) < 8 || strlen($transaction_id) > 20) {
        return false;
    }
    
    // Should be alphanumeric
    if (!preg_match('/^[A-Za-z0-9]+$/', $transaction_id)) {
        return false;
    }
    
    return true;
}
?>