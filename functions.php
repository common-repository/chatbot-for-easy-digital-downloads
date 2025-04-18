<?php

/**
 * @param $type
 * Display recently viewed products
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly


add_action('wp_footer', 'qcld_eddbot_load_footer_html');
if (!function_exists('qcld_eddbot_load_footer_html')) {
    function qcld_eddbot_load_footer_html()
        { ?>
            <?php if (get_option('disable_eddbot') != 1  && qcld_eddbot_load_controlling()==true): ?>
       
            <style>
                <?php  if(get_option('eddbot_custom_css')!=""){echo esc_attr(get_option('eddbot_custom_css')); }  ?>
                    <?php

                     if (get_option('qcld_eddbot_change_bg') == 1) {
                         if (get_option('qcld_eddbot_board_bg_path') != ""){

                            $qcld_eddbot_board_bg_path = esc_attr(get_option('qcld_eddbot_board_bg_path'));

                            ?>

                                .woo-chatbot-ball-inner {
                                    background-image: url(<?php echo ($qcld_eddbot_board_bg_path);?>) !important;
                                    background-size: cover;
                                    background-position: center center;
                                }
        <?php
                         }
                     }
                ?>

</style>      
<div id="woo-chatbot-icon-container">
                <div id="woo-chatbot-ball-wrapper" style="display:block">

                    <div id="woo-chatbot-ball-container" style="display:none" class="woo-chatbot-ball-container">
                        <div class="woo-chatbot-admin">
                            <h4><?php if(get_option('qlcd_eddbot_conversations_with')!=''){echo esc_html_e(get_option('qlcd_eddbot_conversations_with'));}else{  esc_html_e('Conversations with', 'woochatbot'); } ?></h4>
                        <h3> <?php if(get_option('qlcd_eddbot_agent')!=''){echo esc_html_e(get_option('qlcd_eddbot_agent'));} ?></h3>
                        </div>
                        <div class="woo-chatbot-ball-inner">
                            <div class="woo-chatbot-messages-wrapper">
                                <ul id="woo-chatbot-messages-container" class="woo-chatbot-messages-container">
                                </ul>
                            </div>
                        </div>
                        <div id="woo-chatbot-editor-container" class="woo-chatbot-editor-container">
                            <input id="woo-chatbot-editor" class="woo-chatbot-editor" required placeholder="<?php esc_html_e('Send a message.', 'woochatbot'); ?>"
                                maxlength="100">
                            <button type="button" id="woo-chatbot-send-message" class="woo-chatbot-button"><?php esc_html_e('send', 'woochatbot'); ?></button>
                        </div>
                    </div>
                    <!--woo-chatbot-ball-container-->
                    <div id="woo-chatbot-ball" class="woo-chatbot-ball">
                        <img src="<?php echo qcld_eddCHATBOT_IMG_URL . '/' . get_option('eddbot_icon'); ?>"
                            alt="WooChatIcon">
                    </div>
                    <!--container-->
                </div>
                <!--woo-chatbot-ball-wrapper-->
            </div>
        <?php endif;

        }
    }
add_action('wp_ajax_qcld_eddbot_keyword', 'qcld_eddbot_keyword');
add_action('wp_ajax_nopriv_qcld_eddbot_keyword', 'qcld_eddbot_keyword');

if (!function_exists('qcld_eddbot_keyword')) {
    function qcld_eddbot_keyword()
    {
        $keyword = sanitize_text_field($_POST['keyword']);
        $product_per_page=get_option('qlcd_eddbot_ppp')!=''? get_option('qlcd_eddbot_ppp') :10;
        //Merging all query together.
        $argu_params = array(
            'fields'        => 'ids',
            'post_type' => 'download',
            'post_status' => 'publish',
            'posts_per_page' => $product_per_page,
            'order' => 'ASC',
            's' => $keyword,
        );

        /*******WP Query Operation to get products.*
         *******/
        $product_query = new WP_Query($argu_params);
        $downloads = get_posts( $argu_params );
    
        $product_num = $product_query->post_count;
        $html = '<div class="woo-chatbot-featured-products">';

        //repeating the products
        if ($product_num > 0) {
            //$html .= '<p>sdf sdfdsf : '.$asdfdf.'</p>';
            $html .= '<ul class="woo-chatbot-products">';
            
                    foreach ( $downloads as $key => $download_id ) {
                        $download = new EDD_Download( $download_id );
                        $html .= '<li class="woo-chatbot-product">';
                        $html .= '<a target="_blank" href="' .get_the_permalink($download_id) . '" title="' .  esc_attr(get_the_title($download_id)) . '">';
                        $image = get_the_post_thumbnail($download_id);
                        $html .= $image . '</a>
                        <div class="woo-chatbot-product-summary">
                        <div class="woo-chatbot-product-table">
                        <div class="woo-chatbot-product-table-cell">
                        <h3 class="woo-chatbot-product-title"><a target="_blank" href="' . get_the_permalink($download_id) . '" title="' .  esc_attr(get_the_title($download_id)) . '">' .  esc_attr(get_the_title($download_id)) . '</a></h3>
                        <div class="price">' . $download->get_price() . '</div>';
                        $html .= ' </div>
                        </div>
                        </div>
                        </li>';
                    }

            wp_reset_postdata();
            $html .= '</ul>';
        }
        $html .= '</div>';
        $response = array('html' => $html, 'product_num' => $product_num);
        echo wp_send_json($response);
        wp_die();
    }
}
add_action('wp_ajax_qcld_eddbot_category', 'qcld_eddbot_category');
add_action('wp_ajax_nopriv_qcld_eddbot_category', 'qcld_eddbot_category');
if (!function_exists('qcld_eddbot_category')) {
    function qcld_eddbot_category()
    {
        $terms = get_categories(array(
            'taxonomy' => 'download_category',
            'hide_empty' => true,
        ));
        $html = "";
        foreach ($terms as $term) {

            $html .= '<span class="qcld-chatbot-product-category" type="button" data-category-slug="' . $term->slug . '" data-category-id="' . $term->term_id . '">' . $term->name . '</span>';
        }
        echo wp_send_json($html);
        wp_die();
    }
}
add_action('wp_ajax_qcld_eddbot_category_products', 'qcld_eddbot_category_products');
add_action('wp_ajax_nopriv_qcld_eddbot_category_products', 'qcld_eddbot_category_products');
if (!function_exists('qcld_eddbot_category_products')) {
    function qcld_eddbot_category_products()
    {
        $category_id = sanitize_text_field($_POST['category']);
        $product_per_page=get_option('qlcd_eddbot_ppp')!=''? get_option('qlcd_eddbot_ppp') :10;
        //Merging all query together.
        $argu_params = array(
            'post_type' => 'download',
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,
            'posts_per_page' => $product_per_page,
            'tax_query' => array(
                array(
                    'taxonomy' => 'download_category',
                    'field' => 'term_id',
                    'terms' => $category_id,
                    'operator' => 'IN'
                )
            )
        );
        /******
         *WP Query Operation to get products.*
        *******/
        $product_query = new WP_Query($argu_params);
        $product_num = $product_query->post_count;

    // $_pf = new EDD_Download();
        //repeating the products
        $html="";
        if ($product_num > 0) {

            $html .= '<div class="woo-chatbot-featured-products">';
            //$html .= '<p>sdf sdfdsf : '.$asdfdf.'</p>';
            $html .= '<ul class="woo-chatbot-products">';
            while ($product_query->have_posts()) : $product_query->the_post();
            // $product = $_pf->get_the_post(get_the_ID());
                $product = new EDD_Download( get_the_ID() );
                //$qcld_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'shop_thumbnail' );
                $html .= '<li class="woo-chatbot-product">';
                $html .= '<a target="_blank" href="' . get_permalink(get_the_ID()) . '" title="' . esc_attr(get_the_title(get_the_ID()) ? get_the_title(get_the_ID())  : get_the_ID()) . '">';
                $image = get_the_post_thumbnail(get_the_ID(), 'shop_catalog');
                if(empty($image)){
                    $image = woocommerce_placeholder_img( 'shop_catalog' );
                }

                $html .= $image . '</a>
        <div class="woo-chatbot-product-summary">
        <div class="woo-chatbot-product-table">
        <div class="woo-chatbot-product-table-cell">
        <h3 class="woo-chatbot-product-title"><a target="_blank" href="' . get_permalink(get_the_ID()) . '" title="' . esc_attr(get_the_title(get_the_ID()) ? get_the_title(get_the_ID()) : get_the_ID()) . '">' . get_the_title(get_the_ID()). '</a></h3>
        <div class="price">' . $product ->get_price() . '</div>';

    //            if ($product->is_type('simple')) {
    //                $html .= '<a target="_blank" href="' . get_site_url() . '?add-to-cart=' . get_the_ID() . '"  title="' . esc_attr($product->post->post_title ? $product->post->post_title : get_the_ID()) . '"  class="woo-chatbot-button woo-chatbot-button-cart add_to_cart_button ajax_add_to_cart"  data-quantity="1" data-product_id="' . get_the_ID() . '" >Add to Cart</a>';
    //            } else {
    //                $html .= '<a target="_blank" href="' . get_permalink(get_the_ID()) . '"  title="' . esc_attr($product->post->post_title ? $product->post->post_title : get_the_ID()) . '"  class="woo-chatbot-button woo-chatbot-button-cart"  >View Detail</a>';
    //            }
                $html .= ' </div>
        </div>
        </div>
        </li>';
            endwhile;
            wp_reset_postdata();
            $html .= '</ul>';

            $html .= '</div>';
        }else{
            $html.="";
        }
        $response = array('html' => $html, 'product_num' => $product_num);
        echo wp_send_json($response);
        wp_die();

    }
}

//eddbot load control handler.
if (!function_exists('qcld_eddbot_load_controlling')) {
    function qcld_eddbot_load_controlling(){
        $eddbot_load=true;
        if(qcld_eddbot_is_mobile()&& get_option('disable_eddbot_on_mobile')==1){
            $eddbot_load=false;
        }
        if (get_option('wp_chatbot_show_pages') == 'off') {
            $wp_chatbot_select_pages = unserialize(get_option('wp_chatbot_show_pages_list'));
            if (is_page() && !empty($wp_chatbot_select_pages)) {
                
                if (in_array(get_the_ID(), $wp_chatbot_select_pages) == true) {
                    
                    $eddbot_load = true;
                } else {
                    $eddbot_load = false;
                }
                
            }
            
            if(function_exists('is_shop')){
                if (is_shop() || is_cart() || is_checkout() || 'product' == get_post_type()) {
                    $eddbot_load = false;
                }
            }
            
        }
        if (get_option('wp_chatbot_show_wpcommerce') == 'off') {
            
        }
        //load wpwbot shortcode template and prevent default wpwbot from footer.
        if (is_page()) {
            $page_id = get_the_ID();
            $page = get_post($page_id);
            if (has_shortcode($page->post_content, 'wpwbot')) {
                $eddbot_load = false;
            }
        }
        
        $post_list = maybe_unserialize(get_option('wp_chatbot_exclude_post_list'));
        
        if( is_array( $post_list ) && in_array(get_post_type(), $post_list)){
            $eddbot_load = false;
        }
        if (qcld_eddbot_is_mobile() && get_option('disable_wp_chatbot_on_mobile') == 1) {
            $eddbot_load = false;
        }
        
        if (get_option('wp_chatbot_show_home_page') == 'off' && is_home()) {
            $eddbot_load = false;
        }
        
        if (get_option('wp_chatbot_show_posts') == 'off' && 'post' == get_post_type()) {
            $eddbot_load = false;
        }
        
        if(is_admin()){
            $eddbot_load = false;
        }
        return $eddbot_load;
    }
}
//checking Devices
if (!function_exists('qcld_eddbot_is_mobile')) {
    function qcld_eddbot_is_mobile(){
        $useragent= sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            return true;
        }else{
            return false;
        }
    }
}
if (!function_exists('qcld_edd_fnc_get_ip_address')) {
    function qcld_edd_fnc_get_ip_address(){

        if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        {
            $ip_address = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        }
        //whether ip is from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
        {
            $ip_address = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        }
        //whether ip is from remote address
        else
        {
            $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        return $ip_address;

    }
}

add_action('wp_ajax_qcld_eddbot_email', 'qcld_eddbot_email');
add_action('wp_ajax_nopriv_qcld_eddbot_email', 'qcld_eddbot_email');
if (!function_exists('qcld_eddbot_email')) {
    function qcld_eddbot_email(){
        $name = trim(sanitize_text_field($_POST['name']));
        $email = sanitize_email($_POST['email']);
        $message = sanitize_text_field($_POST['message']);
        $page = sanitize_text_field($_POST['page']);
        $subject = 'Support Request from EDDBot';

        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
        $ip_address = qcld_edd_fnc_get_ip_address();

        //Extract Domain
        $url = get_site_url();
        $url = parse_url($url);
        $domain = $url['host'];
        
        $admin_email = get_option('admin_email');
        $toEmail = get_option('qlcd_wp_chatbot_admin_email') != '' ? get_option('qlcd_wp_chatbot_admin_email') : $admin_email;
        $fromEmail = "wordpress@" . $domain;

        if(get_option('qlcd_wp_chatbot_admin_from_email') && get_option('qlcd_wp_chatbot_admin_from_email')!=''){
            $fromEmail = get_option('qlcd_wp_chatbot_admin_from_email');
        }

        $replyto = $fromEmail;

        //Starting messaging and status.
        $response['status'] = 'fail';
        $response['message'] = 'Failed. Unable to send email.';
        //build email body
        $bodyContent = "";
        $bodyContent .= '<p><strong>' . esc_html('Support Request Details', 'eddchatbot') . ':</strong></p><hr>';
        $bodyContent .= '<p>' . esc_html('Name', 'eddchatbot') . ' : ' . esc_html($name) . '</p>';
        $bodyContent .= '<p>' . esc_html('Email', 'eddchatbot') . ' : ' . esc_html($email) . '</p>';
        $bodyContent .= '<p>' . esc_html('Subject', 'eddchatbot') . ' : ' . esc_html($subject) . '</p>';
        $bodyContent .= '<p>' . esc_html('Message', 'eddchatbot') . ' : ' . esc_html($message) . '</p>';
        $bodyContent .= '<p>' . esc_html('Page', 'eddchatbot') . ' : ' . ($page) . '</p>';
        $bodyContent .= '<p>' . esc_html('User Agent', 'eddchatbot') . ' : ' . ($user_agent) . '</p>';
        $bodyContent .= '<p>' . esc_html('IP Address', 'eddchatbot') . ' : ' . ($ip_address) . '</p>';
        $bodyContent .= '<p>' . esc_html('Mail Generated on', 'eddchatbot') . ': ' . current_time('F j, Y, g:i a') . '</p>';
        $to = $toEmail;
        $body = $bodyContent;
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . esc_html($name) . ' <' . esc_html($fromEmail) . '>';
        $headers[] = 'Reply-To: ' . esc_html($name) . ' <' . esc_html($email) . '>';
        $result = wp_mail($to, $subject, $body, $headers);
        $support_email_to_crm_contact = get_option('wpbot_support_mail_to_crm_contact');
        if($support_email_to_crm_contact){
            do_action( 'qcld_mailing_list_subscription_success', $name, $email );
        }
        if ($result) {
            $response['status'] = 'success';
            $response['message'] = 'Email has been sent successfully!';
        }
        
        
        echo json_encode($response);
        die();
    }
}




