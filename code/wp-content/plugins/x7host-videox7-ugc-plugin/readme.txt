=== x7Host's Videox7 UGC Plugin ===
Contributors: rtcwp07, Kaltura
Donate link: http://public.exseven.com/wordpress/
Tags: plugin, admin, images, html5, mobile, user, generated, content, x7host, videox7, posts, Post, comments, kaltura, participate, media library, edit, camera, podcast, record, vlog, video editor, video responses, video blog, audio, media, flickr, Facebook, mix, mixing, remix, collaboration, interactive, richmedia cms, webcam, ria, CCMixter, Jamendo, rich-media, picture, editor, player, video comments, New York Public Library, photo, video, all in one, playlist, video gallery, gallery, widget, all-in-one, transcoding, encoding, advertising, video ads, video advertising
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 2.5.3.4

You, your logged in Wordpress users, and your blog visitors get cutting edge online video capabilities.  Webcam comments, collaborative editing, and more!

== Description ==

This plugin is a fork of Kaltura's original "All In One Video Pack" plugin, enhanced in many ways and designed from the bottom up to be easily integrated with the self-hosted Kaltura Community Edition.

To use this plugin, you need either: 1) A Kaltura Community Edition v3.0 self-hosted server (get a cloud video server today from our friends at x7Host, www.x7host.com), or 2) An SaaS account with Kaltura.com (free trial for up to 10 GB of video)

This is not just another video embed tool - it includes every functionality you might need for video and rich-media, including the ability to upload/ record/import videos directly to your post, edit and remix content with both a standard and advanced video editor, enable video and webcam comments, manage and track your video content, create and edit playlists and much more.

Highlights:

* Give your logged in Wordpress users the ability to upload/edit/remix/share/post their media on your blog!  This is true user generated content!
* Upload, record from webcam and import all rich-media directly to your blog post; 
* Edit and remix videos using Kaltura's online full-featured standard (storyboard) and advanced (timeline based) editors; 
* Easily import all rich media (video, audio, pictures...) from other sites and social networks, such as Flickr, CCMixter, Jamendo, New York Public Library, any URL on the web etc.; 
* Allow readers and subscribers to add video and audio comments, and to participate in collaborative videos; 
* Manage and track interactive videos through the management console; 
* Enable video advertising (requires additional configuration)
* Sidebar widget displaying thumbnails of recent videos and video comments
* Complete administrative capabilities. You decide who can add and edit each video; 
* Supports more than 150 video, audio and image file formats and codecs 
* Choose your preferred video player style for each player you embed
* Custom sizing of the video player 
* Update thumbnail of video by selecting frame from video
* Advanced sharing options for videos 
* Sidebar widget showing all recent videos posted and video comments.
* Easy installation that takes just 4 steps and a few minutes. 

Showcase your blog, see examples and pictures of the plugin and get support in our forum: http://public.exseven.com/wordpress/

== Installation ==

If you are installing this plugin for the first time:

1. Download and extract the plugin zip file to your local machine
2. Paste the 'x7host-videox7-ugc-plugin' directory under the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in the WordPress admin application
4. Go to Settings > x7 UGC Settings to setup the plugin
5. IMPORTANT!  If you are using your own KalturaCE server, you MUST edit the "settings.php" file and change the variables "KALTURA_SERVER_URL" and "KALTURA_CDN_URL" to be the URL of your KalturaCE server (without backslash at the end) - do this BEFORE configuring/connecting the plugin through the "x7 UGC Settings" page!

If you are upgrading your current version of the plugin, or if you're upgrading from the Interactive Video plugin: 

1. Deactivate and uninstall the plugin through the 'Plugins' menu in the WordPress admin application
2. Download the latest version
3. Follow the installation steps above
4. If plugin is not functioning correctly, you may need to manually remove all wp_options database table entries beginning with "x7" and "kaltura_" before re-installing!

Installing the Recent Videos Sidebar Widget

1. Activate the All in One Video Pack Sidebar Widget through the 'Plugins' menu in the WordPress admin application
2. Go to Design > Widgets in the WordPress admin application, then click Add to add the Recent Videos Widget to your sidebar 

Note that videos from earlier versions of the plugin will not show up on the sidebar unless they are reposted, or you can edit them with the Kaltura video editor, resave them, and they will appear in the sidebar.

== Frequently Asked Questions ==

= I installed the plugin, but installation failed after pressing Complete Installation, showing me a text in a red rectangle? =

Cause: Either curl / curl functions is disabled on your server or your hosting blocks API calls to the Kaltura servers.

Solution 1: Enable curl and its functions on the server (or have the hosting company enable it for you).

Solution 2: Remove any blocking of external calls from the server.

= I can't activate the plugin, it presents an error message after clicking Activate on the plugin list =
It might be caused due to an old version of PHP.

This plugin is written for PHP5 with the use of classes and static members, these are not supported on earlier versions of PHP.

== Screenshots ==

1. Blog main page with video posts
2. Add a video comment
3. Add Video Screen
4. Entries Library
5. Player with interactive options of adding assets (photo, video, audio) to the video and edit
6. Create Video Posts
7. The plugin settings page
8. Video Editor