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

    $sql_passenger = "UPDATE passengers SET name = ?, tel = ? WHERE user_id = ?";
    $params_passenger = [$name, $tel, $user_id];

    if (!empty($_FILES['photo']['name'])) {
        $new_photo = uploadFile($_FILES['photo']);
        if ($new_photo) {
            $sql_passenger = "UPDATE passengers SET name = ?, tel = ?, photo_path = ? WHERE user_id = ?";
            $params_passenger = [$name, $tel, $new_photo, $user_id];
        }
    }

    if (!empty($_FILES['passport']['name'])) {
        $new_passport = uploadFile($_FILES['passport']);
        if ($new_passport) {
            $stmt_pp = $pdo->prepare("UPDATE passengers SET passport_img_path = ? WHERE user_id = ?");
            $stmt_pp->execute([$new_passport, $user_id]);
        }
    }

    $stmt = $pdo->prepare($sql_passenger);
    $stmt->execute($params_passenger);

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

    $message = "Profile Updated Successfully!";
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

    <div style="border: 1px solid #ccc; padding: 20px; background: #fff;">
        <?php if(!empty($user['photo_path'])): ?>
            <img src="../assets/uploads/<?php echo $user['photo_path']; ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #333;">
        <?php else: ?>
            <p>No profile photo set.</p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
            <label>Name:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

            <label>New Password (leave blank to keep current):</label><br>
            <input type="password" name="password" placeholder="Enter new password"><br><br>

            <label>Tel:</label><br>
            <input type="text" name="tel" value="<?php echo htmlspecialchars($user['tel']); ?>"><br><br>

            <hr>

            <label>Change Profile Photo:</label><br>
            <input type="file" name="photo"><br><br>

            <label>Change Passport Image:</label><br>
            <input type="file" name="passport"><br><br>

            <?php if(!empty($user['passport_img_path'])): ?>
                <p>Current Passport on file: <a href="../assets/uploads/<?php echo $user['passport_img_path']; ?>" target="_blank">View</a></p>
            <?php endif; ?>

            <button type="submit" name="update_profile" style="background-color: blue; color: white; padding: 10px 20px; border: none; cursor: pointer;">Save Changes</button>
        </form>
    </div>

    <br>
    <a href="customerhome.php">&larr; Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>