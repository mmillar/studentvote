<?php
require_once("load_http.php");
require_once("bubblecast_xmlparser.php");

/**
 * Returns true if we're running in a WPMU, false otherwise.
 * 
 * @return bool whether it's WPMU (Wordpress MU)
 */
function is_wpmu() {
    return function_exists('wpmu_create_user');
}

/**
 * Logins to Bubblecast and returns a site ID. Authentication is made using
 * username/password stored in the DB (actually, in our plugin options); the
 * result is written to these options too before returning.
 * 
 * @return string site ID (or null if no success)
 */
function bubblecast_login() {
    $bubblecast_username = get_bubblecast_option("bubblecast_username");
    $bubblecast_password = get_bubblecast_option("bubblecast_password");
    $siteId = bubblecast_remote_login($bubblecast_username,$bubblecast_password);
    update_bubblecast_option("bubblecast_site_id",$siteId);
    return $siteId;
}

/**
 * Does a remote login to Bubblecast using a remote call.
 * 
 * @param string $bubblecast_username	username on the bubble-cast.com
 * @param string $bubblecast_password	password on the bubble-cast.com
 * 										(plain-text version)
 * @return string site ID (or null if no success)
 * @internal
 */
function bubblecast_remote_login($bubblecast_username,$bubblecast_password) {
    global $authURL;
    
    $xml = bubblecast_load("$authURL?username=$bubblecast_username&password=" . sha1($bubblecast_password),
            array('return_info'    => true));
    $user_doc = XML_unserialize($xml['body']);
    $siteId = $user_doc['root']['siteId'];
    return $siteId;
}

/**
 * Sends post data to bubble-cast.com to update video properties. Data is
 * actually sent if there's at least one [bubblecast] tag in the message (i.e.
 * post contains some Bubblecast videos).
 * 
 * @param string $sendPostDatURL	URL to which to send data
 * @param string $message			message (usually this is a post body)
 * @param string $link				link associated with this content (usually
 * 									this is a permalink to post)
 * @param string $title				content title (usually, this is a post title)
 */
function bubblecast_send_post_data($sendPostDatURL, &$message, $link, $title) {
    if (preg_match("/\\[bubblecast(.*?)\\]/", $message)) {
        bubblecast_load($sendPostDatURL,
             array('method' => 'post',
            		'return_info'    => false,
            		'post_data' => array (
                			'message' => $message,
                			'link' => $link,
                			'title' => $title
            )));
    }
}

/**
 * Determines whether currently logged in user is admin.
 * 
 * @return bool true if admin
 */
function bubblecast_is_admin() {
    global $user_level;
    
    get_currentuserinfo();
    return $user_level == 10;
}

/**
 * Saves a bubblecast plugin-global option. This implementation is WPMU-aware.
 * 
 * @param string $opt_name		option name
 * @param mixed $opt_val		value
 */
function update_bubblecast_option($opt_name,$opt_val) {
    if (is_wpmu()) {
        update_site_option($opt_name, $opt_val);
    } else {
        update_option($opt_name, $opt_val);
    }
}

/**
 * Returns a bubblecast plugin-global option. This implementation is WPMU-aware.
 * 
 * @param string $opt_name		option name
 * @return mixed option value
 */
function get_bubblecast_option($opt_name) {
    if (is_wpmu()) {
        return get_site_option($opt_name);
    } else {
        return get_option($opt_name);
    }
}

/**
 * Constructs an HTML code for a bubblecast flash object.
 * 
 * @param int $width				flash width
 * @param int $height				flash heigth
 * @param string $video_id			video ID
 * @param int $videoNum				index of the video on the current page
 * @param string $playerMovieURL	URL of the player
 * @param string $siteId			ID of the site as registered at Bubblecast
 * @param string $languages			language to use in player
 * @param string $username			username to use in player (as registered
 * 									at Bubblecast)
 * @param string $password_hash		encrypted password to use in player (as
 * 									registered at Bubblecast)
 * @return HTML code
 */
function bubblecast_flash_object($width, $height, $video_id, $videoNum,
        $playerMovieURL, $siteId, $languages, $username, $password_hash) {
    $plugin_mode = 'wp';
    $plugin_mode = apply_filters('bubblecast_plugin_mode', $plugin_mode);
    $flashvars = 'siteId='.$siteId.'&amp;recordEnabled=false&amp;autoPlay=true&amp;isVideo=true&amp;languages=' . $languages . '&amp;pluginMode=' . $plugin_mode . '&amp;embedCodeFormatVersion=2&amp;streamName='.$video_id.'&amp;userName=' . $username . '&amp;password=' . $password_hash;
    $flashvars = apply_filters('bubblecast_flashvars', $flashvars);
    $flash_obj = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"                width="'.$width.'" height="'.$height.'" id="quickcast'.$video_id.'_'.$videoNum.'" align="middle">            <param name="allowScriptAccess" value="always" />            <param name="movie" value="'.$playerMovieURL.'" />            <param name="flashvars" value="' . $flashvars . '" />            <param name="quality" value="high" />            <param name="allowfullscreen" value="true"/>            <param name="bgcolor" value="#ededed" />                <embed src="'.$playerMovieURL.'" quality="high" bgcolor="#ededed" width="'.$width.'" height="'.$height.'" name="quickcast'.$video_id.'_'.$videoNum.'" flashvars="' . $flashvars . '" allowfullscreen="true"                       align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />        </object>';
    return $flash_obj;
}

/**
 * Returns a URL of the video thumbnail. If $video_id is null, then thumbnail
 * of the first video belonging to the post will be returned.
 * 
 * @param string $video_id	video ID
 * @param object $post		post from which to obtain video thumbmail
 * @return URL to thumbnail
 */
function bubblecast_get_thumbnail($video_id,$post) {
    global $bubblecastThumbUrl;
    
    if ($video_id) {
        return "$bubblecastThumbUrl?podcastId=$video_id&type=w&forceCheckProvider=true";
    } else {
        $thumb = get_post_meta($post->ID, 'thumb', true);
        if ($thumb) {
            return $thumb;
        }
        return "$bubblecastThumbUrl?podcastId=0&type=w&forceCheckProvider=false";
    }
    return '';
}

/**
 * Returns video ID of the first video belonging to the post.
 * 
 * @param object $post	post
 * @return string video ID (or null if no video in post)
 */
function bubblecast_get_video_id_from_post($post) {
    $matches = array();
    $matched = preg_match("/\\[bubblecast\\s*id=([^\\s\\]]+)\\s*.*\\s*\\]/", $post->post_content, $matches);
    if ($matched > 0) {
        return  $matches[1];
    } else {
        return null;
    }
}

/**
 * Returns video ID and player dimensions of the first video belonging to the post.
 * 
 * @param object $post	post
 * @return array|null video ID, width, height or null if no video in post
 */
function bubblecast_get_video_id_and_player_dimensions_from_post($post) {
    $matches = array();
    $matched = preg_match("/\\[bubblecast\\s*id=([^\\s\\]]+)\\s+.*\\bplayer=(\\d+)x(\\d+)\\s*.*\\s*\\]/", $post->post_content, $matches);
    if ($matched > 0) {
        return array($matches[1], $matches[2], $matches[3]);
    } else {
        return null;
    }
}

/**
 * Returns IDs of videos belonging to the post.
 * 
 * @param object $post	post
 * @return array|null video IDs (or null if no videos in post)
 */
function bubblecast_get_video_ids_from_post($post) {
    $matches = array();
    $matched = preg_match_all("/\\[bubblecast\\s*id=([^\\s\\]]+)\\s*.*\\s*\\]/", $post->post_content, $matches);
    if ($matched > 0) {
    	$ids = array();
    	foreach ($matches[1] as $match) {
    		$ids[] = $match;
    	}
        return $ids;
    } else {
        return null;
    }
}

/**
 * Returns path to home directory.
 * 
 * @return home path
 */
function bubblecast_get_home_path() {
	$home = get_option('home');
	if ($home != '' && $home != get_option('siteurl')) {
		$home_path = parse_url($home);
		$home_path = $home_path['path'];
		$root = str_replace($_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"]);
		$home_path = trailingslashit($root . $home_path);
	} else {
		$home_path = ABSPATH;
	}
}

/**
 * Removes files from the directory. Subdirectories are not considered.
 * 
 * @param $dir	path to directory to empty
 */
function bubblecast_empty_directory($dir) {
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (!is_dir($file)) {
                    unlink($dir . '/' . $file);
                }
            }
            closedir($dh);
        }
    }
}

/**
 * Returns a regexp used to parse a [bubblecast] tag.
 * 
 * @return string regexp
 */
function bubble_regexp() {
    return "/\\[bubblecast\\s*id=([^\\s\\]]+)\\s*(thumbnail=([^\\s\\]]+))?\\s*(player=([^\\s\\]]+))?\\s*.*\\]/";
}

/**
 * Obtains an excerpt from the post.
 * 
 * @param object $external_post	post to use; if null, global $post is used
 * @return string excerpt
 */
function bubblecast_get_the_excerpt(&$external_post=null){
	global $post;
	
	if ($external_post == null) {
		$post_to_use =& $post;
	} else {
		$post_to_use =& $external_post;
	}
	if (strlen(trim($post_to_use->post_excerpt)) == 0) {
		$bubblecast_post_excerpt = $post_to_use->post_content;
		$bubblecast_post_excerpt = trim(preg_replace(bubble_regexp(), '', $bubblecast_post_excerpt));
		$text_only = $bubblecast_post_excerpt;
	    $bubblecast_post_excerpt = substr(trim($bubblecast_post_excerpt), 0, 200);
	    if (strlen($text_only) > 200) {
	    	$bubblecast_post_excerpt .= "...";
	    }
	} else {
	    $bubblecast_post_excerpt = $post_to_use->post_excerpt;
	}
	$bubblecast_post_excerpt = apply_filters( 'bubblecast_the_excerpt', $bubblecast_post_excerpt);
    return $bubblecast_post_excerpt;
}

/**
 * Encodes XML (very simple and dull implementation).
 * 
 * @param string $str	text to escape
 * @return string result
 */
function bubblecast_encode_xml($str) {
    $str = str_replace('&', '&amp;', $str);
    $str = str_replace('<', '&lt;', $str);
    $str = str_replace('>', '&gt;', $str);
    return $str;
}

?>