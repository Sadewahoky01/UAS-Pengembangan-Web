<?php
/**
 * Smart Event Campus - Landing Page (Public View)
 */

require_once 'config.php';

try {
    // Fetch all events sorted by upcoming date
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Event Campus - Universitas Potensi Utama</title>
    <!-- Modern Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
            <a href="index.php" class="nav-link active">Home</a>
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
                <a href="logout.php" class="nav-btn">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn">Admin Login</a>
            <?php endif; ?>
        </div>
        <!-- Hamburger button untuk mobile -->
        <button class="hamburger" id="hamburger-btn" aria-label="Toggle Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero">
        <h1>Kembangkan Diri Anda Melalui <span>Kegiatan Kampus</span></h1>
        <p>Temukan seminar akademis, workshop teknologi, kompetisi mahasiswa, dan berbagai pelatihan kepemimpinan terbaik di Universitas Potensi Utama.</p>
    </header>

    <main class="container">
        
        <!-- SEARCH & FILTER ROW -->
        <section class="controls-row">
            <!-- Dynamic JavaScript Live Search -->
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="event-search" placeholder="Cari event menarik..." aria-label="Cari event">
            </div>

            <!-- Dynamic Category Filters -->
            <div class="filter-group" id="filter-container">
                <button class="filter-btn active" data-filter="all">Semua</button>
                <button class="filter-btn" data-filter="seminar">Seminar</button>
                <button class="filter-btn" data-filter="workshop">Workshop</button>
                <button class="filter-btn" data-filter="lomba">Lomba</button>
                <button class="filter-btn" data-filter="pelatihan">Pelatihan</button>
            </div>
        </section>

        <!-- EVENT LIST GRID -->
        <section class="event-grid" id="events-wrapper">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): 
                    // Format Date to Indonesia format
                    $dateObj = new DateTime($event['event_date']);
                    $formattedDate = $dateObj->format('d M Y');
                    $formattedTime = substr($event['event_time'], 0, 5);
                    
                    // Determine banner class / gradient fallback
                    $catClass = 'category-' . $event['category'];
                ?>
                    <article class="event-card" 
                             data-category="<?php echo sanitize($event['category']); ?>"
                             data-title="<?php echo sanitize(strtolower($event['title'])); ?>"
                             data-id="<?php echo $event['id']; ?>">
                        
                        <div class="event-banner <?php echo $catClass; ?>">
                            <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])): ?>
                                <img src="<?php echo sanitize($event['image_path']); ?>" alt="Banner <?php echo sanitize($event['title']); ?>" loading="lazy">
                            <?php else: ?>
                                <!-- Premium CSS Gradient Fallback with Category Icon/Indicator -->
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0.01));">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <span class="event-category-badge <?php echo $catClass; ?>">
                                <?php echo sanitize($event['category']); ?>
                            </span>
                        </div>

                        <div class="event-info">
                            <div class="event-date-badge">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span><?php echo $formattedDate; ?> &bull; <?php echo $formattedTime; ?> WIB</span>
                            </div>
                            
                            <h3 class="event-title"><?php echo sanitize($event['title']); ?></h3>
                            <p class="event-description-snippet"><?php echo sanitize($event['description']); ?></p>
                            
                            <div class="event-meta">
                                <div class="event-speaker">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span><?php echo sanitize($event['speaker']); ?></span>
                                </div>
                                <div class="event-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span><?php echo sanitize($event['location']); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Data stored in dataset attributes for modal popup details -->
                        <div class="event-footer" style="display: flex; gap: 10px;">
                            <button type="button" class="event-btn btn-open-detail" 
                                    style="flex: 1;"
                                    data-title-full="<?php echo sanitize($event['title']); ?>"
                                    data-category-full="<?php echo sanitize($event['category']); ?>"
                                    data-desc-full="<?php echo sanitize($event['description']); ?>"
                                    data-date-full="<?php echo $formattedDate; ?>"
                                    data-time-full="<?php echo $formattedTime; ?>"
                                    data-location-full="<?php echo sanitize($event['location']); ?>"
                                    data-speaker-full="<?php echo sanitize($event['speaker']); ?>"
                                    data-image-full="<?php echo sanitize($event['image_path']); ?>"
                                    data-status-full="<?php echo sanitize($event['status']); ?>">
                                Detail Event
                            </button>
                            <a href="event_detail.php?id=<?php echo $event['id']; ?>" 
                               class="event-btn" 
                               style="flex: 0 0 auto; display: flex; align-items: center; justify-content: center; width: 42px; border-radius: 10px;"
                               title="Buka halaman penuh">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <h3>Belum Ada Event Tersedia</h3>
                    <p>Silakan login sebagai admin untuk menambahkan event baru.</p>
                </div>
            <?php endif; ?>

            <!-- JS Dynamic Fallback when search has no results -->
            <div class="no-results" id="search-fallback" style="display: none;">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <h3>Tidak Ada Hasil Ditemukan</h3>
                <p>Coba gunakan kata kunci pencarian yang lain.</p>
            </div>
        </section>
    </main>

    <!-- INTERACTIVE DETAIL MODAL -->
    <div class="modal" id="detail-modal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close" id="modal-close-btn" aria-label="Tutup modal">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <div class="modal-banner" id="modal-banner-container">
                <!-- Image or gradient placeholder gets injected here -->
            </div>
            <div class="modal-body">
                <div class="modal-cat-date">
                    <span class="badge-status status-upcoming" id="modal-status-pill">upcoming</span>
                    <div class="event-date-badge" style="margin-bottom: 0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span id="modal-date-time"></span>
                    </div>
                </div>
                <h2 class="modal-title" id="modal-title-text">Detail Event</h2>
                <p class="modal-desc" id="modal-desc-text">Deskripsi event lengkap...</p>
                
                <div class="modal-meta-grid">
                    <div class="modal-meta-item">
                        <div class="modal-meta-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="modal-meta-text">
                            <span class="modal-meta-label">Pembicara / Narasumber</span>
                            <strong id="modal-speaker-text">-</strong>
                        </div>
                    </div>
                    <div class="modal-meta-item">
                        <div class="modal-meta-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="modal-meta-text">
                            <span class="modal-meta-label">Tempat / Lokasi</span>
                            <strong id="modal-location-text">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Smart Event Campus. Universitas Potensi Utama.</p>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Sistem Informasi Manajemen Event untuk Tugas Akhir Semester Mata Kuliah Pengembangan Web.</p>
        </div>
    </footer>

    <!-- INTERACTIVE JAVASCRIPT FOR SEARCH, FILTER, AND MODAL -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('event-search');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const eventCards = document.querySelectorAll('.event-card');
            const searchFallback = document.getElementById('search-fallback');

            // --- SEARCH & FILTER LOGIC ---
            let currentFilter = 'all';
            let searchQuery = '';

            function applyFilterAndSearch() {
                let matchCount = 0;
                
                eventCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    const cardTitle = card.getAttribute('data-title');
                    
                    const matchesCategory = (currentFilter === 'all' || cardCategory === currentFilter);
                    const matchesSearch = cardTitle.includes(searchQuery);

                    if (matchesCategory && matchesSearch) {
                        card.style.display = 'flex';
                        matchCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show fallback if no cards match
                if (matchCount === 0 && eventCards.length > 0) {
                    searchFallback.style.display = 'block';
                } else {
                    searchFallback.style.display = 'none';
                }
            }

            // Search Keyup
            searchInput.addEventListener('keyup', (e) => {
                searchQuery = e.target.value.toLowerCase().trim();
                applyFilterAndSearch();
            });

            // Category Filter Click
            filterButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    currentFilter = btn.getAttribute('data-filter');
                    applyFilterAndSearch();
                });
            });

            // --- MODAL POPUP LOGIC ---
            const modal = document.getElementById('detail-modal');
            const modalCloseBtn = document.getElementById('modal-close-btn');
            const openButtons = document.querySelectorAll('.btn-open-detail');
            
            const mBannerContainer = document.getElementById('modal-banner-container');
            const mStatusPill = document.getElementById('modal-status-pill');
            const mDateTime = document.getElementById('modal-date-time');
            const mTitle = document.getElementById('modal-title-text');
            const mDesc = document.getElementById('modal-desc-text');
            const mSpeaker = document.getElementById('modal-speaker-text');
            const mLocation = document.getElementById('modal-location-text');

            openButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const title = btn.getAttribute('data-title-full');
                    const category = btn.getAttribute('data-category-full');
                    const desc = btn.getAttribute('data-desc-full');
                    const date = btn.getAttribute('data-date-full');
                    const time = btn.getAttribute('data-time-full');
                    const location = btn.getAttribute('data-location-full');
                    const speaker = btn.getAttribute('data-speaker-full');
                    const image = btn.getAttribute('data-image-full');
                    const status = btn.getAttribute('data-status-full');

                    // Set Title, Description, Speaker, Location
                    mTitle.textContent = title;
                    mDesc.textContent = desc;
                    mSpeaker.textContent = speaker;
                    mLocation.textContent = location;
                    mDateTime.textContent = `${date} • ${time} WIB`;
                    
                    // Set Status pill
                    mStatusPill.className = `badge-status status-${status}`;
                    mStatusPill.textContent = status;

                    // Setup Banner (Image or Gradient Fallback)
                    mBannerContainer.innerHTML = '';
                    if (image && image !== '') {
                        const imgEl = document.createElement('img');
                        imgEl.src = image;
                        imgEl.alt = 'Banner Detail';
                        mBannerContainer.appendChild(imgEl);
                    } else {
                        const gradEl = document.createElement('div');
                        gradEl.className = `category-${category}`;
                        gradEl.style.width = '100%';
                        gradEl.style.height = '100%';
                        gradEl.style.display = 'flex';
                        gradEl.style.alignItems = 'center';
                        gradEl.style.justifyContent = 'center';
                        
                        // Insert Category Name into gradient fallback
                        gradEl.innerHTML = `<span style="font-size: 1.8rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:white; opacity:0.8;">${category}</span>`;
                        mBannerContainer.appendChild(gradEl);
                    }

                    // Open Modal
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });

            // Close Modal function
            function closeModal() {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }

            modalCloseBtn.addEventListener('click', closeModal);

            // Close on click overlay background
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Close on Escape keypress
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });

            // --- HAMBURGER MENU ---
            const hamburger = document.getElementById('hamburger-btn');
            const navMenu   = document.getElementById('nav-links-menu');
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', () => {
                    const isOpen = navMenu.classList.toggle('open');
                    hamburger.classList.toggle('open', isOpen);
                    hamburger.setAttribute('aria-expanded', isOpen.toString());
                });
                // Tutup menu saat klik di luar
                document.addEventListener('click', (e) => {
                    if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                        navMenu.classList.remove('open');
                        hamburger.classList.remove('open');
                        hamburger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
</body>
</html>
