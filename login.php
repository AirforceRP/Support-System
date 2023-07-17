<html>
<head>
     <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- FontAwesome CSS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-V6SXTx5M7XUol+eAARvnnKzeUfFheT/50dVBNKHyKZ1kzRrFiz+JUq3mdJFONRq1yWs2Q4Wbj1ER7RQ9VfQs6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>    
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include 'functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Error output variable
$login_errors = '';
// User authentication
if (isset($_POST['login'], $_POST['email'], $_POST['password'])) {
    // Retrieve the account from the database
    $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
    $stmt->execute([ $_POST['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // Check if account exists and the password is correct
    if ($account && password_verify($_POST['password'], $account['password'])) {
        // Declare the session variables. Session variable we'll use to determine whether a user is logged-in
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_id'] = $account['id'];
        $_SESSION['account_role'] = $account['role'];
        $_SESSION['account_email'] = $account['email'];
        $_SESSION['account_name'] = $account['full_name'];
        // Chat system
        $_SESSION['chat_widget_account_loggedin'] = TRUE;
        $_SESSION['chat_widget_account_id'] = $account['id'];
        $_SESSION['chat_widget_account_role'] = $account['role']; 
        update_info($pdo, $account['id'], $account['email'], $account['secret']);
        // Redirect to the tickets page
        header('Location: tickets.php');
        exit;
    } else {
        $login_errors = 'Incorrect email and/or password!';
    }
}
// Error output variable
$register_errors = [];
// User registration
if (isset($_POST['register'], $_POST['name'], $_POST['password'], $_POST['cpassword'], $_POST['email'])) {
    // Make sure the submitted registration values are not empty.
    if (empty($_POST['name']) || empty($_POST['password']) || empty($_POST['cpassword']) || empty($_POST['email'])) {
    	// One or more values are empty.
    	$register_errors[] = 'Please complete the registration form!';
    }
    // Check to see if the email is valid.
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    	$register_errors[] = 'Email is not valid!';
    }
    // Name must contain only characters and numbers.
    if (!preg_match('/^[a-zA-Z0-9 ]+$/', $_POST['name'])) {
        $register_errors[] = 'Name is not valid!';
    }
    // Name must be between 3 and 20 characters long.
    if (strlen($_POST['name']) > 20 || strlen($_POST['name']) < 3) {
    	$register_errors[] = 'Name must be between 3 and 20 characters long!';
    }
    // Password must be between 5 and 20 characters long.
    if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
    	$register_errors[] = 'Password must be between 5 and 20 characters long!';
    }
    // Check if the password and confirm password match.
    if ($_POST['password'] != $_POST['cpassword']) {
    	$register_errors[] = 'Passwords do not match!';
    }
    // IF there are no errors...
    if (!$register_errors) {
        // Check if the account with that email already exist
        $stmt = $pdo->prepare('SELECT id, password FROM accounts WHERE email = ?');
        $stmt->execute([ $_POST['email'] ]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        // Check if the account exists in the database
        if ($account) {
        	// Email already exist
        	$register_errors[] = 'Email already exists! Please login instead!';
        } else {
            // We do not want to expose passwords in our database and therefore we shall hash the password
        	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        	// Email doesn't exist, insert new account...
        	$stmt = $pdo->prepare('INSERT INTO accounts (full_name, password, email) VALUES (?, ?, ?)');
        	$stmt->execute([ $_POST['name'], $password, $_POST['email'] ]);
            // Automatically authenticate the user
            $id = $pdo->lastInsertId();
            $_SESSION['account_loggedin'] = TRUE;
            $_SESSION['account_id'] = $id;
            $_SESSION['account_role'] = 'Member';
            $_SESSION['account_email'] = $_POST['email'];
            $_SESSION['account_name'] = $_POST['name'];
            // Chat system
            $_SESSION['chat_widget_account_loggedin'] = TRUE;
            $_SESSION['chat_widget_account_id'] = $id;
            $_SESSION['chat_widget_account_role'] = 'Member'; 
            // Update secret code
            update_info($pdo, $id, $_POST['email']);
            // Redirect to the tickets page
            header('Location: tickets.php');
            exit;
        }
    }
}
?>
<?=template_header('Login')?>

<style>
.gl-btn {
  display: flex;
  text-decoration: none;
  position: relative;
  border-radius: 4px;
  text-align: center;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 500;
  font-size: 14px;
  width: 92%;
  margin: 15px 0 5px 0;
  padding: 15px;
  transition: background-color 0.2s;
}
.gl-btn i {
  position: absolute;
  left: 15px;
}
.gl-btn:hover {
  color: #fff;
  transition: background-color 0.2s;
}
.gl-btn {
  background-color: #d6523e;
}
.gl-btn:hover {
  background-color: #cf412c;
}
</style>

<div class="content login">

    <div class="con">

    	<h2>Login</h2>

        <form action="" method="post">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Email" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Password" required>

            <?php if ($login_errors): ?>
            <p class="error-msg"><?=$login_errors?></p>
            <?php endif; ?>

            <button type="submit" name="login" class="btn">Login</button>
            
           

        </form>

    </div>

    <div class="con">

    	<h2>Register</h2>

        <form action="" method="post" autocomplete="off">

            <label for="name">Name</label>
            <input id="name" type="text" name="name" placeholder="Name" required>

            <label for="rpassword">Password</label>
            <input id="rpassword" type="password" name="password" placeholder="Password" autocomplete="new-password" required>

            <label for="cpassword">Confirm Password</label>
            <input id="cpassword" type="password" name="cpassword" placeholder="Confirm Password" required>

            <label for="remail">Email</label>
            <input id="remail" type="email" name="email" placeholder="Email" required>

            <?php if ($register_errors): ?>
            <p class="error-msg"><?=implode('<br>', $register_errors)?></p>
            <?php endif; ?>

            <button type="submit" name="register" class="btn">Register</button>
            
            

        </form>

    </div>

</div>
</body>
</html>
