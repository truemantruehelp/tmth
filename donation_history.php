<?php
// donation_history.php
require_once 'includes/config.php';
if (!isLoggedIn()) redirect('login.php');

$page_title = "Donation History";
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h4 class="m-0 font-weight-bold text-primary">Your Donation History</h4>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Donation History Page</h5>
                    <p class="text-muted">This page will show your complete donation history with receipts.</p>
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>