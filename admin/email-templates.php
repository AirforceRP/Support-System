<?php
include 'main.php';
// Save the email templates
if (isset($_POST['ticket_email_template'])) {
    file_put_contents('../ticket-email-template.php', $_POST['ticket_email_template']);
    header('Location: email-templates.php?success_msg=1');
    exit;
}
// Read the ticket email template HTML file
if (file_exists('../ticket-email-template.php')) {
    $ticket_email_template = file_get_contents('../ticket-email-template.php');
}
// Handle success messages
if (isset($_GET['success_msg'])) {
    if ($_GET['success_msg'] == 1) {
        $success_msg = 'Email template updated successfully!';
    }
}
?>
<?=template_admin_header('Email Templates', 'emailtemplates')?>

<form action="" method="post" enctype="multipart/form-data">

    <div class="content-title responsive-flex-wrap responsive-pad-bot-3">
        <h2 class="responsive-width-100">Email Templates</h2>
        <input type="submit" name="submit" value="Save" class="btn">
    </div>

    <?php if (isset($success_msg)): ?>
    <div class="msg success">
        <i class="fas fa-check-circle"></i>
        <p><?=$success_msg?></p>
        <i class="fas fa-times"></i>
    </div>
    <?php endif; ?>

    <div class="content-block">

        <div class="form responsive-width-100">

            <?php if (isset($ticket_email_template)): ?>
            <label for="ticket_email_template">Ticket Email Template</label>
            <textarea id="ticket_email_template" name="ticket_email_template"><?=$ticket_email_template?></textarea>
            <?php endif; ?>

        </div>

    </div>

</form>

<?=template_admin_footer()?>