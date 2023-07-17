<?php
include '../functions.php';

// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();

// Facebook OAuth configuration

// If the captured code parameter exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => facebook_oauth_client_id,
        'client_secret' => facebook_oauth_client_secret,
        'redirect_uri' => facebook_oauth_client_redirect_uri,
        'state' => $_GET['state'],
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v13.0/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse the response to get the access token
    $token_data = json_decode($response, true);

    // Make sure access token is valid
    if (isset($token_data['access_token']) && !empty($token_data['access_token'])) {
        // Execute cURL request to retrieve the user info associated with the Facebook account
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v13.0/me?fields=name,email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token_data['access_token'],
            'User-Agent: My-App',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $profile = json_decode($response, true);

        // Make sure the profile data exists
        if (isset($profile['email'])) {
            // Check if account exists in the database
            $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
            $stmt->execute([$profile['email']]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get the current date
            $date = date('Y-m-d\TH:i:s');

            if ($account) {
                // Account exists! Bind the SQL data
                $facebook_name = $account['full_name'];
                $role = $account['role'];
                $id = $account['id'];
            } else {
                // Insert new account
                $username = '';

                // Determine Facebook name and remove all special characters
                $facebook_name = '';
                $facebook_name .= isset($profile['name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['name']) : '';

                // Default role
                $role = 'Member';

                // Generate a random password
                $password = password_hash(uniqid() . $date, PASSWORD_DEFAULT);

                // Account doesn't exist, create it
                $stmt = $pdo->prepare('INSERT INTO accounts (full_name, password, email, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$facebook_name, $password, $profile['email'], $role]);

                // Account ID
                $id = $pdo->lastInsertId();
            }

            // Authenticate the user
            session_regenerate_id();
            $_SESSION['account_loggedin'] = true;
            $_SESSION['account_id'] = $id;
            $_SESSION['account_role'] = $role;
            $_SESSION['account_email'] = $profile['email'];
            $_SESSION['account_name'] = $facebook_name;

            // Chat system
            $_SESSION['chat_widget_account_loggedin'] = true;
            $_SESSION['chat_widget_account_id'] = $id;
            $_SESSION['chat_widget_account_role'] = $role;

            update_info($pdo, $id, $profile['email']);

            // Redirect to home page
            header('Location: index.php');
            exit;
        } else {
            exit('Could not retrieve profile information! Please try again later!');
        }
    } else {
        exit('Invalid access token! Please try again later!');
    }
} else {
    // Define params and redirect to Facebook Authentication page
    $params = [
        'client_id' => facebook_oauth_client_id,
        'redirect_uri' => facebook_oauth_client_redirect_uri,
        'state' => uniqid(),
        'scope' => 'email',
    ];

    header('Location: https://www.facebook.com/v13.0/dialog/oauth?' . http_build_query($params));
    exit;
}
?>
