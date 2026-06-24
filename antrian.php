<?php
$page_title = "Antrian Saya";
include_once 'includes/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=antrian.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// Handle Booking Cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel_booking') {
    $booking_id = intval($_POST['booking_id']);
    
    try {
        // Validate that this booking belongs to the logged-in user and is still pending
        $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if ($booking && $booking['status'] == 'pending') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking_id]);
            $msg = "Antrean Anda berhasil dibatalkan.";
            $msg_type = "success";
        } else {
            $msg = "Antrean tidak dapat dibatalkan atau tidak ditemukan.";
            $msg_type = "danger";
        }
    } catch (PDOException $e) {
        $msg = "Terjadi kesalahan: " . $e->getMessage();
        $msg_type = "danger";
    }
}

// Fetch Active Bookings (pending, serving, pending_cash, pending_transfer)
try {
    $stmt = $pdo->prepare("
        SELECT b.*, s.name as service_name, s.price, s.duration, e.name as employee_name, e.image_path as employee_image 
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN employees e ON b.employee_id = e.id
        WHERE b.user_id = ? AND b.status IN ('pending','serving','pending_cash','pending_transfer')
        ORDER BY b.booking_date ASC, b.booking_time ASC
    ");
    $stmt->execute([$user_id]);
    $active_bookings = $stmt->fetchAll();

    // Fetch Booking History (completed and cancelled only)
    $stmt = $pdo->prepare("
        SELECT b.*, s.name as service_name, s.price, e.name as employee_name 
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN employees e ON b.employee_id = e.id
        WHERE b.user_id = ? AND b.status IN ('completed','cancelled')
        ORDER BY b.booking_date DESC, b.booking_time DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $history_bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $msg = "Gagal mengambil data antrean: " . $e->getMessage();
    $msg_type = "danger";
    $active_bookings = [];
    $history_bookings = [];
}

// Function to calculate waiting time estimation
function getEstimatedWaitTime($pdo, $booking) {
    try {
        // Count bookings for the same date, same employee, which are pending and placed before this booking
        // We use booking_time to order them
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_ahead 
            FROM bookings 
            WHERE booking_date = ? 
              AND employee_id = ? 
              AND status = 'pending' 
              AND booking_time < ?
        ");
        $stmt->execute([
            $booking['booking_date'], 
            $booking['employee_id'], 
            $booking['booking_time']
        ]);
        $total_ahead = $stmt->fetch()['total_ahead'];
        
        return $total_ahead * 20; // 20 minutes estimated wait time per customer ahead
    } catch (PDOException $e) {
        return 0;
    }
}

// Function to count customers ahead
function getCustomersAheadCount($pdo, $booking) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_ahead 
            FROM bookings 
            WHERE booking_date = ? 
              AND employee_id = ? 
              AND status = 'pending' 
              AND booking_time < ?
        ");
        $stmt->execute([
            $booking['booking_date'], 
            $booking['employee_id'], 
            $booking['booking_time']
        ]);
        return $stmt->fetch()['total_ahead'];
    } catch (PDOException $e) {
        return 0;
    }
}

include 'includes/header.php';
?>

<div class="my-queue-container">
    
    <div class="queue-page-header scroll-animate fade-down">
        <h2>Antrian Perawatan Saya</h2>
        <p>Pantau nomor antrean Anda secara real-time</p>
    </div>

    <!-- Feedback Message -->
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?> scroll-animate fade-up" style="max-width: 600px; margin: 0 auto 30px;">
            <i class="fa <?php echo ($msg_type == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i> 
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <!-- Active Queues Section -->
    <div class="active-queues-section">
        <?php if (count($active_bookings) > 0): ?>
            <div class="queues-grid">
                <?php foreach ($active_bookings as $booking): 
                    $wait_time = getEstimatedWaitTime($pdo, $booking);
                    $ahead_count = getCustomersAheadCount($pdo, $booking);
                ?>
                    <div class="queue-card scroll-animate zoom-in">
                        
                         <!-- Queue Number Badge -->
                        <div class="queue-card-number-box">
                            <span class="q-label">Nomor Antrian Anda</span>
                            <?php if (!empty($booking['queue_number'])): ?>
                                <span class="q-number"><?php echo htmlspecialchars($booking['queue_number']); ?></span>
                            <?php else: ?>
                                <span class="q-number" style="font-size:1.4rem;letter-spacing:0;padding:10px 0;">Menunggu Persetujuan</span>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] === 'serving'): ?>
                                <span class="q-status-badge active-serving" style="background:linear-gradient(135deg,#4caf50,#388e3c);animation:pulse 1.5s infinite;">🟢 Sedang Dilayani</span>
                            <?php elseif ($booking['payment_status'] === 'pending_transfer'): ?>
                                <span class="q-status-badge waiting" style="background:linear-gradient(135deg,#ff9800,#f57c00);">⏳ Menunggu Konfirmasi Pembayaran Transfer</span>
                            <?php elseif ($booking['payment_status'] === 'pending_cash'): ?>
                                <span class="q-status-badge waiting" style="background:linear-gradient(135deg,#2196f3,#1565c0);">⏳ Menunggu Persetujuan Admin (Cash)</span>
                            <?php elseif ($ahead_count == 0): ?>
                                <span class="q-status-badge active-serving">✅ Menunggu Giliran / Segera Dipanggil</span>
                            <?php else: ?>
                                <span class="q-status-badge waiting">Menunggu giliran (<?php echo $ahead_count; ?> orang di depan Anda)</span>
                            <?php endif; ?>
                        </div>
 
                        <!-- Waiting Time Estimate -->
                        <div class="queue-timer-box">
                            <i class="fa fa-hourglass-half"></i>
                            <div class="timer-text">
                                <?php if (empty($booking['queue_number'])): ?>
                                    <span class="time-est">TBD</span>
                                    <span class="time-label">Menunggu Persetujuan Admin</span>
                                <?php elseif ($wait_time == 0): ?>
                                    <span class="time-est">± 5 menit lagi</span>
                                    <span class="time-label">Silakan bersiap-siap</span>
                                <?php else: ?>
                                    <span class="time-est">± <?php echo $wait_time; ?> menit lagi</span>
                                    <span class="time-label">Estimasi dilayani</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="queue-details-box">
                            <h3>Detail Pemesanan</h3>
                            <div class="detail-row">
                                <span class="d-label">Layanan</span>
                                <span class="d-val"><?php echo htmlspecialchars($booking['service_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="d-label">Karyawan</span>
                                <span class="d-val"><?php echo htmlspecialchars($booking['employee_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="d-label">Jadwal</span>
                                <span class="d-val">
                                    <?php echo date('d M Y', strtotime($booking['booking_date'])); ?> 
                                    - <?php echo substr($booking['booking_time'], 0, 5); ?> WIB
                                </span>
                            </div>
                            <div class="detail-row">
                                <span class="d-label">Durasi</span>
                                <span class="d-val">± <?php echo $booking['duration']; ?> menit</span>
                            </div>
                            <div class="detail-row">
                                <span class="d-label">Biaya</span>
                                <span class="d-val text-pink">Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></span>
                            </div>
                        </div>

                        <!-- Cancel Action -->
                        <form action="antrian.php" method="POST" class="cancel-queue-form" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan antrean booking ini?');">
                            <input type="hidden" name="action" value="cancel_booking">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <button type="submit" class="btn-cancel-queue">Batalkan Antrian</button>
                        </form>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-queue-box scroll-animate zoom-in">
                <div class="empty-icon"><i class="fa fa-calendar-times"></i></div>
                <h3>Belum Ada Antrian Aktif</h3>
                <p>Anda belum memesan perawatan apa pun untuk saat ini.</p>
                <a href="booking.php" class="btn-book-now-empty">Booking Sekarang</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Booking History Section -->
    <div class="booking-history-section scroll-animate fade-up" style="margin-top: 80px;">
        <h3 class="history-title"><i class="fa fa-history"></i> Riwayat Booking</h3>
        
        <?php if (count($history_bookings) > 0): ?>
            <div class="history-table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>No. Antrian</th>
                            <th>Layanan</th>
                            <th>Karyawan</th>
                            <th>Tanggal & Waktu</th>
                            <th>Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history_bookings as $hist): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hist['queue_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($hist['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($hist['employee_name']); ?></td>
                                <td>
                                    <?php echo date('d-m-Y', strtotime($hist['booking_date'])); ?> 
                                    | <?php echo substr($hist['booking_time'], 0, 5); ?> WIB
                                </td>
                                <td>Rp <?php echo number_format($hist['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($hist['status'] === 'completed'): ?>
                                        <span class="status-pill completed">✅ Selesai</span>
                                    <?php elseif ($hist['status'] === 'cancelled'): ?>
                                        <span class="status-pill cancelled">❌ Dibatalkan</span>
                                    <?php else: ?>
                                        <span class="status-pill" style="background:#e3f2fd;color:#1565c0;">ℹ️ <?php echo ucfirst($hist['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-history-text">Belum ada riwayat booking sebelumnya.</p>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
