<?php
get_header();
?>
<div id="page-header">
	<div class="background">
		<div class="title">
			&middot; LEADERS - The Answers &middot;
		</div>
	</div>
</div>

<div id="centered-column" style="clear:both;">
<?php if (have_posts()) : ?>
<?php while (have_posts()) : the_post(); ?>

<div id="player"><div class="placeholder">Video Player</div></div>

<div id="the-answers">
<?php the_content(__('(more...)')); ?>
</div>

<script type="text/javascript">
      var tag = document.createElement('script');
      tag.src = "http://www.youtube.com/player_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      function onYouTubePlayerAPIReady() {
      }

      var player = null;
      function loadMovie(movieid){
      	$(".placeholder").css("display","none");
      	if(player!=null){
      		player.loadVideoById(movieid);
      	} else {
	        player = new YT.Player('player', {
	          height: '390',
	          width: '640',
	          videoId: movieid,
	          events: {
	            'onReady': onPlayerReady
	          }
	        });
      	}
      }

      function onPlayerReady(event) {
        event.target.playVideo();
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