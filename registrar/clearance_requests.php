<?php
include('../includes/db.php');
include('../includes/log_module.php');
$registrar_id = $_SESSION['registrar_id'] ?? null;

$term_filter = $_GET['term'] ?? '';
$school_year_filter = $_GET['school_year'] ?? '';
$section_filter = $_GET['section'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = $_POST['student_number'];
    $term = $_POST['term'];
    $school_year = $_POST['school_year'];
    $role = $_POST['role'];
    $value = $_POST['value'];

    $update = $conn->prepare("UPDATE registrar_clearance SET $role = ? WHERE student_number = ? AND term = ? AND school_year = ?");
    $update->bind_param("ssss", $value, $student_number, $term, $school_year);
    $update->execute();

    $check = $conn->prepare("SELECT registrar, school_director, admin_officer FROM registrar_clearance WHERE student_number = ? AND term = ? AND school_year = ?");
    $check->bind_param("sss", $student_number, $term, $school_year);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res['registrar'] === 'Cleared' && $res['school_director'] === 'Cleared' && $res['admin_officer'] === 'Cleared') {
        $conn->query("UPDATE registrar_clearance SET status = 'Cleared' WHERE student_number = '$student_number' AND term = '$term' AND school_year = '$school_year'");
    } else {
        $conn->query("UPDATE registrar_clearance SET status = 'Pending' WHERE student_number = '$student_number' AND term = '$term' AND school_year = '$school_year'");
    }

    // ✅ Final corrected log message
    log_activity_action($conn, "Updated $role clearance to '$value' for student: $student_number, $term $school_year");

    header("Location: registrar_dashboard.php?page=clearance_requests&updated=1");
    exit();
}


$query = "SELECT rc.*, st.first_name, st.last_name, st.section AS section_name FROM registrar_clearance rc JOIN students st ON rc.student_number = st.student_number WHERE 1=1";
$params = [];
$types = '';

if ($term_filter !== '') {
    $query .= " AND rc.term = ?";
    $params[] = $term_filter;
    $types .= 's';
}
if ($school_year_filter !== '') {
    $query .= " AND rc.school_year = ?";
    $params[] = $school_year_filter;
    $types .= 's';
}
if ($section_filter !== '') {
    $query .= " AND st.section = ?";
    $params[] = $section_filter;
    $types .= 's';
}

$query .= " ORDER BY rc.id DESC";
$stmt = $conn->prepare($query);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

$sections = $conn->query("SELECT DISTINCT section FROM students ORDER BY section ASC");
$years = $conn->query("SELECT DISTINCT school_year FROM registrar_clearance ORDER BY school_year DESC");
$terms = ['1st Term', '2nd Term', '3rd Term'];
?>

<h3>📋 Institutional Clearance Requests</h3>
<?php if (isset($_GET['updated'])): ?>
    <p style="color: green;">✅ Clearance updated!</p>
<?php endif; ?>

<form method="GET" style="margin-bottom: 20px;">
    <input type="hidden" name="page" value="clearance_requests">
    <select name="term">
        <option value="">All Terms</option>
        <?php foreach ($terms as $term): ?>
            <option value="<?= $term ?>" <?= $term_filter === $term ? 'selected' : '' ?>><?= $term ?></option>
        <?php endforeach; ?>
    </select>
    <select name="school_year">
        <option value="">All Years</option>
        <?php while ($row = $years->fetch_assoc()): ?>
            <option value="<?= $row['school_year'] ?>" <?= $school_year_filter === $row['school_year'] ? 'selected' : '' ?>><?= $row['school_year'] ?></option>
        <?php endwhile; ?>
    </select>
    <select name="section">
        <option value="">All Sections</option>
        <?php while ($row = $sections->fetch_assoc()): ?>
            <option value="<?= $row['section'] ?>" <?= $section_filter === $row['section'] ? 'selected' : '' ?>><?= $row['section'] ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<style>
    table.clearance-table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        margin-top: 15px;
    }
    table.clearance-table th {
        background-color: #f0f0f0;
        padding: 10px;
        border: 1px solid #ccc;
    }
    table.clearance-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
    }
    table.clearance-table tr:nth-child(even) {
        background-color: #fafafa;
    }
    table.clearance-table strong {
        color: green;
    }
</style>

<table class="clearance-table">
    <tr>
        <th>Student No.</th>
        <th>Name</th>
        <th>Section</th>
        <th>Term</th>
        <th>School Year</th>
        <th>Registrar</th>
        <th>School Director</th>
        <th>Admin Officer</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['student_number'] ?></td>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= htmlspecialchars($row['section_name']) ?></td>
            <td><?= $row['term'] ?></td>
            <td><?= $row['school_year'] ?></td>
            <td><?= $row['registrar'] ?></td>
            <td><?= $row['school_director'] ?></td>
            <td><?= $row['admin_officer'] ?></td>
            <td><strong><?= $row['status'] ?></strong></td>
            <td>
                <?php foreach (['registrar', 'school_director', 'admin_officer'] as $role): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="student_number" value="<?= $row['student_number'] ?>">
                        <input type="hidden" name="term" value="<?= $row['term'] ?>">
                        <input type="hidden" name="school_year" value="<?= $row['school_year'] ?>">
                        <input type="hidden" name="role" value="<?= $role ?>">
                        <select name="value">
                            <option <?= $row[$role] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option <?= $row[$role] === 'Cleared' ? 'selected' : '' ?>>Cleared</option>
                        </select>
                        <button type="submit">✔</button>
                    </form><br>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>


<link rel="stylesheet" href="code_layout.css">