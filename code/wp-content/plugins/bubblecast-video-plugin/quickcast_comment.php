
<div id="bubblecast_comment" class="leightbox">

<?php
    global $user_login, $user_email,$admin_email,$pluginMode ;
    $pluginMode = 'wpc';
    get_currentuserinfo();
    $admin_email = get_option('admin_email');
    include("iquickcast.php");
?>
	<p class="footer" align="center">
		<a href="#" onclick="hideBubblecastComment(); return false;"><?php _e('Close', 'bubblecast');?></a>
	</p>
</div>
