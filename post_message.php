<?php
// Include the main file
include 'main.php';
// Check if the user is logged-in
if (!is_loggedin($pdo)) {
    // User isn't logged-in
    exit('error');
}
// Ensure the GET ID and msg params exists
if (!isset($_POST['id'], $_POST['msg'])) {
    exit('error');
}
// Make sure the user is associated with the conversation
$stmt = $pdo->prepare('SELECT id FROM conversations WHERE id = ? AND (account_sender_id = ? OR account_receiver_id = ?) AND status = "Open"');
$stmt->execute([ $_POST['id'], $_SESSION['chat_widget_account_id'], $_SESSION['chat_widget_account_id'] ]);
$conversation = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$conversation) {
    // The user isn't not associated with the conversation, output error
    exit('error');
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
            move_uploaded_file($_FILES['files']['tmp_name'][$i], $file_path);
            // Append the new file URL to the attachments variable
            $attachments .= $file_path . ',';
        }
    }
}
$attachments = rtrim($attachments, ',');
// Insert the new message into the database
$stmt = $pdo->prepare('INSERT INTO messages (conversation_id,account_id,msg,attachments,submit_date) VALUES (?,?,?,?,?)');
$stmt->execute([ $_POST['id'], $_SESSION['chat_widget_account_id'], $_POST['msg'], $attachments, date('Y-m-d H:i:s') ]);
// Output success
exit('success');
?>