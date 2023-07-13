<?php
include 'functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Check if the ID param in the URL exists
if (!isset($_GET['id'])) {
    exit('No ID specified!');
}
// MySQL query that selects the ticket by the ID column, using the ID GET request variable
$stmt = $pdo->prepare('SELECT t.*, a.full_name AS a_name, a.email AS a_email, c.title AS category FROM tickets t LEFT JOIN categories c ON c.id = t.category_id LEFT JOIN accounts a ON a.id = t.account_id WHERE t.id = ? AND t.approved = 1');
$stmt->execute([ $_GET['id'] ]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
// Retrieve ticket uplaods from the database
$stmt = $pdo->prepare('SELECT * FROM tickets_uploads WHERE ticket_id = ?');
$stmt->execute([ $_GET['id'] ]);
$ticket_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Check if ticket exists
if (!$ticket) {
    exit('Invalid ticket ID!');
}
// Check if private
if ($ticket['private'] && (!isset($_GET['code']) || $_GET['code'] != md5($ticket['id'] . $ticket['email']))) {
    exit('This is a private ticket!');
}
// If the ticket is private, append the code to the URL
$private_url = $ticket['private'] ? '&code=' . md5($ticket['id'] . $ticket['email']) : '';
// Update status
if (isset($_GET['status'], $_SESSION['account_loggedin']) && ($_SESSION['account_role'] == 'Admin' || $_SESSION['account_id'] == $ticket['account_id']) && in_array($_GET['status'], ['closed', 'resolved']) && $ticket['ticket_status'] == 'open') {
    // Update ticket status in the database
    $stmt = $pdo->prepare('UPDATE tickets SET ticket_status = ? WHERE id = ?');
    $stmt->execute([ $_GET['status'], $_GET['id'] ]);
    // Send updated ticket email to user
    send_ticket_email($ticket['email'], $ticket['id'], $ticket['title'], $ticket['msg'], $ticket['priority'], $ticket['category'], $ticket['private'], $_GET['status'], 'update');
    // Redirect to ticket page
    header('Location: view.php?id=' . $_GET['id'] . $private_url);
    exit;
}
// Check if the comment form has been submitted
if (isset($_POST['msg'], $_SESSION['account_loggedin']) && !empty($_POST['msg']) && $ticket['ticket_status'] == 'open') {
    // Insert the new comment into the "tickets_comments" table
    $stmt = $pdo->prepare('INSERT INTO tickets_comments (ticket_id, msg, account_id) VALUES (?, ?, ?)');
    $stmt->execute([ $_GET['id'], $_POST['msg'], $_SESSION['account_id'] ]);
    // Send updated ticket email to user
    send_ticket_email($ticket['email'], $ticket['id'], $ticket['title'], $ticket['msg'], $ticket['priority'], $ticket['category'], $ticket['private'], $ticket['status'], 'comment');
    // Redirect to ticket page
    header('Location: view.php?id=' . $_GET['id'] . $private_url);
    exit;
}
// Retrieve the ticket comments from the database
$stmt = $pdo->prepare('SELECT tc.*, a.full_name, a.role FROM tickets_comments tc LEFT JOIN accounts a ON a.id = tc.account_id WHERE tc.ticket_id = ? ORDER BY tc.created');
$stmt->execute([ $_GET['id'] ]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_header(htmlspecialchars($ticket['title'], ENT_QUOTES))?>

<div class="content view">

	<h2><?=htmlspecialchars($ticket['title'], ENT_QUOTES)?> <span class="<?=$ticket['ticket_status']?>"><?=$ticket['ticket_status']?></span></h2>

    <div class="profile">
        <div class="icon">
            <span style="background-color:<?=color_from_string($ticket['a_name'] ?? $ticket['full_name'])?>"><?=strtoupper(substr($ticket['a_name'] ?? $ticket['full_name'], 0, 1))?></span>
        </div>
        <div class="info">
            <p class="name"><?=htmlspecialchars($ticket['a_name'] ?? $ticket['full_name'], ENT_QUOTES)?></p>
            <?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin'): ?>
            <p class="email"><?=$ticket['a_email'] ?? $ticket['email']?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="ticket">
        <div>
            <p>
                <span class="priority label <?=$ticket['priority']?>" title="Priority"><?=$ticket['priority']?></span>
                <span class="category" title="Category"><?=$ticket['category']?></span>
            </p>
            <p class="created"><?=date('F dS, G:ia', strtotime($ticket['created']))?></p>
        </div>
        <p class="msg"><?=str_ireplace(['&lt;strong&gt;','&lt;/strong&gt;','&lt;u&gt;','&lt;/u&gt;','&lt;i&gt;','&lt;/i&gt;'], ['<strong>','</strong>','<u>','</u>','<i>','</i>'], nl2br(htmlspecialchars($ticket['msg'], ENT_QUOTES)))?></p>
    </div>

    <?php if (!empty($ticket_uploads)): ?>
    <h3 class="uploads-header">Attachment(s)</h3>
    <div class="uploads">
        <?php foreach($ticket_uploads as $ticket_upload): ?>
        <a href="<?=$ticket_upload['filepath']?>" download>
            <?php if (getimagesize($ticket_upload['filepath'])): ?>
            <img src="<?=$ticket_upload['filepath']?>" width="80" height="80" alt="">
            <?php else: ?>
            <i class="fas fa-file"></i>
            <span><?=pathinfo($ticket_upload['filepath'], PATHINFO_EXTENSION)?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['account_loggedin']) && ($_SESSION['account_role'] == 'Admin' || $_SESSION['account_id'] == $ticket['account_id'])): ?>
    <div class="btns">
        <?php if ($_SESSION['account_role'] == 'Admin'): ?>
        <a href="admin/ticket.php?id=<?=$_GET['id']?>" target="_blank" class="btn">Edit</a>
        <?php endif; ?>
        <?php if ($ticket['ticket_status'] == 'open'): ?>
        <a href="view.php?id=<?=$_GET['id']?>&status=resolved<?=$private_url?>" class="btn" onclick="return confirm('Are you sure you want to mark the ticket as resolved?')">Resolve</a>
        <a href="view.php?id=<?=$_GET['id']?>&status=closed<?=$private_url?>" class="btn red" onclick="return confirm('Are you sure you want to close the ticket?')">Close</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="comments">
        <?php foreach($comments as $comment): ?>
        <div class="comment">
            <div>
                <i class="fas fa-comment fa-2x"></i>
            </div>
            <p>
                <span class="comment-header">
                    <?php if ($comment['full_name']): ?>
                    <span class="name<?=$comment['role'] == 'Admin' ? ' is-admin' : ''?>"><?=htmlspecialchars($comment['full_name'], ENT_QUOTES)?></span>
                    <?php endif; ?>
                    <span class="date"><?=date('F dS, G:ia', strtotime($comment['created']))?></span>
                    <?php if (isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin'): ?>
                    <a href="admin/comment.php?id=<?=$comment['id']?>" target="_blank" class="edit"><i class="fa-solid fa-pen fa-xs"></i></a>
                    <?php endif; ?>
                </span>
                <?=str_ireplace(['&lt;strong&gt;','&lt;/strong&gt;','&lt;u&gt;','&lt;/u&gt;','&lt;i&gt;','&lt;/i&gt;'], ['<strong>','</strong>','<u>','</u>','<i>','</i>'], nl2br(htmlspecialchars($comment['msg'], ENT_QUOTES)))?>
            </p>
        </div>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['account_loggedin']) && $ticket['ticket_status'] == 'open'): ?>
        <form action="" method="post" class="responsive-width-100">
            <div class="msg">
                <textarea name="msg" placeholder="Enter your comment..." class="responsive-width-100" maxlength="<?=max_msg_length?>" required></textarea>
                <div class="toolbar">
                    <i class="format-btn fa-solid fa-bold"></i>
                    <i class="format-btn fa-solid fa-italic"></i>
                    <i class="format-btn fa-solid fa-underline"></i>
                </div>
            </div>
            <button type="submit" class="btn">Post Comment</button>
        </form>
        <?php endif; ?>
    </div>

</div>

<?=template_footer()?>