=== Plugin Name ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: comments, custom, fields
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 0.1.2

Allows for the creation of additional comment fields per post type

== Description ==

Create additional custom comment fields per post type, including built in post types.

== Installation ==

1. Upload `custom-comment-fields` and its contents to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to **Settings &gt; Custom Comment** Fields to manage your comment fields
1. Implement `get_comment_meta(%FIELD_NAME%)` replacing `%FIELD_NAME%` with the `name` provided in **Settings &gt; Custom Comment Fields**

== Frequently Asked Questions ==

= I can't edit the submissions for my custom fields =

This feature is in the works

= My custom fields aren't showing up in my comments list =

Since WordPress allows you to pull comments in many ways, this will need to be done by hand. It is suggested that you use `wp_list_comments()` with a callback that in turn uses `get_comment_meta(%FIELD_NAME%)` to pull your custom field data

== Screenshots ==

1. Main settings screen
2. Custom fields for a Custom Post Type

== Changelog ==

= 0.1.2 =
* Forced sanitization

= 0.1.1 =
* Bugfix: Undefined variable for $commenter

= 0.1 =
* Initial release