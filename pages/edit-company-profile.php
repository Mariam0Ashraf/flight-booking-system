<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

if ($_SESSION['role'] != 'company') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = cleanInput($_POST['name']);
    $bio = cleanInput($_POST['bio']);
    $address = cleanInput($_POST['address']);
    $location = cleanInput($_POST['location']);
    $tel = cleanInput($_POST['tel']);

    $logo_sql = "";
    $params = [$name, $bio, $address, $location, $tel];

    // Handle Logo Upload
    if (!empty($_FILES['logo']['name'])) {
        $new_logo = uploadFile($_FILES['logo']);
        if ($new_logo) {
            $logo_sql = ", logo_path = ?";
            $params[] = $new_logo;
        }
    }

    $params[] = $user_id;

    $sql = "UPDATE companies SET name = ?, bio = ?, address = ?, location = ?, tel = ? $logo_sql WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);

    if($stmt->execute($params)) {
        header("Location: profile.php");
        exit();
    } else {
        $error = "Error updating profile.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
$stmt->execute([$user_id]);
$company = $stmt->fetch();

include '../includes/header.php';
?>

    <div style="max-width:600px; margin:0 auto; background:white; padding:20px; border:1px solid #ddd; border-radius:8px;">

        <h2>Edit Company Profile</h2>
        <?php if($error) echo "<div class='alert' style='color:red;'>$error</div>"; ?>

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
                <label>Telephone:</label>
                <input type="text" name="tel" value="<?php echo htmlspecialchars($company['tel']); ?>" required>
            </div>

            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($company['address']); ?>">
            </div>

            <div class="form-group">
                <label>Location (City/Area):</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($company['location']); ?>">
            </div>

            <div class="form-group">
                <label>Update Logo (Optional):</label>
                <input type="file" name="logo">
                <?php if($company['logo_path']): ?>
                    <p><small>Current logo: <a href="../assets/uploads/<?php echo $company['logo_path']; ?>" target="_blank">View</a></small></p>
                <?php endif; ?>
            </div>

            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" class="btn" style="background:green; color:white;">Save Changes</button>
                <a href="profile.php" class="btn" style="background:#666; color:white;">Cancel</a>
            </div>

        </form>
    </div>

<?php include '../includes/footer.php'; ?>