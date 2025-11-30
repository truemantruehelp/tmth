<?php
// index.php
require_once 'includes/config.php';
$page_title = "Home";
// Get featured campaigns
try {
    $stmt = $pdo->query("
        SELECT c.*, cat.name as category_name, parent.name as parent_category_name
        FROM campaigns c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN categories parent ON cat.parent_id = parent.id 
        WHERE c.status = 'active' 
        ORDER BY c.created_at DESC 
        LIMIT 6
    ");
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $campaigns = [];
    error_log("Database error: " . $e->getMessage());
}
// Get completed campaigns
try {
    $completed_stmt = $pdo->query("
        SELECT c.*, cat.name as category_name, parent.name as parent_category_name
        FROM campaigns c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        LEFT JOIN categories parent ON cat.parent_id = parent.id 
        WHERE (c.raised_amount >= c.goal_amount OR c.status = 'completed')
        AND c.status != 'inactive'
        ORDER BY c.updated_at DESC 
        LIMIT 3
    ");
    $completed_campaigns = $completed_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $completed_campaigns = [];
    error_log("Completed campaigns error: " . $e->getMessage());
}
// Get category hierarchy for navigation
$categories = getCategoryHierarchy();
include 'includes/header.php';
?>
<!-- Carousel Hero Section - Enhanced -->
<div id="homeCarousel" class="carousel slide carousel-hero-enhanced" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators-enhanced">
        <?php for($i = 0; $i < 4; $i++): ?>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $i + 1; ?>">
            <span class="progress-bar"></span>
        </button>
        <?php endfor; ?>
    </div>
    <div class="carousel-inner" role="listbox">
        <div class="carousel-item active">
            <div class="carousel-image-wrapper">
                <img src="assets/images/slider/home-slider-1.JPG" alt="Help those in need" class="carousel-image" loading="lazy">
                <div class="carousel-overlay-enhanced"></div>
            </div>
            <div class="container">
                <div class="carousel-caption-enhanced text-center">
                    <div class="hero-content-wrapper">
                        <h1 class="carousel-title-enhanced" data-aos="zoom-in" data-aos-delay="200">
                            Because They Need Your Help
                        </h1>
                        <h4 class="carousel-subtitle-enhanced" data-aos="zoom-in" data-aos-delay="400">
                            Do not let them down. Your support can change lives.
                        </h4>
                        <div class="hero-actions" data-aos="zoom-in" data-aos-delay="600">
                            <a href="campaigns.php" class="btn btn-hero-primary btn-lg">
                                <i class="fas fa-heart me-2"></i>Donate Now
                            </a>
                            <a href="#campaigns" class="btn btn-hero-secondary btn-lg">
                                <i class="fas fa-search me-2"></i>Explore Causes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item">
            <div class="carousel-image-wrapper">
                <img src="assets/images/slider/home-slider-2.JPG" alt="Together we can make a difference" class="carousel-image" loading="lazy">
                <div class="carousel-overlay-enhanced"></div>
            </div>
            <div class="container">
                <div class="carousel-caption-enhanced text-center">
                    <div class="hero-content-wrapper">
                        <h1 class="carousel-title-enhanced" data-aos="zoom-in" data-aos-delay="200">
                            Together We Can Improve Their Lives
                        </h1>
                        <h4 class="carousel-subtitle-enhanced" data-aos="zoom-in" data-aos-delay="400">
                            Join our mission to create lasting change in communities.
                        </h4>
                        <div class="hero-actions" data-aos="zoom-in" data-aos-delay="600">
                            <a href="campaigns.php" class="btn btn-hero-primary btn-lg">
                                <i class="fas fa-hands-helping me-2"></i>Join Our Mission
                            </a>
                            <a href="#our-story" class="btn btn-hero-secondary btn-lg">
                                <i class="fas fa-play-circle me-2"></i>Our Story
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-item">
            <div class="carousel-image-wrapper">
                <img src="assets/images/slider/home-slider-3.png" alt="Make a difference" class="carousel-image" loading="lazy">
                <div class="carousel-overlay-enhanced"></div>
            </div>
            <div class="container">
                <div class="carousel-caption-enhanced text-center">
                    <div class="hero-content-wrapper">
                        <h1 class="carousel-title-enhanced" data-aos="zoom-in" data-aos-delay="200">
                            A Penny Can Change A Life
                        </h1>
                        <h4 class="carousel-subtitle-enhanced" data-aos="zoom-in" data-aos-delay="400">
                            Your small contribution can make a big difference in someone's life.
                        </h4>
                        <div class="hero-actions" data-aos="zoom-in" data-aos-delay="600">
                            <a href="campaigns.php" class="btn btn-hero-primary btn-lg">
                                <i class="fas fa-gift me-2"></i>Make a Difference
                            </a>
                            <a href="#footer-contact" class="btn btn-hero-secondary btn-lg">
                                <i class="fas fa-phone me-2"></i>Get Involved
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 4th Carousel Item -->
        <div class="carousel-item">
            <div class="carousel-image-wrapper">
                <img src="assets/images/slider/home-slider-4.jpg" alt="True Help True Heart True Change" class="carousel-image" loading="lazy">
                <div class="carousel-overlay-enhanced"></div>
            </div>
            <div class="container">
                <div class="carousel-caption-enhanced text-center">
                    <div class="hero-content-wrapper">
                        <h1 class="carousel-title-enhanced" data-aos="zoom-in" data-aos-delay="200">
                            True Help. True Heart. True Change.
                        </h1>
                        <h4 class="carousel-subtitle-enhanced" data-aos="zoom-in" data-aos-delay="400">
                            Every act of generosity goes directly to people who need it most — turning kindness into hope
                        </h4>
                        <div class="hero-actions" data-aos="zoom-in" data-aos-delay="600">
                            <a href="campaigns.php" class="btn btn-hero-primary btn-lg">
                                <i class="fas fa-hand-holding-heart me-2"></i>Make an Impact
                            </a>
                            <a href="#real-impact-stories" class="btn btn-hero-secondary btn-lg">
                                <i class="fas fa-book-open me-2"></i>Read Stories
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev hero-carousel-control" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next hero-carousel-control" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
    <!-- Category Navigation Carousel -->
    <section class="category-carousel-section py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title center">Browse Our Causes</h2>
                <p class="section-subtitle">Find the perfect cause that resonates with your heart and make a meaningful impact</p>
            </div>
            <!-- Category Carousel -->
            <div id="categoryCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    // Get all subcategories for the grid
                    $allSubcategories = [];
                    foreach($categories as $parent) {
                        $subcategories = getSubcategories($parent['id']);
                        foreach($subcategories as $sub) {
                            $allSubcategories[] = [
                                'id' => $sub['id'],
                                'name' => $sub['name'],
                                'parent_name' => $parent['name'],
                                'icon' => getCategoryIcon($sub['name'])
                            ];
                        }
                    }
                    // Display 8 categories for 2 slides of 4 each
                    $displayCategories = array_slice($allSubcategories, 0, 8);
                    // Split categories into chunks for carousel slides (2 slides with 4 categories each)
                    $categoryChunks = array_chunk($displayCategories, 4);
                    foreach($categoryChunks as $slideIndex => $categoriesChunk): 
                    ?>
                    <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                        <div class="row g-4 justify-content-center">
                            <?php foreach($categoriesChunk as $index => $subcategory): ?>
                            <div class="col-xl-3 col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="category-card text-center">
                                    <div class="category-card-inner">
                                        <div class="category-icon">
                                            <i class="fas fa-<?php echo $subcategory['icon']; ?>"></i>
                                        </div>
                                        <h6 class="category-title"><?php echo htmlspecialchars($subcategory['name']); ?></h6>
                                        <p class="category-parent"><?php echo htmlspecialchars($subcategory['parent_name']); ?></p>
                                        <a href="campaigns.php?category=<?php echo $subcategory['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Explore <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Carousel Controls -->
                <?php if(count($categoryChunks) > 1): ?>
                <button class="carousel-control-prev category-carousel-control" type="button" data-bs-target="#categoryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next category-carousel-control" type="button" data-bs-target="#categoryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <!-- Carousel Indicators -->
                <div class="carousel-indicators-container mt-4">
                    <div class="carousel-indicators">
                        <?php for($i = 0; $i < count($categoryChunks); $i++): ?>
                        <button type="button" data-bs-target="#categoryCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                                class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>" 
                                aria-label="Slide <?php echo $i + 1; ?>"></button>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="campaigns.php" class="btn btn-primary btn-lg">
                    View All Categories <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <!-- Featured Campaigns -->
    <section id="campaigns" class="campaigns-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title center">Featured Campaigns</h2>
                <p class="section-subtitle">Urgent causes that need your immediate attention and support</p>
            </div>
            <div class="row g-4">
                <?php if(empty($campaigns)): ?>
                    <div class="col-12" data-aos="fade-up">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No campaigns available yet.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($campaigns as $index => $campaign): ?>
                    <div class="col-xl-4 col-lg-6" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="campaign-card-hover">
                            <div class="campaign-image">
                                <img src="<?php echo $campaign['image'] ?: 'assets/images/default-campaign.png'; ?>" 
                                     class="img-fluid" alt="<?php echo htmlspecialchars($campaign['title']); ?>" loading="lazy">
                                <div class="campaign-overlay">
                                    <span class="badge campaign-badge">
                                        <?php 
                                        if(isset($campaign['parent_category_name']) && isset($campaign['category_name'])) {
                                            echo htmlspecialchars($campaign['category_name']);
                                        } else {
                                            echo 'General';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="campaign-content">
                                <h5 class="campaign-title"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                                <p class="campaign-description"><?php echo substr($campaign['description'], 0, 120); ?>...</p>
                                <!-- Animated Progress Bar -->
                                <div class="progress-container">
                                    <div class="progress-labels">
                                        <span class="progress-raised">৳<?php echo number_format($campaign['raised_amount']); ?> raised</span>
                                        <span class="progress-goal">৳<?php echo number_format($campaign['goal_amount']); ?> goal</span>
                                    </div>
                                    <div class="progress progress-animated">
                                        <div class="progress-bar bg-gradient-success" 
                                             data-width="<?php 
                                             $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                             echo min(100, $progress); 
                                             ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="campaign-actions">
                                    <a href="campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary btn-hover-lift w-100">
                                        <i class="fas fa-gift me-2"></i>Donate Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="campaigns.php" class="btn btn-outline-primary btn-lg">
                    Explore All Campaigns <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
        <!-- Enhanced Story Section -->
    <section id="our-story" class="story-section-enhanced">
        <div class="container">
            <!-- Centered Section Title -->
            <div class="text-center mb-5" data-aos="fade-down">
                <h2 class="section-title center">True Man. True Help. True Heart.</h2>
                <p class="section-subtitle">Every act of kindness starts with a simple belief — that we can make a difference.</p>
            </div>
            <div class="row align-items-center">
                <!-- Story Content -->
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="story-content-enhanced">
                        <div class="story-intro mb-4">
                            <p class="story-lead">Since 2015, TrueManTrueHelp has been turning that belief into action — reaching out to those who've lost everything, standing beside struggling families, and helping people rebuild their lives with dignity.</p>
                        </div>
                        <div class="story-evolution mb-5">
                            <div class="evolution-card">
                                <div class="evolution-icon">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <div class="evolution-content">
                                    <h5>From Humble Beginnings</h5>
                                    <p>What began as a small group of volunteers delivering relief to flood victims has grown into a trusted community movement built on honesty, compassion, and transparency.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Trust Promise -->
                        <div class="trust-promise-card card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <div class="promise-header d-flex align-items-center mb-3">
                                    <div class="promise-icon me-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">Our Promise: 100% Trusted Help</h5>
                                </div>
                                <p class="mb-0">We believe every donation should reach a real person with a real story. That's why we ensure every contribution is verified, transparent, and fully accountable — from your hands to the hearts of those in need.</p>
                            </div>
                        </div>
                        <!-- Empowerment Section -->
                        <div class="empowerment-section mb-4">
                            <h4 class="empowerment-title mb-3">
                                <i class="fas fa-rocket me-2 text-warning"></i>
                                Empowering Lives, Not Just Giving Aid
                            </h4>
                            <p class="empowerment-text">True help means more than a meal or a blanket. It's about giving someone the chance to stand again — to work, to provide, to dream. Through our Empowerment Programs, we provide rickshaws, small business support, and tools for financial independence, helping families break free from the cycle of poverty.</p>
                        </div>
                        <!-- Comprehensive Care -->
                        <div class="care-section">
                            <h4 class="care-title mb-3">
                                <i class="fas fa-heartbeat me-2 text-danger"></i>
                                Caring for Every Need
                            </h4>
                            <p class="care-text">Whether it's emergency relief, medical support, education, or winter warmth, our mission is to care for every corner of life where hope fades. Because when we help one person rise, we lift an entire community.</p>
                        </div>
                    </div>
                </div>
                <!-- Story Image with 3D Effects -->
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="story-image-container-enhanced">
                        <div class="story-image-3d">
                            <img src="assets/images/our-story.jpg" alt="TrueManTrueHelp Organization Impact" class="story-image-main" loading="lazy">
                            <div class="image-overlay-3d">
                                <div class="overlay-content">
                                    <i class="fas fa-hands-helping fa-3x text-white mb-3"></i>
                                    <h5 class="text-white">Making Real Impact</h5>
                                    <p class="text-white mb-0">Since 2015</p>
                                </div>
                            </div>
                        </div>
                        <!-- Floating Impact Stats -->
                        <div class="floating-stats">
                            <div class="stat-floating" data-aos="fade-up" data-aos-delay="500">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>50K+</h5>
                                    <small>Lives Touched</small>
                                </div>
                            </div>
                            <div class="stat-floating" data-aos="fade-up" data-aos-delay="600">
                                <div class="stat-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>100%</h5>
                                    <small>Verified Help</small>
                                </div>
                            </div>
                            <div class="stat-floating" data-aos="fade-up" data-aos-delay="700">
                                <div class="stat-icon">
                                    <i class="fas fa-hand-holding-heart"></i>
                                </div>
                                <div class="stat-content">
                                    <h5>8 Years</h5>
                                    <small>Of Service</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Impact Highlights -->
            <div class="row mt-5" data-aos="fade-up" data-aos-delay="400">
                <div class="col-12">
                    <div class="impact-highlights">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="highlight-card text-center">
                                    <div class="highlight-icon-wrapper">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </div>
                                    <h5>Transparent Giving</h5>
                                    <p>Every donation tracked and verified with complete transparency</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="highlight-card text-center">
                                    <div class="highlight-icon-wrapper">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <h5>Community Driven</h5>
                                    <p>Built by the community, for the community - real people helping real people</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="highlight-card text-center">
                                    <div class="highlight-icon-wrapper">
                                        <i class="fas fa-infinity"></i>
                                    </div>
                                    <h5>Sustainable Impact</h5>
                                    <p>Creating lasting change through empowerment and education</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Completed Campaigns Section -->
    <section class="completed-campaigns-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title center">Success Stories</h2>
                <p class="section-subtitle">Campaigns that reached their goals and made a real impact in communities</p>
            </div>
            <div class="row g-4">
                <?php if(empty($completed_campaigns)): ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <div class="alert alert-info">
                            <i class="fas fa-trophy me-2"></i> 
                            No completed campaigns yet. Be the first to help a campaign reach its goal!
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($completed_campaigns as $index => $campaign): ?>
                    <div class="col-lg-4 col-md-6" data-aos="flip-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="completed-campaign-card">
                            <div class="completed-badge">
                                <i class="fas fa-trophy"></i> Goal Achieved
                            </div>
                            <div class="campaign-image">
                                <img src="<?php echo $campaign['image'] ?: 'assets/images/default-campaign.png'; ?>" 
                                     class="img-fluid" alt="<?php echo htmlspecialchars($campaign['title']); ?>" loading="lazy">
                                <div class="success-overlay">
                                    <div class="success-content">
                                        <i class="fas fa-check-circle fa-3x text-white mb-3"></i>
                                        <h5 class="text-white">Successfully Completed</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="campaign-content p-4">
                                <span class="badge bg-success mb-2">
                                    <?php 
                                    if(isset($campaign['parent_category_name']) && isset($campaign['category_name'])) {
                                        echo htmlspecialchars($campaign['category_name']);
                                    } else {
                                        echo 'Completed';
                                    }
                                    ?>
                                </span>
                                <h5 class="campaign-title"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                                <p class="campaign-description text-muted"><?php echo substr($campaign['description'], 0, 100); ?>...</p>
                                <!-- Success Stats -->
                                <div class="success-stats mt-4">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="stat">
                                                <h4 class="text-success mb-1">৳<?php echo number_format($campaign['raised_amount']); ?></h4>
                                                <small class="text-muted">Raised</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="stat">
                                                <h4 class="text-primary mb-1">100%</h4>
                                                <small class="text-muted">Funded</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Impact Message -->
                                <div class="impact-message mt-3 p-3 bg-success bg-opacity-10 rounded">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-heart text-success me-2"></i>
                                        <small class="text-success">
                                            <strong>Impact Made:</strong> This campaign successfully reached its goal!
                                        </small>
                                    </div>
                                </div>
                                <div class="campaign-actions mt-3">
                                    <a href="campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-outline-success w-100">
                                        <i class="fas fa-eye me-2"></i>View Success Story
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if(!empty($completed_campaigns)): ?>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="completed-campaigns.php" class="btn btn-success btn-lg">
                    <i class="fas fa-trophy me-2"></i>View All Success Stories
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- Main Content Area -->
<main>
    <!-- Impact Stats Section - Enhanced -->
    <section class="stats-section-enhanced">
        <div class="container">
            <div class="row g-3">
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card-enhanced text-center">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="stat-number-enhanced counter" data-count="2.5">0</h3>
                        <p class="stat-label-enhanced">Million Raised</p>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card-enhanced text-center">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-number-enhanced counter" data-count="50">0</h3>
                        <p class="stat-label-enhanced">Thousand Donors</p>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card-enhanced text-center">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-globe-americas"></i>
                        </div>
                        <h3 class="stat-number-enhanced counter" data-count="100">0</h3>
                        <p class="stat-label-enhanced">Communities Reached</p>
                    </div>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card-enhanced text-center">
                        <div class="stat-icon-enhanced">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="stat-number-enhanced counter" data-count="500">0</h3>
                        <p class="stat-label-enhanced">Active Campaigns</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Impact Stories Section (Replacing Newsletter) -->
    <section id="real-impact-stories" class="impact-stories-section py-5 bg-dark text-white">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title center text-white">Real Impact Stories</h2>
                <p class="section-subtitle text-light">See how your support transforms lives and creates lasting change</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="impact-story-card">
                        <div class="impact-image">
                            <img src="assets/images/impact-1.JPG" alt="Education Transformation" class="img-fluid" loading="lazy">
                            <div class="impact-overlay">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                        <div class="impact-content">
                            <h5>Disable people</h5>
                            <p>Disable man recieved Rickshaw-van to support himself and family with our valnurable assistance program.</p>
                            <div class="impact-stats">
                                <span class="badge bg-primary">500+ People Helped</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="impact-story-card">
                        <div class="impact-image">
                            <img src="assets/images/impact-2.JPG" alt="Healthcare Support" class="img-fluid" loading="lazy">
                            <div class="impact-overlay">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                        </div>
                        <div class="impact-content">
                            <h5>Women Empowerment</h5>
                            <p>Fatima received sewing machine to become financially independent from our women empowerment assistance program.</p>
                            <div class="impact-stats">
                                <span class="badge bg-success">100+ sewing machine </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="impact-story-card">
                        <div class="impact-image">
                            <img src="assets/images/impact-3.jpg" alt="Livelihood Support" class="img-fluid" loading="lazy">
                            <div class="impact-overlay">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                        </div>
                        <div class="impact-content">
                            <h5>Empowering Entrepreneurs</h5>
                            <p>Jahangir started his small business with our Zakat charity program.</p>
                            <div class="impact-stats">
                                <span class="badge bg-warning">200+ Families Empowered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="gallery.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-images me-2"></i>View More Stories
                </a>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
<style>
/* Enhanced Hero Carousel Styles */
.carousel-hero-enhanced {
    margin-top: 0;
    position: relative;
}
.carousel-hero-enhanced .carousel-item {
    height: 100vh;
    min-height: 700px;
    position: relative;
}
.carousel-image-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.carousel-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 8s ease-in-out;
}
.carousel-hero-enhanced .carousel-item.active .carousel-image {
    transform: scale(1.1);
}
.carousel-overlay-enhanced {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}
.carousel-caption-enhanced {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 2;
    width: 100%;
    max-width: 900px;
    padding: 0 20px;
}
.hero-content-wrapper {
    position: relative;
    z-index: 3;
}
.carousel-title-enhanced {
    font-size: 4rem;
    font-weight: 900;
    color: white;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
    line-height: 1.1;
    background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.carousel-subtitle-enhanced {
    font-size: 1.5rem;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2.5rem;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}
.hero-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn-hero-primary {
    background: var(--gradient-secondary);
    border: none;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(230, 62, 62, 0.3);
    color: white;
}
.btn-hero-primary:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 40px rgba(230, 62, 62, 0.4);
    color: white;
}
.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}
.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-5px) scale(1.05);
    color: white;
}
/* Enhanced Carousel Controls */
.hero-carousel-control {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.7;
    transition: all 0.4s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
}
.hero-carousel-control:hover {
    background: var(--gradient-secondary);
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 30px;
    height: 30px;
    filter: invert(1);
}
/* Enhanced Progress Indicators */
.carousel-indicators-enhanced {
    position: absolute;
    bottom: 40px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 15px;
    z-index: 3;
}
.carousel-indicators-enhanced button {
    width: 60px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border: none;
    border-radius: 2px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}
.carousel-indicators-enhanced button.active {
    background: rgba(255, 255, 255, 0.6);
}
.carousel-indicators-enhanced button .progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 0%;
    background: var(--accent);
    transition: width 5s linear;
}
.carousel-indicators-enhanced button.active .progress-bar {
    width: 100%;
}
/* Enhanced Stats Section */
.stats-section-enhanced {
    background: var(--gradient-primary);
    padding: 40px 0;
    position: relative;
    overflow: hidden;
}
.stats-section-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.03)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}
.stat-card-enhanced {
    padding: 30px 20px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    z-index: 1;
}
.stat-card-enhanced:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
    border-color: rgba(255, 255, 255, 0.3);
}
.stat-icon-enhanced {
    margin-bottom: 15px;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    margin: 0 auto 15px;
    transition: all 0.4s ease;
}
.stat-card-enhanced:hover .stat-icon-enhanced {
    background: var(--gradient-secondary);
    transform: scale(1.1) rotate(5deg);
}
.stat-icon-enhanced i {
    font-size: 2rem;
    color: white;
    transition: all 0.4s ease;
}
.stat-number-enhanced {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: white;
}
.stat-label-enhanced {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    font-weight: 500;
}
/* Impact Stories Section */
.impact-stories-section {
    position: relative;
    overflow: hidden;
}
.impact-stories-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.02)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}
.impact-story-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.4s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}
.impact-story-card:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}
.impact-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}
.impact-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.impact-story-card:hover .impact-image img {
    transform: scale(1.1);
}
.impact-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(26, 58, 95, 0.8), rgba(230, 62, 62, 0.8));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.4s ease;
}
.impact-story-card:hover .impact-overlay {
    opacity: 1;
}
.impact-overlay i {
    font-size: 3rem;
    color: white;
}
.impact-content {
    padding: 25px;
}
.impact-content h5 {
    color: white;
    margin-bottom: 10px;
    font-weight: 600;
}
.impact-content p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 15px;
    font-size: 0.95rem;
}
.impact-stats {
    margin-top: 15px;
}
/* Responsive Design */
@media (max-width: 1200px) {
    .carousel-title-enhanced {
        font-size: 3.5rem;
    }
    .carousel-subtitle-enhanced {
        font-size: 1.3rem;
    }
}
@media (max-width: 992px) {
    .carousel-hero-enhanced .carousel-item {
        height: 80vh;
        min-height: 600px;
    }
    .carousel-title-enhanced {
        font-size: 3rem;
    }
    .carousel-subtitle-enhanced {
        font-size: 1.2rem;
    }
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    .btn-hero-primary,
    .btn-hero-secondary {
        width: 250px;
    }
    .stat-number-enhanced {
        font-size: 2.2rem;
    }
    .stat-icon-enhanced {
        width: 60px;
        height: 60px;
    }
    .stat-icon-enhanced i {
        font-size: 1.8rem;
    }
}
@media (max-width: 768px) {
    .carousel-hero-enhanced .carousel-item {
        height: 70vh;
        min-height: 500px;
    }
    .carousel-title-enhanced {
        font-size: 2.5rem;
    }
    .carousel-subtitle-enhanced {
        font-size: 1.1rem;
    }
    .hero-carousel-control {
        width: 50px;
        height: 50px;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 20px;
        height: 20px;
    }
    .stats-section-enhanced {
        padding: 30px 0;
    }
    .stat-card-enhanced {
        padding: 20px 15px;
    }
    .stat-number-enhanced {
        font-size: 2rem;
    }
}
@media (max-width: 576px) {
    .carousel-hero-enhanced .carousel-item {
        height: 60vh;
        min-height: 400px;
    }
    .carousel-title-enhanced {
        font-size: 2rem;
    }
    .carousel-subtitle-enhanced {
        font-size: 1rem;
    }
    .btn-hero-primary,
    .btn-hero-secondary {
        padding: 12px 30px;
        font-size: 1rem;
        width: 200px;
    }
    .hero-carousel-control {
        width: 40px;
        height: 40px;
    }
    .carousel-indicators-enhanced {
        bottom: 20px;
    }
    .carousel-indicators-enhanced button {
        width: 40px;
    }
    .stat-number-enhanced {
        font-size: 1.8rem;
    }
    .stat-icon-enhanced {
        width: 50px;
        height: 50px;
    }
    .stat-icon-enhanced i {
        font-size: 1.5rem;
    }
}
/* Animation Keyframes */
@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
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
.carousel-item.active .carousel-title-enhanced {
    animation: zoomIn 1s ease-out;
}
.carousel-item.active .carousel-subtitle-enhanced {
    animation: fadeInUp 1s ease-out 0.3s both;
}
.carousel-item.active .hero-actions {
    animation: fadeInUp 1s ease-out 0.6s both;
}
</style>
<script>
// Enhanced Hero Carousel Functionality
document.addEventListener('DOMContentLoaded', function() {
    const heroCarousel = document.getElementById('homeCarousel');
    if (heroCarousel) {
        // Initialize auto-rotation with longer interval
        const carousel = new bootstrap.Carousel(heroCarousel, {
            interval: 6000, // 6 seconds for hero
            pause: 'hover',
            wrap: true,
            touch: true
        });
        // Add parallax effect on scroll
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroItems = document.querySelectorAll('.carousel-image');
            heroItems.forEach(item => {
                const speed = 0.7;
                item.style.transform = `translateY(${scrolled * speed}px) scale(1.1)`;
            });
        });
        // Enhanced progress indicators
        function updateProgressIndicators() {
            const activeIndicator = document.querySelector('.carousel-indicators-enhanced .active');
            const progressBars = document.querySelectorAll('.carousel-indicators-enhanced .progress-bar');
            // Reset all progress bars
            progressBars.forEach(bar => {
                bar.style.transition = 'none';
                bar.style.width = '0%';
            });
            // Animate active progress bar
            if (activeIndicator) {
                const progressBar = activeIndicator.querySelector('.progress-bar');
                if (progressBar) {
                    setTimeout(() => {
                        progressBar.style.transition = 'width 6s linear';
                        progressBar.style.width = '100%';
                    }, 100);
                }
            }
        }
        // Update progress on slide
        heroCarousel.addEventListener('slid.bs.carousel', function() {
            updateProgressIndicators();
        });
        // Initialize progress indicators
        updateProgressIndicators();
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                carousel.prev();
            } else if (e.key === 'ArrowRight') {
                carousel.next();
            }
        });
    }
    // Initialize animations
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });
    // Counter animation
    const counters = document.querySelectorAll('.counter');
    const speed = 200;
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText;
            const inc = target / speed;
            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target;
            }
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCount();
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(counter);
    });
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = width + '%';
        }, 500);
    });
    // Navbar background on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-main');
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 120,
                    behavior: 'smooth'
                });
            }
        });
    });
    // Enhanced hover effects for impact stories
    const impactCards = document.querySelectorAll('.impact-story-card');
    impactCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    // Page loader functionality
    const pageLoader = document.getElementById('pageLoader');
    window.addEventListener('load', function() {
        setTimeout(function() {
            pageLoader.classList.add('hidden');
        }, 2000);
    });
    // Mobile menu toggle fix
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = navbarCollapse.contains(event.target) || navbarToggler.contains(event.target);
            if (!isClickInside && navbarCollapse.classList.contains('show')) {
                navbarCollapse.classList.remove('show');
            }
        });
        
        // Close menu when clicking a link on mobile
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                }
            });
        });
    }
});
</script>