<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

if ($_SESSION['role'] != 'company') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = cleanInput($_POST['name']);
    $bio = cleanInput($_POST['bio']);
    $address = cleanInput($_POST['address']);

    $logo_sql = "";
    $params = [$name, $bio, $address];

    if (!empty($_FILES['logo']['name'])) {
        $new_logo = uploadFile($_FILES['logo']);
        if ($new_logo) {
            $logo_sql = ", logo_path = ?";
            $params[] = $new_logo;
        }
    }

    $params[] = $user_id;

    $sql = "UPDATE companies SET name = ?, bio = ?, address = ? $logo_sql WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);

    if($stmt->execute($params)) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM flights WHERE company_id = ? ORDER BY start_time DESC");
$stmt->execute([$user_id]);
$flights = $stmt->fetchAll();

include '../includes/header.php';
?>

    <div class="profile-container">

        <?php if($message): ?>
            <div class="ui-state-highlight ui-corner-all" style="padding: 0.7em; margin-bottom: 20px;">
                <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="display:flex; align-items:center; gap:20px; margin-bottom:20px;">
            <?php if($company['logo_path']): ?>
                <img src="assets/uploads/<?php echo $company['logo_path']; ?>" style="width:120px; height:120px; border-radius:10px; object-fit:cover; border:1px solid #ccc;">
            <?php else: ?>
                <img src="assets/img/default-logo.png" style="width:120px; height:120px; background:#ddd; border-radius:10px;">
            <?php endif; ?>
            <div>
                <h1><?php echo htmlspecialchars($company['name']); ?></h1>
                <p><strong>Balance:</strong> $<?php echo number_format($company['account_balance'], 2); ?></p>
            </div>
        </div>

        <button id="btnView" disabled>View Profile</button>
        <button id="btnEdit">Edit Profile</button>
        <hr>

        <div id="viewSection">
            <h3>About Us</h3>
            <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($company['bio'])); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($company['address']); ?></p>
            <p><strong>Telephone:</strong> <?php echo htmlspecialchars($company['tel']); ?></p>

            <h3>Our Flights History</h3>
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
                        <td><?php echo ucfirst($f['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="editSection" style="display:none;">
            <h3>Edit Details</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($company['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Bio:</label>
                    <textarea name="bio" rows="5"><?php echo htmlspecialchars($company['bio']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Address:</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($company['address']); ?>">
                </div>
                <div class="form-group">
                    <label>Update Logo (Optional):</label>
                    <input type="file" name="logo">
                </div>
                <button type="submit" name="update_profile" class="btn">Save Changes</button>
            </form>
        </div>

    </div>

    <script>
        $(document).ready(function(){
            $("#btnEdit").click(function(){
                $("#viewSection").hide();
                $("#editSection").show();
                $("#btnEdit").prop('disabled', true);
                $("#btnView").prop('disabled', false);
            });
            $("#btnView").click(function(){
                $("#editSection").hide();
                $("#viewSection").show();
                $("#btnView").prop('disabled', true);
                $("#btnEdit").prop('disabled', false);
            });
        });
    </script>

<?php include '../includes/footer.php'; ?>