<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "appointment_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form data
    $name = strtoupper($_POST['name']);
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $payment_date = $_POST['payment_date'];
    $purpose = strtoupper($_POST['purpose']);
    $electric_bill = $_POST['electric_bill'];
    $water_bill = $_POST['water_bill'];
    $rental_bill = $_POST['rental_bill'];
    $combat_pay = $_POST['combat_pay'];
    $pay_allowances = $_POST['pay_allowances'];
    $subsistence_allowance = $_POST['subsistence_allowance'];
    $payment_mode = $_POST['payment_mode'];

    // Check if the email already exists
    $email_check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already exists, display an error message
        echo "<script>alert('This email is already registered. Please use a different email.'); window.history.back();</script>";
        exit();
    }

    // Check the count of submissions for the selected date
    $limit_sql = "SELECT COUNT(*) AS submission_count FROM users WHERE DATE(date_of_payment) = ?";
    $stmt = $conn->prepare($limit_sql);
    $stmt->bind_param("s", $payment_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['submission_count'] >= 50) {
        // Limit reached, show error message
        echo "<script>alert('Submission limit of 50 reached for this date. Please choose another date.'); window.history.back();</script>";
        exit();
    }

    // File upload handling
    $upload_dir = "uploads/";

    // Ensure the upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
    }

    $file_name = $_FILES['bill']['name'] ?? '';
    $file_tmp_name = $_FILES['bill']['tmp_name'] ?? '';
    $file_error = $_FILES['bill']['error'] ?? 1; // Default to error
    $file_size = $_FILES['bill']['size'] ?? 0;

    // Check for file upload error
    if ($file_error === 0) {
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

        // Validate file extension
        if (in_array($file_ext, $allowed_ext)) {
            // Check for file size limit (10MB)
            if ($file_size <= 10485760) {
                $new_file_name = uniqid('', true) . "." . $file_ext;
                $file_destination = $upload_dir . $new_file_name;

                // Move the uploaded file to the designated folder
                if (move_uploaded_file($file_tmp_name, $file_destination)) {
                    // Retrieve the highest queue number for the given date
                    $sql = "SELECT MAX(queue_number) AS max_queue FROM users WHERE DATE(date_of_payment) = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $payment_date);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    $queue_number = ($row['max_queue'] !== null) ? $row['max_queue'] + 1 : 1;

                    // Insert data into the database
                    $insert_sql = "INSERT INTO users (queue_number, name, email, contact_number, date_of_payment, purpose, electric_bill, water_bill, rental_bill, combat_pay, pay_allowances, subsistence_allowance, payment_mode, bill_file_path) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("isssssssssssss", $queue_number, $name, $email, $contact, $payment_date, $purpose, $electric_bill, $water_bill, $rental_bill, $combat_pay, $pay_allowances, $subsistence_allowance, $payment_mode, $new_file_name);

                    if ($stmt->execute()) {
                        // Redirect to queueing_number.php and pass the queue number
                        header("Location: queueing_number.php?queue_number=" . $queue_number);
                        exit();
                    } else {
                        echo "Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    echo "Failed to upload the file.";
                }
            } else {
                echo "File size exceeds the limit of 10MB.";
            }
        } else {
            echo "Invalid file format. Only PDF, JPG, JPEG, PNG files are allowed.";
        }
    } else {
        echo "Error uploading file.";
    }
}

// Close the database connection
$conn->close();
?>
