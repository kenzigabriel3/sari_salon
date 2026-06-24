<?php
// Include database connection and session starter
include_once __DIR__ . '/db.php';

// Active page detection helper
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . " - Beauty Sari Salon" : "Beauty Sari Salon"; ?></title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Stylesheet -->
  <link rel="stylesheet" href="./style.css">
</head>
<body>

  <!-- Navigasi -->
  <header>
    <div class="container">
      <div class="logo">
        <a href="./index.php" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
          <img src="assets/img/logo1.png" alt="Beauty Sari Salon Logo" width="70" style="transform: scale(1.6); margin-right: 5px;">
          <span class="beauty-text">Beauty</span> <span class="sari-text">Sari Salon</span>
        </a>
      </div>
      
      <div class="menu-toggle">
        <span></span>
        <span></span>
        <span></span>
      </div>
      
      <nav>
        <ul>
          <li><a href="./index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
          <li><a href="./about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Tentang</a></li>
          <li><a href="./services.php" class="<?php echo ($current_page == 'services.php' || $current_page == 'booking.php') ? 'active' : ''; ?>">Layanan</a></li>
          <li><a href="./gallery.php" class="<?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>">Galeri</a></li>
          
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="./antrian.php" class="<?php echo ($current_page == 'antrian.php') ? 'active' : ''; ?>">Antrian Saya</a></li>
          <?php endif; ?>
          
          <li><a href="./contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Kontak</a></li>
          
          <!-- Authentication Button / Profile -->
          <li class="nav-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
              <div class="user-profile">
                <span class="user-greeting">Hai, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <div class="avatar-circle">
                  <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <div class="dropdown-menu">
                  <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="./admin/index.php"><i class="fa fa-chart-line"></i> Panel Admin</a>
                  <?php endif; ?>
                  <a href="./antrian.php"><i class="fa fa-list-alt"></i> Antrian Saya</a>
                  <a href="./logout.php" class="logout-btn"><i class="fa fa-sign-out-alt"></i> Logout</a>
                </div>
              </div>
            <?php else: ?>
              <a href="./login.php" class="btn-login-nav">Login</a>
            <?php endif; ?>
          </li>
        </ul>
      </nav>
    </div>
  </header>
  
  <div class="header-spacer" style="height: 70px;"></div>
