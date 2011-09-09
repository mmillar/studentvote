<?php
/*
Template Name: Debates
*/
get_header(); ?>
 <div id="page-header">
  <div class="background">
    <div class="title">
      &middot; <?php the_title(); ?> &middot;
    </div>
  </div>
</div>

  <div id="centered-column" style="clear:both;">
  <?php 
    query_posts('cat=6');
    
    if (have_posts()): ?>
    
  <div id="debates">

  <ol class="debate-listing"><?php
  
      while (have_posts()) : the_post(); ?>
  
      <li class="debate-item-long" id="post-<?php the_ID(); ?>" onclick="window.location='<?php the_permalink() ?>';">
  
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

  <?php
  get_footer();
?>