<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Homey_Review_Post_Type {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'definition' ) );
        add_action( 'save_post', array( __CLASS__, 'save_review_post_type' ), 10, 3 );
        /*add_filter( 'manage_edit-homey_Review_columns', array( __CLASS__, 'custom_columns' ) );
        add_action( 'manage_pages_custom_column', array( __CLASS__, 'custom_columns_manage' ) );*/
    }

    /**
     * Custom post type definition
     *
     * @access public
     * @return void
     */
    public static function definition() {
        $labels = array(
            'name' => __( 'Reviews','homey-core'),
            'singular_name' => __( 'Review','homey-core' ),
            'add_new' => __('Add New','homey-core'),
            'add_new_item' => __('Add New','homey-core'),
            'edit_item' => __('Edit Review','homey-core'),
            'new_item' => __('New Review','homey-core'),
            'view_item' => __('View Review','homey-core'),
            'search_items' => __('Search Review','homey-core'),
            'not_found' =>  __('No Review found','homey-core'),
            'not_found_in_trash' => __('No Review found in Trash','homey-core'),
            'parent_item_colon' => ''
          );

        $labels = apply_filters( 'homey_review_post_type_labels', $labels );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'has_archive' => true,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
            'hierarchical' => true,
            'menu_icon' => 'dashicons-edit',
            'menu_position' => 22,
            'can_export' => true,
            'show_in_rest'       => true,
            'rest_base'          => 'homey_reviews',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'supports' => array('title','editor','revisions','author','page-attributes'),

             // The rewrite handles the URL structure.
            'rewrite' => array(
                  'slug'       => 'homey_Review',
                  'with_front' => false,
                  'pages'      => true,
                  'feeds'      => true,
                  'ep_mask'    => EP_PERMALINK,
            ),
        );

        $args = apply_filters( 'homey_review_post_type_args', $args );

        register_post_type('homey_review',$args);
    }

    /**
     * Update post meta associated info when post updated
     *
     * @access public
     * @return
     */
    public static function save_review_post_type($post_id, $post, $update) {

        if(isset($_POST) && $_POST && !defined( 'DOING_AJAX' )) {
            // If this is just a revision, don't send the email.
            if ( wp_is_post_revision( $post_id ) )
            return;

            if (!is_object($post) || !isset($post->post_type)) {
                return;
            }

            $checkPost = get_post($post_id);
            

            $slug = 'homey_review';
            // If this isn't a 'book' post, don't update it.
            if ($slug != $post->post_type) {
                return;
            }

            $review_id = isset($_POST['post_ID']) ? $_POST['post_ID'] : '';

            if(empty($review_id)) {
                return;
            }
            $listing_id = get_post_meta($review_id, 'reservation_listing_id', true);
            $post_author_id = get_post_field( 'post_author', $listing_id );
            update_post_meta($post_id, 'listing_owner_id', $post_author_id);
            
            if(!empty($listing_id)) {
                homey_add_listing_rating($listing_id);
            }
        }
    }

    /**
     * Custom admin columns for post type
     *
     * @access public
     * @return array
     */
    public static function custom_columns() {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "pic" => __( 'Pic','homey-core' ),
            "title" => __( 'ID','homey-core' ),
            'status' => __( 'Status','homey-core' ),
            "date" => __('Date','homey-core'),
            "address" => __('Address','homey-core'),
            "check_in" => __('Check-in','homey-core'),
            "check_out" => __( 'Check-out','homey-core' ),
            "guests" => __( 'Guests','homey-core' ),
            "pets" => __( 'Pets','homey-core' ),
            "subtotal" => __( 'Subtotal','homey-core' ),
            "actions" => __( 'Actions','homey-core' ),
        );

        $columns = apply_filters( 'houzez_custom_post_listing_columns', $columns );

        return $columns;
        
    }

    /**
     * Custom admin columns implementation
     *
     * @access public
     * @param string $column
     * @return array
     */
    public static function custom_columns_manage( $column ) {
        global $post;
        $houzez_prefix = 'fave_';
        switch ($column)
        {
            case 'pic':
                if ( has_post_thumbnail() ) {
                    the_post_thumbnail( 'thumbnail', array(
                        'class'     => 'attachment-thumbnail attachment-thumbnail-small',
                    ) );
                } else {
                    echo '-';
                }
                break;
            case 'title':
                echo $post->ID;
                break;

            case 'status':
                
                break;
            case 'date':
                
                break;
            case 'address':
                
                break;
            case 'check_in':
                
                break;
            case 'check_out':
                
                break;
            case 'guests':
                
                break;
            case 'pets':
                
                break;
            case 'subtotal':
                
                break;
            
            case 'actions':
                echo '<div class="actions">';
                $admin_actions = apply_filters( 'post_row_actions', array(), $post );

                $user = wp_get_current_user();

                if ( in_array( $post->post_status, array( 'pending' ) ) && in_array( 'administrator', (array) $user->roles ) ) {
                    $admin_actions['confirmed']   = array(
                        'action'  => 'confirmed',
                        'name'    => __( 'Confirm', 'homey-core' ),
                        'url'     =>  wp_nonce_url( add_query_arg( 'confirm_Review', $post->ID ), 'confirm_Review' )
                    );
                }
                if ( in_array( $post->post_status, array( 'publish', 'pending' ) ) && in_array( 'administrator', (array) $user->roles ) ) {
                    $admin_actions['declined']   = array(
                        'action'  => 'declined',
                        'name'    => __( 'Decline', 'homey-core' ),
                        'url'     =>  wp_nonce_url( add_query_arg( 'decline_Review', $post->ID ), 'decline_Review' )
                    );
                }
                if ( $post->post_status !== 'trash' ) {
                    
                    if ( current_user_can( 'edit_post', $post->ID ) ) {
                        $admin_actions['edit']   = array(
                            'action'  => 'edit',
                            'name'    => __( 'View Detail', 'homey-core' ),
                            'url'     => get_edit_post_link( $post->ID )
                        );
                    }
                    
                }

                $admin_actions = apply_filters( 'homey_listing_admin_actions', $admin_actions, $post );

                foreach ( $admin_actions as $action ) {
                    if ( is_array( $action ) ) {
                        printf( '<a class="button button-icon tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action['action'], esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_html( $action['name'] ) );
                    } else {
                        //echo str_replace( 'class="', 'class="button ', $action );
                    }
                }

                echo '</div>';

                break;

        }
    }

}
