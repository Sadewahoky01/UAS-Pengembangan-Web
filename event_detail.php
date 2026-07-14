<?php
/**
 * Smart Event Campus - Public Event Detail Page
 * Menampilkan halaman detail lengkap sebuah event kepada publik
 */

require_once 'config.php';

// Validasi & ambil ID dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch();

    if (!$event) {
        header('Location: index.php');
        exit;
    }

    // Ambil event lain (related) dari kategori yang sama, selain event ini
    $stmtRelated = $pdo->prepare("
        SELECT id, title, category, event_date, event_time, location, speaker, image_path, status 
        FROM events 
        WHERE category = :category AND id != :id 
        ORDER BY event_date ASC 
        LIMIT 3
    ");
    $stmtRelated->execute(['category' => $event['category'], 'id' => $id]);
    $relatedEvents = $stmtRelated->fetchAll();

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// Format tanggal & waktu
$dateObj       = new DateTime($event['event_date']);
$hari          = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulanId       = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$namaHari      = $hari[$dateObj->format('w')];
$formattedDate = $namaHari . ', ' . $dateObj->format('d') . ' ' . $bulanId[(int)$dateObj->format('n')] . ' ' . $dateObj->format('Y');
$formattedTime = substr($event['event_time'], 0, 5);
$catClass      = 'category-' . $event['category'];

// Label status
$statusLabel = [
    'upcoming'  => 'Akan Datang',
    'ongoing'   => 'Sedang Berjalan',
    'completed' => 'Sudah Selesai',
];
$statusText = $statusLabel[$event['status']] ?? $event['status'];

// Label kategori
$katLabel = [
    'seminar'   => 'Seminar',
    'workshop'  => 'Workshop',
    'lomba'     => 'Lomba / Kompetisi',
    'pelatihan' => 'Pelatihan',
];
$katText = $katLabel[$event['category']] ?? ucfirst($event['category']);

// URL halaman ini untuk share
$pageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO Meta Tags -->
    <title><?php echo sanitize($event['title']); ?> - Smart Event Campus</title>
    <meta name="description" content="<?php echo sanitize(substr($event['description'], 0, 160)); ?>">
    <meta property="og:title" content="<?php echo sanitize($event['title']); ?>">
    <meta property="og:description" content="<?php echo sanitize(substr($event['description'], 0, 160)); ?>">
    <meta property="og:type" content="website">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* ===== STYLES KHUSUS HALAMAN DETAIL ===== */
        .detail-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .breadcrumb a {
            color: var(--primary);
            font-weight: 500;
        }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb-sep { color: var(--text-muted); }

        /* Banner utama */
        .detail-banner {
            width: 100%;
            height: 400px;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            margin-bottom: 2.5rem;
            position: relative;
            border: 1px solid var(--border-glass);
        }
        .detail-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .detail-banner-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
        }
        .detail-banner-fallback span {
            font-size: 2.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: rgba(255,255,255,0.85);
        }
        .detail-banner-fallback small {
            font-size: 1rem;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
            letter-spacing: 1px;
        }

        /* Badge kategori di atas banner */
        .detail-badges {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
        }
        .detail-cat-badge {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }

        /* Judul event */
        .detail-title {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        /* Grid info (tanggal, waktu, lokasi, pembicara) */
        .detail-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        .detail-info-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-md);
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            backdrop-filter: blur(10px);
        }
        .detail-info-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }
        .detail-info-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .detail-info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.4;
        }

        /* Section deskripsi */
        .detail-section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .detail-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-glass);
        }

        .detail-description {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.9;
            margin-bottom: 2.5rem;
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-md);
            padding: 25px 30px;
            backdrop-filter: blur(10px);
            white-space: pre-line;
        }

        /* Share section */
        .share-section {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-md);
            padding: 25px 30px;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            backdrop-filter: blur(10px);
        }
        .share-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .share-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .share-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--border-glass);
            background: var(--bg-glass-hover);
            color: var(--text-secondary);
            transition: var(--transition-smooth);
        }
        .share-btn:hover {
            color: var(--text-primary);
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }
        .share-btn.copy-btn.copied {
            border-color: var(--success);
            color: var(--success);
            background: var(--success-bg);
        }

        /* Related events */
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 4rem;
        }
        .related-card {
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            border-radius: var(--border-radius-md);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: var(--transition-smooth);
            text-decoration: none;
            color: inherit;
            backdrop-filter: blur(10px);
        }
        .related-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99,102,241,0.3);
            box-shadow: 0 12px 24px rgba(99,102,241,0.1);
        }
        .related-banner {
            height: 140px;
            overflow: hidden;
            position: relative;
        }
        .related-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .related-banner-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.7);
        }
        .related-info {
            padding: 16px;
        }
        .related-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        .related-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Back button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            background: var(--bg-glass);
            border: 1px solid var(--border-glass);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
            margin-bottom: 2rem;
        }
        .back-btn:hover {
            color: var(--text-primary);
            border-color: var(--primary);
            background: rgba(99,102,241,0.08);
        }

        @media (max-width: 768px) {
            .detail-title { font-size: 1.7rem; }
            .detail-banner { height: 240px; }
            .detail-description { padding: 18px 20px; }
            .share-section { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="main-nav">
        <a href="index.php" class="nav-brand">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
            <span>Smart Event Campus</span>
        </a>
        <div class="nav-links" id="nav-links-menu">
            <a href="index.php" class="nav-link">Home</a>
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="event_edit.php?id=<?php echo $event['id']; ?>" class="nav-link">Edit Event Ini</a>
                <a href="logout.php" class="nav-btn" style="background: linear-gradient(135deg, #ef4444, #f43f5e);">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn">Admin Login</a>
            <?php endif; ?>
        </div>
        <!-- Hamburger button untuk mobile -->
        <button class="hamburger" id="hamburger-btn" aria-label="Toggle Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <main class="detail-wrapper">

        <!-- BREADCRUMB -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">🏠 Beranda</a>
            <span class="breadcrumb-sep">/</span>
            <a href="index.php">Event</a>
            <span class="breadcrumb-sep">/</span>
            <span><?php echo sanitize(strlen($event['title']) > 50 ? substr($event['title'], 0, 47) . '...' : $event['title']); ?></span>
        </nav>

        <!-- BACK BUTTON -->
        <a href="index.php" class="back-btn" id="back-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali ke Daftar Event
        </a>

        <!-- BANNER GAMBAR -->
        <div class="detail-banner">
            <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])): ?>
                <img src="<?php echo sanitize($event['image_path']); ?>" alt="Banner <?php echo sanitize($event['title']); ?>">
            <?php else: ?>
                <div class="detail-banner-fallback <?php echo $catClass; ?>">
                    <span><?php echo sanitize($event['category']); ?></span>
                    <small>Smart Event Campus</small>
                </div>
            <?php endif; ?>
        </div>

        <!-- BADGE KATEGORI & STATUS -->
        <div class="detail-badges">
            <span class="detail-cat-badge <?php echo $catClass; ?>">
                <?php echo sanitize($katText); ?>
            </span>
            <span class="badge-status status-<?php echo sanitize($event['status']); ?>">
                <?php echo sanitize($statusText); ?>
            </span>
        </div>

        <!-- JUDUL EVENT -->
        <h1 class="detail-title"><?php echo sanitize($event['title']); ?></h1>

        <!-- INFO CARDS GRID -->
        <div class="detail-info-grid">
            <!-- Tanggal -->
            <div class="detail-info-card">
                <div class="detail-info-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <span class="detail-info-label">Tanggal Pelaksanaan</span>
                    <div class="detail-info-value"><?php echo sanitize($formattedDate); ?></div>
                </div>
            </div>
            <!-- Waktu -->
            <div class="detail-info-card">
                <div class="detail-info-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div>
                    <span class="detail-info-label">Waktu Mulai</span>
                    <div class="detail-info-value"><?php echo sanitize($formattedTime); ?> WIB</div>
                </div>
            </div>
            <!-- Lokasi -->
            <div class="detail-info-card">
                <div class="detail-info-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div>
                    <span class="detail-info-label">Lokasi / Tempat</span>
                    <div class="detail-info-value"><?php echo sanitize($event['location']); ?></div>
                </div>
            </div>
            <!-- Pembicara -->
            <div class="detail-info-card">
                <div class="detail-info-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <span class="detail-info-label">Pembicara / Pemateri</span>
                    <div class="detail-info-value"><?php echo sanitize($event['speaker']); ?></div>
                </div>
            </div>
        </div>

        <!-- DESKRIPSI LENGKAP -->
        <h2 class="detail-section-title">Deskripsi Kegiatan</h2>
        <div class="detail-description">
            <?php echo nl2br(sanitize($event['description'])); ?>
        </div>

        <!-- SHARE SECTION -->
        <div class="share-section">
            <span class="share-label">📢 Bagikan Event Ini:</span>
            <div class="share-buttons">
                <!-- WhatsApp Share -->
                <a class="share-btn" 
                   href="https://api.whatsapp.com/send?text=<?php echo urlencode($event['title'] . ' - ' . $pageUrl); ?>"
                   target="_blank" rel="noopener noreferrer" title="Bagikan ke WhatsApp">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
                <!-- Copy Link -->
                <button class="share-btn copy-btn" id="copy-link-btn" title="Salin tautan halaman ini">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span id="copy-btn-text">Salin Tautan</span>
                </button>
            </div>
        </div>

        <!-- RELATED EVENTS -->
        <?php if (count($relatedEvents) > 0): ?>
            <h2 class="detail-section-title">Event Serupa</h2>
            <div class="related-grid">
                <?php foreach ($relatedEvents as $rel):
                    $relDate = new DateTime($rel['event_date']);
                    $relFormatted = $relDate->format('d') . ' ' . $bulanId[(int)$relDate->format('n')] . ' ' . $relDate->format('Y');
                    $relCatClass = 'category-' . $rel['category'];
                ?>
                    <a href="event_detail.php?id=<?php echo $rel['id']; ?>" class="related-card">
                        <div class="related-banner">
                            <?php if (!empty($rel['image_path']) && file_exists(__DIR__ . '/' . $rel['image_path'])): ?>
                                <img src="<?php echo sanitize($rel['image_path']); ?>" alt="<?php echo sanitize($rel['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <div class="related-banner-fallback <?php echo $relCatClass; ?>">
                                    <?php echo sanitize($rel['category']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="related-info">
                            <div class="related-title"><?php echo sanitize($rel['title']); ?></div>
                            <div class="related-meta">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <?php echo sanitize($relFormatted); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Smart Event Campus. Universitas Potensi Utama.</p>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Sistem Informasi Manajemen Event &mdash; Tugas Akhir Semester Mata Kuliah Pengembangan Web.</p>
        </div>
    </footer>

    <script>
        // Copy link to clipboard
        const copyBtn = document.getElementById('copy-link-btn');
        const copyText = document.getElementById('copy-btn-text');
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href).then(() => {
                copyBtn.classList.add('copied');
                copyText.textContent = 'Tersalin!';
                setTimeout(() => {
                    copyBtn.classList.remove('copied');
                    copyText.textContent = 'Salin Tautan';
                }, 2500);
            });
        });

        // Smart back button: kembali ke halaman sebelumnya jika tersedia
        const backBtn = document.getElementById('back-btn');
        if (document.referrer && document.referrer.includes(window.location.hostname)) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                history.back();
            });
        }

        // Hamburger menu
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
