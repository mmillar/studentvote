<?php if (!defined("WP_ADMIN")) die();?>
<?php if ($viewData["uiConfId"]): ?>
<script type="text/javascript">
	var playerWidth = "<?php echo $viewData["playerWidth"]; ?>";
	var playerHeight = "<?php echo $viewData["playerHeight"]; ?>";
	var uiConfId = "<?php echo $viewData["uiConfId"]; ?>";
	var entryId = "<?php echo $viewData["entryId"]; ?>";
	var addPermission = "<?php echo $viewData["addPermission"]; ?>";
	var editPermission = "<?php echo $viewData["editPermission"]; ?>";

	var htmlArray = [];
	htmlArray.push('[');
	htmlArray.push('kaltura-widget ');
	htmlArray.push('uiconfid="'+uiConfId+'" '); 
	htmlArray.push('entryid="'+entryId+'" ');
	htmlArray.push('width="'+playerWidth+'" ');
	htmlArray.push('height="'+playerHeight+'" ');
	htmlArray.push('addpermission="'+addPermission+'" ');
	htmlArray.push('editpermission="'+editPermission+'" ');
	htmlArray.push('/]');
	htmlArray.push('\n');
	

	var html = htmlArray.join('');

	// lets make it safe
	try
	{
		var topWindow = Kaltura.getTopWindow();
	
		if (topWindow.tinyMCE && topWindow.tinyMCE.get('content') && !topWindow.tinyMCE.get('content').isHidden()) 
		{
			topWindow.tinyMCE.execCommand('mceInsertRawHTML', false, html);
		}
		else
		{
			if (topWindow.edInsertContent)
			{
				topWindow.edInsertContent(topWindow.document.getElementById('content'), html);
			}
			else
			{
				var content = topWindow.jQuery('#content');
				content.val(content.val() + html);
			}
		}

		<?php if (count($viewData["nextEntryIds"]) > 0): ?>
		var url =  "<?php echo KalturaHelpers::generateTabUrl(array("tab" => "kaltura_upload", "kaction" => "sendtoeditor", "firstedit" => "true", "entryIds" => $viewData["nextEntryIds"])); ?>";
		window.location.href = url;
		<?php else: ?>
		setTimeout('topWindow.tb_remove()', 0);
		<?php endif; ?>
	}
	catch(e) 
	{
		var displayEditTable = true;
	}
</script>
<div id="sendToEditor" class="kalturaTab">
	<form method="post" class="kalturaForm">
		<table class="form-table" style="display: none;">
			<tr>
				<td>
					<b>We were unable to insert the player code into the editor. Please copy and paste the code as it appears below.</b>
					<br />
					<br />
					<textarea id="txtCode" rows="3" style="width: 90%" readonly="readonly"></textarea>
					<br />
					<br />
					<center>
						<input type="button" value="<?php echo attribute_escape( __( 'Close' ) ); ?>" onclick="setTimeout('topWindow.tb_remove()', 0);" name="close" class="button-secondary" />
					</center>
				</td>
			</tr>
		</table>
	</form>
</div>
<script>
	if (displayEditTable)
	{
		jQuery("table").show();
		jQuery("#txtCode").val(html);
	}
</script>
<?php else: ?>
<?php
	$flashVarsStr = KalturaHelpers::flashVarsToString($viewData["flashVars"]);
?>

<div id="sendToEditor" class="kalturaTab">
	<?php if (@$_GET["firstedit"] != "true"): ?>
	<div class="backDiv">
		<a href="<?php echo KalturaHelpers::generateTabUrl(array('tab' => 'kaltura_browse')); ?>"><img src="<?php echo KalturaHelpers::getPluginUrl(); ?>/images/back.gif" alt="Back"/></a>
	</div>
	<?php endif; ?>
	<form method="post" class="kalturaForm" action="<?php echo KalturaHelpers::generateTabUrl(array("tab" => "kaltura_upload", "kaction" => "sendtoeditor", "firstedit" => "true", "entryIds" => $viewData["nextEntryIds"])); ?>">
		<table class="form-table">
			<tr>
				<td valign="top" width="240">
					<div id="divKalturaPlayer"></div>
					<script type="text/javascript">
						function updateRatio() {
							var ratio = jQuery("input[name=playerRatio]:checked").val();
							if (ratio == "16:9")
							{
								jQuery("#playerWidthLarge").next().text("Large (400x290)");
								jQuery("#playerWidthMedium").next().text("Small (260x211)");
							}
							else
							{
								jQuery("#playerWidthLarge").next().text("Large (400x365)");
								jQuery("#playerWidthMedium").next().text("Small (260x260)");
							}
						}
					</script>
				</td>
				<td valign="top">
					<table class="options">
						<tr>
							<td style="padding-bottom:22px;" colspan="2">
							<?php if ($viewData["isLibrary"])?>
								<label for="ktitle">Title:</label>
								<input type="text" name="ktitle" id="ktitle" size="32" value="<?php echo $viewData["entry"]->name; ?>" style="margin-left:6px;" />
								<span style="color:red; font-size: 20px; font-weight: bold; display: none; line-height: 20px">*</span>
							</td>
						</tr>
						<tr>
							<td valign="top">
								<div class="selectBox">
									<label for="uiConfId">Select player design:</label>
									<select name="uiConfId" id="uiConfId"></select>
									<?php if ($selectedPlayerName): ?>
									<script type="text/javascript">
										embedPreviewPlayer('<?php echo $selectedPlayerName; ?>');
									</script>
									<?php endif; ?>
								</div>	
								<?php if ($viewData["entry"]->type == KalturaEntryType_MIX): ?>
								<div class="selectBox">
									<label for="addPermission">Who can add to video:</label>
									<select name="addPermission" id="addPermission">
										<option value="3" <?php echo @get_option("kaltura_permissions_add") == "3" ? "selected=\"selected\"" : ""; ?>>Blog Administrators</option>
										<option value="2" <?php echo @get_option("kaltura_permissions_add") == "2" ? "selected=\"selected\"" : ""; ?>>Blog Editors/Contributors & Authors</option>
										<option value="1" <?php echo @get_option("kaltura_permissions_add") == "1" ? "selected=\"selected\"" : ""; ?>>Blog Subscribers</option>										
										<option value="0" <?php echo @get_option("kaltura_permissions_add") == "0" ? "selected=\"selected\"" : ""; ?>>Everybody</option>
									</select>
								</div>
								<div class="selectBox">
									<label for="editPermission">Who can edit the video:</label>
									<select name="editPermission" id="editPermission">
										<option value="3" <?php echo @get_option("kaltura_permissions_edit") == "3" ? "selected=\"selected\"" : ""; ?>>Blog Administrators</option>
										<option value="2" <?php echo @get_option("kaltura_permissions_edit") == "2" ? "selected=\"selected\"" : ""; ?>>Blog Editors/Contributors & Authors</option>
										<option value="1" <?php echo @get_option("kaltura_permissions_edit") == "1" ? "selected=\"selected\"" : ""; ?>>Blog Subscribers</option>										
										<option value="0" <?php echo @get_option("kaltura_permissions_edit") == "0" ? "selected=\"selected\"" : ""; ?>>Everybody</option>
									</select>
								</div>
								<?php else: ?>
								<input type="hidden" name="addPermission" value="-1" />
								<input type="hidden" name="editPermission" value="-1" />
								<?php endif; ?>
							</td>
							<td valign="top" style="padding-left:25px;">
								<strong>Player Dimensions:</strong>
								<div class="playerRatioDiv">
									<span><input type="radio" class="iradio" name="playerRatio" id="playerRatioNormal" onclick="updateRatio();" value="4:3" checked="checked" /><label for="playerRatioNormal">Normal</label></span>&nbsp;&nbsp;
									<span><input type="radio" class="iradio" name="playerRatio" id="playerRatioWide" onclick="updateRatio();" value="16:9" <?php echo $wideScreenDisabled; ?>/><label for="playerRatioWide">Widescreen</label></span>
								</div>
								<strong>Select player size:</strong>
								<div class="radioBox">
									<input type="radio" class="iradio" name="playerWidth" id="playerWidthLarge" value="400" checked="checked" /><label for="playerWidthLarge"></label><br />
								</div>
								<div class="radioBox">
									<input type="radio" class="iradio" name="playerWidth" id="playerWidthMedium" value="260" /><label for="playerWidthMedium"></label>
								</div>
								<div class="radioBox">
									<input type="radio" class="iradio" name="playerWidth" id="playerWidthCustom" value="" /><label for="playerCustomWidth">Custom width</label>
									<input type="text" name="playerCustomWidth" id="playerCustomWidth" maxlength="3" size="3" />
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php if ($viewData["entry"]->type == KalturaEntryType_MIX): ?>
		<p class="note"><?php _e('Note:  If your custom player includes the "Edit" and/or "Upload" actions make sure you allow users to edit this video.'); ?></p>
		<?php elseif ($viewData["entry"]->type == KalturaEntryType_MEDIA_CLIP): ?>
		<p class="note"><?php _e('Note: Make sure you do not use a player that includes the "Edit" and/or "Upload" actions, as this video is not editable.'); ?></p>
		<?php endif; ?>
		<p class="submit">
			<input type="submit" value="<?php echo attribute_escape( __( 'Insert into Post' ) ); ?>" name="sendToEditorButton" class="button-secondary" />
		</p> 
	</form>			
</div>
<script type="text/javascript">
	updateRatio();
	
	jQuery("#playerCustomWidth").click(function(){
		jQuery(this).siblings("[type=radio]").attr("checked", "checked");
	});
	
	jQuery("input[type=submit]").click(function () {
			jQuery("#ktitle").css("border-color", "").siblings("span").hide();
			if (jQuery("#ktitle").val().replace(/ /g, "").length == 0) {
				jQuery("#ktitle").css("border-color", "red").siblings("span").show();
				return false;
			}
			jQuery("#playerWidthCustom").val(jQuery("#playerCustomWidth").val());
			if (jQuery("#playerWidthCustom").attr("checked")) 
			{
				customWidth = jQuery("#playerCustomWidth").val();
				if (!customWidth.match(/^[0-9]+$/)) 
				{
					jQuery("#playerCustomWidth").css("background-color", "red");
					return false;
				}
			}
			return true;
	});

	jQuery().kalturaPlayerSelector({
		url: '<?php echo KalturaHelpers::getPluginUrl() ?>/ajax_get_players.php',
		defaultId: '<?php echo get_option("kaltura_default_player_type"); ?>',
		swfBaseUrl: '<?php echo KalturaHelpers::getSwfUrlForWidget(); ?>',
		previewId: 'divKalturaPlayer',
		entryId: '<?php echo $viewData["entry"]->id; ?>',
		playersList: '#uiConfId',
		dimensions: 'input[name=playerRatio]',
		submit: 'input[name=sendToEditorButton]',
		onSelect: function() {
			fixHeight();
		}
	});

	function fixHeight() {
		var topWindow = Kaltura.getTopWindow();
		topWindow.Kaltura.animateModalSize(680,jQuery("#sendToEditor").height() + 70);
	}

	fixHeight();
</script>
<?php endif; ?>