<?php
include('../includes/db.php');
include('../includes/log_module.php'); // ✅ Add log module


// Function to generate random reference number
function generateReference() {
    return 'REF-' . strtoupper(bin2hex(random_bytes(4)));
}

// ✅ Handle Approve
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $ref = generateReference();

    // Get visitor email
    $stmt = $conn->prepare("SELECT email FROM visitor_appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $email = $result->fetch_assoc()['email'];

    // Update appointment to Approved
    $stmt = $conn->prepare("UPDATE visitor_appointments SET status = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // ✅ Log activity
    log_activity_action($conn, "Approved visitor appointment with ID: $id");

    // Send email with reference number
    $subject = "Your Appointment Has Been Approved";
    $message = "Your appointment has been approved.\nReference Number: $ref";
    $headers = "From: registrar@example.com";

    mail($email, $subject, $message, $headers);

    echo "<script>
        alert('Appointment approved and reference number sent to $email');
        window.location.href = 'registrar_dashboard.php?page=appointments';
    </script>";
}

// ✅ Handle Reject
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    $stmt = $conn->prepare("UPDATE visitor_appointments SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // ✅ Log activity
    log_activity_action($conn, "Rejected visitor appointment with ID: $id");

    echo "<script>
        alert('Appointment rejected.');
        window.location.href = 'registrar_dashboard.php?page=appointments';
    </script>";
}

// Fetch all appointments
$result = $conn->query("SELECT * FROM visitor_appointments ORDER BY created_at DESC");
?>

<h2 style="text-align:center;">Visitor Appointment Requests</h2>

<table border="1" cellspacing="0" cellpadding="8" width="100%" style="background: white;">
    <tr style="background: #52ab98; color: white;">
        <th>Name</th>
        <th>School</th>
        <th>Status</th>
        <th>Email</th>
        <th>Purpose</th>
        <th>Preferred Date</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['school_last_attended']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= nl2br(htmlspecialchars($row['purpose'])) ?></td>
        <td><?= htmlspecialchars($row['preferred_date']) ?></td>
        <td>
            <?php if ($row['status'] === 'Pending'): ?>
                <a href="?page=appointments&approve=<?= $row['id'] ?>" onclick="return confirm('Approve this appointment?')">Approve</a> |
                <a href="?page=appointments&reject=<?= $row['id'] ?>" onclick="return confirm('Reject this appointment?')">Reject</a>
            <?php else: ?>
                <em><?= $row['status'] ?></em>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>Approved Appointments</h3>
<table border="1" cellspacing="0" cellpadding="8" width="100%">
    <tr style="background: #f2f2f2;">
        <th>Name</th>
        <th>Email</th>
        <th>School</th>
        <th>Purpose</th>
        <th>Date</th>
        <th>Status</th>
    </tr>
    <?php
    $approved = $conn->query("SELECT * FROM visitor_appointments WHERE status = 'Approved' ORDER BY preferred_date ASC");
    while ($row = $approved->fetch_assoc()):
    ?>
    <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['school_last_attended']) ?></td>
        <td><?= nl2br(htmlspecialchars($row['purpose'])) ?></td>
        <td><?= htmlspecialchars($row['preferred_date']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<link rel="stylesheet" href="code_layout.css">
