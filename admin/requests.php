<?php
include 'main.php';
// Retrieve the total number of accounts
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM accounts WHERE status = "Waiting"');
$stmt->execute();
$accounts_total = $stmt->fetchColumn();
// SQL query to get all accounts that are waiting
$stmt = $pdo->prepare('SELECT * FROM accounts WHERE status = "Waiting" ORDER BY last_seen');
// Bind params
$stmt->execute();
// Retrieve query results
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Requests template below
?>
<?=template_admin_header(number_format($accounts_total) . ' Requests', 'requests', 'view')?>

<div class="content-title">
    <h2><?=number_format($accounts_total)?> Requests</h2>
</div>

<div class="content-block cover">
    <div class="requests">
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

<?=template_admin_footer('initRequests()')?>