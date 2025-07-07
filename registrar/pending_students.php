<?php
// 👉 Kinukuha yung database connection at logging functions
include('../includes/db.php');
include('../includes/log_module.php');

// 👉 Start session kung hindi pa naka-start
if (session_status() === PHP_SESSION_NONE) session_start();

// 👉 Kinukuha yung registrar ID kung naka-login
$registrar_id = $_SESSION['registrar_id'] ?? null;

// 👉 Kinukuha ang alert message for feedback (tapos ide-delete na)
$alert = $_SESSION['alert'] ?? '';
unset($_SESSION['alert']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Students</title>

    <!-- ✅ External CSS link for layout and design (same folder as this file) -->
    <link rel="stylesheet" href="code_layout.css">
</head>
<body>

    <!-- 👉 Heading ng page -->
    <h2>📋 Pending Student Accounts</h2>

    <!-- 👉 Kung may alert (success/fail message), ipakita -->
    <?php if (!empty($alert)) : ?>
        <div class="alert"><?= $alert ?></div>
    <?php endif; ?>

    <!-- 👉 Simula ng table structure -->
    <table>
        <tr>
            <!-- 👉 Table column headers -->
            <th>Student Number</th>
            <th>Name</th>
            <th>Middle Name</th>
            <th>Course</th>
            <th>Year Level</th>
            <th>Section</th>
            <th>Term</th>
            <th>Transferee</th>
            <th>Irregular</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Enrollment Receipt</th>
            <th>Actions</th>
        </tr>

        <?php
        // 👉 Query para kunin lahat ng pending students
        $result = $conn->query("SELECT * FROM pending_students");

        // 👉 Check kung may results
        if ($result->num_rows > 0) {
            while ($student = $result->fetch_assoc()) {
                // 👉 Display ng bawat student sa table row
                echo "<tr>
                        <td>{$student['student_number']}</td>
                        <td>{$student['last_name']}, {$student['first_name']}</td>
                        <td>{$student['middle_name']}</td>
                        <td>{$student['course']}</td>
                        <td>{$student['year_level']}</td>
                        <td>{$student['section']}</td>
                        <td>{$student['term']}</td>
                        <td>{$student['transferee']}</td>
                        <td>{$student['irregular']}</td>
                        <td>{$student['phone_number']}</td>
                        <td>{$student['email']}</td>
                        <td><a href='../uploads/{$student['enrollment_receipt']}' target='_blank'>View</a></td>
                        <td>
                            <!-- 👉 Form to approve student -->
                            <form action='approve_student.php' method='POST'>
                                <input type='hidden' name='pending_id' value='{$student['pending_id']}'>
                                <button type='submit'>Approve</button>
                            </form>

                            <!-- 👉 Form to reject student -->
                            <form action='reject_student.php' method='POST'>
                                <input type='hidden' name='pending_id' value='{$student['pending_id']}'>
                                <button type='submit' onclick=\"return confirm('Reject this student?')\">Reject</button>
                            </form>
                        </td>
                    </tr>";
            }
        } else {
            // 👉 Kapag walang pending students, ito ang ipapakita
            echo "<tr><td colspan='13'>No pending students found.</td></tr>";
        }
        ?>
    </table>

</body>
</html>
