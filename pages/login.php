<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'company') {
        header("Location: dashboard.php");
    } else {
        header("Location: customerhome.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'company') {
            header("Location: dashboard.php");
        } else {
            // FIX: Redirect to Customer Home
            header("Location: customerhome.php");
        }
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}

include '../includes/header.php';
?>

    <h2>Login</h2>
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
        <button type="submit">Login</button>
    </form>

<?php include '../includes/footer.php'; ?>