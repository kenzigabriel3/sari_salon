<?php
$page_title = "Home";
include 'includes/header.php';
?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="overlay"></div>
    <div class="content">
      <h1 class="scroll-animate fade-down">Kecantikan Alami, Pelayanan Terbaik</h1>
      <p class="scroll-animate fade-up">Rasakan pengalaman perawatan yang memanjakan diri Anda di Beauty Sari Salon</p>
      <a href="./booking.php" class="btn scroll-animate zoom-in">Booking Sekarang</a>
    </div>
  </section>

  <!-- Testimoni Section -->
  <section class="testimoni" id="testimoni">
    <div class="container-testimoni">
      <h2 class="judul-testimoni scroll-animate fade-down">Apa Kata Pelanggan Kami?</h2>
      <div class="testimoni-wrapper">

        <div class="testimoni-box scroll-animate fade-up" data-delay="0">
          <p class="testimoni-text">
            "Pelayanannya ramah banget dan hasilnya memuaskan! Rambut aku jadi lembut dan sehat lagi."
          </p>
          <div class="testimoni-user">
            <div class="avatar">RP</div>
            <div>
              <h4>Rina Putri</h4>
              <p>Pelanggan Hair Spa</p>
            </div>
          </div>
        </div>

        <div class="testimoni-box scroll-animate fade-up" data-delay="200">
          <p class="testimoni-text">
            "Tempatnya nyaman, bersih, dan harganya juga terjangkau. Recommended banget buat perawatan rutin!"
          </p>
          <div class="testimoni-user">
            <div class="avatar">SA</div>
            <div>
              <h4>Sari Andini</h4>
              <p>Pelanggan Facial Treatment</p>
            </div>
          </div>
        </div>

        <div class="testimoni-box scroll-animate fade-up" data-delay="400">
          <p class="testimoni-text">
            "Suka banget sama hasil make up-nya, cocok buat acara penting. Terima kasih Beauty Sari Salon!"
          </p>
          <div class="testimoni-user">
            <div class="avatar">AL</div>
            <div>
              <h4>Ayu Lestari</h4>
              <p>Pelanggan Make Up</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Galeri Section -->
  <section class="galeri" id="galeri">
    <div class="container-galeri">
      <h2 class="judul-galeri scroll-animate fade-down">Galeri Kecantikan Kami</h2>
      <p class="subjudul-galeri scroll-animate fade-up">
        Lihat hasil perawatan dan suasana nyaman di Beauty Sari Salon 💖
      </p>

      <div class="galeri-wrapper scroll-animate fade-up" data-delay="0">
        <div class="galeri-slider">
          <div class="galeri-item scroll-animate zoom-in" data-delay="100"><img src="assets/img/galeri1.jpeg" alt="Perawatan rambut"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="200"><img src="assets/img/galeri2.jpeg" alt="Makeup acara"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="300"><img src="assets/img/galeri3.webp" alt="Perawatan wajah"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="400"><img src="assets/img/galeri4.jpeg" alt="Hair spa"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="500"><img src="assets/img/galeri5.jpeg" alt="Ruang salon"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="600"><img src="assets/img/galeri6.jpeg" alt="Perawatan kuku"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="700"><img src="assets/img/galeri7.jpeg" alt="Perawatan alis"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="800"><img src="assets/img/galeri8.jpeg" alt="Kuku dan nail art"></div>
          <div class="galeri-item scroll-animate zoom-in" data-delay="900"><img src="assets/img/galeri9.jpeg" alt="Ruang relaksasi"></div>
        </div>

        <div class="slider-buttons">
          <button class="prev-btn">
            <svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-left" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M646 125C629 125 613 133 604 142L308 442C296 454 292 471 292 487 292 504 296 521 308 533L604 854C617 867 629 875 646 875 663 875 679 871 692 858 704 846 713 829 713 812 713 796 708 779 692 767L438 487 692 225C700 217 708 204 708 187 708 171 704 154 692 142 675 129 663 125 646 125Z"></path></svg>
          </button>
          <button class="next-btn"> 
            <svg aria-hidden="true" class="e-font-icon-svg e-eicon-chevron-right" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg"><path d="M696 533C708 521 713 504 713 487 713 471 708 454 696 446L400 146C388 133 375 125 354 125 338 125 325 129 313 142 300 154 292 171 292 187 292 204 296 221 308 233L563 492 304 771C292 783 288 800 288 817 288 833 296 850 308 863 321 871 338 875 354 875 371 875 388 867 400 854L696 533Z"></path></svg>
          </button>
        </div>

        <!-- Pagination Dots -->
        <div class="slider-pagination"></div>
      </div>
    </div>
  </section>

  <!-- Kursus Section -->
  <section class="kursus" id="kursus">
    <div class="container-kursus">
      <div class="kursus-content">

        <!-- Teks -->
        <div class="kursus-text scroll-animate fade-right">
          <h2 class="scroll-animate fade-down">Terima Jasa Kursus Kecantikan</h2>
          <p class="scroll-animate fade-up">
            Terima jasa kursus <b>semua perawatan kecantikan</b> di <b>Beauty Sari Salon</b>.<br>
            Belajar langsung dari tenaga profesional dengan metode praktik terbaik.<br>
            Untuk info lebih lanjut, silakan hubungi kami melalui WhatsApp di bawah ini 💖
          </p>

          <a href="https://wa.me/6281284477303" target="_blank" class="btn-whatsapp scroll-animate zoom-in">
            <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
          </a>
        </div>

        <!-- Slider Gambar -->
        <div class="kursus-slider scroll-animate fade-left">
          <div class="kursus-track">
            <div class="kursus-item"><img src="assets/img/kursus1.webp" alt="Kursus 1"></div>
            <div class="kursus-item"><img src="assets/img/kursus2.webp" alt="Kursus 2"></div>
            <div class="kursus-item"><img src="assets/img/kursus3.webp" alt="Kursus 3"></div>
            <div class="kursus-item"><img src="assets/img/kursus4.webp" alt="Kursus 4"></div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Script Background Otomatis -->
  <script>
    const images = [
      "assets/img/bg5.jpg",
      "assets/img/bg2.jpg",
      "assets/img/bg1.jpeg",
      "assets/img/bg4.jpeg"
    ];
    let index = 0;
    const hero = document.querySelector(".hero");

    if (hero) {
      setInterval(() => {
        index = (index + 1) % images.length;
        hero.style.backgroundImage = `url(${images[index]})`;
      }, 5000);
    }
  </script>

  <!-- Script Galeri Slider -->
  <script>
    const galeriSlider = document.querySelector('.galeri-slider');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const paginationContainer = document.querySelector('.slider-pagination');
    
    if (prevBtn && nextBtn && galeriSlider && paginationContainer) {
      const galeriItems = galeriSlider.querySelectorAll('.galeri-item');
      let currentIndex = 0;

      // Create pagination dots
      galeriItems.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('pagination-dot');
        if (index === 0) dot.classList.add('active');
        dot.setAttribute('data-index', index);
        paginationContainer.appendChild(dot);
      });

      const paginationDots = paginationContainer.querySelectorAll('.pagination-dot');

      // Get single item width including gap
      const getItemWidth = () => {
        const item = galeriSlider.querySelector('.galeri-item');
        if (item) {
          const itemStyle = window.getComputedStyle(item);
          const itemWidth = item.offsetWidth;
          const gap = parseInt(window.getComputedStyle(galeriSlider).gap) || 20;
          return itemWidth + gap;
        }
        return 300; // fallback
      };

      // Update active dot
      const updateActiveDot = (index) => {
        paginationDots.forEach(dot => dot.classList.remove('active'));
        if (paginationDots[index]) {
          paginationDots[index].classList.add('active');
        }
      };

      // Scroll to specific index
      const scrollToIndex = (index) => {
        currentIndex = index;
        galeriSlider.scrollTo({
          left: getItemWidth() * index,
          behavior: 'smooth'
        });
        updateActiveDot(index);
      };
      
      // Smooth scroll with buttons - one item at a time
      prevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
          currentIndex--;
          scrollToIndex(currentIndex);
        }
      });

      nextBtn.addEventListener('click', () => {
        if (currentIndex < galeriItems.length - 1) {
          currentIndex++;
          scrollToIndex(currentIndex);
        }
      });

      // Click on pagination dots
      paginationDots.forEach(dot => {
        dot.addEventListener('click', () => {
          const index = parseInt(dot.getAttribute('data-index'));
          scrollToIndex(index);
        });
      });

      // Update dots on manual scroll
      galeriSlider.addEventListener('scroll', () => {
        const scrollLeft = galeriSlider.scrollLeft;
        const itemWidth = getItemWidth();
        const newIndex = Math.round(scrollLeft / itemWidth);
        if (newIndex !== currentIndex && newIndex >= 0 && newIndex < galeriItems.length) {
          currentIndex = newIndex;
          updateActiveDot(currentIndex);
        }
      });
    }
  </script>

  <!-- Script Slider Kursus -->
  <script>
    const kursusTrack = document.querySelector('.kursus-track');
    const kursusItems = document.querySelectorAll('.kursus-item');
    let kursusIndex = 0;

    if (kursusTrack && kursusItems.length > 0) {
      kursusTrack.style.transition = 'transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)';

      setInterval(() => {
        kursusIndex = (kursusIndex + 1) % kursusItems.length;
        kursusTrack.style.transform = `translateX(-${kursusIndex * 100}%)`;
      }, 4000);
    }
  </script>

<?php include 'includes/footer.php'; ?>
