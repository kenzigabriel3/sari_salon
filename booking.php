<?php
$page_title = "Booking Layanan";
include_once 'includes/db.php';

// Require login for booking
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=booking.php" . (isset($_GET['service_id']) ? "?service_id=" . urlencode($_GET['service_id']) : ""));
    exit();
}

$error_msg = "";
$success_booking = null;

// Fetch all services and employees for the booking form
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT * FROM employees ORDER BY id ASC");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal memuat data formulir: " . $e->getMessage();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_booking') {
    $user_id      = $_SESSION['user_id'];
    $service_id   = intval($_POST['service_id']);
    $employee_id  = intval($_POST['employee_id']);
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';

    if (empty($service_id) || empty($employee_id) || empty($booking_date) || empty($booking_time)) {
        $error_msg = "Semua data booking wajib diisi!";
    } elseif (!in_array($payment_method, ['cash','transfer'])) {
        $error_msg = "Pilih metode pembayaran terlebih dahulu!";
    } else {
        $day_of_week  = date('N', strtotime($booking_date));
        $booking_hour = intval(substr($booking_time, 0, 2));
        $booking_min  = intval(substr($booking_time, 3, 2));

        if ($day_of_week == 6) {
            $error_msg = "Maaf, salon kami libur pada hari Sabtu. Silakan pilih hari lain.";
        } elseif ($booking_hour < 9 || $booking_hour > 19 || ($booking_hour == 19 && $booking_min > 30)) {
            $error_msg = "Jam operasional kami adalah 09:00 - 20:00 WIB (Last order 19:30).";
        } elseif ($booking_date < date('Y-m-d')) {
            $error_msg = "Tanggal booking tidak boleh di masa lalu!";
        } else {
            // Handle proof upload for transfer
            $payment_proof = null;
            if ($payment_method === 'transfer') {
                if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                        $dir = 'assets/uploads/payments/';
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        $fname = uniqid('pay_', true) . '.' . $ext;
                        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dir . $fname)) {
                            $payment_proof = $dir . $fname;
                        }
                    } else {
                        $error_msg = "Format bukti transfer tidak valid! Gunakan JPG/PNG/WEBP.";
                    }
                } else {
                    $error_msg = "Bukti transfer wajib diunggah untuk pembayaran via transfer!";
                }
            }

            if (empty($error_msg)) {
                try {
                    $pdo->beginTransaction();

                    $pay_status    = ($payment_method === 'cash') ? 'pending_cash' : 'pending_transfer';

                    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, employee_id, booking_date, booking_time, queue_number, status, payment_method, payment_status, payment_proof) VALUES (?, ?, ?, ?, ?, NULL, 'pending', ?, ?, ?)");
                    $stmt->execute([$user_id, $service_id, $employee_id, $booking_date, $booking_time, $payment_method, $pay_status, $payment_proof]);
                    $booking_id = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("SELECT b.*, s.name as service_name, s.price, s.duration, e.name as employee_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN employees e ON b.employee_id = e.id WHERE b.id = ?");
                    $stmt->execute([$booking_id]);
                    $success_booking = $stmt->fetch();

                    $pdo->commit();
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error_msg = "Gagal membuat booking: " . $e->getMessage();
                }
            }
        }
    }
}

// Check for preselected service
$preselected_service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

include 'includes/header.php';
?>

<!-- Toast System -->
<div class="toast-container" id="toastContainer"></div>
<script>
function showToast(msg, type='success', dur=4000){
  const c=document.getElementById('toastContainer');
  const icons={success:'fa-circle-check',error:'fa-circle-xmark',warning:'fa-triangle-exclamation',info:'fa-circle-info'};
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<i class="fa-solid ${icons[type]||icons.info} toast-icon"></i><span class="toast-msg">${msg}</span><button class="toast-close" onclick="dismissToast(this.parentElement)">×</button>`;
  c.appendChild(t);
  const timer=setTimeout(()=>dismissToast(t),dur);
  t._timer=timer;
}
function dismissToast(t){
  if(!t||!t.parentElement)return;
  clearTimeout(t._timer);
  t.style.animation='toastSlideOut 0.3s ease forwards';
  setTimeout(()=>t.remove(),300);
}
</script>

<div class="booking-wizard-container">
    
    <!-- Wizard Progress Bar -->
    <div class="wizard-steps-bar">
        <div class="step-indicator active" id="step-ind-1">
            <span class="step-num">1</span>
            <span class="step-label">Pilih Layanan</span>
        </div>
        <div class="step-line" id="line-1"></div>
        <div class="step-indicator" id="step-ind-2">
            <span class="step-num">2</span>
            <span class="step-label">Pilih Karyawan</span>
        </div>
        <div class="step-line" id="line-2"></div>
        <div class="step-indicator" id="step-ind-3">
            <span class="step-num">3</span>
            <span class="step-label">Pilih Waktu</span>
        </div>
        <div class="step-line" id="line-3"></div>
        <div class="step-indicator" id="step-ind-4">
            <span class="step-num">4</span>
            <span class="step-label">Konfirmasi</span>
        </div>
        <div class="step-line" id="line-4"></div>
        <div class="step-indicator" id="step-ind-5">
            <span class="step-num">5</span>
            <span class="step-label">Pembayaran</span>
        </div>
    </div>

    <!-- Error Display -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger" style="max-width: 600px; margin: 20px auto 0;">
            <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <!-- Booking Form Container -->
    <?php if ($success_booking): ?>
        
        <!-- SUCCESS STEP -->
        <div class="booking-success-card scroll-animate zoom-in">
            <div class="success-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <h2>Pemesanan Berhasil!</h2>
            <p>Terima kasih, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>.<br>Booking Anda telah terdaftar.</p>
            
            <div class="success-details-wrapper">
                <div class="queue-display-box">
                    <span class="queue-title">Nomor Antrian Anda</span>
                    <span class="queue-num-big" style="<?php echo empty($success_booking['queue_number']) ? 'font-size:1.6rem; letter-spacing:0; padding:10px 5px;' : ''; ?>">
                        <?php echo empty($success_booking['queue_number']) ? 'Menunggu Persetujuan' : htmlspecialchars($success_booking['queue_number']); ?>
                    </span>
                    <span class="queue-note"><?php echo empty($success_booking['queue_number']) ? 'Nomor antrian akan diterbitkan setelah dikonfirmasi admin.' : 'Tunjukkan nomor ini ke resepsionis saat datang'; ?></span>
                </div>

                <div class="booking-summary-list">
                    <h3>Detail Pemesanan</h3>
                    <div class="summary-item">
                        <span class="item-label">Layanan</span>
                        <span class="item-value"><?php echo htmlspecialchars($success_booking['service_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="item-label">Karyawan</span>
                        <span class="item-value"><?php echo htmlspecialchars($success_booking['employee_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="item-label">Tanggal</span>
                        <span class="item-value"><?php echo date('d F Y', strtotime($success_booking['booking_date'])); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="item-label">Waktu</span>
                        <span class="item-value"><?php echo substr($success_booking['booking_time'], 0, 5); ?> WIB</span>
                    </div>
                    <div class="summary-item">
                        <span class="item-label">Harga</span>
                        <span class="item-value">Rp <?php echo number_format($success_booking['price'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="item-label">Pembayaran</span>
                        <span class="item-value"><?php echo ucfirst($success_booking['payment_method']); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($success_booking['payment_status'] === 'pending_transfer'): ?>
            <div class="payment-transfer-notice">
                ⏳ <strong>Pembayaran Transfer sedang diverifikasi admin.</strong><br>
                Antrian akan aktif setelah admin mengkonfirmasi pembayaran Anda. Pantau status di halaman Antrian Saya.
            </div>
            <?php elseif ($success_booking['payment_status'] === 'pending_cash'): ?>
            <div class="payment-cash-notice">
                💵 <strong>Bayar Cash di Kasir Salon saat datang.</strong><br>
                Booking Anda telah diajukan. Nomor antrian akan aktif setelah disetujui oleh admin salon. Pantau status di halaman Antrian Saya.
            </div>
            <?php endif; ?>

            <div class="success-actions">
                <a href="antrian.php" class="btn-success-action view-queue">Lihat Antrian Saya</a>
                <a href="index.php" class="btn-success-action go-home">Kembali ke Beranda</a>
            </div>
        </div>

    <?php else: ?>
        
        <!-- WIZARD FORM -->
        <form id="bookingWizardForm" action="booking.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_booking">
            <input type="hidden" name="service_id" id="input_service_id" value="<?php echo $preselected_service_id; ?>">
            <input type="hidden" name="employee_id" id="input_employee_id" value="">
            <input type="hidden" name="booking_date" id="input_booking_date" value="">
            <input type="hidden" name="booking_time" id="input_booking_time" value="">

            <!-- STEP 1: PILIH LAYANAN -->
            <div class="wizard-step" id="step-1" style="display: block;">
                <div class="step-header">
                    <h2>Pilih Layanan</h2>
                    <p>Pilih layanan treatment yang ingin Anda lakukan</p>
                </div>
                
                <div class="booking-services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="booking-service-card <?php echo ($preselected_service_id == $service['id']) ? 'selected' : ''; ?>" 
                             data-id="<?php echo $service['id']; ?>"
                             data-name="<?php echo htmlspecialchars($service['name']); ?>"
                             data-price="Rp <?php echo number_format($service['price'], 0, ',', '.'); ?>"
                             data-duration="± <?php echo $service['duration']; ?> menit">
                            
                            <div class="select-badge"><i class="fa fa-check"></i></div>
                            <img src="<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <span class="service-meta-info"><i class="fa fa-clock"></i> <?php echo $service['duration']; ?> menit</span>
                            <span class="service-price-info">Rp <?php echo number_format($service['price'], 0, ',', '.'); ?></span>
                            
                            <button type="button" class="btn-select-wizard">Pilih</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="wizard-buttons right-only">
                    <button type="button" class="btn-next" onclick="nextStep(2)" id="btn-next-1" <?php echo ($preselected_service_id > 0) ? '' : 'disabled'; ?>>Selanjutnya</button>
                </div>
            </div>

            <!-- STEP 2: PILIH KARYAWAN -->
            <div class="wizard-step" id="step-2" style="display: none;">
                <div class="step-header">
                    <h2>Pilih Karyawan</h2>
                    <p>Pilih karyawan yang akan menangani treatment Anda</p>
                </div>

                <div class="booking-employees-grid">
                    <?php foreach ($employees as $employee): ?>
                        <div class="booking-employee-card" 
                             data-id="<?php echo $employee['id']; ?>"
                             data-name="<?php echo htmlspecialchars($employee['name']); ?>">
                            
                            <div class="select-badge"><i class="fa fa-check"></i></div>
                            <img src="<?php echo htmlspecialchars($employee['image_path']); ?>" alt="<?php echo htmlspecialchars($employee['name']); ?>">
                            <h3><?php echo htmlspecialchars($employee['name']); ?></h3>
                            <p class="emp-specialty"><?php echo htmlspecialchars($employee['specialty']); ?></p>
                            <p class="emp-exp"><?php echo htmlspecialchars($employee['experience']); ?></p>
                            <div class="emp-rating">
                                <i class="fa fa-star"></i> <span><?php echo number_format($employee['rating'], 1); ?></span>
                            </div>
                            
                            <button type="button" class="btn-select-wizard">Pilih</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="wizard-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(1)">Kembali</button>
                    <button type="button" class="btn-next" onclick="nextStep(3)" id="btn-next-2" disabled>Selanjutnya</button>
                </div>
            </div>

            <!-- STEP 3: PILIH WAKTU -->
            <div class="wizard-step" id="step-3" style="display: none;">
                <div class="step-header">
                    <h2>Pilih Waktu</h2>
                    <p>Tentukan tanggal dan jam kedatangan Anda</p>
                </div>

                <div class="time-selection-container">
                    <div class="date-picker-section">
                        <label for="date-select"><i class="fa fa-calendar-alt"></i> Pilih Tanggal</label>
                        <input type="date" id="date-select" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="time-picker-section" style="margin-top: 30px;">
                        <label><i class="fa fa-clock"></i> Pilih Jam Kedatangan</label>
                        <div class="time-slots-grid">
                            <?php 
                            $start_time = strtotime('09:00');
                            $end_time = strtotime('19:30');
                            while ($start_time <= $end_time) {
                                $time_str = date('H:i', $start_time);
                                echo "<div class='time-slot' data-time='$time_str'>$time_str</div>";
                                $start_time = strtotime('+30 minutes', $start_time);
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="wizard-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(2)">Kembali</button>
                    <button type="button" class="btn-next" onclick="nextStep(4)" id="btn-next-3" disabled>Selanjutnya</button>
                </div>
            </div>

            <!-- STEP 4: KONFIRMASI -->
            <div class="wizard-step" id="step-4" style="display: none;">
                <div class="step-header">
                    <h2>Konfirmasi Booking</h2>
                    <p>Harap periksa kembali detail pesanan Anda sebelum mengonfirmasi</p>
                </div>

                <div class="booking-confirmation-wrapper">
                    <div class="confirm-summary-card">
                        <h3>Detail Pemesanan</h3>
                        
                        <div class="summary-item">
                            <span class="item-label">Layanan</span>
                            <span class="item-value" id="confirm-service-name">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="item-label">Durasi</span>
                            <span class="item-value" id="confirm-service-duration">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="item-label">Karyawan</span>
                            <span class="item-value" id="confirm-employee-name">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="item-label">Tanggal</span>
                            <span class="item-value" id="confirm-date">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="item-label">Waktu</span>
                            <span class="item-value" id="confirm-time">-</span>
                        </div>
                        <div class="summary-item divider"></div>
                        <div class="summary-item total">
                            <span class="item-label">Total Harga</span>
                            <span class="item-value" id="confirm-service-price">-</span>
                        </div>
                    </div>

                    <div class="confirm-notice">
                        <i class="fa fa-info-circle"></i>
                        <p>Setelah melakukan konfirmasi, nomor antrian akan dibuat secara otomatis. Harap datang 10 menit sebelum jam kedatangan untuk proses registrasi di salon.</p>
                    </div>
                </div>

                <div class="wizard-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(3)">Kembali</button>
                    <button type="button" class="btn-next" onclick="goToPayStep()" id="btn-next-4">Lanjut ke Pembayaran &rarr;</button>
                </div>
            </div>

            <!-- STEP 5: PEMBAYARAN -->
            <div class="wizard-step" id="step-5" style="display: none;">
                <div class="step-header">
                    <h2>Pilih Metode Pembayaran</h2>
                    <p>Pilih cara pembayaran yang paling mudah untuk Anda</p>
                </div>
                <div style="max-width:560px;margin:0 auto;">
                    <div style="background:linear-gradient(135deg,#fff0f5,#fce4ec);border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.9rem;color:#8a99a8;font-weight:500;">Total Pembayaran</span>
                        <span id="pay-total" style="font-size:1.4rem;font-weight:700;color:#ff4081;">-</span>
                    </div>
                    <input type="hidden" name="payment_method" id="input_payment_method" value="">
                    <div class="payment-method-grid">
                        <div class="payment-method-card" id="card-cash" onclick="selectPayment('cash')">
                            <span class="pay-icon">&#128181;</span>
                            <h4>Cash di Tempat</h4>
                            <p>Bayar langsung di kasir salon saat datang</p>
                        </div>
                        <div class="payment-method-card" id="card-transfer" onclick="selectPayment('transfer')">
                            <span class="pay-icon">&#127970;</span>
                            <h4>Transfer Bank</h4>
                            <p>Transfer ke rekening salon &amp; upload bukti</p>
                        </div>
                    </div>
                    <div class="bank-info-card" id="bankInfoCard">
                        <h4><i class="fa-solid fa-building-columns"></i> Info Rekening Salon</h4>
                        <div class="bank-row"><span class="bank-label">Bank</span><span class="bank-value">BCA</span></div>
                        <div class="bank-row"><span class="bank-label">No. Rekening</span><span class="bank-value" id="noRek">5875459845</span><button type="button" class="copy-btn" onclick="copyRek()">Salin</button></div>
                        <div class="bank-row"><span class="bank-label">Atas Nama</span><span class="bank-value">SARI</span></div>
                        <div class="bank-row"><span class="bank-label">Jumlah Transfer</span><span class="bank-value" id="payAmount" style="color:#ff4081;">-</span></div>
                    </div>
                    <div class="proof-upload-area" id="proofUploadArea">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Unggah bukti transfer Anda (JPG / PNG / WEBP)</p>
                        <label class="upload-label" for="proofFileInput"><i class="fa-solid fa-image"></i> Pilih File / Foto</label>
                        <input type="file" id="proofFileInput" name="payment_proof" accept="image/*" onchange="previewProof(this)">
                        <img id="proofPreview" class="proof-preview-img">
                    </div>
                    <div class="cash-info-card" id="cashInfoCard">
                        <h4><i class="fa-solid fa-circle-info"></i> Informasi Pembayaran Cash</h4>
                        <p>&#9989; Booking Anda akan diajukan ke admin salon.<br>&#128181; Siapkan pembayaran Cash di kasir saat datang.<br>&#128203; Nomor antrian akan terbit setelah disetujui oleh admin.</p>
                    </div>
                </div>
                <div class="wizard-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(4)">Kembali</button>
                    <button type="submit" class="btn-submit-booking" id="btn-submit-pay" disabled>&#10003; Konfirmasi &amp; Booking</button>
                </div>
            </div>

        </form>

    <?php endif; ?>

</div>
<!-- Booking Wizard Script -->
<script>
    let currentStep = 1;

    // ─── Element References ───
    const serviceCards     = document.querySelectorAll('.booking-service-card');
    const employeeCards    = document.querySelectorAll('.booking-employee-card');
    const timeSlots        = document.querySelectorAll('.time-slot');
    const dateInput        = document.getElementById('date-select');
    const inputServiceId   = document.getElementById('input_service_id');
    const inputEmployeeId  = document.getElementById('input_employee_id');
    const inputBookingDate = document.getElementById('input_booking_date');
    const inputBookingTime = document.getElementById('input_booking_time');
    const btnNext1         = document.getElementById('btn-next-1');
    const btnNext2         = document.getElementById('btn-next-2');
    const btnNext3         = document.getElementById('btn-next-3');
    const btnNext4         = document.getElementById('btn-next-4');

    // ─── Step 1: Service Selection ───
    serviceCards.forEach(card => {
        card.addEventListener('click', () => {
            serviceCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            inputServiceId.value = card.getAttribute('data-id');
            btnNext1.disabled = false;
            document.getElementById('confirm-service-name').textContent     = card.getAttribute('data-name');
            document.getElementById('confirm-service-price').textContent    = card.getAttribute('data-price');
            document.getElementById('confirm-service-duration').textContent = card.getAttribute('data-duration');
        });
    });

    // ─── Step 2: Employee Selection ───
    employeeCards.forEach(card => {
        card.addEventListener('click', () => {
            employeeCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            inputEmployeeId.value = card.getAttribute('data-id');
            btnNext2.disabled = false;
            document.getElementById('confirm-employee-name').textContent = card.getAttribute('data-name');
            // Re-run slot availability check if date was already picked
            if (inputBookingDate.value) refreshSlotAvailability();
        });
    });

    // ─── Step 3 Helpers ───
    function getTodayLocal() {
        const n = new Date();
        return `${n.getFullYear()}-${String(n.getMonth()+1).padStart(2,'0')}-${String(n.getDate()).padStart(2,'0')}`;
    }
    function getNowTimeStr() {
        const n = new Date();
        return `${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}`;
    }

    function resetSlots() {
        timeSlots.forEach(s => { s.classList.remove('blocked','selected'); s.removeAttribute('title'); });
        inputBookingTime.value = '';
        btnNext3.disabled = true;
    }

    function blockPastSlots(date) {
        if (date !== getTodayLocal()) return;
        const now = getNowTimeStr();
        timeSlots.forEach(slot => {
            if (slot.getAttribute('data-time') <= now) {
                slot.classList.add('blocked');
                slot.title = 'Jam sudah lewat';
            }
        });
    }

    function blockBookedSlots(empId, date) {
        if (!empId || !date) return;
        fetch(`api/get_booked_slots.php?employee_id=${empId}&date=${date}`)
            .then(r => r.json())
            .then(data => {
                (data.booked_slots || []).forEach(bt => {
                    timeSlots.forEach(slot => {
                        if (slot.getAttribute('data-time') === bt) {
                            slot.classList.add('blocked');
                            slot.title = 'Jam ini sudah dipesan oleh customer lain';
                        }
                    });
                });
            })
            .catch(() => {});
    }

    function refreshSlotAvailability() {
        resetSlots();
        const date  = inputBookingDate.value;
        const empId = inputEmployeeId.value;
        if (!date) return;
        blockPastSlots(date);
        blockBookedSlots(empId, date);
    }

    // ─── Date Picker ───
    dateInput.addEventListener('input', e => {
        const v = e.target.value;
        if (!v) { inputBookingDate.value = ''; resetSlots(); return; }

        const day = new Date(v + 'T00:00').getDay();
        if (day === 6) {
            alert('Maaf, Beauty Sari Salon tutup setiap hari Sabtu. Silakan pilih hari lain.');
            dateInput.value = ''; inputBookingDate.value = ''; resetSlots(); return;
        }
        if (v < getTodayLocal()) {
            alert('Tanggal booking tidak boleh di masa lalu!');
            dateInput.value = ''; inputBookingDate.value = ''; resetSlots(); return;
        }

        inputBookingDate.value = v;
        const fmt = new Date(v + 'T00:00').toLocaleDateString('id-ID', {year:'numeric',month:'long',day:'numeric'});
        document.getElementById('confirm-date').textContent = fmt;
        refreshSlotAvailability();
    });

    // ─── Time Slot Click ───
    timeSlots.forEach(slot => {
        slot.addEventListener('click', () => {
            if (slot.classList.contains('blocked')) return;
            timeSlots.forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            const t = slot.getAttribute('data-time');
            inputBookingTime.value = t;
            document.getElementById('confirm-time').textContent = t + ' WIB';
            btnNext3.disabled = false;
        });
    });

    // ─── Navigation ───
    function nextStep(step) {
        if (step === 2 && !inputServiceId.value)  return;
        if (step === 3 && !inputEmployeeId.value) return;
        if (step === 4 && (!inputBookingDate.value || !inputBookingTime.value)) return;

        document.getElementById('step-' + currentStep).style.display = 'none';
        document.getElementById('step-' + step).style.display        = 'block';
        document.getElementById('step-ind-' + step).classList.add('active');
        document.getElementById('line-' + currentStep).classList.add('active');
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prevStep(step) {
        document.getElementById('step-' + currentStep).style.display = 'none';
        document.getElementById('step-' + step).style.display        = 'block';
        document.getElementById('step-ind-' + currentStep).classList.remove('active');
        document.getElementById('line-' + step).classList.remove('active');
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ─── Init: pre-selected service from URL ?service_id= ───
    window.addEventListener('DOMContentLoaded', () => {
        const preId = inputServiceId.value;
        if (preId && preId !== '0') {
            const card = document.querySelector('.booking-service-card[data-id="' + preId + '"]');
            if (card) {
                card.classList.add('selected');
                document.getElementById('confirm-service-name').textContent     = card.getAttribute('data-name');
                document.getElementById('confirm-service-price').textContent    = card.getAttribute('data-price');
                document.getElementById('confirm-service-duration').textContent = card.getAttribute('data-duration');
                btnNext1.disabled = false;
            }
        }
    });

    // === Payment Step Functions ===
    let selectedPayTotal = '';

    function goToPayStep() {
        if (!inputBookingDate.value || !inputBookingTime.value) {
            showToast('Harap pilih tanggal dan jam terlebih dahulu!', 'error');
            return;
        }
        // Sync total to pay step
        const priceText = document.getElementById('confirm-service-price').textContent;
        document.getElementById('pay-total').textContent = priceText;
        document.getElementById('payAmount').textContent = priceText;
        selectedPayTotal = priceText;
        // Navigate to step 5
        document.getElementById('step-' + currentStep).style.display = 'none';
        document.getElementById('step-5').style.display = 'block';
        document.getElementById('step-ind-5').classList.add('active');
        document.getElementById('line-4').classList.add('active');
        currentStep = 5;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function selectPayment(method) {
        document.getElementById('input_payment_method').value = method;
        // Update card visuals
        document.getElementById('card-cash').classList.remove('selected');
        document.getElementById('card-transfer').classList.remove('selected');
        document.getElementById('card-' + method).classList.add('selected');
        // Show/hide panels
        document.getElementById('bankInfoCard').classList.toggle('show', method === 'transfer');
        document.getElementById('proofUploadArea').classList.toggle('show', method === 'transfer');
        document.getElementById('cashInfoCard').classList.toggle('show', method === 'cash');
        // Enable submit
        const btn = document.getElementById('btn-submit-pay');
        if (method === 'cash') {
            btn.disabled = false;
        } else {
            // For transfer, require proof file
            const fileInput = document.getElementById('proofFileInput');
            btn.disabled = !fileInput.files.length;
        }
        showToast(method === 'cash' ? 'Metode Cash dipilih. Bayar saat datang ke salon.' : 'Metode Transfer dipilih. Upload bukti transfer Anda.', 'info', 3000);
    }

    function previewProof(input) {
        const preview = document.getElementById('proofPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
            document.getElementById('btn-submit-pay').disabled = false;
            showToast('Bukti transfer berhasil dipilih!', 'success');
        }
    }

    function copyRek() {
        const rek = document.getElementById('noRek').textContent;
        navigator.clipboard.writeText(rek).then(() => {
            showToast('Nomor rekening ' + rek + ' berhasil disalin!', 'success');
        }).catch(() => {
            showToast('Gagal menyalin. Silakan salin manual: ' + rek, 'warning');
        });
    }

</script>

<?php include 'includes/footer.php'; ?>
