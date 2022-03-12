<?php
/**
 * Plugin Name: Note mon site V3.
 * Plugin URI: https://example.com/plugins/wp-plugin-note-mon-site/
 * Description: This display quotes of websaite.
 * Version: 1.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Renaud Maupoux
 * Author URI: https://ateliermaupoux.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: note-mon-site
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
require_once (dirname(__FILE__).'/inc/class/avis_metabox_create.php');

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
            <label for="nom"><?php _e( 'Your name', 'note-mon-site' ); ?></label>
            <input type="text" id="nom" name="nom" />
        </p>
        <p>
            <label for="message"><?php _e( 'Your message', 'note-mon-site' ); ?></label>
            <textarea id="message" name="message"></textarea>
        </p>
        <p>
            <label for="rating"><?php _e( 'Your rating', 'note-mon-site' ); ?></label>
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
            <input type="submit" value="<?php _e( 'Submit', 'note-mon-site' ); ?>" />
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


new Avis_metabox_create();





// in main plugin file
add_action( 'admin_menu', 'nms_settings_menu' );


function nms_settings_menu() {
    $hookname = add_submenu_page(
        'edit.php?post_type=avis', // Parent slug
        __( 'Note mon site', '27-settings' ), // Page title
        __( 'Réglages', '27-settings' ), // Menu title
        'manage_options', // Capabilities
        'nms-page', // Slug
        'nms_menu_page_callback', // Display callback
        10 // Priority/position.
    );
    //add_action( 'load-' . $hookname, 'nms_handle_settings' );

}
// function nms_handle_settings(){
//     echo '<h1>BlaBla Bla</h1>';

// }



// in main plugin file
add_action( 'admin_init', 'wpcookbook_register_settings' );
/**
 * Registers our new settings
 */
function wpcookbook_register_settings(){
    register_setting(
        'wpcookbook', // Group Name
        'wpcookbook_text_field', // Setting Name
        array(
            'type' => 'string',// Value type
            'description' => __( 'Simple text field', '27-settings' ),// Description
            'sanitize_callback' => 'sanitize_text_field',// Sanitize callback
            'show_in_rest' => false,// Whether to make available in REST API
            'default' => '',// Default value
        )
    );
    add_settings_section(
        'wpcookbook-first-section', // Section ID
        __( 'First section', '27-settings' ), // Title
        'wpcookbook_first_section_display', // Callback
        'wpcookbook-page' // Page
    );
    add_settings_field(
        'wpcookbook_text_field', // Field ID
        __( 'Simple text field', '27-settings' ), // Title
        'wpcookbook_text_field_display', // Callback
        'wpcookbook-page', // Page
        'wpcookbook-first-section', // Section
        array(
            'label_for' => 'wpcookbook_text_field', // Label
            'class' => 'wpcookbook-text-field', // CSS Classname
        )
    );
}


function wpcookbook_first_section_display( $args ){
    printf( '<p><strong>%s</strong></p>', esc_html( $args['title'] ) );
}

function wpcookbook_text_field_display( $args ){

    $value = get_option( 'wpcookbook_text_field' ) ?: '' ;
    ?>
    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" name="wpcookbook_text_field" value="<?php echo esc_attr( $value ); ?>">
    <?php
}


function nms_menu_page_callback(){
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="POST">
            <?php
            settings_fields( 'wpcookbook' );
            do_settings_sections( 'wpcookbook-page' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}