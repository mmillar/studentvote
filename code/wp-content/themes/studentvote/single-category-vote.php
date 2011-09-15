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
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/the-issue/")?"":"in") ?>active the-issue" tabindex=1 onclick="jumpToURL('<?php echo(get_post_meta($post->ID, 'first-page', true)); ?>');"  onkeypress="jumpToCheckedURL('<?php echo(get_post_meta($post->ID, 'first-page', true)); ?>/debates',event);"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/question/")?"":"in") ?>active question" tabindex=2 onclick="jumpToURL('<?php echo(get_post_meta($post->ID, 'second-page', true)); ?>');"  onkeypress="jumpToCheckedURL('<?php echo(get_post_meta($post->ID, 'second-page', true)); ?>/debates',event);"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/debate/")?"":"in") ?>active debate" tabindex=3 onclick="jumpToURL('<?php echo(get_post_meta($post->ID, 'third-page', true)); ?>');"  onkeypress="jumpToCheckedURL('<?php echo(get_post_meta($post->ID, 'third-page', true)); ?>/debates',event);"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/comment/")?"":"in") ?>active comment" tabindex=4 onclick="jumpToURL('<?php echo(get_post_meta($post->ID, 'fourth-page', true)); ?>');"  onkeypress="jumpToCheckedURL('<?php echo(get_post_meta($post->ID, 'fourth-page', true)); ?>/debates',event);"></div>
		<div class="<?php echo(strpos($_SERVER['REQUEST_URI'],"/vote/")?"":"in") ?>active vote" tabindex=5 onclick="jumpToURL('<?php echo(get_post_meta($post->ID, 'fifth-page', true)); ?>');"  onkeypress="jumpToCheckedURL('<?php echo(get_post_meta($post->ID, 'fifth-page', true)); ?>/debates',event);"></div>
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