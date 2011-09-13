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

<input type="text" name="author" id="author" value="First Name" size="22" tabindex="1" style="color:#bbb" />

<input type="text" name="lastname" id="lastname" value="Last Name" size="22" tabindex="1" style="color:#bbb" />

<input type="text" name="email" id="email" value="Email Address" size="22" tabindex="2" style="color:#bbb" />

<input type="text" name="city" id="city" value="City" size="22" tabindex="2" style="color:#bbb" />

<input type="text" name="subject" id="subject" value="Submission Title" size="22" tabindex="3" style="color:#bbb" />

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
<h2 class="comments-header">Submissions</h2>
<?php if ( $comments ) : ?>
<div id="comments-list">
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e6b75ee1d02c461"></script>

<?php foreach ($comments as $comment) : ?>
	<div id="comment-<?php comment_ID() ?>" class="contest-comment">
		<h4 class="comment-title" style="margin:0px"><?php print $comment->extra_subject; ?></h4>
		<div class="comment-text"><?php comment_text() ?></div>
		<small><?php _e('by'); ?> <?php comment_author() ?>, <?php print $comment->extra_city; ?></small>
		<div class="social-buttons">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style " addthis:url="<?php echo get_permalink( $post->ID )."#comment-"; comment_ID() ?>" addthis:title="Vote for me to win lunch with the next Premier">
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
	  if($("#author").val()=="First Name"){
		  $("#author").val("");
		  $("#author").css("color","#333");
		}
	});
	$("#lastname").focus(function() {
	  if($("#lastname").val()=="Last Name"){
		  $("#lastname").val("");
		  $("#lastname").css("color","#333");
		}
	});
	$("#email").focus(function() {
	  if($("#email").val()=="Email Address") {
		  $("#email").val("");
		  $("#email").css("color","#333");
		}  
	});
	$("#city").focus(function() {
	  if($("#city").val()=="City") {
		  $("#city").val("");
		  $("#city").css("color","#333");
		}  
	});
	$("#subject").focus(function() {
	  if($("#subject").val()=="Submission Title") {
		  $("#subject").val("");
		  $("#subject").css("color","#333");
		}  
	});
});
</script>
