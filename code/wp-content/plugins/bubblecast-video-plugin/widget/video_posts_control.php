<?php

/**
 * This is included when Bubblecast Video Posts widget controls in admin console
 * are rendered and when their settings are being saved.
 */

    // building the default options
    $defaultOptions = bubblecast_get_video_posts_widget_default_options();

    // if updated, save options to the DB
    if (isset($_POST['bubblecast_wvp_layout'])) {
        $options = $defaultOptions;
        $options['title'] = $_POST['bubblecast_wvp_title'];
        $options['layout'] = $_POST['bubblecast_wvp_layout'];
        $options['videos'] = $_POST['bubblecast_wvp_videos'];
        $options['categories'] = $_POST['bubblecast_wvp_categories'];
        $options['use_current_cat'] = $_POST['bubblecast_wvp_current_cat'];
        update_option('bubblecast_wvp_options', $options);
    }

    // build options to be displayed in the form
    $options = get_option('bubblecast_wvp_options');
    if(empty($options)){
        $options = array(); 
    }
    $options = array_merge($defaultOptions, $options);
    $categories = get_categories(array('hide_empty' => false));
?>
    <p>
        <label for="bubblecast_wvp_title"><?php _e('Title', 'bubblecast');?>:</label>
        <input name="bubblecast_wvp_title" id="bubblecast_wvp_title" class="widefat" value="<?php echo $options['title']; ?>"/>
    </p>
    <p>
        <label for="bubblecast_wvp_layout"><?php _e('Layout', 'bubblecast');?>:</label>
        <select name="bubblecast_wvp_layout" id="bubblecast_wvp_layout" class="widefat">
            <option value="v" <?php selected($options['layout'], 'v') ?>><?php _e('Vertical', 'bubblecast');?></option>
            <option value="h" <?php selected($options['layout'], 'h') ?>><?php _e('Horizontal', 'bubblecast');?></option>
        </select>
    </p>
    <p>
        <label for="bubblecast_wvp_videos"><?php _e('Videos', 'bubblecast');?>:</label>
        <select name="bubblecast_wvp_videos" id="bubblecast_wvp_videos" class="widefat">
        <?php
            for ($i = 1; $i <= 10; $i++) { ?>
                <option value="<?php echo $i ?>" <?php selected($options['videos'], $i) ?>><?php echo $i;?></option>
            <?php }
        ?>
        </select>
    </p>
    <p>
        <label for="bubblecast_wvp_categories[]"><?php _e('Categories', 'bubblecast');?>:</label>
        <select name="bubblecast_wvp_categories[]" id="bubblecast_wvp_categories" class="widefat" multiple="multiple" size="5" style="height: auto;">
        <?php
            foreach ($categories as $category) { ?>
                <option value="<?php echo $category->cat_ID ?>" <?php if (in_array($category->cat_ID, $options['categories'])) {echo 'selected="selected"';} ?>><?php echo $category->name; echo " ({$category->count})";?></option>
            <?php }
        ?>
        </select>
    </p>
    <p>
        <label for="bubblecast_wvp_current_cat"><?php _e('Get videos from the context');?>:</label>
        <select name="bubblecast_wvp_current_cat" id="bubblecast_wvp_videos" class="widefat">
            <option value="Y" <?php selected($options['use_current_cat'], 'Y') ?>><?php _e('Yes', 'bubblecast');?></option>
            <option value="N" <?php selected($options['use_current_cat'], 'N') ?>><?php _e('No', 'bubblecast');?></option>
        </select>
    </p>