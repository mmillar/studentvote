<?php

  get_header(); query_posts('tag=featured');the_post(); ?>
  
  <div id="hero-section">
  	<div class="hero">
	  	<div class="left"></div>
  		<div class="middle" onclick="window.location='<?php the_permalink() ?>';"><?php the_post_thumbnail(); ?></div>
	  	<div class="right"></div>
  	</div>
  </div>

  

  <div id="left-column" style="clear:both;">
<?php 
  query_posts('cat=6');
  
  if (have_posts()): ?>
  
  <div id="debates">
  	<div class="debate-header"></div>

	<ol class="debate-listing"><?php
	
	    while (have_posts()) : the_post(); ?>
	
	    <li class="debate-item" id="post-<?php the_ID(); ?>" onclick="window.location='<?php the_permalink() ?>';">
	
	      <h2 class="postTitle"><?php the_title(); ?></h2>
	      <small><?php the_date(); ?></small>
	
	    </li>
	
	    <?php endwhile; ?>
	
	</ol>
  </div>
	
<?php else: ?>

  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
  </div>

  <div id="right-column">
    <?php get_sidebar(); ?>
  </div>

  <?php
  get_footer();
?>