<?php
include 'functions.php';
// Redirect user if not logged in
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: login.php');
	exit;
}
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Retrieve the tickets from the database
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE account_id = ? AND approved = 1 ORDER BY created DESC');
$stmt->execute([ $_SESSION['account_id'] ]);
$account_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?=template_header('My Tickets')?>

<div class="content tickets">

	<h2>My Tickets</h2>

	<div class="tickets-list">
		<?php foreach ($account_tickets as $ticket): ?>
		<a href="view.php?id=<?=$ticket['id']?><?=$ticket['private'] ? '&code=' . md5($ticket['id'] . $ticket['email']) : ''?>" class="ticket">
			<span class="con">
				<?php if ($ticket['ticket_status'] == 'open'): ?>
				<i class="far fa-clock fa-2x"></i>
				<?php elseif ($ticket['ticket_status'] == 'resolved'): ?>
				<i class="fas fa-check fa-2x"></i>
				<?php elseif ($ticket['ticket_status'] == 'closed'): ?>
				<i class="fas fa-times fa-2x"></i>
				<?php endif; ?>
			</span>
			<span class="con">
				<span class="title"><?=htmlspecialchars($ticket['title'], ENT_QUOTES)?></span>
				<span class="msg responsive-hidden"><?=htmlspecialchars(strip_tags($ticket['msg']), ENT_QUOTES)?></span>
			</span>
			<span class="con2">
				<span class="created responsive-hidden"><?=date('F dS, G:ia', strtotime($ticket['created']))?></span>
				<span class="priority <?=$ticket['priority']?>"><?=$ticket['priority']?></span>
			</span>
		</a>
		<?php endforeach; ?>
		<?php if (!$account_tickets): ?>
		<p>You have no tickets.</p>
		<?php endif; ?>
	</div>

</div>

<?=template_footer()?>