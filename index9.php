<?php
/**
 * Smart Event Campus - Administrator Login Page
 */

require_once 'config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['name'];

                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Smart Event Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h2>Smart Event Campus</h2>
                <p>Login Administrator</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-message error">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username admin" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn-primary" style="margin-top: 10px;">Login Masuk</button>
            </form>

            <div style="text-align: center; margin-top: 25px;">
                <a href="index.php" style="font-size: 0.9rem; color: var(--text-secondary); transition: var(--transition-smooth);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                    &larr; Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
