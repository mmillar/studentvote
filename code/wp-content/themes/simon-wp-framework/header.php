<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<title>
<?php if (is_home()) { echo bloginfo('name');
			} elseif (is_404()) {
			echo '404 Not Found';
			} elseif (is_category()) {
			echo 'Category:'; wp_title('');
			} elseif (is_search()) {
			echo 'Search Results';
			} elseif ( is_day() || is_month() || is_year() ) {
			echo 'Archives:'; wp_title('');
			} else {
			echo wp_title('');
			}
			?>
</title>
<meta http-equiv="content-type" content="<?php bloginfo('html_type') ?>; charset=<?php bloginfo('charset') ?>" />
<meta name="description" content="<?php bloginfo('description') ?>" />
<?php if(is_search()) { ?>
<meta name="robots" content="noindex, nofollow" />
<?php }?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url'); ?>" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<!-- header START -->
<div class="container_12">
<div id="header-wrap">
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
      </tr>
    </tbody></table>
  </div>
  <div id="nav-bar">
    <div id="navbar-left">
      <ul id="nav">
        <li><a href="<?php echo get_settings('home'); ?>">Home</a></li>
		<?php wp_list_pages('include=63,119&title_li='); ?>
      </ul>
    </div>
    <div id="navbar-right"> <a href="<?php bloginfo('rss_url'); ?>"><img src="<?php bloginfo('template_url'); ?>/images/rss.gif" alt="Subscribe to <?php bloginfo('name'); ?>" /></a>
    </div>
  </div>
  <div class="header">
    <div id="search-bar">
      <?php include (TEMPLATEPATH . '/searchform.php'); ?>
    </div>
    <h1><a href="<?php echo get_option('home'); ?>/">
      <?php bloginfo('name'); ?>
      </a></h1>
    <div class="description">
      <?php bloginfo('description'); ?>
    </div>
    <div style="clear: both"></div>
  </div>
</div>
<!-- header END -->
<div style="clear: both"></div>