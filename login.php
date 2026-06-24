<?php
$page_title = "Masuk Akun";
include_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
    $is_targeting_admin = (strpos($redirect, 'admin') !== false);
    
    if ($_SESSION['user_role'] === 'admin' || !$is_targeting_admin) {
        header("Location: index.php");
        exit();
    } else {
        // Logged in as customer, but trying to access admin panel.
        // Clear customer session so they can log in with admin account.
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        session_start();
        $error_msg = "Anda harus masuk dengan akun Administrator untuk mengakses halaman tersebut.";
    }
}

if (!isset($error_msg)) {
    $error_msg = "";
}
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if (empty($phone) || empty($password)) {
        $error_msg = "Nomor HP dan Password wajib diisi!";
    } else {
        try {
            // Find user by phone number
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['role'] === 'admin') {
                    $error_msg = "Akun Administrator hanya dapat masuk melalui halaman login Admin khusus.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_phone'] = $user['phone'];
                    $_SESSION['user_role'] = $user['role'];

                    $success_msg = "Login berhasil! Selamat datang kembali...";
                    
                    // Redirect based on redirect parameter or back to index
                    $redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '" . addslashes($redirect_to) . "';
                        }, 1500);
                    </script>";
                }
            } else {
                $error_msg = "Nomor HP atau password salah!";
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
            <h2>Masuk Akun</h2>
            <p>Silakan masuk untuk mengelola antrean dan booking layanan Anda</p>
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

        <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST" class="auth-form">
            <div class="form-group">
                <label for="phone"><i class="fa fa-phone"></i> Nomor HP (WhatsApp)</label>
                <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor HP Anda" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>

            <button type="submit" class="btn-auth">Masuk</button>
        </form>

        <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
