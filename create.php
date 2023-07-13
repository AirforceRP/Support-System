<?php
include 'functions.php';
$pdo = pdo_connect_mysql();
$msg = '';
// Check if account authentication is required
if (authentication_required && !isset($_SESSION['account_loggedin'])) {
    header('Location: login.php');
    exit;
}
// Fetch all the category names from the categories MySQL table
$categories = $pdo->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
// Check if POST data exists (user submitted the form)
if (isset($_POST['title'], $_POST['msg'], $_POST['priority'], $_POST['category'], $_POST['private']) && (isset($_SESSION['account_loggedin']) || isset($_POST['email']))) {
    // Validation checks...
    $email = isset($_SESSION['account_loggedin']) ? $_SESSION['account_email'] : $_POST['email'];
    $name = isset($_SESSION['account_loggedin']) ? $_SESSION['account_name'] : $_POST['name'];
    if (empty($_POST['title']) || empty($email) || empty($_POST['msg']) || empty($_POST['priority'])) {
        $msg = 'Please complete the form!';
    } else if (strlen($_POST['title']) > max_title_length) {
        $msg = 'Title must be less than ' . max_title_length . ' characters long!';
    } else if (strlen($_POST['msg']) > max_msg_length) {
        $msg = 'Message must be less than ' . max_msg_length . ' characters long!';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please provide a valid email address!';
    } else if (!preg_match('/^[a-zA-Z0-9 ]+$/', $name)) {
        $msg = 'Name is not valid!';
    } else {
        // Get the account ID if the user is logged in
        $account_id = isset($_SESSION['account_loggedin']) ? $_SESSION['account_id'] : 0;
        $approved = approval_required ? 0 : 1;
        // Insert new record into the tickets table
        $stmt = $pdo->prepare('INSERT INTO tickets (title, email, msg, priority, category_id, private, account_id, created, approved, full_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([ $_POST['title'], $email, $_POST['msg'], $_POST['priority'], $_POST['category'], $_POST['private'], $account_id, date('Y-m-d H:i:s'), $approved, $name ]);
        // Retrieve the ticket ID
        $ticket_id = $pdo->lastInsertId();
        // Handle the file uploads
        if (attachments && isset($_FILES['attachments'])) {
            // Iterate the uploaded files
            for ($i = 0; $i < count($_FILES['attachments']['name']); $i++) {
                // Get the file extension (png, jpg, etc)
                $ext = strtolower(pathinfo($_FILES['attachments']['name'][$i], PATHINFO_EXTENSION));
                // The file name will contain a unique code to prevent multiple files with the same name.
            	$upload_path = uploads_directory . sha1(uniqid() . $ticket_id . $i) .  '.' . $ext;
            	// Check to make sure the file is valid
            	if (!empty($_FILES['attachments']['tmp_name'][$i]) && in_array($ext, explode(',', attachments_allowed))) {
            		if (!file_exists($upload_path) && $_FILES['attachments']['size'][$i] <= max_allowed_upload_file_size) {
            			// If everything checks out, we can move the uploaded file to its final destination...
            			move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $upload_path);
            			// Insert attachment info into the database (ticket_id, filepath)
            			$stmt = $pdo->prepare('INSERT INTO tickets_uploads (ticket_id, filepath) VALUES (?, ?)');
            	        $stmt->execute([ $ticket_id, $upload_path ]);
            		}
            	}
            }
        }
        // Get the category name
        $category_name = 'none';
        foreach ($categories as $c) {
            $category_name = $c['id'] == $_POST['category'] ? $c['title'] : $category_name;
        }
        // Send the ticket email to the user
        send_ticket_email($email, $ticket_id, $_POST['title'], $_POST['msg'], $_POST['priority'], $category_name, $_POST['private'], 'open');
        // Send the ticket email notification to all admin accounts
        $stmt = $pdo->prepare('SELECT email FROM accounts WHERE `role` = "Admin"');
        $stmt->execute();
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Iterate admin accounts and send notification email
        foreach ($accounts as $account) {
            send_ticket_email($account['email'], $ticket_id, $_POST['title'], $_POST['msg'], $_POST['priority'], $category_name, $_POST['private'], 'notification', $name, $email);
        }
        // Redirect to the view ticket page, the user should see their created ticket on this page
        header('Location: view.php?id=' . $ticket_id . ($_POST['private'] ? '&code=' . md5($ticket_id . $email) : ''));
        exit;
    }
}
?>
<?=template_header('Create Ticket')?>

<div class="content update">

	<h2>Create Ticket</h2>

    <form action="" method="post" class="responsive-width-100" enctype="multipart/form-data">

        <label for="title">Title</label>
        <input type="text" name="title" placeholder="Title" id="title" maxlength="<?=max_title_length?>" required>

        <?php if (!isset($_SESSION['account_loggedin'])): ?>
         <label for="name">Your Name</label>
        <input type="text" name="name" placeholder="John Doe" id="name" required>

        <label for="email">Your Email</label>
        <input type="email" name="email" placeholder="johndoe@example.com" id="email" required>
        <?php endif; ?>

        <label for="category">Category</label>
        <select name="category" id="category">
            <?php foreach($categories as $category): ?>
            <option value="<?=$category['id']?>"><?=$category['title']?></option>
            <?php endforeach; ?>
        </select>

        <div class="wrap">
            <label for="priority">Priority</label>
            <label for="private">Private</label>
            <select name="priority" id="priority" required>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <select name="private" id="private" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <label for="msg">Message</label>
        <div class="msg">
            <textarea name="msg" placeholder="Enter your message here..." id="msg" maxlength="<?=max_msg_length?>" required></textarea>
            <div class="toolbar">
                <i class="format-btn fa-solid fa-bold"></i>
                <i class="format-btn fa-solid fa-italic"></i>
                <i class="format-btn fa-solid fa-underline"></i>
            </div>
        </div>

        <?php if (attachments): ?>
        <label for="attachments">Attachments (Optional)</label>
        <input type="file" name="attachments[]" id="attachments" accept=".<?=str_replace(',', ',.', attachments_allowed)?>" multiple>
        <?php endif; ?>

        <?php if ($msg): ?>
        <p class="error-msg"><?=$msg?></p>
        <?php endif; ?>

        <button type="submit" class="btn">Create</button>
        
    </form>

</div>

<?=template_footer()?>