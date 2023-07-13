<?php defined('mail_enabled') or exit; ?>
<!DOCTYPE html>
<html>
	<head>
        <meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
        <title><?=htmlspecialchars($subject, ENT_QUOTES)?></title>
	</head>
	<body style="background-color:#F5F6F8;font-size:16px;box-sizing:border-box;font-family:system-ui,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';">
		<div style="box-sizing:border-box;margin:50px auto;background-color:#fff;padding:40px;width:100%;max-width:600px;box-shadow:0 0 7px 0 rgba(0,0,0,.05);">
			<h1 style="box-sizing:border-box;font-size:18px;color:#474a50;padding:0 0 20px 0;margin:0;font-weight:600;border-bottom:1px solid #eee;">Ticket #<?=$id?></h1>
			<p style="margin:0;padding:25px 0;"><?=htmlspecialchars($subject, ENT_QUOTES)?>! Ticket details are below.</p>
            <div style="display:flex;flex-wrap:wrap;">
			<?php if ($type == 'comment'): ?>
            <div style="width:100%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Comment</div>
                <div style="padding:5px;font-size:16px;"><?=nl2br(htmlspecialchars($msg, ENT_QUOTES))?></div>
            </div>
			<?php else: ?>
            <?php if ($name): ?>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Name</div>
                <div style="padding:5px;font-size:16px;"><?=htmlspecialchars($name, ENT_QUOTES)?></div>
            </div>
            <?php endif; ?>
            <?php if ($user_email): ?>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Email</div>
                <div style="padding:5px;font-size:16px;"><?=htmlspecialchars($user_email, ENT_QUOTES)?></div>
            </div>
            <?php endif; ?>
            <div style="width:100%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Title</div>
                <div style="padding:5px;font-size:16px;"><?=htmlspecialchars($title, ENT_QUOTES)?></div>
            </div>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Category</div>
                <div style="padding:5px;font-size:16px;"><?=$category?></div>
            </div>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Priority</div>
                <div style="padding:5px;font-size:16px;"><?=$priority?></div>
            </div>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Private</div>
                <div style="padding:5px;font-size:16px;"><?=$private==1?'Yes':'No'?></div>
            </div>
            <div style="width:50%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Status</div>
                <div style="padding:5px;font-size:16px;"><?=$status?></div>
            </div>
            <div style="width:100%;padding-bottom:10px;">
                <div style="padding:5px;font-weight:600;font-size:14px;">Message</div>
                <div style="padding:5px;font-size:16px;"><?=nl2br(htmlspecialchars($msg), ENT_QUOTES)?></div>
            </div>
			<?php endif; ?>
            </div>
			<p>Click <a href="<?=$link?>" style="color:#c52424;text-decoration:none;">here</a> to view the ticket.</p>
		</div>
	</body>
</html>