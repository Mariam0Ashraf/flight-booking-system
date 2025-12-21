<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$flight_id = $_GET['id'];
$company_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ? AND company_id = ?");
$stmt->execute([$flight_id, $company_id]);
$flight = $stmt->fetch();

if (!$flight) {
    echo "Flight not found or access denied.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['cancel_flight'])) {
        $pdo->beginTransaction();
        try {
            // Get passengers
            $stmt_passengers = $pdo->prepare("SELECT passenger_id FROM bookings WHERE flight_id = ?");
            $stmt_passengers->execute([$flight_id]);
            $passengers = $stmt_passengers->fetchAll();

            foreach ($passengers as $p) {
                $update_pass = $pdo->prepare("UPDATE passengers SET account_balance = account_balance + ? WHERE user_id = ?");
                $update_pass->execute([$flight['fees'], $p['passenger_id']]);

                $update_comp = $pdo->prepare("UPDATE companies SET account_balance = account_balance - ? WHERE user_id = ?");
                $update_comp->execute([$flight['fees'], $company_id]);
            }

            $update_flight = $pdo->prepare("UPDATE flights SET status = 'cancelled' WHERE id = ?");
            $update_flight->execute([$flight_id]);

            $pdo->commit();
            header("Location: flight-details.php?id=" . $flight_id);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to cancel flight: " . $e->getMessage();
        }
    }

    if (isset($_POST['complete_flight'])) {
        // Simply update the status to 'completed'
        $stmt_complete = $pdo->prepare("UPDATE flights SET status = 'completed' WHERE id = ?");
        if($stmt_complete->execute([$flight_id])) {
            header("Location: flight-details.php?id=" . $flight_id);
            exit();
        } else {
            $error = "Failed to mark flight as completed.";
        }
    }
}


$stmt_pending = $pdo->prepare("
    SELECT p.* FROM bookings b 
    JOIN passengers p ON b.passenger_id = p.user_id 
    WHERE b.flight_id = ? AND b.status = 'pending'
");
$stmt_pending->execute([$flight_id]);
$pending_list = $stmt_pending->fetchAll();

// Registered Passengers
$stmt_registered = $pdo->prepare("
    SELECT p.* FROM bookings b 
    JOIN passengers p ON b.passenger_id = p.user_id 
    WHERE b.flight_id = ? AND b.status = 'registered'
");
$stmt_registered->execute([$flight_id]);
$registered_list = $stmt_registered->fetchAll();

include '../includes/header.php';
?>

    <a href="dashboard.php">&larr; Back to Dashboard</a>
    <h1>Flight: <?php echo htmlspecialchars($flight['name']); ?></h1>

<?php if(isset($error)) echo "<div class='alert' style='background:pink; color:red; padding:10px;'>$error</div>"; ?>

    <div class="ui-widget-content" style="padding:15px; margin-bottom:20px; border:1px solid #ccc; background:#f9f9f9;">
        <p><strong>ID:</strong> <?php echo $flight['id']; ?></p>
        <p><strong>Itinerary:</strong> <?php echo htmlspecialchars($flight['itinerary']); ?></p>
        <p><strong>Fees:</strong> $<?php echo number_format($flight['fees'], 2); ?></p>
        <p><strong>Status:</strong>
            <?php
            if($flight['status']=='completed') echo '<span style="color:green; font-weight:bold;">Completed</span>';
            elseif($flight['status']=='cancelled') echo '<span style="color:red; font-weight:bold;">Cancelled</span>';
            else echo '<span style="color:blue; font-weight:bold;">Pending/Active</span>';
            ?>
        </p>

        <div style="margin-top:20px; display:flex; gap:10px;">
            <?php if($flight['status'] == 'pending'): ?>
                <form method="POST" onsubmit="return confirm('Are you sure the flight has arrived? This will move it to history.');">
                    <button type="submit" name="complete_flight" style="background-color:green; color:white; padding:10px 15px; border:none; cursor:pointer;">
                        &#10003; Mark as Completed
                    </button>
                </form>
            <?php endif; ?>

            <?php if($flight['status'] == 'pending'): ?>
                <form method="POST" onsubmit="return confirm('Are you sure? This will refund all passengers.');">
                    <button type="submit" name="cancel_flight" style="background-color:red; color:white; padding:10px 15px; border:none; cursor:pointer;">
                        &#10005; Cancel Flight
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="lists-container" style="display:flex; gap:20px;">

        <div style="flex:1;">
            <h3>Pending Passengers (<?php echo count($pending_list); ?>)</h3>
            <ul style="list-style:none; padding:0;">
                <?php foreach($pending_list as $p): ?>
                    <li style="border-bottom:1px solid #eee; padding:5px;">
                        <?php if($p['photo_path']): ?>
                            <img src="../assets/uploads/<?php echo $p['photo_path']; ?>" width="30" style="vertical-align:middle; border-radius:50%;">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($p['name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="flex:1;">
            <h3>Registered Passengers (<?php echo count($registered_list); ?>)</h3>
            <ul style="list-style:none; padding:0;">
                <?php foreach($registered_list as $p): ?>
                    <li style="border-bottom:1px solid #eee; padding:5px;">
                        <?php if($p['photo_path']): ?>
                            <img src="../assets/uploads/<?php echo $p['photo_path']; ?>" width="30" style="vertical-align:middle; border-radius:50%;">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($p['name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>

<?php include '../includes/footer.php'; ?>