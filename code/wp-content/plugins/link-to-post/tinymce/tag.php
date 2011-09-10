<p class="showFilter"><a href="javascript:showFilter()"><?php _e('Show filters','link2post'); ?></a></p>
<fieldset class="filter">
	<legend><?php _e('Filter tags','link2post'); ?></legend>
	<form action="" method="GET" id="fc">
		<input type="hidden" name="type" id="type" value="tag" />
		<p>
			<label for="tri"><?php _e('Content','link2post'); ?></label>
			<input type="text" name="tri" id="tri" value="<?php if(strlen($validate)>1 || $bFirstAndSelect) echo stripslashes($tri); ?>"/>
		</p>
		<p id="validate">
			<input type="submit" class="mceButton" name="validate" id="validate" value="<?php _e('Search','link2post'); ?>" />
			<?php if(strlen($tri)>0 && (strlen($validate)>1 || $bFirstAndSelect)){ ?><a href="linktopost.php?type=tag"><?php _e('Cancel','link2post'); ?></a><?php } ?>
			<a href="javascript:hideFilter()"><?php _e('Hide filters','link2post'); ?></a>
		</p>
	</form>
</fieldset>
<?php
 $sql = '';
if(strlen($validate)>1 || $bFirstAndSelect){
	if(strlen($tri)>0){
		$mots = explode(' ',trim($tri));
		if(count($mots)>1){
			$sql = ' AND ';
			foreach($mots as $key=>$mot){
				if($key == 0) $sql .= ' ( ';
				else $sql .= ' AND ';
				$sql .= ' name LIKE "%'.WPLinkToPost::secure_sql($mot).'%" ';
				if($key == count($mots) - 1) $sql .= ' ) ';
			}
		}
		else
			$sql = ' AND name LIKE "%'.WPLinkToPost::secure_sql($tri).'%" ';		
	}
}

$result = $wpdb->get_results('SELECT COUNT( * ) AS num_tags FROM '.$wpdb->terms.' t, '.$wpdb->term_taxonomy.' tt WHERE t.term_id = tt.term_id '.$sql.' AND taxonomy = "post_tag" ');
$nb = $result[0]->num_tags;
$number = 15;
if(!isset($_GET['page'])){ $page = 1; }
else{ $page = $_GET['page']; }
$offset = $number * ($page-1);
$nbpages = ceil($nb/$number);
$tags = $wpdb->get_results('SELECT  * FROM '.$wpdb->terms.' t, '.$wpdb->term_taxonomy.' tt WHERE t.term_id = tt.term_id '.$sql.' AND taxonomy = "post_tag" ORDER BY name asc LIMIT '.$offset.','.$number.'');
if(count($tags)>0){
	pages($nb,$nbpages,$page,$where,$tri,$category,$type);
	echo '<ul id="liens">';
	foreach($tags as $tag){		
		echo '<li><a href="'.get_tag_link($tag->term_id).'" id="tag_'.$tag->term_id.'" onclick="return insertPostLink(this,\''.$nofollow.'\',\''.$shortcode.'\')">'.$tag->name.'</a> ('.$tag->count.')</li>';
	}
	echo '</ul>';
}
else{
	?><p><span class="results"><?php _e('No tag','link2post'); ?></span></p><?php
}
?>