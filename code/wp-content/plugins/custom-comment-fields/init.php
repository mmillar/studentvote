<?php

/*
Plugin Name: Custom Comment Fields
Plugin URI: http://mondaybynoon.com/
Description: Allows for the creation of additional comment fields on a per-post-type basis.
Author: Iron to Iron | Jonathan Christopher
Version: 0.1.2
Author URI: http://mondaybynoon.com/
*/

wp_enqueue_script( 'jquery-ui-sortable' );

wp_enqueue_style( 'customcommentfields_style', WP_PLUGIN_URL . '/custom-comment-fields/style.css' );
wp_enqueue_script( 'customcommentfields_utils', WP_PLUGIN_URL . '/custom-comment-fields/utils.js' );


// =========
// = MENUS =
// =========

function custom_comment_fields_settings_page()
{
	include 'settings.php';
}

function custom_comment_fields_menu()
{
	add_options_page( 'Settings', 'Custom Comment Fields', 8, __FILE__, 'custom_comment_fields_settings_page' );
}

if( is_admin() )
{
	add_action( 'admin_menu', 'custom_comment_fields_menu', 99 );
}




// =========
// = HOOKS =
// =========

function iti_ccf_fields($fields)
{
    global $post;

    // grab all of our post types
    $args           = array(
                        'public'    => true
                        ); 
    $output         = 'objects';
    $operator       = 'and';
    $post_types     = get_post_types( $args, $output, $operator );


    // see if we have any custom fields
    if( count( $post_types ) )
    {
        foreach( $post_types as $post_type )
        {
            if( is_singular( $post_type->name ) )
            {
                $iti_ccf_key    = '_iti_ccf_' . $post_type->name;
                $custom_fields  = get_option( $iti_ccf_key );

                if( !empty( $custom_fields ) )
                {
                    foreach( $custom_fields as $custom_field )
                    {
                        $custom_field_value             = '';
                        if( isset( $commenter[$custom_field_name] ) )
                        {
                          $custom_field_value           = $commenter[$custom_field_name];
                        }
                        $custom_field_name              = $custom_field['name'];
                        $fields[$custom_field_name]     = '<p class="comment-form-' . $custom_field_name . '">' . '<label for="' . $custom_field_name . '">' . __( $custom_field['label'] ) . '</label> ' .
                              '<input id="' . $custom_field_name . '" name="' . $custom_field_name . '" type="text" value="' . esc_attr( $custom_field_value ) . '" size="30" /></p>';
                    }
                }
            }
        }
    }

    return $fields;

}

add_filter( 'comment_form_default_fields', 'iti_ccf_fields' );

function iti_add_ccf_comment_meta($comment_id)
{
    // grab all of our post types
    $args           = array(
                        'public'    => true
                        ); 
    $output         = 'objects';
    $operator       = 'and';
    $post_types     = get_post_types( $args, $output, $operator );

    // unfortunately (for the time being) we're going to have entries for all custom fields, regardless of post type
    foreach( $post_types as $post_type )
    {
        $iti_ccf_key    = '_iti_ccf_' . $post_type->name;
        $custom_fields  = get_option( $iti_ccf_key );
        if( !empty( $custom_fields ) )
        {
            foreach( $custom_fields as $custom_field )
            {
                $custom_field_name = $custom_field['name'];
                $custom_field_value = wp_kses( mysql_real_escape_string( $_POST[$custom_field_name] ) );
                add_comment_meta( $comment_id, $custom_field_name, $custom_field_value, true );
            }
        }
    }
}

add_action( 'comment_post', 'iti_add_ccf_comment_meta', 1 );