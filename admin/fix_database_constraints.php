<?php
// fix_database_constraints.php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die("Access denied. Only administrators can run this script.");
}

$success = false;
$errors = [];

try {
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Fix 1: Update donations table constraints
    try {
        $pdo->exec("ALTER TABLE donations DROP FOREIGN KEY donations_ibfk_1");
        $pdo->exec("ALTER TABLE donations ADD CONSTRAINT donations_ibfk_1 FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE");
        $success = true;
    } catch (PDOException $e) {
        $errors[] = "Error updating donations constraints: " . $e->getMessage();
    }
    
    // Fix 2: Update categories table to allow NULL parent_id
    try {
        $pdo->exec("ALTER TABLE categories MODIFY parent_id INT NULL");
        $success = true;
    } catch (PDOException $e) {
        $errors[] = "Error updating categories table: " . $e->getMessage();
    }
    
    // Fix 3: Update any other tables that might reference campaigns
    try {
        // Check if campaign_updates table exists and update it
        $tableExists = $pdo->query("SHOW TABLES LIKE 'campaign_updates'")->rowCount() > 0;
        if ($tableExists) {
            $pdo->exec("ALTER TABLE campaign_updates DROP FOREIGN KEY campaign_updates_ibfk_1");
            $pdo->exec("ALTER TABLE campaign_updates ADD CONSTRAINT campaign_updates_ibfk_1 FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE");
        }
    } catch (PDOException $e) {
        $errors[] = "Error updating campaign_updates: " . $e->getMessage();
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
} catch (PDOException $e) {
    $errors[] = "General database error: " . $e->getMessage();
}

// Display results
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Constraints Fix</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='container mt-5'>
    <div class='card'>
        <div class='card-header'>
            <h2>Database Constraints Fix Results</h2>
        </div>
        <div class='card-body'>";

if ($success && empty($errors)) {
    echo "<div class='alert alert-success'>
            <h4>✅ Success!</h4>
            <p>Database constraints have been updated successfully.</p>
            <ul>
                <li>Donations table now has ON DELETE CASCADE</li>
                <li>Categories table allows NULL parent_id</li>
                <li>Foreign key checks have been re-enabled</li>
            </ul>
            <p>You can now:</p>
            <ul>
                <li>Create categories without parent (main categories)</li>
                <li>Delete campaigns even if they have donations</li>
                <li>Update campaigns without foreign key constraints blocking</li>
            </ul>
            <a href='categories.php' class='btn btn-success me-2'>Go to Categories</a>
            <a href='campaigns.php' class='btn btn-primary'>Go to Campaigns</a>
        </div>";
} else if (!empty($errors)) {
    echo "<div class='alert alert-warning'>
            <h4>⚠️ Partial Success with Warnings</h4>
            <p>Some operations completed but there were warnings:</p>
            <ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>
            <p>Main operations should still work.</p>
            <a href='categories.php' class='btn btn-warning me-2'>Test Categories</a>
            <a href='campaigns.php' class='btn btn-primary'>Test Campaigns</a>
        </div>";
} else {
    echo "<div class='alert alert-danger'>
            <h4>❌ Failed</h4>
            <p>Could not update database constraints.</p>";
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    echo "</div>";
}

echo "      </div>
    </div>
</body>
</html>";
?>