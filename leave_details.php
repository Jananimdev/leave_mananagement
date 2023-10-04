<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Connect to the database (replace with your database details)
$host = 'localhost';
$db = 'students';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['student_id'];

// Function to get leave balance
function getLeaveBalance($conn, $student_id, $leave_type) {
    $sql = "SELECT $leave_type FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            return $row[$leave_type];
        }
    }
    return 0; // Return 0 or handle the error as needed
}

$medical_leave_balance = getLeaveBalance($conn, $student_id, "medical_leave_balance");
$casual_leave_balance = getLeaveBalance($conn, $student_id, "casual_leave_balance");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('coll6.jpg'); /* Replace 'your-background-image.jpg' with your image file path */
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            backdrop-filter: blur(5px); /* Adjust the blur amount as needed */
        }

        h1 {
            color: black; /* Text color for the heading */
            text-align: center;
        }

        p {
            font-size: 26px; /* Larger font size for the text */
            color: black; /* Text color for the leave balance */
            text-align: center;
            margin-top: 20px; /* Add some spacing between the heading and leave balance */
            font-weight: bold;
        }

        /* Style a container div for the leave balance */
        .leave-balance {
            background-color: #fff; /* Background color for the container */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        /* Style a back button */
        .back-button {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: black; /* Text color for the button text */
            background-color: #ddd; /* Button background color */
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #bbb; /* Hover color for the button */
        }
    </style>
</head>
<body>
    <h1>Leave Details</h1>
    <div class="leave-balance">
        <p>Medical Leave Balance:</p>
        <p style="font-size: 36px;"><?php echo $medical_leave_balance; ?></p> <!-- Larger font size for the balance number -->

        <p>Casual Leave Balance:</p>
        <p style="font-size: 36px;"><?php echo $casual_leave_balance; ?></p> <!-- Larger font size for the balance number -->
    </div>
    <a class="back-button" href="dashboard.php">Back to Dashboard</a>
</body>
</html>
