

<?php
include("../includes/db.php");


$student_number = $_SESSION['student_number'] ?? null;
if (!$student_number) die("Unauthorized access.");

$message = "";

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = $_POST['term'];
    $school_year = $_POST['school_year'];

    // Check if already requested
    $check = $conn->prepare("SELECT * FROM registrar_clearance WHERE student_number = ? AND term = ? AND school_year = ?");
    $check->bind_param("sss", $student_number, $term, $school_year);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "⚠️ You've already submitted a clearance request for this term.";
    } else {
        // Insert new request with default pending status
        $stmt = $conn->prepare("INSERT INTO registrar_clearance (student_number, term, school_year, registrar, school_director, admin_officer, status)
                                VALUES (?, ?, ?, 'Pending', 'Pending', 'Pending', 'Pending')");
        $stmt->bind_param("sss", $student_number, $term, $school_year);
        $stmt->execute();
        $message = "✅ Clearance request submitted!";
    }
}

// Fetch previous requests
$history_query = $conn->prepare("SELECT * FROM registrar_clearance WHERE student_number = ? ORDER BY id DESC");
$history_query->bind_param("s", $student_number);
$history_query->execute();
$history_result = $history_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clearance Request</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>


<h3>🧹 Request Institutional Clearance</h3>

<?php if ($message): ?>
    <p style="color: <?= strpos($message, '✅') !== false ? 'green' : 'red' ?>"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <label>Term:</label>
    <select name="term" required>
        <option value="1st Term">1st Term</option>
        <option value="2nd Term">2nd Term</option>
        <option value="3rd Term">3rd Term</option>
    </select><br><br>

    <label>School Year:</label>
    <input type="text" name="school_year" placeholder="e.g. 2025-2026" required><br><br>

    <button type="submit">Submit Request</button>
</form>

<hr>

<h4>📋 Your Clearance Request History</h4>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Term</th>
        <th>School Year</th>
        <th>Registrar</th>
        <th>School Director</th>
        <th>Admin Officer</th>
        <th>Status</th>
    </tr>
    <?php while ($row = $history_result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['term']) ?></td>
            <td><?= htmlspecialchars($row['school_year']) ?></td>
            <td><?= $row['registrar'] ?></td>
            <td><?= $row['school_director'] ?></td>
            <td><?= $row['admin_officer'] ?></td>
            <td><strong><?= $row['status'] ?></strong></td>
        </tr>
    <?php endwhile; ?>
</table>
