<?php
// reset_admin.php - Run this once then DELETE
require_once 'includes/config.php';

echo "<h3>Admin Password Reset</h3>";

// Generate proper hash for Admin@321
$new_password = 'Admin@321';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'admin@tmth.store']);
    
    echo "✅ Password reset successfully!<br>";
    echo "Email: admin@tmth.store<br>";
    echo "Password: Admin@321<br>";
    echo "New Hash: " . $hashed_password . "<br><br>";
    echo '<a href="login.php" class="btn btn-success">Go to Login</a>';
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>