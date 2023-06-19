<?php

// steven airbnb scrap
add_action('wp_ajax_nopriv_steven_scrap_airbnb_listing', 'steven_scrap_airbnb_listing');
add_action('wp_ajax_steven_scrap_airbnb_listing', 'steven_scrap_airbnb_listing');
if (!function_exists('steven_scrap_airbnb_listing')) {
    function steven_scrap_airbnb_listing() {

        $airbnb_listing_id = $_POST['airbnb_listing_id'];
        $airbnb_listing_plus = $_POST['airbnb_listing_plus'] ?? '';

        if (filter_var($airbnb_listing_id, FILTER_VALIDATE_URL)) {
            $_url = explode('?', $airbnb_listing_id)[0];
            $id = explode('/', $_url);
            $airbnb_listing_id = end($id);
        }

        // $link = homey_option('steven-airbnb-scrap-link');
        $link = 'https://www.airbnb.com/rooms';

        if( empty( trim($link) ) ) {
            $data = json_encode( array(
                'message' => 'Please add airbnb scraping server url',
                "success" => FALSE,
                "test" => TRUE
            ));

            print_r($data); exit;
        }

        if( !str_ends_with( $link, '/' ) ) {
            $link = $link . '/';
        }
        
        $url_settings = $link . $airbnb_listing_id;

        if( !empty( $airbnb_listing_plus ) ) {
            $url_settings = $link . 'plus/' . $airbnb_listing_id;
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $url_settings);
        curl_setopt($curl, CURLOPT_REFERER, $url_settings);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201');

        $response = curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($http_code != 200) {
            $final_result = json_encode( array(
                    "code" => strval($http_code),
                    "message" => "Error : Failed to get listing details",
                    "success" => FALSE
                )
            );
            // throw new Exception('Error : Failed to get listing details');
            echo json_encode( $final_result );
            exit;
        }

        curl_close($curl);

        $response = str_get_html($response);

        if( isset( $response ) || !empty( $response ) || $response == true ) {
            
            $response->clear();
            $data = json_decode( reset(end($response->nodes)->_), true );

            // echo '<pre>'; print_r($data); exit;

            $final_result = '';

            $dataSections = $data['niobeMinimalClientData'][0][1]['data']['presentation']['stayProductDetailPage']['sections'];

            $sections = $dataSections['sections'];
            $metadata = $dataSections['metadata']['loggingContext']['eventDataLogging'];
            
            function filter_section( $section, $filter ) {
                $check = '';
                foreach ($section as $key => $value) {
                    if( $value['sectionId'] == $filter ) 
                        $check = $value;
                }
                return $check;
            }

            # variables list
            $title = '';
            $rating = '';
            $total_review = '';
            $address = '';
            $overviewTitle = '';
            $descriptionDefault = '';
            $price = '';
            $qualifier = '';

            $policy_list = [];
            $aminities_list = [];
            $overviewItems = [];
            $sleepingArrangement = [];
            $highlights = [];
            $images = [];

            $result = [];

            $result = filter_section($sections, 'TITLE_DEFAULT');

            if( !empty( $result ) ) {
                $title = $result['section']['title'];
                
                foreach ($result['section']['overviewItems'] as $key => $value) {
                    if( isset( $value['action']['screenId'] ) && strtolower( $value['action']['screenId'] ) == 'reviews' ) {
                        $rating = $value['title'];
                        $total_review = $value['subtitle'];
                    }
                    if( isset( $value['action']['mapScreenId'] ) && strtolower( $value['action']['mapScreenId'] ) == 'map' ) {
                        $address = $value['title'];
                    }
                }
            }

            # POLICIES_DEFAULT
            $result = filter_section($sections, 'POLICIES_DEFAULT');
            if( !empty( $result )) {
                $policiesSection = $result['section'];

                $rulesSections = array();
                foreach( $policiesSection['houseRulesSections'] as $key => $data ) {
                    $rulesSections[] = $data['items'];
                }

                $safetyAndPropertiesSections = array();
                foreach( $policiesSection['safetyAndPropertiesSections'] as $key => $data ) {
                    $rulesSections[] = $data['items'];
                }

                $rule = array();
                foreach( $rulesSections as $rulesSection ) {
                    foreach( $rulesSection as $rules ) {
                        $temp = [
                            'title' => $rules['title'],
                            'icon' => $rules['icon'],
                            'htmlText' => isset( $rules['html'] ) ? $rules['html']['htmlText'] : ''
                        ];
                        $rule[] = $temp;
                    }
                }

                $policy_list = [
                    'title' => $policiesSection['title'],
                    'houseRulesSections' => $rule,
                ];
            }

            # AMENITIES_DEFAULT
            $result = filter_section($sections, 'AMENITIES_DEFAULT');
            if( !empty( $result ) ) {
                $amenititesSection = $result['section'];
                $aminities = [];
                foreach( $amenititesSection['seeAllAmenitiesGroups'] as $key => $data ) {
                    foreach( $data['amenities'] as $key => $amenity ) {
                        $aminities[] = [
                            'title' => $amenity['title'],
                            'subtitle' => $amenity['subtitle'],
                            'available' => $amenity['available']
                        ];
                    }
                }

                $aminities_list = [
                    'title' => $amenititesSection['title'],
                    'amenities' => $aminities
                ];
            }

            # OVERVIEW_DEFAULT
            $result = filter_section($sections, 'OVERVIEW_DEFAULT');
            if( !empty( $result ) ) {
                $overviewDefaultSection = $result['section'];
                $overviewTitle = $overviewDefaultSection['title'];
                $detailItems = $overviewDefaultSection['detailItems'];
                foreach( $detailItems as $key => $item ) {
                    $temp = explode(' ', $item['title'] );
                    $overviewItems[ rtrim( $temp[1], 's' ) ] = $temp[0];
                }
            }

            # DESCRIPTION_DEFAULT
            $result = filter_section($sections, 'DESCRIPTION_DEFAULT');
            if( !empty( $result ) ) {
                $descriptionDefaultSection = $result['section'];
                $descriptionDefault = $descriptionDefaultSection['htmlDescription']['htmlText'];
            }

            # SLEEPING_ARRANGEMENT_DEFAULT
            $result = filter_section($sections, 'SLEEPING_ARRANGEMENT_DEFAULT');
            if( !empty( $result ) ) {
                $sleepingArrangementDefaultSection = $result['section'];
                $arrangementDetails = $sleepingArrangementDefaultSection['arrangementDetails'];
                foreach( $arrangementDetails as $key => $arrangementDetail ) {
                    $temp = [
                        'room_type' => $arrangementDetail['title'],
                        'bed_type' => $arrangementDetail['subtitle'],
                        'icon' => $arrangementDetail['icons']
                    ];
                    $sleepingArrangement[] = $temp;
                }
            }

            # SLEEPING_ARRANGEMENT_WITH_IMAGES
            $result = filter_section($sections, 'SLEEPING_ARRANGEMENT_WITH_IMAGES');
            if( !empty( $result ) ) {
                $sleepingArrangementWithImagesSection = $result['section'];
                $arrangementDetails = $sleepingArrangementWithImagesSection['arrangementDetails'];
                $sleepingArrangement = array();
                foreach( $arrangementDetails as $key => $arrangementDetail ) {
                    $temp = [
                        'room_type' => $arrangementDetail['title'],
                        'bed_type' => $arrangementDetail['subtitle'],
                        'icon' => $arrangementDetail['icons']
                    ];
                    $sleepingArrangement[] = $temp;
                }
            }

            # HIGHLIGHTS_DEFAULT
            $result = filter_section($sections, 'HIGHLIGHTS_DEFAULT');
            if( !empty( $result ) ) {
                $highlightsDefaultSection = $result['section'];
                $highlightsDefault = $highlightsDefaultSection['highlights'];
                foreach( $highlightsDefault as $key => $highlight ) {
                    $temp = [
                        'title' => $highlight['title'],
                        'description' => $highlight['subtitle']
                    ];
                    $highlights[] = $temp;
                }
            }

            # HERO_DEFAULT
            $result = filter_section($sections, 'HERO_DEFAULT');
            if( !empty( $result ) ) {
                $heroDefaultSection = $result['section'];
                $heroDefault = $heroDefaultSection['previewImages'];
                foreach( $heroDefaultSection['previewImages'] as $key => $image ) {
                    $temp = [
                        'baseUrl' => $image['baseUrl'],
                        'label' => $image['accessibilityLabel']
                    ];
                    $images[] = $temp;
                }
            }

            $result = [
                "title" => $title,
                "review" => $total_review,
                "address" => $address,
                "overview" => $overviewTitle,
                "overviewDetail" => $overviewItems, 
                "images" => $images,
                "highlights" => $highlights,
                "description" => $descriptionDefault,
                "sleep" => $sleepingArrangement,
                "amenities" => $aminities_list,#{"title": amenities_title, "type": amenities},
                "policies" => $policy_list,
                "roomType" => $metadata['roomType'] ?? '',
                "personCapacity" => $metadata['personCapacity'] ?? '',
                "maplocation" => [
                    "lat" => $metadata['listingLat'] ?? '',
                    "lng" => $metadata['listingLng'] ?? ''
                ]
            ];

            $final_result = [
                "success" => true,
                "data" => $result,
                "message" => 'Successfully imported.....'
            ];
        } else {
            $final_result = [
                "success" => false,
                "data" => [],
                "message" => 'Error from Airbnb.....'
            ];
        }


        // echo '<pre>'; print_r($final_result); exit;

        echo json_encode( $final_result );


        exit;

    }
}

// steven find and add amenities
add_action('wp_ajax_nopriv_steven_find_airbnb_listing_amenities', 'steven_find_airbnb_listing_amenities');
add_action('wp_ajax_steven_find_airbnb_listing_amenities', 'steven_find_airbnb_listing_amenities');
if (!function_exists('steven_find_airbnb_listing_amenities')) {
    function steven_find_airbnb_listing_amenities() {

        $airbnb_amenities = [];
        foreach ($_POST['airbnb_listing_amenities'] as $value) {
            wp_create_term($value['title'], 'listing_amenity');
            $airbnb_amenities[] = $value['title'];
        }

        $html = '<label class="label-title">'. esc_attr(homey_option("ad_amenities")) .'</label>';

        $amenities = get_terms('listing_amenity', array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));

        if (!empty($amenities)) {
            $count = 1;
            foreach ($amenities as $amenity) {
                $checked = "";
                if ( in_array( $amenity->name, $airbnb_amenities ) ) {
                    $checked = "checked";
                }
                $html .= '<label class="control control--checkbox">';
                $html .= '<input type="checkbox" name="listing_amenity[]" id="amenity-' . esc_attr($amenity->slug) . '" value="' . esc_attr($amenity->term_id) . '"'. $checked .'>';
                $html .= '<span class="contro-text">' . esc_attr($amenity->name) . '</span>';
                $html .= '<span class="control__indicator"></span>';
                $html .= '</label>';
                $count++;
            }
        }

        echo( $html );exit;
    }
}

// steven_upload_image
add_action('wp_ajax_nopriv_steven_upload_image', 'steven_upload_image');
add_action('wp_ajax_steven_upload_image', 'steven_upload_image');
if (!function_exists('steven_upload_image')) {
    function steven_upload_image() {
        // add_filter( 'upload_dir', 'sa_upload_dir_name' );

		// echo WP_CONTENT_DIR;
        // echo "<pre>";
        // print_r(wp_upload_dir());
        // exit;
        // $base_to_upload = 'wp-content/uploads/';
        $base_to_upload = WP_CONTENT_DIR . '/uploads/';
        $bulk_upload_to_folder = 'listings';
        $bulk_upload_from_folder = 'listing-bulk-upload/';
        $bulk_images_folder = $_POST['steven_folder_name'];

        $airbnb_folder = $base_to_upload . $bulk_images_folder;

        $folder_to_find = $base_to_upload . $bulk_upload_from_folder . $bulk_images_folder;
        $folder_to_upload = $base_to_upload . $bulk_upload_to_folder; // . $bulk_images_folder;

        // Function to write image into file
        if( isset($_POST['airbnb_listing_images']) ){
            foreach ($_POST['airbnb_listing_images'] as $image) {
                // $image_url = explode('?', $image)[0];
                $image_url = $image['baseUrl'];
                $image_name = pathinfo($image_url)['basename'];

                if (!file_exists($airbnb_folder)) {
                    wp_mkdir_p($airbnb_folder);
                
                    $file = $airbnb_folder . '/' . $image_name;
                } else {
                    $file = $airbnb_folder . '/' . $image_name;
                }
                
                file_put_contents($file, file_get_contents( $image_url ));
            }
        }
        // exit;

        if (isset($_POST['airbnb_listing_images'])) {
            $files = list_files($airbnb_folder);
        } else {
            $files = list_files($folder_to_find);
        }
        // echo "<pre>";
        // print_r($files);
        // exit;
        $ajax_response_img_ids = [];
        $homey_thumbs = '';
        foreach ($files as $key => $file) {
            if (is_file($file)) {
                $hostName = $_SERVER['HTTP_HOST'];
                $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
                $my_site_url = $protocol . '://' . $hostName;
                if (isset($file)) {
                    $file_name          =  end(explode('/', $file));
                    $file_type          =  end(explode('.', $file));
                    $file_to_attach     =  end(explode('wp-content/uploads/' . $bulk_upload_from_folder, $file));

                    //echo $folder_to_upload . '/' . $listing_id . '/' . $file_name;
                    // exit;
                    $airbnb_listing_id = $_POST['airbnb_listing_id'];
                    if (!file_exists($folder_to_upload . '/' . 'airbnb-' . $airbnb_listing_id)) {
                        mkdir($folder_to_upload . '/' . 'airbnb-' . $airbnb_listing_id);
                    }

                    rename($file, $folder_to_upload . '/' . 'airbnb-' . $airbnb_listing_id . '/' . $file_name);

                    $file = $folder_to_upload . '/' . 'airbnb-' . $airbnb_listing_id . '/' . $file_name;
                    //echo $file = 'wp-content/uploads/'. $bulk_upload_to_folder . $file_to_attach;

                    // Prepare an array of post data for the attachment.
                    $attachment_details = array(
                        'guid'           => $my_site_url . '/' . $file,
                        'post_mime_type' => 'image/' . $file_type,
                        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_name)),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    if (!function_exists('wp_crop_image')) {
                        include(ABSPATH . 'wp-admin/includes/image.php');
                    }

                    $file_path = $file;

                    $attach_id      =   wp_insert_attachment($attachment_details, $file_path);
                    $attach_data    =   wp_generate_attachment_metadata($attach_id, $file_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);


                    $thumbnail_url = wp_get_attachment_image_src($attach_id, 'thumbnail');
                    $listing_thumb = wp_get_attachment_image_src($attach_id, 'homey-listing-thumb');
                    $feat_image_url = wp_get_attachment_url($attach_id);


                    $galary_thump = '<figure class="upload-gallery-thumb">' .
                                    '<img src="' . $thumbnail_url[0] . '" alt="thumb">' .
                                    '</figure>' .
                                    '<div class="upload-gallery-thumb-buttons">' .
                                    '<a class="icon-featured" data-thumb="' . $listing_thumb[0] . '" data-listing-id="' . 0 . '"  data-attachment-id="' . $attach_id . '"><i class="fa fa-star-o"></i></a>' .
                                    '<button class="icon-delete" data-listing-id="' . 0 . '"  data-attachment-id="' . $attach_id . '"><i class="fa fa-trash-o"></i></button>' .
                                    '<input type="hidden" class="listing-image-id" name="listing_image_ids[]" value="' . $attach_id . '"/>' .
                                    '</div>'.
                                    '<span style="display: none;" class="icon icon-loader"><i class="fa fa-spinner fa-spin"></i></span>';
                    
                    $homey_thumbs .= '<div id="thumb-holder-' . $attach_id . '" class="col-sm-2 col-xs-4 listing-thumb">' . $galary_thump . '</div>';
                    
                }
            }
        }

        echo $homey_thumbs; exit;
        // remove_filter( 'upload_dir', 'sa_upload_dir_name' );
    }
}


// This is your option name where all the Redux data is stored.
$opt_name = "homey_options";

Redux::setSection($opt_name, array(
    'title'  => esc_html__('Airbnb Scrap', 'homey'),
    'id'     => 'steven-airbnb-scrap-section',
    'desc'   => esc_html__('Basic functionality to scrap Airbnb listings.', 'homey'),
    'icon'   => 'el el-home',
    'fields' => array(
        array(
            'id'       => 'steven-airbnb-scrap-link',
            'type'     => 'text',
            'title'    => esc_html__('Server Link', 'homey'),
            'desc'     => esc_html__('Example: https://airscrap.webpenter.com/airbnb/', 'homey'),
            'subtitle' => esc_html__('', 'homey'),
            'hint'     => array(
                'content' => 'This is a <b>hint</b> tool-tip for the text field.<br/><br/>Add any HTML based text you like here.',
            )
        )
    )
));