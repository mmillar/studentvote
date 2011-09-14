<?php
get_header();
?>
<h1 class="page-header">
	<div class="background">
		<div class="title">
			&middot; <?php the_title(); ?> &middot;
		</div>
	</div>
</h1>

<div id="debate-section">
    <div class="nav-bar">
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/the-issue/")?"":"in") ?>active the-issue"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/question/")?"":"in") ?>active question"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/debate/")?"":"in") ?>active debate"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/comment/")?"":"in") ?>active comment"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/vote/")?"":"in") ?>active vote"></div>
	</div>
</div>

<div id="centered-column" style="clear:both;">
<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

<?php the_content(__('(more...)')); ?>

<div id="next-button" onclick="window.location='<?php echo(get_post_meta($post->ID, 'next-page', true)); ?>'">
	> DISCUSS THE TOPIC
</div>
<?php endwhile; ?>        
<?php else: ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
</div>
<?php
get_footer();
?>