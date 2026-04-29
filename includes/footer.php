<?php
/**
 * KEBANA Management System - Footer Component
 * File: includes/footer.php
 * 
 * Footer and closing tags
 * Include this file at the end of your page
 */
?>
    </div><!-- End Main Content Wrapper -->

    <!-- Footer -->
    <footer style="background: #fff; border-top: 1px solid #dee2e6; padding: 2rem 0; margin-top: 3rem;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0" style="color: #6c757d; font-size: 0.9rem;">
                        &copy; <?php echo date('Y'); ?> KEBANA Management System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0" style="color: #6c757d; font-size: 0.9rem;">
                        Version 1.0.0 | 
                        <a href="#" style="color: #0d6efd; text-decoration: none;">Privacy Policy</a> | 
                        <a href="#" style="color: #0d6efd; text-decoration: none;">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-1cmnv0tY8TnZ+Zp8uYjXY2EFd7qFIzyVgq7e0nxA0x0B8Hv5kD4YuoZsT7QCV5Lu" crossorigin="anonymous"></script>
    
    <!-- Optional: Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (!alert.classList.contains('alert-permanent')) {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>
