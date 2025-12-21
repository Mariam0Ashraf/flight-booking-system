<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Booking System</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 10px; }
    </style>
</head>
<body>
<nav>
    <a href="/termProject/index.php">Home</a>

    <?php if(isset($_SESSION['user_id'])): ?>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'company'): ?>
            <a href="/termProject/pages/dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="/termProject/pages/customerhome.php">My Flights</a>
        <?php endif; ?>

        <a href="/termProject/pages/logout.php">Logout</a>

    <?php else: ?>

        <a href="/termProject/pages/login.php">Login</a>
        <a href="/termProject/pages/register.php">Register</a>

    <?php endif; ?>
</nav>
<hr>
<div class="container">