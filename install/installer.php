<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database Configuration
    $db_host = $_POST['db_host'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? '';

    // Tickets Configuration
    $authentication_required = isset($_POST['authentication_required']) ? true : false;
    $approval_required = isset($_POST['approval_required']) ? true : false;
    $num_tickets_per_page = $_POST['num_tickets_per_page'] ?? '';
    $attachments = isset($_POST['attachments']) ? true : false;
    $attachments_allowed = $_POST['attachments_allowed'] ?? '';
    $uploads_directory = $_POST['uploads_directory'] ?? '';
    $max_allowed_upload_file_size = $_POST['max_allowed_upload_file_size'] ?? '';
    $max_title_length = $_POST['max_title_length'] ?? '';
    $max_msg_length = $_POST['max_msg_length'] ?? '';
    $tickets_directory_url = $_POST['tickets_directory_url'] ?? '';

    // SMTP Configuration
    $SMTP = isset($_POST['SMTP']) ? true : false;
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = $_POST['smtp_port'] ?? '';
    $smtp_user = $_POST['smtp_user'] ?? '';
    $smtp_pass = $_POST['smtp_pass'] ?? '';

    // Create config content
    $config_content = <<<EOT
    <?php
    // Database Configuration
    define('db_host', '$db_host');
    define('db_user', '$db_user');
    define('db_pass', '$db_pass');
    define('db_name', '$db_name');
    define('db_charset', 'utf8');

    // Tickets Configuration
    define('authentication_required', $authentication_required);
    define('approval_required', $approval_required);
    define('num_tickets_per_page', '$num_tickets_per_page');
    define('attachments', $attachments);
    define('attachments_allowed', '$attachments_allowed');
    define('uploads_directory', '$uploads_directory');
    define('max_allowed_upload_file_size', '$max_allowed_upload_file_size');
    define('max_title_length', '$max_title_length');
    define('max_msg_length', '$max_msg_length');
    define('tickets_directory_url', '$tickets_directory_url');


    // SMTP Configuration
    define('SMTP', $SMTP);
    define('smtp_host', '$smtp_host');
    define('smtp_port', '$smtp_port');
    define('smtp_user', '$smtp_user');
    define('smtp_pass', '$smtp_pass');
    EOT;

    // Write to config file
    file_put_contents('config.php', $config_content);

    echo 'Configuration file generated successfully!';
    echo '<a class="button" href="install-sql.php">Install SQL</a>'
} else if () {

} else {
    echo 'Invalid request method.';
}

?>
