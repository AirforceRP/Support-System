<?php
include '../functions.php';

// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();

// GitHub OAuth configuration


// If the captured code parameter exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => github_oauth_client_id,
        'client_secret' => github_oauth_client_secret,
        'redirect_uri' => github_oauth_client_redirect_uri,
        'state' => $_GET['state'],
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://github.com/login/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse the response to get the access token
    parse_str($response, $token_data);

    // Make sure access token is valid
    if (isset($token_data['access_token']) && !empty($token_data['access_token'])) {
        // Execute cURL request to retrieve the user info associated with the GitHub account
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: token ' . $token_data['access_token'],
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
                $github_name = $account['full_name'];
                $role = $account['role'];
                $id = $account['id'];
            } else {
                // Insert new account
                $username = '';

                // Determine GitHub name and remove all special characters
                $github_name = '';
                $github_name .= isset($profile['name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['name']) : '';

                // Default role
                $role = 'Member';

                // Generate a random password
                $password = password_hash(uniqid() . $date, PASSWORD_DEFAULT);

                // Account doesn't exist, create it
                $stmt = $pdo->prepare('INSERT INTO accounts (full_name, password, email, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$github_name, $password, $profile['email'], $role]);

                // Account ID
                $id = $pdo->lastInsertId();
            }

            // Authenticate the user
            session_regenerate_id();
            $_SESSION['account_loggedin'] = true;
            $_SESSION['account_id'] = $id;
            $_SESSION['account_role'] = $role;
            $_SESSION['account_email'] = $profile['email'];
            $_SESSION['account_name'] = $github_name;

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
    // Define params and redirect to GitHub Authentication page
    $params = [
        'client_id' => github_oauth_client_id
        'redirect_uri' => github_oauth_client_redirect_uri
        'state' => uniqid(),
        'scope' => 'user:email',
    ];

    header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
    exit;
}
?>
