<?php
// include the main file
include 'main.php';
// Validate the form data
if (!isset($_POST['name'], $_POST['email'])) {
    exit('Please enter a valid name and email address!');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	exit('Please enter a valid email address!');
}
if (!preg_match('/^[a-zA-Z\s]+$/', $_POST['name'])) {
    exit('Name must contain only letters!');
}
// Select account from the database based on the email address
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
$stmt->execute([ $_POST['email'] ]);
// Fetch the results and return them as an associative array
$account = $stmt->fetch(PDO::FETCH_ASSOC);
// Does the account exist?
if ($account) {
    // Yes, it does... Check whether the user is an operator or guest
    if ($account['role'] == 'Operator' || $account['role'] == 'Admin') {
        // User is operator, so show the password input field on the front-end
        exit('Please use the <a href="admin/">admin panel</a> to login.');
    } else if ($account['role'] == 'Guest') {
        // User is a guest
        // Authenticate the guest
        if (authentication_required && !isset($_POST['password'])) {
            exit('MSG_LOGIN_REQUIRED');
        }
        if (!empty($account['password'])) {
            // User is an operator and provided a password
            if (isset($_POST['password']) && password_verify($_POST['password'], $account['password'])) {
                // Password is correct! Authenticate the operator
                $_SESSION['chat_widget_account_loggedin'] = TRUE;
                $_SESSION['chat_widget_account_id'] = $account['id'];
                $_SESSION['chat_widget_account_role'] = $account['role']; 
                // Update the secret code
                update_info($pdo, $account['id'], $account['email'], $account['secret']);
                // Ouput: success
                exit('MSG_SUCCESS');
            } else {
                // Invalid password
                exit('Invalid credentials!');
            }
        } else {
            // Guest don't need a password
            $_SESSION['chat_widget_account_loggedin'] = TRUE;
            $_SESSION['chat_widget_account_id'] = $account['id'];
            $_SESSION['chat_widget_account_role'] = $account['role']; 
            // Update secret code
            update_info($pdo, $account['id'], $account['email'], $account['secret']);
                // Output: success
            exit('MSG_SUCCESS');
        }
    }
} else {
    // Check if authentication is required 
    if (authentication_required && !isset($_POST['password'])) {
        exit('MSG_LOGIN_REQUIRED');
    }
    // Hash password, if there is one
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
    // Accounts doesn't exist, so create one
    $stmt = $pdo->prepare('INSERT INTO accounts (email, password, full_name, role, last_seen, registered) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([ $_POST['email'], $password, $_POST['name'] ? $_POST['name'] : 'Guest', 'Guest', date('Y-m-d H:i:s'), date('Y-m-d H:i:s') ]);
    // Retrieve the account ID
    $id = $pdo->lastInsertId();
    // Authenticate the new user
    $_SESSION['chat_widget_account_loggedin'] = TRUE;
    $_SESSION['chat_widget_account_id'] = $id;   
    $_SESSION['chat_widget_account_role'] = 'Guest'; 
    // Update secret code
    update_info($pdo, $id, $_POST['email']);
    // Output: success
    exit('MSG_CREATE_SUCCESS');
}
?>