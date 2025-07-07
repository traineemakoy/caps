<?php
// 🔐 Start session and DB connection
include("../includes/db.php");

// ⚠️ Security check kung naka-login ang registrar
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Unauthorized access'); window.location.href='login.php';</script>";
    exit;
}

// 🧠 Fetch filters
$filter_type = $_GET['user_type'] ?? '';
$user_fullname = $_GET['user_fullname'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// 🔍 Build main query
$query = "SELECT * FROM audit_trail WHERE 1";

// 🔎 Apply filters kung meron
if (!empty($filter_type)) {
    $query .= " AND user_type = '$filter_type'";
}
if (!empty($user_fullname)) {
    $query .= " AND user_fullname = '$user_fullname'";
}
if (!empty($date_from) && !empty($date_to)) {
    $query .= " AND DATE(timestamp) BETWEEN '$date_from' AND '$date_to'";
}

$query .= " ORDER BY timestamp DESC";
$result = $conn->query($query);
?>

<h2>📜 Audit Trail</h2>

<!-- 🔧 Filters Form -->
<form method="get">
    <input type="hidden" name="page" value="audit_trail">

    <!-- 🎯 Filter by user type -->
    <label>User Type:</label>
    <select name="user_type">
        <option value="">All</option>
        <option value="registrar" <?= ($filter_type === 'registrar') ? 'selected' : '' ?>>Registrar</option>
        <option value="faculty" <?= ($filter_type === 'faculty') ? 'selected' : '' ?>>Faculty</option>
    </select>

    <!-- 👤 Filter by specific full name -->
    <label>User:</label>
    <select name="user_fullname">
        <option value="">All</option>
        <?php
        // 💡 Kuhanin lahat ng unique full names mula sa audit trail
        $users = $conn->query("SELECT DISTINCT user_fullname FROM audit_trail ORDER BY user_fullname ASC");
        while ($u = $users->fetch_assoc()):
            $selected = ($u['user_fullname'] === $user_fullname) ? 'selected' : '';
            echo "<option value='".htmlspecialchars($u['user_fullname'])."' $selected>".htmlspecialchars($u['user_fullname'])."</option>";
        endwhile;
        ?>
    </select>

    <!-- 📆 Filter by date range -->
    <label>Date Range:</label>
    <input type="date" name="from" value="<?= $date_from ?>">
    <input type="date" name="to" value="<?= $date_to ?>">

    <!-- 🔘 Submit filter -->
    <button type="submit">🔎 Filter</button>
</form>

<br>

<!-- 📋 Audit Log Table -->
<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>User</th>
            <th>User Type</th>
            <th>Activity</th>
            <th>Date & Time</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['user_fullname']) ?></td>
                    <td><?= ucfirst($row['user_type']) ?></td>
                    <td><?= htmlspecialchars($row['activity']) ?></td>
                    <td><?= date("M d, Y h:i A", strtotime($row['timestamp'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No logs found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
