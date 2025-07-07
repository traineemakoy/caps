<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include("../includes/db.php");
include("../includes/log_module.php");

$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    echo "Unauthorized access.";
    exit();
}

$uploadDir = "../../uploads/announcements/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// ✅ POST NEW ANNOUNCEMENT
if (isset($_POST['post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $imageName = 'announcement_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $uploadDir . $imageName);
        }
    }

    $stmt = $conn->prepare("INSERT INTO faculty_announcements (faculty_number, title, content, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $faculty_number, $title, $content, $imageName);
    $stmt->execute();
    log_activity_action($conn, "Posted a new announcement: $title");

    header("Location: ?page=announcement&posted=1");
    exit();
}

// ✅ DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("SELECT image_path FROM faculty_announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['image_path']) {
        $filePath = $uploadDir . $row['image_path'];
        if (file_exists($filePath)) unlink($filePath);
    }

    $stmt = $conn->prepare("DELETE FROM faculty_announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    log_activity_action($conn, "Deleted announcement ID: $id");

    header("Location: ?page=announcement&deleted=1");
    exit();
}

// ✅ EDIT
if (isset($_POST['edit'])) {
    $id = $_POST['announcement_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $newImage = null;

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $newImage = 'announcement_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $uploadDir . $newImage);

            // Delete old image
            $stmt = $conn->prepare("SELECT image_path FROM faculty_announcements WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $oldResult = $stmt->get_result();
            $oldRow = $oldResult->fetch_assoc();
            if ($oldRow && $oldRow['image_path']) {
                unlink($uploadDir . $oldRow['image_path']);
            }

            $stmt = $conn->prepare("UPDATE faculty_announcements SET title = ?, content = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $newImage, $id);
        }
    } else {
        $stmt = $conn->prepare("UPDATE faculty_announcements SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $id);
    }

    $stmt->execute();
    log_activity_action($conn, "Edited announcement: $title");

    header("Location: ?page=announcement&updated=1");
    exit();
}
?>

<!-- ✅ UI -->
<h2>📢 Post New Announcement</h2>

<?php if (isset($_GET['posted'])): ?>
    <p style="color:green;">📣 Successfully posted.</p>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <p style="color:green;">🗑️ Successfully deleted.</p>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <p style="color:green;">✏️ Successfully updated.</p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="announcement_id" id="announcement_id">
    <input type="text" name="title" id="title" placeholder="Title" required style="width:100%; padding:10px;"><br><br>
    <textarea name="content" id="content" placeholder="Write your announcement here..." required style="width:100%; padding:10px;"></textarea><br><br>
    <input type="file" name="image" accept="image/*"><br><br>
    <button type="submit" name="post" id="postBtn">📣 Post Announcement</button>
    <button type="submit" name="edit" id="editBtn" style="display:none;">✏️ Update</button>
</form>

<hr>

<h3>📄 Announcements</h3>
<?php
$stmt = $conn->prepare("
    SELECT fa.*, f.full_name 
    FROM faculty_announcements fa
    JOIN faculty f ON fa.faculty_number = f.faculty_number
    WHERE fa.faculty_number = ?
    ORDER BY fa.date_posted DESC
");
$stmt->bind_param("s", $faculty_number);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()):
?>
<div class="announcement" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
    <strong><?= htmlspecialchars($row['title']) ?></strong>
    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
    <?php if ($row['image_path']): ?>
        <img src="../../uploads/announcements/<?= $row['image_path'] ?>" style="max-width: 300px;">
    <?php endif; ?>
    <p>📅 <?= date("M d, Y h:i A", strtotime($row['date_posted'])) ?><br>
    🧑 Posted by: <strong><?= htmlspecialchars($row['full_name']) ?></strong></p>
    <div class="actions">
        <a href="#" onclick="editAnnouncement(`<?= $row['id'] ?>`, `<?= htmlspecialchars($row['title']) ?>`, `<?= htmlspecialchars($row['content']) ?>`)">✏️ Edit</a>
        <a href="?page=announcement&delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
    </div>
</div>
<?php endwhile; ?>

<script>
function editAnnouncement(id, title, content) {
    document.getElementById('announcement_id').value = id;
    document.getElementById('title').value = title;
    document.getElementById('content').value = content;
    document.getElementById('postBtn').style.display = 'none';
    document.getElementById('editBtn').style.display = 'inline-block';
}
</script>
