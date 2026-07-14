-- SQL Database Schema for Smart Event Campus
-- Target Database: smart_event_campus

CREATE DATABASE IF NOT EXISTS `smart_event_campus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smart_event_campus`;

-- Table structure for table `users` (Administrator accounts)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `events`
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` ENUM('seminar', 'workshop', 'lomba', 'pelatihan') NOT NULL,
  `description` TEXT NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `speaker` VARCHAR(100) NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `status` ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default Administrator account
-- Password: admin123 (hashed using PASSWORD_DEFAULT)
INSERT INTO `users` (`id`, `username`, `password`, `name`) 
VALUES (1, 'admin', '$2y$10$Nyq7/Rs94oGgMPmP52mjauoeRyFWItBNY.1fNagGZlCSWheD4RooK', 'Administrator')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Insert initial events for campus activities
INSERT INTO `events` (`title`, `category`, `description`, `event_date`, `event_time`, `location`, `speaker`, `image_path`, `status`) VALUES
('Seminar Nasional Tren AI dan IoT 2026', 'seminar', 'Seminar nasional ini akan mengupas tuntas perkembangan teknologi Kecerdasan Buatan (AI) dan Internet of Things (IoT) yang mendisrupsi berbagai industri saat ini, serta peluang karir mahasiswa di bidang ini.', '2026-08-15', '09:00:00', 'Aula Utama Lantai 3', 'Dr. Adil Setiawan, M.Kom', 'assets/images/seminar_ai.jpg', 'upcoming'),
('Workshop Full-Stack Web dengan Node.js dan PHP', 'workshop', 'Pelajari cara membangun aplikasi web full-stack modern menggunakan kombinasi PHP di backend dan API modern. Cocok untuk mahasiswa yang ingin memperdalam mata kuliah web.', '2026-08-20', '13:00:00', 'Lab Komputer 4 Gedung B', 'Ananda Pratama', 'assets/images/workshop_web.jpg', 'upcoming'),
('Lomba Hackathon Inovasi Kampus Hijau', 'lomba', 'Kompetisi hackathon coding selama 24 jam untuk merancang solusi digital guna mendukung program keberlanjutan dan kampus ramah lingkungan di universitas. Total hadiah jutaan rupiah!', '2026-09-02', '08:00:00', 'Gedung Serbaguna Potensi Utama', 'Tim Panitia Kemahasiswaan', 'assets/images/lomba_hackathon.jpg', 'upcoming'),
('Pelatihan Public Speaking & Leadership Mahasiswa', 'pelatihan', 'Tingkatkan kepercayaan diri Anda dalam berbicara di depan umum serta asah jiwa kepemimpinan organisasi untuk persiapan memasuki dunia kerja yang kompetitif.', '2026-09-10', '08:30:00', 'Ruang Rapat Senat Akademik', 'Drs. Hermawan, M.Si', 'assets/images/pelatihan_speak.jpg', 'upcoming');
