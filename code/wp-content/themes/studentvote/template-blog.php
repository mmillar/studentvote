<?php
/*
Template Name: Blogs
*/
get_header(); ?>
 <div id="page-header">
  <div class="background">
    <div class="title">
      &middot; Blogs &middot;
    </div>
  </div>
</div>

  <div id="centered-column" style="clear:both;">
  <?php 
    query_posts('cat=15');
    if (have_posts()): ?>
    
  <div id="blogs">

  <?php while (have_posts()) : the_post(); ?>
  
    <div class="blog-item" id="post-<?php the_ID(); ?>">
      <div class="blog-image">
        <?php the_post_thumbnail(); ?>
      </div>
      <div class="blog-details">
        <h2 class="postTitle"><?php the_title(); ?></h2>
        <small><?php the_date(); ?> by <?php the_author(); ?></small>

        <div class="post"><?php the_content(__('(more...)')); ?></div>
        <p class="postMeta">Category: <?php the_category(', ') . " " . the_tags(__('Tags: '), ', ', ' | '); ?></p>
      </div>
    </div>

  <?php endwhile; ?>

  </div>
  	
  <?php else: ?>

    <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

  <?php endif; ?>
  </div>

  <?php
  get_footer();
?>