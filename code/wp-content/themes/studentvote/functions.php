<?php

/**
* Wordpress Naked, a very minimal wordpress theme designed to be used as a base for other themes.
*
* @licence LGPL
* @author Darren Beale - http://siftware.co.uk - bealers@gmail.com - @bealers
* 
* Project URL http://code.google.com/p/wordpress-naked/
*/

/* initialization section */

if ( function_exists( 'add_theme_support' ) ) { 
  add_theme_support( 'post-thumbnails' ); 
}

add_action('init', 'register_custom_menu');
 
function register_custom_menu() {
  register_nav_menu('home_menu', __('Home Menu'));
  register_nav_menu('footer_menu', __('Footer Menu'));
}

add_filter('single_template', 'sv_single_template');

function sv_single_template(){
  foreach( (array) get_the_category() as $cat ) { 
    if ( file_exists(TEMPLATEPATH . "/single-category-{$cat->slug}.php") ) 
      return TEMPLATEPATH . "/single-category-{$cat->slug}.php"; 
  }
  return TEMPLATEPATH . "/single.php"; 
}

/**
* naked_nav()
*
* @desc a wrapper for the wordpress wp_list_pages() function that
* will display one or two unordered lists:
* 1) primary nav, a ul with css id #nav - always shown even if empty
* 2) Optional secondary nav, a ul with css id #subNav
*
* @todo default css provided to allow space for both nav 'bars' one below the other to stop the page jig
*
* @param obj post
* @return string (html)
*/
function naked_nav($post = 0)
{
  $output = "";
  $subNav = "";
  $params = "title_li=&depth=1&echo=0";

  // always show top level
  $output .= '<ul id="nav">';
  $output .= wp_list_pages($params);
  $output .= '</ul>';

  // second level?
  if($post->post_parent)
  {
    $params .= "&child_of=" . $post->post_parent;
  }
  else
  {
    $params .= "&child_of=" . $post->ID;
  }
  $subNav = wp_list_pages($params);

  if ($subNav)
  {
    $output .= '<ul id="subNav">';
    $output .= $subNav;
    $output .= '</ul>';
  }
  return $output;
}

/**
* @desc make the site's heading & tagline an h1 on the homepage and an h4 on internal pages
* Naked's default CSS should make the two different states look identical
*/
function do_heading()
{
  $output = "";

  if(is_home()) $output .= "<h1>"; else  $output .= "<h4>";

  $output .= "<a href='"  . get_bloginfo('url') . "'>" . get_bloginfo('name') . "</a> <span>" . get_bloginfo('description') . "</span>";

  if(is_home()) $output .= "</h1>"; else  $output .= "</h4>";

  return $output;
}

/**
* register_sidebar()
*
*@desc Registers the markup to display in and around a widget
*/
if ( function_exists('register_sidebar') )
{
  register_sidebar(array(
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget' => '</li>',
    'before_title' => '',
    'after_title' => '',
  ));
}

/**
* Check to see if this page will paginate
* 
* @return boolean
*/
function will_paginate() 
{
  global $wp_query;
  
  if ( !is_singular() ) 
  {
    $max_num_pages = $wp_query->max_num_pages;
    
    if ( $max_num_pages > 1 ) 
    {
      return true;
    }
  }
  return false;
}


/**
 * FooWidget Class
 */
class SVWidget extends WP_Widget {
	/** constructor */
	function SVWidget() {
		parent::WP_Widget( 'svwidget', $name = 'SVWidget' );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = apply_filters( 'widget_text', $instance['text'] );
    $img = apply_filters( 'widget_img', $instance['img'] );
    $url = apply_filters( 'widget_url', $instance['url'] );
		echo $before_widget; ?>
    <div onclick="window.location='<?php echo $url; ?>';">
		<img src="<?php echo $img; ?>" alt="<?php echo $title; ?> image" />
		<div class="title"><?php echo $title; ?></div>
		<div class="text"><?php echo $text; ?></div>
    </div>
		<?php echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text'] = $new_instance['text'];
    $instance['img'] = $new_instance['img'];
    $instance['url'] = $new_instance['url'];
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$text = $instance[ 'text' ];
      $image = $instance[ 'img' ];
      $url = $instance[ 'url' ];
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:'); ?></label> 
		<textarea class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
		</p>
    <p>
    <label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image:'); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('img'); ?>" type="text" value="<?php echo $image; ?>" />
    </p>
    <p>
    <label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Url:'); ?></label> 
    <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" />
    </p>
		<?php 
	}

} // class FooWidget

// register FooWidget widget
add_action( 'widgets_init', create_function( '', 'return register_widget("SVWidget");' ) );


function comment_author_with_city( $comment_ID = 0 ) {
  $author = apply_filters('comment_author', get_comment_author( $comment_ID ) );
  $comment = get_comment( $comment_ID );
  echo apply_filters('get_comment_author_with_city', $author.", ".$comment->extra_city);
}

?>