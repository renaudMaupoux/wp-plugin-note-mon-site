<?php
/**
* Plugin Name: Note mon site V3.
* Plugin URI: https://example.com/plugins/03-note-mon-site/
* Description: This display quotes of websaite.
* Version: 1.0
* Requires at least: 5.2
* Requires PHP: 7.2
* Author: Renaud Maupoux
* Author URI: https://ateliermaupoux.com/
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: 03-note-mon-site
* Domain Path: languages/
*/
defined( 'ABSPATH' ) || die();

/*****************************************************************************************************
 * Enqueue script and style
 * Tha callback is hooked to 'wp_enqueue_script' to ensure the script is only enqueued on the front-end.
 ***************************************************************************************************/
function enqueue_related_pages_scripts_and_styles(){
    wp_enqueue_style('related-styles', plugins_url('/communs/css/style_avis.css', __FILE__));
    wp_enqueue_script('slick', plugins_url(  '/lib/slick/slick.min.js', __FILE__ ), array( 'jquery' ), true);
    wp_enqueue_script('slickinit', plugins_url(  '/communs/js/slickinit.js', __FILE__ ), array( 'jquery' ), true);
}
add_action('wp_enqueue_scripts','enqueue_related_pages_scripts_and_styles');

/*****************************************************************************************************
 * AFFICHAGE DES REGISTER-CUSTOM-POST
 ***************************************************************************************************/
require_once (dirname(__FILE__).'/inc/custom_post.php');
require_once (dirname(__FILE__).'/inc/shortcodes.php');

/*****************************************************************************************************
 * LE FORMULAIRE
 ***************************************************************************************************/

add_shortcode( 'test_form', 'test_form' );

function test_form( $atts, $content = null, $tag = 'test_form' ){   
    $admin_url = admin_url( 'admin-post.php' );
    ob_start();
    if( ! empty( $_GET['missing-fields'] ) ) {
        echo '<p>Missing fields</p>';
    }
    ?>
        <form method="POST" action="<?php echo esc_url( $admin_url ); ?>">
            <?php wp_nonce_field( 'create_review', 'create_review_nonce' );?>
            <input type="hidden" name="action" value="create_review" />
            <p>
                <label for="nom"><?php _e( 'Your name', '03-note-mon-site' ); ?></label>
                <input type="text" id="nom" name="nom" />
            </p>
            <p>
                <label for="message"><?php _e( 'Your message', '03-note-mon-site' ); ?></label>
                <textarea id="message" name="message"></textarea>
            </p>
            <p>
                <label for="rating"><?php _e( 'Your rating', '03-note-mon-site' ); ?></label>
                <select id="rating" name="rating">
                    <option value="1-0">1</option>
                    <option value="1-5">1.5</option>
                    <option value="2-0">2</option>
                    <option value="2-5">2.5</option>
                    <option value="3-0">3</option>
                    <option value="3-5">3.5</option>
                    <option value="4-0">4</option>
                    <option value="4-5">4.5</option>
                    <option value="5-0" selected="selected">5</option>
                </select>
            </p>
            <p>
                <input type="submit" value="<?php _e( 'Submit', '03-note-mon-site' ); ?>" />
            </p>
        </form>
    <?php

    if( ! empty( $_GET['success'] ) ) {
        echo '<p>Bravo you did it</p>';
    }

    $html = ob_get_clean();
    return $html;
}
/*****************************************************************************************************
 * LE FORMULAIRE security tests on input hiden Value= create review
 ***************************************************************************************************/
add_action( 'admin_post_nopriv_create_review', 'test_create_review' );
add_action( 'admin_post_create_review', 'test_create_review' );

function test_create_review(){

    $referer = wp_get_referer();
    if( empty( $_POST['nom']) || empty( $_POST['message'])){
        wp_safe_redirect( add_query_arg( 'missing-fields', 'nom_message', wp_get_referer() ) );
        exit;
    }

     if( empty( $_POST['create_review_nonce'] ) || wp_verify_nonce() ){
         wp_safe_redirect( add_query_arg( 'missing-fields', 'nonce', wp_get_referer() ) );
         exit;
     }

    $message = sanitize_text_field( $_POST['message']  );
    $nom = sanitize_text_field( $_POST['nom']  );
    $rating = sanitize_text_field( $_POST['rating']  );

    $success = wp_insert_post( 
        array(
        'post_title' => ! empty( $_POST['nom'] ) ? $nom  : '',
        'post_content' => ! empty( $_POST['message'] ) ? $message  : '',
        'meta_input' => array(
            'rating' => (string) $rating
        ),
        'post_type' => 'avis',
        //default post status : draft
        'post_status' => 'publish',
    ) );

    if( $success ){
        wp_safe_redirect( add_query_arg( 'success', '1', wp_get_referer() ) );
    } else {
        wp_safe_redirect( add_query_arg( 'failed', '1', wp_get_referer() ) );
    }
    exit;
}

add_action( 'add_meta_boxes', 'test_register_metabox' );
function test_register_metabox(){
    add_meta_box(
        'test-form-metabox',                              // ID
        __( 'Rating metabox', 'test' ),                   // Title
        'rating_metabox',                                 // Callback
        'avis',                                           // Screen. Default null.
        'side',                                           // Context. Default 'advanced'
        'low',                                            // Priority. Default 'default'
        array(                                            // Additional data passed to the callback. Default null
            'description' => __( 'Type in your awesome tagline !', '28-metabox' ),
        )
    );
}

function rating_metabox( $post ){
     $post_id = $post->ID;
     $rating = get_post_meta( $post_id, 'rating', true );

    ?>
        <p>
            <label for="rating"><?php _e( 'Your rating', 'test' ); ?></label>
            <select id="rating" name="rating">
                <option value="1-0" <?php selected( $rating, 1-0 ); ?>>1</option>
                <option value="1-5" <?php selected( $rating, 1-5 ); ?>>1.5</option>
                <option value="2-0" <?php selected( $rating, 2-0 ); ?>>2</option>
                <option value="2-5" <?php selected( $rating, 2-5 ); ?>>2.5</option>
                <option value="3-0" <?php selected( $rating, 3-0 ); ?>>3</option>
                <option value="3-5" <?php selected( $rating, 3-5 ); ?>>3.5</option>
                <option value="4-0" <?php selected( $rating, 4-0 ); ?>>4</option>
                <option value="4-5" <?php selected( $rating, 4-5 ); ?>>4.5</option>
                <option value="5-0" <?php selected( $rating, 5-0 ); ?>>5</option>
            </select>
        </p>
   <?php
   //print_r( $rating);
}

add_action( 'save_post_avis', 'rating_metabox_save', 10, 3 );
/**
 * Saves our metabox fields
 * @param  int      $post_ID  Post ID.
 * @param  WP_Post  $post     Post object.
 * @param  bool     $update   Whether this is an existing post being updated or not.
 */
function rating_metabox_save( $post_ID, $post, $update ){
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
        update_post_meta( $post_ID, 'rating', (string) $_POST['rating'] );
    }
    var_dump($_POST['rating']);
}