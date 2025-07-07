<?php
include('../includes/db.php');
include('../includes/log_module.php');

// Faculty dropdown
$query = "SELECT faculty_number, full_name FROM faculty";
$result = mysqli_query($conn, $query);
?>

<div class="card">
    <h2>Enroll Faculty Fingerprint</h2>

    <form method="POST" action="trigger_enroll.php">
        <label for="faculty_number">Faculty:</label>
        <select name="faculty_number" required>
            <option value="">-- Select Faculty --</option>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <option value="<?= $row['faculty_number'] ?>">
                    <?= $row['faculty_number'] ?> - <?= $row['full_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <button type="submit">Start Enrollment</button>
    </form>

    <hr>

    <h3>Faculty with Registered Fingerprints</h3>

    <?php
    $fp_query = "SELECT f.faculty_number, f.full_name, ff.fingerprint_id 
                 FROM faculty_fingerprint ff
                 JOIN faculty f ON f.faculty_number = ff.faculty_number";
    $fp_result = mysqli_query($conn, $fp_query);
    ?>

    <?php if (mysqli_num_rows($fp_result) > 0): ?>
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Faculty Number</th>
                    <th>Full Name</th>
                    <th>Fingerprint ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($fp_result)): ?>
                    <tr>
                        <td><?= $row['faculty_number'] ?></td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['fingerprint_id'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No registered fingerprints found.</p>
    <?php endif; ?>
</div>
