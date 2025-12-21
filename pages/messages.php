<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;

if ($partner_id == 0) {
    echo "No user selected to chat with.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['msg_content'])) {
    $msg = cleanInput($_POST['msg_content']);
    if (!empty($msg)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $partner_id, $msg]);
    }
}

$sql = "SELECT m.*, 
        (SELECT role FROM users WHERE id = m.sender_id) as sender_role
        FROM messages m 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $partner_id, $partner_id, $user_id]);
$messages = $stmt->fetchAll();


$stmt_c = $pdo->prepare("SELECT name FROM companies WHERE user_id = ?");
$stmt_c->execute([$partner_id]);
$partner_name = $stmt_c->fetchColumn();

if (!$partner_name) {
    $stmt_p = $pdo->prepare("SELECT name FROM passengers WHERE user_id = ?");
    $stmt_p->execute([$partner_id]);
    $partner_name = $stmt_p->fetchColumn();
}

include '../includes/header.php';
?>

    <div style="max-width:600px; margin:0 auto; background:white; border:1px solid #ccc; height:80vh; display:flex; flex-direction:column;">

        <div style="background:#eee; padding:15px; border-bottom:1px solid #ccc; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">Chat with <?php echo htmlspecialchars($partner_name); ?></h3>
            <a href="javascript:history.back()" style="text-decoration:none;">&times; Close</a>
        </div>

        <div style="flex:1; padding:20px; overflow-y:auto; background:#f9f9f9;">
            <?php foreach($messages as $msg): ?>
                <?php
                $is_me = ($msg['sender_id'] == $user_id);
                $align = $is_me ? "right" : "left";
                $bg = $is_me ? "#dcf8c6" : "#ffffff";
                ?>
                <div style="text-align:<?php echo $align; ?>; margin-bottom:10px;">
                    <div style="display:inline-block; background:<?php echo $bg; ?>; padding:8px 12px; border-radius:10px; border:1px solid #ddd; max-width:70%;">
                        <?php echo htmlspecialchars($msg['message']); ?>
                        <div style="font-size:10px; color:#999; margin-top:5px; text-align:right;">
                            <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" style="padding:15px; background:white; border-top:1px solid #ccc; display:flex;">
            <input type="text" name="msg_content" placeholder="Type a message..." required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:4px;">
            <button type="submit" style="margin-left:10px; padding:10px 20px; background:#007bff; color:white; border:none; border-radius:4px;">Send</button>
        </form>

    </div>

    <script>
        var msgArea = document.querySelector('div[style*="overflow-y:auto"]');
        msgArea.scrollTop = msgArea.scrollHeight;
    </script>

<?php include '../includes/footer.php'; ?>