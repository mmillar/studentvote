<?php
/* 
Plugin Name: S3 Video Plugin
Plugin URI: https://github.com/anthony-mills/S3-Video
Description: Upload and embed videos using your Amazon S3 account
Version: 0.7
Author: Anthony Mills
Author URI: http://www.development-cycle.com
*/

if ('s3-video.php' == basename($_SERVER['SCRIPT_FILENAME'])){
	die ('Access denied');
}

register_activation_hook(__FILE__, 'S3_plugin_activate');
register_deactivation_hook(__FILE__, 'S3_plugin_deactivate');

add_action('admin_menu', 's3_video_plugin_menu');
add_action('admin_print_styles', 's3_video_load_css');
add_action('admin_print_scripts', 's3_video_load_js');
add_action('wp_ajax_my_action', 'my_action_callback');

wp_enqueue_script('jquery');
wp_enqueue_script('swfobject');
wp_enqueue_script('flowPlayer', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/flowplayer-3.2.6.js', array('jquery'), '1.0');
wp_enqueue_script('flowPlayerPlaylist', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.playlist.js', array('jquery'), '1.0');
require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/shared.php');
require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/s3.php');

// Add shortcodes
add_shortcode( 'S3_embed_video', 's3_video_embed_video' );
add_shortcode( 'S3_embed_playlist', 's3_video_embed_playlist' );

// Add deactivation hook
register_deactivation_hook( __FILE__, 's3_video_deactivate');
		
function s3_video_plugin_menu() 
{
	// Main side bar entry
	add_menu_page('S3 Video', 'S3 Video', 'manage_options', 's3-video', 's3_video');

	// S3 sidebar child pages
	add_submenu_page('s3-video', __('Upload Video','upload-video'), __('Upload Video','upload-video'), 'manage_options', 's3_video_upload_video', 's3_video_upload_video');		
	add_submenu_page('s3-video', __('Playlist Management','show-playlists'), __('Playlist Management','show_playlists'), 'manage_options', 's3_video_show_playlist', 's3_video_show_playlists');
	add_submenu_page('s3-video', __('Create Playlist','create-playlist'), __('Create Playlist','create_playlist'), 'manage_options', 's3_video_create_playlist', 's3_video_create_playlist');
	add_submenu_page('s3-video', __('Plugin Settings','plugin-settings'), __('Plugin Settings','plugin-settings'), 'manage_options', 's3_video_plugin_settings', 's3_video_plugin_settings');  			
}

/*
 *  Default plugin page displaying existing media files
 */
function s3_video()
{
	s3_video_check_user_access();
	$pluginSettings = s3_video_check_plugin_settings();
	
	if (!empty($_GET['delete'])) {
		$s3Access = new S3($pluginSettings['amazon_access_key'], $pluginSettings['amazon_secret_access_key'], NULL, $pluginSettings['amazon_url']);
		$result = $s3Access->deleteObject($pluginSettings['amazon_video_bucket'], $_GET['delete']);
		if ($result) {
			$successMsg = $_GET['delete'] . ' was successfully deleted.';
		}
	}
	$existingVideos= s3_video_get_all_existing_video($pluginSettings);
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/video-management/existing-videos.php');
}

/*
 * Upload videos to S3 bucket
 */
function s3_video_upload_video()
{
	s3_video_check_user_access(); 
	$pluginSettings = s3_video_check_plugin_settings();
	$tmpDirectory = s3_video_check_upload_directory();
	if ((!empty($_FILES)) && ($_FILES['upload_video']['size'] > 0)) {
			if (($_FILES['upload_video']['type'] !='video/x-flv') && ($_FILES['upload_video']['type'] !='video/mp4') && ($_FILES['upload_video']['type'] !='application/octet-stream')) {
				$errorMsg = 'You need to provide an .flv or .mp4 file';
			} else {
				$fileName = basename($_FILES['upload_video']['name']);
				$fileName = preg_replace('/[^A-Za-z0-9_.]+/', '', $fileName);
				$videoLocation = $tmpDirectory . $fileName;
				if(move_uploaded_file($_FILES['upload_video']['tmp_name'], $videoLocation)) {
					$s3Access = new S3($pluginSettings['amazon_access_key'], $pluginSettings['amazon_secret_access_key'], NULL, $pluginSettings['amazon_url']);
					$s3Result = $s3Access->putObjectFile($videoLocation, $pluginSettings['amazon_video_bucket'], $fileName, S3::ACL_PUBLIC_READ);
					switch ($s3Result) {
		
						case 0:
							$errorMsg = 'Request unsucessful check your S3 access credentials';
						break;	
		
						case 1:
							$successMsg = 'The video has successfully been uploaded to your S3 account';					
						break;
						
					}
				} else {
            $errorMsg = 'Unable to move file to ' . $videoLocation . ' check the permissions and try again.';
        }
			}
	} else {
    	$errorMsg = 'There was an error uploading the video';
	}
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/video-management/upload-video.php');
}

/*
 * Page to configure plugin settings i.e Amazon access keys etc
 */
function s3_video_plugin_settings()
{
	if (!empty($_POST)) {
		if ((!empty($_POST['amazon_access_key'])) && (!empty($_POST['amazon_secret_access_key'])) && (!empty($_POST['amazon_video_bucket']))) {
			register_setting( 'amazon-s3-video', 'amazon_access_key' );
			register_setting( 'amazon-s3-video', 'amazon_secret_access_key' );
			register_setting( 'amazon-s3-video', 'amazon_video_bucket' );
			register_setting( 'amazon-s3-video', 'amazon_url' );
			register_setting( 's3-video-results-limit', 's3_video_page_result_limit' );

			update_option( 'amazon_access_key', $_POST['amazon_access_key']);
			update_option( 'amazon_secret_access_key', $_POST['amazon_secret_access_key'] );
			update_option( 'amazon_video_bucket', $_POST['amazon_video_bucket'] );
			
			if (!empty($_POST['amazon_url'])) {
				update_option( 'amazon_url', $_POST['amazon_url']);
			} else {
				update_option( 'amazon_url', 's3.amazonaws.com');
			}
			
			if (!empty($_POST['page_result_limit'])) {
				update_option( 's3_video_page_result_limit', $_POST['page_result_limit']);
			} else {
				update_option( 's3_video_page_result_limit', 15);
			}
			
			$successMsg = 'Plugin settings saved successfully.';
			$pluginSettings = s3_video_check_plugin_settings();
		}
	} else {
		$pluginSettings = s3_video_check_plugin_settings(FALSE);
	}

	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/settings/plugin-settings.php');
}

/*
 * Create a new playlist and add videos
 */
function s3_video_create_playlist()
{
	$pluginSettings = s3_video_check_plugin_settings();	
		
	if ((!empty($_POST['playlist_contents'])) && (!empty($_POST['playlist_name']))) {
		require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/playlist_management.php');
		$playlistManagement = new s3_playlist_management();
		
		$playlistName = sanitize_title($_POST['playlist_name']);
		$playlistExists = $playlistManagement->getPlaylistsByTitle($playlistName);
		if (!$playlistExists) {
			$playlistResult = $playlistManagement->createPlaylist($playlistName, $_POST['playlist_contents']);
			if (!$playlistResult) {
	    		$errorMsg = 'An error occurred whilst creating the play list.';			
			} else {
				$successMsg = 'New playlist saved successfully.';			
			} 
		} else {
	    	$errorMsg = 'A playlist with this name already exists.';					
		}  
	}
	$existingVideos= s3_video_get_all_existing_video($pluginSettings);
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/playlist-management/create-playlist.php');	
}
 
/*
 *	Manage existing playlists of S3 based media 
 */
function s3_video_show_playlists()
{
	$pluginSettings = s3_video_check_plugin_settings();			
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/playlist_management.php');
	$playlistManagement = new s3_playlist_management();
	
	if (!empty($_GET['delete'])) {
		$playlistId = preg_replace('/[^0-9]/Uis', '', $_GET['delete']);
		$playlistManagement->deletePlaylist($playlistId);
	}
	
	if (((!empty($_GET['edit'])) && (is_numeric($_GET['edit']))) || ((!empty($_GET['reorder'])) && (is_numeric($_GET['reorder'])))) {
		
		if (!empty($_GET['edit'])) {
			$playlistId = preg_replace('/[^0-9]/Uis', '', $_GET['edit']);
			
			if (!empty($_POST['playlist_contents'])) {
				$playlistManagement->deletePlaylistVideos($playlistId);
				$playlistManagement->updatePlaylistVideos($playlistId, $_POST['playlist_contents']);	
				$playlistUpdated = 1;
			} 
			$existingVideos = $playlistManagement->getPlaylistVideos($playlistId);	
			$s3Videos = s3_video_get_all_existing_video($pluginSettings);
					
			require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/playlist-management/edit-playlist.php');
		} 
		
		if (!empty($_GET['reorder'])) {
			$playlistId = preg_replace('/[^0-9]/Uis', '', $_GET['reorder']);
			$playlistVideos = $playlistManagement->getPlaylistVideos($playlistId);
			require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/playlist-management/reorder-playlist.php');	
		} 	
		
	} else {
		/*
		 * If we don't have a playlist to display a list of them all  
		 */
		$existingPlaylists = $playlistManagement->getAllPlaylists();	
		require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/playlist-management/playlist-management.php');
	}
	
}
 
/*
 *  Embed video player into page
 */
function s3_video_embed_video($embedDetails) 
{	
	$pluginSettings = s3_video_check_plugin_settings();
	if ($embedDetails['file']) {
		$videoFile =  'http://' . $pluginSettings['amazon_video_bucket']  . '.' .  $pluginSettings['amazon_url'] . '/' . $embedDetails['file'];	
	}
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/video-management/play-video.php');	
} 

/*
 * Embed video player for playlist into page
 */
function s3_video_embed_playlist($embedDetails)
{
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/playlist_management.php');
	$playlistManagement = new s3_playlist_management();
	$playlistVideos = $playlistManagement->getPlaylistVideos($embedDetails['id']);		
	$pluginSettings = s3_video_check_plugin_settings();
	$baseUrl =  'http://' . $pluginSettings['amazon_video_bucket']  . '.' .  $pluginSettings['amazon_url'] . '/';
	
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/video-management/play-playlist.php');	
} 

/*
 *  Preview file in colourBoxadmin.php?page=s3_video_show_playlist
 */
function s3_video_preview_media() 
{
	$pluginSettings = s3_video_check_plugin_settings();
	if ($_GET['media']) {
		$videoFile =  'http://' . $pluginSettings['amazon_url'] . '/' . $pluginSettings['amazon_video_bucket'] . $_GET['media'];	
	}	
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/video-management/preview-video.php');	
} 

/*
 * Check if the user has configured the plugin
 */
function s3_video_check_plugin_settings($redirect = TRUE)
{
	$pluginSettings['amazon_access_key'] = get_option('amazon_access_key');
	$pluginSettings['amazon_secret_access_key'] = get_option('amazon_secret_access_key');
	$pluginSettings['amazon_url'] = get_option('amazon_url');
	$pluginSettings['amazon_video_bucket'] = get_option('amazon_video_bucket');
	$pluginSettings['amazon_video_bucket'] = get_option('amazon_video_bucket');
	$pluginSettings['s3_video_page_result_limit'] = get_option('s3_video_page_result_limit');
		
	if ((empty($pluginSettings['amazon_access_key'])) || (empty($pluginSettings['amazon_secret_access_key'])) || (empty($pluginSettings['amazon_secret_access_key']))) {
		if ($redirect) { 
			require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/views/settings/configuration_required.php');
			exit;
		} else {
			return FALSE;	
		}	
	} else {
		return $pluginSettings;
	}

}

/*
* Check if the user can access the page
*/
function s3_video_check_user_access()
{
	if( !current_user_can( 'manage_options' ) ) {
        	wp_die( 'You do not have sufficient permissions to access this page' );
   	}
}

/*
 * Load the custom style sheets for the admin pages
 */
function s3_video_load_css()
{
	wp_register_style('s3_video_default', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/css/style.css');
	wp_enqueue_style('s3_video_default');
	
	wp_register_style('s3_video_colorbox', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/css/colorbox.css');
	wp_enqueue_style('s3_video_colorbox');	
	
	wp_register_style('multiselect_css', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/css/chosen.css');
	wp_enqueue_style('multiselect_css');			
}

/*
 * Load javascript required by the backend administration pages
 */
function s3_video_load_js()
{	
	wp_enqueue_script('validateJSs', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.validate.js', array('jquery'), '1.0');
	wp_enqueue_script('placeholdersJS', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.placeholders.js', array('jquery'), '1.0');
	wp_enqueue_script('colorBox', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.colorbox.js', array('jquery'), '1.0');
	wp_enqueue_script('tableSorter', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.tablesorter.js', array('jquery'), '1.0');	
	wp_enqueue_script('tablePaginator', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.paginator.js', array('jquery'), '1.0');	
	wp_enqueue_script('multiSelect', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.multiselect.js', array('jquery'), '1.0');		
	wp_enqueue_script('dragDropTable', WP_PLUGIN_URL . '/anthony-mills-S3-Video-621abdc/js/jquery.tablednd.js', array('jquery'), '1.0');			
}

/*
 * Clear the saved plugin settings on deactivation
 */
function s3_video_deactivate()
{
	delete_option('amazon_access_key');
	delete_option('amazon_secret_access_key');	
	delete_option('amazon_video_bucket');	
	delete_option('amazon_url');
	delete_option('s3_video_page_result_limit');			
}

/*
 * Install the required database tables for the plugin on activation 
 */
function s3_plugin_activate()
{
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/plugin_setup.php');
	$pluginSetup = new s3_video_plugin_setup();
	$dbVersion = $pluginSetup->activate_plugin();
	
	if (!empty($dbVersion))  {
		add_option("s3_plugin_db_version", $dbVersion);
	}			
}

/*
 * Deactivate the plugin and remove all associate database tables
 */
function s3_plugin_deactivate()
{
	require_once(WP_PLUGIN_DIR . '/anthony-mills-S3-Video-621abdc/includes/plugin_setup.php');	
	$pluginSetup = new s3_video_plugin_setup();
	$pluginSetup->deactivate_plugin();
}	
