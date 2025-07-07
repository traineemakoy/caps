<?php
include('../includes/db.php');

// 📌 Fetch registrar announcements
$registrarResult = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");

// 📌 Fetch faculty announcements
$facultyResult = $conn->query("SELECT * FROM faculty_announcements ORDER BY date_posted DESC");
?>

<h2>📣 Latest Announcements</h2>

<!-- ✅ Registrar Announcements -->
<?php if ($registrarResult->num_rows > 0): ?>
    <h3>📋 Registrar Announcements</h3>
    <?php while ($row = $registrarResult->fetch_assoc()): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h4><?= htmlspecialchars($row['title']) ?></h4>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <small>🧑 Posted by: <strong><?= htmlspecialchars($row['created_by']) ?></strong> — <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No registrar announcements yet.</p>
<?php endif; ?>

<!-- ✅ Faculty Announcements -->
<?php if ($facultyResult->num_rows > 0): ?>
    <h3>👨‍🏫 Faculty Announcements</h3>
    <?php while ($row = $facultyResult->fetch_assoc()): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h4><?= htmlspecialchars($row['title']) ?></h4>
            <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
            <?php if (!empty($row['image_path'])): ?>
                <img src="../../uploads/announcements/<?= htmlspecialchars($row['image_path']) ?>" alt="Announcement Image" style="max-width: 200px; display: block; margin-top: 10px;">
            <?php endif; ?>
            <small>🧑 Posted by Faculty #: <strong><?= htmlspecialchars($row['faculty_number']) ?></strong> — <?= date('M d, Y h:i A', strtotime($row['date_posted'])) ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No faculty announcements yet.</p>
<?php endif; ?>
