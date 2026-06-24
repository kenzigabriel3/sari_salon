<?php
$page_title = "Galeri";
include 'includes/header.php';
?>

<!-- GALLERY SECTION -->
<section class="gallery-section">
  <h2 style="margin-top: 50px; color:#ff00aa; text-align:center;" class="scroll-animate fade-down" data-delay="0"> 
      Kegiatan dan Suasana di Sari Salon
  </h2>
  <p class="scroll-animate fade-up" style="margin-block: 20px; text-align:center;" data-delay="100">Beauty Sari Salon berkomitmen memberikan pelayanan terbaik agar Anda tampil menawan dan percaya diri.</p>

  <div class="gallery-container">
    <!-- FOTO-FOTO -->
    <div class="gallery-item scroll-animate zoom-in" data-delay="100"><img src="assets/img/salon1.webp" alt="Ruang salon"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="150"><img src="assets/img/galeri2.jpeg" alt="Hair treatment"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="200"><img src="assets/img/bg3.jpeg" alt="Perawatan wajah"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="250"><img src="assets/img/kursus4.webp" alt="Perawatan rambut"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="300"><img src="assets/img/salon5.jpeg" alt="Make up pelanggan"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="350"><img src="assets/img/salon6.jpeg" alt="Area tunggu"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="400"><img src="assets/img/salon7.jpeg" alt="Produk perawatan rambut"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="450"><img src="assets/img/salon8.jpeg" alt="Manicure pedicure"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="500"><img src="assets/img/salon9.jpeg" alt="Pelanggan facial"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="550"><img src="assets/img/salon10.jpeg" alt="Hair coloring"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="600"><img src="assets/img/salon11.jpeg" alt="Ruang kerja stylist"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="650"><img src="assets/img/salon12.jpeg" alt="Peralatan salon modern"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="700"><img src="assets/img/salon13.jpeg" alt="Suasana ceria pelanggan"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="750"><img src="assets/img/salon14.jpeg" alt="Pelanggan rambut pendek"></div>
    <div class="gallery-item scroll-animate zoom-in" data-delay="800"><img src="assets/img/salon15.jpeg" alt="Hasil akhir hair styling"></div>

    <!-- VIDEO -->
    <div class="gallery-item scroll-animate fade-up" data-delay="850"><video src="assets/video/salon-video2.mp4" controls></video></div>
    <div class="gallery-item scroll-animate fade-up" data-delay="900"><video src="assets/video/salon-video1.mp4" controls></video></div>
    <div class="gallery-item scroll-animate fade-up" data-delay="950"><video src="assets/video/salon-video3.mp4" controls></video></div>
  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
  <span class="lightbox-prev" onclick="changeImage(-1)">&#10094;</span>
  <span class="lightbox-next" onclick="changeImage(1)">&#10095;</span>
  <div class="lightbox-content" id="lightbox-content"></div>
</div>

<script>
  // Lightbox Gallery
  let lightboxIndex = 0;
  const galleryPhotos = document.querySelectorAll('.gallery-item');
  const lightboxElement = document.getElementById('lightbox');
  const lightboxContentElement = document.getElementById('lightbox-content');

  if (galleryPhotos && lightboxElement && lightboxContentElement) {
    galleryPhotos.forEach((item, index) => {
      item.addEventListener('click', () => {
        lightboxIndex = index;
        openLightbox(item);
      });
    });
  }

  function openLightbox(item) {
    const img = item.querySelector('img');
    const video = item.querySelector('video');
    
    lightboxContentElement.innerHTML = '';
    
    if (img) {
      const imgClone = img.cloneNode(true);
      lightboxContentElement.appendChild(imgClone);
    } else if (video) {
      const videoClone = video.cloneNode(true);
      videoClone.controls = true;
      videoClone.autoplay = true;
      lightboxContentElement.appendChild(videoClone);
    }
    
    lightboxElement.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent body scroll
  }

  function closeLightbox() {
    lightboxElement.classList.remove('active');
    document.body.style.overflow = 'auto'; // Restore body scroll
    
    // Stop video if playing
    const video = lightboxContentElement.querySelector('video');
    if (video) {
      video.pause();
    }
  }

  function changeImage(direction) {
    lightboxIndex += direction;
    
    // Loop around
    if (lightboxIndex < 0) {
      lightboxIndex = galleryPhotos.length - 1;
    } else if (lightboxIndex >= galleryPhotos.length) {
      lightboxIndex = 0;
    }
    
    openLightbox(galleryPhotos[lightboxIndex]);
  }

  // Close lightbox on ESC key and arrow navigation
  document.addEventListener('keydown', (e) => {
    if (lightboxElement.classList.contains('active')) {
      if (e.key === 'Escape') {
        closeLightbox();
      } else if (e.key === 'ArrowLeft') {
        changeImage(-1);
      } else if (e.key === 'ArrowRight') {
        changeImage(1);
      }
    }
  });

  // Close lightbox when clicking outside content
  if (lightboxElement) {
    lightboxElement.addEventListener('click', (e) => {
      if (e.target === lightboxElement) {
        closeLightbox();
      }
    });
  }
</script>

<?php include 'includes/footer.php'; ?>
