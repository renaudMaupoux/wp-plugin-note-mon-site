<?php
add_shortcode( 'carrousel_site', 'carrousel_site' );

function carrousel_site(){
    $my_query2 = new WP_query("post_type=avis&showposts=-1&orderby=menu_order");
?>
<section class="section-avis">
  <div class="container">
<h2>Votre avis nous intéresse</h2>
<div class="customer-avis slider">
<?php
    if ( $my_query2->have_posts() ) : while ( $my_query2->have_posts() ) : $my_query2->the_post();
    
    $meta = get_post_meta( get_the_ID(), "rating", true ); 
    echo '<div class="slide"><div class="avis-block"><div><div class="etoiles note-'.$meta.'"></div><div class="date">';
    the_time('d/m/Y');
    echo '</div></div><div class="content">';
    the_content();
   
    echo '</div><div class="auteur">';
    the_title();
    echo '</div></div></div> ';
    endwhile;
    wp_reset_postdata();
    else: _e('Sorry, no posts matched your criteria.');
    endif;
?>
</div>
</div> <!-- .container -->
</section>
<?php
}
