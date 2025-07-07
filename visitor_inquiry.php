<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- ✔️ Para maayos ang character encoding -->
    <title>Visitor Inquiry</title> <!-- ✔️ Title ng tab sa browser -->

    <style>
        /* 🧠 Iset natin yung body ng page: no margin/padding + background image */
        body {
            margin: 0;
            padding: 0;
            background: url('assets/img/background.jpg') no-repeat center center fixed; /* ✔️ Background image sa gitna, fixed */
            background-size: cover; /* ✔️ Lapat sa buong screen */
            font-family: Arial, sans-serif; /* ✔️ Clean font style */
        }

        /* 📦 Yung container para gitna lahat ng content */
        .container {
            display: flex; /* ✔️ Flexbox layout */
            justify-content: center; /* ✔️ Gitna horizontally */
            align-items: center; /* ✔️ Gitna vertically */
            height: 100vh; /* ✔️ Buong height ng screen */
        }

        /* 🧾 Card na naglalaman ng mga buttons */
        .card {
            background-color: #f2f2f2; /* ✔️ Light gray background */
            padding: 40px; /* ✔️ Spacing sa loob */
            border-radius: 12px; /* ✔️ Bilugan ang corners */
            box-shadow: 0 8px 16px rgba(0,0,0,0.2); /* ✔️ Shadow for depth */
            text-align: center; /* ✔️ Gitna ng text */
        }

        .card h2 {
            margin-bottom: 30px; /* ✔️ Space sa ilalim ng title */
        }

        /* 🔘 Style ng buttons */
        .btn {
            display: block; /* ✔️ One button per line */
            width: 200px; /* ✔️ Fixed width */
            margin: 15px auto; /* ✔️ Centered button + spacing */
            padding: 12px; /* ✔️ Space sa loob ng button */
            font-size: 16px; /* ✔️ Readable font */
            background-color: #52ab98; /* ✔️ Greenish button color */
            color: white; /* ✔️ Text color */
            border: none; /* ✔️ No border */
            border-radius: 10px; /* ✔️ Rounded corners */
            box-shadow: 0 4px 6px rgba(0,0,0,0.2); /* ✔️ Slight shadow */
            cursor: pointer; /* ✔️ Pointer icon pag hover */
            text-decoration: none; /* ✔️ No underline */
            transition: all 0.3s ease; /* ✔️ Smooth animation pag hover */
        }

        .btn:hover {
            background-color:rgb(18, 243, 104); /* ✔️ Darker green pag hover */
        }
    </style>
</head>
<body>

<!--  Main container to center everything vertically & horizontally -->
<div class="container">
    <div class="card">
        <h2>Visitor Inquiry</h2> <!-- 📢 Title ng card -->

        <!-- Button to go to set_appointment form -->
        <a href="set_appointment.php" class="btn">Set Appointment</a>

        <!--  Button for future AI  -->
        <a href="chatbot/chatbot.php" class="btn btn-inquire">Inquire</a>

    </div>
</div>

</body>
</html>
