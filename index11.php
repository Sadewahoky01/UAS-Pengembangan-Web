<?php
/**
 * Smart Event Campus - Database Setup Script
 * Runs database initialization from database.sql
 */

$host = 'localhost';
$username = 'root';
$password = '';
$message = '';
$status = 'info';

if (isset($_POST['setup'])) {
    try {
        // Connect to MySQL server without database first
        $pdo = new PDO("mysql:host=$host", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Read database.sql file
        $sqlPath = __DIR__ . '/database.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("File database.sql tidak ditemukan di: " . $sqlPath);
        }

        $sql = file_get_contents($sqlPath);
        
        // Execute the SQL queries
        // PDO exec can run multiple queries if they are separated by semicolons
        $pdo->exec($sql);
        
        $status = 'success';
        $message = "Database <strong>smart_event_campus</strong> berhasil diinisialisasi!<br>
                    Akun Administrator Default:<br>
                    Username: <strong>admin</strong><br>
                    Password: <strong>admin123</strong>";
    } catch (Exception $e) {
        $status = 'error';
        $message = "Gagal memproses setup database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Smart Event Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --success: #10b981;
            --error: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.15) 0, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
        }

        .btn:active {
            transform: translateY(0);
        }

        .alert {
            margin-top: 25px;
            padding: 20px;
            border-radius: 12px;
            font-size: 0.95rem;
            line-height: 1.6;
            text-align: left;
            border-left: 5px solid;
            animation: fadeIn 0.5s ease;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: var(--success);
            color: #d1fae5;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--error);
            color: #fee2e2;
        }

        .links {
            margin-top: 30px;
            display: flex;
            justify-content: space-around;
        }

        .links a {
            color: #818cf8;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #a5b4fc;
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Smart Event Campus</div>
        <div class="subtitle">Database Setup & Installer Wizard</div>

        <form method="POST">
            <button type="submit" name="setup" class="btn">Inisialisasi Database</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="links">
            <a href="index.php">Landing Page</a>
            <a href="login.php">Admin Login</a>
        </div>
    </div>
</body>
</html>
