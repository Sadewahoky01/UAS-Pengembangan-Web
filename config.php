<?php
/**
 * Smart Event Campus - Database Connection & Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'smart_event_campus';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // If the database does not exist or connection fails, show a clean message and link to setup.php
    die('
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Database Gagal</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
        <style>
            body {
                background-color: #0b0f19;
                color: #f3f4f6;
                font-family: "Outfit", sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                box-sizing: border-box;
            }
            .card {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                max-width: 480px;
                width: 100%;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            }
            h2 {
                color: #ef4444;
                margin-bottom: 15px;
            }
            p {
                color: #9ca3af;
                font-size: 0.95rem;
                line-height: 1.6;
                margin-bottom: 25px;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: linear-gradient(135deg, #4f46e5, #6366f1);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
                transition: all 0.3s;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h2>Koneksi Database Gagal</h2>
            <p>Aplikasi tidak dapat terhubung ke database. Harap pastikan bahwa MySQL server (XAMPP) Anda sudah aktif dan database telah diinisialisasi melalui wizard setup.</p>
            <a href="setup.php" class="btn">Jalankan Setup Database</a>
        </div>
    </body>
    </html>
    ');
}

/**
 * Helper function to escape HTML output
 */
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to check if admin is logged in
 */
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>
