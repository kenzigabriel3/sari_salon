    </main>

    <!-- Footer -->
    <footer class="admin-footer">
      <span>&copy; <?php echo date('Y'); ?> Beauty Sari Salon. All rights reserved.</span>
      <span>Version 1.0.0</span>
    </footer>
  </div>

  <!-- Sidebar Responsive Toggle Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('adminSidebar');
      const toggleBtn = document.getElementById('sidebarToggle');
      
      if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
          sidebar.classList.toggle('show');
          e.stopPropagation();
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
          if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
            sidebar.classList.remove('show');
          }
        });
      }
    });
  </script>
</body>
</html>
