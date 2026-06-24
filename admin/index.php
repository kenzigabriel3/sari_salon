<?php
$page_title = "Dashboard";
$page_subtitle = "Selamat datang, Admin! Kelola bisnis salon Anda dengan mudah.";
include_once 'includes/header.php';

// Handle quick queue status updates from the dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['status'];
    
    // Allowed statuses
    $allowed = ['pending', 'serving', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed)) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $booking_id]);
            echo "<script>alert('Status antrean berhasil diperbarui!'); window.location.href='index.php';</script>";
            exit();
        } catch (PDOException $e) {
            $error_msg = "Gagal memperbarui status: " . $e->getMessage();
        }
    }
}

// Fetch all database metrics for stats cards
try {
    $today_date = date('Y-m-d');
    
    // 1. Total Booking Hari Ini
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ?");
    $stmt->execute([$today_date]);
    $total_bookings_today = $stmt->fetchColumn();
    
    // 2. Total Antrian Hari Ini (Active / Completed - only with queue number)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND status != 'cancelled' AND queue_number IS NOT NULL AND queue_number != ''");
    $stmt->execute([$today_date]);
    $total_queue_today = $stmt->fetchColumn();
    
    // 3. Pendapatan Hari Ini — hanya dari yang payment_status = 'paid'
    $stmt = $pdo->prepare("
        SELECT SUM(s.price) 
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        WHERE b.booking_date = ? AND b.payment_status = 'paid'
    ");
    $stmt->execute([$today_date]);
    $revenue_today = $stmt->fetchColumn();
    $revenue_today = $revenue_today ? $revenue_today : 0;
    
    // 4. Total Layanan Aktif
    $stmt = $pdo->query("SELECT COUNT(*) FROM services");
    $total_services = $stmt->fetchColumn();

    // 5. Pending payments count
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE payment_status IN ('pending_transfer','pending_cash')");
    $pending_payments_count = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    die("Gagal memuat data metrik: " . $e->getMessage());
}

// Fetch revenue data for the last 7 days for the chart
$chart_dates = [];
$chart_revenues = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('d M', strtotime($date));
    $chart_dates[] = $label;
    
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(s.price) 
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.booking_date = ? AND b.payment_status = 'paid'
        ");
        $stmt->execute([$date]);
        $rev = $stmt->fetchColumn();
        $chart_revenues[] = $rev ? intval($rev) : 0;
    } catch (PDOException $e) {
        $chart_revenues[] = 0;
    }
}

// Fetch 5 recent bookings
try {
    $stmt = $pdo->query("
        SELECT b.*, u.name as user_name, s.name as service_name 
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_bookings = [];
}

// Fetch today's queues
try {
    $stmt = $pdo->prepare("
        SELECT b.*, u.name as user_name, s.name as service_name, e.name as employee_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        JOIN employees e ON b.employee_id = e.id
        WHERE b.booking_date = ? AND b.queue_number IS NOT NULL AND b.queue_number != ''
        ORDER BY b.booking_time ASC
    ");
    $today_queues = $stmt->fetchAll();
} catch (PDOException $e) {
    $today_queues = [];
}

// Fetch Popular Services (booking count per service)
try {
    $stmt = $pdo->query("
        SELECT s.name, s.image_path, COUNT(b.id) as count_bookings 
        FROM services s
        LEFT JOIN bookings b ON s.id = b.service_id
        GROUP BY s.id
        ORDER BY count_bookings DESC
        LIMIT 4
    ");
    $popular_services = $stmt->fetchAll();
    
    // Find maximum bookings to calculate percentage
    $max_bookings = 1;
    foreach ($popular_services as $ps) {
        if ($ps['count_bookings'] > $max_bookings) {
            $max_bookings = $ps['count_bookings'];
        }
    }
} catch (PDOException $e) {
    $popular_services = [];
    $max_bookings = 1;
}

// Fetch employee statistics
try {
    $stmt = $pdo->query("
        SELECT e.*, COUNT(b.id) as count_bookings
        FROM employees e
        LEFT JOIN bookings b ON e.id = b.employee_id
        GROUP BY e.id
        ORDER BY count_bookings DESC
    ");
    $top_employees = $stmt->fetchAll();
} catch (PDOException $e) {
    $top_employees = [];
}
?>

<!-- Stat Cards Row -->
<div class="stat-cards-grid">
  <!-- Card 1: Total Booking -->
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Total Booking Hari Ini</span>
      <span class="value"><?php echo $total_bookings_today; ?></span>
      <span class="trend up"><i class="fa-solid fa-arrow-trend-up"></i> +20% dari kemarin</span>
    </div>
    <div class="stat-icon-wrapper booking">
      <i class="fa-regular fa-calendar-check"></i>
    </div>
  </div>

  <!-- Card 2: Total Antrian -->
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Total Antrian Hari Ini</span>
      <span class="value"><?php echo $total_queue_today; ?></span>
      <span class="trend up"><i class="fa-solid fa-arrow-trend-up"></i> +15% dari kemarin</span>
    </div>
    <div class="stat-icon-wrapper queue">
      <i class="fa-solid fa-users-line"></i>
    </div>
  </div>

  <!-- Card 3: Pendapatan -->
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Pendapatan Hari Ini</span>
      <span class="value">Rp <?php echo number_format($revenue_today, 0, ',', '.'); ?></span>
      <span class="trend up"><i class="fa-solid fa-arrow-trend-up"></i> +25% dari kemarin</span>
    </div>
    <div class="stat-icon-wrapper revenue">
      <i class="fa-solid fa-rupiah-sign"></i>
    </div>
  </div>

  <!-- Card 4: Total Layanan -->
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Total Layanan</span>
      <span class="value"><?php echo $total_services; ?></span>
      <span class="trend neutral">Semua layanan aktif</span>
    </div>
    <div class="stat-icon-wrapper services">
      <i class="fa-solid fa-spa"></i>
    </div>
  </div>
</div>

<!-- Secondary Dashboard Row (Chart, Bookings, Queue) -->
<div class="dashboard-grid-3col">
  
  <!-- Line Chart Panel -->
  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Grafik Pendapatan</h3>
      <select>
        <option>7 Hari Terakhir</option>
      </select>
    </div>
    <div style="flex: 1; position: relative; min-height: 250px;">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <!-- Booking Terbaru Panel -->
  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Booking Terbaru</h3>
      <a href="bookings.php" class="btn-link">Lihat Semua</a>
    </div>
    <div class="booking-list-widget">
      <?php if (empty($recent_bookings)): ?>
        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 30px;">Belum ada booking masuk.</p>
      <?php else: ?>
        <?php foreach ($recent_bookings as $booking): ?>
          <div class="booking-item-widget">
            <div class="b-user-info">
              <div class="b-avatar">
                <?php echo strtoupper(substr($booking['user_name'], 0, 1)); ?>
              </div>
              <div class="b-name">
                <h4><?php echo htmlspecialchars($booking['user_name']); ?></h4>
                <p><?php echo htmlspecialchars($booking['service_name']); ?></p>
              </div>
            </div>
            <div class="b-time-info">
              <span class="q-num"><?php echo !empty($booking['queue_number']) ? htmlspecialchars($booking['queue_number']) : '—'; ?></span>
              <span class="status-badge <?php echo $booking['status']; ?>"><?php echo $booking['status']; ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Antrian Hari Ini Panel -->
  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Antrian Hari Ini</h3>
      <a href="bookings.php" class="btn-link">Lihat Semua</a>
    </div>
    <div class="queue-board-widget">
      <?php if (empty($today_queues)): ?>
        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 30px;">Tidak ada jadwal antrean hari ini.</p>
      <?php else: ?>
        <?php foreach (array_slice($today_queues, 0, 4) as $q): ?>
          <div class="queue-item-widget <?php echo ($q['status'] === 'serving') ? 'serving' : ''; ?>">
            <div class="q-badge"><?php echo $q['queue_number']; ?></div>
            <div class="q-details">
              <h4><?php echo htmlspecialchars($q['service_name']); ?></h4>
              <p>Oleh: <strong><?php echo htmlspecialchars($q['employee_name']); ?></strong> (<?php echo htmlspecialchars($q['user_name']); ?>)</p>
            </div>
            <div class="q-action-info">
              <span class="time"><?php echo substr($q['booking_time'], 0, 5); ?> WIB</span>
              <span class="status-badge <?php echo $q['status']; ?>">
                <?php 
                  if ($q['status'] === 'pending') echo 'Menunggu';
                  elseif ($q['status'] === 'serving') echo 'Dilayani';
                  elseif ($q['status'] === 'completed') echo 'Selesai';
                  elseif ($q['status'] === 'cancelled') echo 'Batal';
                ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
        <a href="bookings.php" class="btn-manage-queue"><i class="fa-solid fa-list-check"></i> Kelola Antrian</a>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Tertiary Dashboard Row (Popular Services, Top Employees, Reminders) -->
<div class="dashboard-grid-3col-alt">

  <!-- Popular Services Panel -->
  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Layanan Terpopuler</h3>
      <a href="services.php" class="btn-link">Lihat Semua</a>
    </div>
    <div class="popular-services-widget">
      <?php if (empty($popular_services)): ?>
        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 30px;">Belum ada pesanan layanan.</p>
      <?php else: ?>
        <?php $rank = 1; foreach ($popular_services as $ps): 
          $pct = round(($ps['count_bookings'] / $max_bookings) * 100);
          if ($pct < 10) $pct = 10; // minimum visual bar
        ?>
          <div class="popular-service-item">
            <span class="rank"><?php echo $rank++; ?></span>
            <img src="../<?php echo htmlspecialchars($ps['image_path']); ?>" alt="<?php echo htmlspecialchars($ps['name']); ?>" onerror="this.src='../assets/icons/faciel.png'">
            <div class="popular-service-details">
              <div class="pop-meta">
                <span><?php echo htmlspecialchars($ps['name']); ?></span>
                <span><?php echo $ps['count_bookings']; ?> Booking</span>
              </div>
              <div class="pop-bar-bg">
                <div class="pop-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Top Employees Panel -->
  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Karyawan Terbaik</h3>
      <a href="employees.php" class="btn-link">Lihat Semua</a>
    </div>
    <div class="top-employees-widget">
      <?php if (empty($top_employees)): ?>
        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 30px;">Karyawan belum diisi.</p>
      <?php else: ?>
        <?php foreach ($top_employees as $emp): ?>
          <div class="employee-rank-item">
            <div class="emp-profile-info">
              <img src="../<?php echo htmlspecialchars($emp['image_path']); ?>" alt="<?php echo htmlspecialchars($emp['name']); ?>" class="emp-avatar" onerror="this.src='../assets/img/karyawan-rida.png'">
              <div class="emp-rank-details">
                <h4><?php echo htmlspecialchars($emp['name']); ?></h4>
                <p><?php echo htmlspecialchars($emp['specialty']); ?></p>
              </div>
            </div>
            <div class="emp-stats">
              <div class="emp-rating-badge">
                <i class="fa-solid fa-star"></i> <?php echo number_format($emp['rating'], 1); ?>
              </div>
              <span class="emp-bookings-count"><?php echo $emp['count_bookings']; ?> Booking</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="dashboard-panel">
    <div class="panel-header">
      <h3>Pengingat & Notifikasi</h3>
    </div>
    <div class="reminders-widget">
      <?php if ($pending_payments_count > 0): ?>
      <div class="reminder-item warning">
        <i class="fa-solid fa-triangle-exclamation reminder-icon"></i>
        <div class="reminder-text">
          <h4><?php echo $pending_payments_count; ?> Pembayaran Menunggu Konfirmasi</h4>
          <p><a href="payments.php" style="color:var(--primary-color);font-weight:600;">Klik di sini untuk verifikasi →</a></p>
        </div>
      </div>
      <?php endif; ?>
      <div class="reminder-item">
        <i class="fa-solid fa-circle-exclamation reminder-icon"></i>
        <div class="reminder-text">
          <h4>Jadwal Layanan Hari Ini</h4>
          <p><?php echo $total_queue_today; ?> antrean terdaftar untuk hari ini.</p>
        </div>
      </div>
      <div class="reminder-item warning">
        <i class="fa-solid fa-triangle-exclamation reminder-icon"></i>
        <div class="reminder-text">
          <h4>Stok Produk</h4>
          <p>Cek inventaris secara berkala untuk memastikan ketersediaan produk.</p>
        </div>
      </div>
      <div class="reminder-item info">
        <i class="fa-solid fa-circle-info reminder-icon"></i>
        <div class="reminder-text">
          <h4>Laporan Bulanan</h4>
          <p>Laporan keuangan bulan ini siap untuk ditinjau.</p>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Chart.js Config Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient fill for line chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(255, 64, 129, 0.3)');
    gradient.addColorStop(1, 'rgba(255, 64, 129, 0.0)');
    
    const revenueChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($chart_dates); ?>,
        datasets: [{
          label: 'Pendapatan Harian (Rp)',
          data: <?php echo json_encode($chart_revenues); ?>,
          borderColor: '#ff4081',
          borderWidth: 3,
          backgroundColor: gradient,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#ff4081',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 6,
          pointHoverRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let value = context.parsed.y;
                return 'Pendapatan: Rp ' + value.toLocaleString('id-ID');
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f0f0f0'
            },
            ticks: {
              callback: function(value) {
                if (value >= 1000000) {
                  return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                } else if (value >= 1000) {
                  return 'Rp ' + (value / 1000) + 'K';
                }
                return 'Rp ' + value;
              },
              font: {
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              }
            }
          }
        }
      }
    });
  });
</script>

<?php
include_once 'includes/footer.php';
?>
