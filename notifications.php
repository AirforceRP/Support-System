<?php
// Include the main file
include 'main.php';
// Check if the user is logged-in
if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit;
}
// Retrieve the total number of messages
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM messages m JOIN conversations c ON c.id = m.conversation_id AND (c.account_sender_id = ? OR c.account_receiver_id = ?) WHERE m.account_id != ? AND m.is_read = 0');
$stmt->execute([ $_SESSION['chat_widget_account_id'], $_SESSION['chat_widget_account_id'], $_SESSION['chat_widget_account_id'] ]);
$messages_total = $stmt->fetchColumn();
// Output total
exit($messages_total);
?>