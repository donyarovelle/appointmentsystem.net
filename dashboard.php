<?php
// Start session to check if the user is logged in
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'appointment_system';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch user data from the database
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users List with Date Filter</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f3f4f6;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
    }

    h1 {
      color: #333;
      margin: 30px 0;
      font-size: 32px;
      font-weight: bold;
    }

    .filter-container {
      margin-bottom: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
    }

    input[type="date"] {
      padding: 12px 16px;
      font-size: 16px;
      border: 2px solid #ccc;
      border-radius: 8px;
      background-color: #fff;
      outline: none;
    }

    input[type="date"]:focus {
      border-color: #007bff;
    }

    button {
      padding: 12px 20px;
      font-size: 16px;
      color: white;
      background-color: #007bff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }

    .table-container {
      width: 90%;
      max-width: 1200px;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      overflow-x: auto;
      padding: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    thead {
      background-color: #007bff;
      color: white;
      border-radius: 10px;
    }

    th, td {
      padding: 15px 20px;
      border-bottom: 1px solid #eaeaea;
      font-size: 14px;
      color: #333;
      text-align: center;
    }

    th {
      text-transform: uppercase;
      font-weight: bold;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .no-data {
      text-align: center;
      color: #666;
      font-size: 16px;
      padding: 20px;
    }

    .serve-btn {
      padding: 8px 16px;
      font-size: 14px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .serve-btn:hover {
      background-color: #218838;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      h1 {
        font-size: 24px;
      }

      input[type="date"], button {
        font-size: 14px;
        padding: 10px 14px;
      }

      th, td {
        padding: 12px 16px;
      }
    }
  </style>
</head>
<body>
  <h1>Users List</h1>
  
  <div class="filter-container">
    <label for="filter-date" style="font-size: 18px; color: #333;">Select Date:</label>
    <input type="date" id="filter-date">
    <button onclick="filterTable()">Filter</button>
  </div>

  <div class="table-container">
    <table id="users-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Contact Number</th>
          <th>Date of Payment</th>
          <th>Purpose</th>
          <th>Payment Mode</th>
          <th>Queue Number</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // Determine button text and status based on `served` column
                $buttonText = $row['served'] == 1 ? "Served" : "Serve";
                $isDisabled = $row['served'] == 1 ? "disabled" : "";
                echo "<tr data-id='".$row['id']."' data-date='".$row['date_of_payment']."'>
                        <td>".$row['id']."</td>
                        <td>".$row['name']."</td>
                        <td>".$row['email']."</td>
                        <td>".$row['contact_number']."</td>
                        <td>".$row['date_of_payment']."</td>
                        <td>".$row['purpose']."</td>
                        <td>".$row['payment_mode']."</td>
                        <td>".$row['queue_number']."</td>
                        <td>
                            <button class='serve-btn' $isDisabled onclick='serveUser(".$row['id'].")'>".$buttonText."</button>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr id='no-data-row'>
                    <td colspan='9' class='no-data'>No users found</td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <script>
    function filterTable() {
      const filterDate = document.getElementById("filter-date").value;
      const tableRows = document.querySelectorAll("#users-table tbody tr");
      let hasVisibleRow = false;

      tableRows.forEach(row => {
        const rowDate = row.getAttribute("data-date");
        if (rowDate === filterDate || row.id === "no-data-row") {
          row.style.display = "";
          hasVisibleRow = true;
        } else {
          row.style.display = "none";
        }
      });

      document.getElementById("no-data-row").style.display = hasVisibleRow ? "none" : "";
    }

    function serveUser(userId) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "serve_user.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
          if (xhr.responseText === "success") {
            const row = document.querySelector(`tr[data-id='${userId}']`);
            const button = row.querySelector('.serve-btn');
            button.textContent = "Served";
            button.disabled = true;
          } else {
            alert("Error serving user. Please try again.");
          }
        }
      };

      xhr.send("user_id=" + userId);
    }
  </script>

  <p>
    <a href="logout.php">Log out</a>
  </p>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
