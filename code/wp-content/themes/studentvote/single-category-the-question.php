<?php
get_header();
?>
<div id="page-header">
	<div class="background">
		<div class="title">
			&middot; LEADERS - The Question &middot;
		</div>
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
<div id="next-button" onclick="window.location='<?php echo(get_post_meta($post->ID, 'next-page', true)); ?>'">
	> LEADER ANSWERS
</div>
<?php endwhile; ?>        
<?php else: ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
</div>
<?php
get_footer();
?>