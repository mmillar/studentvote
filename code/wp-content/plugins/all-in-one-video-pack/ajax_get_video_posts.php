<?php 
	define('DOING_AJAX', true);
	require_once('../../../wp-load.php');
	require_once('settings.php');
	require_once('lib/kaltura_helpers.php');
	require_once("lib/kaltura_model.php");
	require_once("lib/kaltura_wp_model.php");
	
	$page_size = 5;
	
	$page = (integer)(@$_GET["page"]);
	if ($page < 1)
		$page = 1;
		
	$widgets = KalturaWPModel::getLastPublishedPostWidgets($page, $page_size);
	$total_count = KalturaWPModel::getLastPublishedPostWidgetsCount();

	if ($page * $page_size >= $total_count)
		$last_page = true;
		
	if ($page == 1)
		$first_page = true;
	
	echo '<div id="kaltura-video-posts">';
	if ($widgets) 
	{
		echo '<ul id="kaltura-items">';
		foreach($widgets as $widget)
		{
			$post_id = $widget["post_id"];
			$post = &get_post($post_id);
			$user = get_userdata($post->post_author);
			echo '<li>';
			echo '<div class="thumb">';
			echo '<a href="'.get_permalink($post_id).'">';
			echo '<img src="'.KalturaHelpers::getThumbnailUrl($widget["id"], $widget["entry_id"], 120, 90, null).'" width="120" height="90" />';
			echo '</a>';
			echo '</div>';
			echo '<a href="'.get_permalink($post_id).'">'.$post->post_title.'</a><br />';
			echo $user->display_name . ", " . mysql2date("M j", $widget["created_at"]);
			echo '</li>';
		}
		echo '</ul>';
		
		echo '<ul class="kaltura-sidebar-pager">';
		echo '	<li>';
		if (!$first_page)
			echo '<a onclick="Kaltura.switchSidebarTab(this, \'posts\','.($page - 1).');">Newer</a>';
		else
			echo '&nbsp;';
		echo '	</li>';
		echo '	<li>';
		if (!$last_page)
			echo '<a onclick="Kaltura.switchSidebarTab(this, \'posts\','.($page + 1).');">Older</a>';
		else
			echo '&nbsp;';
		echo '	</li>';
		echo '</ul>';
	}
	else
	{
		echo 'No posted videos yet';
	}
	echo '</div>';
?>