<?php
// includes/footer.php
?>
<!-- Footer Section -->
<footer class="footer-section" id="footer-contact">
    <!-- Main Footer -->
    <div class="footer-main py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="footer-brand mb-4">
                        <a class="navbar-brand footer-logo" href="index.php">
                            <i class="fas fa-hands-helping"></i>
                            <?php echo defined('SITE_NAME') ? SITE_NAME : 'TrueManTrueHelp'; ?>
                        </a>
                    </div>
                    <p class="footer-description text-light mb-4">
                        TrueManTrueHelp is dedicated to creating lasting change in communities through verified, 
                        transparent charity initiatives. Join us in making a difference one life at a time.
                    </p>
                    <div class="footer-social">
                        <h6 class="text-white mb-3">Follow Our Journey</h6>
                        <div class="social-links">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" class="social-link" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <h5 class="footer-title text-white mb-4">Quick Links</h5>
                    <ul class="footer-links list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#our-story" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Campaigns
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="gallery.php" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Gallery
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#real-impact-stories" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Blog
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#footer-contact" class="footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Contact
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Campaign Categories -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <h5 class="footer-title text-white mb-4">Our Causes</h5>
                    <ul class="footer-links list-unstyled">
                        <li class="mb-2">
                            <a href="campaigns.php?category=education" class="footer-link">
                                <i class="fas fa-book me-2"></i>Education
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php?category=healthcare" class="footer-link">
                                <i class="fas fa-heartbeat me-2"></i>Healthcare
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php?category=emergency" class="footer-link">
                                <i class="fas fa-ambulance me-2"></i>Emergency Relief
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php?category=livelihood" class="footer-link">
                                <i class="fas fa-hands-helping me-2"></i>Livelihood
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php?category=winter" class="footer-link">
                                <i class="fas fa-snowflake me-2"></i>Winter Support
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="campaigns.php" class="footer-link text-accent">
                                <i class="fas fa-arrow-right me-2"></i>View All Causes
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Contact Info -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <h5 class="footer-title text-white mb-4">Get In Touch</h5>
                    <div class="footer-contact">
                        <div class="contact-item d-flex align-items-start mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-map-marker-alt text-accent"></i>
                            </div>
                            <div class="contact-info">
                                <h6 class="text-white mb-1">Our Location</h6>
                                <p class="text-light mb-0">Dhaka, Bangladesh</p>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-start mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-phone text-accent"></i>
                            </div>
                            <div class="contact-info">
                                <h6 class="text-white mb-1">Call Us</h6>
                                <a href="tel:+8801859135478" class="text-light text-decoration-none">+880 1859-135478</a>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-start mb-3">
                            <div class="contact-icon me-3">
                                <i class="fas fa-envelope text-accent"></i>
                            </div>
                            <div class="contact-info">
                                <h6 class="text-white mb-1">Email Us</h6>
                                <a href="mailto:truemantruehelp@gmail.com" class="text-light text-decoration-none">truemantruehelp@gmail.com</a>
                            </div>
                        </div>
                        <div class="contact-item d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-clock text-accent"></i>
                            </div>
                            <div class="contact-info">
                                <h6 class="text-white mb-1">Response Time</h6>
                                <p class="text-light mb-0">Within 24 Hours</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Call to Action Section -->
    <div class="footer-cta py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hands-helping fa-2x text-accent me-3"></i>
                        <div>
                            <h5 class="text-white mb-1">Ready to Make a Difference?</h5>
                            <p class="text-light mb-0">Join thousands of donors who are creating lasting change</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="cta-buttons">
                        <a href="campaigns.php" class="btn btn-primary pulse-constant me-2">
                            <i class="fas fa-heart me-2"></i>Donate Now
                        </a>
                        <a href="#footer-contact" class="btn btn-outline-light">
                            <i class="fas fa-handshake me-2"></i>Volunteer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Bottom -->
    <div class="footer-bottom py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="text-light mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo defined('SITE_NAME') ? SITE_NAME : 'TrueManTrueHelp'; ?>. 
                        All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="footer-bottom-links">
                        <a href="contact.php" class="text-light text-decoration-none">Powered by JSOFT</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Back to Top Button -->
<button class="back-to-top btn btn-primary" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>
<!-- Footer Styles -->
<style>
/* Footer Styles */
.footer-section {
    background: var(--gradient-primary);
    position: relative;
    overflow: hidden;
}
.footer-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.03)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}
.footer-main {
    position: relative;
    z-index: 1;
}
.footer-logo {
    font-weight: 800;
    font-size: 1.6rem;
    background: var(--gradient-secondary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
    text-decoration: none;
    transition: all 0.3s ease;
}
.footer-logo:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}
.footer-logo i {
    margin-right: 8px;
    font-size: 1.6rem;
}
.footer-description {
    line-height: 1.8;
    font-size: 1rem;
    opacity: 0.9;
}
.footer-title {
    font-weight: 700;
    font-size: 1.2rem;
    position: relative;
    padding-bottom: 10px;
}
.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--gradient-secondary);
    border-radius: 2px;
}
.footer-links {
    margin: 0;
    padding: 0;
}
.footer-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
    padding: 5px 0;
    font-size: 0.95rem;
}
.footer-link:hover {
    color: var(--accent-light);
    transform: translateX(5px);
}
.footer-link i {
    width: 16px;
    text-align: center;
}
.text-accent {
    color: var(--accent) !important;
}
/* Social Links */
.social-links {
    display: flex;
    gap: 12px;
}
.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.social-link:hover {
    background: var(--gradient-secondary);
    color: white;
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 20px rgba(230, 62, 62, 0.3);
}
/* Contact Items */
.contact-item {
    transition: all 0.3s ease;
}
.contact-item:hover {
    transform: translateX(5px);
}
.contact-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}
.contact-item:hover .contact-icon {
    background: var(--gradient-secondary);
}
.contact-icon i {
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
/* Call to Action Section */
.footer-cta {
    background: rgba(0, 0, 0, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
}
.footer-cta::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.02)" d="M0,0 L1000,0 L1000,1000 L0,1000 Z"></path></svg>');
    background-size: cover;
}
.cta-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    flex-wrap: wrap;
}
.cta-buttons .btn {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}
.cta-buttons .btn-primary {
    background: var(--gradient-secondary);
    border: none;
}
.cta-buttons .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(230, 62, 62, 0.3);
}
.cta-buttons .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}
/* Footer Bottom */
.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    position: relative;
}
.footer-bottom-links a {
    transition: all 0.3s ease;
    position: relative;
}
.footer-bottom-links a:hover {
    color: var(--accent-light) !important;
}
.footer-bottom-links a::after {
    content: '•';
    margin-left: 12px;
    color: rgba(255, 255, 255, 0.5);
}
.footer-bottom-links a:last-child::after {
    content: '';
    margin-left: 0;
}
/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--gradient-secondary);
    border: none;
    color: white;
    font-size: 1.2rem;
    box-shadow: var(--shadow-medium);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.back-to-top.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.back-to-top:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: var(--shadow-hover);
}
/* Animation for footer elements */
.footer-section [data-aos] {
    transition: all 0.6s ease;
}
/* Enhanced hover effects for footer cards */
.footer-brand, .footer-links, .footer-contact {
    position: relative;
    z-index: 1;
}
/* Responsive Design */
@media (max-width: 768px) {
    .footer-logo {
        font-size: 1.4rem;
    }
    .footer-logo i {
        font-size: 1.4rem;
    }
    .footer-title {
        font-size: 1.1rem;
    }
    .social-links {
        justify-content: flex-start;
    }
    .footer-cta .d-flex {
        text-align: center;
        justify-content: center;
    }
    .cta-buttons {
        justify-content: center;
        margin-top: 15px;
    }
    .footer-bottom-links {
        text-align: center !important;
        margin-top: 10px;
    }
    .footer-bottom-links a {
        display: inline-block;
        margin: 0 8px;
    }
    .footer-bottom-links a::after {
        display: none;
    }
    .back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
        font-size: 1.1rem;
    }
}
@media (max-width: 576px) {
    .footer-main {
        padding: 40px 0;
    }
    .footer-logo {
        font-size: 1.3rem;
    }
    .footer-logo i {
        font-size: 1.3rem;
    }
    .social-link {
        width: 40px;
        height: 40px;
    }
    .contact-icon {
        width: 35px;
        height: 35px;
    }
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    .cta-buttons .btn {
        width: 200px;
        margin-bottom: 10px;
    }
    .back-to-top {
        bottom: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
/* Additional animations */
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
.footer-section [data-aos="fade-up"] {
    animation: fadeInUp 0.6s ease-out;
}
/* Enhanced accessibility */
.footer-link:focus,
.social-link:focus,
.back-to-top:focus {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}
/* Print styles */
@media print {
    .footer-section {
        background: white !important;
        color: black !important;
    }
    .footer-logo,
    .footer-title,
    .contact-info h6 {
        color: black !important;
    }
    .footer-link,
    .footer-description,
    .contact-info p,
    .contact-info a {
        color: #666 !important;
    }
    .social-links,
    .back-to-top {
        display: none;
    }
}
</style>
<script>
// Enhanced Footer Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Back to top button functionality
    const backToTopButton = document.querySelector('.back-to-top');
    function toggleBackToTop() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    }
    window.addEventListener('scroll', toggleBackToTop);
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    // Initialize AOS for footer animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 50
        });
    }
    // Enhanced hover effects for social links
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.1)';
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    // Enhanced footer link interactions
    const footerLinks = document.querySelectorAll('.footer-link');
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.paddingLeft = '10px';
        });
        link.addEventListener('mouseleave', function() {
            this.style.paddingLeft = '0';
        });
    });
    // Contact item hover effects
    const contactItems = document.querySelectorAll('.contact-item');
    contactItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    // Smooth scrolling for footer navigation links
    document.querySelectorAll('.footer-link[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if(targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                }
            }
        });
    });
    // Add loading animation for images
    const footerImages = document.querySelectorAll('footer img');
    footerImages.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
            this.style.transform = 'scale(1)';
        });
        // Set initial state
        img.style.opacity = '0';
        img.style.transform = 'scale(0.9)';
        img.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    // Enhanced performance: Lazy load footer images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        document.querySelectorAll('footer img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Focus management for accessibility
        if (e.key === 'Tab') {
            // Add focus styles dynamically
            const focusedElement = document.activeElement;
            if (focusedElement.classList.contains('footer-link') || 
                focusedElement.classList.contains('social-link')) {
                focusedElement.style.outline = '2px solid var(--accent)';
                focusedElement.style.outlineOffset = '2px';
            }
        }
    });
    // Remove focus styles on mouse interaction
    document.addEventListener('mousedown', function() {
        const focusedElements = document.querySelectorAll('.footer-link:focus, .social-link:focus');
        focusedElements.forEach(el => {
            el.style.outline = 'none';
        });
    });
});
</script>
<!-- Essential Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<!-- Performance Optimization: Load scripts after page load -->
<script>
// Load non-critical scripts after page load
window.addEventListener('load', function() {
    // Initialize AOS animations
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
    // Initialize tooltips and popovers if needed
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Page loader functionality - Only for home page
    const pageLoader = document.getElementById('pageLoader');
    if (pageLoader) {
        setTimeout(function() {
            pageLoader.classList.add('hidden');
        }, 2000);
    }
});
</script>
    </body>
</html>