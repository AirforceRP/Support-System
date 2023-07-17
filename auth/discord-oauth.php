<?php
include '../functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();

// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    $params = [
        'code' => $_GET['code'],
        'client_id' => discord_oauth_client_id,
        'client_secret' => discord_oauth_client_secret,
        'redirect_uri' => discord_oauth_client_redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/oauth2/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, true);

    // Make sure access token is valid
    if (isset($response['access_token']) && !empty($response['access_token'])) {
        // Execute cURL request to retrieve the user info associated with the Discord account
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://discord.com/api/users/@me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
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

            // If the account exists...
            if ($account) {
                // Account exists! Bind the SQL data
                $template_name = $account['full_name'];
                $role = $account['role'];
                $id = $account['id'];
            } else {
                // Insert new account
                $username = '';

                // Determine template name and remove all special characters
                $template_name = '';
                $template_name .= isset($profile['given_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['given_name']) : '';
                $template_name .= $template_name ? ' ' : '';
                $template_name .= isset($profile['family_name']) ? preg_replace('/[^a-zA-Z0-9]/s', '', $profile['family_name']) : '';

                // Default role
                $role = 'Member';

                // Generate a random password
                $password = password_hash(uniqid() . $date, PASSWORD_DEFAULT);

                // Account doesn't exist, create it
                $stmt = $pdo->prepare('INSERT INTO accounts (full_name, password, email, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$template_name, $password, $profile['email'], $role]);

                // Account ID
                $id = $pdo->lastInsertId();
            }

            // Authenticate the user
            session_regenerate_id();
            $_SESSION['account_loggedin'] = TRUE;
            $_SESSION['account_id'] = $id;
            $_SESSION['account_role'] = $role;
            $_SESSION['account_email'] = $profile['email'];
            $_SESSION['account_name'] = $template_name;

            // Chat system
            $_SESSION['chat_widget_account_loggedin'] = TRUE;
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
    // Define params and redirect to the Discord authentication page
    $params = [
        'response_type' => 'code',
        'client_id' => discord_oauth_client_id,
        'redirect_uri' => discord_oauth_client_redirect_uri,
        'scope' => 'identify email',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    header('Location: https://discord.com/api/oauth2/authorize?' . http_build_query($params));
    exit;
}
?>
