<?php
$listid = $_GET['listid'];
$width = $_GET['width'];
$height = $_GET['height'];
$x7server = $_GET['x7serverget'];
$x7serverget = urlencode($x7server);
$x7pluiconfid = $_GET['x7pluiconfid'];
$x7kalpartnerid = $_GET['x7kalpartnerid'];
$x7kalsubpartnerid = $x7kalpartnerid . "00";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://gmpg.org/xfn/11">
<title>x7 List Player</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    </head>
        <body>
        <object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="<?php echo($height); ?>" width="<?php echo($width); ?>" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="<?php echo($x7server) ?>/index.php/kwidget/cache_st/1284005068/wid/_<?php echo($x7kalpartnerid) ?>/uiconf_id/<?php echo($x7pluiconfid) ?>" data="<?php echo($x7server) ?>/index.php/kwidget/cache_st/1284005068/wid/_<?php echo($x7kalpartnerid) ?>/uiconf_id/<?php echo($x7pluiconfid) ?>"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="playlistAPI.autoContinue=true&playlistAPI.autoInsert=true&playlistAPI.kpl0Name=test&playlistAPI.kpl0Url=<?php echo($x7serverget) ?>%2Findex.php%2Fpartnerservices2%2Fexecuteplaylist%3Fuid%3D%26partner_id%3D<?php echo($x7kalpartnerid) ?>%26subp_id%3D<?php echo($x7kalsubpartnerid) ?>%26format%3D8%26ks%3D%7Bks%7D%26playlist_id%3D<?php echo($listid) ?>&" /><param name="movie" value="<?php echo($x7server) ?>/index.php/kwidget/cache_st/1284005068/wid/_<?php echo($x7kalpartnerid) ?>/uiconf_id/<?php echo($x7pluiconfid) ?>" /><a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a> <a href="http://corp.kaltura.com/technology/video_player">video player</a> {SEO} </object>
        </body>
</html>