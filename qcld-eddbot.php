<?php
/**
 * Plugin Name: ChatBot for Easy Digital Downloads
 * Plugin URI: https://wordpress.org/plugins/chatbot-for-easy-digital-downloads/
 * Donate link: https://www.quantumcloud.com
 * Description: ChatBot for Easy Digital Downloads. This simple and native Easy Digital Downloads ChatBot helps shoppers find products easily & increase sales! 
 * Version: 0.9.3
 * @author    QuantumCloud
 * Author: QunatumCloud
 * Author URI: https://www.quantumcloud.com/
 * Requires at least: 4.9
 * Tested up to: 6.0
 * Text Domain: eddchatbot
 * Domain Path: /lang
 * License: GPL2
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('qcld_eddCHATBOT_VERSION', '0.9.3');
define('qcld_eddCHATBOT_REQUIRED_WOOCOMMERCE_VERSION', 2.2);
define('qcld_eddCHATBOT_PLUGIN_DIR_PATH', basename(plugin_dir_path(__FILE__)));
define('qcld_eddCHATBOT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('qcld_eddCHATBOT_IMG_URL', qcld_eddCHATBOT_PLUGIN_URL . "images/");
define('qcld_eddCHATBOT_IMG_ABSOLUTE_PATH', plugin_dir_path(__FILE__) . "images");
require_once("functions.php");
require_once("qc-support-promo-page/class-qc-support-promo-page.php");
require_once("qcld-eddbot-info-page.php");
//require_once("class-qc-free-plugin-upgrade-notice.php");

/**
 * Main Class.
 */
class qcld_eddbot
{

    private $id = 'eddbot';

    private static $instance;

    /**
     *  Get Instance creates a singleton class that's cached to stop duplicate instances
     */
    public static function qcld_eddbot_get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
            self::$instance->qcld_eddbot_init();
        }
        return self::$instance;
    }

    /**
     *  Construct empty on purpose
     */

    private function __construct()
    {
    }

    /**
     *  Init behaves like, and replaces, construct
     */

    public function qcld_eddbot_init()
    {

        // Check if WooCommerce is active, and is required WooCommerce version.
        // if (!class_exists('WooCommerce') || version_compare(get_option('woocommerce_db_version'), qcld_eddCHATBOT_REQUIRED_WOOCOMMERCE_VERSION, '<')) {
        //     add_action('admin_notices', array($this, 'woocommerce_inactive_notice_for_eddbot'));
        //     return;
        // }

        add_action('admin_menu', array($this, 'qcld_eddbot_admin_menu'), 6);

        if ((!empty($_GET["page"])) && ($_GET["page"] == "eddBot")) {

            add_action('admin_init', array($this, 'qcld_eddbot_save_options'));
        }
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'qcld_eddbot_admin_scripts'));
        }
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'qcld_eddbot_frontend_scripts'));
        }
    }


    /**
     * Add a submenu item to the WooCommerce menu
     */
    public function qcld_eddbot_admin_menu()
    {

        add_menu_page('EDDBot', 'EDDBot', 'manage_options', 'eddBot', '', 'dashicons-format-status', 6);
        add_submenu_page(
            'EDDBot',
            __( 'EDDBot Control Panel', 'eddchatot' ),
            __( 'EDDBot Panel', 'eddchatot' ),
            'manage_options',
            'eddBot',
            array($this, 'qcld_eddbot_admin_page')
        );
    }



    /**
     * Include admin scripts
     */
    public function qcld_eddbot_admin_scripts($hook)
    {
        global $woocommerce, $wp_scripts;

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        if (((!empty($_GET["page"])) && ($_GET["page"] == "eddBot")) || ($hook == "widgets.php")) {

            wp_enqueue_script('jquery');

            wp_enqueue_media();

          //  wp_enqueue_style('woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css');
            if( $hook != "widgets.php" ){
                wp_register_style('qlcd-woo-chatbot-admin-style', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/admin-style.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
                wp_enqueue_style('qlcd-woo-chatbot-admin-style');
            }

            wp_register_style('qlcd-woo-chatbot-font-awesome', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/font-awesome.min.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
            wp_enqueue_style('qlcd-woo-chatbot-font-awesome');


            wp_register_style('qlcd-woo-chatbot-tabs-style', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/woo-chatbot-tabs.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
            wp_enqueue_style('qlcd-woo-chatbot-tabs-style');


            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-core');
            wp_register_script('qcld-woo-chatbot-cbpFWTabs', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/cbpFWTabs.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('qcld-woo-chatbot-cbpFWTabs');

            wp_register_script('qcld-woo-chatbot-modernizr-custom', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/modernizr.custom.js', basename(__FILE__)), array(), true);
            wp_enqueue_script('qcld-woo-chatbot-modernizr-custom');

            if( $hook != "widgets.php" ){
                wp_register_script('qcld-woo-chatbot-bootstrap-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/bootstrap.js', basename(__FILE__)), array('jquery'), true);
                wp_enqueue_script('qcld-woo-chatbot-bootstrap-js');

                wp_register_style('qcld-woo-chatbot-bootstrap-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/bootstrap.min.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
                wp_enqueue_style('qcld-woo-chatbot-bootstrap-css');
            }

            wp_register_script('qcld-woo-chatbot-repeatable', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.repeatable.js', basename(__FILE__)), array('jquery'));
            wp_enqueue_script('qcld-woo-chatbot-repeatable');

            wp_register_script('qcld-woo-chatbot-admin-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/qcld-woo-chatbot-admin.js', basename(__FILE__)), array('jquery', 'jquery-ui-core','qcld-woo-chatbot-slick'), true);
            wp_enqueue_script('qcld-woo-chatbot-admin-js');

            wp_localize_script('qcld-woo-chatbot-admin-js', 'ajax_object',
                array('ajax_url' => admin_url('admin-ajax.php')));

        }
        if (((!empty($_GET["page"])) && ($_GET["page"] == "eddBot")) || (!empty($_GET["page"])) && ($_GET["page"] == "qcpro-promo-page-eddBot-free") || (!empty($_GET["page"])) && ($_GET["page"] == "qcld_eddbot_info_page")) {
            wp_register_script('qcld-woo-chatbot-slick', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/slick.min.js', basename(__FILE__)), array('jquery'), true);
            wp_enqueue_script('qcld-woo-chatbot-slick');
            wp_register_style('qcld-woo-chatbot-slick-css', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/slick.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
            wp_enqueue_style('qcld-woo-chatbot-slick-css');
            wp_register_style('qcld-woo-chatbot-slick-theme', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/slick-theme.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
            wp_enqueue_style('qcld-woo-chatbot-slick-theme');
        }

    }


    public function qcld_eddbot_frontend_scripts(){
        global  $wp_scripts;

        $qcld_eddbot_obj = array(
            'eddbot_position_x' => get_option('eddbot_position_x'),
            'eddbot_position_y' => get_option('eddbot_position_y'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'agent_image_path'=> get_option('wp_chatbot_agent_image'),
            'image_path'=>qcld_eddCHATBOT_IMG_URL,
            'host'=> get_option('qlcd_eddbot_host'),
            'agent'=> get_option('qlcd_eddbot_agent'),
            'agent_join'=> get_option('qlcd_eddbot_agent_join'),
            'welcome'=> get_option('qlcd_eddbot_welcome'),
            'asking_name'=> get_option('qlcd_eddbot_asking_name'),
            'i_am'=> get_option('qlcd_eddbot_i_am'),
            'name_greeting'=> get_option('qlcd_eddbot_name_greeting'),
            'product_asking'=> get_option('qlcd_eddbot_product_asking'),
            'product_suggest'=> get_option('qlcd_eddbot_product_suggest'),
            'product_infinite'=> get_option('qlcd_eddbot_product_infinite'),
            'email_successfully' => get_option('qlcd_eddbot_email_successfully'),
            'provide_email_address' => get_option('qlcd_eddbot_provide_email_address'),
            'chatbot_write_your_message' => get_option('qlcd_eddbot_write_your_message'),
			'conversations_with'=> get_option('qlcd_eddbot_conversations_with'),
			'is_typing'=> get_option('qlcd_eddbot_is_typing'),
			'send_a_msg'=> get_option('qlcd_eddbot_send_a_msg'),
            'product_success'=> get_option('qlcd_eddbot_product_success'),
            'product_fail'=> get_option('qlcd_eddbot_product_fail'),
            'specific_fail'=> ( get_option('qlcd_eddbot_more_specific') ? get_option('qlcd_eddbot_more_specific') : 'Can you be more specific?' ),
            'product_search'=> ( get_option('qlcd_eddbot_product_search') ? get_option('qlcd_eddbot_product_search') : 'Product Search' ),
            'send_us_email'=> ( get_option('qlcd_eddbot_send_us_email') ? get_option('qlcd_eddbot_send_us_email') : 'Send Us Email' ),
            'catalog'=> ( get_option('qlcd_eddbot_catalog') ? get_option('qlcd_eddbot_catalog') : 'Catalog' ),
           // 'currency_symbol' => get_woocommerce_currency_symbol(),

            //bargainator
            'your_offer_price'  => (get_option('qcld_minimum_accept_price_heading_text')!=''?get_option('qcld_minimum_accept_price_heading_text'):'Please, tell me what is your offer price.'),
            'map_acceptable_prev_price'  => (get_option('qcld_minimum_accept_price_acceptable_prev_price')!=''?get_option('qcld_minimum_accept_price_acceptable_prev_price'):'We agreed on the price {offer price}. Continue?'),
            'your_offer_price_again'  => (get_option('qcld_minimum_accept_price_heading_text_again')!=''?get_option('qcld_minimum_accept_price_heading_text_again'):'It seems like you have not provided any offer amount. Please give me a number!'),
            'your_low_price_alert' => (get_option('qcld_minimum_accept_price_low_alert_text_two')!=''?get_option('qcld_minimum_accept_price_low_alert_text_two'):'Your offered price {offer price} is too low for us.'),
            'your_too_low_price_alert' => (get_option('qcld_minimum_accept_price_too_low_alert_text')!=''?get_option('qcld_minimum_accept_price_too_low_alert_text'):'The best we can do for you is {minimum amount}. Do you accept?'),
            'map_talk_to_boss' => (get_option('qcld_minimum_accept_price_talk_to_boss')!=''?get_option('qcld_minimum_accept_price_talk_to_boss'):'Please tell me your final price. I will talk to my boss.'),
            'map_get_email_address' => (get_option('qcld_minimum_accept_price_get_email_address')!=''?get_option('qcld_minimum_accept_price_get_email_address'):'Please tell me your email address so I can get back to you.'),
            'map_thanks_test' => (get_option('qcld_minimum_accept_price_thanks_test')!=''?get_option('qcld_minimum_accept_price_thanks_test'):'Thank you.'),
            'map_acceptable_price' => (get_option('qcld_minimum_accept_price_acceptable_price')!=''?get_option('qcld_minimum_accept_price_acceptable_price'):'Your offered price {offer price} is acceptable.'),
            'map_checkout_now_button_text' => (get_option('qcld_minimum_accept_modal_checkout_now_button_text')!=''?get_option('qcld_minimum_accept_modal_checkout_now_button_text'):'Checkout Now'),
           // 'map_get_checkout_url' => (wc_get_checkout_url()),
            'map_free_get_ajax_nonce' => (wp_create_nonce( 'woo-minimum-acceptable-price')),
        );

        wp_register_script('qcld-woo-chatbot-slimscroll-js', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/jquery.slimscroll.min.js', basename(__FILE__)), array('jquery'), qcld_eddCHATBOT_VERSION, true);
        wp_enqueue_script('qcld-woo-chatbot-slimscroll-js');

        wp_register_script('qcld-woo-chatbot-frontend', plugins_url(basename(plugin_dir_path(__FILE__)) . '/js/qcld-woo-chatbot-frontend.js', basename(__FILE__)), array('jquery'), qcld_eddCHATBOT_VERSION, true);
        wp_enqueue_script('qcld-woo-chatbot-frontend');

        wp_localize_script('qcld-woo-chatbot-frontend', 'qcld_eddbot_obj', $qcld_eddbot_obj);
        wp_register_style('qcld-woo-chatbot-frontend-style', plugins_url(basename(plugin_dir_path(__FILE__)) . '/css/frontend-style.css', basename(__FILE__)), '', qcld_eddCHATBOT_VERSION, 'screen');
        wp_enqueue_style('qcld-woo-chatbot-frontend-style');
    }


    /**
     * Render the admin page
     */
    public function qcld_eddbot_admin_page()
    {

        global $woocommerce;

        $action = 'admin.php?page=eddBot'; ?>
        <div class="woo-chatbot-wrap">
            <div class="icon32"><br></div>
            <form action="<?php echo esc_attr($action); ?>" method="POST" enctype="multipart/form-data">
                <div class="container form-container">
                    <h2><?php esc_html_e('EDDBot Control Panel', 'woochatbot'); ?></h2>
                    <div class="qc_get_pro">
                        <p><a href="https://www.quantumcloud.com/" target="_blank"><?php esc_html_e('EDD Bot is a project by Web Design Company QuantumCloud', 'woochatbot'); ?> </a></p>
                    </div>
                    <section class="woo-chatbot-tab-container-inner">
                        <div class="woo-chatbot-tabs woo-chatbot-tabs-style-flip">
                            <nav>
                                <ul>
                                    <li><a href="#section-flip-1"><i class="fa fa-toggle-on"></i><span><?php esc_html_e('GENERAL SETTINGS', 'woochatbot'); ?></span></a>
                                    </li>
                                    <li><a href="#section-flip-3"><i class="fa fa-gear faa-spin"></i><span><?php esc_html_e('EDDBot ICONS', 'woochatbot'); ?> </span></a></li>
                                    <li><a href="#section-flip-7"><i class="fa fa-language"></i><span><?php esc_html_e('LANGUAGE CENTER', 'woochatbot'); ?> </span></a></li>
                                    <li><a href="#section-flip-8"><i class="fa fa-code"></i><span><?php esc_html_e('Custom CSS', 'woochatbot'); ?></span></a></li>
									<!-- <li tab-data="addons"><a href="<?php echo esc_attr($action); ?>&tab=addons"> <span
                                            class="eddBot-admin-tab-icon"> <i class="fa fa-puzzle-piece" aria-hidden="true"></i> </span> <span
                                            class="eddBot-admin-tab-name">
									<?php // esc_html_e('Pro Addons', 'woochatbot'); ?>
									</span> </a></li> -->
                                </ul>
                            </nav>
                            <div class="content-wrap">
                                <section id="section-flip-1">
                                    <div class="top-section">
                                        <div class="row">
                                            
                                            <div class="col-12">
                                                
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('Emails Will be Sent to', 'woochatbot'); ?>
                                                </p>
                                                <?php
                                                $url = get_site_url();
                                                $url = parse_url($url);
                                                $domain = $url['host'];
                                                
                                                $admin_email = get_option('admin_email');
                                                ?>
                                                <div class="cxsc-settings-blocks">
                                                    <input type="text" class="form-control qc-opt-dcs-font"
                                                        name="qlcd_wp_chatbot_admin_email"
                                                        value="<?php echo esc_attr(get_option('qlcd_wp_chatbot_admin_email') != '' ? get_option('qlcd_wp_chatbot_admin_email') : $admin_email); ?>">
                                                </div>
                                            </div>
<!--
                                            <div class="col-12">
                                                
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('From Name', 'woochatbot'); ?>
                                                </p>
                                                
                                                <div class="cxsc-settings-blocks">
                                                    <input type="text" class="form-control qc-opt-dcs-font"
                                                        name="qlcd_wp_chatbot_admin_email_name"
                                                        value="<?php echo(get_option('qlcd_wp_chatbot_admin_email_name') != '' ? get_option('qlcd_wp_chatbot_admin_email_name') : ''); ?>">
                                                </div>
                                            </div>
-->
                                            <div class="col-12">
                                                <?php
                                                //Extract Domain
                                                $url = get_site_url();
                                                $url = parse_url($url);
                                                $domain = $url['host'];
                                                $fromEmail = "wordpress@" . $domain;
                                                ?>
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('From Email Address', 'woochatbot'); ?>
                                                </p>
                                                
                                                <div class="cxsc-settings-blocks">
                                                    <input type="text" class="form-control qc-opt-dcs-font"
                                                        name="qlcd_wp_chatbot_admin_from_email"
                                                        value="<?php echo(get_option('qlcd_wp_chatbot_admin_from_email') != '' ? get_option('qlcd_wp_chatbot_admin_from_email') : $fromEmail); ?>">
                                                </div>
                                            </div>
                                        
                                            <div class="col-12">
                                                <br>
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('Disable eddBot', 'woochatbot'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input  value="1" id="disable_eddbot" type="checkbox" name="disable_eddbot" <?php echo(get_option('disable_eddbot') == 1 ? 'checked' : ''); ?>>
                                                    <label for="disable_eddbot"><?php esc_html_e('Disable eddBot to load', 'woochatbot'); ?> </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <p class="qc-opt-title-font"> <?php esc_html_e('Disable eddBot on Mobile Device', 'woochatbot'); ?> </p>
                                                <div class="cxsc-settings-blocks">
                                                    <input value="1" id="disable_eddbot_on_mobile" type="checkbox"
                                                           name="disable_eddbot_on_mobile" <?php echo(get_option('disable_eddbot_on_mobile') == 1 ? 'checked' : ''); ?>>
                                                    <label for="disable_eddbot_on_mobile"><?php esc_html_e('Disable eddBot to Load on Mobile Device', 'woochatbot'); ?> </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('Override EDDBot Icon\'s Position', 'woochatbot'); ?>
                                                </p>
                                                <div class="cxsc-settings-blocks">
                                                    <?php
                                                    $eddbot_position_x = esc_attr(get_option('eddbot_position_x'));
                                                    if ((!isset($eddbot_position_x)) || ($eddbot_position_x == "")) {
                                                        $eddbot_position_x = __("120", "eddbot");
                                                    }
                                                    $eddbot_position_y = esc_attr(get_option('eddbot_position_y'));
                                                    if ((!isset($eddbot_position_y)) || ($eddbot_position_y == "")) {
                                                        $eddbot_position_y = __("50", "eddbot");
                                                    } ?>

                                                    <input type="number" class="qc-opt-dcs-font"
                                                           name="eddbot_position_x"
                                                           id=""
                                                           value="<?php echo esc_attr($eddbot_position_x); ?>"
                                                           placeholder="From Right In px"> <span class="qc-opt-dcs-font"><?php esc_html_e('From Right In px', 'woochatbot'); ?></span>
                                                    <input type="number" class="qc-opt-dcs-font"
                                                           name="eddbot_position_y"
                                                           id=""
                                                           value="<?php echo esc_attr($eddbot_position_y); ?>"
                                                           placeholder="From Bottom In Px"> <span class="qc-opt-dcs-font"><?php esc_html_e('From Bottom In px', 'woochatbot'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <?php $number_of_product_to_show = get_option('qlcd_eddbot_ppp')!=''? esc_attr(get_option('qlcd_eddbot_ppp')) :10; ?>
                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Number of products to show in search results. ( \'-1\' for all products ).', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_ppp" value="<?php echo esc_attr($number_of_product_to_show); ?>">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <p class="qc-opt-title-font">
                                                    <?php esc_html_e('Page controller', 'woochatbot'); ?>
                                                </p>
                                            </div>
                                            <div class="col-sm-4 text-right"> <span class="qc-opt-title-font">
                                                <?php esc_html_e('Show on Home Page', 'eddchatbot'); ?>
                                                </span> </div>
                                            <div class="col-sm-8">
                                                <label class="radio-inline">
                                                <input id="wp-chatbot-show-home-page" type="radio"
                                                                                    name="wp_chatbot_show_home_page"
                                                                                    value="on" <?php echo( get_option('wp_chatbot_show_home_page') == 'on' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('YES', 'eddchatbot'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                <input id="wp-chatbot-show-home-page" type="radio"
                                                                                    name="wp_chatbot_show_home_page"
                                                                                    value="off" <?php echo(get_option('wp_chatbot_show_home_page') == 'off' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('NO', 'eddchatbot'); ?>
                                                </label>
                                            </div>
                                            </div>
                                            <!--  row-->
                                            <div class="row">
                                            <div class="col-sm-4 text-right"> <span class="qc-opt-title-font">
                                                <?php esc_html_e('Show on blog posts', 'eddchatbot'); ?>
                                                </span> </div>
                                            <div class="col-sm-8">
                                                <label class="radio-inline">
                                                <input class="wp-chatbot-show-posts" type="radio"
                                                                                    name="wp_chatbot_show_posts"
                                                                                    value="on" <?php echo(get_option('wp_chatbot_show_posts') == 'on' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('YES', 'eddchatbot'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                <input class="wp-chatbot-show-posts" type="radio"
                                                                                    name="wp_chatbot_show_posts"
                                                                                    value="off" <?php echo(get_option('wp_chatbot_show_posts') == 'off' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('NO', 'eddchatbot'); ?>
                                                </label>
                                            </div>
                                            </div>
                                            <!-- row-->
                                            <div class="row">
                                            <div class="col-md-4 text-right"> <span class="qc-opt-title-font">
                                                <?php esc_html_e('Show on  pages', 'eddchatbot'); ?>
                                                </span> </div>
                                            <div class="col-md-8">
                                                <label class="radio-inline">
                                                <input class="wp-chatbot-show-pages" type="radio"
                                                                                    name="wp_chatbot_show_pages"
                                                                                    value="on" <?php echo(get_option('wp_chatbot_show_pages') == 'on' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('All Pages', 'eddchatbot'); ?>
                                                </label>
                                                <label class="radio-inline">
                                                <input class="wp-chatbot-show-pages" type="radio"
                                                                                    name="wp_chatbot_show_pages"
                                                                                    value="off" <?php echo(get_option('wp_chatbot_show_pages') == 'off' ? esc_attr('checked') : ''); ?>>
                                                <?php esc_html_e('Selected Pages Only ', 'eddchatbot'); ?>
                                                </label>
                                                <div id="wp-chatbot-show-pages-list">
                                                <ul class="checkbox-list">
                                                    <?php
                                                        $wp_chatbot_pages = get_pages();
                                                        $wp_chatbot_select_pages = unserialize(get_option('wp_chatbot_show_pages_list'));
                                                        if(get_option('wp_chatbot_show_pages') == 'off'){

                                                       
                                                        foreach ($wp_chatbot_pages as $wp_chatbot_page) {
                                                    ?>
                                                    <li>
                                                    <input id="wp_chatbot_show_page_<?php echo esc_attr($wp_chatbot_page->ID); ?>"
                                                            type="checkbox"
                                                            name="wp_chatbot_show_pages_list[]"
                                                            value="<?php echo $wp_chatbot_page->ID; ?>" <?php if (!empty($wp_chatbot_select_pages) && in_array($wp_chatbot_page->ID, $wp_chatbot_select_pages) == true) {
                                                        echo esc_attr('checked');
                                                    } ?> >
                                                    <label for="wp_chatbot_show_page_<?php echo esc_attr($wp_chatbot_page->ID); ?>"> <?php echo esc_html($wp_chatbot_page->post_title); ?></label>
                                                    </li>
                                                    <?php }  }?>
                                                </ul>
                                                </div>
                                            </div>
                                            </div>
                                            <!--row-->
                                            <div class="row">
                                            <div class="col-sm-4 text-right"> <span class="qc-opt-title-font">
                                                <?php _e('Exclude from Custom Post', 'eddchatbot'); ?>
                                                </span></div>
                                            <div class="col-sm-8">
                                                <div id="wp-chatbot-exclude-post-list">
                                                <ul class="checkbox-list">
                                                    <?php
                                                    $get_cpt_args = array(
                                                        'public'   => true,
                                                        '_builtin' => false
                                                    );
                                                    
                                                    $post_types = get_post_types( $get_cpt_args, 'object' );
                                                    $wp_chatbot_exclude_post_list = maybe_unserialize(get_option('wp_chatbot_exclude_post_list'));
                                                    
                                                    foreach ($post_types as $post_type) {
                                                        ?>
                                                    <li>
                                                    <input
                                                            id="wp_chatbot_exclude_post_<?php echo esc_attr($post_type->name); ?>"
                                                            type="checkbox"
                                                            name="wp_chatbot_exclude_post_list[]"
                                                            value="<?php echo esc_html($post_type->name); ?>" <?php if (!empty($wp_chatbot_exclude_post_list) && in_array($post_type->name, $wp_chatbot_exclude_post_list) == true) {
                                                        echo esc_html('checked');
                                                    } ?> >
                                                    <label
                                                    for="wp_chatbot_exclude_post_<?php echo esc_attr($post_type->name); ?>"> <?php echo esc_html($post_type->name); ?></label>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section id="section-flip-3">
                                    <div class="top-section">
                                        <div class="row">
                                            <div class="col-12">
                                                <ul class="radio-list">
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-0.png"
                                                             alt=""> <input type="radio"
                                                                            name="eddbot_icon" <?php echo(get_option('eddbot_icon') == 'icon-0.png' ? esc_attr('checked') : ''); ?>
                                                                            value="icon-0.png">
                                                       <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 0', 'woochatbot'); ?></span>
                                                    </li>


                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-1.png"
                                                             alt=""> <input type="radio"
                                                                            name="eddbot_icon" <?php echo(get_option('eddbot_icon') == 'icon-1.png' ? esc_attr('checked') : ''); ?>
                                                                            value="icon-1.png">
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 1', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-2.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-2.png" <?php echo(get_option('eddbot_icon') == 'icon-2.png' ? esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 2', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-3.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-3.png" <?php echo(get_option('eddbot_icon') == 'icon-3.png' ? esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 3', 'woochatbot'); ?></span>
                                                    </li>

                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-4.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-4.png" <?php echo(get_option('eddbot_icon') == 'icon-4.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 4', 'woochatbot'); ?></span>
                                                    </li>


                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-5.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-5.png" <?php echo(get_option('eddbot_icon') == 'icon-5.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 5', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-6.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-6.png" <?php echo(get_option('eddbot_icon') == 'icon-6.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 6', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-7.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-7.png" <?php echo(get_option('eddbot_icon') == 'icon-7.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 7', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-8.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-8.png" <?php echo(get_option('eddbot_icon') == 'icon-8.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font">Icon - 8</span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-9.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-9.png" <?php echo(get_option('eddbot_icon') == 'icon-9.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon -9', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-10.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-10.png" <?php echo(get_option('eddbot_icon') == 'icon-10.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 10', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-11.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-11.png" <?php echo(get_option('eddbot_icon') == 'icon-11.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 11', 'woochatbot'); ?></span>
                                                    </li>
                                                    <li><img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/icon-12.png"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="icon-12.png" <?php echo(get_option('eddbot_icon') == 'icon-12.png' ?  esc_attr('checked') : ''); ?>>
                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Icon - 12', 'woochatbot'); ?></span>
                                                    </li>


                                                    <li>
                                                        <img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>/custom.png?<?php echo time(); ?>"
                                                             alt=""> <input type="radio" name="eddbot_icon"
                                                                            value="custom.png" <?php echo(get_option('eddbot_icon') == 'custom.png' ?  esc_attr('checked') : ''); ?>>

                                                        <span class="qc-opt-dcs-font"><?php esc_html_e('Custom Icon', 'woochatbot'); ?></span>
                                                    </li>


                                                </ul>
                                            </div>
                                        </div>
                                        </br></br>
                                        <div class="row">
                                            <div class="col-12">
                                                <h4 class="qc-opt-title">
                                                    <?php esc_html_e('Upload custom Icon', 'woochatbot'); ?>
                                                </h4>
                                                <div class="cxsc-settings-blocks">
                                                    <p class="qc-opt-dcs-font"><?php echo __('Select file to upload') ?><input type="file" name="custom_icon" id="custom_icon"  size="35" class=""/>
                                                        
                                                </div>
                                            </div>
                                        </div>
										</br></br>
                                        <div class="row">
                                            <div class="col-12">



                                        <div class="top-section">
                                            <div class="">
                                                <div class="col-xs-12">
                                                    <h4 class="qc-opt-title"><?php esc_html_e(' WPBot Agent Image', 'eddchatbot'); ?></h4>
                                                    <div class="cxsc-settings-blocks">
                                                        <ul class="radio-list">
                                                            <li>
                                                                <label for="wp_chatbot_agent_image_def" class="qc-opt-dcs-font">
                                                                <img src="<?php echo qcld_eddCHATBOT_IMG_URL; ?>icon-0.png"
                                                                    alt=""> 
                                                                    <input id="wp_chatbot_agent_image_def" type="radio"
                                                                                    name="wp_chatbot_agent_image" <?php echo(get_option('wp_chatbot_agent_image') ==  qcld_eddCHATBOT_IMG_URL.'icon-0.png' ? 'checked' : ''); ?>
                                                                                    value="<?php echo qcld_eddCHATBOT_IMG_URL; ?>icon-0.png">
                                                                                
                                                                <?php esc_html_e('Default Agent', 'eddchatbot'); ?></label>
                                                            </li>
                                                            <li>
                                                                <?php
                                                                if (get_option('wp_chatbot_custom_agent_path') != "") {
                                                                    $wp_chatbot_custom_agent_path = esc_attr(get_option('wp_chatbot_custom_agent_path'));
                                                                } else {
                                                                    $wp_chatbot_custom_agent_path = qcld_eddCHATBOT_IMG_URL . 'custom-agent.png';
                                                                }
                                                                ?>
                                                                <label for="wp_chatbot_agent_image_custom" class="qc-opt-dcs-font">
                                                                    <img id="wp_chatbot_custom_agent_src"
                                                                    src="<?php echo esc_url($wp_chatbot_custom_agent_path); ?>"
                                                                    alt="Agent">
                                                                <input type="radio" name="wp_chatbot_agent_image"
                                                                    id="wp_chatbot_agent_image_custom"
                                                                    value="<?php echo esc_url($wp_chatbot_custom_agent_path); ?>" <?php echo(get_option('wp_chatbot_agent_image') !=  qcld_eddCHATBOT_IMG_URL.'icon-0.png' ? esc_attr('checked') : ''); ?>>
                                                                <?php echo esc_html__('Custom Agent', 'eddchatbot'); ?></label>
                                                            </li>
                                                            
                                                        </ul>
                                                    </div>
                                                    <!--                                        cxsc-settings-blocks-->
                                                </div>
                                            </div>
                                        </div>
                                        </br></br>
                                        <div class="top-section">
                                        <div class="">
                                            <div class="col-xs-12">
                                                <h4 class="qc-opt-title"> <?php esc_html_e('Custom Agent Icon', 'eddchatbot'); ?>  </h4>
                                                <div class="cxsc-settings-blocks">
                                                    <input type="hidden" name="wp_chatbot_custom_agent_path"
                                                        id="wp_chatbot_custom_agent_path"
                                                        value="<?php echo $wp_chatbot_custom_agent_path; ?>"/>
                                                    <button type="button" class="wp_chatbot_custom_agent_button button"><?php esc_html_e('Upload Agent Icon', 'eddchatbot'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                        </br></br>
                                    </div>
                                                

                                          <div id="top-section">
                                                        <div class="row">
                                                          <div class="col-sm-12">
                                                            <h4 class="qc-opt-title">
                                                              <?php esc_html_e('Custom Backgroud', 'eddchatbot'); ?>
                                                            </h4>
                                                            <div class="cxsc-settings-blocks">
                                                              <input value="1" id="qcld_eddbot_change_bg" type="checkbox" name="qcld_eddbot_change_bg" <?php echo(get_option('qcld_eddbot_change_bg') == 1 ? esc_attr('checked') : ''); ?>>
                                                              <label for="qcld_eddbot_change_bg">
                                                                <?php esc_html_e('Change the  message board background image (except mini mode).', 'woochatbot'); ?>
                                                              </label>
                                                            </div>
                                                          </div>
                                                        </div>
                                                        <div class="row qcld-woo-chatbot-board-bg-container" <?php if (get_option('qcld_eddbot_change_bg') != 1) {
                                                                                echo 'style="display:none"';
                                                                            } ?>>
                                                          <div class="col-md-6 col-12">
                                                            <p class="woo-chatbot-settings-instruction">
                                                              <?php esc_html_e('Upload  message board background (Ideal image size 350px X 550px).', 'woochatbot'); ?>
                                                            </p>
                                                            <div class="cxsc-settings-blocks">
                                                              <?php
                                                                if (get_option('qcld_eddbot_board_bg_path') != "") {
                                                                    $qcld_eddbot_board_bg_path = esc_attr(get_option('qcld_eddbot_board_bg_path'));
                                                                } else {
                                                                    $qcld_eddbot_board_bg_path = '';
                                                                }
                                                                ?>
                                                              <input type="hidden" name="qcld_eddbot_board_bg_path"
                                                                                               id="qcld_eddbot_board_bg_path"
                                                                                               value="<?php echo esc_attr($qcld_eddbot_board_bg_path); ?>"/>
                                                              <button type="button" class="qcld_eddbot_board_bg_button button">
                                                              <?php esc_html_e('Upload  background.', 'woochatbot'); ?>
                                                              </button>
                                                            </div>
                                                          </div>
                                                          <!-- col-xs-6 -->
                                                          <div class="col-md-6 col-12">
                                                            <p class="woo-chatbot-settings-instruction">
                                                              <?php esc_html_e('Custom message board background', 'woochatbot'); ?>
                                                            </p>
                                                            <?php if (get_option('qcld_eddbot_board_bg_path') != "") { ?>
                                                            <img id="qcld_eddbot_board_bg_image" style="height:100%;width:100%" src="<?php echo esc_url($qcld_eddbot_board_bg_path); ?>" alt="">
                                                            <?php }else{ ?>
                                                            <img id="qcld_eddbot_board_bg_image" style="height:100%;width:100%; display: none;" src="" alt="">
                                                            <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>      


                                            </div>
                                        </div>


                                    </div>
                                </section>
                                <section id="section-flip-7">
                                    <div class="top-section">
                                        <div class="row">
                                            <div class="col-12" id="woo-chatbot-language-section">
                                                <p class="qc-opt-title-font"> <?php esc_html_e('Message setting for', 'woochatbot'); ?> <strong><?php esc_html_e('Identity', 'woochatbot'); ?> </strong ></p>

                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Your Company or Website Name', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_host" value="<?php echo esc_attr(get_option('qlcd_eddbot_host')!=''? get_option('qlcd_eddbot_host') :'Our Store');?>">
                                                </div>
                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Agent name', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_agent" value="<?php echo esc_attr(get_option('qlcd_eddbot_agent')!=''? get_option('qlcd_eddbot_agent') :'Carrie');?>">
                                                </div>
                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('has joined the conversation', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_agent_join" value="<?php echo esc_attr(get_option('qlcd_eddbot_agent_join')!=''? get_option('qlcd_eddbot_agent_join') :'has joined the conversation');?>">
                                                </div>
                                            </div>
                                            <div class="col-12" id="woo-chatbot-language-section">
                                                <p class="qc-opt-title-font"> <?php esc_html_e('Message setting for', 'woochatbot'); ?> <strong><?php esc_html_e('Greetings', 'woochatbot'); ?>: </strong ></p>
                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Welcome to ', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_welcome" value="<?php echo esc_attr(get_option('qlcd_eddbot_welcome')!=''? esc_attr(get_option('qlcd_eddbot_welcome')) :'Welcome to ');?>">
                                                </div>

                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Hi There! May I know your name?', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_asking_name" value="<?php echo esc_attr(get_option('qlcd_eddbot_asking_name')!=''? get_option('qlcd_eddbot_asking_name') :'Hi There! May I know your name?');?>">
                                                </div>
                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('I am ', 'woochatbot'); ?> </p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_i_am" value="<?php echo esc_attr(get_option('qlcd_eddbot_i_am')!=''? get_option('qlcd_eddbot_i_am') :'I am ');?>">
                                                </div>

                                                <div class="form-group">
                                                    <p class="qc-opt-title-font"><?php esc_html_e('Nice to meet you', 'woochatbot'); ?></p>
                                                    <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_name_greeting" value="<?php echo esc_attr(get_option('qlcd_eddbot_name_greeting')!=''? get_option('qlcd_eddbot_name_greeting') :'Nice to meet you');?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="top-section">
                                        <div class="row">
                                            <div class="col-12" id="woo-chatbot-language-section">
                                                <p class="qc-opt-title-font"><?php esc_html_e('Message settings for', 'woochatbot'); ?> <strong> <?php esc_html_e('Editor Box', 'woochatbot'); ?>:</strong ></p>
                                                <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Conversations with', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_conversations_with" value="<?php echo esc_attr(get_option('qlcd_eddbot_conversations_with')!=''? get_option('qlcd_eddbot_conversations_with') :'Conversations with');?>">
                                            </div>
                                                
                                                
                                                
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('is typing...', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_is_typing" value="<?php echo esc_attr(get_option('qlcd_eddbot_is_typing')!=''? get_option('qlcd_eddbot_is_typing') :'is typing...');?>">
                                            </div>
                                            
                                             <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Send a message', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_send_a_msg" value="<?php echo esc_attr(get_option('qlcd_eddbot_send_a_msg')!=''? get_option('qlcd_eddbot_send_a_msg') :'Send a message');?>">
                                            </div>

                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="top-section">
                                        <div class="row">
                                            <div class="col-12" id="woo-chatbot-language-section">
                                                <p class="qc-opt-title-font"><?php esc_html_e('Message settings for', 'woochatbot'); ?> <strong> <?php esc_html_e('Products Search', 'woochatbot'); ?>:</strong ></p>
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('I am here to find you the product you need. What are you shopping for', 'woochatbot'); ?> </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_asking" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_asking')!=''? get_option('qlcd_eddbot_product_asking') :'I am here to find you the product you need. What are you shopping for');?>">
                                            </div>
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('if products found', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_success" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_success')!=''? get_option('qlcd_eddbot_product_success') :'Great! We have these products.');?>">
                                            </div>

                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('If no matching products is found', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_fail" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_fail')!=''? get_option('qlcd_eddbot_product_fail') :'Oops! Nothing matches your criteria ');?>">
                                            </div>
                                            
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Can you be more specific?', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_more_specific" value="<?php echo esc_attr(get_option('qlcd_eddbot_more_specific')!=''? get_option('qlcd_eddbot_more_specific') :'Can you be more specific?');?>">
                                            </div>

                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Product Search', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_search" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_search')!=''? get_option('qlcd_eddbot_product_search') :'Product Search');?>">
                                            </div>

                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Catalog', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_catalog" value="<?php echo esc_attr(get_option('qlcd_eddbot_catalog')!=''? get_option('qlcd_eddbot_catalog') :'Catalog');?>">
                                            </div>

                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Send Us Email', 'woochatbot'); ?>: </p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_send_us_email" value="<?php echo esc_attr(get_option('qlcd_eddbot_send_us_email')!=''? get_option('qlcd_eddbot_send_us_email') :'Send Us Email');?>">
                                            </div>

                                             <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('You can browse our extensive catalog. Just pick a category from below', 'woochatbot'); ?>:</p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_suggest" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_suggest')!=''? get_option('qlcd_eddbot_product_suggest') :'You can browse our extensive catalog. Just pick a category from below:');?>">
                                            </div>
                                             <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Too many choices? Let\'s try another search term', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_product_infinite" value="<?php echo esc_attr(get_option('qlcd_eddbot_product_infinite')!=''? get_option('qlcd_eddbot_product_infinite') :"Too many choices? Let's try another search term");?>">
                                            </div>
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Email has been sent successfully', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_email_successfully" value="<?php echo esc_attr(get_option('qlcd_eddbot_email_successfully')!=''? get_option('qlcd_eddbot_email_successfully') :"Your email has been sent successfully! We will post a reply very soon. Thank you!");?>">
                                            </div>
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Please provide your email address', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_provide_email_address" value="<?php echo esc_attr(get_option('qlcd_eddbot_provide_email_address')!=''? get_option('qlcd_eddbot_provide_email_address') :"Please provide your email address");?>">
                                            </div>
                                            <div class="form-group">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('Please write you message', 'woochatbot'); ?></p>
                                                <input type="text" class="form-control qc-opt-dcs-font" name="qlcd_eddbot_write_your_message" value="<?php echo esc_attr(get_option('qlcd_eddbot_write_your_message')!=''? get_option('qlcd_eddbot_write_your_message') :"Please write you message");?>">
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                </section>
                                <section id="section-flip-8">
                                    <div class="top-section">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="qc-opt-dcs-font"><?php esc_html_e('You can paste or write your custom css here.', 'woochatbot'); ?></p>
                                                <textarea name="eddbot_custom_css"
                                                          class="form-control woo-chatbot-custom-css"
                                                          cols="10"
                                                          rows="8"><?php echo esc_textarea(get_option('eddbot_custom_css')); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                               
								
								<section id="section-flip-15">
								  <div class="top-section">
									<div class="row">
									  <div class="col-12">

										
									<?php wp_enqueue_style( 'qcpd-google-font-lato', 'https://fonts.googleapis.com/css?family=Lato' ); ?>
									<?php wp_enqueue_style( 'qcpd-style-addon-page', qcld_eddCHATBOT_PLUGIN_URL.'qc-support-promo-page/css/style.css' ); ?>
									<?php wp_enqueue_style( 'qcpd-style-responsive-addon-page', qcld_eddCHATBOT_PLUGIN_URL.'qc-support-promo-page/css/responsive.css' ); ?>
									
							<div class="qc_support_container" style="background-color:#fff;border:none;"><!--qc_support_container-->

							<div class="qc_tabcontent clearfix-div">
							<div class="qc-row">
								<div class="wpbot-chatbot-pro-link">
								
								
								
<div class="support-block support-block-custom support-block-top">
		<div class="support-block-img">
			<a href="https://www.quantumcloud.com/products/woocommerce-chatbot-eddBot/" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/logo-woow.png'); ?>" /></a>
		</div>
		<div class="support-block-info" style="    padding: 0 40px;">
			<h4><a style="    color: #a0408d;font-weight: bold; font-size: 26px;" href="https://www.quantumcloud.com/products/woocommerce-chatbot-eddBot/" target="_blank">Get the #1 ChatBot for WooCommerce – eddBot Pro</a></h4>

			
<p style="text-align: center;">			
eddBot Pro is a WooCommerce Shopping ChatBot that can help Increase your store Sales perceptibly. Shoppers can converse fluidly with the Bot – thanks to its Integration with Google‘s Dialogflow, Search and Add products to the cart directly from the chat interface, get Support and more!
</p>
<p style="text-align: center;">The Onsite Retargeting helps your Conversion rate optimization by showing special offers and coupons on Exit Intent, time interval or page scroll-down. Track Customer Conversions with statistics to find out if shoppers are abandoning carts. Get more sales!			
</p>			
			
			<a class="IncreaseSales" href="https://www.quantumcloud.com/products/woocommerce-chatbot-eddBot/" target="_blank">Get the eddBot Pro Now and Increase Sales!</a>

		</div>
	</div>								
								
								
								
								</div>
								
								
								
								
								
							<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/muli-lamguage.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Multi Language Addon (**new)</a></h4>
											<p>Add multiple language support for your ChatBot. User can change language from drop down menu any time. Admin can select default language. Supports all major languages. Connect with different Dialogflow agents for different languages<p/>
										</div>
									</div>
								</div>								
																
								
<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/voice-message.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Voice Message AddOn (**new)</a></h4>
				<p>Allow your customers to record a voice message from the ChatBot interface. Voice messages are saved in the backend to listen to any time. Supports speech to text using Google API. Compatible with all Modern Browsers. Beautiful modern User Interface<p/>
			</div>
		</div>
	</div>								
																
								
<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/templates-addon-2-1-300x300.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Extended UI Addon</a></h4>
				<p>Give your beloved ChatBot a facelift. Choose from 2 additional modern, slick and quite fancy templates! These new templates are sure to WOW your website visitors! New loader effect and Extensive color customization options are available!<p/>
			</div>
		</div>
	</div>								
								
<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/simple-text-responses-300x300.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Simple Text Responses Pro</a></h4>
				<p>Create text based responses for your customer queries easily with CSV export/import feature. STR Pro supports categories for Simple text responses for back end and front end. HTML visual editor to format your ChatBot replies and removing stop words for better search mathing.<p/>
			</div>
		</div>
	</div>								
								
								
								
<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/bargaining-chatbot.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Bargaining ChatBot for eddBot</a></h4>
				<p>Make Your Offer Now with the Bargaining ChatBot. Win more customers with smart price negotiations. Allow your customers to make an offer on your price. Negotiate a minimum price set by you product wise. Capture shoppers while they have a high intent to purchase. The Make your Offer button will only show on product single page that you set the minimum price for.<p/>
			</div>
		</div>
	</div>		



								
<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/icon-256x2561.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Conversational Form Builder</a></h4>
				<p>Create conditional conversations and forms for a native WordPress ChatBot experience  Build Standard Forms, Dynamic Forms with conditional fields, Calculators, Appointment booking etc. Comes with 7 ready templates built-in. Saves form data into database, auto response, conditional fields, variables, saved revisions and more!
<p/>
			</div>
		</div>
	</div>


<div class="qc-column-6"><!-- qc-column-4 -->
		<!-- Feature Box 1 -->
		<div class="support-block support-block-custom">
			<div class="support-block-img">
				<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/chatbot-settings.png'); ?>" alt=""></a>
			</div>
			<div class="support-block-info">
				<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Export Import Settings</a></h4>
				<p>Using the WPBot Pro on multiple websites? Then this nifty little addon may come in handy. This addon allows you to export your settings and import them back in another site or if you want to just keep a back up. Very helpful for porting the Language center settings which can be a handful with lots of options. Grab it now!
<p/>
			</div>
		</div>
	</div>



								
								
								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/messenger-chatbot.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Messenger ChatBot Addon</a></h4>
											<p>Utilize the WPBot on your website as a hub to respond to customer questions on FB Page & Messenger</p>

										</div>
									</div>
								</div><!--/qc-column-4 -->
								
								
								
								
								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/custom-post-type-addon-logo.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Extended Search</a></h4>
											<p>Extend WPBot’s search power to include almost any Custom Post Type including WooCommerce</p>

										</div>
									</div>
								</div><!--/qc-column-4 -->
								
								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/chatbot-sesssion-save.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">ChatBot Session Save Addon</a></h4>
											<p>This AddOn saves the user chat sessions and helps you fine tune the bot for better support and performance.</p>

										</div>
									</div>
								</div><!--/qc-column-4 -->
								
								
								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/WPBot-LiveChat.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">LiveChat Addon</a></h4>
											<p>Live Human Chat integrated with WPBot<p/>
										</div>
									</div>
								</div><!--/qc-column-4 -->

								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/white-label.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">White Label WPBot</a></h4>
											<p>Replace the QuantumCloud Logo and branding with yours. Suitable for developers and agencies interested in providing ChatBot services for their clients.<p/>
										</div>
									</div>
								</div><!--/qc-column-4 -->
								
								<div class="qc-column-6"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block support-block-custom">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank"> <img src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/mailing-list-integrationt.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">Mailing List Integration AddOn</a></h4>
											<p>Mailing List Integration is the ChatBot addon that lets you connect with your Mailchimp and Zapier accounts. You can add new subscribers to your Mailchimp Lists from the ChatBot and unsubscribe them. You can also create new Zap on your Zapier Account and connect with this addon.<p/>
										</div>
									</div>
								</div><!--/qc-column-4 -->
								<!--<div class="qc-column-12">
									<div style="text-align:center;font-size: 26px;">and <span style="font-size:50px"><a href="<?php echo esc_url('https://www.quantumcloud.com/products/chatbot-addons/'); ?>" target="_blank">More..</a></span></div>
								</div>-->
								<div class="qc-column-12"><!-- qc-column-4 -->
									<!-- Feature Box 1 -->
									<div class="support-block ">
										<div class="support-block-img">
											<a href="<?php echo esc_url('https://www.quantumcloud.com/products/themes/eddBot-theme/'); ?>" target="_blank"> <img class="wp_addon_fullwidth" src="<?php echo esc_url(qcld_eddCHATBOT_PLUGIN_URL.'images/ChatBot-Master-theme.png'); ?>" alt=""></a>
										</div>
										<div class="support-block-info">
											<h4><a href="<?php echo esc_url('https://www.quantumcloud.com/products/themes/eddBot-theme/'); ?>" target="_blank">eddBot Master Theme</a></h4>
											<p style="margin-top: -18px;">Get a eddBot Powered Theme!</p>
										</div>
									</div>
								</div><!--/qc-column-4 -->
								

							</div>
							<!--qc row-->
							</div>

							</div><!--qc_support_container-->
										
										
									  </div>
									</div>
									<!--                                row--> 
								  </div>
								</section>
								
                            </div><!-- /content -->
                        </div><!-- /woo-chatbot-tabs -->
                        <hr>
                        <div class="text-right">
                            <input type="submit" class="btn btn-primary submit-button" name="submit"
                                   id="submit" value="<?php esc_attr_e('Save Settings', 'eddbot'); ?>"/>
                        </div>
                    </section>
                </div>


                <?php wp_nonce_field('eddbot'); ?>
            </form>


        </div>


        <?php

    }

    function qcld_eddbot_save_options()
    {


        global $woocommerce;
        if (isset($_POST['_wpnonce']) && $_POST['_wpnonce']) {


            wp_verify_nonce($_POST['_wpnonce'], 'eddbot');


            // Check if the form is submitted or not

            if (isset($_POST['submit'])) {

                //eddBoticon position settings.
                if (isset($_POST["eddbot_position_x"])) {
                    $eddbot_position_x = intval(sanitize_text_field( $_POST["eddbot_position_x"]));
                    update_option('eddbot_position_x', $eddbot_position_x);
                }
                if (isset($_POST["eddbot_position_y"])) {
                    $eddbot_position_y = intval(sanitize_text_field($_POST["eddbot_position_y"]));
                    update_option('eddbot_position_y', $eddbot_position_y);
                }
                //Enable or disable eddBot
                if (isset($_POST["disable_eddbot"])) {
                    $disable_eddbot = $_POST["disable_eddbot"] ? sanitize_text_field($_POST["disable_eddbot"]) : '';
                    update_option('disable_eddbot', $disable_eddbot);
                }else{
                    update_option('disable_eddbot', '');

                }
                
                if (isset($_POST["qlcd_wp_chatbot_admin_email"])) {
                    $qlcd_wp_chatbot_admin_email = sanitize_email($_POST["qlcd_wp_chatbot_admin_email"]);
                    update_option('qlcd_wp_chatbot_admin_email', $qlcd_wp_chatbot_admin_email);
                }
                
                if (isset($_POST["qlcd_wp_chatbot_admin_from_email"])) {
                    $qlcd_wp_chatbot_admin_from_email = sanitize_text_field($_POST["qlcd_wp_chatbot_admin_from_email"]);
                    update_option('qlcd_wp_chatbot_admin_from_email', $qlcd_wp_chatbot_admin_from_email);
                }
                
                if (isset($_POST["qlcd_wp_chatbot_admin_email_name"])) {
                    $qlcd_wp_chatbot_admin_email_name = sanitize_text_field($_POST["qlcd_wp_chatbot_admin_email_name"]);
                    update_option('qlcd_wp_chatbot_admin_email_name', $qlcd_wp_chatbot_admin_email_name);
                }

                //Enable or disable on mobile device
                if (isset($_POST["disable_eddbot_on_mobile"])) {
                $disable_eddbot_on_mobile = $_POST["disable_eddbot_on_mobile"] ? sanitize_text_field($_POST["disable_eddbot_on_mobile"]) : '';
                update_option('disable_eddbot_on_mobile', $disable_eddbot_on_mobile);
                }else{
                update_option('disable_eddbot_on_mobile', '');

                }
                //page controll of chatbot
                if(isset($_POST["wp_chatbot_show_home_page"])){
					$wp_chatbot_show_home_page = sanitize_key(($_POST["wp_chatbot_show_home_page"]));
					update_option('wp_chatbot_show_home_page', $wp_chatbot_show_home_page);
				}
               
				
				if(isset($_POST["wp_chatbot_show_posts"])){
					$wp_chatbot_show_posts = sanitize_key(($_POST["wp_chatbot_show_posts"]));
					update_option('wp_chatbot_show_posts', $wp_chatbot_show_posts);
				}
                
				
				if(isset($_POST["wp_chatbot_show_pages"])){
					$wp_chatbot_show_pages = sanitize_key(($_POST["wp_chatbot_show_pages"]));
					update_option('wp_chatbot_show_pages', $wp_chatbot_show_pages);
				}
                
                if(isset( $_POST["wp_chatbot_show_pages_list"])) {
                    $wp_chatbot_show_pages_list = wp_parse_id_list($_POST["wp_chatbot_show_pages_list"]);
                    update_option('wp_chatbot_show_pages_list', serialize($wp_chatbot_show_pages_list));
                }else{
                    $wp_chatbot_show_pages_list='';
                    update_option('wp_chatbot_show_pages_list', serialize($wp_chatbot_show_pages_list));
                }
                if(isset( $_POST["wp_chatbot_exclude_post_list"])) {
                    $wp_chatbot_exclude_post_list = sanitize_text_field($_POST["wp_chatbot_exclude_post_list"]);
                }else{ $wp_chatbot_exclude_post_list='';}
                update_option('wp_chatbot_exclude_post_list', serialize($wp_chatbot_exclude_post_list));

				if(isset($_POST["wp_chatbot_show_wpcommerce"])){
					$wp_chatbot_show_wpcommerce = sanitize_key(($_POST["wp_chatbot_show_wpcommerce"]));
					update_option('wp_chatbot_show_wpcommerce', $wp_chatbot_show_wpcommerce);
				}
                //Product per page settings.
                if (isset($_POST["qlcd_eddbot_ppp"])) {
                    $qlcd_eddbot_ppp = intval(sanitize_text_field($_POST["qlcd_eddbot_ppp"]));
                    update_option('qlcd_eddbot_ppp', intval($qlcd_eddbot_ppp));
                }
                //eddBot icon settings.
                
                    $eddbot_icon = $_POST['eddbot_icon'] ? sanitize_text_field($_POST['eddbot_icon']) : 'icon-1.png';
                    update_option('eddbot_icon', sanitize_text_field($eddbot_icon));
                // upload custom eddBot icon

                if ($_FILES['custom_icon']['tmp_name'] != "") {

                    $pic = 'custom.png';
                    $img_path = qcld_eddCHATBOT_IMG_ABSOLUTE_PATH . '/' . $pic;

                    $pic_loc = esc_url_raw($_FILES['custom_icon']['tmp_name']);


                    if (move_uploaded_file($pic_loc, $img_path)) {
                        update_option('eddbot_icon', $pic);
                        ?>
                        <script> alert('successfully uploaded');</script><?php
                    } else {
                        ?>
                        <script> alert('error while uploading file');</script><?php
                    }


                }
                if ($_FILES['custom_icon']['tmp_name'] != "") {

                    $pic = 'custom.png';
                    $img_path = qcld_eddCHATBOT_IMG_ABSOLUTE_PATH . '/' . $pic;

                    $pic_loc = esc_url_raw($_FILES['custom_icon']['tmp_name']);


                    if (move_uploaded_file($pic_loc, $img_path)) {
                        update_option('eddbot_icon', $pic);
                        ?>
                        <script> alert('successfully uploaded');</script><?php
                    } else {
                        ?>
                        <script> alert('error while uploading file');</script><?php
                    }


                }
                if (isset($_POST["wp_chatbot_agent_image"])) {
                    $wp_chatbot_agent_image = sanitize_text_field($_POST["wp_chatbot_agent_image"]);
                    $wp_chatbot_custom_agent_path = sanitize_text_field($_POST["wp_chatbot_custom_agent_path"]);
                    update_option('wp_chatbot_agent_image', $wp_chatbot_agent_image);
                    update_option('wp_chatbot_custom_agent_path', $wp_chatbot_custom_agent_path);
                }
                //To override style use custom css.
                $eddbot_custom_css = wp_unslash(sanitize_text_field($_POST["eddbot_custom_css"]));
                update_option('eddbot_custom_css', $eddbot_custom_css);

                /****Language center settings.   ****/
                //identity
				if( isset( $_POST["qlcd_eddbot_host"] ) ){
					$qlcd_eddbot_host = sanitize_text_field($_POST["qlcd_eddbot_host"]);
					update_option('qlcd_eddbot_host', $qlcd_eddbot_host);
				}
                
				if( isset( $_POST["qlcd_eddbot_agent"] ) ){
					$qlcd_eddbot_agent = sanitize_text_field($_POST["qlcd_eddbot_agent"]);
					update_option('qlcd_eddbot_agent', $qlcd_eddbot_agent);
				}
				
				if( isset( $_POST["qlcd_eddbot_agent_join"] ) ){
					$qlcd_eddbot_agent_join = sanitize_text_field($_POST["qlcd_eddbot_agent_join"]);
					update_option('qlcd_eddbot_agent_join', $qlcd_eddbot_agent_join);
				}
                

              //Greeting.
                $qlcd_eddbot_welcome = sanitize_text_field($_POST["qlcd_eddbot_welcome"]);
                update_option('qlcd_eddbot_welcome', $qlcd_eddbot_welcome);

                $qlcd_eddbot_asking_name = sanitize_text_field($_POST["qlcd_eddbot_asking_name"]);
                update_option('qlcd_eddbot_asking_name', $qlcd_eddbot_asking_name);

				if( isset( $_POST["qlcd_eddbot_name_greeting"] ) ){
					$qlcd_eddbot_name_greeting = sanitize_text_field($_POST["qlcd_eddbot_name_greeting"]);
					update_option('qlcd_eddbot_name_greeting', $qlcd_eddbot_name_greeting);
				}
                

				if( isset( $_POST["qlcd_eddbot_i_am"] ) ){
					$qlcd_eddbot_i_am = sanitize_text_field($_POST["qlcd_eddbot_i_am"]);
					update_option('qlcd_eddbot_i_am', $qlcd_eddbot_i_am);
				}
                

                //Products search .
                if (isset($_POST["qlcd_eddbot_product_success"])) {
                    $qlcd_eddbot_product_success = sanitize_text_field($_POST["qlcd_eddbot_product_success"]);
                    update_option('qlcd_eddbot_product_success', $qlcd_eddbot_product_success);
                }
                if (isset($_POST["qlcd_eddbot_product_fail"])) {
                    $qlcd_eddbot_product_fail = sanitize_text_field($_POST["qlcd_eddbot_product_fail"]);
                    update_option('qlcd_eddbot_product_fail', $qlcd_eddbot_product_fail);
                }
                if (isset($_POST["qlcd_eddbot_product_search"])) {
                    $qlcd_eddbot_product_search = sanitize_text_field($_POST["qlcd_eddbot_product_search"]);
                    update_option('qlcd_eddbot_product_search', $qlcd_eddbot_product_search);
                }
                if (isset($_POST["qlcd_eddbot_catalog"])) {
                    $qlcd_eddbot_catalog = sanitize_text_field($_POST["qlcd_eddbot_catalog"]);
                    update_option('qlcd_eddbot_catalog', $qlcd_eddbot_catalog);
                }
                if (isset($_POST["qlcd_eddbot_send_us_email"])) {
                    $qlcd_eddbot_send_us_email = sanitize_text_field($_POST["qlcd_eddbot_send_us_email"]);
                    update_option('qlcd_eddbot_send_us_email', $qlcd_eddbot_send_us_email);
                }
                if (isset($_POST["qlcd_eddbot_more_specific"])) {
                    $qlcd_eddbot_more_specific = sanitize_text_field($_POST["qlcd_eddbot_more_specific"]);
                    update_option('qlcd_eddbot_more_specific', $qlcd_eddbot_more_specific);
                }
                $qlcd_eddbot_product_asking = sanitize_text_field($_POST["qlcd_eddbot_product_asking"]);
                update_option('qlcd_eddbot_product_asking', $qlcd_eddbot_product_asking);

                $qlcd_eddbot_product_suggest = sanitize_text_field($_POST["qlcd_eddbot_product_suggest"]);
                update_option('qlcd_eddbot_product_suggest', $qlcd_eddbot_product_suggest);

                $qlcd_eddbot_product_infinite = str_replace('\\', '', sanitize_text_field($_POST["qlcd_eddbot_product_infinite"])); 
                update_option('qlcd_eddbot_product_infinite', $qlcd_eddbot_product_infinite);

				$qlcd_eddbot_email_successfully = str_replace('\\', '', sanitize_text_field($_POST["qlcd_eddbot_email_successfully"])); 
                update_option('qlcd_eddbot_email_successfully', $qlcd_eddbot_email_successfully);

                $qlcd_eddbot_provide_email_address = str_replace('\\', '', sanitize_text_field($_POST["qlcd_eddbot_provide_email_address"])); 
                update_option('qlcd_eddbot_provide_email_address', $qlcd_eddbot_provide_email_address);

                $qlcd_eddbot_write_your_message = str_replace('\\', '', sanitize_text_field($_POST["qlcd_eddbot_write_your_message"])); 
                update_option('qlcd_eddbot_write_your_message', $qlcd_eddbot_write_your_message);

				$qlcd_eddbot_conversations_with = sanitize_text_field($_POST["qlcd_eddbot_conversations_with"]);
                update_option('qlcd_eddbot_conversations_with', $qlcd_eddbot_conversations_with);
				
				
				$qlcd_eddbot_is_typing = sanitize_text_field($_POST["qlcd_eddbot_is_typing"]);
                update_option('qlcd_eddbot_is_typing', $qlcd_eddbot_is_typing);
				
				$qlcd_eddbot_send_a_msg = sanitize_text_field($_POST["qlcd_eddbot_send_a_msg"]);
                update_option('qlcd_eddbot_send_a_msg', $qlcd_eddbot_send_a_msg);


                //Theme custom background option
                if(isset( $_POST["qcld_eddbot_change_bg"])) {
                    $qcld_eddbot_change_bg = sanitize_text_field($_POST["qcld_eddbot_change_bg"]);
                }else{$qcld_eddbot_change_bg='';}
                update_option('qcld_eddbot_change_bg', wp_unslash($qcld_eddbot_change_bg));
                
                $qcld_eddbot_board_bg_path = esc_url_raw($_POST["qcld_eddbot_board_bg_path"]);

                update_option('qcld_eddbot_board_bg_path', wp_unslash($qcld_eddbot_board_bg_path));

            }
        }
    }
    /**
     * Display Notifications on specific criteria.
     *
     * @since    2.14
     */
    public static function woocommerce_inactive_notice_for_eddbot()
    {
        if (current_user_can('activate_plugins')) :
            if (!class_exists('WooCommerce')) :
                deactivate_plugins(plugin_basename(__FILE__));
                ?>
                <div id="message" class="error">
                    <p>
                        <?php
                        printf(
                            __('%s eddBot for WooCommerce REQUIRES WooCommerce%s %sWooCommerce%s must be active for eddBot to work. Please install & activate WooCommerce.', 'eddbot'),
                            '<strong>',
                            '</strong><br>',
                            '<a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank" >',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
                <?php
            elseif (version_compare(get_option('woocommerce_db_version'), qcld_eddCHATBOT_REQUIRED_WOOCOMMERCE_VERSION, '<')) :
                ?>
                <div id="message" class="error">

                    <p>
                        <?php
                        printf(
                            __('%eddBot for WooCommerce is inactive%s This version of eddBot requires WooCommerce %s or newer. For more information about our WooCommerce version support %sclick here%s.', 'eddbot'),
                            '<strong>',
                            '</strong><br>',
                            qcld_eddCHATBOT_REQUIRED_WOOCOMMERCE_VERSION
                        );
                        ?>
                    </p>
                    <div style="clear:both;"></div>
                </div>
                <?php
            endif;
        endif;
    }



}

/**
 * Instantiate plugin.
 *
 */

if (!function_exists('qcld_edd_chatboot_plugin_init')) {
    function qcld_edd_chatboot_plugin_init()
    {

        global $qcld_eddbot;

        $qcld_eddbot = qcld_eddbot::qcld_eddbot_get_instance();
    }
}
add_action('plugins_loaded', 'qcld_edd_chatboot_plugin_init');

/*
* Initial Options will be insert as defualt data
*/
register_activation_hook(__FILE__, 'qcld_edd_chatboot_defualt_options');
function qcld_edd_chatboot_defualt_options(){
    if(!get_option('eddbot_position_x')){
        update_option('eddbot_position_x', intval(50));
    }
    if(!get_option('eddbot_position_y')) {
        update_option('eddbot_position_y', intval(50));
    }
    if(!get_option('qlcd_eddbot_ppp')){
        update_option('qlcd_eddbot_ppp', intval(10));
    }
    if(!get_option('disable_eddbot')){
        update_option('disable_eddbot', '');
    }
    if(!get_option('qlcd_wp_chatbot_admin_email')){
        update_option('qlcd_wp_chatbot_admin_email', '');
    }
    if(!get_option('qlcd_wp_chatbot_admin_from_email')){
        update_option('qlcd_wp_chatbot_admin_from_email', '');
    }
    
    if(!get_option('qlcd_wp_chatbot_admin_email_name')){
        update_option('qlcd_wp_chatbot_admin_email_name', '');
    }
    if(!get_option('disable_eddbot_on_mobile')) {
        update_option('disable_eddbot_on_mobile', '');
    }
    if(!get_option('eddbot_icon')) {
        update_option('eddbot_icon', sanitize_text_field('icon-0.png'));
    }
    if(!get_option('qlcd_eddbot_host')) {
        update_option('qlcd_eddbot_host', sanitize_text_field('Our Store'));
    }
    if(!get_option('qlcd_eddbot_agent')) {
        update_option('qlcd_eddbot_agent', sanitize_text_field('Carrie'));
    }
    if(!get_option('wp_chatbot_custom_agent_path')) {
       $default_image =  qcld_eddCHATBOT_IMG_URL.'icon-0.png';
        update_option('wp_chatbot_agent_image', sanitize_text_field($default_image));
        update_option('wp_chatbot_custom_agent_path', sanitize_text_field('agent image'));
    }
    if(!get_option('qlcd_eddbot_agent_join')) {
        update_option('qlcd_eddbot_agent_join', sanitize_text_field('has joined the conversation'));
    }
    if(!get_option('qlcd_eddbot_welcome')) {
        update_option('qlcd_eddbot_welcome', sanitize_text_field('Welcome to'));
    }
    if(!get_option('qlcd_eddbot_asking_name')) {
        update_option('qlcd_eddbot_asking_name', sanitize_text_field('May I know your name?!'));
    }
    if(!get_option('qlcd_eddbot_name_greeting')) {
        update_option('qlcd_eddbot_name_greeting', sanitize_text_field('Nice to meet you'));
    }
    if(!get_option('qlcd_eddbot_i_am')) {
        update_option('qlcd_eddbot_i_am', sanitize_text_field('I am!'));
    }
    if(!get_option('qlcd_eddbot_product_success')) {
        update_option('qlcd_eddbot_product_success', sanitize_text_field('Great! We have these products.'));
    }
    if(!get_option('qlcd_eddbot_product_fail')) {
        update_option('qlcd_eddbot_product_fail', sanitize_text_field('Oops! Nothing matches your criteria'));
    }
    
    if(!get_option('qlcd_eddbot_product_search')) {
        update_option('qlcd_eddbot_product_search', sanitize_text_field('Product Search'));
    }
    if(!get_option('qlcd_eddbot_catalog')) {
        update_option('qlcd_eddbot_catalog', sanitize_text_field('Catalog'));
    }
    if(!get_option('qlcd_eddbot_send_us_email')) {
        update_option('qlcd_eddbot_send_us_email', sanitize_text_field('Send Us Email'));
    }
    if(!get_option('qlcd_eddbot_more_specific')) {
        update_option('qlcd_eddbot_more_specific', sanitize_text_field('Can you be more specific?'));
    }
    if(!get_option('qlcd_eddbot_product_asking')) {
        update_option('qlcd_eddbot_product_asking', sanitize_text_field('I am here to find you the product you need. What are you shopping for?'));
    }
    if(!get_option('qlcd_eddbot_product_suggest')) {
        update_option('qlcd_eddbot_product_suggest', sanitize_text_field('You can browse our extensive catalog. Just pick a category from below:'));
    }
    if(!get_option('qlcd_eddbot_product_infinite')) {
        update_option('qlcd_eddbot_product_infinite', sanitize_text_field('Too many choices? Lets try another search term'));
    }
    if(!get_option('qlcd_eddbot_email_successfully')){
        update_option('qlcd_eddbot_email_successfully', sanitize_text_field('Your email has been sent successfully! We will post a reply very soon. Thank you!'));
    }
    if(!get_option('qlcd_eddbot_provide_email_address')){
        update_option('qlcd_eddbot_provide_email_address', sanitize_text_field('Please provide your email address'));
    }
    if(!get_option('qlcd_eddbot_write_your_message')){
        update_option('qlcd_eddbot_write_your_message', sanitize_text_field('Please write you message'));
    }
	if(!get_option('qlcd_eddbot_conversations_with')) {
        update_option('qlcd_eddbot_conversations_with', sanitize_text_field('Conversations with'));
    }
	if(!get_option('qlcd_eddbot_is_typing')) {
        update_option('qlcd_eddbot_is_typing', sanitize_text_field('is typing...'));
    }
	if(!get_option('qlcd_eddbot_send_a_msg')) {
        update_option('qlcd_eddbot_send_a_msg', sanitize_text_field('Send a message'));
    }
	
}

/**
 *
 * Function to load translation files.
 *
 */

function qcld_eddbot_lang_init() {
    load_plugin_textdomain( 'woochatbot', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

add_action( 'plugins_loaded', 'qcld_eddbot_lang_init' );
//Blink


if( is_admin() ){
    require_once("class-plugin-deactivate-feedback.php");
    $wowbot_feedback = new Wp_Usage_Feedback( __FILE__, 'plugins@quantumcloud.com', false, true );
}