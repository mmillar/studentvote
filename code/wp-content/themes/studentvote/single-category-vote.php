<?php
get_header();
?>
<div id="page-header">
	<div class="background">
		<div class="title">
			&middot; <?php the_title(); ?> &middot;
		</div>
	</div>
</div>

<div id="debate-section">
    <div class="nav-bar">
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/the-issue/")?"":"in") ?>active the-issue" onclick="window.location='<?php bloginfo('url'); ?>/debates'"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/question/")?"":"in") ?>active question" onclick="window.location='<?php bloginfo('url'); ?>/leaders'"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/debate/")?"":"in") ?>active debate" onclick="window.location='<?php bloginfo('url'); ?>/candidates'"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/comment/")?"":"in") ?>active comment" onclick="window.location='<?php bloginfo('url'); ?>/survey'"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/vote/")?"":"in") ?>active vote" onclick="window.location='<?php bloginfo('url'); ?>/contest'"></div>
	</div>
</div>

<div id="centered-column" style="clear:both;">
<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

<?php the_content(__('(more...)')); ?>

<!-- <div id="debate-next-button">
	<a href="<?php echo(get_post_meta($post->ID, 'next-page', true)); ?>">Next Page</a>
</div>
 -->
<?php endwhile; ?>        
<?php else: ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
</div>
<?php
get_footer();
?>