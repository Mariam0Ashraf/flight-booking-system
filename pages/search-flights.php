<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

$search_from = isset($_GET['from']) ? cleanInput($_GET['from']) : '';
$search_to = isset($_GET['to']) ? cleanInput($_GET['to']) : '';

$sql = "SELECT f.*, c.name as company_name 
        FROM flights f 
        JOIN companies c ON f.company_id = c.user_id 
        WHERE f.status = 'pending'";

$params = [];

if ($search_from || $search_to) {
    if ($search_from) {
        $sql .= " AND f.itinerary LIKE ?";
        $params[] = "%$search_from%";
    }
    if ($search_to) {
        $sql .= " AND f.itinerary LIKE ?";
        $params[] = "%$search_to%";
    }
}

$sql .= " ORDER BY f.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flights = $stmt->fetchAll();

include '../includes/header.php';
?>

    <div style="padding: 20px;">
        <a href="customerhome.php">&larr; Back to Dashboard</a>
        <h1>Search Flights</h1>

        <form method="GET" action="" style="background:#f4f4f4; padding:15px; border-radius:8px; margin-bottom:20px; display:flex; gap:10px; align-items:end;">
            <div>
                <label>From City:</label><br>
                <input type="text" name="from" value="<?php echo htmlspecialchars($search_from); ?>" placeholder="e.g. Cairo">
            </div>
            <div>
                <label>To City:</label><br>
                <input type="text" name="to" value="<?php echo htmlspecialchars($search_to); ?>" placeholder="e.g. London">
            </div>
            <button type="submit" style="height:40px; background:#007bff; color:white; border:none; padding:0 20px; cursor:pointer;">Search</button>
        </form>

        <table style="width:100%; border-collapse:collapse; background:white; border:1px solid #ddd;">
            <thead>
            <tr style="background:#eee; text-align:left;">
                <th style="padding:10px;">Airline</th>
                <th>Route (Itinerary)</th>
                <th>Fees</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if(count($flights) > 0): ?>
                <?php foreach($flights as $f): ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:10px;"><?php echo htmlspecialchars($f['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($f['itinerary']); ?></td>
                        <td>$<?php echo number_format($f['fees'], 2); ?></td>
                        <td><?php echo date('M d, H:i', strtotime($f['start_time'])); ?></td>
                        <td>
                            <a href="passenger-flight-details.php?id=<?php echo $f['id']; ?>" class="btn" style="background:green; color:white; text-decoration:none; padding:5px 10px; font-size:14px;">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="padding:20px; text-align:center;">No flights found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php include '../includes/footer.php'; ?>