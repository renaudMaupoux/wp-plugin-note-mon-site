<?php
/***************************************************************************************************
 * METABOX CREATION USING CLASS
 ***************************************************************************************************/
class Avis_metabox_create {
    public $post;
    public function __construct(){
        add_action( 'save_post_avis', [$this,'rating_metabox_save'], 10, 3 );
        add_action('add_meta_boxes', [$this,'create_meta_boxes']);

    }
    public function create_meta_boxes(){
        add_meta_box(
            'test-form-metabox',                              // ID
            __( 'Rating metabox', 'note-mon-site' ),          // Title
            [$this, 'meta_boxes_html'],                       // Callback
            ['avis'],                                         // Screen. Default null.
            'side',                                           // Context. Default 'advanced'
            'low',                                            // Priority. Default 'default'
            array(                                            // Additional data passed to the callback. Default null
                'description' => __( 'Type in your awesome tagline !', 'note-mon-site' ),
            )
        );
    }
    public function meta_boxes_html( $post ){
        $post_id = $post->ID;
        $rating = get_post_meta( $post_id, 'rating', true );
        ?>
        <p>
            <label for="rating"><?php _e( 'Your rating', 'test' ); ?></label>
            <select id="rating" name="rating">
                <option value="1-0" <?php selected( $rating, '1-0' ); ?>>1 truc</option>
                <option value="1-5" <?php selected( $rating, '1-5' ); ?>>1.5</option>
                <option value="2-0" <?php selected( $rating, '2-0' ); ?>>2</option>
                <option value="2-5" <?php selected( $rating, '2-5' ); ?>>2.5</option>
                <option value="3-0" <?php selected( $rating, '3-0' ); ?>>3</option>
                <option value="3-5" <?php selected( $rating, '3-5' ); ?>>3.5</option>
                <option value="4-0" <?php selected( $rating, '4-0' ); ?>>4</option>
                <option value="4-5" <?php selected( $rating, '4-5' ); ?>>4.5</option>
                <option value="5-0" <?php selected( $rating, '5-0' ); ?>>5</option>
            </select>
        </p>
        <?php
    }

    public function rating_metabox_save( $post_ID, $post, $update ){
        // Check if nonce is set and valid. If not, just early return.
        // if ( ! isset( $_POST['wpcookbook_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['wpcookbook_metabox_nonce'], 'wpcookbook_metabox_save_' . $post_ID ) ) {
        //     return;
        // }
        // Check user capabilities
        if ( ! current_user_can( 'edit_post', $post_ID ) ) {
            return;
        }
        // Do not save metabox content when auto saving.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if( ! empty( $_POST['rating'] ) ){
            echo 'tratratr';
            update_post_meta( $post_ID, 'rating', (string) $_POST['rating'] );
        }
    }
}