<?php
$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$host_parts = explode(':', $http_host);
$host_only = isset($host_parts[0]) ? $host_parts[0] : '';
$is_localhost = in_array($host_only, array('localhost', '127.0.0.1', '::1')) || (empty($http_host) && PHP_SAPI === 'cli');

if ($is_localhost) {
    $host = 'localhost';
    $dbname = 'sari_salon';
    $username = 'root';
    $password = '';
} else {
    $host = 'sql311.infinityfree.com';
    $dbname = 'if0_42095320_sari_salon';
    $username = 'if0_42095320';
    $password = 'kenzi0711';
}

try {
    // Establish a PDO database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // In case of connection error, terminate script and display error message
    die("Koneksi database gagal: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Automatically seed a default admin user if none exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    if ($adminCount == 0) {
        $adminName = 'Admin Salon';
        $adminPhone = '081122334455';
        $adminPassword = password_hash('admin', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO users (name, phone, password, role) VALUES (?, ?, ?, 'admin')");
        $stmtInsert->execute([$adminName, $adminPhone, $adminPassword]);
    }
} catch (PDOException $e) {
    // Ignore seeding errors in normal flow
}

// Auto-migrate: add payment columns to bookings if they don't exist
try {
    // Make queue_number nullable
    $pdo->exec("ALTER TABLE bookings MODIFY COLUMN queue_number VARCHAR(10) NULL");

    $cols = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_method'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE bookings 
            ADD COLUMN payment_method ENUM('cash','transfer') DEFAULT NULL,
            ADD COLUMN payment_status ENUM('unpaid','pending_cash','pending_transfer','paid') DEFAULT 'unpaid',
            ADD COLUMN payment_proof VARCHAR(255) DEFAULT NULL
        ");
    }
} catch (PDOException $e) {
    // Ignore migration errors silently
}
?>
