<?php
// Include the main file
include 'main.php';
// Check if the user is logged-in
if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit('error');
}
// Update the account status to Waiting
$stmt = $pdo->prepare('UPDATE accounts SET status = "Waiting" WHERE id = ?');
$stmt->execute([ $_SESSION['chat_widget_account_id'] ]);
// Check if the conversation was already created
$stmt = $pdo->prepare('SELECT * FROM conversations WHERE (account_sender_id = ? OR account_receiver_id = ?) AND submit_date > date_sub(?, interval 1 minute) AND status = "Open"');
$stmt->execute([ $_SESSION['chat_widget_account_id'], $_SESSION['chat_widget_account_id'], date('Y-m-d H:i:s') ]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
// If the conversation exists, output the ID
if ($conversation) {
    exit($conversation['id']);  
}
// Automated responses while waiting to connect to an operator
$automated_responses = [
    'We are trying to connect you with an operator...',
    'Please hang tight...',
    'Any moment now...'
];
// Output response
exit('Msg: ' . $automated_responses[array_rand($automated_responses)]); 
?>