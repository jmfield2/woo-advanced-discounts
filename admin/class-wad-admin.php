<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.orionorigin.com/
 * @since      0.1
 *
 * @package    Wad
 * @subpackage Wad/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wad
 * @subpackage Wad/admin
 * @author     ORION <support@orionorigin.com>
 */
class Wad_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Wad_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Wad_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wad-admin.css', array(), $this->version, 'all');
        wp_enqueue_style("acd-flexgrid", plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
//                wp_enqueue_style( "acd-tooltip", plugin_dir_url( __FILE__ ) . 'css/tooltip.css', array(), $this->version, 'all' );
        wp_enqueue_style("o-ui", plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
        wp_enqueue_style("o-datepciker", plugin_dir_url(__FILE__) . 'js/datepicker/css/datepicker.css', array(), $this->version, 'all');
        wp_enqueue_style("wad-datetimepicker", plugin_dir_url(__FILE__) . 'js/datetimepicker/jquery.datetimepicker.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wad-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wad-tabs", plugin_dir_url(__FILE__) . 'js/SpryAssets/SpryTabbedPanels.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wad-serializejson", plugin_dir_url(__FILE__) . 'js/jquery.serializejson.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script("wad-datetimepicker", plugin_dir_url(__FILE__) . 'js/datetimepicker/jquery.datetimepicker.full.min.js', array('jquery'), $this->version, false);
    }

    /**
     * Initialize the plugin sessions
     */
    function init_sessions() {
        if (!session_id()) {
            session_start();
        }

        if (!isset($_SESSION["active_discounts"]))
            $_SESSION["active_discounts"] = array();
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function add_wad_menu() {
        $parent_slug = "edit.php?post_type=o-discount";
        add_submenu_page($parent_slug, __('Products Lists', 'wad'), __('Products Lists', 'wad'), 'manage_product_terms', 'edit.php?post_type=o-list', false);
        add_submenu_page($parent_slug, __('Settings', 'wad'), __('Settings', 'wad'), 'manage_product_terms', 'wad-manage-settings', array($this, 'get_wad_settings_page'));
        //        add_submenu_page($parent_slug, __('Get Started', 'wad'), __('Get Started', 'wad'), 'manage_product_terms', 'wad-about', array($this, "get_about_page"));
        add_submenu_page($parent_slug, __('Pro features', 'wad' ), __( 'Pro features', 'wad' ), 'manage_product_terms', 'wad-pro-features', array($this, "get_wad_pro_features_page"));
//        add_submenu_page($parent_slug, __('Get Started', 'wad'), __('Get Started', 'wad'), 'manage_product_terms', 'wad-about', array($this, "get_about_page"));
    }

    public function get_wad_settings_page() {
        if ((isset($_POST["wad-options"]) && !empty($_POST["wad-options"]))) {
            update_option("wad-options", $_POST["wad-options"]);
        }
        wad_remove_transients();
        ?>
        <div class="o-wrap cf">
            <h1><?php _e("Woocommerce All Discounts Settings", "wad"); ?></h1>
            <form method="POST" action="" class="mg-top">
                <div class="postbox" id="wad-options-container">
                    <?php
                    $begin = array(
                        'type' => 'sectionbegin',
                        'id' => 'wad-datasource-container',
                        'table' => 'options',
                    );


                    $enable_cache = array(
                        'title' => __('Cache discounts', 'wad'),
                        'name' => 'wad-options[enable-cache]',
                        'type' => 'select',
                        'options' => array(0 => "No", 1 => "Yes"),
                        'desc' => __('Wether or not to store the discounts in the cache to increase the pages load speed. Cache is valid for 12hours', 'wad'),
                        'default' => '',
                    );

                    $end = array('type' => 'sectionend');
                    $settings = array(
                        $begin,
                        $enable_cache,
                        $end
                    );
                    echo o_admin_fields($settings);
                    ?>
                </div>
                <input type="submit" class="button button-primary button-large" value="<?php _e("Save", "wad"); ?>">
            </form>
        </div>
        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    /**
     * Builds the about page
     */
    function get_about_page() {
        $wpc_logo = WAD_URL . 'admin/images/wpc.jpg';
        $img1 = WAD_URL . 'admin/images/install-demo-package.jpg';
        $img2 = WAD_URL . 'admin/images/set-basic-settings.jpg';
        $img3 = WAD_URL . 'admin/images/create-customizable-product.jpg';
        $img4 = WAD_URL . 'admin/images/manage-templates.jpg';
        ?>
        <div id='wad-about-page'>
            <div class="about-heading">
                <div>
                    <H2><?php echo __("Welcome to WooCommerce All Discounts", "wad") . " " . WAD_VERSION; ?></H2>
                    <H4><?php printf(__("Thanks for installing! WooCommerce All Discounts %s is more powerful, stable and secure than ever before. We hope you enjoy using it.", "wad"), WAD_VERSION); ?></H4>
                </div>
                <div class="about-logo">
                    <img src="<?php echo $wpc_logo; ?>" />
                </div>
            </div>
            <div class="about-button">
                <div><a href="<?php echo admin_url('edit.php?post_type=o-discount&page=wad-manage-settings'); ?>" class="button">Settings</a></div>
                <div><a href="<?php echo WAD_URL . 'user_manual.pdf'; ?>" class="button"><?php _e("User Manual", "wad"); ?></a></div>
            </div>

            <div id="TabbedPanels1" class="TabbedPanels">
                <ul class="TabbedPanelsTabGroup ">
                    <li class="TabbedPanelsTab " tabindex="4"><span><?php _e('Getting Started', 'wad'); ?></span> </li>
                    <li class="TabbedPanelsTab" tabindex="5"><span><?php _e('Changelog', 'wad'); ?> </span></li>
                    <li class="TabbedPanelsTab" tabindex="6"><span><?php _e('Follow Us', 'wad'); ?></span></li>
                </ul>

                <div class="TabbedPanelsContentGroup">
                    <div class="TabbedPanelsContent">
                        <div class='wpc-grid wpc-grid-pad'>
                            <div class="wpc-col-3-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=AlSMCIoOLRA" target="blank">
                                        <div class="img-container"><img src="<?php echo $img1; ?>"></div>
                                        <div class="img-title"><?php _e('How to install the demo package?', 'wad'); ?></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wpc-col-3-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=NTvIvhJHueU" target="blank">
                                        <div class="img-container"><img src="<?php echo $img2; ?>"></div>
                                        <div class="img-title"><?php _e('How to set the basic settings?', 'wad'); ?></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wpc-col-3-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=FDnM7hjepqo" target="blank">
                                        <div class="img-container"><img src="<?php echo $img3; ?>"></div>
                                        <div class="img-title"><?php _e('How to create a customizable product?', 'wad'); ?></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wpc-col-3-12">
                                <div class="product-container">
                                    <a href="https://www.youtube.com/watch?v=_hoANHYazI4" target="blank">
                                        <div class="img-container"><img src="<?php echo $img4; ?>"></div>
                                        <div class="img-title"><?php _e('How to manage your designs templates?', 'wad'); ?></div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="TabbedPanelsContent">
                        <div class='wpc-grid wpc-grid-pad'>
                            <?php
                            $file_path = WAD_DIR . "/changelog.txt";
                            $myfile = fopen($file_path, "r") or die(__("Unable to open file!", "wad"));
                            while (!feof($myfile)) {
                                $line_of_text = fgets($myfile);
                                if (strpos($line_of_text, 'Version') !== false)
                                    print '<b>' . $line_of_text . "</b><BR>";
                                else
                                    print $line_of_text . "<BR>";
                            }
                            fclose($myfile);
                            ?>
                        </div>
                    </div>
                    <div class="TabbedPanelsContent">
                        <div class="wpc-grid wpc-grid-pad follow-us">
                            <div class="wpc-col-6-12 ">
                                <h3>Why?</h3>
                                <ul class="follow-us-list">
                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/love.png"></span>
                                            <span>
                                                <h4 class="title"> Show us some love of course!</h4>
                                                You like our product and you tried it. Cool! Then give us some boost by sharing it with friends or making interesting comments on our pages!
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/update.png"></span>
                                            <span>
                                                <h4 class="title"> Receive regular updates from us on our products.</h4>
                                                This is the best way to enjoy the full of the news features added to our plugins. 
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/features.png"></span>
                                            <span>
                                                <h4 class="title"> Suggest new features for the products you're interested in.</h4>
                                                One of our products arouses your interest but it’s not exactly what you want. If only some features can be added… You know what? Actually it’s possible! Just leave your suggestion and we’ll do our best! 
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/bug.png"></span>
                                            <span>
                                                <h4 class="title"> Become a beta tester for our pre releases.</h4>
                                                For each couple of feature up-coming we need beta tester to improve the final product we are about to propose. So if you want to be part of this, freely apply here.
                                            </span>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="#">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/free.png"></span>
                                            <span>
                                                <h4 class="title"> Access our freebies collection anytime.</h4>
                                                Find the coolest free collection of our plugins and make the most of it!
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div> 
                            <div id="separator"></div>
                            <div class="wpc-col-6-12 ">
                                <h3>How?</h3>
                                <div class="follow-us-text">
                                    <div>
                                        Easy!! Just access our social networks pages and follow/like us. Yeah just like that :).
                                    </div>

                                    <div class="btn-container">
                                        <a href="http://twitter.com/OrionOrigin" target="blank" style="display: inline-block;">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/twitter.png"></span>
                                        </a>
                                        <a href="https://www.facebook.com/OrionOrigin" target="blank" style="display: inline-block;">
                                            <span class="rs-ico"><img src="<?php echo WAD_URL; ?>/admin/images/facebook-about.png"></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div> 
                    </div>

                </div>
            </div>
        </div>
        <?php
    }
    
    function get_wad_pro_features_page()
    {
        $messages=  $this->get_pro_features_messages();
        
        ?>
        <div class="wrap">
            <h1>Need more features? Let's go pro!</h1>
            <div id="wad-pro-features">
                <div class="o-wrap">
                    <?php
                            foreach ($messages as $message_key=>$message)
                            {
                                ?>
                    <div class="col xl-1-3 wad-infox">
                        <p>
                        <h3><?php echo $message_key;?></h3>
                        </p>
                        <p>
                            <?php echo ucfirst($message);?>
                        </p>

                        <a href="http://www.orionorigin.com/plugins/woocommerce-all-discounts/?utm_source=Free%20Trial&utm_medium=cpc&utm_term=<?php echo urlencode($message_key);?>&utm_campaign=Woocommerce%20All%20Discounts" class="button"  target="_blank">Click here to unlock</a></p>
                    </div>
                                <?php
                            }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    function get_pro_features_messages()
    {
        $messages=array(
            "Free gifts"=>"create a \"Buy one, get one for free\" kind of discount",
            "Shipping Country"=>"apply a discount based on the shipping country.",
            "Billing Country"=>"apply a discount based on the billing country.",
            "Payment gateways"=>"apply a discount if the customers checks out with a specific payment gateway.",
            "Usage limit"=>"limits the number of customers who can use a discount.",
            "Periodic discounts"=>"automatically enable a discount periodically.",
            "Social networks"=>"apply a discounts if the customer is following you on social networks.",
            "Groups based discounts"=>"apply a discount is the customer belong to a specific group.",
            "Newsletters based discounts"=>"offer a discount if the customer subscribed to your newsletters.",
            "Taxes inclusion"=>"apply discounts on subtotal with or without the taxes.",
            "Specific users discounts"=>"apply discounts for specific(s) customer(s).",
            "Currency based discounts"=>"apply discounts depending on the customer selected currency (useful for currency switchers).",
            "Previous purchases discounts"=>"ability to define a discount based on previously purchased products.",
            "Improved Speed"=>"Do you feel the plugin is a bit slow? Upgrade to make it faster.",
        );
        return $messages;
    }
    
    function get_ad_messages()
    {
        global $pagenow;
        $messages=  $this->get_pro_features_messages();
        $random_message_key=  array_rand($messages);
        if(($pagenow=="post-new.php"
            ||$pagenow=="post.php"
            ||(isset($_GET["post_type"])&&$_GET["post_type"]=="o-discount")
            ||(isset($_GET["post_type"])&&$_GET["post_type"]=="product")
            ||(isset($_GET["page"])&&$_GET["page"]=="o-list")
        )
            &&
            (isset($_GET["page"])&&$_GET["page"]!="wad-pro-features"))
        {
            echo '<div class="wad-info">
               <p><strong>'.$random_message_key.'</strong>: '.$messages[$random_message_key].' <a href="http://www.orionorigin.com/plugins/woocommerce-all-discounts/?utm_source=Free%20Trial&utm_medium=cpc&utm_term='.urlencode($random_message_key).'&utm_campaign=Woocommerce%20All%20Discounts" class="button"  target="_blank">Click here to unlock</a></p>
            </div>';
        }

    }
    
    function get_review_suggestion_notice()
    {
        $ignore_notices=  get_option( 'wad_admin_notice_ignore' );
//        var_dump($ignore_notices);
        $dismiss_transient=get_transient( 'wad_notice_dismiss' );
//        var_dump($dismiss_transient);
        if($ignore_notices||$dismiss_transient!==false)
            return;
//        $admin_notices_option = get_option( 'wad_admin_notice', array() );
//        var_dump($admin_notices_option);
//        if ( ! isset( $admin_notices_option[ 'disable_admin_notices' ] ) || ( isset( $admin_notices_option[ 'disable_admin_notices' ] ) && $admin_notices_option[ 'disable_admin_notices' ] == 0 ) ){
//                if ( current_user_can('manage_product_terms' ) ) {
//                        return true;
//                }
//        }
        
        $two_week_review_ignore = add_query_arg( array( 'wad_admin_notice_ignore' => '1' ) );
        $two_week_review_temp = add_query_arg( array( 'wad_admin_notice_temp_ignore' => '1', 'wad_int' => 14 ) );
        $one_week_support = add_query_arg( array( 'wad_admin_notice_ignore' => '1' ) );
        ?>
        <div class="update-nag wad-admin-notice">
            <div class="wad-notice-logo"></div> 
            <p class="wad-notice-title">Leave A Review? </p> 
            <p class="wad-notice-body">We hope you've enjoyed using Woocommerce Advanced Discounts! Would you consider leaving us a review on WordPress.org? </p>
            <ul class="wad-notice-body wad-red">
                <li> <span class="dashicons dashicons-smiley"></span><a href="<?php echo $two_week_review_ignore;?>"> I've already left a review</a></li>
                <li><span class="dashicons dashicons-calendar-alt"></span><a href="<?php echo $two_week_review_temp;?>">Maybe Later</a></li>
                <li><span class="dashicons dashicons-external"></span><a href="https://wordpress.org/support/view/plugin-reviews/woo-advanced-discounts?filter=5" target="_blank">Sure! I'd love to!</a></li>
            </ul>
            <a href="<?php echo $one_week_support;?>" class="dashicons dashicons-dismiss"></a>
        </div>
        <?php
    }
    
    // Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
    public function admin_notice_ignore() {

        // If user clicks to ignore the notice, update the option to not show it again
        if ( isset($_GET['wad_admin_notice_ignore']) && current_user_can( 'manage_product_terms' ) ) {

//                $admin_notices_option = get_option( 'wad_admin_notice', array() );
//                $admin_notices_option[ $_GET[ 'wad_admin_notice_ignore' ] ][ 'dismissed' ] = 1;
                update_option( 'wad_admin_notice_ignore', true );
                $query_str = remove_query_arg( 'wad_admin_notice_ignore' );
                wp_redirect( $query_str );
                exit;
        }
    }

    // Temp Ignore function that gets ran at admin init to ensure any messages that were temp dismissed get their start date changed
    public function admin_notice_temp_ignore() {

        // If user clicks to temp ignore the notice, update the option to change the start date - default interval of 14 days
        if ( isset($_GET['wad_admin_notice_temp_ignore']) && current_user_can( 'manage_product_terms' ) ) {
            
                 $interval = ( isset( $_GET[ 'wad_int' ] ) ? $_GET[ 'wad_int' ] : 14 );
                 set_transient( 'wad_notice_dismiss', true, MINUTE_IN_SECONDS/*$interval * DAY_IN_SECONDS*/ );
//                 update_option( 'wad_admin_notice', "1" );
//                $admin_notices_option = get_option( 'wad_admin_notice', array() );
//
//                $current_date = current_time( "n/j/Y" );
//                $date_array = explode( '/', $current_date );
//                $interval = ( isset( $_GET[ 'wad_int' ] ) ? $_GET[ 'wad_int' ] : 14 );
//                $date_array[1] += $interval;
//                $new_start = date( "n/j/Y", mktime( 0, 0, 0, $date_array[0], $date_array[1], $date_array[2] ) );
//
//                $admin_notices_option[ $_GET[ 'wad_admin_notice_temp_ignore' ] ][ 'start' ] = $new_start;
//                $admin_notices_option[ $_GET[ 'wad_admin_notice_temp_ignore' ] ][ 'dismissed' ] = 0;
//                update_option( 'wad_admin_notice', $admin_notices_option );
                $query_str = remove_query_arg( array( 'wad_admin_notice_temp_ignore', 'wad_int' ) );
                wp_redirect( $query_str );
                exit;
        }
    }


}
