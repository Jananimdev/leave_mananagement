<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];

// Connect to the database (replace with your database details)
$host = 'localhost';
$db = 'students';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch leave details for the logged-in student
$sql = "SELECT * FROM leave_applications WHERE student_id = $student_id";
$result = $conn->query($sql);

// Initialize an empty array to store leave details
$leave_details = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_details[] = $row;
    }
}
$sql = "SELECT username FROM students WHERE id = $student_id";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
} else {
    // Handle the case where the username is not found
    $username = "User"; // Provide a default username or error message
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('your-background-image.jpg'); /* Replace 'your-background-image.jpg' with your image file path */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            color: #333; /* Text color */
        }

        h1 {
            color: white;
            text-align: center;
            margin-top: 8px;
        }

        p {
            font-size: 20px;
            margin-left: 20px;
            font-weight: bold;
        }

        hr {
            margin: 10px 0;
            border: none;
            border-top: 1px solid #ccc; /* Horizontal line color */
        }
        a:hover {
            background-color: #555; /* Hover color for links */
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 22px;
            font-weight: bold;
        }

        .navbar a:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<body>
    <div class="navbar">
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <!-- <a href="#">Welcome to Your Dashboard</a> -->
        <a href="apply_leave.php">Apply for Leave</a>
        <a href="leave_details.php"> Check Activity</a>
        <a href="logout.php">Log Out</a>
    </div>
    
    <h1>Your Leave Details:</h1>
    <?php foreach ($leave_details as $leave) { ?>
        <p>Leave Type: <?php echo $leave['leave_type']; ?></p>
        <p>Start Date: <?php echo $leave['start_date']; ?></p>
        <p>End Date: <?php echo $leave['end_date']; ?></p>
        <p>Reason: <?php echo $leave['reason']; ?></p>
        
        <hr>
    <?php } ?>
    
</body>
</html>
