<?php
/**
 * Smart Event Campus - Delete Event Handler
 */

require_once 'config.php';
check_admin_auth();

// Check if event ID is supplied
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['action_error'] = 'ID Event tidak valid atau tidak diberikan!';
    header('Location: admin_dashboard.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    // 1. Fetch current event to get image path (for unlinking the image file)
    $stmtFetch = $pdo->prepare("SELECT image_path, title FROM events WHERE id = :id LIMIT 1");
    $stmtFetch->execute(['id' => $id]);
    $event = $stmtFetch->fetch();

    if (!$event) {
        $_SESSION['action_error'] = 'Event tidak ditemukan!';
        header('Location: admin_dashboard.php');
        exit;
    }

    // Delete image file from filesystem if it exists
    if (!empty($event['image_path']) && file_exists(__DIR__ . '/' . $event['image_path'])) {
        unlink(__DIR__ . '/' . $event['image_path']);
    }

    // 2. Delete event from database
    $stmtDelete = $pdo->prepare("DELETE FROM events WHERE id = :id");
    $stmtDelete->execute(['id' => $id]);

    $_SESSION['action_success'] = 'Event "' . $event['title'] . '" berhasil dihapus!';
} catch (PDOException $e) {
    $_SESSION['action_error'] = 'Gagal menghapus event dari database: ' . $e->getMessage();
}

header('Location: admin_dashboard.php');
exit;
?>
