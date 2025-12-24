<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$message = "";

if(isset($_POST['update_profile'])) {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $tel = cleanInput($_POST['tel']);
    $password = $_POST['password'];

    $add_balance = floatval($_POST['add_balance']);

    $sql_passenger = "UPDATE passengers SET name = ?, tel = ? WHERE user_id = ?";
    $params_passenger = [$name, $tel, $user_id];

    if (!empty($_FILES['photo']['name'])) {
        $new_photo = uploadFile($_FILES['photo']);
        if ($new_photo) {
            $sql_passenger = "UPDATE passengers SET name = ?, tel = ?, photo_path = ? WHERE user_id = ?";
            $params_passenger = [$name, $tel, $new_photo, $user_id];
        }
    }

    $stmt = $pdo->prepare($sql_passenger);
    $stmt->execute($params_passenger);

    if (!empty($_FILES['passport']['name'])) {
        $new_passport = uploadFile($_FILES['passport']);
        if ($new_passport) {
            $stmt_pp = $pdo->prepare("UPDATE passengers SET passport_img_path = ? WHERE user_id = ?");
            $stmt_pp->execute([$new_passport, $user_id]);
        }
    }

    if ($add_balance > 0) {
        $stmt_bal = $pdo->prepare("UPDATE passengers SET account_balance = account_balance + ? WHERE user_id = ?");
        $stmt_bal->execute([$add_balance, $user_id]);
        $message .= " Balance updated.";
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_users = "UPDATE users SET email = ?, password = ? WHERE id = ?";
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute([$email, $hashed_password, $user_id]);
    } else {
        $sql_users = "UPDATE users SET email = ? WHERE id = ?";
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute([$email, $user_id]);
    }

    if(empty($message)) {
        $message = "Profile Updated Successfully!";
    }
}

$query = "SELECT u.email, u.password, p.* FROM users u 
          JOIN passengers p ON u.id = p.user_id 
          WHERE u.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

    <h2>Edit Profile</h2>

<?php if($message): ?>
    <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border:1px solid #c3e6cb;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

    <div style="border: 1px solid #ccc; padding: 20px; background: #fff; max-width:600px;">

        <div style="text-align:center; margin-bottom:20px;">
            <?php if(!empty($user['photo_path'])): ?>
                <img src="../assets/uploads/<?php echo $user['photo_path']; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #333;">
            <?php else: ?>
                <div style="width:120px; height:120px; background:#ccc; border-radius:50%; display:inline-block;"></div>
                <p>No profile photo.</p>
            <?php endif; ?>

            <h3 style="color:green;">Balance: $<?php echo number_format($user['account_balance'], 2); ?></h3>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

            <label>New Password (leave blank to keep current):</label><br>
            <input type="password" name="password" placeholder="Enter new password"><br><br>

            <label>Tel:</label><br>
            <input type="text" name="tel" value="<?php echo htmlspecialchars($user['tel']); ?>"><br><br>

            <div style="background:#f9f9f9; padding:10px; border:1px solid #eee; margin-bottom:20px;">
                <label style="font-weight:bold;">Add Balance ($):</label><br>
                <input type="number" name="add_balance" step="0.01" min="0" placeholder="e.g. 500.00" style="width:100%; padding:8px;">
                <small style="color:#666;">Enter amount to deposit into your account.</small>
            </div>

            <hr>

            <label>Change Profile Photo:</label><br>
            <input type="file" name="photo"><br><br>

            <label>Change Passport Image:</label><br>
            <input type="file" name="passport"><br><br>

            <?php if(!empty($user['passport_img_path'])): ?>
                <p>Current Passport on file: <a href="../assets/uploads/<?php echo $user['passport_img_path']; ?>" target="_blank">View</a></p>
            <?php endif; ?>

            <button type="submit" name="update_profile" style="background-color: blue; color: white; padding: 10px 20px; border: none; cursor: pointer; width:100%;">Save Changes</button>
        </form>
    </div>

    <br>
    <a href="customerhome.php">&larr; Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>