<?php
include 'functions.php';
// Connect to MySQL using the below function
$pdo = pdo_connect_mysql();
// Fetch all the category names from the categories MySQL table
$categories = $pdo->query('SELECT * FROM categories')->fetchAll(PDO::FETCH_ASSOC);
// MySQL query that selects all the tickets from the databse
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$priority = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';
// The current pagination page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
// The maximum amount of tickets per page
$num_tickets_per_page = num_tickets_per_page;
// Build the SQL string
$sql = 'WHERE approved = 1';
$sql .= $status != 'all' ? ' AND ticket_status = :status ' : '';
$sql .= $category != 'all' ? ' AND category_id = :category ' : '';
$sql .= $priority != 'all' ? ' AND priority = :priority ' : '';
$sql .= $search ? ' AND title LIKE :search ' : '';
$sql .= isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin' ? '' : ' AND private = 0 ';
// Fetch the tickets from the database
$stmt = $pdo->prepare('SELECT * FROM tickets ' . $sql . ' ORDER BY created DESC LIMIT :current_page, :tickets_per_page');
// Bind params
if ($status != 'all') {
	$stmt->bindParam(':status', $status);
}
if ($category != 'all') {
	$stmt->bindParam(':category', $category);
}
if ($priority != 'all') {
	$stmt->bindParam(':priority', $priority);
}
if ($search) {
	$s = '%' . $search . '%';
	$stmt->bindParam(':search', $s);
}
$stmt->bindValue(':current_page', ($page-1)*(int)$num_tickets_per_page, PDO::PARAM_INT);
$stmt->bindValue(':tickets_per_page', (int)$num_tickets_per_page, PDO::PARAM_INT);
$stmt->execute();
// Fetch all tickets
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get total number of tickets
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets ' . $sql);
// Bind params
if ($status != 'all') {
	$stmt->bindParam(':status', $status);
}
if ($category != 'all') {
	$stmt->bindParam(':category', $category);
}
if ($priority != 'all') {
	$stmt->bindParam(':priority', $priority);
}
if ($search) {
	$s = '%' . $search . '%';
	$stmt->bindParam(':search', $s);
}
$stmt->execute();
// Get total
$total_tickets = $stmt->fetchColumn();
// Below queries will get the total number of tickets
if (isset($_SESSION['account_loggedin']) && $_SESSION['account_role'] == 'Admin') {
	// Only admins can view public and private tickets
	$num_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE approved = 1')->fetchColumn();
	$num_open_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "open" AND approved = 1')->fetchColumn();
	$num_closed_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "closed" AND approved = 1')->fetchColumn();
	$num_resolved_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "resolved" AND approved = 1')->fetchColumn();
} else {
	$num_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE private = 0 AND approved = 1')->fetchColumn();
	$num_open_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "open" AND private = 0 AND approved = 1')->fetchColumn();
	$num_closed_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "closed" AND private = 0 AND approved = 1')->fetchColumn();
	$num_resolved_tickets = $pdo->query('SELECT COUNT(*) FROM tickets WHERE ticket_status = "resolved" AND private = 0 AND approved = 1')->fetchColumn();
}
?>
<?=template_header('Tickets')?>

<div class="content tickets">

	<h2><?=ucfirst($status)?> Tickets</h2>

	<form action="" method="get">
		<div>
			<label for="status">Status</label>
			<select name="status" id="status" onchange="this.parentElement.parentElement.submit()">
				<option value="all"<?=$status=='all'?' selected':''?>>All (<?=number_format($num_tickets)?>)</option>
				<option value="open"<?=$status=='open'?' selected':''?>>Open (<?=number_format($num_open_tickets)?>)</option>
				<option value="resolved"<?=$status=='resolved'?' selected':''?>>Resolved (<?=number_format($num_resolved_tickets)?>)</option>
				<option value="closed"<?=$status=='closed'?' selected':''?>>Closed (<?=number_format($num_closed_tickets)?>)</option>
			</select>
			<label for="category">Category</label>
			<select name="category" id="category" onchange="this.parentElement.parentElement.submit()">
				<option value="all"<?=$category=='all'?' selected':''?>>All</option>
				<?php foreach($categories as $c): ?>
	            <option value="<?=$c['id']?>"<?=$c['id']==$category?' selected':''?>><?=$c['title']?></option>
	            <?php endforeach; ?>
			</select>
			<label for="priority">Priority</label>
			<select name="priority" id="priority" onchange="this.parentElement.parentElement.submit()">
				<option value="all"<?=$priority=='all'?' selected':''?>>All</option>
				<option value="low"<?=$priority=='low'?' selected':''?>>Low</option>
				<option value="medium"<?=$priority=='medium'?' selected':''?>>Medium</option>
				<option value="high"<?=$priority=='high'?' selected':''?>>High</option>
			</select>
		</div>
		<div class="search">
			<input name="search" type="text" placeholder="Search..." value="<?=htmlspecialchars(trim($search, '%'), ENT_QUOTES)?>" onkeypress="if(event.keyCode == 13) this.parentElement.submit()">
			<button type="submit"><i class="fas fa-search"></i></button>
		</div>
	</form>

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
		<?php if (!$tickets): ?>
		<p>There are no tickets.</p>
		<?php endif; ?>
	</div>

	<div class="pagination">
		<?php if ($page > 1): ?>
		<a href="tickets.php?status=<?=$status?>&category=<?=$category?>&priority=<?=$priority?>&search=<?=$search?>&page=<?=$page-1?>" class="prev">Prev</a>
		<?php endif; ?>
		<span>Page <?=$page?> of <?=ceil($total_tickets / $num_tickets_per_page) == 0 ? 1 : ceil($total_tickets / $num_tickets_per_page)?></span>
		<?php if ($page * $num_tickets_per_page < $total_tickets): ?>
		<a href="tickets.php?status=<?=$status?>&category=<?=$category?>&priority=<?=$priority?>&search=<?=$search?>&page=<?=$page+1?>" class="next">Next</a>
		<?php endif; ?>
	</div>

</div>

<?=template_footer()?>