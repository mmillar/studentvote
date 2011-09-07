<?php
/*
Plugin Name: Bubblecast Video for Wordpress
Plugin URI: http://bubble-cast.com/wordpress.html
Description: Bubblecast video plugin brings in video capabilities to your blog. It can upload, record and embed video into your posts in couple clicks
Author: bubble-cast.com
Version: 1.2.2
Author URI: http://bubble-cast.com/
*/

// this is to check whether we have already been plugged in from mu-plugins
if (!function_exists('bubblecast_post')) :

require('config.php');
require_once('bubblecast_utils.php');

define('BUBBLECAST_SITEMAP_REBUILD_PERIOD_SECONDS', 60 * 60);

/**
 * Number of the next video on the current post/page.
 * 
 * @var int
 */
$videoNum = 0;

/**
 * Returns a comma-separated list of categories (or empty string if argument
 * is not an array).
 * 
 * @param mixed $categories		categories
 * @return string comma-separated list of categories
 */
function bubblecast_get_cat_ids_str(&$categories) {
    if (!is_array($categories)) {
        return '';
    }
    return join($categories, ',');
}

/**
 * Returns an absolute path to the Bubblecast logo.
 * 
 * @return string path to logo
 */
function get_bubblecast_logo() {
    return bubblecast_get_plugin_base_dir() . '/i/bubblecast_icon.png';
}

/**
 * Responds to 'media_buttons_context' action. Adds a button to insert a video
 * to a post.
 * 
 * @param string $context	html already generated for the media buttons context
 * @return string updated context
 */
function bubblecast_media_buttons_context($context) {
	global $post_ID, $temp_ID;
    global $user_login, $user_email, $admin_email;
        
    $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
    $url = get_option('siteurl') . "/wp-admin/media-upload.php?type=image&tab=bubblecastvideos&post_id=$uploading_iframe_ID&user_login=$user_login&amp;user_email=$user_email&amp;admin_email=$admin_email";
	$image_btn = get_bubblecast_logo();
	$image_title = __('Bubblecast video', 'bubblecast');
	$out = ' <a href="' . $url . '&amp;TB_iframe=true&amp;height=320&amp;width=400" class="thickbox" title="' . $image_title . '"><img src="' . $image_btn . '" alt="' . $image_title . '" /></a>';
	return $context . $out;
}

/**
 * Processes a post comment to display Bubblecast player in it.
 * 
 * @param string $content	initial content
 * @return string content with player instead of [bubblecast] tags
 */
function bubblecast_comment($content) {
    return embed_quickcast($content);
}

/**
 * Processes a post to display Bubblecast player in it.
 * 
 * @param string $content	initial content
 * @return string content with player instead of [bubblecast] tags
 */
function bubblecast_post($content) {
    return embed_quickcast($content);

}

/**
 * Replaces [bubblecast] tags with player code.
 * 
 * @param string $content	initial content
 * @return string result
 */
function embed_quickcast($content) {
    $q_content = preg_replace_callback(bubble_regexp(), 'bubblecast_handle_tag_params', $content);
    return $q_content;
}

/**
 * Handles tag params when parsing a [bubblecast] tag.
 * 
 * @param array $matches	matches from preg_replace_callback() function
 * @return string result
 */
function bubblecast_handle_tag_params($matches) {
    global $videoNum;
    
    $video_id = $matches[1];
    $thumbnail_dimensions = $matches[3];
    $player_dimensions = $matches[5];

    $default_width = 475;
    $default_height = 375;
    $min_player_width = 475;
    $min_player_height = 375;
    
    $player_width = $default_width;
    $player_height = $default_height;
    $thumbnail_width = $default_width;
    $thumbnail_height = $default_height;
    
    $thumbnail_dimensions_matched = preg_match('/^(\d+)x(\d+)$/', $thumbnail_dimensions, $thumbnail_dimensions_matches);
    $player_dimensions_matched = preg_match('/^(\d+)x(\d+)$/', $player_dimensions, $player_dimensions_matches);
    $params_valid = $thumbnail_dimensions_matched > 0 && $player_dimensions_matched > 0;
    if ($params_valid) {
    	$player_width = $player_dimensions_matches[1];
    	$player_height = $player_dimensions_matches[2];
    	$thumbnail_width = $thumbnail_dimensions_matches[1];
    	$thumbnail_height = $thumbnail_dimensions_matches[2];
    }

    $ep = bubblecast_get_clickable_video_thumbnail_html($video_id, $videoNum,
    		$player_width, $player_height, $thumbnail_width, $thumbnail_height);
    
    $videoNum++;
    return $ep;
}

/**
 * Generates an HTML which presents a thumbnail of a video with a 'Play' button
 * above it which launches video playback.
 * 
 * @param string $video_id					ID of the video
 * @param int $videoNum						index of the video on post/page
 * @param int $player_width					width of the player
 * @param int $player_height				height of the player
 * @param int $thumbnail_width				width of the thumbnail image
 * @param int $thumbnail_height				height of the thumbnail image
 * @param string $additional_onplay_code	additional JS code to be executed
 * 											when 'Play' button is pressed
 * @return string generated HTML
 */
function bubblecast_get_clickable_video_thumbnail_html($video_id, $videoNum,
		$player_width, $player_height, $thumbnail_width, $thumbnail_height,
		$additional_onplay_code='') {
    global $embeddedQuickcastMovieURL, $playerMovieURL, $bubblecastThumbUrl;
    global $current_user;
    
    // getting user info to be sure it's initialized
    get_currentuserinfo();
    
	$is_wide = $player_width > $thumbnail_width || $player_height > $thumbnail_height;
    $is_wide_string = $is_wide ? 'true' : 'false';
    
    $thumbnail_type = ($thumbnail_width > $default_width || $thumbnail_height > $default_height)
    		? 'o' : 'b';
    
    if (!$is_wide) {
    	$player_width = max($player_width, $thumbnail_width);
    	$player_height = max($player_height, $thumbnail_height);
    }
			
    $div_width = $player_width;
    $div_height = $is_wide ? $player_height + 30 : $player_height; // 30 px for Close button
    
    $play_button_width = 135;
    $play_button_height = 135;
    $play_button_left = (int) ((($thumbnail_width - $play_button_width) / 2));
    $play_button_top = (int) (($thumbnail_height - $play_button_height) / 2);

    $bubblecast_player_style = $is_wide ? 'bubblecast_player_wide' : 'bubblecast_player';
    $siteId = get_bubblecast_option('bubblecast_site_id');
    if (!$siteId) {
        $siteId = bubblecast_login();
    }
    $ep =  '<div class="bubblecast_player_wp">';
    $ep .= '<div class="bubblecast_fl_wp"><a href="http://bubble-cast.com" class="bubblecast_site_link" title="Watch demo video before you buy software">http://bubble-cast.com</a></div>';
    if (!$siteId && bubblecast_is_admin()) {
        $ep .= ('<div class="bubblecast_cfg_err_wp">' . __('You haven\'t set up Bubblecast login and password. Please, follow installation instructions to finish setup in your administration console at <b>Site Admin -&gt; Settings -&gt; Bubblecast</b>', 'bubblecast') . ' </div>');
    }
    
    $onclick = 'bubblecastShowPlayer(\''.$video_id.'_'.$videoNum.'\','.$is_wide_string.');';
    $onclick .= $additional_onplay_code;
    $onclick = apply_filters('bubblecast_play_button_onclick', $onclick);
    $onclick .= 'return true;';
    
    $ep .= '<div class="bubblecast_fl_wp_thumb"  id="t'.$video_id.'_'.$videoNum.'"><img src="'.$bubblecastThumbUrl.'?podcastId='.$video_id.'&type=' . $thumbnail_type . '&forceCheckProvider=true" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '"/><a class="bubblecast_play_btn" style="left: ' . $play_button_left . 'px; top: ' . $play_button_top . 'px;" onclick="' . $onclick . '"><img src="'.bubblecast_get_plugin_base_dir().'/i/play.png"  alt="Play"/></a></div>';
    $flash_obj = bubblecast_flash_object($player_width, $player_height, $video_id, $videoNum, $playerMovieURL, $siteId, get_option('bubblecast_language'), $current_user->user_login, $current_user->user_pass);
    $flash_div_open = '<div class="'.$bubblecast_player_style.'" id="p'.$video_id.'_'.$videoNum.'" style="width: ' . $div_width . 'px; height: ' . $div_height . 'px;">';
    if (!$is_wide) {
        $flash_div_close = '</div>';
    } else {
        $flash_div_close = '<div class="bubblecast_ws_close_btn" align="center"><a href="#" onclick="javascript:bubblecastHidePlayer(\''.$video_id.'_'.$videoNum.'\','.$is_wide_string.');return false;">'.__("Close").'</a></div>';
        $flash_div_close .= '</div>';
    }
    $ep .= $flash_div_open.$flash_obj.$flash_div_close;
    $ep .= '</div>';
    return $ep;
}

/**
 * Adds Bubblecast-related HTML to a comment form (to add a button to insert
 * a Bubblecast video).
 * 
 * @param string $text	initial form code
 * @return string changed form code
 */
function bubblecast_comment_form($text='') {
    $url = 'quickcast_comment.php';
    include($url); 
    $image_btn = get_bubblecast_logo();
    $v .= '<a href="#" onclick="showBubblecastComment(); return false;"><img src="' . $image_btn . '" /> ' . __('Add video comment', 'bubblecast') . '</a>' . "\n";
	echo $v;
}

/**
 * Echoes code which includes javascripts and CSS for Bubblecast plugin.
 */
function bubblecast_head() {
	global $current_user;
	
	get_currentuserinfo();
	
    $pluginurl = bubblecast_get_plugin_base_dir() . '/';
    echo "\n" . '<link href="' . $pluginurl . 'bubblecast.css" media="screen" rel="stylesheet" type="text/css"/>' . "\n";
    echo "\n" . '<script src="' . $pluginurl . 'js/bubblecast.js" type="text/javascript"></script>' . "\n";
    echo "\n" . '<script src="' . $pluginurl . 'js/dynamic-js.php?username=' . $current_user->user_login . '&password_hash=' . $current_user->user_pass . '" type="text/javascript"></script>'."\n";
}

/**
 * Process a bubblecastvideos tab in the media upload.
 * 
 * @return string
 */
function media_upload_bubblecastvideos() {
  return wp_iframe('bubblecastvideos_page');
}

/**
 * Generates a bubblecastvideos page (for media upload tab).
 */
function bubblecastvideos_page() {
    global $pluginMode;
    
    $pluginMode = 'wp';
    include("iquickcast.php");
}

/**
 * Adds a bubblecast tab to the media manager.
 * 
 * @param string $content	initial content
 * @return string content with our code added
 */
function add_bubblecast_tab($content) {
	$content['bubblecastvideos'] = 'Bubblecast';
	return $content;
}

/**
 * Includes bubblecast stuff to the blog head.
 */
function bubblecast_on_wp_head() {
    bubblecast_head();
}

/**
 * Includes bubblecast stuff to the blog admin head.
 */
function bubblecast_on_admin_head() {
    bubblecast_head();
}

/**
 * Hook for 'save post' by Bubblecast. Used to send a callback to Bubblecast
 * server to update post link/title in the videos embedded to a post.
 * 
 * @param int $postID		ID of the post
 * @param object $postData	post data
 */
function bubblecast_save_post($postID, $postData) {
    global $sendPostDatURL;
    //error_log("bubblecast_save_post = ".$postData->guid);
    if ($postData->post_type == "page" || $postData->post_type == "post") {
        $link = get_permalink($postID);
        bubblecast_send_post_data($sendPostDatURL, $postData->post_content,
        		$link, htmlentities($postData->post_title));
    }
}

/**
 * Processes a comment which is added to a post. Used to send a callback to
 * Bubblecast server to update post data in the videos embedded to a post.
 * 
 * @param int $commentPostID	ID of the comment
 * @param int $comment_approved	1 if comment is approved
 */
function bubblecast_comment_post($commentPostID, $comment_approved) {
    if ($comment_approved == 1) {
        bubblecast_handle_comment($commentPostID);
    }
}

/**
 * Processes a comment update. Used to send a callback to
 * Bubblecast server to update post data in the videos embedded to a post.
 * 
 * @param int $commentPostID	ID of the comment
 */
function bubblecast_edit_comment($commentPostID) {
    bubblecast_handle_comment($commentPostID);
}

/**
 * Handles a comment creation/update. Used to send a callback to
 * Bubblecast server to update post data in the videos embedded to a post.
 * 
 * @param int $commentPostID	ID of the comment
 */
function bubblecast_handle_comment($commentPostID){
    global $sendPostDatURL;
    
    $postData = &get_comment($commentPostID);
    $link = get_comment_link( $commentPostID );
    bubblecast_send_post_data($sendPostDatURL, $postData->post_content, $link,
    		htmlentities($postData->post_title));
}

/**
 * Returns default options for Bubblecast Video Posts widget.
 * 
 * @return array default options
 */
function bubblecast_get_video_posts_widget_default_options() {
    // first find out the category which we'll be using as a default one
    // trying 'Video' first
    $defaultCategoryId = get_cat_ID('Video');
    if (!$defaultCategoryId) {
        // trying to create it
        if(function_exists('wp_create_category')){
            if (wp_create_category('Video')) {
                $defaultCategoryId = get_cat_ID('Video');
            } else {
                // select Uncategorized as a fallback
                $defaultCategoryId = 0;
            }
        } else {
            // select Uncategorized as a fallback
            $defaultCategoryId = 0;
        }
    }

    // building the default options
    $defaultOptions = array(
        'title' => __('Bubblecast Video Posts', 'bubblecast'),
        'layout' => 'v',
        'videos' => 3,
        'categories' => array($defaultCategoryId),
        'use_current_cat' => 'N'
    );
    return $defaultOptions;
}

/**
 * Echoes a Bubblecast Video Posts widget control HTML.
 */
function bubblecast_widget_video_posts_control() {
    require 'widget/video_posts_control.php';
}

/**
 * Echoes a Bubblecast Video Posts widget HTML.
 * 
 * @param mixed $args	widget args
 */
function bubblecast_widget_video_posts($args) {
    global $bubblecastThumbUrl;
    
    extract($args);
    require 'widget/video_posts.php';
}

/**
 * Registers a Bubblecast Video Posts widget.
 */
function bubblecast_widget_video_posts_register() {
    // these two names must match!
    register_sidebar_widget('Bubblecast Video Posts', 'bubblecast_widget_video_posts');
    register_widget_control('Bubblecast Video Posts', 'bubblecast_widget_video_posts_control');
}

/**
 * Loads a text domain for our plugin.
 */
function bubblecast_load_textdomain() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain('bubblecast', 'wp-content/plugins/' . $plugin_dir, $plugin_dir);
}

/**
 * Registers bubblecast plugin settings.
 */
function reg_bubblecast_settings() {
    register_setting( 'bubblecast-group', 'bubblecast_username' ); 
    register_setting( 'bubblecast-group', 'bubblecast_password' );
    register_setting( 'bubblecast-group', 'bubblecast_language' );
    register_setting( 'bubblecast-group', 'bubblecast_wvp_options' );
    register_setting( 'bubblecast-group', 'bubblecast_auto_build_sitemap' );
}

/**
 * Modifies a menu.
 */
function bubblecast_plugin_menu() {
    $show_bubblecast_options = false;
    if (is_wpmu()) {
        // We're in WPMU
        if (is_site_admin()) {// We should show options page only to WPMU site admin
            $show_bubblecast_options = true;
        }
    } else {
        $show_bubblecast_options = true;
    }
    if ($show_bubblecast_options) {
        add_options_page(__('Bubblecast Plugin Options', 'bubblecast'), 'Bubblecast', 8, __FILE__, 'bubblecast_plugin_options');
    }
}

/**
 * Echose an options page.
 */
function bubblecast_plugin_options() {
    include("boptions.php");
}

/**
 * Returns a plugin base dir path.
 * 
 * @return string path
 */
function bubblecast_get_plugin_base_dir() {
    return WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), '', plugin_basename(__FILE__));
}

/**
 * Signals that sitemap has be bu rebuilt.
 * 
 * @param int|null $post_id	ID of the post which was created/updated/deleted
 * @param int $delay		delay in seconds
 */
function bubblecast_signal_to_build_sitemap($post_id=null, $delay=15) {
	wp_clear_scheduled_hook('bubblecast_build_sitemap_cron');
	wp_schedule_event(time() + $delay, BUBBLECAST_SITEMAP_REBUILD_PERIOD_SECONDS, 'bubblecast_build_sitemap_cron');
}

/**
 * Cron trigger for sitemap generation.
 */
function bubblecast_build_sitemap_cron() {
	do_bubblecast_build_sitemap();
}

/**
 * Actually builds a sitemap.
 */
function do_bubblecast_build_sitemap() {
	global $wpdb, $playerMovieURL;
	
	$home_path = ABSPATH;
	$sitemapdirname = 'bubblecast-sitemap';
	$dir = $home_path . '/' . $sitemapdirname;
	
	// preparing directory
	@mkdir($dir);
//	bubblecast_empty_directory($dir);
	
	$common_part = " FROM $wpdb->posts ".
           " WHERE 1=1 AND ($wpdb->posts.post_type = 'post' OR $wpdb->posts.post_type = 'page') ".
           " AND ($wpdb->posts.post_status = 'publish') ".
           " AND ($wpdb->posts.post_content like '%[bubblecast%') ";
	$count_q = 'select count(*) ' . $common_part;
	$total = $wpdb->get_var($count_q);
	
    $offset = 0;
    $delta = 2000;
    $index = 0;
    
    do {
	    $q = "SELECT $wpdb->posts.* ".
	           $common_part.
	           " order by $wpdb->posts.post_date DESC limit $offset, $delta ";
        $video_posts = $wpdb->get_results($q);
        
        $siteId = get_bubblecast_option('bubblecast_site_id');
	    if (!$siteId) {
	        $siteId = bubblecast_login();
	    }
        
        $file_name = "$dir/videositemap-$index.xml";
        $out_handle = fopen($file_name, 'wb');
        $gzip_handle = gzopen($file_name . '.gz', 'wb');
        bubblecast_write_to_file_and_gzip($out_handle, $gzip_handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        bubblecast_write_to_file_and_gzip($out_handle, $gzip_handle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">');
        foreach ($video_posts as $video_post) {
        	$video_ids_arr = bubblecast_get_video_ids_from_post($video_post);
            $video_xml = "";
            $video_xml .= '<url>'."\n";
            $video_xml .= '<loc>'.get_permalink($video_post).'</loc>'."\n";
            foreach ($video_ids_arr as $video_id) {
	            $player_loc = $playerMovieURL."?siteId=".$siteId."&amp;recordEnabled=false&amp;isVideo=true&amp;languages=en&amp;streamName=".$video_id."&amp;pluginMode=wp";
	            $video_xml .= '<video:video>'."\n";
	            $video_xml .= '      <video:player_loc allow_embed="yes" autoplay="autoPlay=true">'.$player_loc.'</video:player_loc>'."\n";
	            $video_xml .= '      <video:thumbnail_loc>'.bubblecast_encode_xml(bubblecast_get_thumbnail($video_id, $video_post)).'</video:thumbnail_loc>'."\n";
	            $title = strip_tags(get_the_title($video_post));
	            $video_xml .= '      <video:title>'.bubblecast_encode_xml($title).'</video:title>'."\n";
	            $description = trim(strip_tags(bubblecast_get_the_excerpt($video_post)));
	            if ($description) {
	            	$video_xml .= '      <video:description>'.bubblecast_encode_xml($description).'</video:description>'."\n";
	            }
	            $video_xml .= '      <video:publication_date>'.$video_post->post_date.'</video:publication_date>'."\n";
	            $video_xml .= '      <video:family_friendly>yes</video:family_friendly>'."\n";
	            $video_xml .= '</video:video>'."\n";
            }
            $video_xml .= '</url>'."\n";
            bubblecast_write_to_file_and_gzip($out_handle, $gzip_handle, $video_xml);
            fflush($out_handle);
        }
        bubblecast_write_to_file_and_gzip($out_handle, $gzip_handle, '</urlset>'."\n");
        fflush($out_handle);
        fclose($out_handle);
        gzclose($gzip_handle);

        $offset += $delta;
        $index++;
    } while ($offset < $total);
    
    // creating sitemap index
    $out_handle = fopen($dir."/videositemap-index.xml", 'w');
    fwrite($out_handle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($out_handle, '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
    for ($i = 0; $i < $index; $i++) {
        fwrite($out_handle, "    <sitemap>\n");
        fwrite($out_handle, "        <loc>" . get_bloginfo('siteurl') . "/$sitemapdirname/videositemap-$i.xml.gz</loc>\n");
        $lastmod = date('Y-m-d');
        fwrite($out_handle, "        <lastmod>$lastmod</lastmod>\n");
        fwrite($out_handle, "    </sitemap>\n");
    }
    fwrite($out_handle, '</sitemapindex>' . "\n");
    fclose($out_handle);
}

/**
 * Writes the same string to file and gzip streams.
 * 
 * @param $fh		file stream
 * @param $gzh		zgip stream
 * @param $s		string to write
 */
function bubblecast_write_to_file_and_gzip($fh, $gzh, $s) {
	fwrite($fh, $s);
	gzwrite($gzh, $s);
}

/**
 * Builds sitemap from the UI.
 */
function bubblecast_build_sitemap_from_ui() {
	bubblecast_signal_to_build_sitemap(null, BUBBLECAST_SITEMAP_REBUILD_PERIOD_SECONDS);
	do_bubblecast_build_sitemap();
}

/**
 * Generates a link to our sitemap to robots.txt.
 */
function bubblecast_sitemap_robots() {
	$url = get_bloginfo('siteurl') . '/bubblecast-sitemap/videositemap-index.xml';
	echo  "\nSitemap: " . $url . "\n";
}

add_filter('media_buttons_context', 'bubblecast_media_buttons_context');
add_action('wp_head', 'bubblecast_on_wp_head');
add_action('admin_head', 'bubblecast_on_admin_head');
add_filter('comment_text', 'bubblecast_comment');
add_filter('the_content', 'bubblecast_post');
add_action('comment_form', 'bubblecast_comment_form');
add_action('media_upload_tabs','add_bubblecast_tab');
add_action('media_upload_bubblecastvideos', 'media_upload_bubblecastvideos');
add_action('save_post', 'bubblecast_save_post',10,2);
add_action('comment_post', 'bubblecast_comment_post',10,2);
add_action('edit_comment', 'bubblecast_edit_comment',10,1);
add_action('admin_init', 'reg_bubblecast_settings');
add_action('admin_menu', 'bubblecast_plugin_menu');
add_action('init', 'bubblecast_load_textdomain');

// registering widgets
add_action('init', 'bubblecast_widget_video_posts_register');

// sitemap-related actions
add_action('publish_post', 'bubblecast_signal_to_build_sitemap', 10000, 1);
add_action('publish_page', 'bubblecast_signal_to_build_sitemap', 10000, 1);
add_action('delete_post', 'bubblecast_signal_to_build_sitemap', 10000, 1);
add_action('save_post', 'bubblecast_signal_to_build_sitemap', 10000, 1);
add_action('bubblecast_build_sitemap_cron', 'bubblecast_build_sitemap_cron');
add_action('do_robots', 'bubblecast_sitemap_robots');

endif;// this is for if on the top which checks whether we need to plug in

?>