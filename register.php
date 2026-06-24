<?php
$page_title = "Daftar Akun";
include_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if (empty($name) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error_msg = "Semua kolom wajib diisi!";
    } elseif (!preg_match("/^[0-9]{9,15}$/", $phone)) {
        $error_msg = "Format nomor HP tidak valid (hanya angka, 9-15 digit)!";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password minimal harus 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Konfirmasi password tidak cocok!";
    } else {
        try {
            // Check if phone number already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error_msg = "Nomor HP sudah terdaftar! Silakan login.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, 'customer')");
                $stmt->execute([$name, $phone, $hashed_password]);
                
                $success_msg = "Pendaftaran berhasil! Mengalihkan ke halaman login...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card scroll-animate zoom-in">
        <div class="auth-header">
            <h2>Daftar Akun Baru</h2>
            <p>Bergabunglah dengan Beauty Sari Salon untuk menikmati kemudahan booking perawatan Anda</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="name"><i class="fa fa-user"></i> Nama Lengkap</label>
                <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap Anda" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fa fa-phone"></i> Nomor HP (WhatsApp)</label>
                <input type="tel" id="phone" name="phone" placeholder="Contoh: 081284477303" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fa fa-lock"></i> Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
            </div>

            <button type="submit" class="btn-auth">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
