<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');

$x7bloghome = $_POST['x7bloghome'];
$count = count($_POST['eids']);
$name = $_POST['listname'];
$contentEntries = implode(",", $_POST['eids']);
$ul = $_POST['ul'];
$ks = $_POST['ks'];
$x7server = $_POST['x7server'];
$x7kalpartnerid = $_POST['x7kalpartnerid'];

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
include_once('settings.php');
include_once('lib/kaltura_client.php');
include_once('lib/kaltura_helpers.php');
include_once('lib/kaltura_model.php');

$config = new KalturaConfiguration("$x7kalpartnerid");
		$config->serviceUrl = $x7server;
		$client           = new KalturaClient($config);
      $client->setKs($ks);

      $newlist = new KalturaPlaylist;
      $newlist->playlistContent = $contentEntries;
	  $newlist->status = 2;
      $newlist->playlistType = 3;
      $newlist->name = $name;
      $newlist->partnerId = $x7kalpartnerid;
      $newlist->type = 5;
      $newlist->userId = $ul;
      $newlist->licenseType = 2;
      $response = $client->playlist->add($newlist, true);
      echo "$response->id";
} else { //end eregi check
	echo "error";
}
?>
