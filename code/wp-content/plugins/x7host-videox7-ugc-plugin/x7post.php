<?php

$encoding = 'UTF-8';
$x7server = $_POST['x7server'];
$x7uiconfid = $_POST['x7uiconfid'];
$eid = $_POST['eid'];
$title = $_POST['title'];
$title = htmlentities($title,ENT_NOQUOTES,$encoding);
$rpcurl = $_POST['rpcurl'];
$username = $_POST['username'];
$password = $_POST['password'];
$x7bloghome = $_POST['x7bloghome'];
$x7fullplugurl = $_POST['x7fullplugurl'];
$description = $_POST['description'];
$x7kalpartnerid = $_POST['x7kalpartnerid'];
$x7kalsubpartnerid = "$x7kalpartnerid * 100";

//include IXR class
include("ixr.php");
if (!isset($_POST['category'])){
    $category[] = "Uncategorized";
} else {
$category = implode(",", $_POST['category']);
$category = preg_split("/[\s,]+/", $category);
$pattern = '/undefined/';
$replacement = '';
$category = preg_replace($pattern, $replacement, $category, -1, $count);
}

$keywords = $_POST['keywords'];
$keywords = htmlentities($keywords,ENT_NOQUOTES,$encoding);

$client = new IXR_Client("$x7bloghome/xmlrpc.php");

$body = $description;
$body .= "<br /><br />";
$body .= <<<BODY
<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="330" width="400" xmlns:dc="http://purl.org/dc/terms/" xmlns:media="http://search.yahoo.com/searchmonkey/media/" rel="media:video" resource="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/$eid" data="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/$eid"><param name="allowFullScreen" value="true" /><param name="allowNetworking" value="all" /><param name="allowScriptAccess" value="always" /><param name="bgcolor" value="#000000" /><param name="flashVars" value="&" /><param name="movie" value="$x7server/index.php/kwidget/cache_st/1283996450/wid/_100/uiconf_id/$x7uiconfid/entry_id/$eid" /><a href="http://corp.kaltura.com">video platform</a> <a href="http://corp.kaltura.com/technology/video_management">video management</a> <a href="http://corp.kaltura.com/solutions/overview">video solutions</a> <a href="http://corp.kaltura.com/technology/video_player">video player</a> <a rel="media:thumbnail" href="$x7server/p/$x7kalpartnerid/sp/$x7kalsubpartnerid/thumbnail/entry_id/'+eid+'/width/120/height/90/bgcolor/000000/type/2" /> <span property="dc:description" content="" /><span property="media:title" content="x7Video" /> <span property="media:width" content="400" /><span property="media:height" content="330" /> <span property="media:type" content="application/x-shockwave-flash" /><span property="media:duration" content="{DURATION}" /> </object>
BODY;

$content['title'] = $title;
$content['description'] = $body;
$content['mt_allow_comments'] = 1;
$content['mt_allow_pings'] = 1;
$content['mt_keywords'] = $keywords;

foreach ($category as $key => $value)
{
    $content['categories'][$key] = $value;
}

//var_export($category);
//var_export($categories);

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
if (!$client->query('metaWeblog.newPost','', $username, $password, $content, false))
{  
    //XMLRPC FAILED redirect user to posts page
//$redirect = $_SERVER['HTTP_REFERER'] . "?result=failxmlrpc";
//header("HTTP/1.1 301 Moved Permanently");
//header ("Location: $redirect");
//exit;
die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
} else {
    //XML RPC SUCCESS redirect user to posts page
$redirect = $_SERVER['HTTP_REFERER'] . "?result=success";
header("HTTP/1.1 301 Moved Permanently");
header ("Location: $redirect");
exit;
}
}
else {
    //REFERRER CHECK FAILED redirect user to posts page
$redirect = $_SERVER['HTTP_REFERER'] . "?result=fail";
header("HTTP/1.1 301 Moved Permanently");
header ("Location: $redirect");
exit;
}
?>