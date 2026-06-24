<?php
include_once __DIR__ . '/../../includes/db.php';

// Check ADMIN session (separate from user session)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . " — Admin Dashboard" : "Admin Dashboard"; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,600&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Toast Notification System */
    .toast-container {
      position: fixed; top: 24px; right: 24px;
      z-index: 99999; display: flex; flex-direction: column; gap: 10px;
    }
    .toast {
      background: white; border-radius: 14px; padding: 14px 18px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      display: flex; align-items: center; gap: 12px;
      min-width: 300px; max-width: 420px;
      border-left: 4px solid #ff4081;
      animation: toastSlideIn 0.35s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    .toast.success { border-left-color: #4caf50; }
    .toast.error   { border-left-color: #f44336; }
    .toast.warning { border-left-color: #ff9800; }
    .toast.info    { border-left-color: #2196f3; }
    .toast-icon    { font-size: 1.3rem; flex-shrink: 0; }
    .toast.success .toast-icon { color: #4caf50; }
    .toast.error   .toast-icon { color: #f44336; }
    .toast.warning .toast-icon { color: #ff9800; }
    .toast.info    .toast-icon { color: #2196f3; }
    .toast-msg     { flex: 1; font-size: 0.83rem; font-weight: 500; color: #2c3e50; line-height: 1.4; }
    .toast-close   { background: none; border: none; cursor: pointer; color: #b0bec5; font-size: 1.1rem; padding: 0; }
    .toast-close:hover { color: #607d8b; }
    @keyframes toastSlideIn {
      from { opacity:0; transform: translateX(100px); }
      to   { opacity:1; transform: translateX(0); }
    }
    @keyframes toastSlideOut {
      from { opacity:1; transform: translateX(0); }
      to   { opacity:0; transform: translateX(100px); }
    }
  </style>
</head>
<body>
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
function showToast(message, type = 'success', duration = 4000) {
  const container = document.getElementById('toastContainer');
  const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <i class="fa-solid ${icons[type] || icons.info} toast-icon"></i>
    <span class="toast-msg">${message}</span>
    <button class="toast-close" onclick="dismissToast(this.parentElement)">×</button>
  `;
  container.appendChild(toast);
  const timer = setTimeout(() => dismissToast(toast), duration);
  toast._timer = timer;
}
function dismissToast(toast) {
  if (!toast || !toast.parentElement) return;
  clearTimeout(toast._timer);
  toast.style.animation = 'toastSlideOut 0.3s ease forwards';
  setTimeout(() => toast.remove(), 300);
}
</script>

  <!-- Sidebar -->
  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <img src="../assets/img/logo1.png" alt="Logo">
      <div class="sidebar-brand">
        <h3>Beauty Sari</h3>
        <p>Admin Dashboard</p>
      </div>
    </div>
    
    <ul class="sidebar-menu">
      <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
        <a href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
      </li>
      <li class="<?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
        <a href="bookings.php"><i class="fa-regular fa-calendar-check"></i> Booking & Antrian</a>
      </li>
      <li class="<?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
        <a href="payments.php"><i class="fa-solid fa-money-bill-wave"></i> Konfirmasi Pembayaran
          <?php
          // Show pending payment count badge
          try {
            $stmt_badge = $pdo->query("SELECT COUNT(*) FROM bookings WHERE payment_status = 'pending_transfer'");
            $pending_pay = $stmt_badge->fetchColumn();
            if ($pending_pay > 0) echo "<span style='background:#ff4081;color:white;border-radius:20px;padding:1px 7px;font-size:0.65rem;margin-left:6px;'>$pending_pay</span>";
          } catch(Exception $e) {}
          ?>
        </a>
      </li>
      <li class="<?php echo ($current_page == 'services.php') ? 'active' : ''; ?>">
        <a href="services.php"><i class="fa-solid fa-spa"></i> Layanan</a>
      </li>
      <li class="<?php echo ($current_page == 'employees.php') ? 'active' : ''; ?>">
        <a href="employees.php"><i class="fa-solid fa-user-tie"></i> Karyawan</a>
      </li>
      <li><a href="#" onclick="showToast('Fitur Jadwal Karyawan sedang dikembangkan.','info'); return false;"><i class="fa-regular fa-clock"></i> Jadwal Karyawan</a></li>
      <li><a href="#" onclick="showToast('Fitur Pelanggan sedang dikembangkan.','info'); return false;"><i class="fa-regular fa-user"></i> Pelanggan</a></li>
      <li><a href="#" onclick="showToast('Fitur Laporan sedang dikembangkan.','info'); return false;"><i class="fa-solid fa-chart-pie"></i> Laporan</a></li>
      <li><a href="#" onclick="showToast('Fitur Pengaturan sedang dikembangkan.','info'); return false;"><i class="fa-solid fa-sliders"></i> Pengaturan</a></li>
    </ul>

    <div class="sidebar-promo-card">
      <img src="../assets/icons/faciel.png" alt="Card Icon" style="border-radius:8px;">
      <h4>Kelola Salon Anda</h4>
      <p>Pantau booking, karyawan, layanan, dan laporan bisnis dalam satu dashboard.</p>
      <a href="../index.php" class="btn-sidebar-action" target="_blank">Lihat Halaman Utama</a>
    </div>
  </aside>

  <!-- Main Wrapper -->
  <div class="main-wrapper">
    <header class="top-navbar">
      <div class="nav-left">
        <button class="menu-toggle-btn" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
        <div class="page-info">
          <h1><?php echo isset($page_title) ? $page_title : "Dashboard"; ?></h1>
          <p><?php echo isset($page_subtitle) ? $page_subtitle : "Panel administrasi Beauty Sari Salon."; ?></p>
        </div>
      </div>
      <div class="nav-right">
        <div class="date-widget">
          <i class="fa-regular fa-calendar"></i>
          <span><?php echo date('d F Y'); ?></span>
        </div>
        <div class="admin-profile">
          <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#ff4081,#f50057);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;">
            <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
          </div>
          <div class="admin-info">
            <h4><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h4>
            <span>Administrator</span>
          </div>
          <a href="logout.php" title="Logout" style="margin-left:15px;color:var(--text-muted);transition:color 0.2s;" onmouseover="this.style.color='#f44336'" onmouseout="this.style.color='var(--text-muted)'">
            <i class="fa-solid fa-right-from-bracket" style="font-size:1.1rem;"></i>
          </a>
        </div>
      </div>
    </header>
    <main class="content-body">
