<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');
include_once('settings.php');
include_once('lib/kaltura_client.php');
include_once('lib/kaltura_helpers.php');
include_once('lib/kaltura_model.php');

$ks = $_GET['ks'];
$ks = urldecode($ks);
$x7server = $_GET['x7server'];
$x7server = urldecode($x7server);
$pluginurl = $_GET['pluginurl'];
$pluginurl = urldecode($pluginurl);
$eid = $_GET['eid'];
$listname = $_GET['listname'];
$x7kalpartnerid = $_GET['x7kalpartnerid'];
$x7kalsubpartnerid = $x7kalpartnerid . "00";
$x7bloghome = urldecode($_GET['x7bloghomeget']);
$user_login = $_GET['userlogin'];

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
//Start Kaltura admin session
		$kmodel = KalturaModel::getInstance();
		$ks = $kmodel->getAdminSession("","$user_login");
		if (!$ks)
			wp_die(__('Failed to start new session.<br/><br/>'.$closeLink));

		//get media
		$list = $kmodel->listAllEntriesByPagerandFilter($x7kalpartnerid, 'all', $namelike, $user, $tags, $admintags, $category, $pagesize, $pageindex);

    //get list of entries for editing current playlist
$plentryresult = rest_helper("$x7server/api_v3/?service=playlist&action=get",
					 array(
						'ks' => $ks,
						'id' => $eid
					 ), 'POST'
					 );
} else {
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://gmpg.org/xfn/11">
<title>x7 Playlist Editor</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
<script type="text/javascript" src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js'></script>
<script type='text/javascript' src="<?php echo($pluginurl); ?>/js/jquery.tools.min.js"></script>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js?ver=3.0.1'></script>
<script type='text/javascript' src='<?php echo($pluginurl); ?>/js/shadowbox.js?ver=3.0.1'></script>
<script type='text/javascript' src='<?php echo($pluginurl); ?>/js/x7js.js?ver=3.0.1'></script>
<script type='text/javascript' src='<?php echo($pluginurl); ?>/js/validator.js?ver=3.0.1'></script>
<link rel='stylesheet' href='<?php echo($pluginurl); ?>/css/jqueryui/jquery-ui-1.8.7.custom.css' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo($pluginurl); ?>/css/sbox/shadowbox.css' type='text/css' media='all' />
<link rel='stylesheet' href='<?php echo($pluginurl); ?>/css/x7style.css' type='text/css' media='all' />

<script type="text/javascript">
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
		})
		.append( "<span class='ui-icon ui-icon-grip-dotted-vertical'></span>" )
		.wrap( "<div class='ui-handle-helper-parent'></div>" ).parent();
		
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
				ui.placeholder.html("<img src='<?php echo($pluginurl); ?>/images/x7placeholder.png' />");
				},
			connectWith: '.connectedSortable',
			forcePlaceholderSize: 'true',
			revert: 'true',
			tolerance: 'pointer',
			placeholder: 'ui-state-highlight'
		}).disableSelection();
		
		jQuery("div.scroll-content-item>img[title]").tooltip({
					position: 'bottom center',
					effect: 'slide'
				});
				
	    });//end document ready
    
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
	    
	    if (listname.length < 5)
	    {
		  valError = "error";
		  alert("Playlist name must contain at least five characters!");
	    }
	    var plid;
	    plid = jQuery("#plid").html();
	    plid = String(plid);
	    if (plid.length < 10)
	    {
		  valError = "error";
		  alert("Error - Playlist ID not identified.  Please try again.");
	    }
	    
	    if (valError != "error")
	    {
	    strEids = arrEids.join(",");
	    jQuery.post(
		       "<?php echo($pluginurl) ?>/x7plupdate.php",
		       {'x7bloghome': "<?php echo($x7bloghome) ?>", 'ks': "<?php echo($ks) ?>", 'eid': plid, 'x7server': "<?php echo($x7server) ?>", 'name': listname, 'plcontent': strEids},
		       function ( response ){
			alert('Playlist ID: '+response+' updated.');
                        parent.Shadowbox.close();
		       }); //end post
	    };//end if not valerror error
      }//end x7listpreview
      
      function x7EditClose() {
	    parent.Shadowbox.close();
	}
</script>
</head>
        <body style="background-color: white; margin: 10px;">
	  <h3>Media List</h3>
<div class="scroll-pane ui-widget ui-widget-header ui-corner-all">
			<ul id="vidlist" class="scroll-content connectedSortable">
<?php
				$itemcount = "0";
				foreach ($list->objects as $mediaEntry) {
					$itemcount++;
					$name     = $mediaEntry->name; // get the entry name
					$id       = $mediaEntry->id;
					$thumbUrl = $mediaEntry->thumbnailUrl;  // get the entry thumbnail URL
					$submitter = $mediaEntry->userId;
					$description = $mediaEntry->description;
					$description = str_replace("'", "", "$description"); 
					echo "<div eid='$id' class='scroll-content-item ui-widget-header'><img title='<strong>Name</strong>: $name<br /><strong>Author</strong>: $submitter' height='90' width='120' src='$thumbUrl' /></div>";
				}
?>
			</ul>
		<div class="scroll-bar-wrap ui-widget-content ui-corner-bottom">
		<div class="scroll-bar"></div>
		</div>
		</div>
			<h3>Your Playlist</h3>
			  <strong>Playlist ID: <span id="plid"><?php echo($eid) ?></span><br>
			  <strong>Playlist name:  </strong><textarea id="listname"><?php echo($listname) ?></textarea><br><br>
			  <a onclick="x7ListPreview()" id="preview"><strong>[SAVE AND PREVIEW]</strong></a>  <a onclick="x7EditClose()">[CANCEL]</a><br><br>
			<ul id="playlist" class="connectedSortable">
			  <?php
                            $content = (string) $plentryresult->result->playlistContent;
                            $plids = explode(",", $content);
                            foreach ($plids as $plid){
				echo "<div class='scroll-content-item ui-widget-header' eid='$plid'><img title='Drag me!' src='$x7server/p/$x7kalpartnerid/sp/$x7kalsubpartnerid/thumbnail/entry_id/$plid/width/90/height/120'/></div>";
                            } // end foreach
			  ?>
			</ul>
        </body>
</html>