<?php
include 'main.php';
// Default ticket values
$ticket = [
    'title' => '',
    'msg' => '',
    'full_name' => '',
    'email' => '',
    'created' => date('Y-m-d H:i:s'),
    'ticket_status' => 'open',
    'priority' => 'low',
    'category_id' => 1,
    'private' => 0,
    'account_id' => 0,
    'approved' => 1
];
// Retrieve accounts from the database
$accounts = $pdo->query('SELECT * FROM accounts')->fetchAll(PDO::FETCH_ASSOC);
// Retrieve categories from the database
$categories = $pdo->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
// Check whether the ticket ID is specified
if (isset($_GET['id'])) {
    // Retrieve the ticket from the database
    $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing ticket
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the ticket
        $stmt = $pdo->prepare('UPDATE tickets SET title = ?, msg = ?, full_name = ?, email = ?, created = ?, ticket_status = ?, priority = ?, category_id = ?, private = ?, account_id = ?, approved = ? WHERE id = ?');
        $stmt->execute([ $_POST['title'], $_POST['msg'], $_POST['full_name'], $_POST['email'], date('Y-m-d H:i:s', strtotime($_POST['created'])), $_POST['ticket_status'], $_POST['priority'], $_POST['category_id'], $_POST['private'], $_POST['account_id'], $_POST['approved'], $_GET['id'] ]);
        header('Location: tickets.php?success_msg=2');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Redirect and delete the ticket
        header('Location: tickets.php?delete=' . $_GET['id']);
        exit;
    }
} else {
    // Create a new ticket
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO tickets (title, msg, full_name, email, created, ticket_status, priority, category_id, private, account_id, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([ $_POST['title'], $_POST['msg'], $_POST['full_name'], $_POST['email'], date('Y-m-d H:i:s', strtotime($_POST['created'])), $_POST['ticket_status'], $_POST['priority'], $_POST['category_id'], $_POST['private'], $_POST['account_id'], $_POST['approved'] ]);
        header('Location: tickets.php?success_msg=1');
        exit;
    }
}
?>
<?=template_admin_header($page . ' Ticket', 'tickets', 'manage')?>

<form action="" method="post">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100"><?=$page?> Ticket</h2>
        <a href="tickets.php" class="btn alt mar-right-2">Cancel</a>
        <?php if ($page == 'Edit'): ?>
        <input type="submit" name="delete" value="Delete" class="btn red mar-right-2" onclick="return confirm('Are you sure you want to delete this ticket?')">
        <?php endif; ?>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>

    <div class="content-block">

        <div class="form responsive-width-100">

            <label for="title"><i class="required">*</i> Title</label>
            <input id="title" type="text" name="title" placeholder="Title" value="<?=htmlspecialchars($ticket['title'], ENT_QUOTES)?>" required>

            <label for="msg"><i class="required">*</i> Message</label>
            <textarea id="msg" name="msg" placeholder="Write your ticket message..." required><?=htmlspecialchars($ticket['msg'], ENT_QUOTES)?></textarea>

            <label for="full_name"><i class="required">*</i> Full Name</label>
            <input id="full_name" type="text" name="full_name" placeholder="Full Name" value="<?=htmlspecialchars($ticket['full_name'], ENT_QUOTES)?>" required>

            <label for="email"><i class="required">*</i> Email</label>
            <input id="email" type="email" name="email" placeholder="Email" value="<?=htmlspecialchars($ticket['email'], ENT_QUOTES)?>" required>

            <label for="created"><i class="required">*</i> Created</label>
            <input id="created" type="datetime-local" name="created" value="<?=date('Y-m-d\TH:i', strtotime($ticket['created']))?>" required>

            <label for="ticket_status"><i class="required">*</i> Status</label>
            <select id="ticket_status" name="ticket_status" required>
                <option value="open"<?=$ticket['ticket_status']=='open'?' selected':''?>>Open</option>
                <option value="closed"<?=$ticket['ticket_status']=='closed'?' selected':''?>>Closed</option>
                <option value="resolved"<?=$ticket['ticket_status']=='resolved'?' selected':''?>>Resolved</option>
            </select>

            <label for="priority"><i class="required">*</i> Priority</label>
            <select id="priority" name="priority" required>
                <option value="low"<?=$ticket['priority']=='low'?' selected':''?>>Low</option>
                <option value="medium"<?=$ticket['priority']=='medium'?' selected':''?>>Medium</option>
                <option value="high"<?=$ticket['priority']=='high'?' selected':''?>>High</option>
            </select>

            <label for="category_id"><i class="required">*</i> Category</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                <option value="<?=$category['id']?>"<?=$ticket['category_id']==$category['id']?' selected':''?>><?=$category['title']?></option>
                <?php endforeach; ?>
            </select>

            <label for="private"><i class="required">*</i> Private</label>
            <select id="private" name="private" required>
                <option value="0"<?=$ticket['private']==0?' selected':''?>>No</option>
                <option value="1"<?=$ticket['private']==1?' selected':''?>>Yes</option>
            </select>

            <label for="account_id">Account</label>
            <select id="account_id" name="account_id" required>
                <option value="0">(none)</option>
                <?php foreach ($accounts as $account): ?>
                <option value="<?=$account['id']?>"<?=$ticket['account_id']==$account['id']?' selected':''?>><?=$account['id']?> - <?=$account['email']?></option>
                <?php endforeach; ?>
            </select>

            <label for="approved"><i class="required">*</i> Approved</label>
            <select id="approved" name="approved" required>
                <option value="0"<?=$ticket['approved']==0?' selected':''?>>No</option>
                <option value="1"<?=$ticket['approved']==1?' selected':''?>>Yes</option>
            </select>

        </div>

    </div>

</form>

<?=template_admin_footer()?>