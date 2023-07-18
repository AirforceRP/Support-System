<?php
// Your MySQL database hostname.
define('db_host','');
// Your MySQL database username.
define('db_user','');
// Your MySQL database password.
define('db_pass','');
// Your MySQL database name.
define('db_name','');
// Your MySQL database charset.
define('db_charset','utf8');
/* Tickets */
// Authentication will require the user to login or register before they can create a ticket.
define('authentication_required',true);
// If enabled, the ticket will require approval.
define('approval_required',false);
// The number of tickets to show per page.
define('num_tickets_per_page','5');
// If enabled, the user will be able to attach files to the ticket.
define('attachments',true);
// The list of attachment types allowed to be uploaded.
define('attachments_allowed','png,jpg,jpeg,gif,webp,bmp,doc,docx,xls,xlsx,ppt,pptx,pdf,zip,rar,txt,csv');
// The directory where the uploaded files will be stored.
define('uploads_directory','uploads/');
// The maximum size of the uploaded image in bytes (5MB default).
define('max_allowed_upload_file_size','5000000');
// The maximum size of the title in characters.
define('max_title_length','200');
// The maximum size of the message in characters.
define('max_msg_length','10000');
// The tickets directory URL (e.g. http://example.com/ticketsystem/).
define('tickets_directory_url','');
/* Mail */
// Send mail to the users, etc?
define('mail_enabled',false);
// This is the email address that will be used to send emails.
define('mail_from','');
// The name of your business.
define('mail_name','');
// If enabled, the mail will be sent using SMTP.
define('SMTP',true);
// Your SMTP hostname.
define('smtp_host','');
// Your SMTP port number.
define('smtp_port','');
// Your SMTP username.
define('smtp_user','');
// Your SMTP Password.
define('smtp_pass','');
/* Discord Oauth */
// The OAuth client ID associated with your API Twitter Developer account.
define('discord_oauth_client_id', '');
// The OAuth client secret associated with your API Twitter Developer account.
define('discord_oauth_client_secret','');
// The URL to the Twitter OAuth file.
define('discord_oauth_redirect_uri','');
/* Facebook Oauth */
// The OAuth client ID associated with your API Twitter Developer account.
define('facebook_oauth_client_id', '');
// The OAuth client secret associated with your API Twitter Developer account.
define('facebook_oauth_client_secret','');
// The URL to the Twitter OAuth file.
define('facebook_oauth_redirect_uri','');
/* GitHub Oauth */
// The OAuth client ID associated with your API Twitter Developer account.
define('github_oauth_client_id', '');
// The OAuth client secret associated with your API Twitter Developer account.
define('github_oauth_client_secret','');
// The URL to the Twitter OAuth file.
define('github_oauth_redirect_uri','');
/* Google OAuth */
// The OAuth client ID associated with your API console account.
define('google_oauth_client_id','');
// The OAuth client secret associated with your API console account.
define('google_oauth_client_secret','');
// The URL to the Google OAuth file.
define('google_oauth_redirect_uri','');
/* steamauth */
// The Steam API Key
define('steamauth_openid_key', '');
// The URL to the Steam OpenID file.
define('steamauth_openid_redirect_uri', '');
/* Twitter OAuth */
// The OAuth client ID associated with your API Twitter Developer account.
define('twitter_oauth_client_id', '');
// The OAuth client secret associated with your API Twitter Developer account.
define('twitter_oauth_client_secret','');
// The URL to the Twitter OAuth file.
define('twitter_oauth_redirect_uri','');
/* Microsoft OAuth */
// The OAuth client ID associated with your API Microsoft Developer account.
define('microsoft_oauth_client_id', '');
// The OAuth client secret associated with your API Microsoft Developer account.
define('microsoft_oauth_client_secret', '');
// The URL to the Microsoft OAuth file.
define('microsoft_oauth_client_redirect_uri', '');

// The Livechat Script ON or OFF TRUE = ONLINE FALSE = OFFLINE
define('livechat_ONLINE',true);
// Max number of messages
define('max_messages', 30);
// Emoji list
define('emoji_list', '1F600,1F601,1F602,1F603,1F604,1F605,1F606,1F607,1F608,1F609,1F60A,1F60B,1F60C,1F60D,1F60E,1F60F,1F610,1F611,1F612,1F613,1F614,1F615,1F616,1F617,1F618,1F619,1F61A,1F61B,1F61C,1F61D,1F61E,1F61F,1F620,1F621,1F622,1F623,1F624,1F625,1F626,1F627,1F628,1F629,1F62A,1F62B,1F62C,1F62D,1F62E,1F62F,1F630,1F631,1F632,1F633,1F634,1F635,1F636,1F637,1F641,1F642,1F643,1F644,1F910,1F911,1F912,1F913,1F914,1F915,1F920,1F921,1F922,1F923,1F924,1F925,1F927,1F928,1F929,1F92A,1F92B,1F92C,1F92D,1F92E,1F92F,1F9D0');
/* Attachments */
// Enabled?
define('attachments_enabled', true);
// Upload directory
define('file_upload_directory', 'uploads/');
// File extension whitelist
define('file_types_allowed', '.png,.jpg,.jpeg,.webp,.gif,.bmp');
/* Performance */
// Measured in miliseconds
define('conversation_refresh_rate', 5000);
define('requests_refresh_rate', 10000);
define('users_online_refresh_rate', 10000);
define('general_info_refresh_rate', 10000);
?>
