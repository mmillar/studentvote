<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<title><?php if(is_home()) bloginfo('name'); else wp_title(''); ?></title>

	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
	</style>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />
	<link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php
    wp_get_archives('type=monthly&format=link');
    wp_enqueue_script("jquery");
    wp_head();
  ?>
    <script type="text/javascript" src="<?php bloginfo("template_url"); ?>/js/jquery-1.6.2.min.js"></script>
</head>

<body>
  <div id="canvas">

    <ul class="skip">
      <li><a href="#nav">Skip to navigation</a></li>
      <li><a href="#primaryContent">Skip to main content</a></li>
      <li><a href="#secondaryContent">Skip to secondary content</a></li>
      <li><a href="#footer">Skip to footer</a></li>
    </ul>

    <div id="header">
      <div id="top-section">
	      <table cellspacing="0" cellpadding="0" border="0" style="width:939px">
	      <tbody><tr>
	        <td width="28%"><a href="home.php"><img height="91" border="0" width="267" style="margin-top:14px;" alt="Student Vote" src="<?php bloginfo('template_url'); ?>/images/logo.jpg"></a></td>
	        <td align="right" width="72%" valign="bottom" style="padding-bottom:10px;">
	        <table cellspacing="0" cellpadding="0" border="0">
	          <tbody><tr>
	            <td align="left" style="padding-right:17px;"><a class="toplink" href="home.php"><strong>English</strong></a></td>
	            <td align="left" style="padding-right:12px;"><a class="toplink" href="http://www.voteetudiant.ca">Français</a></td>
	            <td align="left"><a target="_blank" href="http://www.facebook.com/pages/StudentVote/103385289724812?ref=ts#!/pages/StudentVote/103385289724812?v=wall&amp;ref=ts"><img height="22" border="0" width="22" alt="Facebook" src="<?php bloginfo('template_url'); ?>/images/structure/facebook.jpg"></a></td>
	            <td width="15"></td>
	            <td><a target="_blank" href="http://twitter.com/studentvote"><img height="22" border="0" width="22" alt="Twitter" src="<?php bloginfo('template_url'); ?>/images/structure/twitter.jpg"></a></td>
	            <td width="15"></td>
	            <td><a target="_blank" href="http://www.youtube.com/studentvote"><img height="22" border="0" width="19" alt="Youtube" src="<?php bloginfo('template_url'); ?>/images/structure/youtube.jpg"></a></td>
	          </tr>
	        </tbody></table>
	        </td>
	      </tr></tbody>
	      </table>
      </div>
    <div id="nav-section">
	    <div class="nav-bar">
			<div class="inactive home" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive debates" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive leaders" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive candidates" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive survey" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive contest" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive events" onclick="window.location='http://localhost/home'"></div>
			<div class="inactive blog" onclick="window.location='http://localhost/home'"></div>
		</div>
	</div>
    </div>
