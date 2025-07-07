<?php

include('../includes/db.php');
include('../includes/log_module.php');



$created_by = $_SESSION['registrar_name'] ?? $_SESSION['faculty_name'] ?? 'Unknown';
$upload_dir = "../uploads/";
$success = $error = "";

// 🔄 DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ?page=announcement&deleted=1");
    exit();
}

// 🔄 EDIT (FETCH DATA)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// 🔄 ADD or UPDATE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image_path = "";

    // 🖼️ Handle image upload kung meron
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_type, $allowed)) {
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image_path = "uploads/" . $image_name;
            
        }
    }

    // ✅ UPDATE MODE
    if (isset($_POST['update_id'])) {
        $id = $_POST['update_id'];
        if (!empty($image_path)) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, image_path = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $image_path, $id);
            log_activity_action($conn, "Updated announcement: $title");
            
        } else {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $content, $id);
            log_activity_action($conn, "Updated announcement: $title");
        }
        $stmt->execute();
        header("Location: ?page=announcement&updated=1");
        exit();
    } else {
        // ✅ ADD NEW
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, created_by, image_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $content, $created_by, $image_path);
            $stmt->execute();
            log_activity_action($conn, "Posted a new announcement: $title");
            header("Location: ?page=announcement&posted=1");
            
            exit();
        } else {
            $error = "❌ Please fill in all fields.";
        }
    }
}

// 📌 GET ALL ANNOUNCEMENTS
$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<!-- ✅ SUCCESS MESSAGES -->
<?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <p style="color:green;">🗑️ Announcement successfully deleted.</p>
<?php endif; ?>
<?php if (isset($_GET['posted']) && $_GET['posted'] == 1): ?>
    <p style="color:green;">📣 Announcement successfully posted.</p>
<?php endif; ?>
<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <p style="color:green;">💾 Announcement successfully updated.</p>
<?php endif; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<!-- ✅ ANNOUNCEMENT FORM -->
<h2><?= $edit_data ? "✏️ Edit Announcement" : "📢 Post New Announcement" ?></h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required style="width: 100%; padding: 8px;" value="<?= $edit_data['title'] ?? '' ?>"><br><br>
    <textarea name="content" rows="8" placeholder="Write your announcement here..." style="width: 100%; padding: 10px;" required><?= $edit_data['content'] ?? '' ?></textarea><br><br>
    <input type="file" name="image" accept="image/*"><br><br>
    <?php if ($edit_data): ?>
        <input type="hidden" name="update_id" value="<?= $edit_data['id'] ?>">
        <button type="submit" style="background: blue; color: white; padding: 10px 20px; border: none; border-radius: 5px;">💾 Update</button>
        <a href="registrar_dashboard.php?page=announcement" style="margin-left: 10px;">Cancel</a>
    <?php else: ?>
        <button type="submit" style="background: green; color: white; padding: 10px 20px; border: none; border-radius: 5px;">📣 Post Announcement</button>
    <?php endif; ?>
</form>

<hr>

<!-- 📋 ANNOUNCEMENT LIST -->
<h3>🗒️ Announcements</h3>
<?php while ($row = $result->fetch_assoc()): ?>
    <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
        <h4><?= htmlspecialchars($row['title']) ?></h4>
        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
        <?php if (!empty($row['image_path'])): ?>
            <img src="../<?= $row['image_path'] ?>" style="max-width: 300px; margin-top: 10px;">
        <?php endif; ?>
        <small>🧑 Posted by <strong><?= htmlspecialchars($row['created_by']) ?></strong> | <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></small><br><br>
        <a href="registrar_dashboard.php?page=announcement&edit=<?= $row['id'] ?>" onclick="return confirm('Edit this announcement?')">✏️ Edit</a> |
        <a href="registrar_dashboard.php?page=announcement&delete=<?= $row['id'] ?>" onclick="return confirm('Delete this announcement?')">🗑️ Delete</a>
    </div>
<?php endwhile; ?>


<link rel="stylesheet" href="code_layout.css">