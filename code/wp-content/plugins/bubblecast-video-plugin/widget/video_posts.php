<?php
    $defaultOptions = bubblecast_get_video_posts_widget_default_options();

    // build options to be displayed in the form
    $options = get_option('bubblecast_wvp_options');
    if(empty($options)){
        $options = array();
    }
    $options = array_merge($defaultOptions, $options);

    echo $before_widget;
    echo $before_title;
    echo $options['title'];
    echo $after_title;

    $baseq = 'numberposts=' . $options['videos'] . '&order=DESC&orderby=date';
    $layout = $options['layout'];
    $use_current_cat = $options['use_current_cat'];
    $vertical = $layout == 'v';

    if(is_tag() && $use_current_cat == 'Y'){
       $q = $baseq . '&tag=' . get_query_var('tag');
    }
    else{
        if(is_category() && $use_current_cat == 'Y'){
            $categories = array(get_query_var('cat'));
        }
        else if(is_single() && $use_current_cat == 'Y'){
            $cats = get_the_category();
            foreach ($cats as $cur_cat) {
                $categories[] = $cur_cat->cat_ID;
            }
        }
        else{
            $categories = $options['categories'];
        }
        $categoryIds = bubblecast_get_cat_ids_str($categories);
        $q = $baseq . '&category=' . $categoryIds;
    }
    $posts = get_posts($q);
    if(count($posts) == 0 && $use_current_cat == 'Y'){
        $categoryIds = bubblecast_get_cat_ids_str($options['categories']);
        $q = $baseq . '&category=' . $categoryIds;
        $posts = get_posts($q);
    }

    // outputting table beginning for horizontal layout
    if (!$vertical) : ?>
        <table>
        <tr>
<?php
    endif; // $vertical

    foreach ($posts as $post) :
        
        // locating first [bubblecast] code if exists to show a thumbnail
        $video_id = bubblecast_get_video_id_from_post($post);

        $permalink = get_permalink($post->ID);
        $title = get_the_title($post->ID);

        // outputting cell beginning for horizontal layout
        if (!$vertical) : ?>
            <td>
<?php
        endif;

?>
        <div class="bubblecast_wvp_block">
            <a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>" class="wp_caption"><?php echo $title; ?></a>
            <div><a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>"><img src="<?php echo bubblecast_get_thumbnail($video_id, $post); ?>" alt="<?php echo $title; ?>" width="160" height="160"/></a></div>
        </div>
<?php

        // outputting cell end for horizontal layout
        if (!$vertical) : ?>
            </td>
<?php
        endif;

    endforeach;

    // outputting table end for horizontal layout
    if (!$vertical) : ?>
        </tr>
        </table>
<?php
    endif; // $vertical

    echo $after_widget;
                        
?>