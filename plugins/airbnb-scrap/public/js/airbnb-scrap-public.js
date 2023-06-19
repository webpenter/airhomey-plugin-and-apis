jQuery('document').ready(function () {
    "use strict";

	if( window.location.href.split("/").includes("add-listing") ) {
		jQuery('#submit_listing_form').find('.block-body').first().prepend(
			'<div class="row" style="margin-bottom: 20px" id="steven_airbnb">'+
			'    <div class="col-sm-12" style="padding: 15px; border-radius: 10px; box-shadow: rgba(136, 165, 191, 0.48) 6px 2px 16px 0px, rgba(255, 255, 255, 0.8) -6px -2px 16px 0px;">'+
			'        <div class="form-group">'+
			'            <label for="steven_airbnb_listing_id">Airbnb Sync</label>'+
			'            <div class="form-group" style="display: flex;">'+
			'                <input style="margin-right: 10px;" type="text" name="steven_airbnb_listing_id" id="steven_airbnb_listing_id" class="form-control" placeholder="Enter Airbnb listing URL or ID">'+
			'                <button type="button" class="btn btn-primary" id="steven_import_listing_data">Import</button>'+
			'            </div>'+
			'        </div>'+
			'        <p style="font-size: 12px; margin: 0"><strong>Note:</strong> if you have Airbnb listings so please put Airbnb listing URL to auto fill this form</p>'+
			'    </div>'+
            '</div>'
		)
	}


    if (typeof HOMEY_ajax_vars !== "undefined") {

        var ajaxurl = HOMEY_ajax_vars.admin_url + 'admin-ajax.php';
        var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
        // var verify_nonce = Homey_Listing.verify_nonce;
        // var verify_file_type = Homey_Listing.verify_file_type;

        // alert(ajaxurl);

        jQuery('#steven_import_listing_data').on('click', function (e) {
            e.preventDefault();

            var $this = jQuery(this);
            var parentDIV = $this.parents('.user-dashboard-right');
            var plus = '';
            var url = jQuery('#steven_airbnb_listing_id').val();

            if (!url) {
                alert("Enter id or URL");
            }

            var url = url.split("?")[0];

            if( url.includes('plus') ) {
                plus = 'plus';
            }

            var _url = url.split("/");
            
            var airbnb_listing_id = _url[_url.length - 1];

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'steven_scrap_airbnb_listing',
                    'airbnb_listing_id': airbnb_listing_id,
                    'airbnb_listing_plus': plus,
                },
                beforeSend: function () {
                    jQuery('#steven_import_listing_data i').remove();
                    jQuery('#steven_import_listing_data').prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
                },
                success: function (result) {
                    if (result.test) {
                        Snackbar.show({
                            text: result.message,
                            pos: 'top-center',
                            actionText: 'Thanks!',
                            backgroundColor: 'red',
                            textColor: 'white',
                            actionTextColor: 'black',
                            customClass: 'steven-snackbar-text'
                        });
                        jQuery('#steven_import_listing_data i').removeClass(process_loader_spinner);
                    } else if (result.success) {
                        console.log(result);

                        let data = result.data;
                        
                        // Amenities
                        jQuery.ajax({
                            type: 'post',
                            url: ajaxurl,
                            dataType: 'html',
                            data: {
                                'action': 'steven_find_airbnb_listing_amenities',
                                'airbnb_listing_amenities': data.amenities.amenities,
                            },
                            success: function (result) {
                                jQuery('.house-features-list').first().empty();
                                jQuery('.house-features-list').first().append(result);
                            },
                            error: function (xhr, status, error) {
                                var err = eval("(" + xhr.responseText + ")");
                                console.log(err.Message);
                            },
                            complete: function () {
                                console.log('Complete');
                            }
                        });

                        // Medaia Tab
                        jQuery.ajax({
                            type: 'post',
                            url: ajaxurl,
                            dataType: 'html',
                            data: {
                                'action': 'steven_upload_image',
                                'airbnb_listing_images': data.images,
                                'steven_folder_name': 'airbnb',
                                'airbnb_listing_id': airbnb_listing_id
                            },
                            success: function (result) {
                                jQuery('#homey_gallery_container').empty();
                                jQuery('#homey_gallery_container').append(result);
                                lisitng_thumbnail_event();
                                jQuery('.icon-featured').first().trigger('click');

                                Snackbar.show({
                                    text: 'Images are uploaded.',
                                    pos: 'bottom-right',
                                    actionText: 'Thanks!',
                                });
                            },
                            error: function (xhr, status, error) {
                                var err = eval("(" + xhr.responseText + ")");
                                console.log(err.Message);
                            },
                            complete: function () {
                                console.log('Complete Images');
                                jQuery('#steven_import_listing_data i').removeClass(process_loader_spinner);
                            }
                        });

                        // Information Tab
                        jQuery('#listing_title').val(data.title).change();
                        tinymce.activeEditor.setContent(data.description);
                        jQuery('#listing_bedrooms').val(data.overviewDetail.bedroom).change();
                        jQuery('#guests').val(data.personCapacity).change();
                        jQuery('#beds').val(data.overviewDetail.bed);
                        var bathrooms = typeof data.overviewDetail.bath != 'undefined' ? data.overviewDetail.bath : data.overviewDetail.bathroom;
                        jQuery('#baths').val(bathrooms).change();
                        jQuery('#listing_rooms').val(Object.keys(data.sleep).length);
                        jQuery('#night_price').val(data.price).change();
                        jQuery('#price_postfix').val(data.price_postfix).change();

                        let roomType = data.roomType.toLowerCase();

                        jQuery.makeArray(jQuery("input:radio[name='room_type']")).forEach(element => {
                            var t = element.parentNode;
                        
                            var te = jQuery(t).find('.control-text').text().toLowerCase();
                            if (roomType.includes("entire") && te.includes("entire")) {
                                jQuery(t).find("input:radio[name='room_type']").prop('checked', true);
                            } else if (roomType.includes("private") && te.includes("private")) {
                                jQuery(t).find("input:radio[name='room_type']").prop('checked', true);
                            } else if (roomType.includes("shared") && te.includes("shared")) {
                                jQuery(t).find("input:radio[name='room_type']").prop('checked', true);
                            }
                        });
                        
                        
                        // Location Tab
                        jQuery('#listing_address').val(data.address).change();
                        jQuery('#lat').val(data.maplocation.lat).change();
                        jQuery('#lng').val(data.maplocation.lng).change();

                        // Bedrooms Tab
                        for (let room = 0; room < Object.keys(data.sleep).length - 1; room++) {
                            jQuery('#add_more_bedrooms').trigger('click');
                        }

                        for (let room = 0; room < Object.keys(data.sleep).length; room++) {
                            var b = data.sleep[room].bed_type.split(' ');
                            var beds = b[0];
                            var type = b[1] + ' ' + b[2];
                            jQuery('input[name="homey_accomodation[' + room + '][acc_bedroom_name]"]').val(data.sleep[room].room_type);
                            jQuery('input[name="homey_accomodation[' + room + '][acc_no_of_beds]"]').val(beds);
                            jQuery('input[name="homey_accomodation[' + room + '][acc_bedroom_type]"]').val(type);
                        }

                        // Terms Tab
                        var policies = data.policies.houseRulesSections;
                        policies.forEach(el => {
                            var element = el.title.toLowerCase();
                            // Smoke
                            if (element.includes("smoking")) {
                                if (element.includes("no")) {
                                    jQuery("input:radio[value='0'][name='smoke']").prop('checked', true);
                                } else {
                                    jQuery("input:radio[value='1'][name='smoke']").prop('checked', true);
                                }
                            }
                            // Pets
                            if (element.includes("pets")) {
                                if (element.includes("no")) {
                                    jQuery("input:radio[value='0'][name='pets']").prop('checked', true);
                                } else {
                                    jQuery("input:radio[value='1'][name='pets']").prop('checked', true);
                                }
                            }
                            // Parties 
                            if (element.includes("parties")) {
                                if (element.includes("no")) {
                                    jQuery("input:radio[value='0'][name='party']").prop('checked', true);
                                } else {
                                    jQuery("input:radio[value='1'][name='party']").prop('checked', true);
                                }
                            }
                            // Children 
                            if (element.includes("child")) {
                                if (element.includes("no")) {
                                    jQuery("input:radio[value='0'][name='children']").prop('checked', true);
                                } else {
                                    jQuery("input:radio[value='1'][name='children']").prop('checked', true);
                                }
                            }
                            // Additional Rules
                            if (element.includes("additional")) {
                                var pre = jQuery('textarea#rules').val();
                                jQuery('textarea#rules').val(pre + "-" + el.htmlText);
                            }
                        });

                        // var n = 1;
                        // var additional_rules = data.policies["Health & safety"];
                        // additional_rules.forEach(element => {
                        //     if (element.toLowerCase().includes("show more")) {
                        //         element = element.toLowerCase().replace("show more", "");
                        //     }
                        //     var pre = jQuery('textarea#rules').val();
                        //     jQuery('textarea#rules').val(pre + n + "-" + element + "\n");
                        //     n++;
                        // });

                        Snackbar.show({
                            text: 'Listing data filled successfylly.',
                            pos: 'bottom-right',
                            actionText: 'Thanks!',
                            duration: 3000,
                            onClose: function(){
                                Snackbar.show({
                                    text: 'Wait '+ data.images.length +' images are uploading.....',
                                    pos: 'bottom-right',
                                    actionText: 'Thanks!',
                                    duration: 150000
                                });
                            }
                        });
                        
                        // parentDIV.find('.dashboard-area').prepend('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' + result.message + '</div>');
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>' + result.message + '</div>');
                        jQuery('#steven_import_listing_data i').removeClass(process_loader_spinner);
                    }
                },
                error: function (xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function () {
                    // jQuery('#steven_import_listing_data i').removeClass(process_loader_spinner);
                    jQuery('#steven_airbnb_listing_id').val('');
                }
            });
        });

        /* ------------------------------------------------------------------------ */
        /*  START LISTING VIEW Child-Theme
        /* ------------------------------------------------------------------------ */
        var get_title = jQuery("#listing_title");
        var view_title = jQuery("#property-title-fill");
        var selected = null;

        function keyup_fill(ele, ele_place) {

            jQuery(ele).on("change", function (event) {
                if (jQuery(ele).attr("name") === "night_price") {
                    if (!jQuery.isNumeric(jQuery(ele).val())) {
                        return
                    }
                }

                if (jQuery(ele).attr("name") === "listing_bedrooms" || jQuery(ele).attr("name") === "guests" || jQuery(ele).attr("name") === "baths") {
                    if (!jQuery.isNumeric(jQuery(ele).val())) {
                        return
                    }
                }

                var newText = event.target.value;
                jQuery(ele_place).html(newText);
            });
        }

        keyup_fill("#listing_title", "#title-place");
        keyup_fill("#listing_address", "#address-place");
        keyup_fill("#night_price", "#price-place");
        keyup_fill("#price_postfix", "#price-postfix");
        keyup_fill("#hour_price", "#price-place");
        keyup_fill("#listing_bedrooms", "#total-beds");
        keyup_fill("#guests", "#total-guests");
        keyup_fill("#baths", "#total-baths");

        function amenities_selector(ele, view_ele, is_text) {
            jQuery(ele).on('change', function () {
                if (is_text == 'yes') {
                    var selected = jQuery(this).find("option:selected").text();
                } else {
                    var selected = jQuery(this).find("option:selected").val();
                }
                jQuery(view_ele).html(selected);
            });
        }
        amenities_selector("#listing_type", "#listing-type-view", 'yes');
    }
    //----------------------

    /* ------------------------------------------------------------------------ */
    /*  Listing Thumbnails actions ( make features & delete )
     /* ------------------------------------------------------------------------ */
    var lisitng_thumbnail_event = function () {

        // Set Featured Image
        jQuery('.icon-featured').on('click', function (e) {
            e.preventDefault();

            var $this = jQuery(this);
            var thumb_id = $this.data('attachment-id');
            var thumb = $this.data('thumb');
            var icon = $this.find('i');

            jQuery('.upload-view-media .media-image img').attr('src', thumb);
            jQuery('.upload-gallery-thumb-buttons .featured_image_id').remove();
            jQuery('.upload-gallery-thumb-buttons .icon-featured i').removeClass('fa-star').addClass('fa-star-o');

            $this.closest('.upload-gallery-thumb-buttons').append('<input type="hidden" class="featured_image_id" name="featured_image_id" value="' + thumb_id + '">');
            icon.removeClass('fa-star-o').addClass('fa-star');
        });

        //Remove Image
        jQuery('.icon-delete').on('click', function (e) {
            e.preventDefault();

            var $this = jQuery(this);
            var thumbnail = $this.closest('.listing-thumb');
            var loader = $this.siblings('.icon-loader');
            var listing_id = $this.data('listing-id');
            var thumb_id = $this.data('attachment-id');

            loader.show();

            var ajax_request = jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_remove_listing_thumbnail',
                    'listing_id': listing_id,
                    'thumb_id': thumb_id,
                    'removeNonce': verify_nonce
                }
            });

            ajax_request.done(function (response) {
                if (response.remove_attachment) {
                    thumbnail.remove();
                } else {

                }
            });

            ajax_request.fail(function (jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
            });

        });

    }

    lisitng_thumbnail_event();
});