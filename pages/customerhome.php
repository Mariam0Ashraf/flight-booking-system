<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'company') {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("SELECT u.email, p.* FROM users u JOIN passengers p ON u.id = p.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$passenger = $stmt->fetch();

$sql_flights = "
    SELECT b.status as booking_status, f.*, c.name as company_name 
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    JOIN companies c ON f.company_id = c.user_id
    WHERE b.passenger_id = ?
    ORDER BY f.start_time ASC
";
$stmt = $pdo->prepare($sql_flights);
$stmt->execute([$user_id]);
$all_flights = $stmt->fetchAll();

$current_flights = [];
$completed_flights = [];

foreach ($all_flights as $flight) {
    if ($flight['status'] == 'completed') {
        $completed_flights[] = $flight;
    } else {
        $current_flights[] = $flight;
    }
}

include '../includes/header.php';
?>

    <div class="passenger-dashboard">
        <div style="display:flex; align-items:center; gap:20px; background:#fff; padding:20px; border-bottom:1px solid #ddd;">
            <?php if(!empty($passenger['photo_path'])): ?>
                <img src="../assets/uploads/<?php echo $passenger['photo_path']; ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover;">
            <?php else: ?>
                <img src="../assets/img/default-user.png" style="width:100px; height:100px; background:#ccc; border-radius:50%;">
            <?php endif; ?>

            <div>
                <h1>Hello, <?php echo htmlspecialchars($passenger['name']); ?></h1>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($passenger['email']); ?></p>
                <p><strong>Balance:</strong> <span style="color:green;">$<?php echo number_format($passenger['account_balance'], 2); ?></span></p>
            </div>

            <div style="margin-left:auto;">
                <a href="passenger-profile.php" class="btn">Profile</a>
                <a href="search-flights.php" class="btn" >Search for Flight</a>
                <a href="logout.php" class="btn" >Logout</a>
            </div>
        </div>

        <h3 style="margin-top:30px;"> Upcoming Flights</h3>
        <?php if(count($current_flights) > 0): ?>
            <table style="width:100%; border-collapse:collapse; background:white;">
                <tr style="background:#f0f8ff;">
                    <th style="padding:10px; text-align:left;">Flight</th>
                    <th>Route</th>
                    <th>Status</th>
                </tr>
                <?php foreach($current_flights as $f): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px;">
                            <strong><?php echo htmlspecialchars($f['name']); ?></strong><br>
                            <small>by <?php echo htmlspecialchars($f['company_name']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($f['itinerary']); ?></td>
                        <td><?php echo ucfirst($f['booking_status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>You have no upcoming flights.</p>
        <?php endif; ?>

        <h3 style="margin-top:30px; color:#666;">Travel History</h3>
        <ul>
            <?php foreach($completed_flights as $f): ?>
                <li><?php echo htmlspecialchars($f['name']); ?> (<?php echo htmlspecialchars($f['itinerary']); ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>

<?php include '../includes/footer.php'; ?>