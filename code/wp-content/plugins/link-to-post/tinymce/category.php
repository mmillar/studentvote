<ul id="liens">
<?php foreach($categories as $categorie){
		echo '<li><a href="'.get_category_link($categorie->term_id).'" id="cat_'.$categorie->term_id.'" onclick="return insertPostLink(this,\''.$nofollow.'\',\''.$shortcode.'\')">'.$categorie->name.'</a></li>';
} ?>
</ul>