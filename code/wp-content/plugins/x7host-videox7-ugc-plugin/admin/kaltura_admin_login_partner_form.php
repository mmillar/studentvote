<?php
if (! defined("WP_ADMIN"))
    die();

if (@$_POST['is_postback'] == "postback") {
    $email = @$_POST['email'];
    $password = @$_POST['password'];
    $partnerId = @$_POST['partner_id'];
    
    $config = KalturaHelpers::getKalturaConfiguration();
    $config->partnerId = $partnerId;
    $kalturaClient = new KalturaClient($config);
    $kmodel = KalturaModel::getInstance();
    $partner = $kmodel->getSecrets($partnerId, $email, $password);
    
    // check for errors
    if ($kmodel->getLastError()) {
        $error = $kmodel->getLastError();
        $viewData["error"] = $error["message"];
    }
    else {
        $partnerId = $partner->id;
        $secret = $partner->secret;
        $adminSecret = $partner->adminSecret;
        $cmsUser = $partner->adminEmail;
        
        // save partner details
        update_option("kaltura_partner_id", $partnerId);
        update_option("kaltura_secret", $secret);
        update_option("kaltura_admin_secret", $adminSecret);
        update_option("kaltura_cms_user", $cmsUser);
        update_option("kaltura_cms_password", $password);
        update_option("kaltura_permissions_add", 0);
        update_option("kaltura_permissions_edit", 0);
        update_option("kaltura_enable_video_comments", true);
        update_option("kaltura_allow_anonymous_comments", true);
		update_option("x7uiconfid", "1727910");
		update_option("x7pluiconfid", "1727911");
		update_option("x7adminuiconfid", "1727910");
		update_option("x7kcwuiconfid", "1727883");
		update_option("x7allowposts", true);
		update_option("x7allowstandard", true);
		update_option("x7allowadvanced", true);
        
        $viewData["success"] = true;
    }
}
?>

<?php if ($viewData["error"]): ?>
	<div class="wrap">
		<h2><?php _e('x7Host Videox7 UGC Plugin Installation'); ?></h2>
		<br />
		<div class="error">
			<p>
				<strong><?php echo $viewData["error"]; ?></strong>
			</p>
		</div>
		<br />
		<div class="wrap">
			<a href="#" onclick="history.go(-1);"><?php _e('Back'); ?></a>
		</div>
	</div>
<?php elseif (@$viewData["success"] === true): ?>
	<div class="wrap">
		<h2><?php _e('Congratulations!'); ?></h2>
		<br />
		<div class="updated fade">
			<p>
				<strong>You have successfully installed the x7Host Videox7 UGC Plugin. </strong>
			</p>
		</div>
		<p>
			Next time you write a post, you will see a new icon in the Add Media toolbar that allows you to upload and edit Interactive Videos. <br />
			<br />
			If you created a new publisher account with Kaltura.com SaaS, check your email for details about your new account.<br />
		</p>
		<br />
		<div class="wrap">
			<a href="#" onclick="window.location.href = 'options-general.php?page=interactive_video'"><?php _e('Continue...'); ?></a>
		</div>
	</div>
<?php else: ?>
	<div class="wrap">
	<h2><?php _e('x7Host Videox7 UGC Plugin Installation'); ?></h2>
    <p>
	    Please enter your Kaltura Management Console (KMC) Email & password
    </p>
	<p>IMPORTANT!  If you are using your own, self-hosted KalturaCE Server, you MUST edit the "KALTURA_SERVER_URL" and "KALTURA_CDN_URL" variables in this plugin's "settings.php" file first!  Set both variables to be the address of your server, for example: "http://kaltura.myserver.com"
	</p>
	<form name="form1" method="post" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Partner ID"); ?>:</th>
				<td><input type="text" id="partner_id" name="partner_id" value="" size="10" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Email"); ?>:</th>
				<td><input type="text" id="email" name="email" value="" size="40" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Password"); ?>:</th>
				<td><input type="password" id="password" name="password" value="" size="20" /> <a href="<?php echo KalturaHelpers::getServerUrl(); ?>/index.php/kmc">forgot password?</a></td>
			</tr>
		</table>
		
		<p class="submit" style="text-align: left; "><input type="submit" name="Submit" value="<?php _e('Complete installation') ?>" /></p>
					
		<input type="hidden" name="is_postback" value="postback" />
	</form>
	</div>
<?php endif; ?>
