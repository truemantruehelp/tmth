<?php
// campaign.php
require_once 'includes/config.php';

$campaign_id = $_GET['id'] ?? 0;

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
    error_log("Campaign page error: " . $e->getMessage());
}

if (!$campaign) {
    header("Location: campaigns.php");
    exit();
}

$page_title = $campaign['title'];
include 'includes/header.php';

// Calculate progress
$progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
$progress = min(100, $progress);

// Calculate days left
if (isset($campaign['end_date']) && $campaign['end_date'] != '0000-00-00') {
    $end_date = new DateTime($campaign['end_date']);
    $today = new DateTime();
    $days_left = $today->diff($end_date)->days;
    $days_left = $days_left > 0 ? $days_left : 0;
} else {
    $days_left = null;
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
                        <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($campaign['title']); ?></li>
                    </ol>
                </nav>
                <h1 class="text-white mb-2" data-aos="fade-right"><?php echo htmlspecialchars($campaign['title']); ?></h1>
                <div class="d-flex flex-wrap gap-2" data-aos="fade-right" data-aos-delay="100">
                    <span class="badge bg-warning">
                        <?php 
                        if(isset($campaign['parent_category_name']) && isset($campaign['category_name'])) {
                            echo htmlspecialchars($campaign['category_name']);
                        } else {
                            echo 'General';
                        }
                        ?>
                    </span>
                    <?php if($days_left !== null): ?>
                    <span class="badge bg-info">
                        <i class="fas fa-clock me-1"></i><?php echo $days_left; ?> days left
                    </span>
                    <?php endif; ?>
                    <span class="badge bg-success">
                        <i class="fas fa-trophy me-1"></i><?php echo number_format($progress, 1); ?>% Funded
                    </span>
                </div>
            </div>
            <div class="col-md-4 text-md-end" data-aos="fade-left">
                <div class="header-stats">
                    <div class="stat-item">
                        <h3 class="text-white mb-1">৳<?php echo number_format($campaign['raised_amount']); ?></h3>
                        <small class="text-white-50">Raised of ৳<?php echo number_format($campaign['goal_amount']); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Campaign Details Section -->
<section class="campaign-details-section py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="campaign-main-card card border-0 shadow-sm mb-4">
                    <div class="campaign-image-container">
                        <img src="<?php echo $campaign['image'] ?: 'assets/images/default-campaign.jpg'; ?>" 
                             class="campaign-main-image" 
                             alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                        <div class="campaign-image-overlay">
                            <span class="badge campaign-image-badge">
                                <?php echo number_format($progress, 1); ?>% Funded
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Progress Section -->
                        <div class="progress-section mb-4">
                            <div class="progress-container">
                                <div class="progress-labels d-flex justify-content-between mb-2">
                                    <span class="progress-raised fw-bold text-primary">
                                        ৳<?php echo number_format($campaign['raised_amount']); ?> raised
                                    </span>
                                    <span class="progress-goal text-muted">
                                        ৳<?php echo number_format($campaign['goal_amount']); ?> goal
                                    </span>
                                </div>
                                <div class="progress progress-animated" style="height: 12px;">
                                    <div class="progress-bar bg-gradient-success" 
                                         style="width: <?php echo $progress; ?>%">
                                    </div>
                                </div>
                                <div class="progress-stats text-center mt-2">
                                    <small class="text-muted">
                                        <strong><?php echo number_format($progress, 1); ?>%</strong> of goal reached
                                        <?php if($days_left !== null): ?>
                                         • <strong><?php echo $days_left; ?></strong> days remaining
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Campaign Description -->
                        <div class="campaign-description-section">
                            <h4 class="section-title mb-3">About This Campaign</h4>
                            <div class="campaign-content">
                                <?php if (!empty($campaign['short_description'])): ?>
                                <div class="campaign-summary mb-4">
                                    <p class="lead text-dark"><?php echo nl2br(htmlspecialchars($campaign['short_description'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="campaign-full-description">
                                    <p><?php echo nl2br(htmlspecialchars($campaign['description'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Campaign Details -->
                        <div class="campaign-details-grid mt-5">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="detail-card text-center p-3 border rounded">
                                        <div class="detail-icon mb-2">
                                            <i class="fas fa-donate fa-2x text-primary"></i>
                                        </div>
                                        <h5 class="text-primary mb-1">৳<?php echo number_format($campaign['raised_amount']); ?></h5>
                                        <small class="text-muted">Total Raised</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card text-center p-3 border rounded">
                                        <div class="detail-icon mb-2">
                                            <i class="fas fa-bullseye fa-2x text-success"></i>
                                        </div>
                                        <h5 class="text-success mb-1">৳<?php echo number_format($campaign['goal_amount']); ?></h5>
                                        <small class="text-muted">Funding Goal</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-card text-center p-3 border rounded">
                                        <div class="detail-icon mb-2">
                                            <i class="fas fa-calendar fa-2x text-info"></i>
                                        </div>
                                        <h5 class="text-info mb-1">
                                            <?php echo $days_left !== null ? $days_left . ' Days' : 'Ongoing'; ?>
                                        </h5>
                                        <small class="text-muted">Time Remaining</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Share Campaign -->
                <div class="share-campaign-card card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-share-alt me-2"></i>Share This Campaign
                        </h5>
                        <p class="text-muted mb-3">Help spread the word by sharing this campaign with your friends and family.</p>
                        <div class="share-buttons">
                            <button type="button" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                <i class="fab fa-facebook-f me-1"></i>Facebook
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm me-2 mb-2">
                                <i class="fab fa-twitter me-1"></i>Twitter
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm me-2 mb-2">
                                <i class="fab fa-instagram me-1"></i>Instagram
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm me-2 mb-2">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </button>
                            <button type="button" class="btn btn-outline-dark btn-sm mb-2">
                                <i class="fas fa-link me-1"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Donation Sidebar -->
            <div class="col-lg-4" data-aos="fade-left">
                <div class="donation-sidebar-card card border-0 shadow-sm sticky-top" style="top: 135px;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-heart me-2"></i> Donate Now
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="donation-motivation mb-4">
                            <p class="text-muted text-center">
                                <i class="fas fa-quote-left text-primary me-1"></i>
                                Your generosity can create lasting change. Every contribution brings us closer to our goal.
                                <i class="fas fa-quote-right text-primary ms-1"></i>
                            </p>
                        </div>
                        
                        <!-- Quick Donation Amounts -->
                        <div class="donation-options mb-4">
                            <h6 class="section-subtitle mb-3">Quick Donate:</h6>
                            <div class="row g-2">
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="100">
                                        ৳100
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="500">
                                        ৳500
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="1000">
                                        ৳1000
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="2000">
                                        ৳2000
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="5000">
                                        ৳5000
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100 donation-amount" data-amount="10000">
                                        ৳10000
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Donation Form -->
                        <form action="donate.php" method="GET" id="donationForm">
                            <input type="hidden" name="campaign" value="<?php echo $campaign['id']; ?>">
                            
                            <div class="mb-4">
                                <label for="customAmount" class="form-label fw-bold">Custom Amount:</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0">৳</span>
                                    <input type="number" class="form-control border-start-0" 
                                           id="customAmount" name="amount" 
                                           min="1" step="1" placeholder="Enter amount" 
                                           required style="border-left: none;">
                                </div>
                                <div class="form-text">Minimum donation: ৳1</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 pulse-animation">
                                <i class="fas fa-heart me-2"></i>Donate Now
                            </button>
                        </form>
                        
                        <!-- Security Badge -->
                        <div class="security-badge text-center mt-4 pt-3 border-top">
                            <div class="security-features">
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-lock text-success me-1"></i>Secure SSL Encryption
                                </small>
                                <small class="text-muted d-block mb-1">
                                    <i class="fas fa-shield-alt text-success me-1"></i>100% Protected
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>Verified Campaign
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campaign Status -->
                <div class="campaign-status-card card border-0 shadow-sm mt-4">
                    <div class="card-body text-center">
                        <div class="status-icon mb-3">
                            <i class="fas fa-rocket fa-2x text-primary"></i>
                        </div>
                        <h6 class="text-primary mb-2">Campaign Status</h6>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <small class="text-muted">
                            <strong><?php echo number_format($progress, 1); ?>%</strong> funded • 
                            <strong><?php echo $days_left !== null ? $days_left : '∞'; ?></strong> days left
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Campaign Page Specific Styles */
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
    margin-bottom: 1rem;
}

.breadcrumb-item a {
    text-decoration: none;
    color: rgba(255,255,255,0.8) !important;
}

.breadcrumb-item.active {
    color: white !important;
}

.header-stats .stat-item h3 {
    font-weight: 800;
}

.campaign-main-card {
    border-radius: 15px;
    overflow: hidden;
}

.campaign-image-container {
    position: relative;
    overflow: hidden;
    height: 400px;
}

.campaign-main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.campaign-main-card:hover .campaign-main-image {
    transform: scale(1.02);
}

.campaign-image-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
}

.campaign-image-badge {
    font-size: 0.9rem;
    padding: 8px 15px;
    border-radius: 20px;
    background: var(--gradient-success);
    border: 2px solid white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.progress-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1rem;
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--gradient-secondary);
    border-radius: 2px;
}

.section-subtitle {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
}

.campaign-content {
    line-height: 1.8;
    color: var(--text-light);
}

.campaign-content p {
    margin-bottom: 1.5rem;
}

.detail-card {
    transition: all 0.3s ease;
    background: white;
}

.detail-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-soft);
}

.detail-icon {
    opacity: 0.8;
}

.share-campaign-card {
    border-radius: 15px;
}

.share-buttons .btn {
    border-radius: 25px;
    padding: 8px 15px;
    font-weight: 500;
}

.donation-sidebar-card {
    border-radius: 15px;
    overflow: hidden;
}

.donation-motivation {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 15px;
    border-radius: 10px;
    border-left: 4px solid var(--accent);
}

.donation-options .btn {
    border-radius: 10px;
    padding: 10px 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.donation-options .btn.active {
    background: var(--gradient-primary);
    color: white;
    border-color: var(--primary);
    transform: scale(1.05);
}

.security-badge {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    padding: 15px;
}

.campaign-status-card {
    border-radius: 15px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.status-icon {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: var(--shadow-soft);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header-section {
        margin-top: 60px;
        padding: 30px 0;
    }
    
    .campaign-image-container {
        height: 300px;
    }
    
    .donation-sidebar-card.sticky-top {
        position: relative !important;
        top: 0 !important;
        margin-bottom: 20px;
    }
    
    .header-stats {
        text-align: left !important;
        margin-top: 15px;
    }
}

@media (max-width: 576px) {
    .page-header-section {
        margin-top: 60px;
        padding: 25px 0;
    }
    
    .campaign-details-section {
        padding: 25px 0;
    }
    
    .campaign-image-container {
        height: 250px;
    }
    
    .donation-options .btn {
        font-size: 0.8rem;
        padding: 8px 4px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bar
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        setTimeout(() => {
            progressBar.style.width = progressBar.style.width;
        }, 500);
    }

    // Donation amount buttons functionality
    const amountButtons = document.querySelectorAll('.donation-amount');
    const customAmountInput = document.getElementById('customAmount');
    
    amountButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            customAmountInput.value = amount;
            
            // Update button states
            amountButtons.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('active', 'btn-primary');
        });
    });
    
    // Clear selected button when typing custom amount
    customAmountInput.addEventListener('input', function() {
        amountButtons.forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
        });
    });

    // Share buttons functionality
    const shareButtons = document.querySelectorAll('.share-buttons .btn');
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const platform = this.textContent.trim();
            const url = window.location.href;
            const title = document.querySelector('.page-header-section h1').textContent;
            
            let shareUrl = '';
            
            switch(platform) {
                case 'Facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'Twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'Instagram':
                    // Instagram doesn't support direct sharing, open app or show message
                    alert('Copy the campaign link and share it on Instagram!');
                    return;
                case 'WhatsApp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
                case 'Copy Link':
                    navigator.clipboard.writeText(url).then(() => {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                        setTimeout(() => {
                            this.innerHTML = originalText;
                        }, 2000);
                    });
                    return;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
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

<?php include 'includes/footer.php'; ?>