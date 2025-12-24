<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Redirect passengers
if ($role !== 'company') {
    header("Location: customerhome.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();

// 2. Get Flights List
$stmt = $pdo->prepare("SELECT * FROM flights WHERE company_id = ? ORDER BY start_time DESC");
$stmt->execute([$user_id]);
$flights = $stmt->fetchAll();

include '../includes/header.php';
?>

    <div class="dashboard-header">
        <div class="company-profile">
            <?php if($company['logo_path']): ?>
                <img src="../assets/uploads/<?php echo $company['logo_path']; ?>" alt="Logo" style="width:80px; height:80px; border-radius:50%; object-fit:cover;">
            <?php endif; ?>
            <div>
                <h2><?php echo htmlspecialchars($company['name']); ?></h2>
                <p>Balance: $<?php echo number_format($company['account_balance'], 2); ?></p>
            </div>
        </div>

        <div class="actions">
            <a href="profile.php" class="btn">Profile</a>
            <a href="company-inbox.php" class="btn"  color:white;">Messages</a>
            <a href="logout.php" class="btn btn-red">Logout</a>
        </div>
    </div>

    <hr>

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Manage Flights (<?php echo count($flights); ?> Total)</h3>

        <a href="add-flight.php" class="btn" style="background-color: green; text-decoration:none; color:white; padding:10px;">
            + Add New Flight
        </a>
    </div>

    <table class="flight-table" style="width:100%; border-collapse:collapse; margin-top:20px;">
        <thead>
        <tr style="background:#eee; text-align:left;">
            <th>ID</th>
            <th>Flight Name</th>
            <th>Itinerary</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($flights as $flight): ?>
            <tr class="clickable-row" data-href="flight-details.php?id=<?php echo $flight['id']; ?>" style="border-bottom:1px solid #ddd; cursor:pointer;">
                <td><?php echo $flight['id']; ?></td>
                <td><?php echo htmlspecialchars($flight['name']); ?></td>
                <td><?php echo htmlspecialchars($flight['itinerary']); ?></td>
                <td><?php echo date('M d, H:i', strtotime($flight['start_time'])); ?></td>
                <td>
                    <?php
                    if($flight['status'] == 'cancelled') echo '<span style="color:red; font-weight:bold;">Cancelled</span>';
                    elseif($flight['status'] == 'completed') echo '<span style="color:green; font-weight:bold;">Completed</span>';
                    else echo '<span style="color:blue;">Pending</span>';
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // Make table rows clickable
            $(".clickable-row").click(function() {
                window.location = $(this).data("href");
            });
        });
    </script>

<?php include '../includes/footer.php'; ?>