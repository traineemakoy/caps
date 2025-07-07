<?php
include('../includes/db.php');
$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    echo "Unauthorized access.";
    exit();
}
?>

<h2>📋 Activity Log</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Timestamp</th>
        <th>Activity</th>
    </tr>
    <?php
    $stmt = $conn->prepare("SELECT activity, timestamp FROM faculty_logs WHERE faculty_number = ? ORDER BY timestamp DESC");
    $stmt->bind_param("s", $faculty_number);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()):
    ?>
    <tr>
        <td><?= htmlspecialchars($row['timestamp']) ?></td>
        <td><?= htmlspecialchars($row['activity']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>


<link rel="stylesheet" href="../../assets/css/dashboard.css">
