<?php

header('content-type: text/javascript');

require("../config.php");

// this is for bubblecast_flash_object which calls apply_filters
require("../../../../wp-config.php");
require_once(ABSPATH . WPINC . '/plugin.php');

require_once("../bubblecast_utils.php");

?>

function bubblecastFlashObjectCode(width, height, videoId, videoNum, siteId, languages) {
    var code = '<?php echo bubblecast_flash_object('$WIDTH$', '$HEIGHT$', '$VIDEO_ID$', '$VIDEO_NUM$', $playerMovieURL, '$SITE_ID$', '$LANG$', $_GET['username'], $_GET['password_hash']); ?>';
    code = bubblecastReplaceAll(code, /\$WIDTH\$/, width);
    code = bubblecastReplaceAll(code, /\$HEIGHT\$/, height);
    code = bubblecastReplaceAll(code, /\$VIDEO_ID\$/, videoId);
    code = bubblecastReplaceAll(code, /\$VIDEO_NUM\$/, videoNum);
    code = bubblecastReplaceAll(code, /\$SITE_ID\$/, siteId);
    code = bubblecastReplaceAll(code, /\$LANG\$/, languages);
    return code;
}

function bubblecastReplaceAll(str, regex, toWhat) {
    var oldStr;
    do {
		oldStr = str;
		str = str.replace(regex, toWhat);
    } while (oldStr != str);
    return str;
}

