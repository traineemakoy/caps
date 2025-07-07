<?php
// 📌 registrar/pages/analytics.php
include("../includes/db.php");

// 🗓️ Get selected date (default: today)
$selected_date = $_GET['date'] ?? date('Y-m-d');

// 📊 Fetch counts by type and filter by selected date
function getCount($conn, $query, $param = null) {
    $stmt = $conn->prepare($query);
    if ($param) $stmt->bind_param("s", $param);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

$total_students = getCount($conn, "SELECT COUNT(*) FROM students");
$total_faculty = getCount($conn, "SELECT COUNT(*) FROM faculty");
$total_registrars = getCount($conn, "SELECT COUNT(*) FROM registrar");
$online_students = getCount($conn, "SELECT COUNT(*) FROM login_analytics WHERE user_type = 'student' AND DATE(login_time) = ?", $selected_date);
$online_faculty = getCount($conn, "SELECT COUNT(*) FROM login_analytics WHERE user_type = 'faculty' AND DATE(login_time) = ?", $selected_date);
$online_registrars = getCount($conn, "SELECT COUNT(*) FROM login_analytics WHERE user_type = 'registrar' AND DATE(login_time) = ?", $selected_date);
$total_logins = getCount($conn, "SELECT COUNT(*) FROM login_analytics WHERE DATE(login_time) = ?", $selected_date);
$total_appointments = getCount($conn, "SELECT COUNT(*) FROM visitor_appointments WHERE DATE(created_at) = ?", $selected_date);

// 🧮 Calculate average session duration in minutes
$avg_duration_stmt = $conn->prepare("SELECT AVG(TIMESTAMPDIFF(SECOND, login_time, logout_time)) FROM login_analytics WHERE DATE(login_time) = ? AND logout_time IS NOT NULL");
$avg_duration_stmt->bind_param("s", $selected_date);
$avg_duration_stmt->execute();
$avg_duration_stmt->bind_result($avg_seconds);
$avg_duration_stmt->fetch();
$avg_duration = $avg_seconds ? round($avg_seconds / 60) : 0;
?>

<h2>📊 School Portal Summary Analytics</h2>

<!-- 🗓️ Filter by Date -->
<form method="get">
    <label><strong>Select Date:</strong></label>
    <input type="date" name="date" value="<?= $selected_date ?>">
    <button type="submit">🔍 Filter</button>
</form>

<!-- 📆 Chart and Summary Layout -->
<style>
.analytics-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-top: 20px;
}

canvas#summaryChart {
    max-width: 68%;
    height: 320px !important;
}

.report-summary {
    width: 30%;
    background: #fff;
    padding: 15px 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-family: Arial, sans-serif;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-summary h4 {
    margin-top: 0;
    font-size: 16px;
    color: #222;
}

.report-summary ul {
    padding-left: 18px;
    list-style-type: disc;
    margin: 0;
}

.report-summary li {
    padding: 5px 0;
}
</style>

<div class="analytics-container">
    <canvas id="summaryChart"></canvas>
    <div class="report-summary">
        <h4>📋 Report Summary</h4>
        <ul>
            <li><strong>Total Students:</strong> <?= $total_students ?></li>
            <li><strong>Total Faculty:</strong> <?= $total_faculty ?></li>
            <li><strong>Total Registrars:</strong> <?= $total_registrars ?></li>
            <li><strong>Currently Online:</strong> <?= $online_students + $online_faculty + $online_registrars ?></li>
            <li><strong>Total Logins (<?= $selected_date ?>):</strong> <?= $total_logins ?></li>
            <li><strong>Total Appointments:</strong> <?= $total_appointments ?></li>
            <li><strong>⏱ Avg Session Duration:</strong> <?= $avg_duration ?> mins</li>
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('summaryChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            'Total Students', 'Total Faculty', 'Total Registrars',
            'Online Students', 'Online Faculty', 'Online Registrars',
            'Total Logins', 'Total Appointments', 'Avg Session Duration'
        ],
        datasets: [{
            label: '<?= $selected_date ?>',
            data: [
                <?= $total_students ?>,
                <?= $total_faculty ?>,
                <?= $total_registrars ?>,
                <?= $online_students ?>,
                <?= $online_faculty ?>,
                <?= $online_registrars ?>,
                <?= $total_logins ?>,
                <?= $total_appointments ?>,
                <?= $avg_duration ?>
            ],
            backgroundColor: '#FFA500',
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'School Portal Summary Analytics'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>
