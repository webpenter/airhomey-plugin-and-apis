<?php
global $post, $related_posts;
$categories = get_the_category( $post->ID );
if ($categories):
    $cat_ids = array();
    foreach($categories as $individual_cat) $cat_ids[] = $individual_cat->term_id;
    $args=array(
        'category__in' => $cat_ids,
        'post__not_in' => array( $post->ID ),
        'posts_per_page' => '3'
    );
    $related_posts = get_posts( $args );
endif;

if( $related_posts ) {
?>
<div class="blog-section block">
    <div class="block-title">
        <h3 class="title"><?php esc_html_e( 'Related posts', 'homey' ); ?></h3>
    </div>
    <div class="block-body">
    <?php foreach( $related_posts as $post ): setup_postdata( $post ); ?>
        
            <?php get_template_part('content-list'); ?>
        
    <?php endforeach; ?>
    </div>
</div>
<?php } ?>
<?php wp_reset_postdata(); ?>