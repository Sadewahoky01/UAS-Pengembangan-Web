<?php
/**
 * Smart Event Campus - Halaman Profil & Ganti Password Admin
 */

require_once 'config.php';
check_admin_auth();

$errorPassword = '';
$errorProfile  = '';
$successMsg    = '';

// ========================
// PROSES GANTI PASSWORD
// ========================
if (isset($_POST['change_password'])) {
    $currentPassword = trim($_POST['current_password']);
    $newPassword     = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorPassword = 'Semua kolom password harus diisi!';
    } elseif (strlen($newPassword) < 6) {
        $errorPassword = 'Password baru minimal 6 karakter!';
    } elseif ($newPassword !== $confirmPassword) {
        $errorPassword = 'Konfirmasi password tidak cocok!';
    } else {
        try {
            // Ambil password hash saat ini
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $_SESSION['admin_user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($currentPassword, $user['password'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmtUpdate = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmtUpdate->execute(['password' => $newHash, 'id' => $_SESSION['admin_user_id']]);
                $successMsg = 'Password berhasil diperbarui! Silakan login kembali dengan password baru Anda.';
            } else {
                $errorPassword = 'Password saat ini salah!';
            }
        } catch (PDOException $e) {
            $errorPassword = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

// ========================
// PROSES UPDATE PROFIL (Nama)
// ========================
if (isset($_POST['update_profile'])) {
    $newName = trim($_POST['name']);

    if (empty($newName)) {
        $errorProfile = 'Nama tidak boleh kosong!';
    } elseif (strlen($newName) > 100) {
        $errorProfile = 'Nama terlalu panjang (maks. 100 karakter)!';
    } else {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
            $stmtUpdate->execute(['name' => $newName, 'id' => $_SESSION['admin_user_id']]);
            // Update session name juga
            $_SESSION['admin_name'] = $newName;
            $successMsg = 'Profil berhasil diperbarui!';
        } catch (PDOException $e) {
            $errorProfile = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

// Ambil data admin terbaru dari DB
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['admin_user_id']]);
    $adminData = $stmt->fetch();
} catch (PDOException $e) {
    $adminData = ['username' => $_SESSION['admin_username'], 'name' => $_SESSION['admin_name'], 'created_at' => '-'];
}

// Ambil statistik event untuk info panel
try {
    $totalEvents  = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalUploads = $pdo->query("SELECT COUNT(*) FROM events WHERE image_path IS NOT NULL")->fetchColumn();
} catch (PDOException $e) {
    $totalEvents = $totalUploads = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - Smart Event Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* ===== STYLES KHUSUS HALAMAN PROFIL ===== */
        .profile-wrapper {
            max-width: 960px;
            margin: 0 auto;
            padding: 2rem;
        }

        .profile-page-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
        }
        .profile-page-subtitle {
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
        }

        /* Grid layout 2 kolom */
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* Sidebar profil */
        .profile-sidebar {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            position: sticky;
            top: 100px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 2.8rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            box-shadow: 0 8px 25px var(--primary-glow);
            position: relative;
        }
        .profile-avatar-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 22px;
            height: 22px;
            background: var(--success);
            border-radius: 50%;
            border: 3px solid var(--bg-main);
        }
        .profile-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .profile-username {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        .profile-username::before { content: '@'; }

        .profile-divider {
            height: 1px;
            background: var(--border-glass);
            margin: 20px 0;
        }

        .profile-stat-row {
            display: flex;
            justify-content: space-around;
            gap: 10px;
            margin-bottom: 20px;
        }
        .profile-stat-item {
            text-align: center;
        }
        .profile-stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .profile-stat-lbl {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .profile-joined {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .profile-joined strong {
            color: var(--text-secondary);
        }

        /* Konten utama (kanan) */
        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Card section */
        .profile-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            backdrop-filter: blur(10px);
        }
        .profile-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-glass);
            color: var(--text-primary);
        }
        .profile-card-title .icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(99,102,241,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        /* Password strength bar */
        .strength-bar-wrap {
            margin-top: 8px;
            height: 4px;
            background: var(--border-glass);
            border-radius: 10px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            border-radius: 10px;
            width: 0%;
            transition: all 0.4s;
        }
        .strength-label {
            font-size: 0.78rem;
            margin-top: 4px;
            font-weight: 600;
        }

        /* Info keys (readonly info) */
        .info-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .info-row:last-child { border-bottom: none; }
        .info-row-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--bg-glass-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .info-row-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .info-row-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        /* Success/error global */
        .profile-success {
            background: var(--success-bg);
            border: 1px solid var(--success);
            border-radius: 12px;
            padding: 15px 20px;
            color: #d1fae5;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .profile-sidebar {
                position: static;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
            <span>Smart Event Campus (Admin)</span>
        </a>
        <div class="nav-links" id="nav-links-menu">
            <a href="index.php" class="nav-link">Home</a>
            <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            <a href="admin_profile.php" class="nav-link active">Profil</a>
            <a href="logout.php" class="nav-btn" style="background: linear-gradient(135deg, #ef4444, #f43f5e); box-shadow: 0 4px 15px rgba(239,68,68,0.4);">Logout</a>
        </div>
        <button class="hamburger" id="hamburger-btn" aria-label="Toggle Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <main class="profile-wrapper">

        <!-- Page Header -->
        <div>
            <h1 class="profile-page-title">Profil Administrator</h1>
            <p class="profile-page-subtitle">Kelola informasi akun dan keamanan login Anda.</p>
        </div>

        <!-- Success Message Global -->
        <?php if (!empty($successMsg)): ?>
            <div class="profile-success" style="margin-bottom: 1.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php echo sanitize($successMsg); ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">

            <!-- SIDEBAR PROFIL -->
            <aside class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($adminData['name'] ?? 'A', 0, 1)); ?>
                    <div class="profile-avatar-badge"></div>
                </div>
                <div class="profile-name"><?php echo sanitize($adminData['name'] ?? '-'); ?></div>
                <div class="profile-username"><?php echo sanitize($adminData['username'] ?? '-'); ?></div>

                <div class="profile-divider"></div>

                <div class="profile-stat-row">
                    <div class="profile-stat-item">
                        <div class="profile-stat-num"><?php echo $totalEvents; ?></div>
                        <div class="profile-stat-lbl">Total Event</div>
                    </div>
                    <div class="profile-stat-item">
                        <div class="profile-stat-num"><?php echo $totalUploads; ?></div>
                        <div class="profile-stat-lbl">Dgn Gambar</div>
                    </div>
                </div>

                <div class="profile-divider"></div>

                <p class="profile-joined">
                    Bergabung sejak:<br>
                    <strong><?php
                        $createdAt = $adminData['created_at'] ?? null;
                        if ($createdAt && $createdAt !== '-') {
                            $d = new DateTime($createdAt);
                            $bulanId = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                            echo $d->format('d') . ' ' . $bulanId[(int)$d->format('n')] . ' ' . $d->format('Y');
                        } else { echo '-'; }
                    ?></strong>
                </p>

                <div class="profile-divider"></div>

                <a href="admin_dashboard.php" class="btn-secondary" style="width: 100%; display: block;">
                    ← Kembali ke Dashboard
                </a>
            </aside>

            <!-- KONTEN UTAMA -->
            <div class="profile-main">

                <!-- CARD: Informasi Akun (Read Only) -->
                <div class="profile-card">
                    <div class="profile-card-title">
                        <div class="icon-wrap">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        Informasi Akun
                    </div>

                    <div class="info-row">
                        <div class="info-row-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <div>
                            <div class="info-row-label">Username (tidak dapat diubah)</div>
                            <div class="info-row-value">@<?php echo sanitize($adminData['username'] ?? '-'); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-row-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div>
                            <div class="info-row-label">Tanggal Dibuat</div>
                            <div class="info-row-value">
                                <?php
                                    $createdAt = $adminData['created_at'] ?? null;
                                    if ($createdAt && $createdAt !== '-') {
                                        $d = new DateTime($createdAt);
                                        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                        echo $d->format('d') . ' ' . $bulanId[(int)$d->format('n')] . ' ' . $d->format('Y') . ' pukul ' . $d->format('H:i') . ' WIB';
                                    } else { echo '-'; }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-row-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <div>
                            <div class="info-row-label">Password</div>
                            <div class="info-row-value">••••••••••••</div>
                        </div>
                    </div>
                </div>

                <!-- CARD: Edit Nama -->
                <div class="profile-card">
                    <div class="profile-card-title">
                        <div class="icon-wrap">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </div>
                        Ubah Nama Tampilan
                    </div>

                    <?php if (!empty($errorProfile)): ?>
                        <div class="alert-message error" style="margin-bottom: 20px;">
                            <?php echo sanitize($errorProfile); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="admin_profile.php">
                        <div class="form-group">
                            <label for="name" class="form-label">Nama Lengkap *</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="<?php echo sanitize($adminData['name'] ?? ''); ?>"
                                   placeholder="Masukkan nama lengkap Anda" required maxlength="100">
                        </div>
                        <div class="form-actions" style="margin-top: 10px; border-top: none; padding-top: 0;">
                            <button type="submit" name="update_profile" class="btn-primary" style="width: auto; padding: 12px 28px;">
                                Simpan Nama
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CARD: Ganti Password -->
                <div class="profile-card">
                    <div class="profile-card-title">
                        <div class="icon-wrap" style="background: rgba(239,68,68,0.15); color: var(--error);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        Ganti Password
                    </div>

                    <?php if (!empty($errorPassword)): ?>
                        <div class="alert-message error" style="margin-bottom: 20px;">
                            <?php echo sanitize($errorPassword); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="admin_profile.php" id="password-form">
                        <div class="form-group">
                            <label for="current_password" class="form-label">Password Saat Ini *</label>
                            <input type="password" name="current_password" id="current_password" class="form-control"
                                   placeholder="Masukkan password yang sedang aktif" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password" class="form-label">Password Baru *</label>
                            <input type="password" name="new_password" id="new_password" class="form-control"
                                   placeholder="Minimal 6 karakter" required minlength="6">
                            <div class="strength-bar-wrap">
                                <div class="strength-bar" id="strength-bar"></div>
                            </div>
                            <div class="strength-label" id="strength-label" style="color: var(--text-muted);"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                   placeholder="Ulangi password baru" required>
                            <div class="strength-label" id="match-label" style="margin-top: 5px;"></div>
                        </div>

                        <div style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 0.88rem; color: var(--warning);">
                            ⚠️ Setelah mengganti password, Anda perlu login ulang menggunakan password baru.
                        </div>

                        <div class="form-actions" style="margin-top: 0; border-top: none; padding-top: 0;">
                            <button type="submit" name="change_password" class="btn-primary"
                                    style="width: auto; padding: 12px 28px; background: linear-gradient(135deg, #ef4444, #f43f5e); box-shadow: 0 4px 15px rgba(239,68,68,0.35);">
                                Perbarui Password
                            </button>
                        </div>
                    </form>
                </div>

            </div><!-- /profile-main -->

        </div><!-- /profile-grid -->
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Smart Event Campus. Universitas Potensi Utama.</p>
        </div>
    </footer>

    <script>
        // ---- PASSWORD STRENGTH INDICATOR ----
        const newPassInput    = document.getElementById('new_password');
        const strengthBar     = document.getElementById('strength-bar');
        const strengthLabel   = document.getElementById('strength-label');
        const confirmInput    = document.getElementById('confirm_password');
        const matchLabel      = document.getElementById('match-label');

        newPassInput.addEventListener('input', () => {
            const val = newPassInput.value;
            let score = 0;
            if (val.length >= 6)  score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: '0%',   color: '',           label: '' },
                { pct: '25%',  color: '#ef4444',    label: '🔴 Sangat Lemah' },
                { pct: '45%',  color: '#f97316',    label: '🟠 Lemah' },
                { pct: '65%',  color: '#f59e0b',    label: '🟡 Cukup' },
                { pct: '85%',  color: '#10b981',    label: '🟢 Kuat' },
                { pct: '100%', color: '#6366f1',    label: '💜 Sangat Kuat' },
            ];
            const lvl = levels[Math.min(score, 5)];
            strengthBar.style.width = lvl.pct;
            strengthBar.style.background = lvl.color;
            strengthLabel.textContent = lvl.label;
            strengthLabel.style.color = lvl.color;
        });

        // ---- CONFIRM PASSWORD MATCH CHECK ----
        function checkMatch() {
            if (confirmInput.value.length === 0) {
                matchLabel.textContent = '';
                return;
            }
            if (newPassInput.value === confirmInput.value) {
                matchLabel.textContent = '✅ Password cocok';
                matchLabel.style.color = 'var(--success)';
            } else {
                matchLabel.textContent = '❌ Password tidak cocok';
                matchLabel.style.color = 'var(--error)';
            }
        }
        confirmInput.addEventListener('input', checkMatch);
        newPassInput.addEventListener('input', checkMatch);

        // ---- HAMBURGER MENU ----
        const hamburger = document.getElementById('hamburger-btn');
        const navMenu = document.getElementById('nav-links-menu');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                const isOpen = navMenu.classList.toggle('open');
                hamburger.classList.toggle('open', isOpen);
                hamburger.setAttribute('aria-expanded', isOpen.toString());
            });
        }
    </script>
</body>
</html>
