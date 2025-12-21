<?php
require_once '../core/db_connect.php';
require_once '../core/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

if ($_SESSION['role'] != 'company') {
    header("Location: customerhome.php");
    exit();
}

$sql = "SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as partner_id
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id, $user_id]);
$partners = $stmt->fetchAll();

$chat_list = [];
foreach ($partners as $p) {
    $pid = $p['partner_id'];

    $stmt_name = $pdo->prepare("SELECT name, photo_path FROM passengers WHERE user_id = ?");
    $stmt_name->execute([$pid]);
    $passenger = $stmt_name->fetch();

    if ($passenger) {
        $chat_list[] = [
            'id' => $pid,
            'name' => $passenger['name'],
            'photo' => $passenger['photo_path']
        ];
    }
}

include '../includes/header.php';
?>

    <div style="max-width:800px; margin:0 auto;">
        <a href="dashboard.php">&larr; Back to Dashboard</a>
        <h1>Customer Messages</h1>

        <?php if (empty($chat_list)): ?>
            <div class="alert" style="background:#f8f9fa; border:1px solid #ddd; padding:20px; text-align:center;">
                <p>No messages yet.</p>
            </div>
        <?php else: ?>
            <div style="background:white; border:1px solid #ddd; border-radius:8px;">
                <?php foreach ($chat_list as $chat): ?>
                    <div style="border-bottom:1px solid #eee; padding:15px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <?php if($chat['photo']): ?>
                                <img src="../assets/uploads/<?php echo $chat['photo']; ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:50px; height:50px; background:#ccc; border-radius:50%;"></div>
                            <?php endif; ?>

                            <h3 style="margin:0; font-size:18px;"><?php echo htmlspecialchars($chat['name']); ?></h3>
                        </div>

                        <a href="messages.php?partner_id=<?php echo $chat['id']; ?>" class="btn" style="background:#007bff; color:white; padding:8px 15px; text-decoration:none; border-radius:4px;">
                            Open Chat
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>