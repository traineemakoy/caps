<?php
// login_fingerprint.php
include('../includes/db.php');


$response = null;
$data = null;
$faculty_number = $_SESSION['faculty_number'] ?? null;

// 🔄 Handle fingerprint scan
if (isset($_POST['trigger_scan'])) {
    $response = file_get_contents("http://localhost:5000/scan");
    $data = json_decode($response, true);
    $faculty_number = $data['faculty_number'] ?? $faculty_number;
}

// ✅ Handle customize_out update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customize_out']) && $faculty_number) {
    $custom_time = $_POST['custom_time'];

    $stmt = $conn->prepare("UPDATE attendance_log SET customize_out = ? WHERE faculty_number = ? AND date = CURDATE()");
    $stmt->bind_param("ss", $custom_time, $faculty_number);
    $stmt->execute();

    $custom_msg = "✔ Customized time-out set to: <strong>$custom_time</strong>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Fingerprint Login</title>
    <link rel="stylesheet" href="css/login_fingerprint.css">
</head>
<body>

<div class="card">
    <h2>Faculty Log Presence</h2>

    <form method="POST">
        <button type="submit" name="trigger_scan">🟢 Trigger Scan</button>
    </form>

    <?php if ($data): ?>
        <div class="status <?= strpos($data['status'], 'Time') !== false ? 'success' : 'fail' ?>">
            <?= htmlspecialchars($data['status']) ?>
        </div>

        <?php if (!empty($data['faculty_number'])): ?>
            <div class="faculty">Faculty Number: <strong><?= htmlspecialchars($data['faculty_number']) ?></strong></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($faculty_number)): ?>
        <hr>
        <h3>Customize Time-Out</h3>
        <?php if (!empty($custom_msg)) echo "<p style='color: green; font-weight: bold;'>$custom_msg</p>"; ?>
        <form method="POST">
            <input type="time" name="custom_time" required>
            <button type="submit" name="customize_out">📝 Set Time</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
