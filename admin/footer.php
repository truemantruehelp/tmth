<?php
// admin/footer.php
?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
        // Admin-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-collapse sidebar on mobile
            if (window.innerWidth < 768) {
                const sidebar = new bootstrap.Collapse(document.getElementById('adminSidebar'));
                sidebar.hide();
            }

            // Active nav link highlighting
            const currentPage = location.pathname.split('/').pop();
            document.querySelectorAll('.admin-nav-link').forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = new bootstrap.Collapse(document.getElementById('adminSidebar'));
            sidebar.toggle();
        }
    </script>
</body>
</html>