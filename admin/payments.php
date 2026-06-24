<?php
$page_title    = "Konfirmasi Pembayaran";
$page_subtitle = "Verifikasi bukti transfer dan konfirmasi pembayaran pelanggan.";
include_once 'includes/header.php';

// Handle payment confirmation or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);

    if ($_POST['action'] === 'confirm_payment' || $_POST['action'] === 'confirm_cash') {
        try {
            $pdo->beginTransaction();
            
            // Get booking details
            $stmt = $pdo->prepare("SELECT booking_date FROM bookings WHERE id = ? FOR UPDATE");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();
            
            if ($booking) {
                $b_date = $booking['booking_date'];
                
                // Count bookings that already have a queue number for that date
                $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE booking_date = ? AND queue_number IS NOT NULL AND queue_number != ''");
                $stmtCount->execute([$b_date]);
                $count = $stmtCount->fetch()['total'];
                
                $queue_num = "A-" . str_pad($count + 1, 3, "0", STR_PAD_LEFT);
                
                // Update booking
                $stmtUpdate = $pdo->prepare("UPDATE bookings SET queue_number = ?, payment_status = 'paid', status = 'pending' WHERE id = ?");
                $stmtUpdate->execute([$queue_num, $booking_id]);
                
                $pdo->commit();
                
                $success_txt = ($_POST['action'] === 'confirm_payment') ? 
                    'Pembayaran transfer dikonfirmasi! Nomor antrean: ' . $queue_num : 
                    'Pembayaran cash diterima! Nomor antrean: ' . $queue_num;
                    
                echo "<script>showToast('" . addslashes($success_txt) . "','success');</script>";
            } else {
                $pdo->rollBack();
                echo "<script>showToast('Data booking tidak ditemukan!','error');</script>";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "<script>showToast('Gagal memproses: " . addslashes($e->getMessage()) . "','error');</script>";
        }
    } elseif ($_POST['action'] === 'reject_payment') {
        try {
            $pdo->prepare("UPDATE bookings SET payment_status = 'unpaid', status = 'cancelled' WHERE id = ?")->execute([$booking_id]);
            echo "<script>showToast('Pembayaran ditolak. Booking dibatalkan.','warning');</script>";
        } catch (PDOException $e) {
            echo "<script>showToast('Gagal menolak: " . addslashes($e->getMessage()) . "','error');</script>";
        }
    }
}

// Fetch pending transfer payments
try {
    $stmt = $pdo->query("
        SELECT b.*, u.name as user_name, u.phone as user_phone,
               s.name as service_name, s.price,
               e.name as employee_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        JOIN employees e ON b.employee_id = e.id
        WHERE b.payment_status IN ('pending_transfer', 'pending_cash')
        ORDER BY b.created_at DESC
    ");
    $pending_payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_payments = [];
}

// Fetch recently confirmed payments today
try {
    $stmt_done = $pdo->query("
        SELECT b.*, u.name as user_name, s.name as service_name, s.price
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN services s ON b.service_id = s.id
        WHERE b.payment_status = 'paid' AND DATE(b.created_at) = CURDATE()
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $confirmed_today = $stmt_done->fetchAll();
} catch (PDOException $e) {
    $confirmed_today = [];
}
?>

<!-- Stats Row -->
<div class="stat-cards-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:28px;">
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Menunggu Verifikasi Transfer</span>
      <span class="value" style="color:#ff9800;"><?php echo count(array_filter($pending_payments, function($p) { return $p['payment_status']==='pending_transfer'; })); ?></span>
    </div>
    <div class="stat-icon-wrapper" style="background:#fff9e6;color:#ff9800;"><i class="fa-solid fa-file-invoice"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Menunggu Bayar Cash</span>
      <span class="value" style="color:#2196f3;"><?php echo count(array_filter($pending_payments, function($p) { return $p['payment_status']==='pending_cash'; })); ?></span>
    </div>
    <div class="stat-icon-wrapper" style="background:#eef7ff;color:#2196f3;"><i class="fa-solid fa-money-bills"></i></div>
  </div>
  <div class="stat-card">
    <div class="stat-details">
      <span class="label">Dikonfirmasi Hari Ini</span>
      <span class="value" style="color:#4caf50;"><?php echo count($confirmed_today); ?></span>
    </div>
    <div class="stat-icon-wrapper" style="background:#effbf4;color:#4caf50;"><i class="fa-solid fa-circle-check"></i></div>
  </div>
</div>

<!-- Pending Payments Table -->
<div class="dashboard-panel" style="margin-bottom:28px;">
  <div class="panel-header">
    <h3><i class="fa-solid fa-clock" style="color:#ff9800;"></i> Pembayaran Menunggu Konfirmasi</h3>
    <span style="font-size:0.8rem;color:var(--text-muted);"><?php echo count($pending_payments); ?> transaksi</span>
  </div>

  <?php if (empty($pending_payments)): ?>
    <div style="text-align:center;padding:40px 20px;color:var(--text-muted);">
      <i class="fa-solid fa-circle-check" style="font-size:3rem;color:#4caf50;opacity:0.5;margin-bottom:12px;display:block;"></i>
      <p>Tidak ada pembayaran yang perlu dikonfirmasi. Semua bersih! ✅</p>
    </div>
  <?php else: ?>
    <div class="admin-table-card" style="box-shadow:none;padding:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Antrian</th>
            <th>Pelanggan</th>
            <th>Layanan</th>
            <th>Karyawan</th>
            <th>Jadwal</th>
            <th>Metode</th>
            <th>Total</th>
            <th>Bukti Transfer</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pending_payments as $p): ?>
            <tr>
              <td>
                <?php if (!empty($p['queue_number'])): ?>
                  <strong style="color:var(--primary-color);font-size:1.1rem;"><?php echo htmlspecialchars($p['queue_number']); ?></strong>
                <?php else: ?>
                  <span class="status-badge" style="background:#fce4ec;color:#e91e63;padding:4px 8px;border-radius:6px;font-size:0.7rem;font-weight:600;display:inline-block;white-space:nowrap;">BARU</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($p['user_name']); ?></strong><br>
                <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo $p['user_phone']; ?></span>
              </td>
              <td><?php echo htmlspecialchars($p['service_name']); ?></td>
              <td><?php echo htmlspecialchars($p['employee_name']); ?></td>
              <td>
                <?php echo date('d M Y', strtotime($p['booking_date'])); ?><br>
                <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo substr($p['booking_time'],0,5); ?> WIB</span>
              </td>
              <td>
                <?php if ($p['payment_status'] === 'pending_transfer'): ?>
                  <span class="status-badge" style="background:#fff9e6;color:#e65100;padding:5px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;">
                    <i class="fa-solid fa-university"></i> Transfer Bank
                  </span>
                <?php else: ?>
                  <span class="status-badge" style="background:#eef7ff;color:#1565c0;padding:5px 10px;border-radius:20px;font-size:0.7rem;font-weight:600;">
                    <i class="fa-solid fa-money-bill"></i> Cash di Tempat
                  </span>
                <?php endif; ?>
              </td>
              <td><strong>Rp <?php echo number_format($p['price'],0,',','.'); ?></strong></td>
              <td>
                <?php if (!empty($p['payment_proof'])): ?>
                  <a href="../<?php echo htmlspecialchars($p['payment_proof']); ?>" target="_blank">
                    <img src="../<?php echo htmlspecialchars($p['payment_proof']); ?>" alt="Bukti" 
                         style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:2px solid var(--border-color);cursor:pointer;transition:transform 0.2s;"
                         onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                  </a>
                <?php else: ?>
                  <span style="font-size:0.75rem;color:var(--text-muted);font-style:italic;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;flex-direction:column;gap:6px;">
                  <?php if ($p['payment_status'] === 'pending_transfer'): ?>
                    <form action="payments.php" method="POST">
                      <input type="hidden" name="action" value="confirm_payment">
                      <input type="hidden" name="booking_id" value="<?php echo $p['id']; ?>">
                      <button type="submit" style="width:100%;background:#4caf50;color:white;border:none;padding:7px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                        <i class="fa-solid fa-check"></i> Konfirmasi
                      </button>
                    </form>
                    <form action="payments.php" method="POST" onsubmit="return confirm('Tolak pembayaran ini? Booking akan dibatalkan.')">
                      <input type="hidden" name="action" value="reject_payment">
                      <input type="hidden" name="booking_id" value="<?php echo $p['id']; ?>">
                      <button type="submit" style="width:100%;background:#f44336;color:white;border:none;padding:7px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                        <i class="fa-solid fa-xmark"></i> Tolak
                      </button>
                    </form>
                  <?php else: ?>
                    <form action="payments.php" method="POST">
                      <input type="hidden" name="action" value="confirm_cash">
                      <input type="hidden" name="booking_id" value="<?php echo $p['id']; ?>">
                      <button type="submit" style="width:100%;background:#2196f3;color:white;border:none;padding:7px 12px;border-radius:8px;font-size:0.75rem;font-weight:600;cursor:pointer;">
                        <i class="fa-solid fa-money-bill-wave"></i> Cash Diterima
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Confirmed Today -->
<?php if (!empty($confirmed_today)): ?>
<div class="dashboard-panel">
  <div class="panel-header">
    <h3><i class="fa-solid fa-circle-check" style="color:#4caf50;"></i> Dikonfirmasi Hari Ini</h3>
  </div>
  <div class="admin-table-card" style="box-shadow:none;padding:0;">
    <table class="admin-table">
      <thead>
        <tr><th>Antrian</th><th>Pelanggan</th><th>Layanan</th><th>Total</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($confirmed_today as $c): ?>
          <tr>
            <td><strong style="color:var(--primary-color);"><?php echo $c['queue_number']; ?></strong></td>
            <td><?php echo htmlspecialchars($c['user_name']); ?></td>
            <td><?php echo htmlspecialchars($c['service_name']); ?></td>
            <td>Rp <?php echo number_format($c['price'],0,',','.'); ?></td>
            <td><span class="status-badge completed">✅ Lunas</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
