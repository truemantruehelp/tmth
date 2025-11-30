<?php
// gallery.php
require_once 'includes/config.php';
require_once 'includes/performance.php';
$page_title = "Gallery";

include 'includes/header.php';
?>

<!-- Gallery Hero Section -->
<section class="gallery-hero py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Our Gallery</h1>
                <p class="lead mb-4">Witness the impact of your generosity through our collection of moments that capture hope, transformation, and community.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#impact-gallery" class="btn btn-light btn-lg">View Impact Stories</a>
                    <a href="#events-gallery" class="btn btn-outline-light btn-lg">Event Photos</a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-images display-1 text-warning"></i>
            </div>
        </div>
    </div>
</section>

<!-- Impact Gallery Section -->
<section id="impact-gallery" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Impact in Action</h2>
            <p class="section-subtitle">Real stories of transformation made possible by your support</p>
        </div>
        
        <div class="row g-4">
            <!-- Gallery Item 1 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/impact-1.JPG', 'Education support for children', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Education Support</h5>
                            <p>Providing school supplies to underprivileged children</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Item 2 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/impact-2.JPG', 'Healthcare support', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Healthcare Support</h5>
                            <p>Medical assistance for families in need</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Item 3 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/impact-3.jpg', 'Livelihood support', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Livelihood Support</h5>
                            <p>Empowering communities with sustainable resources</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Item 4 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/slider/home-slider-1.JPG', 'Emergency relief', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Emergency Relief</h5>
                            <p>Quick response during natural disasters</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Item 5 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/slider/home-slider-2.JPG', 'Community development', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Community Development</h5>
                            <p>Building sustainable communities together</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gallery Item 6 -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/slider/home-slider-3.png', 'Education programs', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Education Programs</h5>
                            <p>Supporting children's education and development</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Gallery Section -->
<section id="events-gallery" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Events & Activities</h2>
            <p class="section-subtitle">Moments from our community events and fundraising activities</p>
        </div>
        
        <div class="row g-4">
            <!-- Event Item 1 -->
            <div class="col-lg-3 col-md-6" data-aos="zoom-in">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/gallery/event-1.jpg', 'Fundraising event', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Fundraising Event</h5>
                            <p>Annual charity gala 2023</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Item 2 -->
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/gallery/event-2.jpg', 'Volunteer program', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Volunteer Program</h5>
                            <p>Community service day</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Item 3 -->
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/gallery/event-3.jpg', 'Awareness campaign', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Awareness Campaign</h5>
                            <p>Public health awareness</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Item 4 -->
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="gallery-item position-relative overflow-hidden rounded-3">
                    <?php echo lazyImage('assets/images/gallery/event-4.jpg', 'Distribution event', 'img-fluid w-100 gallery-image'); ?>
                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <h5>Distribution Event</h5>
                            <p>Winter clothes distribution</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<
<?php include 'includes/footer.php'; ?>

<style>
.gallery-hero {
    background: var(--gradient-primary);
    position: relative;
    overflow: hidden;
    top: 60px;
}

.gallery-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}

.gallery-item {
    transition: all 0.3s ease;
    cursor: pointer;
    height: 300px;
    overflow: hidden;
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.gallery-image {
    height: 100%;
    width: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.gallery-item:hover .gallery-image {
    transform: scale(1.1);
}

.gallery-overlay {
    background: linear-gradient(135deg, rgba(26,58,95,0.8), rgba(230,62,62,0.8));
    opacity: 0;
    transition: all 0.3s ease;
    padding: 20px;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}


/* Responsive Design */
@media (max-width: 768px) {
    .gallery-item {
        height: 250px;
    }
    
    .gallery-hero .display-4 {
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .gallery-item {
        height: 200px;
    }
    
    .gallery-hero .display-4 {
        font-size: 2rem;
    }
}
</style>

<script>
// Gallery-specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
    
    // Smooth scrolling for gallery navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            const targetElement = document.querySelector(href);
            if (targetElement) {
                e.preventDefault();
                
                const navbarHeight = document.querySelector('.navbar-main').offsetHeight + 
                                   document.querySelector('.navbar-top').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + 
                                      window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Enhanced gallery item interactions
    const galleryItems = document.querySelectorAll('.gallery-item');
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            // Add click functionality - could open modal with larger image
            const img = this.querySelector('img');
            const title = this.querySelector('h5')?.textContent || 'Image';
            const description = this.querySelector('p')?.textContent || '';
            
            // For now, just log the click - you can implement a lightbox here
            console.log('Gallery item clicked:', title);
        });
        
        // Enhanced hover effects
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        // Touch device support
        item.addEventListener('touchstart', function() {
            this.classList.add('active');
        });
        
        item.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('active');
            }, 150);
        });
    });
    
    // Lazy load images with intersection observer
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.src; // Trigger load
                imageObserver.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Filter functionality (can be extended)
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Here you can implement gallery filtering
            const filterValue = this.getAttribute('data-filter');
            console.log('Filter by:', filterValue);
        });
    });
});

// Fallback: Ensure loader is hidden even if there are JS errors
window.addEventListener('load', function() {
    const pageLoader = document.getElementById('pageLoader');
    if (pageLoader) {
        setTimeout(() => {
            pageLoader.style.opacity = '0';
            setTimeout(() => {
                pageLoader.style.display = 'none';
            }, 500);
        }, 1000);
    }
});
</script>