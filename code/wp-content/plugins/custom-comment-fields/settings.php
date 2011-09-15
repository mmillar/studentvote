<?php 

    // we can't query by name... yet
    $args           = array(
                        'public'    => true
                        ); 
    $output         = 'objects';
    $operator       = 'and';
    $post_types     = get_post_types( $args, $output, $operator );

?>


<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>Custom Comment Fields Settings</h2>

    <?php if( !empty( $_GET['action'] ) && !empty( $_GET['cpt'] ) ) : ?>

        <?php if( count( $post_types ) ) : foreach( $post_types as $post_type ) : if ( $post_type->name === $_GET['cpt'] ) : ?>

            <?php $iti_ccf_key  = '_iti_ccf_' . $post_type->name; ?>

            <h3>Custom Fields for <?php echo $post_type->labels->name; ?></h3>
            <form id="iti-ccf-fields" action="options-general.php?page=custom-comment-fields/init.php" method="post">

                <table class="widefat page fixed" cellspacing="0">
                    <colgroup>
                        <col id="iti-ccf-order" />
                        <col id="iti-ccf-name" />
                        <col id="iti-ccf-label" />
                        <col id="iti-ccf-delete" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Name</th>
                            <th scope="col">Label</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Name</th>
                            <th scope="col">Label</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php $fields = get_option( $iti_ccf_key ); ?>
                        <?php if( !empty( $fields ) ) : foreach( $fields as $field ) : ?>
                            <tr class="alternate author-self status-publish iedit">
                                <th scope="row"><img src="<?php echo WP_PLUGIN_URL; ?>/custom-comment-fields/reorder.png" alt="Reorder" /></th>
                                <td><input class="text" name="iti_ccf_field_names[]" value="<?php echo $field['name']; ?>" /></td>
                                <td><input class="text" name="iti_ccf_field_labels[]" value="<?php echo $field['label']; ?>" /></td>
                                <td><a href="#" class="iti-ccf-remove">Remove</a></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr class="alternate author-self status-publish iedit">
                                <th scope="row"><img src="<?php echo WP_PLUGIN_URL; ?>/custom-comment-fields/reorder.png" alt="Reorder" /></th>
                                <td><input class="text" name="iti_ccf_field_names[]" value="" /></td>
                                <td><input class="text" name="iti_ccf_field_labels[]" value="" /></td>
                                <td><a href="#" class="iti-ccf-remove">Remove</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <input type="hidden" name="iti_ccf_key" id="iti_ccf_key" value="<?php echo $iti_ccf_key; ?>" />
                <?php wp_nonce_field( '_iti_ccf_update','_iti_ccf_update' ); ?>
                <div class="iti-buttons">
                    <p class="add">
                        <a href="#" class="button">Add Field</a>
                    </p>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e( 'Save', 'customcommentfields' );?>" />
                    </p>
                </div>
            </form>
        <?php break; endif; endforeach; endif; ?>


    <?php else : ?>

        <?php if( function_exists( 'get_post_types' ) ) : ?>

            <?php 
                if ( !empty( $_POST ) && wp_verify_nonce( $_POST['_iti_ccf_update'], '_iti_ccf_update' ) )
                {
                    $iti_ccf_key                = $_POST['iti_ccf_key'];
                    $iti_ccf_field_names        = $_POST['iti_ccf_field_names'];
                    $iti_ccf_field_labels       = $_POST['iti_ccf_field_labels'];

                    $iti_ccf_field_names_final  = array();

                    // we need to ensure that the names are sanitized (and unique to the post type)
                    foreach( $iti_ccf_field_names as $name )
                    {

                        if( !empty( $name ) )
                        {
                            $tmp_name = str_replace( '-', '_', sanitize_title( $name ) );

                            // we'll check to see if the key has already been prepended
                            if( substr( $tmp_name, 0, strlen( $iti_ccf_key ) ) != $iti_ccf_key )
                            {
                                $tmp_name = $iti_ccf_key . '_' . $tmp_name;
                            }

                            // do we have a duplicate name?
                            if( in_array( $tmp_name, $iti_ccf_field_names_final ) )
                            {
                                $tmp_name .= '_dupe';
                            }

                            // name is final
                            $iti_ccf_field_names_final[] = $tmp_name;

                            $fields = array();

                            for( $i=0; $i < count( $iti_ccf_field_names_final ); $i++ )
                            {

                                $label = $iti_ccf_field_labels[$i];

                                // if the label is empty, we'll use the name
                                if( empty( $label ) )
                                {
                                    $label = $iti_ccf_field_names_final[$i];
                                }

                                $fields[] = array(
                                                'name'      => $iti_ccf_field_names_final[$i],
                                                'label'     => $label
                                );
                            }
                        }

                        update_option( $iti_ccf_key, $fields );

                    } ?>

                        <div id="message" class="updated">
                            <p><strong>Custom comment fields updated</strong></p>
                        </div>

                    <?php
                }
            ?>

            <?php if( count( $post_types ) ) : ?>
                <br />
                <table id="iti-ccf-post-types" class="widefat page fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Label</th>
                            <th scope="col">Fields</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Label</th>
                            <th scope="col">Fields</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach( $post_types as $post_type ) : $iti_ccf_key  = '_iti_ccf_' . $post_type->name; ?>
                            <tr class="alternate author-self status-publish iedit">
                                <th scope="row">
                                    <a href="options-general.php?page=custom-comment-fields/init.php&amp;action=edit&amp;cpt=<?php echo $post_type->name; ?>"><?php echo $post_type->name; ?></a>
                                </th>
                                <td><?php echo $post_type->labels->name; ?></td>
                                <td>
                                    <?php 
                                        $fields = get_option( $iti_ccf_key ); 
                                        if( !empty( $fields ) )
                                        {
                                            $labels = array();
                                            foreach( $fields as $field )
                                            {
                                                $labels[] = $field['label'];
                                            }
                                            echo implode( ', ', $labels );
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif ?>

        <?php endif ?>

    <?php endif ?>

</div>