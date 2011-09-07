<?php
$eid = $_GET['eid'];
$x7server = $_GET['x7server'];
$x7server = urldecode($x7server);
$x7uiconfid = $_GET['x7uiconfid'];
$x7kalpartnerid = $_GET['x7kalpartnerid'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://gmpg.org/xfn/11">
<title>x7 Video Player</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    </head>
        <body>
                <div id="kplayerWrap">
                     <div id="kplayer"></div>
                </div>
                <script type="text/javascript">
                var params = {
                    allowscriptaccess: "always",
                    allownetworking: "all",
                    allowfullscreen: "true",
                    bgcolor: "#000000",
                    wmode: "opaque"
                    };
                var flashVars = {
                        entryID: "<?php echo($eid) ?>"
                        };
                        swfobject.embedSWF("<?php echo($x7server); ?>/index.php/kwidget/cache_st/1283953031/wid/_<?php echo($x7kalpartnerid) ?>/ui_conf_id/<?php echo($x7uiconfid); ?>/entry_id/<?php echo($eid); ?>", "kplayer", "405", "370", "9.0.0", false, flashVars, params);
                        </script>
        </body>
</html>