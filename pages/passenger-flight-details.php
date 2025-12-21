<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: search-flights.php");
    exit();
}

$flight_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

$stmt = $pdo->prepare("SELECT f.*, c.name as company_name, c.user_id as company_id 
                       FROM flights f 
                       JOIN companies c ON f.company_id = c.user_id 
                       WHERE f.id = ?");
$stmt->execute([$flight_id]);
$flight = $stmt->fetch();

$stmt_p = $pdo->prepare("SELECT account_balance FROM passengers WHERE user_id = ?");
$stmt_p->execute([$user_id]);
$passenger = $stmt_p->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $pay_type = $_POST['pay_type']; // 'account' or 'cash'

    $check = $pdo->prepare("SELECT id FROM bookings WHERE flight_id = ? AND passenger_id = ?");
    $check->execute([$flight_id, $user_id]);

    if ($check->rowCount() > 0) {
        $error = "You have already booked this flight.";
    } else {
        if ($pay_type == 'account') {
            if ($passenger['account_balance'] >= $flight['fees']) {
                $pdo->beginTransaction();
                try {
                    $pdo->prepare("UPDATE passengers SET account_balance = account_balance - ? WHERE user_id = ?")
                        ->execute([$flight['fees'], $user_id]);

                    $pdo->prepare("UPDATE companies SET account_balance = account_balance + ? WHERE user_id = ?")
                        ->execute([$flight['fees'], $flight['company_id']]);

                    $pdo->prepare("INSERT INTO bookings (flight_id, passenger_id, status) VALUES (?, ?, 'registered')")
                        ->execute([$flight_id, $user_id]);

                    $pdo->commit();
                    $message = "Booking confirmed! Fees deducted from your account.";
                    $passenger['account_balance'] -= $flight['fees'];
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Transaction failed.";
                }
            } else {
                $error = "Insufficient funds in your account.";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (flight_id, passenger_id, status) VALUES (?, ?, 'pending')");
            if ($stmt->execute([$flight_id, $user_id])) {
                $message = "Booking placed! Please pay cash at the company office to confirm.";
            } else {
                $error = "Booking failed.";
            }
        }
    }
}

include '../includes/header.php';
?>

    <div style="max-width:800px; margin:0 auto; background:white; padding:20px; border:1px solid #ddd; margin-top:20px;">
        <a href="search-flights.php">&larr; Back to Search</a>

        <h1>Flight Info</h1>
        <?php if($message) echo "<div class='alert' style='background:#d4edda; color:green;'>$message</div>"; ?>
        <?php if($error) echo "<div class='alert' style='background:#f8d7da; color:red;'>$error</div>"; ?>

        <div style="background:#f9f9f9; padding:15px; margin-bottom:20px;">
            <h2 style="margin-top:0;"><?php echo htmlspecialchars($flight['name']); ?></h2>
            <p><strong>Airline:</strong> <?php echo htmlspecialchars($flight['company_name']); ?></p>
            <p><strong>Itinerary:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
            <p><strong>Departure:</strong> <?php echo date('F j, Y, g:i a', strtotime($flight['start_time'])); ?></p>
            <p><strong>Arrival:</strong> <?php echo date('F j, Y, g:i a', strtotime($flight['end_time'])); ?></p>
            <p><strong>Fees:</strong> <span style="font-size:1.2em; color:green;">$<?php echo number_format($flight['fees'], 2); ?></span></p>
        </div>

        <div style="display:flex; gap:30px;">

            <div style="flex:1; border:1px solid #eee; padding:15px;">
                <h3>Book This Flight</h3>
                <form method="POST">
                    <p><strong>Select Payment Method:</strong></p>

                    <label style="display:block; margin-bottom:10px; cursor:pointer;">
                        <input type="radio" name="pay_type" value="account" checked>
                        Pay from Account (Balance: $<?php echo number_format($passenger['account_balance'], 2); ?>)
                    </label>

                    <label style="display:block; margin-bottom:20px; cursor:pointer;">
                        <input type="radio" name="pay_type" value="cash">
                        Pay Cash (Deal with Company)
                    </label>

                    <button type="submit" name="confirm_booking" class="btn" style="width:100%; background:#28a745;">Confirm Booking</button>
                </form>
            </div>

            <div style="flex:1; border:1px solid #eee; padding:15px; text-align:center; display:flex; flex-direction:column; justify-content:center;">
                <h3>Have Questions?</h3>
                <p>Contact the airline directly.</p>
                <a href="messages.php?partner_id=<?php echo $flight['company_id']; ?>" class="btn" style="background:#007bff; display:block;">Message Company</a>
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>