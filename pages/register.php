<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = cleanInput($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = cleanInput($_POST['role']); // company or passenger

    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$email, $password, $role]);
        $user_id = $pdo->lastInsertId();

        $_SESSION['temp_user_id'] = $user_id;
        $_SESSION['temp_role'] = $role;
        header("Location: complete-profile.php");
        exit();
    } catch (PDOException $e) {
        $error = "Email already exists.";
    }
}

include '../includes/header.php';
?>

    <h2>Step 1: Create Account</h2>
<?php if(isset($error)) echo "<div class='alert'>$error</div>"; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>I am a:</label>
            <select name="role">
                <option value="passenger">Passenger</option>
                <option value="company">Company</option>
            </select>
        </div>
        <button type="submit">Next Step</button>
    </form>

<?php include '../includes/footer.php'; ?>