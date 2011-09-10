<?php
/*
Plugin Name: Link to Post
Plugin URI: http://julienappert.com/developpements-wordpress/plugin-link-to-post
Author: Julien Appert
Author URI: http://julienappert.com
Version: 1.0.2
Description: This plugin allows you to easily create a link to an existing article, page, category or tag.
*/

class WPLinkToPost{

	function WPLinkToPost(){$this->__construct();}
		
	function __construct(){
		add_action('init', array(&$this,'init'));
		add_action('admin_menu', array(&$this,'adminpage'));
		add_action('admin_print_scripts',array(&$this,'adminjavascript'));
		add_action( 'edit_form_advanced', array(&$this,'quicktags'));
		add_action( 'edit_page_form', array(&$this,'quicktags'));	
		add_shortcode('link2post', array($this,'shortcode_func'));
	}
	
	function secure_sql($sql){
		return mysql_real_escape_string($sql);
	}	

	function shortcode_func($atts, $content = null) {
		$permalink = get_permalink($atts['id']);
		$title = get_the_title($atts['id']);
		$nofollow = get_option('pl_nofollow');
		if($nofollow == 'on') $rel = ' rel="nofollow"';
		else $rel = '';
		return '<a href="'.$permalink.'" title="'.$title.'"'.$rel.'>'.$content.'</a>';
	}
	
	
	function option($name,$value){
		if(strlen($value)==0) $value = 'off';
		if(get_option($name) == FALSE)
			add_option($name,$value);
		else
			update_option($name,$value);
	}

	function init(){
		$locale = get_locale ();
		if ( empty($locale) )
			$locale = 'en_US';

		$mofile = dirname (__FILE__)."/locale/$locale.mo";
		load_textdomain ('link2post', $mofile);
		$this->addbuttons();
	}

	function addbuttons() {
	   // Don't bother doing this stuff if the current user lacks permissions
	   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		 return;
	 
	   // Add only in Rich Editor mode
	   if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this,"add_tinymce_plugin"));
			add_filter('mce_buttons', array(&$this,'register_button'));
	   }
	}
 
	function register_button($buttons) {
	   array_push($buttons, "separator", "post_link");
	   return $buttons;
	}
 
	function add_tinymce_plugin($plugin_array) {
	   $plugin_array['link2post'] = get_bloginfo('wpurl').'/wp-content/plugins/link-to-post/tinymce/editor_plugin.js';
	   return $plugin_array;
	}

	function adminjavascript(){
		$type = get_option('pl_defaultab');
		if(!$type) $type = 'post';
		?>
		<script type="text/javascript" src="<?php bloginfo('wpurl'); ?>/wp-content/plugins/link-to-post/link2post.js"></script>
		<script type="text/javascript">
		//<![CDATA[
		function edlinktopost() {
			var content = getContentSelection(window);
		   tb_show("<?php _e('Link a post','link2post'); ?>","<?php bloginfo('wpurl'); ?>/wp-content/plugins/link-to-post/linktopost.php?type=<?php echo $type; ?>&amp;tri="+content+"&amp;validate=1&amp;where=both&amp;category=-1&amp;TB_iframe=true",false);
		}
		//]]>
		</script>	
		<?php
	}

	function quicktags(){
		$buttonshtml = '<input type="button" class="ed_button" onclick="edlinktopost(); return false;" title="' . __('Link a post','link2post') . '" value="' . __('Link a post','link2post') . '" />';
		?>
		<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		   (function(){
			  if (typeof jQuery === 'undefined') {
				 return;
			  }
			  jQuery(document).ready(function(){
				 jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
			  });
		   }());
		// ]]>
		</script>
		<?php
	}

	function adminpage() {	
		add_options_page('Link to post', 'Link to post', 8, __FILE__, array(&$this,'optionpage'));	
	}

	function optionpage(){
		if(isset($_POST['Submit'])){
			$this->option('pl_select',$_POST['select']);
			$this->option('pl_nofollow',$_POST['nofollow']);
			$this->option('pl_shortcode',$_POST['shortcode']);
			$this->option('pl_defaultab',$_POST['defaultab']);
		}
		$select = get_option('pl_select');
		$nofollow = get_option('pl_nofollow');
		$shortcode = get_option('pl_shortcode');
		$defaultab = get_option('pl_defaultab');
		?>
		<div class="wrap">
			<h2><?php echo _e('Options of "Link to post"','link2post'); ?></h2>
			
			<h3><?php _e('Configuration','link2post'); ?></h3>
				<form action="" method="post">
					<p>
						<label for="defaultab"><?php _e('Default tab','link2post'); ?></label>
						<select name="defaultab" id="defaultab">
							<option value="post" <?php if($defaultab == 'post') echo 'selected="selected"'; ?>><?php _e('post','link2post'); ?></option>
							<option value="page" <?php if($defaultab == 'page') echo 'selected="selected"'; ?>><?php _e('page','link2post'); ?></option>
							<option value="category" <?php if($defaultab == 'category') echo 'selected="selected"'; ?>><?php _e('category','link2post'); ?></option>
							<option value="tag" <?php if($defaultab == 'tag') echo 'selected="selected"'; ?>><?php _e('tag','link2post'); ?></option>
						</select>
					</p>
					<p>
						<input type="checkbox" name="select" id="select" <?php if($select == 'on') echo 'checked="checked"'; ?>/>
						<label for="select"><?php _e('Search with selected text','link2post'); ?></label>
					</p>
					<p>
						<input type="checkbox" name="nofollow" id="nofollow" <?php if($nofollow == 'on') echo 'checked="checked"'; ?>/>
						<label for="nofollow"><?php _e('Add the nofollow attribute','link2post'); ?></label>
					</p>
					<p>
						<input type="checkbox" name="shortcode" id="shortcode" <?php if($shortcode == 'on') echo 'checked="checked"'; ?>/>
						<label for="shortcode"><?php _e('Use the shortcode','link2post'); ?></label> <strong><?php _e("(use with caution : if you disable the plugin, shortcode won't generate links anymore)","link2post"); ?></strong>
					</p>					
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save changes','link2post'); ?>" />
					</p>
				</form>
		</div>
		<?php
	}
 
}

new WPLinkToPost();

?>