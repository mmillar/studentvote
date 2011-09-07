<?php
	$flashVars = $viewData["flashVars"];
	$flashVarsStr = KalturaHelpers::flashVarsToString($viewData["flashVars"]);
	$flashVarsjson = json_encode($flashVars);
?>

<div id="kaltura_contribution_wizard_wrapper"></div>

<script type="text/javascript">
	var cwWidth = 680;
	var cwHeight = 360;
	
	var topWindow = Kaltura.getTopWindow();
	// fix for IE6, scroll the page up so modal would animate in the center of the window
	if (jQuery.browser.msie && jQuery.browser.version < 7)
		topWindow.scrollTo(0,0);

	//var cwSwf = new SWFObject("<?php echo $viewData["swfUrl"]; ?>", "kaltura_contribution_wizard", cwWidth, cwHeight, "9", "#000000");
	//cwSwf.addParam("flashVars", "<?php echo $flashVarsStr; ?>");
	//cwSwf.addParam("allowScriptAccess", "always");
	//cwSwf.addParam("allowNetworking", "all");
	
	var flashVars = <?php echo($flashVarsjson); ?>;
	var params = {
		allowScriptAccess:"always",
		allowNetworking:"all"
		};
	swfobject.embedSWF("<?php echo $viewData["swfUrl"]; ?>", "kaltura_contribution_wizard_wrapper", "680", "360", "9.0.0", false, flashVars, params);
</script>