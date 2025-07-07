<?php
session_start();
include("includes/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $firstChar = isset($username[0]) ? $username[0] : '';

    $stmt = null;
    $redirect = "";
    $session_key = "";

    if ($firstChar === '@') {
        $stmt = $conn->prepare("SELECT * FROM registrar WHERE username = ?");
        $redirect = "registrar/registrar_dashboard.php";
        $session_key = "username";
    } elseif ($firstChar === 'f') {
        $stmt = $conn->prepare("SELECT * FROM faculty WHERE username = ?");
        $redirect = "faculty/faculty_dashboard.php";
        $session_key = "faculty_number";
    } elseif (is_numeric($firstChar)) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_number = ?");
        $redirect = "student/student_dashboard.php";
        $session_key = "student_number";
    } else {
        $error = "Invalid username format.";
    }

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION[$session_key] = $user[$session_key];
                $_SESSION['username'] = $username;

                // Set full_name based on user type
                if ($firstChar === '@') {
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['registrar_name'] = $user['full_name'];
                    $_SESSION['registrar_id'] = $user['id'];
                } elseif ($firstChar === 'f') {
                    $_SESSION['full_name'] = $user['full_name'];
                } elseif (is_numeric($firstChar)) {
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                }

                // ✅ Analytics Logging
                $user_type = ($firstChar === '@') ? 'Registrar' : (($firstChar === 'f') ? 'Faculty' : 'Student');
                $user_fullname = $_SESSION['full_name'] ?? 'Unknown';

                // Insert into user_sessions table
                $log_stmt = $conn->prepare("INSERT INTO user_sessions (user_type, user_fullname, login_time) VALUES (?, ?, NOW())");
                $log_stmt->bind_param("ss", $user_type, $user_fullname);
                $log_stmt->execute();
                $_SESSION['session_log_id'] = $conn->insert_id;

                // Insert into login_analytics table
                $stmtLog = $conn->prepare("INSERT INTO login_analytics (username, user_type, full_name, login_time) VALUES (?, ?, ?, NOW())");
                $stmtLog->bind_param("sss", $username, $user_type, $user_fullname);
                $stmtLog->execute();

                header("Location: $redirect");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>School Portal Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="title">
        <h1>SCHOOL PORTAL</h1>
        <p><strong>Infotech College of Arts and Science</strong></p>
    </div>

    <div class="container">
        <div class="logo-section">
            <img src="assets/img/logo_icas.jpg" alt="ICAS Logo">
        </div>
        <div class="login-section">
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username / Student Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="links">
                <p><a href="#">Forgot Password?</a></p>
                <p>New student? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
