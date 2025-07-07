<?php
include("includes/db.php");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = $_POST['student_number'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $phone = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];
    $term = $_POST['term'];
    $transferee = $_POST['transferee'];
    $irregular = $_POST['irregular'];

    // Password match check
    if ($password !== $repeat_password) {
        $errors[] = "⚠ Passwords do not match.";
    }

    // Check if student number already pending
    $check = $conn->prepare("SELECT * FROM pending_students WHERE student_number = ?");
    $check->bind_param("s", $student_number);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "⚠ Student number already submitted for approval.";
    }

    // File upload
    if ($_FILES['enrollment_receipt']['error'] === 0) {
        $tmp_name = $_FILES['enrollment_receipt']['tmp_name'];
        $original_name = basename($_FILES['enrollment_receipt']['name']);
        $filename = time() . "_" . $original_name;
        $upload_path = "uploads/" . $filename;
        move_uploaded_file($tmp_name, $upload_path);
    } else {
        $errors[] = "⚠ Enrollment receipt is required.";
    }

    // If no errors, insert into pending_students
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO pending_students 
            (student_number, first_name, last_name, middle_name, password, course, year_level, section, transferee, irregular, enrollment_receipt, phone_number, email, term)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssssssssssss", $student_number, $first_name, $last_name, $middle_name, $hashed_password, $course, $year_level, $section, $transferee, $irregular, $filename, $phone, $email, $term);
        $stmt->execute();

        echo "<script>
            alert('✅ Registration Successful!\\nPlease wait for the registrar to approve your account.');
            window.location.href = 'login.php';
        </script>";
        exit();
    }
}

// Fetch dropdowns
$courses = $conn->query("SELECT course_name FROM courses");
$sections = $conn->query("SELECT section_name FROM sections");
$year_levels = ['1st Year', '2nd Year', '3rd Year'];
$terms = ['1st Term', '2nd Term', '3rd Term'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="assets/css/register.css">

</head>
<body>

<div class="register-box">
    <h2>Student Registration</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e): ?>
                <p><?= $e ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="student_number" placeholder="Student Number" required>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="text" name="middle_name" placeholder="Middle Name">
        <input type="text" name="phone_number" placeholder="Contact Number" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="repeat_password" placeholder="Repeat Password" required>

        <select name="course" required>
            <option value="">Select Course</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?= $row['course_name'] ?>"><?= $row['course_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <select name="year_level" required>
            <option value="">Select Year Level</option>
            <?php foreach ($year_levels as $level): ?>
                <option value="<?= $level ?>"><?= $level ?></option>
            <?php endforeach; ?>
        </select>

        <select name="section" required>
            <option value="">Select Section</option>
            <?php while ($row = $sections->fetch_assoc()): ?>
                <option value="<?= $row['section_name'] ?>"><?= $row['section_name'] ?></option>
            <?php endwhile; ?>
        </select>

        <select name="transferee" required>
            <option value="">Are you a Transferee?</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>

        <select name="irregular" required>
            <option value="">Are you Irregular?</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>

        <select name="term" required>
            <option value="">Select Term</option>
            <?php foreach ($terms as $term): ?>
                <option value="<?= $term ?>"><?= $term ?></option>
            <?php endforeach; ?>
        </select>

        <label>Upload Enrollment Receipt:</label>
        <input type="file" name="enrollment_receipt" accept=".jpg,.jpeg,.png,.pdf" required>

        <button type="submit" class="btn">Register</button>
    </form>
</div>

</body>
</html>
