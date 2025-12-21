<?php
require_once 'core/functions.php';
include 'includes/header.php';
?>

    <h1>Welcome to SkyHigh Booking</h1>

<?php if(isset($_SESSION['user_id'])): ?>
    <div class="ui-widget">
        <div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                <strong>Hello!</strong> You are logged in as a <b><?php echo ucfirst($_SESSION['role']); ?></b>.</p>
        </div>
    </div>
<?php else: ?>
    <p>Please <a href="/termProject/pages/login.php">Login</a> or <a href="/termProject/pages/register.php">Register</a> to continue.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>