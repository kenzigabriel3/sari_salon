<!-- ======= FOOTER ======= -->
<footer class="footer">
  <div class="footer-container">

    <!-- Kolom 1 -->
    <div class="footer-section">
      <h2 class="footer-title">Beauty Sari Salon</h2>
      <p><strong>Jam Operasional:</strong></p>
      <p>Minggu - Jumat: 09:00 - 20:00 WIB</p>
      <p><strong>Setiap hari Sabtu libur</strong></p>
      <p><strong>Last Order:</strong> 19:30 WIB</p>
    </div>

    <!-- Kolom 2 -->
    <div class="footer-section">
      <h2 class="footer-title">Temukan Kami</h2>
      <div class="social-icons">
        <a href="https://www.instagram.com/sarisalon10gmail.c" target="_blank" aria-label="Instagram">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" viewBox="0 0 24 24">
            <path d="M7.75 2A5.75 5.75 0 0 0 2 7.75v8.5A5.75 5.75 0 0 0 7.75 22h8.5A5.75 5.75 0 0 0 22 16.25v-8.5A5.75 5.75 0 0 0 16.25 2zm0 1.5h8.5A4.25 4.25 0 0 1 20.5 7.75v8.5A4.25 4.25 0 0 1 16.25 20.5h-8.5A4.25 4.25 0 0 1 3.5 16.25v-8.5A4.25 4.25 0 0 1 7.75 3.5zm8.5 2a.75.75 0 0 0 0 1.5h.01a.75.75 0 0 0 0-1.5zm-4.25 1A5.25 5.25 0 1 0 17.25 12 5.26 5.26 0 0 0 12 6.5zm0 1.5a3.75 3.75 0 1 1-3.75 3.75A3.75 3.75 0 0 1 12 8z"/>
          </svg>
        </a>
        <a href="https://www.tiktok.com/@sarisalonsepatan" target="_blank" aria-label="TikTok">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" viewBox="0 0 24 24">
            <path d="M12 2a1 1 0 0 1 1 1c0 5.518 4.482 10 10 10a1 1 0 0 1 0 2 11.944 11.944 0 0 1-6-1.608V18a6 6 0 1 1-6-6 1 1 0 0 1 0 2 4 4 0 1 0 4 4V3a1 1 0 0 1 1-1z"/>
          </svg>
        </a>
      </div>
    </div>

    <!-- Kolom 3 -->
    <div class="footer-section">
      <h2 class="footer-title">Menu</h2>
      <ul>
        <li><a href="./index.php">Home</a></li>
        <li><a href="./about.php">Tentang</a></li>
        <li><a href="./services.php">Layanan</a></li>
        <li><a href="./contact.php">Kontak</a></li>
        <li><a href="./gallery.php">Galeri</a></li>
      </ul>
    </div>

    <!-- Kolom 4 (Kontak) -->
    <div class="footer-section">
      <h2 class="footer-title">Kontak Kami</h2>
      <div class="footer-contact">
        <p><strong>Alamat:</strong></p>
        <p>Jl. Raya Mauk Km 12, RT 02 RW 02,<br>
        Desa Pisangan Jaya, Kosambi Sepatan,<br>
        Kab. Tangerang, Banten 15520</p>
        
        <p><strong>Telepon:</strong> 0812-8447-7303</p>
        <p><strong>Email:</strong> sarisalon11@gmail.com</p>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© 2025 Beauty Sari Salon | Semua Hak Dilindungi</p>
  </div>
</footer>

<!-- JavaScript for Header User Profile Dropdown and Mobile Menu -->
<script>
  // Mobile Menu Toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('nav');
  const navLinks = document.querySelectorAll('nav a');

  if (menuToggle && nav) {
    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      menuToggle.classList.toggle('active');
      nav.classList.toggle('active');
    });

    // Close menu when clicking a link
    navLinks.forEach(link => {
      link.addEventListener('click', () => {
        nav.classList.remove('active');
        menuToggle.classList.remove('active');
      });
    });
  }

  // User Profile Dropdown Toggle (for desktop & mobile compatibility)
  const userProfile = document.querySelector('.user-profile');
  if (userProfile) {
    userProfile.addEventListener('click', (e) => {
      e.stopPropagation();
      userProfile.classList.toggle('active');
    });
  }

  // Close dropdowns and menus when clicking outside
  document.addEventListener('click', (e) => {
    if (nav && menuToggle && !nav.contains(e.target) && !menuToggle.contains(e.target)) {
      nav.classList.remove('active');
      menuToggle.classList.remove('active');
    }
    if (userProfile && !userProfile.contains(e.target)) {
      userProfile.classList.remove('active');
    }
  });
</script>

<!-- Custom Scroll Animation -->
<script>
  // Intersection Observer for scroll animations
  const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const delay = entry.target.getAttribute('data-delay');
        if (delay) {
          setTimeout(() => {
            entry.target.classList.add('animate');
          }, parseInt(delay));
        } else {
          entry.target.classList.add('animate');
        }
      } else {
        entry.target.classList.remove('animate');
      }
    });
  }, observerOptions);

  // Observe all elements with scroll-animate class
  document.addEventListener('DOMContentLoaded', () => {
    const animateElements = document.querySelectorAll('.scroll-animate');
    animateElements.forEach(el => observer.observe(el));
  });
</script>

</body>
</html>
