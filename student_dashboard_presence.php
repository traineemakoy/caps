<?php
include('../includes/db.php');

// Get today's date by default
$today = date('Y-m-d');
$filter_date = $_GET['date'] ?? $today;

// Fetch presence logs for the selected date
$stmt = $conn->prepare("
    SELECT f.full_name, a.time_in, a.time_out, a.customize_out
    FROM attendance_log a
    JOIN faculty f ON a.faculty_number = f.faculty_number
    WHERE a.date = ?
    ORDER BY f.full_name
");
$stmt->bind_param("s", $filter_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- 🔍 Filter Form -->
<form method="GET">
    <label for="date">Select Date:</label>
    <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">
    <button type="submit">Filter</button>
</form>

<h2>Faculty Presence for <?= htmlspecialchars($filter_date) ?></h2>

<table border="1" cellpadding="10">
    <tr style="background-color: darkgreen; color: white;">
        <th>Name</th>
        <th>In-Campus</th>
        <th>Out</th>
        <th>Available Until</th>
        <th>Status</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <?php
        $status = is_null($row['time_out']) ? '🟢' : '🔴';
    ?>
    <tr>
        <td><?= $row['full_name'] ?></td>
        <td><?= $row['time_in'] ?? '—' ?></td>
        <td><?= $row['time_out'] ?? '—' ?></td>
        <td><?= $row['customize_out'] ?? '—' ?></td>
        <td style="text-align:center; font-size: 20px;"><?= $status ?></td>
    </tr>
    <?php endwhile; ?>
</table>
