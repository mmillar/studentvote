<?php
/*
Template Name: Leaders
*/
get_header(); ?>
  
  <div id="hero-section">
  	<div class="hero">
	  	<div class="left"></div>
  		<div class="middle"><?php the_post_thumbnail(); ?></div>
	  	<div class="right"></div>
  	</div>
  </div>

  

  <div id="centered-column" style="clear:both;">
  <?php 
    query_posts('cat=5');
    
    if (have_posts()): ?>
    
    <div id="leaders">
    	<ol class="leader-listing"><?php
    	
    	    while (have_posts()) : the_post(); ?>
    	
    	    <li class="leader-item" id="post-<?php the_ID(); ?>">
    	
    	      <h2 class="postTitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
    	
    	    </li>
    	
    	    <?php endwhile; ?>
    	
    	</ol>
    </div>
  	
  <?php else: ?>

    <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

  <?php endif; ?>
  </div>

  <?php
  get_footer();
?>