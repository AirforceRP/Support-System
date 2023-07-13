<?php
include 'functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Fetch the 3 newest tickets
if (isset($_SESSION['admin_loggedin'])) {
	$stmt = $pdo->prepare('SELECT * FROM tickets WHERE approved = 1 ORDER BY created DESC LIMIT 3');
} else {
	$stmt = $pdo->prepare('SELECT * FROM tickets WHERE approved = 1 AND private = 0 ORDER BY created DESC LIMIT 3');
}
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get the total number of tickets for each status
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin') {
	$num_open_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "open" AND approved = 1')->fetchColumn();
	$num_closed_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "closed" AND approved = 1')->fetchColumn();
	$num_resolved_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "resolved" AND approved = 1')->fetchColumn();
} else {
	$num_open_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "open" AND private = 0 AND approved = 1')->fetchColumn();
	$num_closed_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "closed" AND private = 0 AND approved = 1')->fetchColumn();
	$num_resolved_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "resolved" AND private = 0 AND approved = 1')->fetchColumn();
}
?>
<?=template_header('Home')?>
<div class="content home">

	<h2>Home</h2>

	<div class="btns">
		<a href="create.php" class="btn">Create Ticket</a>
	</div>

	<div class="tickets-links responsive-width-100">
		<a href="tickets.php?status=open" class="open responsive-width-100">
			<i class="far fa-clock fa-10x"></i>
			<span class="num"><?=number_format($num_open_tickets)?></span>
			<span class="title">Open Ticket<?=$num_open_tickets==1?'':'s'?></span>
		</a>
		<a href="tickets.php?status=resolved" class="resolved responsive-width-100">
			<i class="fas fa-check fa-10x"></i>
			<span class="num"><?=number_format($num_resolved_tickets)?></span>
			<span class="title">Resolved Ticket<?=$num_resolved_tickets==1?'':'s'?></span>
		</a>
		<a href="tickets.php?status=closed" class="closed responsive-width-100">
			<i class="fas fa-times fa-10x"></i>
			<span class="num"><?=number_format($num_closed_tickets)?></span>
			<span class="title">Closed Ticket<?=$num_closed_tickets==1?'':'s'?></span>
		</a>
	</div>

	<h2 class="new">New Tickets</h2>

	<div class="tickets-list">
		<?php foreach ($tickets as $ticket): ?>
		<a href="view.php?id=<?=$ticket['id']?><?=isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin' && $ticket['private'] ? '&code=' . md5($ticket['id'] . $ticket['email']) : ''?>" class="ticket">
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
		<?php if (empty($tickets)): ?>
		<p>No tickets have been created.</p>
		<?php endif; ?>
	</div>

</div>
<?=template_footer()?>