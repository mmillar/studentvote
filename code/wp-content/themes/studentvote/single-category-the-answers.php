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

<div id="centered-column" style="clear:both;">
<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

<div id="player"><div class="placeholder">Choisir une video</div></div>

<div id="the-answers">
<?php the_content(__('(more...)')); ?>
</div>

<script type="text/javascript">
$f("player", "http://releases.flowplayer.org/swf/flowplayer-3.2.7.swf",{ 
	clip:{ 
		scaling: 'orig'
	}
});
function loadMovie(src){
      $f().play(src);
}
</script>
<?php endwhile; ?>        
<?php else: ?>

<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>

<?php endif; ?>
</div>
<?php
get_footer();
?>