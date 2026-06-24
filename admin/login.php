<?php
include_once '../includes/db.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone    = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($phone) || empty($password)) {
        $error_msg = "Nomor HP dan Password wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? AND role = 'admin'");
            $stmt->execute([$phone]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Set SEPARATE admin session keys
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];

                header("Location: index.php");
                exit();
            } else {
                $error_msg = "Nomor HP atau password Admin salah!";
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — Beauty Sari Salon</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #fff0f5 0%, #fce4ec 40%, #f8bbd0 100%);
      position: relative;
      overflow: hidden;
    }

    /* Decorative blobs */
    body::before {
      content: '';
      position: fixed;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,64,129,0.15), transparent 70%);
      top: -150px; right: -150px;
      border-radius: 50%;
    }
    body::after {
      content: '';
      position: fixed;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(212,175,55,0.12), transparent 70%);
      bottom: -100px; left: -100px;
      border-radius: 50%;
    }

    .login-card {
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      padding: 48px 44px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 25px 60px rgba(255,64,129,0.15), 0 8px 20px rgba(0,0,0,0.08);
      position: relative;
      z-index: 10;
      animation: cardIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
    }

    @keyframes cardIn {
      from { opacity:0; transform: translateY(40px) scale(0.95); }
      to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .login-logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 32px;
    }

    .login-logo img {
      width: 64px;
      margin-bottom: 10px;
    }

    .login-logo h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.6rem;
      color: #ff4081;
      line-height: 1.2;
    }

    .login-logo h1 span { color: #2c3e50; }

    .login-logo p {
      font-size: 0.78rem;
      color: #8a99a8;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-top: 4px;
    }

    .admin-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #ff4081, #f50057);
      color: white;
      font-size: 0.7rem;
      font-weight: 600;
      padding: 4px 12px;
      border-radius: 20px;
      letter-spacing: 0.5px;
      margin-top: 8px;
    }

    .error-box {
      background: #ffebee;
      border-left: 4px solid #f44336;
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.83rem;
      color: #c62828;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 8px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #b0bec5;
      font-size: 0.95rem;
    }

    .input-wrapper input {
      width: 100%;
      padding: 12px 14px 12px 42px;
      border: 1.5px solid #eef2f5;
      border-radius: 10px;
      font-size: 0.88rem;
      font-family: 'Poppins', sans-serif;
      color: #2c3e50;
      background: #f8f9fc;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    .input-wrapper input:focus {
      border-color: #ff4081;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(255,64,129,0.1);
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #ff4081, #f50057);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 0.95rem;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      margin-top: 8px;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 4px 15px rgba(255,64,129,0.4);
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(255,64,129,0.5);
    }

    .btn-login:active { transform: translateY(0); }

    .login-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 0.78rem;
      color: #8a99a8;
    }

    .login-footer a {
      color: #ff4081;
      font-weight: 600;
      text-decoration: none;
    }

    .login-footer a:hover { text-decoration: underline; }

    .credential-hint {
      background: #f0f8ff;
      border: 1px dashed #90caf9;
      border-radius: 8px;
      padding: 10px 14px;
      margin-bottom: 20px;
      font-size: 0.75rem;
      color: #1565c0;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-logo">
      <img src="../assets/img/logo1.png" alt="Logo">
      <h1><i>Beauty</i> <span>Sari Salon</span></h1>
      <p>Sistem Manajemen Salon</p>
      <span class="admin-badge"><i class="fa-solid fa-shield-halved"></i> Admin Panel</span>
    </div>

    <?php if (!empty($error_msg)): ?>
      <div class="error-box">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <div class="credential-hint">
      <strong><i class="fa-solid fa-key"></i> Default Admin:</strong><br>
      📱 HP: <strong>081122334455</strong> &nbsp;|&nbsp; 🔑 Password: <strong>admin</strong>
    </div>

    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="phone"><i class="fa-solid fa-phone"></i> Nomor HP Admin</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-phone"></i>
          <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor HP admin"
                 value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
        <div class="input-wrapper">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Masukkan password admin" required>
        </div>
      </div>

      <button type="submit" class="btn-login">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Dashboard
      </button>
    </form>

    <div class="login-footer">
      <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Utama</a>
    </div>
  </div>
</body>
</html>
