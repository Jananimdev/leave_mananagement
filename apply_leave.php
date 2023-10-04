<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $leave_type = $_POST['leave_type']; // Get the selected leave type from the form

    // Calculate the number of leave days (e.g., based on start and end date)
    $leave_days = calculateLeaveDays($start_date, $end_date);

    // Check if the student has enough leave balance
    $leave_balance = getLeaveBalance($conn, $student_id, $leave_type);


    if ($leave_balance !== false) {
        // Define the leave limits for each type
        $leave_limits = [
            'casual' => 8,
            'medical' => 12
        ];

        // Check if the selected leave type exists in the limits array
        if (array_key_exists($leave_type, $leave_limits)) {
            $leave_limit = $leave_limits[$leave_type]; // Get the limit for the selected type

            if ($leave_balance >= $leave_days && $leave_days <= $leave_limit) {
                $new_balance = $leave_balance - $leave_days;

                // Update the student's leave balance in the database
                if (updateLeaveBalance($conn, $student_id, $new_balance,$leave_type)) {
                    // Insert leave application data into the database
                    $sql = "INSERT INTO leave_applications (student_id, start_date, end_date, reason, leave_type) 
                            VALUES ($student_id, '$start_date', '$end_date', '$reason', '$leave_type')";

                    if ($conn->query($sql) === TRUE) {
                        echo "Leave application submitted successfully";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                } else {
                    echo "Error updating leave balance";
                }
            } else {
                echo "Insufficient leave balance or invalid leave duration.";
            }
        } else {
            echo "Invalid leave type selected";
        }
    } else {
        echo "Error retrieving leave balance";
    }

    $conn->close();
}

function calculateLeaveDays($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days + 1; // Include the start and end days
}

function getLeaveBalance($conn, $student_id, $leaveType) {
    $sql = "SELECT casual_leave_balance, medical_leave_balance FROM students WHERE id = ?";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    
    // Check for errors in preparing the statement
    if (!$stmt) {
        die("Error in preparing the SQL statement: " . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param("i", $student_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Depending on the leave type, return the corresponding balance
            if ($leaveType === 'medical') {
                return $row['medical_leave_balance'];
            } elseif ($leaveType === 'casual') {
                return $row['casual_leave_balance'];
            }
        }
    } else {
        die("Error in executing the SQL statement: " . $stmt->error);
    }
    
    return 0; // Return 0 or handle the error as needed
}


function updateLeaveBalance($conn, $student_id, $new_balance, $leave_type) {
    $column_name = '';

    if ($leave_type === 'casual') {
        $column_name = 'casual_leave_balance';
    } elseif ($leave_type === 'medical') {
        $column_name = 'medical_leave_balance';
    }

    if (!empty($column_name)) {
        $sql = "UPDATE students SET $column_name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_balance, $student_id);

        if ($stmt->execute()) {
            return true; // Leave balance updated successfully
        } else {
            return false; // Return false if an error occurs
        }
    } else {
        return false; // Invalid or missing leave type
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('coll4.jpg'); /* Replace 'coll4.jpg' with your image file path */
            background-size: cover;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            backdrop-filter: blur(5px);
        }

        h1 {
            color: black;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
        }

        form {
            max-width: 900px;
            margin: 0 auto;
            padding: 70px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin-bottom: 20px; /* Increase margin for better spacing */
            font-weight: bold;
            font-size: 18px; /* Increase font size for labels */
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 15px; /* Increase padding for larger input fields */
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 18px; /* Increase font size for input fields */
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #333;
            color: #fff;
            padding: 15px 30px; /* Increase padding for a larger button */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <h1>Apply for Leave</h1>
    <form method="post" action="apply_leave.php">
    <label for="leave_type">Leave Type:</label>
<select name="leave_type" required>
    <option value="casual">Casual Leave</option>
    <option value="medical">Medical Leave</option>
</select><br>
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" required><br>
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" required><br>
        <label for="reason">Reason:</label>
        <textarea name="reason" rows="4" required></textarea><br>
        <input type="submit" value="Submit Application">
    </form>
</body>
</html>
