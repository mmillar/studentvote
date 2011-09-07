<?php 
require_once('../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');
auth_redirect();
nocache_headers();
require_once('settings.php');
require_once('lib/kaltura_model.php');
require_once('lib/kaltura_helpers.php');
  
kaltura_register_js();

$body_id = "previewPage";
 
function update_thumbnail_frame()
{
	$entryId = @$_GET['entryId'];
	
	if (!$entryId)
	{
		wp_die(__('The video is missing or invalid.'));
	}
	
	global $KALTURA_DEFAULT_PLAYERS;
	$swfUrl	= KalturaHelpers::getSwfUrlForWidget(null, $KALTURA_DEFAULT_PLAYERS[0]["id"]);
	$flashVars = KalturaHelpers::getKalturaPlayerFlashVars(null, null, $entryId);
	$flashVars["autoPlay"] = "true";
	$flashVarsStr = KalturaHelpers::flashVarsToString($flashVars);
	$kalturaserver = KALTURA_SERVER_URL;
	$uiconf = get_option('x7uiconfid');
	$pid = get_option("kaltura_partner_id");
	?>
<div class="playerWrapper">
	<div id="kplayer">
		<script type="text/javascript">

			if (window.parent)
				window.parent.jQuery('#TB_title').css({'background-color':'#222','color':'#cfcfcf'}); // copied from media-upload.js
			
			//var kalturaSwf = new SWFObject("<?php echo $swfUrl; ?>", "swfKalturaPlayer", "350", "320", "9", "#000000");
			//kalturaSwf.addParam("flashVars", "<?php echo $flashVarsStr; ?>");
			//kalturaSwf.addParam("wmode", "opaque");
			//kalturaSwf.addParam("allowScriptAccess", "always");
			//kalturaSwf.addParam("allowFullScreen", "true");
			//kalturaSwf.addParam("allowNetworking", "all");
			//kalturaSwf.write("kplayer");
		</script>
		<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="333" width="400" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="<?php echo($kalturaserver); ?>/kwidget/cache_st/1294461779/wid/_<?php echo($pid); ?>/uiconf_id/<?php echo($uiconf); ?>/entry_id/<?php echo($entryId); ?>" data="<?php echo($kalturaserver); ?>/kwidget/cache_st/1294461779/wid/_<?php echo($pid); ?>/uiconf_id/<?php echo($uiconf); ?>/entry_id/<?php echo($entryId); ?>">
		<param name="allowFullScreen" value="true" />
		<param name="allowNetworking" value="all" />
		<param name="allowScriptAccess" value="always" />
		<param name="bgcolor" value="#000000" />
		<param name="flashVars" value="&" />
		<param name="movie" value="<?php echo($kalturaserver); ?>/kwidget/cache_st/1294461779/wid/_<?php echo($pid); ?>/uiconf_id/<?php echo($uiconf); ?>/entry_id/<?php echo($entryId); ?>" />
		<a rel="media:thumbnail" href="<?php echo($kalturaserver); ?>/p/<?php echo($pid); ?>/sp/<?php echo($kalturaserver); ?>00/thumbnail/entry_id/<?php echo($entryId); ?>/width/120/height/90/bgcolor/000000/type/2" />
		<span property="dc:description" content="media" /><span property="media:title" content="media" />
		<span property="media:width" content="400" />
		<span property="media:height" content="333" />
		<span property="media:type" content="application/x-shockwave-flash" /></object>
	</div>
	<?php 
}
 
wp_iframe("update_thumbnail_frame");

?>