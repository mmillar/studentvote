<?php
/*
Template Name: Blank
*/
get_header();
  if (have_posts()) : while (have_posts()) : the_post();
  ?>
<div id="page-header">
	<div class="background">
		<div class="title">
			&middot; <?php the_title(); ?> &middot;
		</div>
	</div>
</div>


<div class="postWrapper" id="post-<?php the_ID(); ?>">

  <div class="post"><?php the_content(__('(more...)')); ?></div>
</div>

<?php
endwhile; else: ?>

<p>Sorry, no pages matched your criteria.</p>

<?php
  endif;
  get_footer();
?>