<?php
// Database connection details
$servername = "localhost";
$username = "root";  // Replace with your MySQL username
$password = "";  // Replace with your MySQL password
$dbname = "appointment_system";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the user input from the form
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prepare the SQL statement to fetch user from the database
    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);  // "s" means string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check the password (assuming passwords are stored in plain text)
        // In a real-world scenario, use password_hash() and password_verify() for better security.
        if ($pass == $row['password']) {
            // Start a session and store user info
            session_start();
            $_SESSION['username'] = $user;

            // Redirect to a protected page (e.g., dashboard.php)
            header("Location: dashboard.php");
            exit();
        } else {
            // Incorrect password
            header("Location: login.php?error=incorrect_password");
            exit();
        }
    } else {
        // User not found
        header("Location: login.php?error=user_not_found");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background: #ffffff;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 300px;
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333333;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      color: #555555;
      margin-bottom: 5px;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      font-size: 14px;
      border: 1px solid #cccccc;
      border-radius: 5px;
    }

    .form-group input:focus {
      border-color: #007bff;
      outline: none;
    }

    .login-btn {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      color: #ffffff;
      background-color: #007bff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .login-btn:hover {
      background-color: #0056b3;
    }

    .forgot-password {
      text-align: center;
      margin-top: 10px;
    }

    .forgot-password a {
      color: #007bff;
      text-decoration: none;
      font-size: 14px;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    .error {
      color: red;
      font-size: 14px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="login-btn">Login</button>
      <div class="forgot-password">
        <a href="#">Forgot Password?</a>
      </div>

      <?php
      // Display the login error message if it exists
      if (isset($_SESSION['login_error'])) {
          echo '<p class="error">' . $_SESSION['login_error'] . '</p>';
          unset($_SESSION['login_error']);  // Clear the error message after displaying it
      }
      ?>
    </form>
  </div>
</body>
</html>
