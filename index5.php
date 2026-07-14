<?php
/**
 * Smart Event Campus - Add Event Page
 */

require_once 'config.php';
check_admin_auth();

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $title       = trim($_POST['title']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);
    $event_date  = trim($_POST['event_date']);
    $event_time  = trim($_POST['event_time']);
    $location    = trim($_POST['location']);
    $speaker     = trim($_POST['speaker']);
    $status      = trim($_POST['status']);
    
    // Check required fields
    if (empty($title) || empty($category) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || empty($speaker)) {
        $error = 'Harap isi semua kolom wajib!';
    } else {
        $image_path = null;

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                // Limit size to 2MB
                if ($fileSize <= 2 * 1024 * 1024) {
                    $uploadFileDir = __DIR__ . '/assets/images/';
                    
                    // Create directory if not exists
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    
                    $newFileName = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $image_path = 'assets/images/' . $newFileName;
                    } else {
                        $error = 'Ada masalah saat memindahkan file gambar yang diunggah.';
                    }
                } else {
                    $error = 'Ukuran file gambar terlalu besar. Maksimal 2MB!';
                }
            } else {
                $error = 'Format file gambar tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP!';
            }
        }

        // If no image error occurred, proceed to save to database
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO events (title, category, description, event_date, event_time, location, speaker, image_path, status) 
                    VALUES (:title, :category, :description, :event_date, :event_time, :location, :speaker, :image_path, :status)
                ");
                
                $stmt->execute([
                    'title'       => $title,
                    'category'    => $category,
                    'description' => $description,
                    'event_date'  => $event_date,
                    'event_time'  => $event_time,
                    'location'    => $location,
                    'speaker'     => $speaker,
                    'image_path'  => $image_path,
                    'status'      => $status
                ]);

                $_SESSION['action_success'] = 'Event "' . $title . '" berhasil ditambahkan!';
                header('Location: admin_dashboard.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan event ke database: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Event Baru - Smart Event Campus</title>
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
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="admin_dashboard.php" class="nav-link active">Dashboard</a>
            <a href="logout.php" class="nav-btn" style="background: linear-gradient(135deg, #ef4444, #f43f5e); box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">Logout</a>
        </div>
    </nav>

    <!-- CONTENT CONTAINER -->
    <main class="container">
        
        <div class="form-card">
            <div class="form-card-title">
                <span>Tambah Event Baru</span>
                <a href="admin_dashboard.php" class="btn-secondary" style="font-size: 0.85rem; padding: 8px 16px;">Batal & Kembali</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-message error">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="event_add.php" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <!-- Judul Event -->
                    <div class="form-group col-span-2">
                        <label for="title" class="form-label">Judul Kegiatan / Event *</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Seminar Nasional Rekayasa Web" required value="<?php echo isset($_POST['title']) ? sanitize($_POST['title']) : ''; ?>">
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label for="category" class="form-label">Kategori *</label>
                        <select name="category" id="category" class="form-control" required style="background-color: #0b0f19;">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="seminar" <?php echo (isset($_POST['category']) && $_POST['category'] === 'seminar') ? 'selected' : ''; ?>>Seminar</option>
                            <option value="workshop" <?php echo (isset($_POST['category']) && $_POST['category'] === 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                            <option value="lomba" <?php echo (isset($_POST['category']) && $_POST['category'] === 'lomba') ? 'selected' : ''; ?>>Lomba</option>
                            <option value="pelatihan" <?php echo (isset($_POST['category']) && $_POST['category'] === 'pelatihan') ? 'selected' : ''; ?>>Pelatihan</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="form-label">Status Event *</label>
                        <select name="status" id="status" class="form-control" required style="background-color: #0b0f19;">
                            <option value="upcoming" <?php echo (isset($_POST['status']) && $_POST['status'] === 'upcoming') ? 'selected' : ''; ?>>Upcoming (Akan Datang)</option>
                            <option value="ongoing" <?php echo (isset($_POST['status']) && $_POST['status'] === 'ongoing') ? 'selected' : ''; ?>>Ongoing (Sedang Berjalan)</option>
                            <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] === 'completed') ? 'selected' : ''; ?>>Completed (Selesai)</option>
                        </select>
                    </div>

                    <!-- Narasumber / Pembicara -->
                    <div class="form-group">
                        <label for="speaker" class="form-label">Pembicara / Pemateri *</label>
                        <input type="text" name="speaker" id="speaker" class="form-control" placeholder="Contoh: Dr. Adil Setiawan, M.Kom" required value="<?php echo isset($_POST['speaker']) ? sanitize($_POST['speaker']) : ''; ?>">
                    </div>

                    <!-- Tempat / Lokasi -->
                    <div class="form-group">
                        <label for="location" class="form-label">Tempat / Lokasi *</label>
                        <input type="text" name="location" id="location" class="form-control" placeholder="Contoh: Lab Komputer 4 / Zoom Meeting" required value="<?php echo isset($_POST['location']) ? sanitize($_POST['location']) : ''; ?>">
                    </div>

                    <!-- Tanggal -->
                    <div class="form-group">
                        <label for="event_date" class="form-label">Tanggal Pelaksanaan *</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required value="<?php echo isset($_POST['event_date']) ? sanitize($_POST['event_date']) : ''; ?>">
                    </div>

                    <!-- Waktu -->
                    <div class="form-group">
                        <label for="event_time" class="form-label">Waktu Mulai *</label>
                        <input type="time" name="event_time" id="event_time" class="form-control" required value="<?php echo isset($_POST['event_time']) ? sanitize($_POST['event_time']) : ''; ?>">
                    </div>

                    <!-- Banner Image Upload -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Upload Banner Event (Opsional)</label>
                        <div class="file-input-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);" id="upload-instruction">
                                Klik atau seret gambar ke sini (JPG, PNG, WEBP maks. 2MB)
                            </div>
                            <input type="file" name="image" id="image-input" accept="image/*">
                            <img id="image-preview" class="file-input-preview" alt="Pratinjau Banner">
                        </div>
                    </div>

                    <!-- Deskripsi Lengkap -->
                    <div class="form-group col-span-2">
                        <label for="description" class="form-label">Deskripsi Lengkap Kegiatan *</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Tuliskan secara lengkap deskripsi acara, syarat pendaftaran, tautan registrasi, dll." required><?php echo isset($_POST['description']) ? sanitize($_POST['description']) : ''; ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-secondary">Reset Form</button>
                    <button type="submit" name="submit" class="btn-primary" style="width: auto; padding: 12px 30px;">Simpan Event</button>
                </div>
            </form>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Smart Event Campus. Universitas Potensi Utama.</p>
        </div>
    </footer>

    <script>
        // Real-time image upload preview
        const imageInput = document.getElementById('image-input');
        const imagePreview = document.getElementById('image-preview');
        const uploadInstruction = document.getElementById('upload-instruction');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                    imagePreview.style.display = 'block';
                    uploadInstruction.textContent = `File terpilih: ${file.name}`;
                });
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                uploadInstruction.textContent = 'Klik atau seret gambar ke sini (JPG, PNG, WEBP maks. 2MB)';
            }
        });
    </script>
</body>
</html>
