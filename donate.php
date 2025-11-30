<?php
// donate.php - Enhanced with Guest Donation System (Single Field)
require_once 'includes/config.php';

$campaign_id = $_GET['campaign'] ?? 0;
$amount = $_GET['amount'] ?? 0;

// Debug: Check incoming parameters
error_log("Donation Debug - Campaign ID: $campaign_id, Amount: $amount");

if (!$campaign_id || !$amount) {
    error_log("Donation Debug - Missing campaign ID or amount");
    header("Location: campaigns.php");
    exit();
}

$page_title = "Make Donation";
include 'includes/header.php';

// Get campaign details
try {
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name, parent.name as parent_category_name
        FROM campaigns c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN categories parent ON cat.parent_id = parent.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$campaign_id]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $campaign = null;
    error_log("Donation Debug - Campaign query error: " . $e->getMessage());
}

if (!$campaign) {
    header("Location: campaigns.php");
    exit();
}

// Calculate progress
$progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
$progress = min(100, $progress);

// Bikash phone number
$bikash_phone = "+880 1859-135478";

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_name = trim($_POST['donor_name'] ?? '');
    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $message = trim($_POST['message'] ?? '');
    $is_anonymous = isset($_POST['is_anonymous']) ? true : false;
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    
    // Validate inputs
    $error = '';
    
    // Basic validation
    if (empty($donor_name)) {
        $error = "Please enter your name!";
    } elseif (empty($email_or_phone)) {
        $error = "Please provide your email or phone number!";
    } elseif ($amount <= 0) {
        $error = "Invalid donation amount!";
    } elseif ($payment_method == 'bikash' && empty($transaction_id)) {
        $error = "Transaction ID is required for Bikash payments!";
    }
    
    // Determine if input is email or phone
    $donor_email = null;
    $donor_phone = null;
    
    if (empty($error)) {
        if (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
            $donor_email = $email_or_phone;
        } elseif (preg_match('/^[0-9+\-\s()]{10,20}$/', $email_or_phone)) {
            $donor_phone = $email_or_phone;
        } else {
            $error = "Please enter a valid email address or phone number!";
        }
    }
    
    if (empty($error)) {
        // Handle user authentication/creation
        $user_id = null;
        
        if (isLoggedIn()) {
            // Use logged-in user
            $user_id = $_SESSION['user_id'];
            $donor_email = $donor_email ?: $_SESSION['user_email'];
            error_log("Donation: Using logged-in user ID: " . $user_id);
        } else {
            // Guest donation - create or get user account
            error_log("Donation: Processing guest donation");
            error_log("Donation: Name: $donor_name, Email/Phone: $email_or_phone");
            
            $user_id = handleGuestDonorSingleField($donor_name, $email_or_phone);
            
            if (!$user_id) {
                $error = "Unable to process your donation. Please try again.";
                error_log("Donation: Guest user creation failed");
            } else {
                error_log("Donation: Guest user created/got ID: " . $user_id);
                // Auto-login the guest user
                if (autoLoginUser($user_id)) {
                    error_log("Donation: Auto-login successful");
                } else {
                    error_log("Donation: Auto-login failed");
                }
            }
        }
        
        if ($user_id && empty($error)) {
            // Set status based on payment method
            $status = ($payment_method == 'bikash') ? 'pending' : 'completed';
            
            // Create donation record
            $donation_id = createDonation(
                $campaign_id, 
                $donor_name, 
                $donor_email, 
                $amount, 
                $payment_method, 
                $message, 
                $is_anonymous, 
                $user_id,
                $status,
                $transaction_id,
                $donor_phone
            );
            
            if ($donation_id) {
                if ($payment_method == 'bikash') {
                    $success = "Thank you for your donation of ৳" . number_format($amount, 0) . "! Your Bikash payment is pending verification. We'll update the status once we confirm your transaction.";
                } else {
                    $success = "Thank you for your donation of ৳" . number_format($amount, 0) . "! Your contribution has been recorded successfully.";
                }
                
                // Update session with success for redirect
                $_SESSION['donation_success'] = true;
                $_SESSION['donation_amount'] = $amount;
                $_SESSION['campaign_title'] = $campaign['title'];
                $_SESSION['payment_method'] = $payment_method;
                $_SESSION['guest_donation'] = !isLoggedIn(); // Mark if this was a guest donation
                $_SESSION['user_identifier'] = $email_or_phone; // Store for display
                
            } else {
                $error = "There was an error processing your donation. Please try again.";
            }
        }
    }
}
?>

<!-- Page Header Section -->
<section class="page-header-section py-4" style="margin-top: 115px; background: var(--gradient-primary);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                        <li class="breadcrumb-item"><a href="campaigns.php" class="text-white">Campaigns</a></li>
                        <li class="breadcrumb-item"><a href="campaign.php?id=<?php echo $campaign_id; ?>" class="text-white"><?php echo htmlspecialchars($campaign['title']); ?></a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Donate</li>
                    </ol>
                </nav>
                <h1 class="text-white mb-2" data-aos="fade-right">Complete Your Donation</h1>
                <p class="text-white mb-0" data-aos="fade-right" data-aos-delay="100">
                    Support: <strong><?php echo htmlspecialchars($campaign['title']); ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end" data-aos="fade-left">
                <div class="header-stats">
                    <div class="stat-item">
                        <h3 class="text-white mb-1">৳<?php echo number_format($amount, 0); ?></h3>
                        <small class="text-white-50">Donation Amount</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donation Content Section -->
<section class="donation-section py-5">
    <div class="container">
        <div class="row">
            <!-- Main Donation Form -->
            <div class="col-lg-8" data-aos="fade-right">
                <?php if(isset($success)): ?>
                    <!-- Success Message -->
                    <div class="donation-success-card card border-0 shadow-sm mb-4">
                        <div class="card-header bg-<?php echo ($_SESSION['payment_method'] == 'bikash') ? 'warning' : 'success'; ?> text-white py-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?php echo ($_SESSION['payment_method'] == 'bikash') ? 'clock' : 'check-circle'; ?> fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-1">
                                        <?php echo ($_SESSION['payment_method'] == 'bikash') ? 'Donation Pending Verification' : 'Donation Successful!'; ?>
                                    </h4>
                                    <p class="mb-0"><?php echo $success; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($_SESSION['payment_method'] == 'bikash'): ?>
                        <div class="card-body p-4">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Next Steps:</h6>
                                <ul class="mb-0">
                                    <li>We have recorded your donation information</li>
                                    <li>Our team will verify your Bikash transaction</li>
                                    <li>You'll receive confirmation email once verified</li>
                                </ul>
                            </div>
                            
                            <div class="verification-details p-3 bg-light rounded">
                                <h6 class="mb-3">Verification Details:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Transaction ID:</strong><br>
                                        <code><?php echo htmlspecialchars($transaction_id); ?></code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Amount Sent:</strong><br>
                                        ৳<?php echo number_format($amount, 0); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Guest Account Information -->
                        <?php if(isset($_SESSION['guest_donation']) && $_SESSION['guest_donation']): ?>
                        <div class="card-body border-top">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-user-plus"></i> Account Created for You!</h6>
                                <p class="mb-2">We've automatically created an account for you so you can track your donations.</p>
                                <div class="guest-account-info">
                                    <strong>Login Details:</strong><br>
                                    <small>
                                        Email/Phone: <strong><?php echo htmlspecialchars($_SESSION['user_identifier']); ?></strong><br>
                                        Password: <code>123456</code> (You can change this later)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-footer bg-light p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="campaign.php?id=<?php echo $campaign_id; ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-arrow-left"></i> Return to Campaign
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="dashboard.php" class="btn btn-success w-100">
                                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="campaigns.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-search"></i> Browse More
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Show updated campaign progress (only for completed payments) -->
                        <?php if($_SESSION['payment_method'] != 'bikash'): ?>
                        <div class="card-body border-top">
                            <h6 class="mb-3">Updated Campaign Progress</h6>
                            <?php 
                            // Get updated campaign data
                            $updated_stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ?");
                            $updated_stmt->execute([$campaign_id]);
                            $updated_campaign = $updated_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($updated_campaign) {
                                $new_raised = $updated_campaign['raised_amount'];
                                $goal = $updated_campaign['goal_amount'];
                                $new_progress = ($new_raised / $goal) * 100;
                                $new_progress = min(100, $new_progress);
                            ?>
                            <div class="progress-container">
                                <div class="progress-labels d-flex justify-content-between mb-2">
                                    <span class="progress-raised fw-bold text-primary">
                                        ৳<?php echo number_format($new_raised, 0); ?> raised
                                    </span>
                                    <span class="progress-goal text-muted">
                                        ৳<?php echo number_format($goal, 0); ?> goal
                                    </span>
                                </div>
                                <div class="progress progress-animated" style="height: 12px;">
                                    <div class="progress-bar bg-gradient-success" 
                                         style="width: <?php echo $new_progress; ?>%">
                                    </div>
                                </div>
                                <div class="progress-stats text-center mt-2">
                                    <small class="text-muted">
                                        <strong><?php echo number_format($new_progress, 1); ?>%</strong> of goal reached
                                    </small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <!-- Donation Form -->
                    <form method="POST" action="">
                        <div class="donation-form-card card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white py-4">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-heart me-2"></i> Complete Your Donation
                                </h4>
                            </div>
                            
                            <div class="card-body p-4">
                                <?php if(isset($error)): ?>
                                    <div class="alert alert-danger" data-aos="shake">
                                        <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                                        <p class="mb-0"><?php echo $error; ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Donation Summary -->
                                <div class="donation-summary-card card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="fas fa-receipt me-2"></i>Donation Summary
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Campaign:</strong><br>
                                                    <span class="text-dark"><?php echo htmlspecialchars($campaign['title']); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Amount:</strong><br>
                                                    <span class="text-success fw-bold fs-5">৳<?php echo number_format($amount, 0); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="campaign-progress mt-3">
                                            <div class="progress-container">
                                                <div class="progress-labels d-flex justify-content-between mb-2">
                                                    <span class="progress-raised fw-bold text-primary">
                                                        ৳<?php echo number_format($campaign['raised_amount'], 0); ?> raised
                                                    </span>
                                                    <span class="progress-goal text-muted">
                                                        ৳<?php echo number_format($campaign['goal_amount'], 0); ?> goal
                                                    </span>
                                                </div>
                                                <div class="progress progress-animated" style="height: 10px;">
                                                    <div class="progress-bar bg-gradient-success" 
                                                         style="width: <?php echo $progress; ?>%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Donor Information -->
                                <div class="donor-info-section mb-4">
                                    <h5 class="section-title mb-4">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo isLoggedIn() ? 'Your Information' : 'Donor Information'; ?>
                                    </h5>
                                    
                                    <?php if(isLoggedIn()): ?>
                                        <!-- Logged in user - show minimal info -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            You're donating as: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> 
                                            (<?php echo htmlspecialchars($_SESSION['user_email']); ?>)
                                        </div>
                                        <input type="hidden" name="donor_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
                                        <input type="hidden" name="email_or_phone" value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>">
                                    <?php else: ?>
                                        <!-- Guest donor - show full form -->
                                        <div class="guest-donor-notice alert alert-warning mb-4">
                                            <h6><i class="fas fa-bolt"></i> Quick Donation</h6>
                                            <p class="mb-0">No need to register! Just fill in your details below. We'll create an account automatically so you can track your donations later.</p>
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" name="donor_name" 
                                                           value="<?php echo isset($_POST['donor_name']) ? htmlspecialchars($_POST['donor_name']) : ''; ?>" 
                                                           placeholder="Your Full Name" required>
                                                    <label>Full Name *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" name="email_or_phone" 
                                                           value="<?php echo isset($_POST['email_or_phone']) ? htmlspecialchars($_POST['email_or_phone']) : ''; ?>" 
                                                           placeholder="Enter your email or phone number" required>
                                                    <label>Email or Phone Number *</label>
                                                    <small class="text-muted mt-1 d-block">We'll use this to create your account automatically</small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <div class="form-floating">
                                            <textarea class="form-control" name="message" 
                                                      placeholder="Ref-Name,example-John..." 
                                                      style="height: 100px"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                            <label>Reference Name (Optional)</label>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" <?php echo isset($_POST['is_anonymous']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_anonymous">
                                            <i class="fas fa-user-secret me-1"></i> Make this donation anonymous
                                        </label>
                                    </div>
                                </div>

                                <!-- Payment Method Selection -->
                                <div class="payment-method-section mb-4">
                                    <h5 class="section-title mb-4">
                                        <i class="fas fa-credit-card me-2"></i>Choose Payment Method
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="payment-method-card" data-method="cash">
                                                <div class="card-body text-center p-3">
                                                    <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                    <h6>Cash/Offline</h6>
                                                    <small class="text-muted">Local Payment</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-method-card active" data-method="bikash">
                                                <div class="card-body text-center p-3">
                                                    <i class="fas fa-mobile-alt fa-2x text-danger mb-2"></i>
                                                    <h6>Bikash</h6>
                                                    <small class="text-muted">Mobile Banking</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-method-card" data-method="stripe">
                                                <div class="card-body text-center p-3">
                                                    <i class="fab fa-cc-stripe fa-2x text-primary mb-2"></i>
                                                    <h6>Credit Card</h6>
                                                    <small class="text-muted">Stripe</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-method-card" data-method="paypal">
                                                <div class="card-body text-center p-3">
                                                    <i class="fab fa-paypal fa-2x text-primary mb-2"></i>
                                                    <h6>PayPal</h6>
                                                    <small class="text-muted">Secure</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="payment_method" value="bikash" id="paymentMethod">
                                </div>

                                <!-- Bikash Instructions -->
                                <div class="bikash-instructions alert alert-info mb-4" id="bikashInstructions">
                                    <h6><i class="fas fa-info-circle"></i> Bikash Payment Instructions</h6>
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <p class="mb-2"><strong>Step 1:</strong> Send ৳<?php echo number_format($amount, 0); ?> to our Bikash number:</p>
                                            <h5 class="text-primary my-2"><?php echo $bikash_phone; ?></h5>
                                            <p class="mb-2"><strong>Step 2:</strong> After payment, you'll receive a transaction ID</p>
                                            <p class="mb-0"><strong>Step 3:</strong> Enter that transaction ID below to complete your donation</p>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="bikash-qr bg-white p-3 rounded">
                                                <i class="fas fa-qrcode fa-4x text-muted"></i>
                                                <small class="d-block mt-2">Scan to Pay</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction ID Field -->
                                <div class="mb-4" id="transactionIdField">
                                    <label for="transaction_id" class="form-label fw-bold">Bikash Transaction ID *</label>
                                    <input type="text" class="form-control form-control-lg" id="transaction_id" name="transaction_id" 
                                           value="<?php echo isset($_POST['transaction_id']) ? htmlspecialchars($_POST['transaction_id']) : ''; ?>"
                                           placeholder="Enter the transaction ID from your Bikash payment" required>
                                    <small class="text-muted">Example: 8A7D6F5G4H3J or similar code from Bikash confirmation message</small>
                                </div>

                                <!-- Payment Notice -->
                                <div class="payment-notice alert alert-warning">
                                    <h6><i class="fas fa-info-circle"></i> Payment Notice</h6>
                                    <ul class="mb-0">
                                        <li><strong>Cash/Offline:</strong> Donation recorded immediately</li>
                                        <li><strong>Bikash:</strong> Requires transaction ID verification (pending until approved by admin)</li>
                                        <li><strong>Credit Card & PayPal:</strong> Integration coming soon!</li>
                                    </ul>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg py-3 pulse-animation" name="submit_donation">
                                        <i class="fas fa-check-circle me-2"></i> 
                                        <?php echo isLoggedIn() ? 'Complete Donation' : 'Donate Now (No Registration Needed)'; ?>
                                    </button>
                                    <a href="campaign.php?id=<?php echo $campaign_id; ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Back to Campaign
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <!-- Campaign Info Card -->
                <div class="campaign-info-card card border-0 shadow-sm mb-4 sticky-top" style="top: 135px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i> Campaign Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="campaign-image mb-3">
                            <img src="<?php echo $campaign['image'] ?: 'assets/images/default-campaign.jpg'; ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                        </div>
                        
                        <h6 class="text-primary"><?php echo htmlspecialchars($campaign['title']); ?></h6>
                        <p class="text-muted small mb-3"><?php echo substr($campaign['short_description'] ?? $campaign['description'], 0, 100); ?>...</p>
                        
                        <div class="campaign-stats">
                            <div class="stat-item d-flex justify-content-between mb-2">
                                <span class="text-muted">Category:</span>
                                <span class="fw-bold">
                                    <?php 
                                    if(isset($campaign['category_name'])) {
                                        echo htmlspecialchars($campaign['category_name']);
                                    } else {
                                        echo 'General';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="stat-item d-flex justify-content-between mb-2">
                                <span class="text-muted">Progress:</span>
                                <span class="fw-bold text-success"><?php echo number_format($progress, 1); ?>%</span>
                            </div>
                            <div class="stat-item d-flex justify-content-between">
                                <span class="text-muted">Raised:</span>
                                <span class="fw-bold">৳<?php echo number_format($campaign['raised_amount'], 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="security-card card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="security-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-success"></i>
                        </div>
                        <h6 class="text-success mb-3">Secure & Verified</h6>
                        <div class="security-features">
                            <div class="feature-item mb-2">
                                <i class="fas fa-lock text-success me-2"></i>
                                <small>SSL Encrypted</small>
                            </div>
                            <div class="feature-item mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Verified Campaign</small>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-user-shield text-success me-2"></i>
                                <small>100% Protected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Enhanced styles for guest donation system */
.guest-donor-notice {
    border-left: 4px solid var(--accent);
}

.guest-account-info {
    background: rgba(255,255,255,0.5);
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}

/* Rest of your existing styles remain the same */
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

.donation-section {
    background: var(--light);
}

.donation-form-card,
.donation-success-card,
.campaign-info-card,
.security-card {
    border-radius: 15px;
    overflow: hidden;
}

.donation-summary-card {
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

.payment-method-card {
    cursor: pointer;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: white;
}

.payment-method-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
}

.payment-method-card.active {
    border-color: var(--success);
    background: linear-gradient(135deg, #f8fff9, #e8f5e8);
    box-shadow: var(--shadow-soft);
}

.payment-method-card .card-body {
    padding: 20px 15px;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--gradient-secondary);
    border-radius: 2px;
}

.bikash-instructions {
    border-radius: 10px;
    border-left: 4px solid var(--info);
}

.bikash-qr {
    border: 2px dashed #dee2e6;
}

.security-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.security-icon {
    opacity: 0.8;
}

.feature-item {
    display: flex;
    align-items: center;
    justify-content: center;
}

.verification-details {
    border-left: 3px solid var(--info);
}

/* Form enhancements */
.form-floating .form-control {
    border-radius: 10px;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header-section {
        margin-top: 60px;
        padding: 30px 0;
    }
    
    .campaign-info-card.sticky-top,
    .donation-sidebar-card.sticky-top {
        position: relative !important;
        top: 0 !important;
    }
    
    .payment-method-card .card-body {
        padding: 15px 10px;
    }
    
    .bikash-instructions .row {
        text-align: center;
    }
    
    .bikash-qr {
        margin-top: 15px;
    }
}

@media (max-width: 576px) {
    .page-header-section {
        margin-top: 60px;
        padding: 25px 0;
    }
    
    .donation-section {
        padding: 25px 0;
    }
    
    .payment-method-card {
        margin-bottom: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const paymentMethodInput = document.getElementById('paymentMethod');
    const bikashInstructions = document.getElementById('bikashInstructions');
    const transactionIdField = document.getElementById('transactionIdField');
    const transactionIdInput = document.getElementById('transaction_id');
    
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove active class from all cards
            paymentCards.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked card
            this.classList.add('active');
            
            // Get payment method
            const method = this.getAttribute('data-method');
            paymentMethodInput.value = method;
            
            // Handle different payment methods
            if (method === 'cash') {
                bikashInstructions.style.display = 'none';
                transactionIdField.style.display = 'none';
                transactionIdInput.removeAttribute('required');
            } else if (method === 'bikash') {
                bikashInstructions.style.display = 'block';
                transactionIdField.style.display = 'block';
                transactionIdInput.setAttribute('required', 'required');
            } else {
                // For stripe/paypal - show coming soon message
                alert('Credit Card and PayPal integration coming soon! For now, please use Cash or Bikash method.');
                // Re-select bikash method
                document.querySelector('[data-method="bikash"]').classList.add('active');
                paymentMethodInput.value = 'bikash';
                bikashInstructions.style.display = 'block';
                transactionIdField.style.display = 'block';
                transactionIdInput.setAttribute('required', 'required');
            }
        });
    });

    // Form validation enhancement
    const donationForm = document.querySelector('form');
    if (donationForm) {
        donationForm.addEventListener('submit', function(e) {
            const method = paymentMethodInput.value;
            const amount = <?php echo $amount; ?>;
            
            // Validate guest donor information
            const isGuest = <?php echo isLoggedIn() ? 'false' : 'true'; ?>;
            if (isGuest) {
                const donorName = document.querySelector('input[name="donor_name"]').value.trim();
                const emailOrPhone = document.querySelector('input[name="email_or_phone"]').value.trim();
                
                if (!donorName) {
                    e.preventDefault();
                    alert('Please enter your name to continue with the donation.');
                    return false;
                }
                
                if (!emailOrPhone) {
                    e.preventDefault();
                    alert('Please provide your email or phone number so we can create your account.');
                    return false;
                }
                
                // Basic validation for email/phone format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phoneRegex = /^[0-9+\-\s()]{10,20}$/;
                
                if (!emailRegex.test(emailOrPhone) && !phoneRegex.test(emailOrPhone)) {
                    e.preventDefault();
                    alert('Please enter a valid email address or phone number.');
                    return false;
                }
            }
            
            if (method === 'bikash' && !transactionIdInput.value.trim()) {
                e.preventDefault();
                alert('Please enter your Bikash transaction ID to complete the donation.');
                transactionIdInput.focus();
                return false;
            }
            
            if (amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid donation amount.');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                submitBtn.disabled = true;
            }
        });
    }

    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        setTimeout(() => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        }, 500);
    });

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?php 
// Enhanced Footer aligned with header design
include 'includes/footer.php'; 
?>