<?php
include 'main.php';
// Retrieve the total number of accounts
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM accounts WHERE last_seen > date_sub(?, interval 5 minute)');
$stmt->execute([ date('Y-m-d H:i:s') ]);
$accounts_total = $stmt->fetchColumn();
// SQL query to get all accounts online in the last 5 mins
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE last_seen > date_sub(?, interval 5 minute) ORDER BY full_name');
// Bind params
$stmt->execute([ date('Y-m-d H:i:s') ]);
// Retrieve query results
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Users online template below
?>
<?=template_admin_header(number_format($accounts_total) . ' Users Online', 'users_online', 'view')?>

<div class="content-title">
    <h2><?=number_format($accounts_total)?> User<?=$accounts_total!=1?'s':''?> Online</h2>
</div>

<div class="content-block cover">
    <div class="users-online">
        <div class="list">
            <div class="form responsive-width-100">
                <label for="search">
                    <input type="text" class="search" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </label>
            </div>
            <div class="users scroll">
                <?php foreach ($accounts as $account): ?>
                <a href="#" class="user" data-id="<?=$account['id']?>" data-status="<?=$account['status']?>" data-ip="<?=$account['ip']?>" data-useragent="<?=htmlspecialchars($account['user_agent'], ENT_QUOTES)?>" data-role="<?=$account['role']?>" data-email="<?=$account['email']?>" data-registered="<?=$account['registered']?>">
                    <div class="profile-img">
                        <?=!empty($account['photo_url']) ? '<img src="' . htmlspecialchars($account['photo_url'], ENT_QUOTES) . '" alt="' . htmlspecialchars($account['photo_url'], ENT_QUOTES) . '\'s Profile Image">' : '<span style="background-color:' . color_from_string($account['full_name']) . '">' . strtoupper(substr($account['full_name'], 0, 1)) . '</span>';?>
                        <i class="<?=strtolower($account['status'])?>"></i>
                    </div>
                    <div class="details">
                        <h3 class="<?=strtolower($account['role'])?>"><?=htmlspecialchars($account['full_name'], ENT_QUOTES)?></h3>
                        <p><?=time_elapsed_string($account['last_seen'])?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="info scroll"></div>
    </div>
</div>

<?=template_admin_footer('initUsersOnline()')?>