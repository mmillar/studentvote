<?php
require_once('settings.php');
require_once('lib/kaltura_client.php');
require_once('lib/kaltura_helpers.php');
require_once('lib/kaltura_model.php');

// comments filter
if (KalturaHelpers::compareWPVersion("2.5", "=")) 
	// in wp 2.5 there was a bug in wptexturize which corrupted our tag with unicode html entities
	// thats why we run our filter before (using lower priority)
	add_filter('comment_text', 'kaltura_the_comment', -1);
else
	// in wp 2.5.1 and higher we can use the default priority
	add_filter('comment_text', 'kaltura_the_comment');

// tag shortcode
add_shortcode('kaltura-widget', 'kaltura_shortcode');
add_shortcode('x7video', 'x7video_shortcode');

if (KalturaHelpers::videoCommentsEnabled()) {
	add_action('comment_form', 'kaltura_comment_form');
}

// js
add_action('init', 'kaltura_register_js'); // register js files

// css
add_action('wp_head', 'kaltura_head'); // print css

// footer
add_action('wp_footer', 'kaltura_footer');

// admin css
add_filter('admin_head', 'kaltura_add_admin_css'); // print admin css

if (KalturaHelpers::compareWPVersion("2.7", ">="))
	add_action('load-media_page_interactive_video_library', 'kaltura_library_page_load'); // to enqueue scripts and css
else
	add_action('load-manage_page_interactive_video_library', 'kaltura_library_page_load'); // to enqueue scripts and css

// admin menu & tabs
add_action('admin_menu', 'kaltura_add_admin_menu'); // add kaltura admin menu

add_filter("media_buttons_context", "kaltura_add_media_button"); // will add button over the rich text editor
add_filter("media_upload_tabs", "kaltura_add_upload_tab"); // will add tab to the modal media box

add_action("media_upload_kaltura_upload", "kaltura_upload_tab");
add_action("media_upload_kaltura_browse", "kaltura_browse_tab");

if (KalturaHelpers::compareWPVersion("2.6", "<")) {
	add_action("admin_head_kaltura_tab_content", "media_admin_css");
	add_action("admin_head_kaltura_tab_browse_content", "media_admin_css");
}

// tiny mce
add_filter('mce_external_plugins', 'kaltura_add_mce_plugin'); // add the kaltura mce plugin
add_filter('tiny_mce_version', 'kaltura_mce_version');

/*
 * Occures when publishing the post, and on every save while the post is published
 * 
 * @param $postId
 * @param $post
 * @return unknown_type
 */
function kaltura_publish_post($post_id, $post)
{
	require_once("lib/kaltura_wp_model.php");

	$content = $post->post_content;

	$shortcode_tags = array();
	
	global $kaltura_post_id, $kaltura_widgets_in_post;
	$kaltura_post_id = $post_id;
	$kaltura_widgets_in_post = array();
	KalturaHelpers::runKalturaShortcode($content, "_kaltura_find_post_widgets");

	// delete all widgets that doesn't exists in the post anymore
	KalturaWPModel::deleteUnusedWidgetsByPost($kaltura_post_id, $kaltura_widgets_in_post);
}

add_action("publish_post", "kaltura_publish_post", 10, 2);
add_action("publish_page", "kaltura_publish_post", 10, 2);


/*
 * Occures on evey status change, we need to mark our widgets as unpublished when status of the post is not publish
 * 
 * @param $oldStatus
 * @param $newStatus
 * @param $post
 * @return unknown_type
 */
function kaltura_post_status_change($new_status, $old_status, $post)
{
	// get all widgets linked to this post and mark them as not published
	$statuses = array("inherit", "publish");
	// we don't handle "inherit" status because it not the real post, but the revision
	// we don't handle "publish" status because it's handled in: "kaltura_publish_post"
	if (!in_array($new_status, $statuses))
	{
		require_once("lib/kaltura_wp_model.php");
		$widgets = KalturaWPModel::getWidgetsByPost($post->ID);
		KalturaWPModel::unpublishWidgets($widgets);
	}
}

add_action("transition_post_status", "kaltura_post_status_change", 10, 3); 


/*
 * Occures on post delete, and deleted all widgets for that post
 * 
 * @param $post_id
 */
function kaltura_delete_post($post_id)
{
	require_once("lib/kaltura_wp_model.php");
	KalturaWPModel::deleteUnusedWidgetsByPost($post_id, array());
}

add_action("deleted_post", "kaltura_delete_post", 10, 1); 


/*
 * Occures when comment status is changed
 * @param $comment_id
 * @param $status
 * @return unknown_type
 */
function kaltura_set_comment_status($comment_id, $status)
{
	require_once("lib/kaltura_wp_model.php");

	switch ($status)
	{
		case "approve":
			kaltura_comment_post($comment_id, 1);
			break;
		default:
			KalturaWPModel::deleteWidgetsByComment($comment_id);
	}
}

add_action("wp_set_comment_status", "kaltura_set_comment_status", 10, 2);


/*
 * Occured when posting a comment
 * @param $comment_id
 * @param $approved
 * @return unknown_type
 */
function kaltura_comment_post($comment_id, $approved)
{
	if ($approved) 
	{
		require_once("lib/kaltura_wp_model.php");

		global $kaltura_comment_id;
		$kaltura_comment_id = $comment_id;
		
		$comment = get_comment($comment_id);
		KalturaHelpers::runKalturaShortcode($comment->comment_content, "_kaltura_find_comment_widgets");
	}
}

add_action("comment_post", "kaltura_comment_post", 10, 2);

/*
 * Occures when the plugin is activated 
 * @return unknown_type
 */
function kaltura_activate()
{
	update_option("kaltura_default_player_type", "whiteblue");
	update_option("kaltura_comments_player_type", "whiteblue");
	update_option("x7uiconfid", "1727910");
	update_option("x7pluiconfid", "1727911");
	update_option("x7adminuiconfid", "1727910");
	update_option("x7kcwuiconfid", "1727883");
	update_option("x7allowposts", true);
	update_option("x7allowstandard", true);
	update_option("x7allowadvanced", true);
	update_option("x7html5enabled", true);
	require_once("kaltura_db.php");
	kaltura_install_db();
}

register_activation_hook(KALTURA_PLUGIN_FILE, 'kaltura_activate');


function kaltura_admin_page()
{
	require_once("lib/kaltura_model.php");
	require_once('admin/kaltura_admin_controller.php');
}

function kaltura_library_page()
{
	$_GET["kaction"] = isset($_GET["kaction"]) ? $_GET["kaction"] : "entries";
	require_once("lib/kaltura_library_controller.php");
}

function kaltura_video_library_video_posts_page()
{
	require_once("lib/kaltura_library_controller.php");
}

function kaltura_library_page_load()
{
	if (KalturaHelpers::compareWPVersion("2.6", ">="))
		add_thickbox();
	else
		wp_enqueue_script('thickbox');
}

function kaltura_add_mce_plugin($content) {
	$pluginUrl = KalturaHelpers::getPluginUrl();
	$content["kaltura"] = $pluginUrl . "/tinymce/kaltura_tinymce.js?v".kaltura_get_version();
	return $content;
}

function kaltura_mce_version($content) 
{
	return $content . '_k'.kaltura_get_version();
}
  
function kaltura_add_admin_menu() 
{
	add_options_page('x7 UGC Settings', 'x7 UGC Settings', 8, 'interactive_video', 'kaltura_admin_page');
	$args = array('x7 UGC Video', 'x7 UGC Video', 8, 'interactive_video_library', 'kaltura_library_page');
	// because of the change in wordpress 2.7 menu structure, we move the library page under "Media" tab
	if (KalturaHelpers::compareWPVersion("2.7", ">=")) 
		call_user_func_array("add_media_page", $args);
	else
		call_user_func_array("add_management_page", $args);
}

function kaltura_the_content($content) 
{
	return _kaltura_replace_tags($content, false);
}

function kaltura_the_comment($content) 
{
	global $shortcode_tags;
	
	// we want to run our shortcode and not all
	$shortcode_tags_backup = $shortcode_tags;
	$shortcode_tags = array();
	
	add_shortcode('kaltura-widget', 'kaltura_shortcode');
	$content = do_shortcode($content);
	
	// restore the original array
	$shortcode_tags = $shortcode_tags_backup;
	
	return $content;
}

function kaltura_register_js() 
{
	$plugin_url = KalturaHelpers::getPluginUrl();
	if ( is_admin() ) {
		wp_register_script('kadmin', $plugin_url . '/js/kadmin.js?v'.kaltura_get_version(), false, false, false);
		wp_enqueue_script( 'kadmin' );
		wp_register_script('swfobject-script15', $plugin_url . '/js/swfobject.js', false, false, false);
		wp_enqueue_script( 'swfobject-script15' );
		//wp_register_script('swfobject-script', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js', false, false, false);
		//wp_enqueue_script( 'swfobject-script' );
		
		wp_register_script('kaltura', $plugin_url . '/js/kaltura.js?v'.kaltura_get_version(), false, false, false);
		wp_enqueue_script( 'kaltura' );
	}
	if( !is_admin() ){
		wp_deregister_script('jquery'); 
		wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"), false, '1.4.4', false);
		wp_enqueue_script( 'jquery' );
		
		wp_register_script('kaltura', $plugin_url . '/js/kaltura.js?v'.kaltura_get_version(), false, false, false);
		wp_enqueue_script( 'kaltura' );
	//register swfobject for flash embedding
		wp_register_script('swfobject-script', 'http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js', false, false, false);
		wp_enqueue_script( 'swfobject-script' );
		
		//wp_register_script('swfobject-script15', $plugin_url . '/js/swfobject.js', false, false, true);
		//wp_enqueue_script( 'swfobject-script15' );
	//includes jquery tools ui
		wp_register_script('jquerytools', $plugin_url . '/js/jquery.tools.min.js', false, false, false);
		wp_enqueue_script( 'jquerytools' );
	//register newest jquery ui
		wp_register_script('jqueryui-script', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js', false, false, false);
		wp_enqueue_script( 'jqueryui-script' );
	//register shadowbox
		wp_register_script('shadowbox-script', $plugin_url . '/js/shadowbox.js', false, false, false);
		wp_enqueue_script( 'shadowbox-script' );
	//register custom x7js
		wp_register_script('x7ugc-script', $plugin_url . '/js/x7js.js', false, false, false);
		wp_enqueue_script( 'x7ugc-script' );
	//register form validator
		wp_register_script('x7validator-script', $plugin_url . '/js/validator.js', false, false, false);
		wp_enqueue_script( 'x7validator-script' );
	//register datatables
		wp_register_script('x7datatables-script', $plugin_url . '/js/jquery.dataTables.min.js', false, false, false);
		wp_enqueue_script( 'x7datatables-script' );
	}
}

//enqueue styles and scripts
add_action('after_setup_theme', 'enqueue_my_styles');
function enqueue_my_styles(){
	//jquery ui style
	wp_enqueue_style('jqueryui-style', plugins_url( 'css/jqueryui/jquery-ui-1.8.7.custom.css', __FILE__ ));
	//shadowbox (lightbox) style
	wp_enqueue_style('shadowbox-style', plugins_url( 'css/sbox/shadowbox.css', __FILE__ ));
	//x7video custom style
	wp_enqueue_style('x7video-style', plugins_url( 'css/x7style.css', __FILE__ ));
	//datatables
	wp_enqueue_style('datatables-style', plugins_url( 'css/datatables/css/demo_table_jui.css', __FILE__ ));
}

function kaltura_head() 
{
	$plugin_url = KalturaHelpers::getPluginUrl();
	echo('<link rel="stylesheet" href="' . $plugin_url . '/css/kaltura.css?v'.kaltura_get_version().'" type="text/css" />');
	echo("<script type='text/javascript'>var Kaltura_PluginUrl = '$plugin_url';</script>");
	if (get_option('x7html5enabled') == 1) {
		$html5server = KALTURA_SERVER_URL;
		echo('<script type="text/javascript" src="http://html5.kaltura.org/js"></script>');
		echo("<script type='text/javascript'>mw.setConfig( 'Kaltura.ServiceUrl' , '$html5server' );mw.setConfig( 'Kaltura.CdnUrl' , '$html5server' );mw.setConfig('EmbedPlayer.AttributionButton', false );mw.setConfig( 'EmbedPlayer.NativeControlsMobileSafari', false );</script>");
	}
}

function kaltura_footer() 
{
	$plugin_url = KalturaHelpers::getPluginUrl();
	echo ' 
	<script type="text/javascript">
		function handleGotoContribWizard (widgetId, entryId) {
			KalturaModal.openModal("contribution_wizard", "' . $plugin_url . '/page_contribution_wizard_front_end.php?wid=" + widgetId + "&entryId=" + entryId, { width: 680, height: 360 } );
			jQuery("#contribution_wizard").addClass("modalContributionWizard");
		}
	
		function handleGotoEditorWindow (widgetId, entryId) {
			KalturaModal.openModal("simple_editor", "' . $plugin_url . '/page_simple_editor_front_end.php?wid=" + widgetId + "&entryId=" + entryId, { width: 890, height: 546 } );
			jQuery("#simple_editor").addClass("modalSimpleEditor");
		}
		
		function gotoContributorWindow(entryId) {
			handleGotoContribWizard("", entryId);
		}
		
		function gotoEditorWindow(entryId) {
			handleGotoEditorWindow("", entryId);
		}
	</script>
	
	';
}

function kaltura_add_admin_css($content) 
{
	$plugin_url = KalturaHelpers::getPluginUrl();
	$content .= '<link rel="stylesheet" href="' . $plugin_url . '/css/kaltura.css?v'.kaltura_get_version().'" type="text/css" />' . "\n";
	$content .= "<script type='text/javascript'>var Kaltura_PluginUrl = '$plugin_url';</script>\n";
	echo $content;
}

function kaltura_create_tab() 
{
	require_once('tab_create.php');
}

function kaltura_add_media_button($content)
{
	global $post_ID, $temp_ID;
	$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
	$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
	$kaltura_iframe_src = apply_filters('kaltura_iframe_src', "$media_upload_iframe_src&amp;tab=kaltura_upload");
	$kaltura_browse_iframe_src = apply_filters('kaltura_iframe_src', "$media_upload_iframe_src&amp;tab=kaltura_browse");
	$kaltura_title = __('Add Interactive Video');
	$kaltura_button_src = KalturaHelpers::getPluginUrl() . '/images/interactive_video_button.gif';
	$content .= <<<EOF
		<a href="{$kaltura_iframe_src}&amp;TB_iframe=true&amp;height=500&amp;width=640" class="thickbox" title='$kaltura_title'><img src='$kaltura_button_src' alt='$kaltura_title' /></a>
EOF;

	return $content;
}

function kaltura_add_upload_tab($content)
{
	$content["kaltura_upload"] = __("All in One Video");
	return $content;
}

function kaltura_add_upload_tab_interactive_video_only($content)
{
	$content = array();
	$content["kaltura_upload"] = __("Add Interactive Video");
	$content["kaltura_browse"] = __("Browse Interactive Videos");
	return $content;
}

function kaltura_upload_tab()
{
	wp_enqueue_style('media');	
	wp_iframe('kaltura_upload_tab_content');
}

function kaltura_browse_tab()
{
	wp_enqueue_style('media');		
	wp_iframe('kaltura_browse_tab_content');
}

function kaltura_upload_tab_content()
{
	unset($GLOBALS['wp_filter']['media_upload_tabs']); // remove all registerd filters for the tabs
	add_filter("media_upload_tabs", "kaltura_add_upload_tab_interactive_video_only"); // register our filter for the tabs
	media_upload_header(); // will add the tabs menu
	
	if (!isset($_GET["kaction"]))
		$_GET["kaction"] = "upload";
	require_once("lib/kaltura_library_controller.php");
}

function kaltura_browse_tab_content()
{
	unset($GLOBALS['wp_filter']['media_upload_tabs']); // remove all registerd filters for the tabs
	add_filter("media_upload_tabs", "kaltura_add_upload_tab_interactive_video_only"); // register our filter for the tabs
	media_upload_header(); // will add the tabs menu
	
	if (!isset($_GET["kaction"]))
		$_GET["kaction"] = "browse";
	require_once("lib/kaltura_library_controller.php");
}

function kaltura_comment_form($post_id) 
{
	$user = wp_get_current_user();
	if (!$user->ID && !KalturaHelpers::anonymousCommentsAllowed())
	{
		echo "You must be <a href=" . get_option('siteurl') . "/wp-login.php?redirect_to=" . urlencode(get_permalink()) . ">logged in</a> to post a <br /> video comment.";
	}
	else
	{
		$plugin_url = KalturaHelpers::getPluginUrl();
		$js_click_code = "Kaltura.openCommentCW('".$plugin_url."'); ";
		echo "<input type=\"button\" id=\"kaltura_video_comment\" name=\"kaltura_video_comment\" tabindex=\"6\" value=\"Add Video Comment\" onclick=\"" . $js_click_code . "\" />";
	}
}

//PHP REST helper function that does not use CURL
function rest_helper($url, $params = null, $verb = 'POST', $format = 'xml')
{
  $cparams = array(
    'http' => array(
      'method' => $verb,
      'ignore_errors' => true
    )
  );
  if ($params !== null) {
    $params = http_build_query($params);
    if ($verb == 'POST') {
      $cparams['http']['content'] = $params;
    } else {
      $url .= '?' . $params;
    }
  }

  $context = stream_context_create($cparams);
  $fp = fopen($url, 'rb', false, $context);
  if (!$fp) {
    $res = false;
  } else {
    // If you're trying to troubleshoot problems, try uncommenting the
    // next two lines; it will show you the HTTP response headers across
    // all the redirects:
    // $meta = stream_get_meta_data($fp);
    // var_dump($meta['wrapper_data']);
    $res = stream_get_contents($fp);
  }

  if ($res === false) {
    throw new Exception("$verb $url failed: $php_errormsg");
  }

  switch ($format) {
    case 'json':
      $r = json_decode($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as json");
      }
      return $r;

    case 'xml':
      $r = simplexml_load_string($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as xml");
      }
      return $r;
  }
  return $res;
}

function x7video_shortcode($atts)
{
   extract( shortcode_atts( array(
      'widget' => 'kcw',
      'show' => 'all',
      'namelike' => 'false',
      'user' => 'false',
      'tags' => 'false',
      'admintags' => 'false',
      'category' => 'false',
      'pagesize' => '500',
      'pageindex' => '1'
      ), $atts ) );

	//First, master check for logged in wordpress user.  ALL widgets will not function if this fails.
if (is_user_logged_in()){
	//Set add scripts global to true, which results in javascripts printing in footer
	global $add_my_script;
	$add_my_script = true;
	global $current_user;
        get_currentuserinfo();

	$user_login = $current_user->user_login;
	$user_ID = $current_user->ID;
	$x7kalpartnerid = get_option("kaltura_partner_id");
	$x7kalsubpartnerid = $x7kalpartnerid . "00";
	$x7server = KalturaHelpers::getServerUrl();
	$x7serverget = urlencode($x7server);
	$x7kaladminsecret = get_option("kaltura_admin_secret");
	$x7kalusersecret = get_option("kaltura_admin_secret");
	
	$x7bloghome = get_bloginfo('url');
	$x7bloghomeget = urlencode($x7bloghome);
	$pluginurl = KalturaHelpers::getPluginUrl();
	$pluginurlget = urlencode($pluginurl);
	
	$x7uiconfid = get_option('x7uiconfid');
	$x7pluiconfid = get_option('x7pluiconfid');
	$x7adminuiconfid = get_option('x7adminuiconfid');
	$x7kcwuiconfid = get_option('x7kcwuiconfid');
	$x7allowposts = get_option('x7allowposts');
	$x7allowstandard = get_option('x7allowstandard');
	$x7allowadvanced = get_option('x7allowadvanced');

/***********************************************************************************************************************
 * REGULAR VIDEO GALLERY SHORTCODE *
 * ********************************************************************************************************************/	
if ($widget=="videogallery"){
	//Start Kaltura "Admin" Session
	$kmodel = KalturaModel::getInstance();
	$ks = $kmodel->getAdminSession("","$user_login");
	if (!$ks)
		wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));

	$list = $kmodel->listAllEntriesByPagerandFilter($x7kalpartnerid, $show, $namelike, $user, $tags, $admintags, $category, $pagesize, $pageindex);
				$itemcount = "0";
				foreach ($list->objects as $mediaEntry) {
					$itemcount++;
					if ($itemcount == "5"){
						$return .= "</div><div>";
						$itemcount = "0";
					}
						$name     = $mediaEntry->name; // get the entry name
						$id       = $mediaEntry->id;
						$thumbUrl = $mediaEntry->thumbnailUrl;  // get the entry thumbnail URL
						$createdat = (string) $mediaentry->createdAt;
						$createdat = date(DATE_RFC822, $createdat);
						$author = $mediaEntry->userId;
						$description = $mediaEntry->description;
						$description = str_replace("'", "", "$description"); 
					$return .= "<a class='tt' title='<strong>Name</strong>: $name<br /><strong>Creator</strong>: $submitter' href='javascript:LoadMedia(\"$id\")'><img alt='$name' title='$name' src='$thumbUrl'></a>";
				}
	} //end if widget is regular gallery
/***********************************************************************************************************************
 * VIDEO SCROLL GALLERY SHORTCODE *
 * ********************************************************************************************************************/	
if ($widget=="scrollgallery"){
	//Start Kaltura "Admin" Session
	$kmodel = KalturaModel::getInstance();
	$ks = $kmodel->getAdminSession("","$user_login");
	if (!$ks)
		wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
	
	$plurl = "$x7server/index.php/partnerservices2/executeplaylist?partner_id=$x7kalpartnerid&subp_id=$x7kalsubpartnerid&format=8&playlist_id=";
	
	$list = $kmodel->listAllEntriesByPagerandFilter($x7kalpartnerid, $show, $namelike, $user, $tags, $admintags, $category, $pagesize, $pageindex);
	
	if ($show == 'playlists') {
		$x7galuiconfid = $x7pluiconfid;
	} else {
		$x7galuiconfid = $x7uiconfid;
	}
	
	$player = $kmodel->getPlayerUiConf($x7galuiconfid);
	
	//player vars
		$entryId = $list->objects[0]->id;
		$player_width = $player->width;
		$player_height = $player->height;
		$backgroundColor = "000000";
	
	$return .= <<<GALLERY
	<script type="text/javascript">
	    if (swfobject.hasFlashPlayerVersion("9.0.0")) {
	      var fn = function() {
	        var att = { data:"$x7server/index.php/kwidget/wid/_$x7kalpartnerid/uiconf_id/$x7galuiconfid", 
						width:"$player_width", 
						height:"$player_height",
						id:"mykdp",
						name:"mykdp" };
GALLERY;
			if ($show != 'playlists') {
				$return .= <<<GALLERY4
				var par = { flashvars:"&entryId=$entryId" +
						"&autoPlay=true",
						allowScriptAccess:"always",
						allowfullscreen:"true",
						bgcolor:"$backgroundColor"
					};
GALLERY4;
			} else {
				$plurl2 = $plurl.$entryId;
				$plurl2 = urlencode($plurl2);
				$return .= <<<GALLERY5
				
				var par = { flashvars:"&playlistAPI.autoInsert=true&playlistAPI.kpl0Name=Playlist&playlistAPI.kpl0Url=$plurl2" +
						"&autoPlay=true",
						allowScriptAccess:"always",
						allowfullscreen:"true",
						bgcolor:"$backgroundColor"
					};
GALLERY5;
			}
			$return .= <<<GALLERY6
	        var id = "mykdp";
	        var myObject = swfobject.createSWF(att, par, id);
	      }
	      swfobject.addDomLoadEvent(fn);
	    }
    </script>

		<div id="mykdp">KDP Should be loaded here...</div>
		<!-- "previous page" action -->
		<div id="scrollwrap">
		<a class="prev browse left"></a>
		<div class="scrollable"> 
			<div class="items">
			<div>
GALLERY6;
				$itemcount = "0";
				foreach ($list->objects as $mediaEntry) {
					$itemcount++;
					if ($itemcount == "5"){
						$return .= "</div><div>";
						$itemcount = "0";
					}
						$name     = $mediaEntry->name; // get the entry name
						$id       = $mediaEntry->id;
						$thumbUrl = $mediaEntry->thumbnailUrl;  // get the entry thumbnail URL
						$submitter = $mediaEntry->userId;
						$description = $mediaEntry->description;
						$description = str_replace("'", "", "$description"); 
					$return .= "<a class='tt' title='<strong>Name</strong>: $name<br /><strong>Creator</strong>: $submitter' href='javascript:LoadMedia(\"$id\")'><img alt='$name' title='$name' src='$thumbUrl'></a>";
				}
	$return .= <<<GALLERY2
			</div>
			</div>
		</div>
		<!-- "next page" action -->
		<a class="next browse right"></a>
		<div class="tooltip" id="tooltip"></div>
		</div>
		<br clear="all" />

	<script type="text/javascript">
		function LoadMedia(entryId) {
		
GALLERY2;
		if ($show == 'playlists'){
			$return .= <<<PLURL
			var plurl = '$plurl'+entryId;
			plurl = encodeURIComponent(plurl);
				var par = { flashvars:"&playlistAPI.autoInsert=true" +
						"&playlistAPI.kpl0Name=Playlist" +
						"&playlistAPI.kpl0Url=" + 
						plurl +
						"&autoPlay=true",
						allowScriptAccess:"always",
						allowfullscreen:"true",
						bgcolor:"$backgroundColor"
					};
				var att = { data:"$x7server/index.php/kwidget/wid/_$x7kalpartnerid/uiconf_id/$x7galuiconfid", 
						width:"$player_width", 
						height:"$player_height",
						id:"mykdp",
						name:"mykdp" };
				var id = "mykdp";
			var myObject = swfobject.createSWF(att, par, id);
PLURL;

		} else {
			$return .= "jQuery('#mykdp').get(0).sendNotification('changeMedia',{entryId:entryId})";
		}
	$return .= <<<GALLERY3
	
		}
		jQuery(document).ready(function() {
			jQuery("div.scrollable").scrollable().find("a").tooltip({
				tip: '#tooltip',
				position: 'bottom center',
				effect: 'slide',
				delay: '2000'
			});
		}); 
	</script>
GALLERY3;

	} //end if widget is scroll gallery
/***********************************************************************************************************************
 * UPLOAD NEW MEDIA SHORTCODE *
 * ********************************************************************************************************************/	
if ($widget=="kcw"){
		//Kaltura Contribution Wizard (Uploader)
		//Start Kaltura "User" Session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getClientSideSession("",86400,$user_login);
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
		//Embed the KCW
		$return .= '<div id="kcw"></div>';
		$return .= "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js'></script>";
		$return .= <<<X7KCW
		<script type="text/javascript">
		var params = {
			allowScriptAccess: "always",
			allowNetworking: "all",
			wmode: "opaque"
		};
		var flashVars = {"Permissions":"1","partnerId":"$x7kalpartnerid","uid":"$user_login","ks":"$ks","afterAddEntry":"onContributionWizardAfterAddEntry","showCloseButton":"false"};
		swfobject.embedSWF("$x7server/kcw/ui_conf_id/1727883", "kcw", "680", "360", "9.0.0", false, flashVars, params);
		
		function onContributionWizardAfterAddEntry(entries) {
		        alert("Media successfully uploaded.  Please allow time for conversion.  Page will refresh now.");
			window.location.reload(true);
		}
		</script>
X7KCW;

	} //end if widget is kcw
/***********************************************************************************************************************
 * USER UPLOADED MEDIA SHORTCODE *
 * ********************************************************************************************************************/
if ($widget=="useruploads"){
		//This widget displays the logged in user's Kaltura uploads and offers the ability to
		//play, edit (remix), delete and post them as drafts to the wordpress blog
		//Start Kaltura "Admin" Session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getAdminSession("","$user_login");
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
		$ksget = urlencode($ks);
		
		//SET RPCURL XMLRPC FILE VALUE
		$x7rpcurl = $x7bloghome . "/xmlrpc.php";
		$x7fullplugurl = $pluginurl."/ixr.php";
		$playurl = $pluginurl."/x7vidplayer.php";
                $advancedediturl = $pluginurl."/x7advancededitor.php";
		$standardediturl = $pluginurl."/x7standardeditor.php";
		//GET WORDPRESS CATEGORIES LIST
		$categories = get_categories('hide_empty=0'); 
			foreach ($categories as $cat) {
				$option .= "<option value=\"$cat->cat_name\">$cat->cat_name</option>";
			}
		
		//EMBED DELETE JAVASCRIPT FUNCTION AND POST FUNCTION AND GET VARIABLE READER
		$return.= <<<DELETE_JS
		<script type="text/javascript">
			function x7VidPlay()
			{
				var eid = jQuery("#x7aplaychange").attr("title");
				var playurl = '$playurl'+'?eid='+eid+'&x7kalpartnerid=$x7kalpartnerid&x7bloghomeget=$x7bloghomeget&x7server=$x7serverget&x7uiconfid=$x7adminuiconfid';
				Shadowbox.open({
					content: playurl,
					player: "iframe",
					height: "370",
					width: "405"
				});
			}
			
			function x7VidEditStandard()
			{
				var eid = jQuery("#x7aeditchange").attr("title");
				var name = jQuery("#x7aeditchange").attr("name");
				jQuery.post(
					"$pluginurl/x7mixcreate.php",
					{'x7bloghome': '$x7bloghome', 'x7server': "$x7server", 'ks': "$ks", 'x7editortype': '1', 'eid': eid, 'x7name': name, 'x7kalpartnerid': "$x7kalpartnerid", 'user_login': "$user_login"},
					function ( response ){
						jQuery('div#x7form').hide('slow');
						jQuery('div#x7tablewrap').show('slow');
						var editurl = '$standardediturl'+'?entryId='+response+'&ks=$ksget&x7kalpartnerid=$x7kalpartnerid&x7bloghomeget=$x7bloghomeget&userlogin=$user_login&x7server=$x7serverget&pluginurl=$pluginurlget';
						Shadowbox.open({
						content: editurl,
						player: "iframe",
						height: "600",
						width: "1000"
					});
				});
			}
			
			function x7VidEditAdvanced()
			{
				var eid = jQuery("#x7aedit2change").attr("title");
				var name = jQuery("#x7aedit2change").attr("name");
				jQuery.post(
					"$pluginurl/x7mixcreate.php",
					{'x7bloghome': '$x7bloghome', 'x7server': "$x7server", 'ks': "$ks", 'x7editortype': '2', 'eid': eid, 'x7name': name, 'x7kalpartnerid': "$x7kalpartnerid", 'user_login': "$user_login"},
					function ( response ){
						jQuery('div#x7form').hide('slow');
						jQuery('div#x7tablewrap').show('slow');
						var editurl = '$advancedediturl'+'?entryId='+response+'&ks=$ksget&x7bloghomeget=$x7bloghomeget&x7kalpartnerid=$x7kalpartnerid&userlogin=$user_login&x7server=$x7serverget&pluginurl=$pluginurlget';
						Shadowbox.open({
						content: editurl,
						player: "iframe",
						height: "600",
						width: "1000"
					});
				});
			}
		
			function x7VidDelete()
			{
				var delid = jQuery("#x7adelchange").attr("title");
				if (confirm("Warning! This will affect all mixes that include entry ID: " + delid + ". Continue?"))
				{ 
				    jQuery.post(
				       "$pluginurl/x7delete.php",
				       {'x7bloghome': '$x7bloghome', 'ks': "$ks", 'x7entrytype': 'media', 'eid': delid, 'x7server': "$x7server"},
				       function ( response ){
					      jQuery("#x7entriestable tbody tr [title="+delid+"]").remove();
					      jQuery('div#x7form').hide('slow');
						jQuery('div#x7tablewrap').show('slow');
						alert("Entry successfully deleted.");
					      //var x7nodes = x7Table.fnGetNodes();
					      //TODO NEED TO REMOVE APPROPRIATE ROW FROM THE TABLE AND REFRESH TABLE
				       });//end post
				} //end confirm
			} //end x7VidDelete
			var postout;
			postout = 'false';
			function x7VidPost(eid, name)
			{
				if (postout == 'false'){
					formValidate();
					var thumburl = '$x7server/p/1/sp/10000/thumbnail/entry_id/'+eid+'/width/150/height/120';
					var embedcode = '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="330" width="400" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'" data="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="&" /><param name="movie" value="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'" /><a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a> <a href="http://corp.kaltura.com/technology/video_player">video player</a> <a rel="media:thumbnail" href="$x7server/p/$x7kalpartnerid/sp/$x7kalsubpartnerid/thumbnail/entry_id/'+eid+'/width/120/height/90/bgcolor/000000/type/2" /> <span property="dc:description" content="" /><span property="media:title" content="x7Video" /> <span property="media:width" content="400" /><span property="media:height" content="330" /> <span property="media:type" content="application/x-shockwave-flash" /><span property="media:duration" content="{DURATION}" /> </object>';
					
					jQuery('#x7aplaychange').attr("title",eid);
					jQuery('#x7aeditchange').attr("title",eid);
					jQuery('#x7aedit2change').attr("title",eid);
					jQuery('#x7aeditchange').attr("name",name);
					jQuery('#x7aedit2change').attr("name",name);
					jQuery('#x7adelchange').attr("title",eid);
					jQuery('textarea#x7embedchange').val(embedcode);
					jQuery(':input#x7hiddeneidchange').val(eid);
					jQuery('img#x7imgchange').attr("src",thumburl);
					
					Shadowbox.init();
					jQuery('div#x7tablewrap').hide('slow');
					jQuery('div#x7form').show('slow');
					var allowpost = '$x7allowposts';
					if (allowpost=='1'){
						//jQuery('div#x7postform').show('slow');
						jQuery( "#x7form" ).tabs("option","disabled",[]);
					}
					postout = 'true';
				} else if(postout == 'true')
				{
					jQuery('div#x7form').hide('slow');
					jQuery('div#x7tablewrap').show('slow');
					postout = 'false';
				}
			}
			function formValidate()
			{
				var title = new LiveValidation('title', {onlyOnSubmit: true });
				title.add( Validate.Presence );
				var keywords = new LiveValidation('keywords', {onlyOnSubmit: true });
				//Pattern matches for comma delimited string
				keywords.add( Validate.Format, { pattern: /([^\"]+?)\",?|([^,]+),?|,/ } );
				var password = new LiveValidation('password', {onlyOnSubmit: true });
				password.add( Validate.Presence );
			}
			//Function that retrieves URL get variables
			function getUrlVars()
			{
				var vars = [], hash;
				var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
				for(var i = 0; i < hashes.length; i++)
				    {
				        hash = hashes[i].split('=');
				        vars.push(hash[0]);
				        vars[hash[0]] = hash[1];
				    }
				return vars;
			}
			var map = getUrlVars();
			//Shows the user success or failure feedback
			function addSuccessDiv() {
			if (map.result=="success"){
				jQuery("#x7loading").prepend("<div class='ui-state-error'>Success! Your post has now been queued for moderation by an administrator.</div><br><br>");
			}
			if (map.result=="fail") {
				jQuery("#x7loading").prepend("<div class='ui-state-error'>Post failed! Please try again.</div><br><br>");
			}
			}
			jQuery(document).ready(function() {
				addSuccessDiv();
				jQuery("td.tt[title]").tooltip({
					position: 'bottom center',
					effect: 'slide'
				});
			
			jQuery("#x7entriestable").dataTable({
				"bJQueryUI": true,
				"bPaginate": true,
				"bProcessing": true,
				"bSort": true,
				"sScrollY": "300px",
				"sScrollX": "100%",
				"iDisplayLength": 10,
				"sPaginationType": "full_numbers"
			});
			
			jQuery("#x7entriestable tbody tr").live('click', function() {
				var eid = jQuery(this).attr("title");
				var name = jQuery(this).attr("name");
				x7VidPost(eid, name);
			});
			
			//set up the tabs with the post tab disabled by fault and enabled with a check for x7allowposts
			jQuery( "#x7form" ).tabs({ disabled: [1] });
			
			//set up all of the buttons with icons
			jQuery( "button#x7aplaychange" ).button({
				icons: { primary: "ui-icon-play" }
				});
			jQuery( "button#x7aeditchange" ).button({
				icons: { primary: "ui-icon-scissors" }
				});
			jQuery( "button#x7aedit2change" ).button({
				icons: { primary: "ui-icon-scissors" }
				});
			jQuery( "button#x7adelchange" ).button({
				icons: { primary: "ui-icon-trash" }
				});
			jQuery( "button.x7cancel" ).button({
				icons: { primary: "ui-icon-cancel" }
				});
			
			}); //end document ready
DELETE_JS;

		$return .= '</script>';
		//ADD X7LOADING DIV
			$return .= "<div id='x7loading' style='display:none'><img border='0' src='$pluginurl/images/x7loader.gif'></div><br /><br />";
		
		//Embed user uploads
		$xmlresult = rest_helper("$x7server/api_v3/?service=media&action=list",
					 array(
						'ks' => $ks,
						'filter:userIdEqual' => $user_login,
						'filter:orderBy' => '-createdAt'
					 ), 'POST'
					 );
						
			//ADD post form
			$return .= <<<X7POSTFORM
			<div class="ui-corner-all" style="display:none" id="x7form">
				<ul>
					<li><a href="#x7form-1">Media Admin</a></li>
					<li><a href="#x7form-2">Submit for Post</a></li>
					<li><a href="#x7form-3">Embed Code</a></li>
				</ul>
				<div id="x7form-1">
				<img id="x7imgchange" src=""><br />

				<button onClick="x7VidPlay()" id="x7aplaychange" title="">[PLAY]</button><br />
X7POSTFORM;
			if ($x7allowstandard) {
				$return .= '<button id="x7aeditchange" name="" title="" onClick="x7VidEditStandard()">[CREATE STANDARD MIX]</button><br />';
			}
			if ($x7allowadvanced) {
				$return .= '<button id="x7aedit2change" name="" title="" onClick="x7VidEditAdvanced()">[CREATE ADVANCED MIX]</button><br />';
			}
			$return .= <<<X7POSTFORM2
				<button id="x7adelchange" title="" onClick="x7VidDelete()">[DELETE]</button><br />
				<button class="x7cancel" onClick="x7VidPost();">[CANCEL]</button>
				</div> <!-- end x7form-1 -->
				<div id="x7form-2">
					<div id="x7postform">
					<form name="x7postdraft" id="x7postdraft" action="$pluginurl/x7post.php" method="post">
						<input type="hidden" name="x7server" id="x7server" value="$x7server" >
						<input type="hidden" name="x7kalpartnerid" id="x7kalpartnerid" value="$x7kalpartnerid" >
						<input type="hidden" name="x7uiconfid" id="x7uiconfid" value="$x7uiconfid" >
						<input type="hidden" name="eid" id="x7hiddeneidchange" value="" >
						<input type="hidden" name="rpcurl" id="rpcurl" value="$x7rpcurl" >
						<input type="hidden" name="username" id="username" value="$user_login" >
						<input type="hidden" name="x7fullplugurl" id="x7fullplugurl" value="$x7fullplugurl" >
						<input type="hidden" name="x7bloghome" id="x7bloghome" value="$x7bloghome" >
						<label for="title">Title of Post:</label><br />
						<input type="text" size="25" name="title" id="title" value="" class="" ><br />
						<label for="category">Category(ies):</label><br />
						<select name="category[]" id="category" multiple="multiple" class="">
							$option
						</select><br />
						<label for="description">Description:</label><br />
						<textarea cols="35" rows="4" name="description" id="description" class="" />Another new video from $user_login!</textarea><br />
						<label for="keywords">Tags (comma delimited):</label><br />
						<input type="text" size="25" name="keywords" id="keywords" value="" class="" ><br />
						<label for="password">Wordpress Password:</label><br />
						<input type="password" name="password" id="password" size="20" ><br />
						<input type="submit" value="[POST]" name="submit" id="submit" ></form>
					</div> <!--end x7postform -->
				</div> <!--end x7form-2 -->
				<div id="x7form-3">
					<strong>Embed code:</strong><br><textarea id="x7embedchange" cols="40" rows="10"></textarea>
				</div> <!--end x7form-3 -->
			</div> <!--end x7form -->
X7POSTFORM2;

			$return .= "<div id='x7tablewrap'><table id='x7entriestable'><thead><tr><th>Name</th><th>ID</th><th>Description</th><th>Duration</th><th>When Created</th></tr></thead><tbody>";
			
		foreach ($xmlresult->result->objects->item as $mediaentry) {
			$eid = $mediaentry->id;
			$thumb = $mediaentry->thumbnailUrl;
			$userId = $mediaentry->userId;
			$name = $mediaentry->name;
			$description = $mediaentry->description;
			$duration = $mediaentry->duration;
			$createdat = (string) $mediaentry->createdAt;
			$createdat = date(DATE_RFC822, $createdat);
				$return .= <<<ENTRY_DIV
				<tr title="$eid" name="$name">
					<td class="tt" title="Click to open media management panel.">$name</td>
					<td>$eid</td>
					<td>$description</td>
					<td>$duration</td>
					<td>$createdat</td>
				</tr>
ENTRY_DIV;
		} //end foreach
		//End x7entries table
		$return .= "</tbody></table></div>";
	} //end if widget is user upload gallery
/***********************************************************************************************************************
 * USER CREATED MIXES SHORTCODE *
 * ********************************************************************************************************************/
	if ($widget=="usermixes"){
		//This widget displays the logged in user's Kaltura uploads and offers the ability to
		//play, edit (remix), delete and post them as drafts to the wordpress blog
		//Start Kaltura "Admin" Session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getAdminSession("","$user_login");
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
		$ksget = urlencode($ks);
		
		//SET RPCURL XMLRPC FILE VALUE
		$x7rpcurl = $x7bloghome . "/xmlrpc.php";
		$x7fullplugurl = $pluginurl."/ixr.php";
		$playurl = $pluginurl."/x7vidplayer.php";
                $advancedediturl = $pluginurl."/x7advancededitor.php";
		$standardediturl = $pluginurl."/x7standardeditor.php";
		
		//GET CATEGORIES LIST
		$categories = get_categories('hide_empty=0'); 
			foreach ($categories as $cat) {
				$option .= "<option value=\"$cat->cat_name\">$cat->cat_name</option>";
			}
		
		//EMBED DELETE JAVASCRIPT FUNCTION AND POST FUNCTION AND GET VARIABLE READER
		$return.= <<<DELETE_JS
		<script type="text/javascript">
		
			function x7VidPlay()
			{
				var eid = jQuery("button#x7aplaychange").attr("title");
				var playurl = '$playurl'+'?eid='+eid+'&x7kalpartnerid=$x7kalpartnerid&x7server=$x7serverget&x7uiconfid=$x7adminuiconfid';
				Shadowbox.open({
					content: playurl,
					player: "iframe",
					height: "370",
					width: "405"
				});
			}
			
			function x7VidEdit()
			{
				var eid = jQuery("button#x7aeditchange").attr("title");
				var name = jQuery("button#x7aeditchange").attr("name");
				var type = jQuery("button#x7aeditchange").attr("edtype");
				jQuery('div#x7form').hide('slow');
				jQuery('div#x7tablewrap').show('slow');
				if (type == "1"){
					var editurl = '$standardediturl'+'?entryId='+eid+'&ks=$ksget&x7bloghomeget=$x7bloghomeget&x7kalpartnerid=$x7kalpartnerid&userlogin=$user_login&x7server=$x7serverget&pluginurl=$pluginurlget';
				}
				if (type == "2"){
					var editurl = '$advancedediturl'+'?entryId='+eid+'&ks=$ksget&x7bloghomeget=$x7bloghomeget&x7kalpartnerid=$x7kalpartnerid&userlogin=$user_login&x7server=$x7serverget&pluginurl=$pluginurlget';
				}
				Shadowbox.open({
					content: editurl,
					player: "iframe",
					height: "600",
					width: "1000"
				});
			}
		
			function x7VidDelete()
			{
				var delid = jQuery("button#x7adelchange").attr("title");
				if (confirm("Warning!  This will affect all playlists that contain mix id: " + delid + ". Continue?"))
				{ 
				    jQuery.post(
				       "$pluginurl/x7delete.php",
				       {'x7bloghome': '$x7bloghome', 'ks': "$ks", 'x7entrytype': 'mix', 'eid': delid, 'x7server': "$x7server"},
				       function ( response ){
					      jQuery("#x7entriestable tbody tr [title="+delid+"]").remove();
					      jQuery('div#x7form').hide('slow');
						jQuery('div#x7tablewrap').show('slow');
						alert("Mix successfully deleted. Reloading table...");
						window.location.reload();
					      //var x7nodes = x7Table.fnGetNodes();
					      //TODO NEED TO REMOVE APPROPRIATE ROW FROM THE TABLE AND REFRESH TABLE
				       });//end post
				} //end confirm
			} //end x7VidDelete
			var postout;
			postout = 'false';
			function x7VidPost(eid, name, type)
			{
				if (postout == 'false'){
					formValidate();
					var thumburl = '$x7server/p/1/sp/10000/thumbnail/entry_id/'+eid+'/width/150/height/120';
					var embedcode = '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="330" width="400" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'" data="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="&" /><param name="movie" value="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/'+eid+'" /><a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a> <a href="http://corp.kaltura.com/technology/video_player">video player</a> <a rel="media:thumbnail" href="$x7server/p/$x7kalpartnerid/sp/$x7kalsubpartnerid/thumbnail/entry_id/'+eid+'/width/120/height/90/bgcolor/000000/type/2" /> <span property="dc:description" content="" /><span property="media:title" content="x7Video" /> <span property="media:width" content="400" /><span property="media:height" content="330" /> <span property="media:type" content="application/x-shockwave-flash" /><span property="media:duration" content="{DURATION}" /> </object>';
					
					jQuery('button#x7aplaychange').attr("title",eid);
					jQuery('button#x7aeditchange').attr("title",eid);
					jQuery('button#x7aeditchange').attr("name",name);
					jQuery('button#x7aeditchange').attr("edtype",type);
					jQuery('button#x7adelchange').attr("title",eid);
					jQuery('textarea#x7embedchange').val(embedcode);
					jQuery(':input#x7hiddeneidchange').val(eid);
					jQuery('img#x7imgchange').attr("src",thumburl);
					
					Shadowbox.init();
					jQuery('div#x7tablewrap').hide('slow');
					jQuery('div#x7form').show('slow');
					var allowpost = '$x7allowposts';
					if (allowpost=='1'){
						//jQuery('div#x7postform').show('slow');
						jQuery( "#x7form" ).tabs("option","disabled",[]);
					}
					postout = 'true';
				} else if(postout == 'true')
				{
					jQuery('div#x7form').hide('slow');
					jQuery('div#x7tablewrap').show('slow');
					postout = 'false';
				}
			}
			function formValidate()
			{
				var title = new LiveValidation('title', {onlyOnSubmit: true });
				title.add( Validate.Presence );
				var keywords = new LiveValidation('keywords', {onlyOnSubmit: true });
				//Pattern matches for comma delimited string
				keywords.add( Validate.Format, { pattern: /([^\"]+?)\",?|([^,]+),?|,/ } );
				var password = new LiveValidation('password', {onlyOnSubmit: true });
				password.add( Validate.Presence );
			}
			//Function that retrieves URL get variables
			function getUrlVars()
			{
				var vars = [], hash;
				var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
				for(var i = 0; i < hashes.length; i++)
				    {
				        hash = hashes[i].split('=');
				        vars.push(hash[0]);
				        vars[hash[0]] = hash[1];
				    }
				return vars;
			}
			var result = getUrlVars()["result"];
			//Shows the user success or failure feedback
			function addSuccessDiv() {
			if (result=="success"){
				alert("Success! Your post has now been queued for moderation by an administrator.");
			}
			if (result=="fail") {
				alert("Post failed! Please try again.");
			}
			}
			jQuery(document).ready(function() {
				addSuccessDiv();
				jQuery("td.tt[title]").tooltip({
					position: 'bottom center',
					effect: 'slide'
				});
				
			x7table = jQuery("#x7entriestable").dataTable({
				"bJQueryUI": true,
				"bPaginate": true,
				"bProcessing": true,
				"bSort": true,
				"sScrollY": "300px",
				"sScrollX": "100%",
				"iDisplayLength": 10,
				"sPaginationType": "full_numbers"
			});
			
			jQuery("#x7entriestable tbody tr").live('click', function() {
				var eid = jQuery(this).attr("title");
				var name = jQuery(this).attr("name");
				var type = jQuery(this).attr("edtype");
				x7VidPost(eid, name, type);
			});
			
			//set up the tabs with the post tab disabled by fault and enabled with a check for x7allowposts
			jQuery( "#x7form" ).tabs({ disabled: [1] });
			
			//set up all of the buttons with icons
			jQuery( "button#x7aplaychange" ).button({
				icons: { primary: "ui-icon-play" }
				});
			jQuery( "button#x7aeditchange" ).button({
				icons: { primary: "ui-icon-scissors" }
				});
			jQuery( "button#x7adelchange" ).button({
				icons: { primary: "ui-icon-trash" }
				});
			jQuery( "button.x7cancel" ).button({
				icons: { primary: "ui-icon-cancel" }
				});
			
			}); //end document ready
DELETE_JS;

		$return .= '</script>';
		//ADD X7LOADING DIV
			$return .= "<div id='x7loading' style='display:none'><img border='0' src='$pluginurl/images/x7loader.gif'></div><br /><br />";
		
		//Embed user uploads
		$xmlresult = rest_helper("$x7server/api_v3/?service=mixing&action=list",
					 array(
						'ks' => $ks,
						'filter:userIdEqual' => $user_login,
						'filter:orderBy' => '-createdAt'
					 ), 'POST'
					 );
						
			//ADD post form
			$return .= <<<X7POSTFORM
			<div class="ui-corner-all" style="display:none" id="x7form">
				<ul>
					<li><a href="#x7form-1">Mix Admin</a></li>
					<li><a href="#x7form-2">Submit for Post</a></li>
					<li><a href="#x7form-3">Embed Code</a></li>
				</ul>
				<div id="x7form-1">
				<img id="x7imgchange" src=""><br />
				<button onClick="x7VidPlay()" id="x7aplaychange" title="">[PLAY]</button><br />
				<button id="x7aeditchange" name="" title="" onClick="x7VidEdit()">[EDIT]</button><br />
				<button id="x7adelchange" title="" onClick="x7VidDelete()">[DELETE]</button><br />
				<button class="x7cancel" onClick="x7VidPost();">[CANCEL]</button>
				</div> <!-- end x7form-1 -->
				<div id="x7form-2">
					<div id="x7postform">
					<form name="x7postdraft" id="x7postdraft" action="$pluginurl/x7post.php" method="post">
						<input type="hidden" name="x7server" id="x7server" value="$x7server" >
						<input type="hidden" name="x7kalpartnerid" id="x7kalpartnerid" value="$x7kalpartnerid" >
						<input type="hidden" name="x7uiconfid" id="x7uiconfid" value="$x7uiconfid" >
						<input type="hidden" name="eid" id="x7hiddeneidchange" value="" >
						<input type="hidden" name="rpcurl" id="rpcurl" value="$x7rpcurl" >
						<input type="hidden" name="username" id="username" value="$user_login" >
						<input type="hidden" name="x7fullplugurl" id="x7fullplugurl" value="$x7fullplugurl" >
						<input type="hidden" name="x7bloghome" id="x7bloghome" value="$x7bloghome" >
						<label for="title">Title of Post:</label><br />
						<input type="text" size="25" name="title" id="title" value="" class="" ><br />
						<label for="category">Category(ies):</label><br />
						<select name="category[]" id="category" multiple="multiple" class="">
							$option
						</select><br />
						<label for="description">Description:</label><br />
						<textarea cols="35" rows="4" name="description" id="description" class="" />Another new mix from $user_login!</textarea><br />
						<label for="keywords">Tags (comma delimited):</label><br />
						<input type="text" size="25" name="keywords" id="keywords" value="" class="" ><br />
						<label for="password">Wordpress Password:</label><br />
						<input type="password" name="password" id="password" size="20" ><br />
						<input type="submit" value="[POST]" name="submit" id="submit" ></form>
					</div> <!--end x7postform -->
				</div> <!--end x7form-2 -->
				<div id="x7form-3">
					<strong>Embed code:</strong><br><textarea id="x7embedchange" cols="40" rows="10"></textarea>
				</div> <!--end x7form-3 -->
			</div> <!--end x7form -->
X7POSTFORM;

			$return .= "<div id='x7tablewrap'><table id='x7entriestable'><thead><tr><th>Name</th><th>ID</th><th>Description</th><th>Duration (s)</th><th>Editor Type</th><th>When Created</th></tr></thead><tbody>";
			
		foreach ($xmlresult->result->objects->item as $mixentry) {
			$eid = $mixentry->id;
			$thumb = $mixentry->thumbnailUrl;
			$userId = $mixentry->userId;
			$name = $mixentry->name;
			$description = $mixentry->description;
			$duration = $mixentry ->duration;
			$editortype = (string) $mixentry->editorType;
			if ($editortype == "1"){
				$editortypestr = "Simple";
				};
			if ($editortype == "2"){
				$editortypestr = "Advanced";
				};
			$createdat = (string) $mixentry->createdAt;
			$createdat = date(DATE_RSS, $createdat);
                        //only add if the current user is the uploader
			//if ($userId == $user_login) {
				$return .= <<<ENTRY_DIV
				<tr title="$eid" name="$name" edtype="$editortype">
					<td class="tt" title="Click to open administration menu!">$name</td>
					<td>$eid</td>
					<td>$description</td>
					<td>$duration</td>
					<td>$editortypestr</td>
					<td>$createdat</td>
				</tr>
ENTRY_DIV;
			//} //end if user login
		} //end foreach
		//End x7entries table
		$return .= "</tbody></table></div>";
	} //end if widget is user mixes
/***********************************************************************************************************************
 * USER SUBMITTED POSTS SHORTCODE *
 * ********************************************************************************************************************/
if ($widget=="userposts"){
		//gotta use wpdb global here to query the database
		global $wpdb;
		//explain that this will only show draft posts
		$return .= <<<WARNING
		<div class="ui-widget">
      <div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;"> 
	    <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span> 
	    <strong>Posts submitted by you and approved for publishing:</strong>
      </div>
</div>
<br />
WARNING;

		// Extract drafts from database based on parameters
		$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_author = '$user_ID'");
		// Loop through and output results
		//$return .= var_export($drafts);
		if ($posts) {
			//setup drafts master div
			$return .= "<div class='x7drafts'>";
			foreach ($posts as $post) {
				setup_postdata($post);
				$postid = get_the_id();
				$title = get_the_title($postid);
				$content = get_the_content($postid);
				$author = get_the_author($postid);
				$tags = get_the_tags($postid);
				$cats = get_the_category($postid);
				$date = get_the_date();
				$return .= "<div class='ui-corner-all' style='padding:10px;'>";
				$return .= "Post Title: " . $title . "<br>";
				$return .= "Date Submitted: " . $date . "<br>";
				$return .= "Status: Awaiting Moderation<br>";
				$return .= "Content of post:<br><br><br>";
				//$return .= "Tags: " . foreach ($tags as $tag){echo($tag . ', ')} . "<br>";
				//$return .= "Category(ies): " . foreach ($cats as $cat){echo($cat . ', ')} . "<br><br>";
				$return .= $content . "<br><br>";
				$return .="</div>";
			} // end foreach
			//close master drafts div
			$return .= "</div><br />";
			} // end if drafts
		$return .= <<<DRAFTS
		<div class="ui-widget">
      <div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;"> 
	    <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span> 
	    <strong>Posts submitted by you and awaiting approval:</strong>
      </div>
</div>
<br />
DRAFTS;

			// Extract drafts from database based on parameters
		$drafts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_status = 'draft' AND post_author = '$user_ID'");
		// Loop through and output results
		//$return .= var_export($drafts);
		if ($drafts) {
			//setup drafts master div
			$return .= "<div class='x7drafts'>";
			foreach ($drafts as $post) {
				setup_postdata($post);
				$postid = get_the_id();
				$title = get_the_title($postid);
				$content = get_the_content($postid);
				$author = get_the_author($postid);
				$tags = get_the_tags($postid);
				$cats = get_the_category($postid);
				$date = get_the_date();
				$return .= "<div class='ui-corner-all' style='padding:10px;'>";
				$return .= "Post Title: " . $title . "<br>";
				$return .= "Date Submitted: " . $date . "<br>";
				$return .= "Status: Awaiting Moderation<br>";
				$return .= "Content of post:<br><br><br>";
				//$return .= "Tags: " . foreach ($tags as $tag){echo($tag . ', ')} . "<br>";
				//$return .= "Category(ies): " . foreach ($cats as $cat){echo($cat . ', ')} . "<br><br>";
				$return .= $content . "<br><br>";
				$return .="</div>";
			} // end foreach
			//close master drafts div
			$return .= "</div>";
			} // end if drafts
		} //end if widget is user posts
/***********************************************************************************************************************
 * MAKE PLAYLIST WIDGET SHORTCODE *
 * ********************************************************************************************************************/
if ($widget=="makeplaylist"){
		//Start Kaltura admin session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getAdminSession("","$user_login");
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
		$ksget = urlencode($ks);
		//get media
		$list = $kmodel->listAllEntriesByPagerandFilter($x7kalpartnerid, $show, $namelike, $user, $tags, $admintags, $category, $pagesize, $pageindex);
		
		$player = $kmodel->getPlayerUiConf($x7pluiconfid);
	
		//player vars
		$player_width = $player->width;
		$player_height = $player->height;
		
		//add javascript and info box
		$return .= <<<INFOBOX
		<script type="text/javascript">
		
		//list users created playlists
		jQuery(document).ready(function() {
		
		//scrollpane parts
		var scrollPane = jQuery( ".scroll-pane" ),
			scrollContent = jQuery( ".scroll-content" );
		
		//build slider
		var scrollbar = jQuery( ".scroll-bar" ).slider({
			slide: function( event, ui ) {
				if ( scrollContent.width() > scrollPane.width() ) {
					scrollContent.css( "margin-left", Math.round(
						ui.value / 100 * ( scrollPane.width() - scrollContent.width() )
					) + "px" );
				} else {
					scrollContent.css( "margin-left", 0 );
				}
			}
		});
		
		//append icon to handle
		var handleHelper = scrollbar.find( ".ui-slider-handle" )
		.mousedown(function() {
			scrollbar.width( handleHelper.width() );
		})
		.mouseup(function() {
			scrollbar.width( "100%" );
		}).append("<span class='ui-icon ui-icon-grip-dotted-vertical'></span>").wrap("<div class='ui-handle-helper-parent'></div>").parent();
		
		//change overflow to hidden now that slider handles the scrolling
		scrollPane.css( "overflow", "hidden" );
		
		//size scrollbar and handle proportionally to scroll distance
		function sizeScrollbar() {
			var remainder = scrollContent.width() - scrollPane.width();
			var proportion = remainder / scrollContent.width();
			var handleSize = scrollPane.width() - ( proportion * scrollPane.width() );
			scrollbar.find( ".ui-slider-handle" ).css({
				width: handleSize,
				"margin-left": -handleSize / 2
			});
			handleHelper.width( "" ).width( scrollbar.width() - handleSize );
		}
		
		//reset slider value based on scroll content position
		function resetValue() {
			var remainder = scrollPane.width() - scrollContent.width();
			var leftVal = scrollContent.css( "margin-left" ) === "auto" ? 0 :
				parseInt( scrollContent.css( "margin-left" ) );
			var percentage = Math.round( leftVal / remainder * 100 );
			scrollbar.slider( "value", percentage );
		}
		
		//if the slider is 100% and window gets larger, reveal content
		function reflowContent() {
				var showing = scrollContent.width() + parseInt( scrollContent.css( "margin-left" ), 10 );
				var gap = scrollPane.width() - showing;
				if ( gap > 0 ) {
					scrollContent.css( "margin-left", parseInt( scrollContent.css( "margin-left" ), 10 ) + gap );
				}
		}
		
		//change handle position on window resize
		jQuery( window ).resize(function() {
			resetValue();
			sizeScrollbar();
			reflowContent();
		});
		//init scrollbar size
		setTimeout( sizeScrollbar, 10 );//safari wants a timeout
		
		jQuery("#playlist, #vidlist").sortable({
			start: function (e, ui) { 
				// modify ui.placeholder however you like
				ui.placeholder.html("<img src='$pluginurl/images/x7placeholder.png' />");
				},
			connectWith: '.connectedSortable',
			forcePlaceholderSize: 'true',
			revert: 'true',
			tolerance: 'pointer',
			placeholder: 'ui-state-highlight'
		}).disableSelection();

		jQuery("#listname").val("Playlist Name Here");
		
		jQuery("div.scroll-content-item>img[title]").tooltip({
					position: 'bottom center',
					effect: 'slide'
				});
				
	    });//end document ready
		
		function x7ListPreview()
		{
			var valError = "noerror";
			arrEids = []; //clear out the eids array
			var listname;
			jQuery("#playlist div").each(
				function( intindex ){
					arrEids[intindex] = jQuery( this ).attr("eid");
				});
			if (arrEids.length < 2)
			{
				valError = "error";
				alert("New playlists must contain at least two videos!");
			}
			listname = jQuery("#listname").val(); //get entered listname text
			if (listname.length < 3)
			{
				valError = "error";
				alert("Playlist name must contain at least three characters.");
			}
			if (valError != "error")
			{
				jQuery('#x7loading').html('<img border="0" src="$pluginurl/images/x7loader.gif">');
				jQuery.post(
					"$pluginurl/x7listadd.php",
					{'x7server': "$x7server", 'x7kalpartnerid': "$x7kalpartnerid", 'ks': "$ks", 'eids[]': arrEids, 'listname': listname, 'ul': "$user_login", 'x7bloghome': "$x7bloghome"},
					function ( data ){
						jQuery("#x7loading").html('');
						if (data != "error"){
							var theUrl;
							theUrl = "$pluginurl/x7listplayer.php";
							Shadowbox.open({
							content:    theUrl + "?listid=" + data + "&x7kalpartnerid=$x7kalpartnerid&x7serverget=$x7serverget&x7pluiconfid=$x7pluiconfid&width=$player_width&height=$player_height",
							player:     "iframe",
							height:     "$player_height",
							width:      "$player_width"
							});
						} else {
							alert("Error creating playlist.");
						}; //end if not server data returned error
					}); //end post
	    };//end if not valerror error
		}//end x7listpreview
		
		</script>
		<h3>Media List - Drag Entries Below To Create New Playlist</h3>
		<div class="scroll-pane ui-widget ui-widget-header ui-corner-all">
			<ul id="vidlist" class="scroll-content connectedSortable">
INFOBOX;
				$itemcount = "0";
				foreach ($list->objects as $mediaEntry) {
					$itemcount++;
					$name     = $mediaEntry->name; // get the entry name
					$id       = $mediaEntry->id;
					$thumbUrl = $mediaEntry->thumbnailUrl;  // get the entry thumbnail URL
					$submitter = $mediaEntry->userId;
					$description = $mediaEntry->description;
					$description = str_replace("'", "", "$description"); 
					$return .= "<div eid='$id' class='scroll-content-item ui-widget-header'><img title='<strong>Name</strong>: $name<br /><strong>Author</strong>: $submitter' height='90' width='120' src='$thumbUrl' /></div>";
				}
		
		$return .= <<<INFOBOX3
			</ul>
		<div class="scroll-bar-wrap ui-widget-content ui-corner-bottom">
		<div class="scroll-bar"></div>
		</div>
		</div>
			<h3>New Playlist</h3>
			<a onclick="x7ListPreview()">[Save and Preview]</a><br />
			<textarea id="listname"></textarea>
			<ul id="playlist" class="connectedSortable">
			</ul>
INFOBOX3;

	} // end if widget is makeplaylist
/***********************************************************************************************************************
 * VIEW USER PLAYLISTS WIDGET SHORTCODE *
 * ********************************************************************************************************************/
if ($widget=="userplaylists"){
	
		//Start Kaltura admin session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getAdminSession("","$user_login");
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));
		$ksget = urlencode($ks);
		
		$player = $kmodel->getPlayerUiConf($x7pluiconfid);
	
		//player vars
		$player_width = $player->width;
		$player_height = $player->height;
		
		//SET RPCURL XMLRPC FILE VALUE
		$x7rpcurl = $x7bloghome . "/xmlrpc.php";
		$x7fullplugurl = plugins_url('/ixr.php', __FILE__);
		$playurl = plugins_url('x7vidplayer.php', __FILE__);
                $editurl = plugins_url('x7advancededitor.php', __FILE__);
		//GET CATEGORIES LIST
		$categories = get_categories('hide_empty=0'); 
			foreach ($categories as $cat) {
				$option .= "<option value=\"$cat->cat_name\">$cat->cat_name</option>";
			}
		
		//add javascript and styles
		$return .= <<<USERPLJS
		<script type="text/javascript">
		//Function that retrieves URL get variables
			function getUrlVars()
			{
				var vars = [], hash;
				var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
				for(var i = 0; i < hashes.length; i++)
				    {
				        hash = hashes[i].split('=');
				        vars.push(hash[0]);
				        vars[hash[0]] = hash[1];
				    }
				return vars;
			}
			var map = getUrlVars();
			//Shows the user success or failure feedback
			function addSuccessDiv() {
			if (map.result=="success"){
				alert("Success! Your post has now been queued for moderation by an administrator.");
			}
			if (map.result=="fail") {
				alert("Post failed! Please try again.");
			}
			} //end addsuccessdiv
			
			function formValidate()
			{
				var title = new LiveValidation('title', {onlyOnSubmit: true });
				title.add( Validate.Presence );
				var keywords = new LiveValidation('keywords', {onlyOnSubmit: true });
				//Pattern matches for comma delimited string
				keywords.add( Validate.Format, { pattern: /([^\"]+?)\",?|([^,]+),?|,/ } );
				var password = new LiveValidation('password', {onlyOnSubmit: true });
				password.add( Validate.Presence );
			} //end validate
			
		jQuery(document).ready(function() {
			addSuccessDiv();

			jQuery("td.tt[title]").tooltip({
				effect: 'slide',
				position: 'bottom center'
			});
				
			x7table = jQuery("#x7entriestable").dataTable({
				"bJQueryUI": true,
				"bPaginate": true,
				"bProcessing": true,
				"bSort": true,
				"sScrollY": "300px",
				"sScrollX": "100%",
				"iDisplayLength": 10,
				"sPaginationType": "full_numbers"
			});
			
			jQuery("#x7entriestable tbody tr").live('click', function() {
				var eid = jQuery(this).attr("title");
				var name = jQuery(this).attr("name");
				var type = jQuery(this).attr("edtype");
				x7ListPost(eid, name);
			});
			
			//set up the tabs with the post tab disabled by fault and enabled with a check for x7allowposts
			jQuery( "#x7form" ).tabs({ disabled: [1] });
			
			//set up all of the buttons with icons
			jQuery( "button#x7aplaychange" ).button({
				icons: { primary: "ui-icon-play" }
				});
			jQuery( "button#x7aeditchange" ).button({
				icons: { primary: "ui-icon-scissors" }
				});
			jQuery( "button#x7adelchange" ).button({
				icons: { primary: "ui-icon-trash" }
				});
			jQuery( "button.x7cancel" ).button({
				icons: { primary: "ui-icon-cancel" }
				});
				
			}); //end document ready
			var postout = 'false';
		function x7ListPost(eid, name)
		{
			if (postout == 'false'){
					formValidate();
					var thumburl = '$pluginurl/images/playlist.png';
					var embedcode = '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="$player_height" width="$player_width" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="$x7server/index.php/kwidget/cache_st/1293071951/wid/_$x7kalpartnerid/uiconf_id/$x7pluiconfid" data="$x7server/index.php/kwidget/cache_st/1293071951/wid/_$x7kalpartnerid/uiconf_id/$x7pluiconfid"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="playlistAPI.autoInsert=true&playlistAPI.kpl0Name='+eid+'&playlistAPI.kpl0Url=$x7serverget%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26partner_id%3D$x7kalpartnerid%26subp_id%3D$x7kalsubpartnerid%26format%3D8%26ks%3D%7Bks%7D%26playlist_id%3D'+eid+'&" /><param name="movie" value="$x7server/index.php/kwidget/cache_st/1293071951/wid/_$x7kalpartnerid/uiconf_id/$x7pluiconfid" /></object>';
					
					jQuery('button#x7aplaychange').attr("title",eid);
					jQuery('button#x7aeditchange').attr("title",eid);
					jQuery('button#x7aeditchange').attr("name",name);
					jQuery('button#x7adelchange').attr("title",eid);
					jQuery('textarea#x7embedchange').val(embedcode);
					jQuery(':input#x7hiddeneidchange').val(eid);
					jQuery('img#x7imgchange').attr("src",thumburl);
					
					Shadowbox.init();
					jQuery('div#x7tablewrap').hide('slow');
					jQuery('div#x7form').show('slow');
					var allowpost = '$x7allowposts';
					if (allowpost=='1'){
						//jQuery('div#x7postform').show('slow');
						jQuery( "#x7form" ).tabs("option","disabled",[]);
					}
					postout = 'true';
				} else if(postout == 'true')
				{
					jQuery('div#x7form').hide('slow');
					jQuery('div#x7tablewrap').show('slow');
					postout = 'false';
				}
		}
		
		function x7FormClose()
		{
			jQuery("#x7form").hide('slow');
			jQuery('#x7tablewrap').show('slow');
		}
	  
		function x7VidDelete(delid)
			{
				if (confirm("Are you sure you want to delete playlist ID: " + delid))
				{ 
				    jQuery.post(
				       "$pluginurl/x7delete.php",
				       {'x7bloghome': '$x7bloghome', 'ks': "$ks", 'x7entrytype': 'playlist', 'eid': delid, 'x7server': "$x7server"},
				       function ( response ){
					      jQuery("div#"+delid).remove();
						alert("Playlist ID: "+delid+" successfully deleted.");
				       });//end post
				} //end confirm
			} //end x7VidDelete
			
			function x7VidPlay()
			{
				var eid = jQuery("button#x7aplaychange").attr("title");
				var theUrl;
				theUrl = "$pluginurl/x7listplayer.php";
			    Shadowbox.open({
			    content:    theUrl + "?listid=" + eid + "&x7kalpartnerid=$x7kalpartnerid&x7serverget=$x7serverget&x7pluiconfid=$x7pluiconfid&height=$player_height&width=$player_width",
			    player:     "iframe",
			    height:     "$player_height",
			    width:      "$player_width"
			    });
			}
			
			function x7VidEdit(eid, name)
			{
				var eid = jQuery("button#x7aeditchange").attr("title");
				var name = jQuery("button#x7aeditchange").attr("name");
				var theUrl = "$pluginurl/x7pledit.php";
				Shadowbox.open({
					content: theUrl + "?ks=$ksget&userlogin=$user_login&x7bloghomeget=$x7bloghomeget&x7server=$x7serverget&x7kalpartnerid=$x7kalpartnerid&pluginurl=$pluginurlget&eid="+eid+"&listname="+name,
					player: "iframe",
					height: 800,
					width: 800
				});
			}//end x7videdit
			</script>
USERPLJS;

		//ADD X7LOADING DIV
		$return .= "<div id='x7loading' style='display:none'><img border='0' src='$pluginurl/images/x7loader.gif'></div><br /><br />";
		
		//Embed user uploads
		$xmlresult = rest_helper("$x7server/api_v3/?service=playlist&action=list",
					 array(
						'ks' => $ks,
						'filter:userIdEqual' => $user_login,
						'filter:orderBy' => '-createdAt'
					 ), 'POST'
					 );
						
			//ADD post form
			$return .= <<<X7POSTFORM
			<div class="ui-corner-all" style="display:none" id="x7form">
				<ul>
					<li><a href="#x7form-1">Playlist Admin</a></li>
					<li><a href="#x7form-2">Submit for Post</a></li>
					<li><a href="#x7form-3">Embed Code</a></li>
				</ul>
				<div id="x7form-1">
				<img id="x7imgchange" src=""><br />
				<button onClick="x7VidPlay()" id="x7aplaychange" title="">[PLAY]</button><br />
				<button id="x7aeditchange" name="" title="" onClick="x7VidEdit()">[EDIT]</button><br />
				<button id="x7adelchange" title="" onClick="x7VidDelete()">[DELETE]</button><br />
				<button class="x7cancel" onClick="x7FormClose();">[CANCEL]</button>
				</div> <!-- end x7form-1 -->
				<div id="x7form-2">
					<div id="x7postform">
					<form name="x7postdraft" id="x7postdraft" action="$pluginurl/x7plpost.php" method="post">
						<input type="hidden" name="x7server" id="x7server" value="$x7server" >
						<input type="hidden" name="x7kalpartnerid" id="x7kalpartnerid" value="$x7kalpartnerid" >
						<input type="hidden" name="x7uiconfid" id="x7uiconfid" value="$x7uiconfid" >
						<input type="hidden" name="eid" id="x7hiddeneidchange" value="" >
						<input type="hidden" name="rpcurl" id="rpcurl" value="$x7rpcurl" >
						<input type="hidden" name="username" id="username" value="$user_login" >
						<input type="hidden" name="x7fullplugurl" id="x7fullplugurl" value="$x7fullplugurl" >
						<input type="hidden" name="x7bloghome" id="x7bloghome" value="$x7bloghome" >
						<label for="title">Title of Post:</label><br />
						<input type="text" size="25" name="title" id="title" value="" class="" ><br />
						<label for="category">Category(ies):</label><br />
						<select name="category[]" id="category" multiple="multiple" class="">
							$option
						</select><br />
						<label for="description">Description:</label><br />
						<textarea cols="35" rows="4" name="description" id="description" class="" />Another new playlist from $user_login!</textarea><br />
						<label for="keywords">Tags (comma delimited):</label><br />
						<input type="text" size="25" name="keywords" id="keywords" value="" class="" ><br />
						<label for="password">Wordpress Password:</label><br />
						<input type="password" name="password" id="password" size="20" ><br />
						<input type="submit" value="[POST]" name="submit" id="submit" ></form>
					</div> <!--end x7postform -->
				</div> <!--end x7form-2 -->
				<div id="x7form-3">
					<strong>Embed code:</strong><br><textarea id="x7embedchange" cols="40" rows="10"></textarea>
				</div> <!--end x7form-3 -->
			</div> <!--end x7form -->
X7POSTFORM;

$return .= "<div id='x7tablewrap'><table id='x7entriestable'><thead><tr><th>Name</th><th>ID</th><th>Description</th><th>When Created</th></tr></thead><tbody>";
			
		foreach ($xmlresult->result->objects->item as $plentry) {
			$eid = $plentry->id;
			$thumb = $plentry->thumbnailUrl;
			$userId = $plentry->userId;
			$name = $plentry->name;
			$description = $plentry->description;
			$createdat = (string) $plentry->createdAt;
			$createdat = date(DATE_RSS, $createdat);
				$return .= <<<ENTRY_DIV
				<tr title="$eid" name="$name">
					<td class="tt" title="Click to open administration menu">$name</td>
					<td>$eid</td>
					<td>$description</td>
					<td>$createdat</td>
				</tr>
ENTRY_DIV;
		} //end foreach
		//End x7entries table
		$return .= "</tbody></table></div>";
	
	}//end if widget is user playlists
	
} else { //not logged in
	$return = "Sorry, but you must be a logged in registered user for access.";
} //end logged in check
	return "$return";
} //end x7video shortcode

function kaltura_shortcode($attrs) 
{
	// for wordpress 2.5, in wordpress 2.6+ shortcodes are striped in rss feedds
	if (is_feed())
		return "";

	// prevent xss
	foreach($attrs as $key => $value)
	{
		$attrs[$key] = js_escape($value);
	}
	
	// get the embed options from the attributes
	$embedOptions = _kaltura_get_embed_options($attrs);

	$isComment		= (@$attrs["size"] == "comments") ? true : false;
	$wid 			= $embedOptions["wid"];
	$entryId 		= $embedOptions["entryId"];
	$width 			= $embedOptions["width"];
	$height 		= $embedOptions["height"];
	$randId 		= md5($wid . $entryId . rand(0, time()));
	$divId 			= "kaltura_wrapper_" . $randId;
	$thumbnailDivId = "kaltura_thumbnail_" . $randId;
	$playerId 		= "kaltura_player_" . $randId;

	$link = '';
	
	$powerdByBox ='';
	
	if ($isComment)
	{
		$thumbnailPlaceHolderUrl = KalturaHelpers::getCommentPlaceholderThumbnailUrl($wid, $entryId, 240, 180, null);

		$embedOptions["flashVars"] .= "&autoPlay=true";
		$html = '
				<!-- <div id="' . $thumbnailDivId . '" style="width:'.$width.'px;height:'.$height.'px;" class="kalturaHand" onclick="Kaltura.activatePlayer(\''.$thumbnailDivId.'\',\''.$divId.'\');">
					<img src="' . $thumbnailPlaceHolderUrl . '" style="" />
				</div> -->
				<div id="' . $divId . '" style="display:none;height: '.$height.'px;"">
					<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="' . $embedOptions["height"] . '" width="' . $embedOptions["width"] . '" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="' . $embedOptions["swfUrl"] . '" data="' . $embedOptions["swfUrl"] . '">
					<param name="allowFullScreen" value="true" />
					<param name="allowNetworking" value="all" />
					<param name="allowScriptAccess" value="always" />
					<param name="bgcolor" value="#000000" />
					<param name="flashVars" value="&" />
					<param name="movie" value="' . $embedOptions["swfUrl"] . '" />
					<a rel="media:thumbnail" href="' . $thumbnailPlaceHolderUrl . '" />
					<span property="dc:description" content="KalturaCE Media Entry" />
					<span property="media:title" content="KalturaCE Media" />
					<span property="media:width" content="' . $embedOptions["width"] . '" />
					<span property="media:height" content="' . $embedOptions["height"] . '" />
					<span property="media:type" content="application/x-shockwave-flash" />
					</object>
				</div>
		';
	}
	else
	{
		$style = '';
		$style .= 'width:' . $embedOptions["width"] .'px;';
		$style .= 'height:' . $embedOptions["height"] . 'px;';
		if (@$embedOptions["align"])
			$style .= 'float:' . $embedOptions["align"] . ';';
			
		// append the manual style properties
		if (@$embedOptions["style"])
			$style .= $embedOptions["style"];
			
		$html = '
				<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="' . $embedOptions["height"] . '" width="' . $embedOptions["width"] . '" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="' . $embedOptions["swfUrl"] . '" data="' . $embedOptions["swfUrl"] . '">
				<param name="allowFullScreen" value="true" />
				<param name="allowNetworking" value="all" />
				<param name="allowScriptAccess" value="always" />
				<param name="bgcolor" value="#000000" />
				<param name="flashVars" value="&" />
				<param name="movie" value="' . $embedOptions["swfUrl"] . '" />
				<a rel="media:thumbnail" href="' . $embedOptions["thumbUrl"] . '" />
				<span property="dc:description" content="KalturaCE Media Entry" />
				<span property="media:title" content="KalturaCE Media" />
				<span property="media:width" content="' . $embedOptions["width"] . '" />
				<span property="media:height" content="' . $embedOptions["height"] . '" />
				<span property="media:type" content="application/x-shockwave-flash" />
				</object>
				';
	}
		
	return $html;
}
function kaltura_get_version() 
{
	$plugin_data = implode( '', file( str_replace('all_in_one_video_pack.php', 'interactive_video.php', __FILE__)));
	if ( preg_match( "|Version:(.*)|i", $plugin_data, $version ))
		$version = trim( $version[1] );
	else
		$version = '';
	
	return $version;
}

function _kaltura_get_embed_options($params) 
{
	if (@$params["size"] == "comments") // comments player
	{
		if (get_option('kaltura_comments_player_type'))
			$type = get_option('kaltura_comments_player_type');
		else
			$type = get_option('kaltura_default_player_type'); 
			
		// backward compatibility
		if ($type == "whiteblue")
			$params["uiconfid"] = 530;
		elseif ($type == "dark")
			$params["uiconfid"] = 531;
		elseif ($type == "grey")
			$params["uiconfid"] = 532;
		elseif ($type)
			$params["uiconfid"] = $type;
		else 
		{
			global $KALTURA_DEFAULT_PLAYERS;
			$params["uiconfid"] = $KALTURA_DEFAULT_PLAYERS[0]["id"];
		}
			
		$params["width"] = 250;
		$params["height"] = 244;
		$layoutId = "tinyPlayer";
	}
	else 
	{ 
		// backward compatibility
		switch($params["size"])
		{
			case "large":
				$params["width"] = 410;
				$params["height"] = 364;
				break;
			case "small":
				$params["width"] = 250;
				$params["height"] = 244;
				break;
		}
		
		// if width is missing set some default
		if (!@$params["width"]) 
			$params["width"] = 400;

		// if height is missing, recalculate it
		if (!@$params["height"])
		{
			require_once("lib/kaltura_model.php");
			$params["height"] = KalturaHelpers::calculatePlayerHeight(get_option('kaltura_default_player_type'), $params["width"]);
		}
			
		// check the permissions
		$kdp3LayoutFlashVars = "";
		$externalInterfaceDisabled = null;
		if (KalturaHelpers::userCanEdit(@$params["editpermission"]))
		{
			$layoutId = "full";
			$externalInterfaceDisabled = false;
			$kdp3LayoutFlashVars .= _kdp3_upload_layout_flashvars(true);
			$kdp3LayoutFlashVars .= "&";
			$kdp3LayoutFlashVars .= _kdp3_edit_layout_flashvars(true);
		}
		else if (KalturaHelpers::userCanAdd(@$params["addpermission"]))
		{
			$layoutId = "addOnly";
			$externalInterfaceDisabled = false;
			$kdp3LayoutFlashVars .= _kdp3_upload_layout_flashvars(true);
			$kdp3LayoutFlashVars .= "&";
			$kdp3LayoutFlashVars .= _kdp3_edit_layout_flashvars(false);
		}
		else
		{ 
			$layoutId = "playerOnly";
			$kdp3LayoutFlashVars .= _kdp3_upload_layout_flashvars(false);
			$kdp3LayoutFlashVars .= "&";
			$kdp3LayoutFlashVars .= _kdp3_edit_layout_flashvars(false);
		}
			
		if ($params["size"] == "large_wide_screen")  // FIXME: temp hack
			$layoutId .= "&wideScreen=1";
	}
	
	// align
	switch ($params["align"])
	{
		case "r":
		case "right":
			$align = "right";
			break;
		case "m": 
		case "center":
			$align = "center";
			break;
		case "l":
		case "left":
			$align = "left";
			break;
		default:
			$align = "";			
	}
		
	if ($_SERVER["SERVER_PORT"] == 443)
		$protocol = "https://";
	else
		$protocol = "http://";
		 
	$postUrl = $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

	$flashVarsStr = "";
	$flashVarsStr .=  "layoutId=" . $layoutId;
	$flashVarsStr .= ("&" . $kdp3LayoutFlashVars);
	if ($externalInterfaceDisabled === false)
		$flashVarsStr .= "&externalInterfaceDisabled=false";

	
	$wid = $params["wid"];
	$swfUrl = KalturaHelpers::getSwfUrlForWidget($wid);

	if (isset($params["uiconfid"]))
		$swfUrl .= "/uiconf_id/".$params["uiconfid"];
		
	$entryId = null;
	if (isset($params["entryid"]))
	{
		$entryId = $params["entryid"];
		$swfUrl .= "/entry_id/".$entryId;
	}
	
	$thumbUrl = KalturaHelpers::getThumbnailUrl(null,$entryId,"400","330");
	
	return array(
		"flashVars" => $flashVarsStr,
		"height" => $params["height"],
		"width" => $params["width"],
		"align" => $align,
		"style" => @$params["style"],
		"wid" => $wid,
		"entryId" => $entryId,
		"swfUrl" => $swfUrl,
		"thumbUrl" => $thumbUrl
	);
}

function _kaltura_find_post_widgets($args) 
{
	$wid = isset($args["wid"]) ? $args["wid"] : null;
	$entryId = isset($args["entryid"]) ? $args["entryid"] : null;
	if (!$wid && !$entryId)
		return;
		
	global $kaltura_post_id;
	global $kaltura_widgets_in_post;
	$kaltura_widgets_in_post[] = array($wid, $entryId); // later will use it to delete the widgets that are not in the post 
	
	$widget = array();
	$widget["id"] = $wid;
	$widget["entry_id"] = $entryId;
	$widget["type"] = KALTURA_WIDGET_TYPE_POST;
	$widget["add_permissions"] = $args["addpermission"];
	$widget["edit_permissions"] = $args["editpermission"];
	$widget["post_id"] = $kaltura_post_id;
	$widget["status"] = KALTURA_WIDGET_STATUS_PUBLISHED;
	$widget = KalturaWPModel::insertOrUpdateWidget($widget);
}

function _kaltura_find_comment_widgets($args)
{
	$wid = isset($args["wid"]) ? $args["wid"] : null;
	$entryId = isset($args["entryid"]) ? $args["entryid"] : null;
	if (!$wid && !$entryId)
		return;
		
	if (!$wid)
		$wid = "_" . get_option("kaltura_partner_id");
		
	global $kaltura_comment_id;
	$comment = get_comment($kaltura_comment_id);
	
	// add new widget
	$widget = array();
	$widget["id"] = $wid;
	$widget["entry_id"] = $entryId;
	$widget["type"] = KALTURA_WIDGET_TYPE_COMMENT;
	$widget["post_id"] = $comment->comment_post_ID;
	$widget["comment_id"] = $kaltura_comment_id;
	$widget["status"] = KALTURA_WIDGET_STATUS_PUBLISHED;
	
	$widget = KalturaWPModel::insertOrUpdateWidget($widget);
}

function _kdp3_edit_layout_flashvars($enabled) {
	$enabled = ($enabled) ? 'true' : 'false';
	$params = array(
		"editBtnControllerScreen.includeInLayout" => $enabled,
		"editBtnControllerScreen.visible" => $enabled,
		"editBtnStartScreen.includeInLayout" => $enabled,
		"editBtnStartScreen.visible" => $enabled,
		"editBtnPauseScreen.includeInLayout" => $enabled,
		"editBtnPauseScreen.visible" => $enabled,
		"editBtnPlayScreen.includeInLayout" => $enabled,
		"editBtnPlayScreen.visible" => $enabled,
		"editBtnEndScreen.includeInLayout" => $enabled,
		"editBtnEndScreen.visible" => $enabled,
	);
	return http_build_query($params);
}

function _kdp3_upload_layout_flashvars($enabled) {
	$enabled = ($enabled) ? 'true' : 'false';
	$params = array(
		"uploadBtnControllerScreen.includeInLayout" => $enabled,
		"uploadBtnControllerScreen.visible" => $enabled,
		"uploadBtnStartScreen.includeInLayout" => $enabled,
		"uploadBtnStartScreen.visible" => $enabled,
		"uploadBtnPauseScreen.includeInLayout" => $enabled,
		"uploadBtnPauseScreen.visible" => $enabled,
		"uploadBtnPlayScreen.includeInLayout" => $enabled,
		"uploadBtnPlayScreen.visible" => $enabled,
		"uploadBtnEndScreen.includeInLayout" => $enabled,
		"uploadBtnEndScreen.visible" => $enabled,
	);
	return http_build_query($params);
}
		
if ( !get_option('kaltura_partner_id') && !isset($_POST['submit']) && !strpos($_SERVER["REQUEST_URI"], "page=interactive_video")) {
	function kaltura_warning() {
		echo "
		<div class='updated fade'><p><strong>".__('To complete the x7Host Videox7 UGC Plugin installation, <a href="'.get_settings('siteurl').'/wp-admin/options-general.php?page=interactive_video">you must set your Partner ID.</a>')."</strong></p></div>
		";
	}
	add_action('admin_notices', 'kaltura_warning');
}
?>