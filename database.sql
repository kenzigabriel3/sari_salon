-- Create Database
CREATE DATABASE IF NOT EXISTS sari_salon;
USE sari_salon;

-- Table for users (customers and admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration INT NOT NULL, -- in minutes
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for employees (stylists/therapists)
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    rating DECIMAL(2,1) NOT NULL,
    experience VARCHAR(50) NOT NULL,
    image_path VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for bookings / queue
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    employee_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    queue_number VARCHAR(10) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- pending, completed, cancelled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed services data (from existing service cards in services.html)
INSERT INTO services (name, duration, price, image_path, description) VALUES
('Facial', 60, 150000.00, 'assets/icons/faciel.png', 'Perawatan wajah menyeluruh untuk membersihkan dan menutrisi kulit agar tampak segar dan bercahaya.'),
('Skin Booster', 90, 250000.00, 'assets/icons/skinbooster.png', 'Perawatan untuk mencerahkan dan melembapkan kulit agar tampak lebih glowing alami.'),
('BB Glow', 90, 300000.00, 'assets/icons/bbglow.png', 'Treatment semi-permanen untuk efek kulit halus dan cerah seperti memakai foundation.'),
('Paket Laser Black Doll', 120, 350000.00, 'assets/icons/laser.png', 'Mengangkat sel kulit mati, mengecilkan pori, dan mencerahkan kulit dengan teknologi laser terkini.'),
('Laser Sulam Alis Gagal', 60, 200000.00, 'assets/icons/alis.png', 'Solusi aman memperbaiki hasil sulam alis yang tidak sesuai.'),
('Cauter Milia & Kutil', 45, 150000.00, 'assets/icons/cauter.jpeg', 'Pengangkatan milia dan kutil dengan alat steril dan profesional.'),
('Colour', 90, 200000.00, 'assets/icons/colour.png', 'Perawatan pewarnaan rambut dengan hasil alami dan tahan lama.'),
('Smoothing', 120, 300000.00, 'assets/icons/smoothing.png', 'Menjadikan rambut lurus, lembut, dan mudah diatur dengan hasil alami.'),
('Eyelash', 60, 120000.00, 'assets/icons/eyelash.jpg', 'Menebalkan dan memperindah bulu mata agar tampak lebih lentik dan natural.'),
('Nail Art', 45, 80000.00, 'assets/icons/nailart.png', 'Seni menghias kuku dengan berbagai motif dan warna yang menarik.'),
('Therapy Kutu', 60, 100000.00, 'assets/icons/kutu.jpg', 'Perawatan untuk menghilangkan kutu rambut secara tuntas dan higienis.'),
('Therapy Ketombe', 45, 90000.00, 'assets/icons/ketombe.png', 'Mengatasi masalah ketombe dan kulit kepala gatal dengan bahan alami.'),
('Crimbat', 45, 75000.00, 'assets/icons/crimbat.jpg', 'Perawatan rambut tradisional untuk memperkuat akar dan mencegah kerontokan.'),
('Hair Mask', 45, 85000.00, 'assets/icons/hairmask.jpg', 'Masker rambut yang menutrisi dan melembapkan agar rambut tetap sehat dan berkilau.');

-- Seed employees data (Rida, Ika, Sari)
INSERT INTO employees (name, specialty, rating, experience, image_path) VALUES
('Rida', 'Spesialis Facial & Perawatan Wajah', 4.9, '5 tahun pengalaman', 'assets/img/karyawan-rida.png'),
('Ika', 'Spesialis Kulit & BB Glow', 4.8, '6 tahun pengalaman', 'assets/img/karyawan-ika.png'),
('Sari', 'Spesialis Hair Styling & Make Up', 5.0, '8 tahun pengalaman', 'assets/img/karyawan-sari.png');
