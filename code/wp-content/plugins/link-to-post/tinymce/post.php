		<p class="showFilter"><a href="javascript:showFilter()"><?php _e('Show filters','link2post'); ?></a></p>
		<fieldset class="filter">
			<legend><?php _e('Filter posts','link2post'); ?></legend>
			<form action="" method="GET" id="fc">
				<input type="hidden" name="type" id="type" value="post" />
				<p>
					<label for="tri"><?php _e('Content','link2post'); ?></label>
					<input type="text" name="tri" id="tri" value="<?php if(strlen($validate)>1 || $bFirstAndSelect) echo stripslashes($tri); ?>"/>
					<select name="where" id="where">
						<option value="title" <?php if($where == 'title') echo 'selected="selected"'; ?>><?php _e('in title','link2post'); ?></option>
						<option value="content" <?php if($where == 'content') echo 'selected="selected"'; ?>><?php _e('in content','link2post'); ?></option>
						<option value="both" <?php if($where == 'both' || strlen($where) == 0) echo 'selected="selected"'; ?>><?php _e('in both','link2post'); ?></option>
					</select>
					<select name="category" id="category">
					<?php foreach($categories as $cat){ if($cat->category_count == 0) continue; ?>
						<option value="<?php echo $cat->term_id; ?>" <?php if($category == $cat->term_id) echo 'selected="selected"'; ?>><?php echo $cat->name; ?></option>
					<?php } ?>
						<option value="-1" <?php if($category == -1 || strlen($category) == 0) echo 'selected="selected"'; ?>><?php _e('all categories','link2post'); ?></option>
					</select>
				</p>
				<p id="validate">
					<input type="submit" class="mceButton" name="validate" id="validate" value="<?php _e('Search','link2post'); ?>" />
					<?php if(strlen($tri)>0 && (strlen($validate)>1 || $bFirstAndSelect)){ ?><a href="linktopost.php?type=post"><?php _e('Cancel','link2post'); ?></a><?php } ?>
					<a href="javascript:hideFilter()"><?php _e('Hide filters','link2post'); ?></a>
				</p>
			</form>
		</fieldset>
<?php
$sql = $tables = '';
if(strlen($validate)>1 || $bFirstAndSelect){
	if(strlen($tri)>0){
		$mots = explode(' ',trim($tri));
		switch($where){
			case 'title':
				if(count($mots)>1){
					$sql = ' AND ';
					foreach($mots as $key=>$mot){
						if($key == 0) $sql .= ' ( ';
						else $sql .= ' AND ';
						$sql .= ' post_title LIKE "%'.WPLinkToPost::secure_sql($mot).'%" ';
						if($key == count($mots) - 1) $sql .= ' ) ';
					}
				}
				else
					$sql = ' AND post_title LIKE "%'.WPLinkToPost::secure_sql($tri).'%" ';
				
			break;
			case 'content':
				if(count($mots)>1){
					$sql = ' AND ';
					foreach($mots as $key=>$mot){
						if($key == 0) $sql .= ' ( ';
						else $sql .= ' AND ';
						$sql .= ' post_content LIKE "%'.WPLinkToPost::secure_sql($mot).'%" ';
						if($key == count($mots) - 1) $sql .= ' ) ';
					}
				}
				else
				$sql = ' AND post_content LIKE "%'.WPLinkToPost::secure_sql($tri).'%" ';
			break;
			case 'both':
				if(count($mots)>1){
					$sql = ' AND ';
					foreach($mots as $key=>$mot){
						if($key == 0) $sql .= ' ( ';
						else $sql .= ' AND ';
						$sql .= ' ( post_title LIKE "%'.WPLinkToPost::secure_sql($mot).'%" OR post_content LIKE "%'.WPLinkToPost::secure_sql($mot).'%" ) ';
						if($key == count($mots) - 1) $sql .= ' ) ';
					}
				}
				else
				$sql = ' AND ( post_title LIKE "%'.WPLinkToPost::secure_sql($tri).'%" OR post_content LIKE "%'.WPLinkToPost::secure_sql($tri).'%" ) ';
			break;
		}
	}
	switch($_REQUEST['category']){
		case -1:
			
		break;
		default:
			$tables = ', '.$wpdb->terms.' as t, '.$wpdb->term_taxonomy.' as tt,'.$wpdb->term_relationships.' as tr ';
			$sql .= ' AND ID = object_id AND t.term_id = tt.term_id AND t.term_id = "'.WPLinkToPost::secure_sql($category).'" AND tt.term_taxonomy_id = tr.term_taxonomy_id ';
	}
}
$result = $wpdb->get_results('SELECT COUNT( * ) AS num_posts FROM '.$wpdb->posts.$tables.' WHERE  post_type = "POST" '.$sql.' AND post_status = "publish" ');
$nb = $result[0]->num_posts;
$number = 15;
if(!isset($_GET['page'])){ $page = 1; }
else{ $page = $_GET['page']; }
$offset = $number * ($page-1);
$nbpages = ceil($nb/$number);
$posts = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.$tables.' WHERE post_type = "POST" '.$sql.' AND post_status = "publish" ORDER BY post_date desc LIMIT '.$offset.','.$number.'');
			if(count($posts)>0){
				pages($nb,$nbpages,$page,$where,$tri,$category,$type);
				echo '<ul id="liens">';
				foreach($posts as $post){
					$GLOBALS['post'] = $post;
					$local_post_id = $post->ID;
					$local_permalink = get_permalink($local_post_id);
					$local_post_title = get_the_title($local_post_id);					
					echo '<li><a href="'.$local_permalink.'"  id="'.$local_post_id.'" onclick="return insertPostLink(this,\''.$nofollow.'\',\''.$shortcode.'\')" >'.$local_post_title.'</a></li>';
				}
				echo '</ul>';
			}
			else{
				?><p><span class="results"><?php _e('No post','link2post'); ?></span></p><?php	
			}
?>