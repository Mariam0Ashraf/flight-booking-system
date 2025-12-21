<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

if ($_SESSION['role'] != 'company') {
    echo "Access Denied";
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_id = $_SESSION['user_id'];
    $name = cleanInput($_POST['name']);
    $itinerary = cleanInput($_POST['itinerary']);
    $fees = floatval($_POST['fees']);
    $max_passengers = intval($_POST['max_passengers']);

    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if(empty($name) || empty($itinerary) || empty($start_time) || empty($end_time)) {
        $error = "All fields are required.";
    } else {
        $sql = "INSERT INTO flights (company_id, name, itinerary, fees, max_passengers, start_time, end_time, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$company_id, $name, $itinerary, $fees, $max_passengers, $start_time, $end_time]);
            $success = "Flight added successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

    <div class="container" style="max-width:600px;">
        <h2>Add New Flight</h2>

        <?php
        if($error) echo "<div class='alert' style='background:pink; color:red;'>$error</div>";
        if($success) echo "<div class='alert' style='background:lightgreen; color:green;'>$success</div>";
        ?>

        <form action="" method="POST" class="ui-widget-content" style="padding:20px;">

            <div class="form-group">
                <label>Flight Name / Number:</label>
                <input type="text" name="name" placeholder="e.g. SKY-2024" required>
            </div>

            <div class="form-group">
                <label>Itinerary (Cities):</label>
                <input type="text" name="itinerary" placeholder="e.g. New York -> London -> Dubai" required>
                <small>List cities the flight passes through.</small>
            </div>

            <div class="form-group">
                <label>Ticket Fees ($):</label>
                <input type="number" name="fees" step="0.01" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label>Max Passengers:</label>
                <input type="number" name="max_passengers" value="50" required>
            </div>

            <fieldset style="border:1px solid #ddd; padding:10px; margin-bottom:15px;">
                <legend>Flight Time</legend>
                <div class="form-group">
                    <label>Start Day & Hour:</label>
                    <input type="datetime-local" name="start_time" required>
                </div>
                <div class="form-group">
                    <label>End Day & Hour:</label>
                    <input type="datetime-local" name="end_time" required>
                </div>
            </fieldset>

            <button type="submit" style="width:100%; padding:10px; font-size:16px;">Create Flight</button>
        </form>

        <br>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>

<?php include '../includes/footer.php'; ?>