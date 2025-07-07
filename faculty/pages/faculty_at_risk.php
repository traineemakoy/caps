<?php

include("../includes/db.php");

$faculty_number = $_SESSION['faculty_number'] ?? null;
if (!$faculty_number) {
    echo "<p>❌ Unauthorized access.</p>";
    exit();
}

// ✅ JOIN with students for names
$stmt = $conn->prepare("
    SELECT 
        s.subject_code, s.subject_name, 
        g.prelim_grade, g.midterm_grade, g.finals_grade, 
        g.student_number, st.first_name, st.last_name,
        os.course, os.year_level
    FROM grades g
    JOIN subjects s ON s.subject_id = g.subject_id
    JOIN offered_subjects os ON os.subject_id = s.subject_id
    JOIN students st ON st.student_number = g.student_number
    WHERE os.faculty_number = ?
");

if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $faculty_number);
$stmt->execute();
$result = $stmt->get_result();

$at_risk = [];

while ($row = $result->fetch_assoc()) {
    $prelim = floatval($row['prelim_grade']);
    $midterm = floatval($row['midterm_grade']);
    $finals = floatval($row['finals_grade']);
    $risk = '';

    if ($prelim > 3.0 || $midterm > 3.0 || $finals > 3.0) {
        $risk = ($prelim > 3.0 && $midterm > 3.0 && $finals > 3.0) ? '🔥 High' : '⚠️ Moderate';
        $row['risk'] = $risk;
        $at_risk[] = $row;
    }
}

// ✅ GPT Feedback Function (optional)
function getGPTFeedback($at_risk) {
    $apiKey = 'sk-proj-xxx'; // Replace with your real OpenAI key

    if (count($at_risk) === 0) return "All students are currently performing well.";

    $subjectLines = array_map(function ($subject) {
        return "Student {$subject['student_number']} - {$subject['first_name']} {$subject['last_name']} - {$subject['subject_name']} | Prelim: {$subject['prelim_grade']}, Midterm: {$subject['midterm_grade']}, Finals: {$subject['finals_grade']} (Risk: {$subject['risk']})";
    }, $at_risk);

    $prompt = "Here is a list of at-risk students:\n" . implode("\n", $subjectLines) . "\n\nGenerate helpful, encouraging academic feedback based on these students' performance. Be supportive and constructive.";

    $postData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful academic advisor.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? "No feedback generated.";
}
?>

<h2>📕 At Risk Students</h2>

<?php if (count($at_risk) > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Student No.</th>
                <th>Full Name</th>
                <th>Subject</th>
                <th>Prelim</th>
                <th>Midterm</th>
                <th>Finals</th>
                <th>Course</th>
                <th>Year</th>
                <th>Risk Level</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($at_risk as $subject): ?>
                <tr>
                    <td><?= htmlspecialchars($subject['student_number']) ?></td>
                    <td><?= htmlspecialchars($subject['first_name'] . ' ' . $subject['last_name']) ?></td>
                    <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                    <td><?= htmlspecialchars($subject['prelim_grade']) ?></td>
                    <td><?= htmlspecialchars($subject['midterm_grade']) ?></td>
                    <td><?= htmlspecialchars($subject['finals_grade']) ?></td>
                    <td><?= htmlspecialchars($subject['course']) ?></td>
                    <td><?= htmlspecialchars($subject['year_level']) ?></td>
                    <td><strong><?= $subject['risk'] ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 25px; background: #f0f9ff; padding: 15px; border-left: 5px solid #2196F3;">
        <h4>📘 GPT Academic Feedback:</h4>
        <p><?= nl2br(htmlspecialchars(getGPTFeedback($at_risk))) ?></p>
    </div>

<?php else: ?>
    <p>🎉 None of your students are at risk. Great job!</p>
<?php endif; ?>

<link rel="stylesheet" href="../assets/css/style.css">
