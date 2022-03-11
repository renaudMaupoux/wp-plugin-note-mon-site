<?php 
// in main plugin file
add_action( 'init', 'wpcookbook_register_post_type' );
/**
* Registers our Testimonials custom post type
*/
function wpcookbook_register_post_type(){
$labels = array(
    'name'               => 'Avis',
    'singular_name'      => 'Avis clients',
    'add_new'            => 'Ajouter une nouvelle avis',
    'add_new_item'       => 'Ajouter une nouvelle image de avis',
    'new_item'           => 'Nouvelle avis',
    'edit_item'          => 'Editer la avis',
    'view_item'          => 'Voir la avis',
    'all_items'          => 'Toutes les avis',
    'search_items'       => 'Rechercher une avis',
    'parent_item_colon'  => '',
    'not_found'          => '',
    'not_found_in_trash' => '',
    'menu_name' => 'Avis clients'
);
register_post_type('avis', array(
    'public'=>true,
    'labels'=>$labels,
    'menu_position'=>10,
    'menu_icon' => 'dashicons-format-status',
    'supports'=>array('title','editor','custom-fields'),
    'exclude_from_search' => true,
));
}
