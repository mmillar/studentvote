<?php
/*
Template Name: Contest
*/
get_header();
  if (have_posts()) : while (have_posts()) : the_post();
  ?>
<div id="contest">
	<h1 class="page-header">
		<div class="background">
			<div class="title">
				&middot; Contest &middot;
			</div>
		</div>
	</h1>

	<div class="contest-summary" id="post-<?php the_ID(); ?>">
	  <h2 class="title"><?php the_title(); ?></h2>
	  <div class="post"><?php the_content(__('(more...)')); ?></div>
	</div>

	<?php comments_template("/contest-comments.php",false); ?>

</div>

<?php
endwhile; else: ?>

<p>Sorry, no pages matched your criteria.</p>

<?php
  endif;
  get_footer();
?>