<?php
// Include the configuration file
include_once '../config.php';
include_once '../main.php';
// Check if admin is logged in
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: ../login.php');
    exit;
}
// If the user is not admin redirect them back to the login page
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
$stmt->execute([ $_SESSION['account_id'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// Ensure account is an admin or operator
if (!$account || ($account['role'] != 'Admin' && $account['role'] != 'Operator')) {
    header('Location: login.php');
    exit;
}
if (!isset($_GET['ajax']) && ($account['status'] == 'Occupied' || $account['status'] == 'Waiting')) {
    // Update status
    $stmt = $pdo->prepare('UPDATE accounts SET status = "Idle" WHERE id = ?');
    $stmt->execute([ $_SESSION['account_id'] ]);  
    $account['status'] = 'Idle';
}
// Update last seen date
$stmt = $pdo->prepare('UPDATE accounts SET last_seen = ? WHERE id = ?');
$stmt->execute([ date('Y-m-d H:i:s'), $_SESSION['account_id'] ]);
// Template admin header
function template_admin_header($title, $selected = 'orders', $selected_child = 'view') {
    global $pdo, $account;
    // Retrieve the total number of accounts in the last 5 mins
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM accounts WHERE last_seen > date_sub(?, interval 5 minute)');
    $stmt->execute([ date('Y-m-d H:i:s') ]);
    $accounts_total = $stmt->fetchColumn();
    // Retrieve the total number of requests
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM accounts WHERE status = "Waiting"');
    $stmt->execute();
    $requests_total = $stmt->fetchColumn();
    // Retrieve the total number of messages
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM messages m JOIN conversations c ON c.id = m.conversation_id AND (c.account_sender_id = ? OR c.account_receiver_id = ?) WHERE m.account_id != ? AND m.is_read = 0');
    $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
    $messages_total = $stmt->fetchColumn();
    // Admin links template
    $admin_links = '
        <a href="index.php"' . ($selected == 'dashboard' ? ' class="selected"' : '') . '><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <a href="messages.php"' . ($selected == 'conversations' ? ' class="selected"' : '') . '><i class="fas fa-comments"></i>Messages<span class="note messages-total">' . number_format($messages_total) . '</span></a>
        <div class="sub">
            <a href="messages.php"' . ($selected == 'conversations' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Messages</a>
            <a href="chat_logs.php"' . ($selected == 'conversations' && $selected_child == 'chat_logs' ? ' class="selected"' : '') . '><span>&#9724;</span>View Chat Logs</a>
        </div>
        <a href="requests.php"' . ($selected == 'requests' ? ' class="selected"' : '') . '><i class="fas fa-user-check"></i>Requests<span class="note requests-total">' . number_format($requests_total) . '</span></a>
        <a href="users_online.php"' . ($selected == 'users_online' ? ' class="selected"' : '') . '><i class="fas fa-user-clock"></i>Users Online<span class="note users-online-total">' . number_format($accounts_total) . '</span></a>
        <a href="accounts.php"' . ($selected == 'accounts' ? ' class="selected"' : '') . '><i class="fas fa-users"></i>Accounts</a>
        <div class="sub">
            <a href="accounts.php"' . ($selected == 'accounts' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Accounts</a>
            ' . ($_SESSION['account_role'] == 'Admin' ? '<a href="account.php"' . ($selected == 'accounts' && $selected_child == 'manage' ? ' class="selected"' : '') . '><span>&#9724;</span>Create Account</a>' : '') . '
        </div>
    ';
    // Show additional links if account is admin
    if ($_SESSION['account_role'] == 'Admin') {
        $admin_links .= '
            <a href="tickets.php"' . ($selected == 'tickets' ? ' class="selected"' : '') . '><i class="fa-solid fa-ticket"></i>Tickets</a>
            <div class="sub">
                <a href="tickets.php"' . ($selected == 'tickets' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Tickets</a>
                <a href="ticket.php"' . ($selected == 'tickets' && $selected_child == 'manage' ? ' class="selected"' : '') . '><span>&#9724;</span>Create Ticket</a>
                <a href="tickets_export.php"' . ($selected == 'tickets' && $selected_child == 'export' ? ' class="selected"' : '') . '><span>&#9724;</span>Export</a>
                <a href="tickets_import.php"' . ($selected == 'tickets' && $selected_child == 'import' ? ' class="selected"' : '') . '><span>&#9724;</span>Import</a>
            </div>
            <a href="comments.php"' . ($selected == 'comments' ? ' class="selected"' : '') . '><i class="fas fa-comments"></i>Comments</a>
            <div class="sub">
                <a href="comments.php"' . ($selected == 'comments' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Comments</a>
                <a href="comment.php"' . ($selected == 'comments' && $selected_child == 'manage' ? ' class="selected"' : '') . '><span>&#9724;</span>Create Comment</a>
            </div>
            <a href="categories.php"' . ($selected == 'categories' ? ' class="selected"' : '') . '><i class="fas fa-list"></i>Categories</a>
            <div class="sub">
                <a href="categories.php"' . ($selected == 'categories' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Categories</a>
                <a href="category.php"' . ($selected == 'categories' && $selected_child == 'manage' ? ' class="selected"' : '') . '><span>&#9724;</span>Create Category</a>
            </div>
            <a href="email-templates.php"' . ($selected == 'emailtemplates' ? ' class="selected"' : '') . '><i class="fa-solid fa-envelope"></i>Email Templates</a>
            <a href="settings.php"' . ($selected == 'settings' ? ' class="selected"' : '') . '><i class="fas fa-tools"></i>Settings</a>
            <div class="sub">
                <a href="settings.php"' . ($selected == 'settings' && $selected_child == 'view' ? ' class="selected"' : '') . '><span>&#9724;</span>View Settings</a>
                <a href="word_filters.php"' . ($selected == 'settings' && $selected_child == 'word_filters' ? ' class="selected"' : '') . '><span>&#9724;</span>Word Filters</a>
                <a href="presets.php"' . ($selected == 'settings' && $selected_child == 'presets' ? ' class="selected"' : '') . '><span>&#9724;</span>Message Presets</a>
            </div>
        ';
    }
    // Profile image
    $profile_img = '
    <div class="profile-img">
        ' . (!empty($account['photo_url']) ? '<img src="' . htmlspecialchars($account['photo_url'], ENT_QUOTES) . '" alt="' . htmlspecialchars($account['photo_url'], ENT_QUOTES) . '\'s Profile Image">' : '<span style="background-color:' . color_from_string($account['full_name']) . '">' . strtoupper(substr($account['full_name'], 0, 1)) . '</span>') . '
        <i class="' . strtolower($account['status']) . '"></i>
    </div>
    ';
// DO NOT INDENT THE BELOW CODE
echo <<<EOT
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>$title</title>
		<link href="admin.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.1.1/css/all.css">
	</head>
	<body class="admin">
        <aside class="responsive-width-100 responsive-hidden">
            <h1>Admin</h1>
            $admin_links
            <div class="footer">
                Version 1.0.0
            </div>
        </aside>
        <main class="responsive-width-100">
            <header>
                <a class="responsive-toggle" href="#">
                    <i class="fas fa-bars"></i>
                </a>
                <div class="space-between"></div>
                <div class="dropdown right">
                    $profile_img
                    <div class="list">
                        <div>
                            Status
                            <span class="list">
                                <a href="#" class="update-status" data-status="Idle">Online</a>
                                <a href="#" class="update-status" data-status="Away">Away</a>
                            </span>
                        </div>
                        <a href="account.php?id={$_SESSION['account_id']}">Edit Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </header>
EOT;
}
// Template admin footer
function template_admin_footer($js_script = '') {
        $js_script = $js_script ? '<script>' . $js_script . '</script>' : '';
        $conversation_refresh_rate = conversation_refresh_rate;
        $requests_refresh_rate = requests_refresh_rate;
        $users_online_refresh_rate = users_online_refresh_rate;
        $general_info_refresh_rate = general_info_refresh_rate;
        $attachments_enabled = attachments_enabled ? 'true' : 'false';
// DO NOT INDENT THE BELOW CODE
echo <<<EOT
        </main>
        <script>
        const conversation_refresh_rate = {$conversation_refresh_rate};
        const requests_refresh_rate = {$requests_refresh_rate};
        const users_online_refresh_rate = {$users_online_refresh_rate};
        const general_info_refresh_rate = {$general_info_refresh_rate};
        const attachments_enabled = {$attachments_enabled};
        const account_id = {$_SESSION['account_id']};
        const account_role = "{$_SESSION['account_role']}";
        </script>
        <script src="admin.js"></script>
        {$js_script}
    </body>
</html>
EOT;
}
// Convert date to elapsed string function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = ['y' => 'year','m' => 'month','w' => 'week','d' => 'day','h' => 'hour','i' => 'minute','s' => 'second'];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
