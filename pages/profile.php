<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

if ($_SESSION['role'] != 'company') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM flights WHERE company_id = ? ORDER BY start_time DESC");
$stmt->execute([$user_id]);
$flights = $stmt->fetchAll();

include '../includes/header.php';
?>

    <div class="profile-container" style="max-width:800px; margin:0 auto; background:white; padding:20px; border:1px solid #ddd; border-radius:8px;">

        <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:20px;">
            <?php if($company['logo_path']): ?>
                <img src="../assets/uploads/<?php echo $company['logo_path']; ?>" style="width:100px; height:100px; border-radius:10px; object-fit:cover; border:1px solid #ccc;">
            <?php else: ?>
                <img src="../assets/img/default-logo.png" style="width:100px; height:100px; background:#ddd; border-radius:10px;">
            <?php endif; ?>

            <div style="flex-grow:1;">
                <h1 style="margin:0;"><?php echo htmlspecialchars($company['name']); ?></h1>
                <p style="font-size:1.2em; margin-top:5px;">
                    <strong>Account Balance:</strong>
                    <span style="color:green; font-weight:bold;">$<?php echo number_format($company['account_balance'], 2); ?></span>
                </p>
            </div>
        </div>

        <div style="margin-bottom:20px; display:flex; gap:10px;">
            <a href="dashboard.php" class="btn" ">&larr; Back to Dashboard</a>
            <a href="edit-company-profile.php" class="btn"  color:white;">Edit Profile</a>
        </div>

        <div id="viewSection">
            <h3>Company Details</h3>
            <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($company['bio'])); ?></p>
            <p><strong>Telephone:</strong> <?php echo htmlspecialchars($company['tel']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($company['address']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($company['location']); ?></p>

            <h3 style="margin-top:30px;">Flight History</h3>
            <?php if(count($flights) > 0): ?>
                <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                    <tr style="background:#f4f4f4; text-align:left;">
                        <th style="padding:8px;">ID</th>
                        <th>Name</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach($flights as $f): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px;"><?php echo $f['id']; ?></td>
                            <td><a href="flight-details.php?id=<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></a></td>
                            <td><?php echo htmlspecialchars($f['itinerary']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($f['start_time'])); ?></td>
                            <td>
                                <?php
                                if($f['status']=='completed') echo '<span style="color:green">Completed</span>';
                                elseif($f['status']=='cancelled') echo '<span style="color:red">Cancelled</span>';
                                else echo 'Pending';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No flights created yet.</p>
            <?php endif; ?>
        </div>

    </div>

<?php include '../includes/footer.php'; ?>