<?php
/**
 * Smart Event Campus - Administrator Dashboard
 */

require_once 'config.php';
check_admin_auth();

try {
    // 1. Fetch count stats
    $stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM events");
    $totalEvents = $stmtTotal->fetch()['total'];

    $stmtUpcoming = $pdo->query("SELECT COUNT(*) AS upcoming FROM events WHERE status = 'upcoming'");
    $upcomingEvents = $stmtUpcoming->fetch()['upcoming'];

    $stmtOngoing = $pdo->query("SELECT COUNT(*) AS ongoing FROM events WHERE status = 'ongoing'");
    $ongoingEvents = $stmtOngoing->fetch()['ongoing'];

    $stmtCompleted = $pdo->query("SELECT COUNT(*) AS completed FROM events WHERE status = 'completed'");
    $completedEvents = $stmtCompleted->fetch()['completed'];

    // 2. Fetch list of all events
    $stmtList = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
    $events = $stmtList->fetchAll();
} catch (PDOException $e) {
    die("Kesalahan database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Event Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
            <a href="admin_dashboard.php" class="nav-link active">Dashboard</a>
            <a href="admin_profile.php" class="nav-link">Profil</a>
            <span style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;">
                Halo, <?php echo sanitize($_SESSION['admin_name']); ?>
            </span>
            <a href="logout.php" class="nav-btn" style="background: linear-gradient(135deg, #ef4444, #f43f5e); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">Logout</a>
        </div>
        <button class="hamburger" id="hamburger-btn" aria-label="Toggle Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- CONTENT CONTAINER -->
    <main class="container">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.2rem; font-weight: 800;">Overview Pengelolaan Event</h1>
            <p style="color: var(--text-secondary);">Kelola, tambah, ubah, dan hapus event kampus dengan mudah dari panel administrator ini.</p>
        </header>

        <!-- STATS CARDS GRID -->
        <section class="stats-grid">
            <div class="stat-card">
                <div>
                    <div class="stat-title">Total Event</div>
                    <div class="stat-val"><?php echo $totalEvents; ?></div>
                </div>
                <div class="stat-icon primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-title">Akan Datang</div>
                    <div class="stat-val"><?php echo $upcomingEvents; ?></div>
                </div>
                <div class="stat-icon secondary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-title">Sedang Berjalan</div>
                    <div class="stat-val"><?php echo $ongoingEvents; ?></div>
                </div>
                <div class="stat-icon accent">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div>
                    <div class="stat-title">Selesai</div>
                    <div class="stat-val"><?php echo $completedEvents; ?></div>
                </div>
                <div class="stat-icon success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>
        </section>

        <!-- DASHBOARD HEADER -->
        <div class="dash-header">
            <h2>Daftar Kegiatan Kampus</h2>
            <a href="event_add.php" class="btn-add">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Event Baru
            </a>
        </div>

        <!-- NOTIFICATIONS SESSION SUCCESS/ERROR -->
        <?php if (isset($_SESSION['action_success'])): ?>
            <div class="alert-message success" style="margin-bottom: 25px;">
                <?php 
                    echo sanitize($_SESSION['action_success']); 
                    unset($_SESSION['action_success']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['action_error'])): ?>
            <div class="alert-message error" style="margin-bottom: 25px;">
                <?php 
                    echo sanitize($_SESSION['action_error']); 
                    unset($_SESSION['action_error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- EVENTS DATA TABLE -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Judul Event</th>
                        <th>Kategori</th>
                        <th>Tanggal & Waktu</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($events) > 0): ?>
                        <?php foreach ($events as $event): 
                            $dateObj = new DateTime($event['event_date']);
                            $formattedDate = $dateObj->format('d M Y');
                            $formattedTime = substr($event['event_time'], 0, 5);
                            $catClass = 'category-' . $event['category'];
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])): ?>
                                        <img src="<?php echo sanitize($event['image_path']); ?>" alt="Banner" style="width: 70px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-glass);">
                                    <?php else: ?>
                                        <!-- Gradient fallbacks in table -->
                                        <div class="<?php echo $catClass; ?>" style="width: 70px; height: 45px; border-radius: 6px; display:flex; align-items:center; justify-content:center; font-size:0.6rem; font-weight:800; color:white; text-transform:uppercase;">
                                            <?php echo sanitize(substr($event['category'], 0, 3)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-event-title" title="<?php echo sanitize($event['title']); ?>">
                                        <?php echo sanitize($event['title']); ?>
                                    </div>
                                    <span style="font-size: 0.8rem; color: var(--text-secondary);">
                                        Oleh: <?php echo sanitize($event['speaker']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="table-event-cat" style="color: var(--text-primary); font-weight:600;">
                                        &bull; <?php echo sanitize($event['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem; font-weight: 500;"><?php echo $formattedDate; ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo $formattedTime; ?> WIB</div>
                                </td>
                                <td>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);" title="<?php echo sanitize($event['location']); ?>">
                                        <?php echo sanitize(strlen($event['location']) > 20 ? substr($event['location'], 0, 18) . '..' : $event['location']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status status-<?php echo sanitize($event['status']); ?>">
                                        <?php echo sanitize($event['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <!-- Edit button -->
                                        <a href="event_edit.php?id=<?php echo $event['id']; ?>" class="btn-icon edit" title="Edit Event">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                        <!-- Delete button with confirmation -->
                                        <a href="event_delete.php?id=<?php echo $event['id']; ?>" class="btn-icon delete" title="Hapus Event" onclick="return confirm('Apakah Anda yakin ingin menghapus event \'<?php echo addslashes(sanitize($event['title'])); ?>\'? Tindakan ini tidak dapat dibatalkan.');">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">
                                Belum ada data kegiatan terdaftar. Silakan klik "Tambah Event Baru".
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Smart Event Campus. Universitas Potensi Utama.</p>
        </div>
    </footer>

    <script>
        // Hamburger Menu
        const hamburger = document.getElementById('hamburger-btn');
        const navMenu   = document.getElementById('nav-links-menu');
        if (hamburger && navMenu) {
            hamburger.addEventListener('click', () => {
                const isOpen = navMenu.classList.toggle('open');
                hamburger.classList.toggle('open', isOpen);
                hamburger.setAttribute('aria-expanded', isOpen.toString());
            });
            document.addEventListener('click', (e) => {
                if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    navMenu.classList.remove('open');
                    hamburger.classList.remove('open');
                }
            });
        }
    </script>
</body>
</html>
