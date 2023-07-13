<?php
include 'main.php';
// Delete ticket
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE t, tc, tu FROM tickets t LEFT JOIN tickets_comments tc ON tc.ticket_id = t.id LEFT JOIN tickets_uploads tu ON tu.ticket_id = t.id WHERE t.id = ?');
    $stmt->execute([ $_GET['delete'] ]);
    header('Location: tickets.php?success_msg=3');
    exit;
}
// Approve ticket
if (isset($_GET['approve'])) {
    $stmt = $pdo->prepare('UPDATE tickets SET approved = 1 WHERE id = ?');
    $stmt->execute([ $_GET['approve'] ]);
    header('Location: tickets.php?success_msg=2');
    exit;
}
// Retrieve the GET request parameters (if specified)
$pagination_page = isset($_GET['pagination_page']) ? $_GET['pagination_page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
// Order by column
$order = isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
// Add/remove columns to the whitelist array
$order_by_whitelist = ['id','title','msg','full_name','email','created','ticket_status','priority','category_id','approved','private','account_id'];
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], $order_by_whitelist) ? $_GET['order_by'] : 'id';
// Number of results per pagination page
$results_per_page = 15;
// Declare query param variables
$param1 = ($pagination_page - 1) * $results_per_page;
$param2 = $results_per_page;
$param3 = '%' . $search . '%';
// SQL where clause
$where = '';
$where .= $search ? 'WHERE (t.full_name LIKE :search OR t.email LIKE :search OR t.id LIKE :search) ' : '';
if (isset($_GET['acc_id'])) {
    $where .= $where ? ' AND t.account_id = :acc_id ' : ' WHERE t.account_id = :acc_id ';
} 
if ($status) {
    $where .= $where ? ' AND t.ticket_status = :status ' : ' WHERE t.ticket_status = :status ';
}
// Retrieve the total number of tickets from the database
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM tickets t ' . $where);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if (isset($_GET['acc_id'])) $stmt->bindParam('acc_id', $_GET['acc_id'], PDO::PARAM_INT);
if ($status) $stmt->bindParam('status', $status, PDO::PARAM_STR);
$stmt->execute();
$tickets_total = $stmt->fetchColumn();
// SQL query to get all tickets from the "tickets" table
$stmt = $pdo->prepare('SELECT t.*, (SELECT COUNT(tc.id) FROM tickets_comments tc WHERE tc.ticket_id = t.id) AS num_comments, (SELECT GROUP_CONCAT(tu.filepath) FROM tickets_uploads tu WHERE tu.ticket_id = t.id) AS imgs, c.title AS category, a.full_name AS p_full_name, a.email AS a_email FROM tickets t LEFT JOIN categories c ON c.id = t.category_id LEFT JOIN accounts a ON t.account_id = a.id ' . $where . ' ORDER BY ' . $order_by . ' ' . $order . ' LIMIT :start_results,:num_results');
// Bind params
$stmt->bindParam('start_results', $param1, PDO::PARAM_INT);
$stmt->bindParam('num_results', $param2, PDO::PARAM_INT);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if (isset($_GET['acc_id'])) $stmt->bindParam('acc_id', $_GET['acc_id'], PDO::PARAM_INT);
if ($status) $stmt->bindParam('status', $status, PDO::PARAM_STR);
$stmt->execute();
// Retrieve query results
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Handle success messages
if (isset($_GET['success_msg'])) {
    if ($_GET['success_msg'] == 1) {
        $success_msg = 'Ticket created successfully!';
    }
    if ($_GET['success_msg'] == 2) {
        $success_msg = 'Ticket updated successfully!';
    }
    if ($_GET['success_msg'] == 3) {
        $success_msg = 'Ticket deleted successfully!';
    }
    if ($_GET['success_msg'] == 4) {
        $success_msg = $_GET['imported'] . ' ticket(s) imported successfully!';
    }
}
// Determine the URL
$url = 'tickets.php?search=' . $search . (isset($_GET['page_id']) ? '&page_id=' . $_GET['page_id'] : '') . (isset($_GET['acc_id']) ? '&acc_id=' . $_GET['acc_id'] : '') . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '');
?>
<?=template_admin_header('Tickets', 'tickets', 'view')?>

<div class="content-title">
    <div class="title">
        <i class="fa-solid fa-ticket"></i>
        <div class="txt">
            <h2>Tickets</h2>
            <p>View, manage, and search tickets.</p>
        </div>
    </div>
</div>

<?php if (isset($success_msg)): ?>
<div class="msg success">
    <i class="fas fa-check-circle"></i>
    <p><?=$success_msg?></p>
    <i class="fas fa-times"></i>
</div>
<?php endif; ?>

<div class="content-header responsive-flex-column pad-top-5">
    <div class="btns">
        <a href="ticket.php" class="btn">Create Ticket</a>
        <a href="tickets_import.php" class="btn mar-left-1">Import</a>
        <a href="tickets_export.php" class="btn mar-left-1">Export</a>
    </div>
    <form action="" method="get">
        <div class="filters">
            <a href="#"><i class="fas fa-filter"></i> Filters</a>
            <div class="list">
                <label><input type="radio" name="status" value="open"<?=$status=='open'?' checked':''?>>Open</label>
                <label><input type="radio" name="status" value="closed"<?=$status=='closed'?' checked':''?>>Closed</label>
                <label><input type="radio" name="status" value="resolved"<?=$status=='resolved'?' checked':''?>>Resolved</label>
                <button type="submit">Apply</button>
            </div>
        </div>
        <div class="search">
            <label for="search">
                <input id="search" type="text" name="search" placeholder="Search ticket..." value="<?=htmlspecialchars($search, ENT_QUOTES)?>" class="responsive-width-100">
                <i class="fas fa-search"></i>
            </label>
        </div>
    </form>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=id'?>">#<?php if ($order_by=='id'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td colspan="2"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=full_name'?>">User<?php if ($order_by=='full_name'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=title'?>">Title<?php if ($order_by=='title'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=ticket_status'?>">Status<?php if ($order_by=='ticket_status'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden">Has Comments</td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=priority'?>">Priority<?php if ($order_by=='priority'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden">Category</td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=private'?>">Private<?php if ($order_by=='private'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=approved'?>">Approved<?php if ($order_by=='approved'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=created'?>">Date<?php if ($order_by=='created'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="20" style="text-align:center;">There are no tickets.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?=$ticket['id']?></td>
                    <td class="img">
                        <span style="background-color:<?=color_from_string($ticket['p_full_name'] ?? $ticket['full_name'])?>"><?=strtoupper(substr($ticket['p_full_name'] ?? $ticket['full_name'], 0, 1))?></span>
                    </td>
                    <td class="user">
                        <?=htmlspecialchars($ticket['p_full_name'] ?? $ticket['full_name'], ENT_QUOTES)?>
                        <span><?=$ticket['p_email'] ?? $ticket['email']?></span>
                    </td>
                    <td><?=htmlspecialchars($ticket['title'], ENT_QUOTES)?></td>
                    <td><span class="<?=$ticket['ticket_status']=='resolved'?'green':($ticket['ticket_status']=='closed'?'red':'grey')?>"><?=ucwords($ticket['ticket_status'])?></span></td>
                    <td class="responsive-hidden"><?=$ticket['num_comments'] ? '<span class="mark yes"><i class="fa-solid fa-check"></i></span>' : '<span class="mark no"><i class="fa-solid fa-xmark"></i></span>'?></td>
                    <td class="responsive-hidden"><span class="<?=$ticket['priority']=='low'?'green':($ticket['priority']=='high'?'red':'orange')?>"><?=ucwords($ticket['priority'])?></span></td>
                    <td class="responsive-hidden"><span class="grey"><?=$ticket['category']?></span></td>
                    <td class="responsive-hidden"><?=$ticket['private'] ? '<span class="mark yes"><i class="fa-solid fa-check"></i></span>' : '<span class="mark no"><i class="fa-solid fa-xmark"></i></span>'?></td>
                    <td><?=$ticket['approved'] ? '<span class="mark yes"><i class="fa-solid fa-check"></i></span>' : '<span class="mark no"><i class="fa-solid fa-xmark"></i></span>'?></td>
                    <td class="responsive-hidden"><?=date('F j, Y H:ia', strtotime($ticket['created']))?></td>
                    <td>
                        <a href="../view.php?id=<?=$ticket['id']?>&code=<?=md5($ticket['id'] . $ticket['email'])?>" target="_blank" class="link1">View</a>
                        <a href="ticket.php?id=<?=$ticket['id']?>" class="link1">Edit</a>
                        <a href="tickets.php?delete=<?=$ticket['id']?>" class="link1" onclick="return confirm('Are you sure you want to delete this ticket?')">Delete</a>
                        <?php if ($ticket['approved'] != 1): ?>
                        <a href="tickets.php?approve=<?=$ticket['id']?>" class="link1" onclick="return confirm('Are you sure you want to approve this ticket?')">Approve</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="pagination">
    <?php if ($pagination_page > 1): ?>
    <a href="<?=$url?>&pagination_page=<?=$pagination_page-1?>&order=<?=$order?>&order_by=<?=$order_by?>">Prev</a>
    <?php endif; ?>
    <span>Page <?=$pagination_page?> of <?=ceil($tickets_total / $results_per_page) == 0 ? 1 : ceil($tickets_total / $results_per_page)?></span>
    <?php if ($pagination_page * $results_per_page < $tickets_total): ?>
    <a href="<?=$url?>&pagination_page=<?=$pagination_page+1?>&order=<?=$order?>&order_by=<?=$order_by?>">Next</a>
    <?php endif; ?>
</div>

<?=template_admin_footer()?>