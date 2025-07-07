<?php
// 👉 Start ng session para ma-access ang session variables
session_start();

// 👉 Check kung naka-login ang registrar, kung hindi ay babalik sa login page
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// 👉 Kunin ang full name ng registrar mula session, default kung wala
$registrar_full_name = $_SESSION['full_name'] ?? 'Registrar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrar Dashboard</title>
    <!-- 👉 External CSS file for dashboard layout -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

    <!-- 🔧 Sticky styled topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <strong>👨‍💼 Welcome, <?= htmlspecialchars($registrar_full_name) ?>!</strong>
        </div>
        <div class="topbar-center" id="datetime"></div>
        <form action="../logout.php" method="POST" class="topbar-right">
            <button class="logout-btn">Logout</button>
        </form>
    </div>


    <!-- 👉 Sidebar navigation buttons -->
    <div class="sidebar">
        <a href="?page=pending_students"><button>Pending Students</button></a>
        <a href="?page=student"><button>Student</button></a>
        <a href="?page=view_courses"><button>Course</button></a>
        <a href="?page=section_management"><button>Section</button></a>
        <a href="?page=professor"><button>Professors</button></a>
        <a href="?page=enroll_fingerprint"><button>Enroll Fingerprint</button></a>
        <a href="?page=subjects"><button>Subjects</button></a>
        <a href="?page=offer_subjects"><button>Open Subjects</button></a>
        <a href="?page=clearance_requests"><button>Clearance Requests</button></a>
        <a href="?page=announcement"><button>Announcements</button></a>
        <a href="?page=appointments"><button>Appointments</button></a>
        <a href="?page=audit_trail"><button>Audit Trail</button></a>
        <a href="?page=log_attendance"><button>Attendance Log</button></a>
        <a href="?page=analytics"><button>Analytics</button></a>


    </div>

    <!-- 👉 Main content area kung saan lilitaw ang laman ng page -->
    <div class="content" id="main-content">
        <?php
        // 👉 Script to display current time and update every second
        echo '<script>
            function updateTime() {
                const now = new Date();
                document.getElementById("datetime").innerText = now.toLocaleString();
            }
            setInterval(updateTime, 1000);
            window.onload = updateTime;
        </script>';

        // 👉 Handle POST requests or edit/delete actions for specific pages (code sigma fix)
        if (
            isset($_GET['page']) &&
            in_array($_GET['page'], ['professor', 'subjects']) &&
            ($_SERVER["REQUEST_METHOD"] === "POST" || isset($_GET['edit']) || isset($_GET['delete']))
        ) {
            include($_GET['page'] . ".php");
            exit(); // 👉 Stop execution after dynamic include
        }

        // 👉 Load the appropriate page content based on ?page=
        if (isset($_GET['page'])) {
            $page = $_GET['page'];

            // 👉 Secure list of allowed pages only
            $allowed_pages = [
                'pending_students',
                'student',
                'view_courses',
                'edit_course',
                'section_management',
                'professor',
                'enroll_fingerprint',
                'log_attendance',
                'subjects',
                'offer_subjects',
                'clearance_requests',
                'announcement',
                'appointments',
                'audit_trail',
                'analytics',
                'enrolled_students' // 
            ];

            // 👉 If page is allowed, include it; otherwise show error
            if (in_array($page, $allowed_pages)) {
                $path = "$page.php";

                // 👉 Check kung existing yung PHP file before include
                if (file_exists($path)) {
                    include($path);
                } else {
                    echo "<h3>Page not found.</h3>";
                }
            } else {
                // 👉 Show error kapag hindi part ng allowed pages
                echo "<h3>Invalid page.</h3>";
            }
        } else {
            // 👉 Default message kapag walang ?page=
            echo "<h2>Welcome Registrar!</h2><p>Select an item from the sidebar to manage the data.</p>";
        }
        ?>
    </div>

</body>
</html>
