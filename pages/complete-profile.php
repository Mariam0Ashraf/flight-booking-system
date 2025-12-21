<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: register.php");
    exit();
}

$role = $_SESSION['temp_role'];
$user_id = $_SESSION['temp_user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = cleanInput($_POST['name']);
    $tel = cleanInput($_POST['tel']);

    if ($role == 'company') {
        $bio = cleanInput($_POST['bio']);
        $address = cleanInput($_POST['address']);

        $logo = uploadFile($_FILES['logo']);

        $stmt = $pdo->prepare("INSERT INTO companies (user_id, name, bio, address, tel, logo_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $bio, $address, $tel, $logo]);
    }
    else {
        $photo = uploadFile($_FILES['photo']);
        $passport = uploadFile($_FILES['passport']);

        $stmt = $pdo->prepare("INSERT INTO passengers (user_id, name, tel, photo_path, passport_img_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $tel, $photo, $passport]);
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_role']);

    if ($role == 'company') {
        header("Location: dashboard.php");
    } else {
        header("Location: customerhome.php");
    }
    exit();
}

include '../includes/header.php';
?>

    <h2>Step 2: Complete <?php echo ucfirst($role); ?> Profile</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Telephone:</label>
            <input type="text" name="tel" required>
        </div>

        <?php if($role == 'company'): ?>
            <div class="form-group">
                <label>Bio:</label>
                <textarea name="bio"></textarea>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address">
            </div>
            <div class="form-group">
                <label>Company Logo:</label>
                <input type="file" name="logo" required>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label>Personal Photo:</label>
                <input type="file" name="photo" required>
            </div>
            <div class="form-group">
                <label>Passport Image:</label>
                <input type="file" name="passport" required>
            </div>
        <?php endif; ?>

        <button type="submit">Finish Registration</button>
    </form>

<?php include '../includes/footer.php'; ?>