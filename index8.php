<?php
/**
 * Smart Event Campus - Edit Event Page
 */

require_once 'config.php';
check_admin_auth();

$error = '';
$success = '';

// Check if event ID is supplied
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['action_error'] = 'ID Event tidak valid atau tidak diberikan!';
    header('Location: admin_dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

// Fetch current event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        $_SESSION['action_error'] = 'Event tidak ditemukan!';
        header('Location: admin_dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    die("Kesalahan database: " . $e->getMessage());
}

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
        $image_path = $event['image_path']; // Keep existing image path as default

        // Handle Image Upload if a new file is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileSize = $_FILES['image']['size'];
            $fileType = $_FILES['image']['type'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 2 * 1024 * 1024) {
                    $uploadFileDir = __DIR__ . '/assets/images/';
                    
                    // Create directory if not exists
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    
                    $newFileName = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        // Delete previous image file if it exists and is different
                        if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])) {
                            unlink(__DIR__ . '/' . $event['image_path']);
                        }
                        
                        $image_path = 'assets/images/' . $newFileName;
                    } else {
                        $error = 'Ada masalah saat memindahkan file gambar yang baru.';
                    }
                } else {
                    $error = 'Ukuran file gambar baru terlalu besar. Maksimal 2MB!';
                }
            } else {
                $error = 'Format file gambar tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP!';
            }
        }

        // If no error occurred, update in database
        if (empty($error)) {
            try {
                $stmtUpdate = $pdo->prepare("
                    UPDATE events SET 
                        title = :title, 
                        category = :category, 
                        description = :description, 
                        event_date = :event_date, 
                        event_time = :event_time, 
                        location = :location, 
                        speaker = :speaker, 
                        image_path = :image_path, 
                        status = :status 
                    WHERE id = :id
                ");
                
                $stmtUpdate->execute([
                    'title'       => $title,
                    'category'    => $category,
                    'description' => $description,
                    'event_date'  => $event_date,
                    'event_time'  => $event_time,
                    'location'    => $location,
                    'speaker'     => $speaker,
                    'image_path'  => $image_path,
                    'status'      => $status,
                    'id'          => $id
                ]);

                $_SESSION['action_success'] = 'Event "' . $title . '" berhasil diperbarui!';
                header('Location: admin_dashboard.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui database: ' . $e->getMessage();
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
    <title>Edit Event - Smart Event Campus</title>
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
                <span>Ubah Detail Event</span>
                <a href="admin_dashboard.php" class="btn-secondary" style="font-size: 0.85rem; padding: 8px 16px;">Batal & Kembali</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-message error">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="event_edit.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <!-- Judul Event -->
                    <div class="form-group col-span-2">
                        <label for="title" class="form-label">Judul Kegiatan / Event *</label>
                        <input type="text" name="title" id="title" class="form-control" required value="<?php echo sanitize(isset($_POST['title']) ? $_POST['title'] : $event['title']); ?>">
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label for="category" class="form-label">Kategori *</label>
                        <select name="category" id="category" class="form-control" required style="background-color: #0b0f19;">
                            <?php $selectedCat = isset($_POST['category']) ? $_POST['category'] : $event['category']; ?>
                            <option value="seminar" <?php echo $selectedCat === 'seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="workshop" <?php echo $selectedCat === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="lomba" <?php echo $selectedCat === 'lomba' ? 'selected' : ''; ?>>Lomba</option>
                            <option value="pelatihan" <?php echo $selectedCat === 'pelatihan' ? 'selected' : ''; ?>>Pelatihan</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="form-label">Status Event *</label>
                        <select name="status" id="status" class="form-control" required style="background-color: #0b0f19;">
                            <?php $selectedStatus = isset($_POST['status']) ? $_POST['status'] : $event['status']; ?>
                            <option value="upcoming" <?php echo $selectedStatus === 'upcoming' ? 'selected' : ''; ?>>Upcoming (Akan Datang)</option>
                            <option value="ongoing" <?php echo $selectedStatus === 'ongoing' ? 'selected' : ''; ?>>Ongoing (Sedang Berjalan)</option>
                            <option value="completed" <?php echo $selectedStatus === 'completed' ? 'selected' : ''; ?>>Completed (Selesai)</option>
                        </select>
                    </div>

                    <!-- Narasumber / Pembicara -->
                    <div class="form-group">
                        <label for="speaker" class="form-label">Pembicara / Pemateri *</label>
                        <input type="text" name="speaker" id="speaker" class="form-control" required value="<?php echo sanitize(isset($_POST['speaker']) ? $_POST['speaker'] : $event['speaker']); ?>">
                    </div>

                    <!-- Tempat / Lokasi -->
                    <div class="form-group">
                        <label for="location" class="form-label">Tempat / Lokasi *</label>
                        <input type="text" name="location" id="location" class="form-control" required value="<?php echo sanitize(isset($_POST['location']) ? $_POST['location'] : $event['location']); ?>">
                    </div>

                    <!-- Tanggal -->
                    <div class="form-group">
                        <label for="event_date" class="form-label">Tanggal Pelaksanaan *</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required value="<?php echo isset($_POST['event_date']) ? $_POST['event_date'] : $event['event_date']; ?>">
                    </div>

                    <!-- Waktu -->
                    <div class="form-group">
                        <label for="event_time" class="form-label">Waktu Mulai *</label>
                        <input type="time" name="event_time" id="event_time" class="form-control" required value="<?php echo isset($_POST['event_time']) ? $_POST['event_time'] : substr($event['event_time'], 0, 5); ?>">
                    </div>

                    <!-- Banner Image Upload -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Ganti Banner Event (Biarkan kosong jika tidak ingin diubah)</label>
                        <div class="file-input-wrapper">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);" id="upload-instruction">
                                Klik atau seret gambar baru ke sini untuk mengganti (JPG, PNG, WEBP maks. 2MB)
                            </div>
                            <input type="file" name="image" id="image-input" accept="image/*">
                            
                            <?php if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])): ?>
                                <img src="<?php echo sanitize($event['image_path']); ?>" id="image-preview" class="file-input-preview" alt="Pratinjau Banner" style="display: block;">
                            <?php else: ?>
                                <img id="image-preview" class="file-input-preview" alt="Pratinjau Banner">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Deskripsi Lengkap -->
                    <div class="form-group col-span-2">
                        <label for="description" class="form-label">Deskripsi Lengkap Kegiatan *</label>
                        <textarea name="description" id="description" class="form-control" required><?php echo sanitize(isset($_POST['description']) ? $_POST['description'] : $event['description']); ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn-primary" style="width: auto; padding: 12px 30px;">Perbarui Event</button>
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
            }
        });
    </script>
</body>
</html>
