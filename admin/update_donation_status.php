<?php
// admin/update_donation_status.php
require_once '../includes/config.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donation_id = $_POST['donation_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';
    
    if ($donation_id && in_array($new_status, ['completed', 'pending', 'failed', 'refunded'])) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Get current donation details
            $stmt = $pdo->prepare("SELECT * FROM donations WHERE id = ?");
            $stmt->execute([$donation_id]);
            $donation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($donation) {
                // Update donation status
                $update_stmt = $pdo->prepare("UPDATE donations SET status = ? WHERE id = ?");
                $update_stmt->execute([$new_status, $donation_id]);
                
                // If status changed to completed and it wasn't completed before, update campaign raised amount
                if ($new_status == 'completed' && $donation['status'] != 'completed') {
                    $campaign_stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?");
                    $campaign_stmt->execute([$donation['amount'], $donation['campaign_id']]);
                }
                // If status changed from completed to something else, subtract from campaign raised amount
                elseif ($donation['status'] == 'completed' && $new_status != 'completed') {
                    $campaign_stmt = $pdo->prepare("UPDATE campaigns SET raised_amount = raised_amount - ? WHERE id = ?");
                    $campaign_stmt->execute([$donation['amount'], $donation['campaign_id']]);
                }
                
                $pdo->commit();
                $_SESSION['success_message'] = "Donation status updated successfully!";
            } else {
                $_SESSION['error_message'] = "Donation not found!";
            }
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Error updating donation: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Invalid request!";
    }
}

// Redirect back to donations page
header("Location: donations.php");
exit();
?>