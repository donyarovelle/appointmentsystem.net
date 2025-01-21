<?php
// Prevent browser from caching this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

// Fetch queue number and user details from the database
if (isset($_GET['queue_number']) && is_numeric($_GET['queue_number'])) {
    $queue_number = $_GET['queue_number'];

    $sql = "SELECT * FROM users WHERE queue_number = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $queue_number);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if data is found
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
        } else {
            die("<h2>Queue number not found in the database.</h2>");
        }

        $stmt->close();
    } else {
        die("<h2>Failed to prepare statement: " . $conn->error . "</h2>");
    }
} else {
    die("<h2>Queue number not specified or invalid.</h2>");
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Queue Details</title>
  <style>
    /* General body styling */
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f0f2f5;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    /* Card container */
    .queue-card {
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 400px;
      width: 100%;
    }

    /* Queue number design */
    .queue-number {
      font-size: 80px;
      font-weight: bold;
      color: #007bff;
      margin-bottom: 20px;
    }

    /* User details container */
    .user-details {
      margin-top: 20px;
    }

    /* User detail headings */
    .user-details h3 {
      font-size: 20px;
      font-weight: bold;
      color: #333;
      margin-bottom: 10px;
    }

    /* User detail text */
    .user-details p {
      font-size: 16px;
      color: #555;
      margin: 5px 0;
    }

    /* Highlight key details */
    .user-details p span {
      font-weight: bold;
      color: #007bff;
    }

    /* Serve button */
    .serve-btn {
      margin-top: 20px;
      padding: 15px 30px;
      background-color: #28a745;
      color: #fff;
      font-size: 18px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    /* Button hover effect */
    .serve-btn:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>
  <div class="queue-card">
    <!-- Display the queue number with leading zeros -->
    <div class="queue-number">
      <?php echo str_pad($user_data['queue_number'], 3, '0', STR_PAD_LEFT); ?>
    </div>

    <!-- Display user details -->
    <div class="user-details">
      <h3>User Details</h3>
      <p><span>Name:</span> <?php echo htmlspecialchars($user_data['name']); ?></p>
      <p><span>Email:</span> <?php echo htmlspecialchars($user_data['email']); ?></p>
      <p><span>Contact:</span> <?php echo htmlspecialchars($user_data['contact_number']); ?></p>
      <p><span>Date of Payment:</span> <?php echo htmlspecialchars($user_data['date_of_payment']); ?></p>
      <p><span>Purpose:</span> <?php echo htmlspecialchars($user_data['purpose']); ?></p>
      <?php if (!empty($user_data['payment_mode'])): ?>
      <p><span>Payment Mode:</span> <?php echo htmlspecialchars($user_data['payment_mode']); ?></p>
      <?php endif; ?>
    </div>
      <p>
        <a href="payment_form.html">Back to the form</a>
      </p>
  </div>

  <script>
    function serveUser(queueNumber) {
      // Example: Logic to serve the user can be added here
      alert(`Serving queue number: ${queueNumber}`);
    }
  </script>
</body>
</html>
