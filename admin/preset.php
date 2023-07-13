<?php
include 'main.php';
// Ensure account is admin
if ($_SESSION['account_role'] != 'Admin') {
    exit('Invalid request!');
} 
// Default preset product values
$preset = [
    'msg' => ''
];
if (isset($_GET['id'])) {
    // Retrieve the preset from the database
    $stmt = $pdo->prepare('SELECT * FROM presets WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $preset = $stmt->fetch(PDO::FETCH_ASSOC);
    // ID param exists, edit an existing preset
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Update the preset
        $stmt = $pdo->prepare('UPDATE presets SET msg = ? WHERE id = ?');
        $stmt->execute([ $_POST['msg'], $_GET['id'] ]);
        header('Location: presets.php?success_msg=2');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Delete the preset
        $stmt = $pdo->prepare('DELETE FROM presets WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: presets.php?success_msg=3');
        exit;
    }
} else {
    // Create a new preset
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO presets (msg) VALUES (?)');
        $stmt->execute([ $_POST['msg'] ]);
        header('Location: presets.php?success_msg=1');
        exit;
    }
}
// Preset template below
?>
<?=template_admin_header($page . ' Preset', 'settings', 'presets')?>

<form action="" method="post">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100"><?=$page?> Preset</h2>
        <a href="presets.php" class="btn alt mar-right-2">Cancel</a>
        <?php if ($page == 'Edit'): ?>
        <input type="submit" name="delete" value="Delete" class="btn red mar-right-2" onclick="return confirm('Are you sure you want to delete this preset?')">
        <?php endif; ?>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>

    <div class="content-block">

        <div class="form responsive-width-100">

            <label for="msg"><i class="required">*</i> Message</label>
            <textarea id="msg" type="text" name="msg" placeholder="Message..." required><?=htmlspecialchars($preset['msg'], ENT_QUOTES)?></textarea>

        </div>

    </div>

</form>

<?=template_admin_footer()?>