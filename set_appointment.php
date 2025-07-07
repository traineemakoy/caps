<?php
include 'includes/db.php'; // 🧩 Connect to DB (update path if needed)

// 🔄 Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $school = $_POST['school'];
    $graduated_status = $_POST['graduated_status'];
    $email = $_POST['email'];
    $purpose = $_POST['purpose'];
    $preferred_date = $_POST['preferred_date'];

    // 💾 Save to DB
    $stmt = $conn->prepare("INSERT INTO visitor_appointments 
        (name, school_last_attended, graduated_status, email, purpose, preferred_date, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssss", $name, $school, $graduated_status, $email, $purpose, $preferred_date);
    $stmt->execute();

    // ✅ SweetAlert2 success message
    echo "<!DOCTYPE html>
    <html>
    <head>
      <meta charset='UTF-8'>
      <title>Submitted</title>
      <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
      <meta http-equiv='refresh' content='10;url=index.php'>
    </head>
    <body>
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Appointment Submitted!',
          text: 'We will contact you through your email.',
          confirmButtonText: 'Go back to main page'
        }).then(() => {
          window.location.href = 'index.php';
        });
      </script>
    </body>
    </html>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Set Appointment</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #e9e9e9;
      font-family: Arial, sans-serif;
    }
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .card {
      background-color: #f2f2f2;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 600px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .radio-row {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 15px;
    }
    .main-label {
      min-width: 150px;
    }
    .inline-option {
      display: flex;
      align-items: center;
      gap: 5px;
      white-space: nowrap;
    }
    button {
      background-color: #52ab98;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 10px;
      width: 100%;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #3b8c7b;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card">
    <h2>Set an Appointment</h2>

    <form method="POST" action="">
      <label>Full Name:</label>
      <input type="text" name="name" required>

      <label>School Last Attended:</label>
      <input type="text" name="school" required>

      <div class="radio-row">
        <label class="main-label">Graduation Status:</label>
        <label class="inline-option">
          Graduated
          <input type="radio" name="graduated_status" value="Graduated" required>
        </label>
        <label class="inline-option">
          Post-Graduate
          <input type="radio" name="graduated_status" value="Post-Graduate" required>
        </label>
      </div>

      <label>Email Address:</label>
      <input type="email" name="email" required>

      <label>Purpose of Appointment:</label>
      <textarea name="purpose" rows="4" required></textarea>

      <label>Preferred Date:</label>
      <input type="date" name="preferred_date" required>

      <button type="submit">Submit Appointment</button>
    </form>
  </div>
</div>

</body>
</html>
