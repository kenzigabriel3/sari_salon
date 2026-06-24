<?php
$page_title = "Layanan";
include 'includes/header.php';

// Fetch all services from the database
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    $error_msg = "Gagal mengambil data layanan: " . $e->getMessage();
}
?>

<section class="services">
  <h2 style="margin-top: 50px; color:#ff00aa; text-align:center;" class="scroll-animate fade-down" data-delay="0"> 
    Layanan Kami
  </h2>
  <p class="scroll-animate fade-up" data-delay="100" style="margin-bottom: 40px; color: #666; font-size: clamp(0.9rem, 2vw, 1.1rem);">
    Kami menyediakan berbagai treatment perawatan wajah dan rambut dengan produk berkualitas dan tenaga ahli profesional.
  </p>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-danger" style="max-width: 600px; margin: 0 auto 30px;"><?php echo htmlspecialchars($error_msg); ?></div>
  <?php endif; ?>

  <div class="service-container">
    <?php 
    $delay = 0;
    foreach ($services as $service): 
    ?>
      <div class="service-card scroll-animate zoom-in" data-delay="<?php echo $delay; ?>">
        <img src="<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
        <h3><?php echo htmlspecialchars($service['name']); ?></h3>
        <p style="margin-bottom: 15px; min-height: 50px;"><?php echo htmlspecialchars($service['description']); ?></p>
        
        <div class="service-meta" style="margin-bottom: 20px; display: flex; justify-content: center; gap: 15px; font-size: 0.9rem; color: #555;">
          <span><i class="fa fa-clock" style="color: #ff4081;"></i> <?php echo $service['duration']; ?> menit</span>
          <span style="font-weight: bold; color: #ff2e7e;">Rp <?php echo number_format($service['price'], 0, ',', '.'); ?></span>
        </div>
        
        <a href="booking.php?service_id=<?php echo $service['id']; ?>" class="btn-booking-service" style="
          display: block;
          background: linear-gradient(135deg, #ff6b9d 0%, #ff4081 100%);
          color: white;
          text-decoration: none;
          padding: 10px 20px;
          border-radius: 25px;
          font-weight: 600;
          font-size: 0.9rem;
          transition: all 0.3s ease;
          box-shadow: 0 4px 10px rgba(255, 64, 129, 0.2);
        ">Pilih Layanan</a>
      </div>
    <?php 
      $delay += 100;
      // Reset delay if too high for layout flow
      if ($delay > 600) $delay = 0;
    endforeach; 
    ?>
  </div>
</section>

<section class="cta">
  <h2 class="scroll-animate fade-up" data-delay="0">Tertarik dengan layanan kami?</h2>
  <a href="./contact.php" class="scroll-animate zoom-in" data-delay="200">Hubungi Kami Sekarang</a>
</section>

<?php include 'includes/footer.php'; ?>
