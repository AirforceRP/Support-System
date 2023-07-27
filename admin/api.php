<?php
include 'main.php';
// Remove the time limit (for media uploads)
set_time_limit(0);
// Output JSON
header('Content-Type: application/json; charset=utf-8');
// Conversation endpoint Endpoint
if (isset($_GET['action']) && $_GET['action'] == 'conversation') {
    // Ensure GET ID exists
    if (isset($_GET['id'])) {
        // Gte conversation based on the GET ID parameter
        $stmt = $pdo->prepare('SELECT c.*, m.msg, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name FROM conversations c JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id LEFT JOIN messages m ON m.conversation_id = c.id WHERE c.id = ? AND (c.account_sender_id = ? OR c.account_receiver_id = ?) AND c.status = "Open"');
        $stmt->execute([ $_GET['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        // If the conversation doesn't exist
        if (!$conversation) {
            exit('{"error":"The conversation does not exist!"}');
        }
        $conversation['which'] = $conversation['account_sender_id'] != $_SESSION['account_id'] ? 'sender' : 'receiver';
        $conversation['account_id'] = $_SESSION['account_id'];
        // Retrieve all messages based on the conversation ID
        $stmt = $pdo->prepare('SELECT * FROM messages WHERE conversation_id = ? ORDER BY submit_date DESC LIMIT ?');
        $stmt->bindValue(1, $_GET['id'], PDO::PARAM_INT);
        $stmt->bindValue(2, max_messages, PDO::PARAM_INT);
        $stmt->execute();
        $results = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC), true);
        // Retrieve all word filters from the database
        $word_filters = $pdo->query('SELECT * FROM word_filters')->fetchAll();
        // Update read messages
        $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND account_id != ?');
        $stmt->execute([ $_GET['id'], $_SESSION['account_id'] ]);        
        // Group all messages by the submit date
        foreach ($results as $result) {
            $result['msg'] = str_ireplace(array_column($word_filters, 'word'), array_column($word_filters, 'replacement'), nl2br(decode_emojis(htmlspecialchars($result['msg'], ENT_QUOTES))));
            $conversation['messages'][date('d/m/y', strtotime($result['submit_date']))][] = $result;
        }
        // Encode results to JSON format
        exit(json_encode($conversation));
    }
}
// Archive conversation endpoint
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'conversation_archive') {
    // Update the conversation status to Archived
    $stmt = $pdo->prepare('UPDATE conversations SET status = "Archived" WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);    
    // Mark all messages as read
    $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ?');
    $stmt->execute([ $_GET['id'] ]);   
    // Output success
    exit('{"msg":"Success"}');
}
// Create conversation endpoint
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'conversation_create') {
    // Ensure the account exists
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        // Account exists, so check if there is already a conversation between the sender and receiver
        $stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND (account_sender_id = ? OR account_receiver_id = ?) AND status = "Open"');
        $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], $_GET['id'], $_GET['id'] ]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($conversation) {
            // Conversation already exists, output redirect link to the conversation
            exit('{"url":"messages.php?id=' . $conversation['id'] . '"}');
        }
        // Conversation doesn't exist, create new conversation
        $stmt = $pdo->prepare('INSERT INTO conversations (account_sender_id,account_receiver_id,submit_date,status) VALUES (?,?,?,"Open")');
        $stmt->execute([ $_SESSION['account_id'], $_GET['id'], date('Y-m-d H:i:s')]);
        // Ouput redirect link to the conversation
        exit('{"url":"messages.php?id=' . $pdo->lastInsertId() . '"}');
    } else {
        exit('{"error":"Request no longer available!"}');
    }
}
// New message endpoint
if (isset($_GET['action']) && $_GET['action'] == 'message') {
    // Make sure the user is associated with the conversation
    $stmt = $pdo->prepare('SELECT id FROM conversations WHERE id = ? AND (account_sender_id = ? OR account_receiver_id = ?) AND status = "Open"');
    $stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_SESSION['account_id'] ]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$conversation) {
        // The user isn't not associated with the conversation, output error
        exit('{"error":"The conversation does not exist!"}');
    }
    // Attachments comma-seperated string
    $attachments = '';
    // Check if the user has uploaded files
    if (isset($_FILES['files']) && attachments_enabled) {
        // Iterate all the uploaded files
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            // Get the file extension (png, jpg, etc)
            $ext = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);
            // The file name will contain a unique code to prevent multiple files with the same name.
            $file_path = file_upload_directory . sha1(uniqid() . $i) .  '.' . $ext;
            // Ensure the file is valid
            if (!empty($_FILES['files']['tmp_name'][$i]) && $_FILES['files']['size'][$i] <= max_allowed_upload_file_size && in_array('.' . strtolower($ext), explode(',', file_types_allowed))) {
                // If everything checks out we can move the uploaded file to its final destination...
                move_uploaded_file($_FILES['files']['tmp_name'][$i], '../' . $file_path);
                // Append the new file URL to the attachments variable
                $attachments .= $file_path . ',';
            }
        }
    }
    $attachments = rtrim($attachments, ',');
    // Insert the new message into the database
    $stmt = $pdo->prepare('INSERT INTO messages (conversation_id,account_id,msg,attachments,submit_date) VALUES (?,?,?,?,?)');
    $stmt->execute([ $_POST['id'], $_SESSION['account_id'], $_POST['msg'], $attachments, date('Y-m-d H:i:s') ]);
    // Update status
    $stmt = $pdo->prepare('UPDATE accounts SET status = "Occupied" WHERE id = ?');
    $stmt->execute([ $_SESSION['account_id'] ]);   
    // Output success
    exit('{"msg":"Success"}');
}
// General info endpoint
if (isset($_GET['action']) && $_GET['action'] == 'info') {
    // Retrieve the total number of active accounts
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
    // Output JSON
    exit('{"users_online_total":' . $accounts_total . ', "messages_total":' . $messages_total . ', "requests_total":' . $requests_total . ', "account_status":"' . $account['status'] . '"}');
}
// Account endpoint
if (isset($_GET['action']) && $_GET['action'] == 'account') {
    // Ensure GET ID paramater exists
    if (isset($_GET['id'])) {
        // Retrieve account from database
        $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$account) {
            exit('{"error":"The account does not exist!"}');
        }
        exit(json_encode($account));
    }
}
// Accept request endpoint
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'request') {
    // Ensure the account is waiting for an operator
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE status = "Waiting" AND id = ?');
    $stmt->execute([ $_GET['id'] ]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        // Account is waiting, so update the account status to Idle
        $stmt = $pdo->prepare('UPDATE accounts SET status = "Idle" WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        // Check if conversation already exists
        $stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND (account_sender_id = ? OR account_receiver_id = ?) AND status = "Open"');
        $stmt->execute([ $_SESSION['account_id'], $_SESSION['account_id'], $_GET['id'], $_GET['id'] ]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($conversation) {
            // Conversation already exists, so output message redirect URL
            exit('{"url":"messages.php?id=' . $conversation['id'] . '"}');
        }
        // Conversation doesn't exist, so create one
        $stmt = $pdo->prepare('INSERT INTO conversations (account_sender_id,account_receiver_id,submit_date,status) VALUES (?,?,?,"Open")');
        $stmt->execute([ $_SESSION['account_id'], $_GET['id'], date('Y-m-d H:i:s')]);
        // Output redirect URL
        exit('{"url":"messages.php?id=' . $pdo->lastInsertId() . '"}');
    } else {
        exit('{"error":"Request no longer available!"}');
    }
}
// Delete request endpoint
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] == 'request_delete') {
    // Update the account status
    $stmt = $pdo->prepare('UPDATE accounts SET status = "Idle" WHERE id = ? AND status = "Waiting"');
    $stmt->execute([ $_GET['id'] ]);
    // Ouput success    
    exit('{"msg":"Success"}');
}
// Update status endpoint
if (isset($_GET['action'], $_GET['status']) && $_GET['action'] == 'update_status') {
    // Update the account status
    $stmt = $pdo->prepare('UPDATE accounts SET status = ? WHERE id = ?');
    $stmt->execute([ $_GET['status'], $_SESSION['account_id'] ]);
    // Ouput success    
    exit('{"msg":"Success"}');
}
// Encode results to JSON format
exit('{"error":"No action provided! Please put in a Action"}');

?>