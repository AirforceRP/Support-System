<?php
include 'main.php';
// Default category values
$category = [
    'title' => ''
];
if (isset($_GET['id'])) {
    // Retrieve the category from the database
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing category
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the category
        $stmt = $pdo->prepare('UPDATE categories SET title = ? WHERE id = ?');
        $stmt->execute([ $_POST['title'], $_GET['id'] ]);
        header('Location: categories.php?success_msg=2');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the category
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: categories.php?success_msg=3');
        exit;
    }
} else {
    // Create a new category
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO categories (title) VALUES (?)');
        $stmt->execute([ $_POST['title'] ]);
        header('Location: categories.php?success_msg=1');
        exit;
    }
}
?>
<?=template_admin_header($page . ' Category', 'categories', 'manage')?>

<form action="" method="post">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100"><?=$page?> Category</h2>
        <a href="categories.php" class="btn alt mar-right-2">Cancel</a>
        <?php if ($page == 'Edit'): ?>
        <input type="submit" name="delete" value="Delete" class="btn red mar-right-2" onclick="return confirm('Are you sure you want to delete this category?')">
        <?php endif; ?>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>

    <div class="content-block">

        <div class="form responsive-width-100">

            <label for="title"><i class="required">*</i> Title</label>
            <input id="title" type="text" name="title" placeholder="Title" value="<?=htmlspecialchars($category['title'], ENT_QUOTES)?>" required>

        </div>

    </div>

</form>

<?=template_admin_footer()?>