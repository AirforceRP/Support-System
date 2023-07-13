<?php
include 'main.php';
// Delete comment
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM tickets_comments WHERE id = ?');
    $stmt->execute([ $_GET['delete'] ]);
    header('Location: comments.php?success_msg=3');
    exit;
}
// Retrieve the GET request parameters (if specified)
$pagination_page = isset($_GET['pagination_page']) ? $_GET['pagination_page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Order by column
$order = isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
// Add/remove columns to the whitelist array
$order_by_whitelist = ['ticket_id','ticket_title','msg','full_name','created'];
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], $order_by_whitelist) ? $_GET['order_by'] : 'created';
// Number of results per pagination page
$results_per_page = 15;
// Declare query param variables
$param1 = ($pagination_page - 1) * $results_per_page;
$param2 = $results_per_page;
$param3 = '%' . $search . '%';
// SQL where clause
$where = '';
$where .= $search ? 'WHERE (t.title LIKE :search OR t.full_name LIKE :search OR t.email LIKE :search OR t.id LIKE :search OR a.full_name LIKE :search) ' : '';
if (isset($_GET['acc_id'])) {
    $where .= $where ? ' AND t.account_id = :acc_id ' : ' WHERE t.account_id = :acc_id ';
} 
// Retrieve the total number of comments from the database
$stmt = $pdo->prepare('SELECT COUNT(*) AS total, tc.* FROM tickets_comments tc JOIN tickets t ON t.id = tc.ticket_id LEFT JOIN accounts a ON a.id = tc.account_id ' . $where);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if (isset($_GET['acc_id'])) $stmt->bindParam('acc_id', $_GET['acc_id'], PDO::PARAM_INT);
$stmt->execute();
$comments_total = $stmt->fetchColumn();
// SQL query to get all comments from the "comments" table
$stmt = $pdo->prepare('SELECT tc.*, t.title AS ticket_title, a.full_name AS full_name, t.email AS ticket_email FROM tickets_comments tc JOIN tickets t ON t.id = tc.ticket_id LEFT JOIN accounts a ON a.id = tc.account_id ' . $where . ' GROUP BY tc.id ORDER BY ' . $order_by . ' ' . $order . ' LIMIT :start_results,:num_results');
// Bind params
$stmt->bindParam('start_results', $param1, PDO::PARAM_INT);
$stmt->bindParam('num_results', $param2, PDO::PARAM_INT);
if ($search) $stmt->bindParam('search', $param3, PDO::PARAM_STR);
if (isset($_GET['acc_id'])) $stmt->bindParam('acc_id', $_GET['acc_id'], PDO::PARAM_INT);
$stmt->execute();
// Retrieve query results
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Handle success messages
if (isset($_GET['success_msg'])) {
    if ($_GET['success_msg'] == 1) {
        $success_msg = 'Comment created successfully!';
    }
    if ($_GET['success_msg'] == 2) {
        $success_msg = 'Comment updated successfully!';
    }
    if ($_GET['success_msg'] == 3) {
        $success_msg = 'Comment deleted successfully!';
    }
}
// Determine the URL
$url = 'comments.php?search=' . $search;
?>
<?=template_admin_header('Comments', 'comments')?>

<div class="content-title">
    <div class="title">
        <i class="fa-solid fa-comments"></i>
        <div class="txt">
            <h2>Comments</h2>
            <p>View, manage, and search comments.</p>
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
        <a href="comment.php" class="btn">Create Comment</a>
    </div>
    <form action="" method="get">
        <div class="search">
            <label for="search">
                <input id="search" type="text" name="search" placeholder="Search comment..." value="<?=htmlspecialchars($search, ENT_QUOTES)?>" class="responsive-width-100">
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
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=ticket_id'?>">Ticket ID<?php if ($order_by=='ticket_id'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=ticket_title'?>">Ticket Title<?php if ($order_by=='ticket_title'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=full_name'?>">User<?php if ($order_by=='full_name'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=msg'?>">Msg<?php if ($order_by=='msg'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td class="responsive-hidden"><a href="<?=$url . '&order=' . ($order=='ASC'?'DESC':'ASC') . '&order_by=created'?>">Date<?php if ($order_by=='created'): ?><i class="fas fa-level-<?=str_replace(['ASC', 'DESC'], ['up','down'], $order)?>-alt fa-xs"></i><?php endif; ?></a></td>
                    <td>Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comments)): ?>
                <tr>
                    <td colspan="20" style="text-align:center;">There are no comments.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td class="responsive-hidden"><?=$comment['ticket_id']?></td>
                    <td><?=$comment['ticket_title']?></td>
                    <td><?=htmlspecialchars($comment['full_name'], ENT_QUOTES)?></td>
                    <td style="max-width:200px"><?=nl2br(htmlspecialchars($comment['msg'], ENT_QUOTES))?></td>
                    <td class="responsive-hidden"><?=date('F j, Y H:ia', strtotime($comment['created']))?></td>
                    <td>
                        <a href="../view.php?id=<?=$comment['ticket_id']?>&code=<?=md5($comment['ticket_id'] . $comment['ticket_email'])?>" target="_blank" class="link1">View</a>
                        <a href="comment.php?id=<?=$comment['id']?>" class="link1">Edit</a>
                        <a href="comments.php?delete=<?=$comment['id']?>" class="link1" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
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
    <span>Page <?=$pagination_page?> of <?=ceil($comments_total / $results_per_page) == 0 ? 1 : ceil($comments_total / $results_per_page)?></span>
    <?php if ($pagination_page * $results_per_page < $comments_total): ?>
    <a href="<?=$url?>&pagination_page=<?=$pagination_page+1?>&order=<?=$order?>&order_by=<?=$order_by?>">Next</a>
    <?php endif; ?>
</div>

<?=template_admin_footer()?>