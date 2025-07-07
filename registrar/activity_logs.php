<?php
include("../includes/db.php");
include("../includes/functions.php");



$query = "SELECT * FROM activity_logs WHERE user_type = 'registrar' ORDER BY timestamp DESC";
$result = $conn->query($query);
?>

<div class="main-content">
    <h2>📋 Registrar Activity Logs</h2>
    <table style="border-collapse: collapse; width: 100%; background: #fff;">
        <tr style="background: #f2f2f2;">
            <th style="padding: 10px;">#</th>
            <th style="padding: 10px;">Registrar ID</th>
            <th style="padding: 10px;">Activity</th>
            <th style="padding: 10px;">Timestamp</th>   
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td style="padding: 10px;"><?= $row['id'] ?></td>
            <td style="padding: 10px;"><?= $row['user_id'] ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($row['activity']) ?></td>
            <td style="padding: 10px;"><?= date("Y-m-d h:i A", strtotime($row['timestamp'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>


<link rel="stylesheet" href="code_layout.css">