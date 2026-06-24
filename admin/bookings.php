<?php
$page_title = "Kelola Booking & Antrian";
$page_subtitle = "Daftar pemesanan treatment pelanggan Beauty Sari Salon.";
include_once 'includes/header.php';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $booking_id = intval($_POST['booking_id']);
        $new_status = $_POST['status'];
        
        $allowed = ['pending', 'serving', 'completed', 'cancelled'];
        if (in_array($new_status, $allowed)) {
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $booking_id]);
                $success_msg = "Status booking berhasil diperbarui.";
            } catch (PDOException $e) {
                $error_msg = "Gagal memperbarui status: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'delete_booking') {
        $booking_id = intval($_POST['booking_id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $success_msg = "Data booking berhasil dihapus.";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus data: " . $e->getMessage();
        }
    }
}

// Fetch search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Build SQL query dynamically
$query = "
    SELECT b.*, u.name as user_name, u.phone as user_phone, s.name as service_name, s.price, e.name as employee_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN employees e ON b.employee_id = e.id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $query .= " AND (u.name LIKE ? OR b.queue_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter !== '') {
    $query .= " AND b.status = ?";
    $params[] = $status_filter;
}

if ($date_filter !== '') {
    $query .= " AND b.booking_date = ?";
    $params[] = $date_filter;
} else {
    // Default show today or future bookings, or ordered by date
}

$query .= " ORDER BY b.booking_date DESC, b.booking_time ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data booking: " . $e->getMessage());
}
?>

<!-- Message displays -->
<?php if (isset($success_msg)): ?>
    <div style="background-color: #effbf4; color: #4caf50; padding: 12px 20px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
    </div>
<?php endif; ?>
<?php if (isset($error_msg)): ?>
    <div style="background-color: #ffebee; color: #f44336; padding: 12px 20px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
    </div>
<?php endif; ?>

<!-- Search, Date and Status filters -->
<div class="actions-row">
  <form action="bookings.php" method="GET" class="search-filter-box">
    <input type="text" name="search" placeholder="Cari nama / antrian..." value="<?php echo htmlspecialchars($search); ?>">
    
    <select name="status">
      <option value="">Semua Status</option>
      <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Menunggu (Pending)</option>
      <option value="serving" <?php echo ($status_filter == 'serving') ? 'selected' : ''; ?>>Sedang Dilayani</option>
      <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Selesai (Completed)</option>
      <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Batal (Cancelled)</option>
    </select>

    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">

    <button type="submit" class="btn-admin-primary" style="padding: 8px 16px;"><i class="fa-solid fa-filter"></i> Filter</button>
    <?php if ($search !== '' || $status_filter !== '' || $date_filter !== ''): ?>
        <a href="bookings.php" class="btn-admin-secondary" style="padding: 8px 16px; text-decoration: none; font-size: 0.85rem;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Table Card -->
<div class="admin-table-card">
  <?php if (empty($bookings)): ?>
    <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
      <i class="fa-regular fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
      <p>Tidak ada data booking ditemukan yang cocok dengan kriteria filter.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>No. Antrian</th>
          <th>Nama Pelanggan</th>
          <th>No. HP</th>
          <th>Layanan</th>
          <th>Karyawan</th>
          <th>Jadwal Booking</th>
          <th>Total Harga</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
          <tr>
            <td>
              <?php if (!empty($b['queue_number'])): ?>
                <strong style="color: var(--primary-color); font-size: 1.1rem;"><?php echo htmlspecialchars($b['queue_number']); ?></strong>
              <?php else: ?>
                <span class="status-badge" style="background:#fff9e6;color:#e65100;font-size:0.75rem;padding:4px 8px;border-radius:6px;font-weight:600;display:inline-block;white-space:nowrap;">BELUM AKTIF</span>
              <?php endif; ?>
            </td>
            <td><strong><?php echo htmlspecialchars($b['user_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($b['user_phone']); ?></td>
            <td><?php echo htmlspecialchars($b['service_name']); ?></td>
            <td><?php echo htmlspecialchars($b['employee_name']); ?></td>
            <td>
              <div><i class="fa-regular fa-calendar" style="color: var(--primary-color);"></i> <?php echo date('d M Y', strtotime($b['booking_date'])); ?></div>
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;"><i class="fa-regular fa-clock"></i> <?php echo substr($b['booking_time'], 0, 5); ?> WIB</div>
            </td>
            <td><strong>Rp <?php echo number_format($b['price'], 0, ',', '.'); ?></strong></td>
            <td>
              <span class="status-badge <?php echo $b['status']; ?>">
                <?php 
                  if ($b['status'] === 'pending') echo 'Menunggu';
                  elseif ($b['status'] === 'serving') echo 'Dilayani';
                  elseif ($b['status'] === 'completed') echo 'Selesai';
                  elseif ($b['status'] === 'cancelled') echo 'Batal';
                ?>
              </span>
            </td>
            <td>
              <div style="display: flex; gap: 8px; align-items: center;">
                <!-- Serve/Dilayani Action -->
                <?php if ($b['status'] === 'pending'): ?>
                  <form action="bookings.php" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                    <input type="hidden" name="status" value="serving">
                    <button type="submit" class="btn-action-icon status" title="Mulai Layani" style="background-color: #eef7ff; color: #2196f3;"><i class="fa-solid fa-play"></i></button>
                  </form>
                <?php endif; ?>

                <!-- Complete Action -->
                <?php if ($b['status'] === 'serving'): ?>
                  <form action="bookings.php" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-action-icon status" title="Selesaikan" style="background-color: #effbf4; color: #4caf50;"><i class="fa-solid fa-check"></i></button>
                  </form>
                <?php endif; ?>

                <!-- Cancel Action -->
                <?php if ($b['status'] === 'pending' || $b['status'] === 'serving'): ?>
                  <form action="bookings.php" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-action-icon edit" title="Batalkan Booking" style="background-color: #ffebee; color: #f44336;"><i class="fa-solid fa-ban"></i></button>
                  </form>
                <?php endif; ?>

                <!-- Delete Action -->
                <form action="bookings.php" method="POST" style="display: inline;" onsubmit="return confirm('Hapus permanen booking ini? Tindakan ini tidak bisa dibatalkan.')">
                  <input type="hidden" name="action" value="delete_booking">
                  <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                  <button type="submit" class="btn-action-icon delete" title="Hapus Permanen"><i class="fa-solid fa-trash-can"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
include_once 'includes/footer.php';
?>
