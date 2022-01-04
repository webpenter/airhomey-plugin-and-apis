<?php
namespace LocoAutoTranslateAddon\Register;
use LocoAutoTranslateAddon\Helpers\Atlt_downloader;

require_once "LocoAutomaticTranslateAddonProBase.php";
	class LocoAutomaticTranslateAddonPro {
        public $plugin_file=__FILE__;
        public $responseObj;
        public $licenseMessage;
        public $showMessage=false;
        public $slug="loco-atlt-register";
        function __construct() {
    	    add_action( 'admin_print_styles', [ $this, 'SetAdminStyle' ] );
    	    $licenseKey=get_option("LocoAutomaticTranslateAddonPro_lic_Key","");
    	    $liceEmail=get_option( "LocoAutomaticTranslateAddonPro_lic_email","");
            LocoAutomaticTranslateAddonProBase::addOnDelete(function(){
               delete_option("LocoAutomaticTranslateAddonPro_lic_Key");
            });
    	    if(LocoAutomaticTranslateAddonProBase::CheckWPPlugin($licenseKey,$liceEmail,$this->licenseMessage,$this->responseObj,__FILE__)){
    		    add_action( 'admin_menu', [$this,'ActiveAdminMenu'],101);
    		    add_action( 'admin_post_LocoAutomaticTranslateAddonPro_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
    		    //$this->licenselMessage=$this->mess;
                update_option("atlt-type","pro");
                add_action('wp_ajax_loco_install_pro', array($this, 'loco_install_pro'));           

    	    }else{
    	        if(!empty($licenseKey) && !empty($this->licenseMessage)){
    	           $this->showMessage=true;
                }
                update_option("atlt-type","free");
    		    update_option("LocoAutomaticTranslateAddonPro_lic_Key","") || add_option("LocoAutomaticTranslateAddonPro_lic_Key","");
    		    add_action( 'admin_post_LocoAutomaticTranslateAddonPro_el_activate_license', [ $this, 'action_activate_license' ] );
    		    add_action( 'admin_menu', [$this,'InactiveMenu'],101);
    	    }
        }
    	function SetAdminStyle() {
    		wp_register_style( "LocoAutomaticTranslateAddonProLic", plugins_url("style.css",$this->plugin_file),10);
    		wp_enqueue_style( "LocoAutomaticTranslateAddonProLic" );
        }
        
        function loco_install_pro(){
            $pass = (isset( $_POST['pass'] ) && !empty( $_POST['pass'] )) ? $_POST['pass'] : null;
            if( $pass === null || false == wp_verify_nonce( $pass, 'loco-pro-activation-nonce' )){
                die( array('response'=>'500','message'=>'Nonce Verification falied') );
            }
            $hash = isset($_POST['hash']) ? $_POST['hash'] : null;
            if( !empty( $hash ) && file_exists( WP_PLUGIN_DIR . '/loco-automatic-translate-addon-pro')==false ){
                require_once ATLT_PATH . 'includes/Helpers/Atlt_downloader.php';
                $request = new Atlt_downloader();
                $response = $request->install(  'https://locoaddon.com/data/download-plugin.php?license-key=' . $hash );
            }
            if( file_exists( WP_PLUGIN_DIR . '/loco-automatic-translate-addon-pro')==true ){
                activate_plugin( 'loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php' );
                die( json_encode( array('response'=>'200','message'=>'Loco Automatic Translate Addon Pro - Installed Successfully!')) );
            }else{
                die( json_encode( array('response'=>'500','message'=>'Loco Automatic Translate Addon Pro - Installation failed! Try downloading manually.')) );
            }
        }
        function ActiveAdminMenu(){
                add_submenu_page( 'loco',
                'Loco Automatic Translate Addon Pro', 
                'Auto Translate Addon - Premium License',
                 'manage_options', 
                    $this->slug,
                 array($this, 'Activated'));

                 if( class_exists( 'LocoAutoTranslateAddonPro' ) ){
                     // no further execution required
                     return;
                 }
            if( isset( $_GET['page'] ) && $_GET['page'] == "loco-atlt-register" &&
             false == is_plugin_active( 'loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php' ) ){

                add_action('admin_footer', function(){
                    $hash_key = get_option( 'LocoAutomaticTranslateAddonPro_lic_Key' ,false);
                    $pass = wp_create_nonce('loco-pro-activation-nonce');
                    $url = admin_url( 'admin-ajax.php' );
                    $script = '
                    <script>
                    jQuery(document).ready(function($){
                        $(".error.loco-pro-missing").hide();
                        $.ajax({
                            type:"POST",
                            url:"'.$url.'",
                            data:{action:"loco_install_pro",hash:"'.$hash_key.'",pass:"'.$pass.'"},
                            complete:function(res){
                                console.log(res);
                                if( res.status == 200 ){
                                    console.log( res.message )
                                    $("#activating-loco-pro-response").text("Pro features activated successfully!");
                                    window.setTimeout(function(){
                                        $("#activating-loco-pro-response").fadeOut(400);   
                                    },1500);
                                }else{
                                    console.log( res.message )
                                }
                            }
                        })
                    });
                    </script>
                    <div id="activating-loco-pro-response">
                    <span> Please wait while we are activating pro features for you</span>
                    </div>
                    ';
                    echo $script;
                } );
            }
        }
        function InactiveMenu() {
            add_submenu_page( 'loco',
            'Loco Automatic Translate Addon Pro', 
            'Auto Translate Addon - Premium License',
             'activate_plugins', 
             $this->slug,
             array($this, 'LicenseForm'));
    	  /*  add_menu_page( "LocoAutomaticTranslateAddonPro", "Loco Automatic Translate Addon Pro", 'activate_plugins', $this->slug,  [$this,"LicenseForm"], " dashicons-star-filled " ); */

        }
        function action_activate_license(){
        		check_admin_referer( 'el-license' );
        		$licenseKey=!empty($_POST['el_license_key'])?$_POST['el_license_key']:"";
        		$licenseEmail=!empty($_POST['el_license_email'])?$_POST['el_license_email']:"";
        		update_option("LocoAutomaticTranslateAddonPro_lic_Key",$licenseKey) || add_option("LocoAutomaticTranslateAddonPro_lic_Key",$licenseKey);
        		update_option("LocoAutomaticTranslateAddonPro_lic_email",$licenseEmail) || add_option("LocoAutomaticTranslateAddonPro_lic_email",$licenseEmail);
        		wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
        	}
        function action_deactivate_license() {
    	    check_admin_referer( 'el-license' );
    	    if(LocoAutomaticTranslateAddonProBase::RemoveLicenseKey(__FILE__,$message)){
    		    update_option("LocoAutomaticTranslateAddonPro_lic_Key","") || add_option("LocoAutomaticTranslateAddonPro_lic_Key","");
    	    }
    	    wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
        }
        function Activated(){
            ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="LocoAutomaticTranslateAddonPro_el_deactivate_license"/>
                <div class="el-license-container">
                    <h3 class="el-license-title"><i class="dashicons-before dashicons-translation"></i> <?php _e("Automatic Translate Addon For Loco Translate - Premium License Status",$this->slug);?> </h3>
                    <div class="el-license-content">
                        <div class="el-license-form">
                            <h3>Active License Status</h3>
                            <ul class="el-license-info">
                            <li>
                                <div>
                                    <span class="el-license-info-title"><?php _e("License Status",$this->slug);?></span>

                                    <?php if ( $this->responseObj->is_valid ) : ?>
                                        <span class="el-license-valid"><?php _e("Valid",$this->slug);?></span>
                                    <?php else : ?>
                                        <span class="el-license-valid"><?php _e("Invalid",$this->slug);?></span>
                                    <?php endif; ?>
                                </div>
                            </li>

                            <li>
                                <div>
                                    <span class="el-license-info-title"><?php _e("License Type",$this->slug);?></span>
                                    <?php echo $this->responseObj->license_title; ?>
                                </div>
                            </li>

                            <li>
                                <div>
                                    <span class="el-license-info-title"><?php _e("License Expiry Date",$this->slug);?></span>
                                    <?php echo $this->responseObj->expire_date; ?>
                                </div>
                            </li>

                            <li>
                                <div>
                                    <span class="el-license-info-title"><?php _e("Support Expiry Date",$this->slug);?></span>
                                    <?php echo $this->responseObj->support_end; ?>
                                </div>
                            </li>
                                <li>
                                    <div>
                                        <span class="el-license-info-title"><?php _e("Your License Key",$this->slug);?></span>
                                        <span class="el-license-key"><?php echo esc_attr( substr($this->responseObj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->responseObj->license_key,-9) ); ?></span>
                                    </div>
                                </li>
                            </ul>
                            <div class="el-license-active-btn">
                                <?php wp_nonce_field( 'el-license' ); ?>
                                <?php submit_button('Deactivate License'); ?>
                            </div>
                        </div>
                        <div class="el-license-textbox">
                        <h3>Important Points</h3>
                        <ol>
                            <li>Please deactivate your license first before moving your website or changing domain.</li>
                            <li>Plugin does not auto-translate any string that contains HTML and special characters.</li>
                            <li>Currently DeepL Doc Translator provides limited number of free docs translations per day. You can purchase to <a href="https://www.deepl.com/pro?cta=homepage-free-trial#pricing" target="_blank">DeepL Pro</a> to increase this limit.</li>
                            <li>If you have any issue or query, please <a href="https://locoaddon.com/support/" target="_blank">contact support</a>.</li>
                        </ol>
                        <div class="el-pluginby">
                            Plugin by<br/>
                            <a href="https://coolplugins.net" target="_blank"><img src="<?php echo ATLT_URL.'/assets/images/coolplugins-logo.png' ?>"/></a>
                        </div>
                        </div>
                    </div>
                </div>
            </form>
    	<?php
        }

        function LicenseForm() {
    	    ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    	    <input type="hidden" name="action" value="LocoAutomaticTranslateAddonPro_el_activate_license"/>
    	    <div class="el-license-container">
    		    <h3 class="el-license-title"><i class="dashicons-before dashicons-translation"></i> <?php _e("Automatic Translate Addon For Loco Translate - Premium License",$this->slug);?></h3>
                <div class="el-license-content">
                    <div class="el-license-form">
                        <h3>Activate Premium License</h3>
                        <?php
                        if(!empty($this->showMessage) && !empty($this->licenseMessage)){
                            ?>
                            <div class="notice notice-error is-dismissible">
                                <p><?php echo _e($this->licenseMessage,$this->slug); ?></p>
                            </div>
                            <?php
                        }
                        ?>
                        <!--Enter License Key Here START-->
                        <div class="el-license-field">
                            <label for="el_license_key"><?php _e("Enter License code",$this->slug);?></label>
                            <input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
                        </div>
                        <div class="el-license-field">
                            <label for="el_license_key"><?php _e("Email Address",$this->slug);?></label>
                            <?php
                                $purchaseEmail   = get_option( "LocoAutomaticTranslateAddonPro_lic_email", get_bloginfo( 'admin_email' ));
                            ?>
                            <input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo sanitize_email($purchaseEmail); ?>" placeholder="" required="required">
                            <div><small><?php _e("✅ I agree to share my purchase code and email for plugin verification and to receive future updates notifications!",$this->slug);?></small></div>
                        </div>
                        <div class="el-license-active-btn">
                            <?php wp_nonce_field( 'el-license' ); ?>
                            <?php submit_button('Activate'); ?>
                        </div>
                        <!--Enter License Key Here END-->
                        <div>
                        <strong style="color:#e00b0b;">*Important Points</strong>
                        <ol>
                        <li>DeepL Translate provides better translations than Google, Yandex or other machine translation providers. <a href="https://techcrunch.com/2017/08/29/deepl-schools-other-online-translators-with-clever-machine-learning/" target="_blank"><b>Read review by Techcrunch!</b></a></li>
                        <li>Currently DeepL Doc Translator provides limited number of free docs translations per day. You can purchase to <a href="https://www.deepl.com/pro?cta=homepage-free-trial#pricing" target="_blank">DeepL Pro</a> to increase this limit.</li>
                        <li>Automatic translate providers do not support HTML and special characters translations. So plugin will not translate any string that contains HTML or special characters.</li>
                        <li>If any auto-translation provider stops any of its free translation service then plugin will not support that translation service provider.</li>
                        </ol>
                        </div>
                        <br/>
                        <a class="button button-primary" href='https://locoaddon.com/addon/loco-automatic-translate-premium-license-key/#pricing' target='_blank'>Buy Pro Plugin + License</a>
                    </div>
                    
                    <div class="el-license-textbox">
                        <h3>Compare Free vs Pro (<a href='https://locoaddon.com/addon/loco-automatic-translate-premium-license-key/#pricing' target='_blank'>Buy Pro Plugin + License</a>)</h3>
                        <table class="loco-addon-license">
                        
                        <tr>
                        <th>Features</th>
                        <th>Free License</th>
                        <th>Premium License</th>
                        </tr>

                        <tr>
                        <td>Yandex Translate Widget Support<br/><img style="border: 1px solid;" src="<?php echo ATLT_URL.'/assets/images/powered-by-yandex.png' ?>"/></td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available</td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available</td>
                        </tr>

                        <tr>
                        <td>Unlimited Translations</td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available<br/><span style="font-size:11px;font-weight:bold;">(Via Yandex Only)</span></td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available<br/><span style="font-size:11px;font-weight:bold;">(Via Yandex, Google & DeepL)</td>
                        </tr>

                        <tr>
                        <td>No API Key Required</td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> API Not Required<br/><span style="font-size:11px;font-weight:bold;">(Only Yandex Support)</span></td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> API Not Required<br/><span style="font-size:11px;font-weight:bold;">(Yandex, Google & DeepL Support)</span></td>
                        </tr>

                        <tr style="background:#fffb7a;font-weight: bold;">
                        <td>Google Translate Widget Support<br/><img style="border: 1px solid;" src="<?php echo ATLT_URL.'/assets/images/powered-by-google.png' ?>"/></td>
                        <td>❌ Not Available</td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available<br/><span style="font-size:11px;font-weight:bold;">(Better than Yandex)</span></td>
                        </tr>

                        <tr style="background:#fffb7a;font-weight: bold;">
                        <td>DeepL Doc Translator Support<br/><img style="border: 1px solid;" src="<?php echo ATLT_URL.'/assets/images/powered-by-deepl.png' ?>"/></td>
                        <td>❌ Not Available</td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available<br/><span style="font-size:11px;font-weight:bold;">(Limited Free Docs Translations / Day)</span></td>
                        </tr>

                        <tr>
                        <td><strong>Premium Support</strong></td>
                        <td>❌ Not Available<br/><strong>(Support Time: 7 – 10 days)</strong></td>
                        <td><span style="color:green;font-size:1.4em;">✔</span> Available<br/><strong>(Support Time: 24 - 48 Hrs)</strong></td>
                        </tr>

                        </table>
                        <div class="el-pluginby">
                            Plugin by<br/>
                            <a href="https://coolplugins.net" target="_blank"><img src="<?php echo ATLT_URL.'/assets/images/coolplugins-logo.png' ?>"/></a>
                        </div>
                    </div>
                </div>
    	    </div>
        </form>
    	    <?php
        }
    }

    new LocoAutomaticTranslateAddonPro();