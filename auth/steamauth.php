/* =======WORK IN PROGRESS========= */

<?php
require 'openid.php';
include '../functions.php';

// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();

$openid = new LightOpenID(steamauth_openid_redirect_uri);
$openid->identity = 'http://steamcommunity.com/openid';

// If the captured SteamID exists and is valid
if ($openid->mode === 'id_res') {
    if ($openid->validate()) {
        $steamId = $openid->identity;

        // Check if account exists in database
        $stmt = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
        $stmt->execute([$steamId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get the current date
        $date = date('Y-m-d\TH:i:s');

        // If the account exists...
        if ($account) {
            // Account exists! Bind the SQL data
            $steam_name = $account['full_name'];
            $role = $account['role'];
            $id = $account['id'];
        } else {
            // Account doesn't exist, create it
            $username = '';

            // Fetch user information from Steam API using the SteamID
            $apiUrl = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . steamauth_openid_key . '&steamids=' . $steamId;
            $response = file_get_contents($apiUrl);
            $jsonResponse = json_decode($response, true);

            // Check if the API response is valid
            if ($jsonResponse && isset($jsonResponse['response']['players'][0])) {
                $playerData = $jsonResponse['response']['players'][0];

                // Extract the Steam name and remove special characters
                $steam_name = preg_replace('/[^a-zA-Z0-9]/s', '', $playerData['personaname']);

                // Default role
                $role = 'Member';

                // Generate a random password
                $password = password_hash(uniqid() . $date, PASSWORD_DEFAULT);

                // Create a new account
                $stmt = $pdo->prepare('INSERT INTO accounts (full_name, password, email, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$steam_name, $password, '', $role]);

                // Account ID
                $id = $pdo->lastInsertId();
            } else {
                exit('Could not retrieve player information from Steam API! Please try again later!');
            }
        }

        // Authenticate the user
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_id'] = $id;
        $_SESSION['account_role'] = $role;
        $_SESSION['account_steam_id'] = $steamId;
        $_SESSION['account_name'] = $steam_name;

        // Chat system
        $_SESSION['chat_widget_account_loggedin'] = TRUE;
        $_SESSION['chat_widget_account_id'] = $id;
        $_SESSION['chat_widget_account_role'] = $role;

        update_info($pdo, $id, $steamId);

        // Redirect to home page
        header('Location: ../index.php');
        exit;
    } else {
        exit('Steam authentication failed!');
    }
} else {
    // Redirect to Steam OpenID Authentication page
    $openid->returnUrl = steamauth_openid_redirect_uri;
    header('Location: ' . $openid->authUrl());
    exit;
}
?>
