<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');

$entryId = @$_GET["entryId"];
$user_login = @$_GET["user_login"];
$x7server = @$_GET["x7server"];
$x7server = urldecode($x7server);
$pluginurl = @$_GET["pluginurl"];
$pluginurl = urldecode($pluginurl);
$x7kalpartnerid = @$_GET["x7kalpartnerid"];
$x7bloghome = urldecode($_GET['x7bloghomeget']);

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
require_once('settings.php');
require_once('lib/kaltura_client.php');
require_once('lib/kaltura_helpers.php');
require_once('lib/kaltura_model.php');

$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getClientSideSession("edit:*",86400,$user_login);

$flashVars = array();
$flashVars["partnerId"] = $x7kalpartnerid;
$flashVars["subpId"] = $x7kalpartnerid * 100;
$flashVars["uid"] = $user_login;
$flashVars["ks"] 		= $ks;
$flashVars["kshowId"] 	= -1;
$flashVars["entryId"] 	= $entryId;
$flashVars["jsDelegate"] 	= "callbacksObj";
} else {
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>x7 Standard Editor</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo($pluginurl); ?>/js/shadowbox.js"></script>
	<script type="text/javascript">
		var callbacksObj = {
			publishHandler:publishHandler,
			closeHandler:closeHandler,
			openContributionWizardHandler:openKCWHandler,
			kalturaLogoClickHandler:kalHandler
		};		
		function publishHandler() {
			//if (confirm("Video successfully saved.  Click OK to close or CANCEL to keep editing.")){
				//parent.Shadowbox.close();
			//}
		}
		function closeHandler() {
			parent.Shadowbox.close();
		}
		function openKCWHandler() {
			window.alert("Remember - Editor uploads are not treated as separate video entries!");
		}
		function kalHandler() {
			window.alert("We make use of Kaltura open source video solutions, Kaltura Dot Org");
		}
	</script>
</head>
<body>
				<div id="kaeWrap">
					<div id="kae"></div>
				</div>
				<script type="text/javascript">
					var params = {
						allowscriptaccess: "always",
						allownetworking: "all",
						wmode: "opaque"
					};
					var flashVars = <?php echo json_encode($flashVars); ?>;
					swfobject.embedSWF("<?php echo($x7server); ?>/kse/ui_conf_id/1727887", "kae", "900", "700", "9.0.0", false, flashVars, params);
				</script>
</body>
</html>