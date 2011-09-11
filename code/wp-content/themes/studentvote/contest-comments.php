<?php

  /**
  *@desc Included at the bottom of post.php and single.php, deals with all comment layout
  */

  if ( !empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) :
?>
<p><?php _e('Enter your password to view comments.'); ?></p>
<?php return; endif; ?>

</p>

<div id="contest-comment-form">
<?php if ( comments_open() ) : ?>
<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.'), get_option('siteurl')."/wp-login.php?redirect_to=".urlencode(get_permalink()));?></p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<input type="text" name="author" id="author" value="<?php echo ($comment_author!=null) ? $comment_author:"Name"; ?>" size="22" tabindex="1" style="color:<?php echo ($comment_author!=null)? "#333" : "#bbb" ?>" />

<input type="text" name="email" id="email" value="<?php echo ($comment_author_email!=null) ? $comment_author_email:"E-mail"; ?>" size="22" tabindex="2" style="color:<?php echo ($comment_author_email!=null)? "#333" : "#bbb" ?>" />

<input type="text" name="url" id="url" value="<?php echo ($comment_author_url!=null) ? $comment_author_url:"Website"; ?>" size="22" tabindex="3" style="color:<?php echo ($comment_author_url!=null)? "#333" : "#bbb" ?>" />

<!--<p><small><strong>XHTML:</strong> <?php printf(__('You can use these tags: %s'), allowed_tags()); ?></small></p>-->
<?php do_action('comment_form', $post->ID); ?>

<textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea>

<input name="submit" type="submit" id="submit" tabindex="5" value="<?php echo attribute_escape(__('Submit Comment')); ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />

</form>

<?php endif; // If registration required and not logged in ?>

<?php else : // Comments are closed ?>
<p><?php _e('Sorry, the comment form is closed at this time.'); ?></p>
<?php endif; ?>
</div>

<div id="contest-comments">
<div class="comments-header">Submissions</div>
<?php if ( $comments ) : ?>
<div id="comments-list">
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e6b75ee1d02c461"></script>

<?php foreach ($comments as $comment) : ?>
	<div id="comment-<?php comment_ID() ?>" class="contest-comment">
		<div class="comment-text"><?php comment_text() ?></div>
		<small><?php _e('by'); ?> <?php comment_author_link() ?></small>
		<div class="social-buttons">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style " addthis:url="<?php echo get_permalink( $post->ID )."#comment-"; comment_ID() ?>" addthis:title="<?php echo get_comment_author() ?>'s Contest Entry">
			<a class="addthis_button_facebook"></a>
			<a class="addthis_button_twitter"></a>
			<a class="addthis_button_email"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<!-- AddThis Button END -->
		</div>
	</div>

<?php endforeach; ?>

</div>

<?php else : // If there are no comments yet ?>
	<p><?php _e('No comments yet.'); ?></p>
<?php endif; ?>
</div>
</div>

<script>
$(function(){
	$("#author").focus(function() {
	  if($("#author").val()=="Name"){
		  $("#author").val("");
		  $("#author").css("color","#333");
		}
	});
	$("#email").focus(function() {
	  if($("#email").val()=="E-mail") {
		  $("#email").val("");
		  $("#email").css("color","#333");
		}  
	});
	$("#url").focus(function() {
	  if($("#url").val()=="Website") {
		  $("#url").val("");
		  $("#url").css("color","#333");
		}  
	});
});
</script>
