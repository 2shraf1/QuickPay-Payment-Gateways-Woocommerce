<?php
/*
Plugin Name: Quickpay
Description:Quickpay Payment Gateway for WooCommerce
Version: 1
Author: Ashraf Eltayeb
Author URI: https://ashraf.cc
Tags: payment, online payment, woocommerce,Quickpay, Sudan payment gateway
Text Domain: woocommerce-extension
Requires at least: 4.0.0
Tested up to: 5.8.0
Requires PHP: 7.1
Stable tag: 5.6.2
WC requires at least: 4.0.0
WC tested up to: 5.5.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

add_filter( 'woocommerce_payment_gateways', 'quickpay_add_gateway_class' );
function quickpay_add_gateway_class( $gateways ) {
    $gateways[] = 'WC_QuickPay_Gateway'; // your class name is here
    return $gateways;
}

add_action( 'plugins_loaded', 'quickpay_init_gateway_class' );

function quickpay_init_gateway_class() {

    class WC_QuickPay_Gateway extends WC_Payment_Gateway
    {
        public function __construct() {

            $this->id = 'quickpay'; // payment gateway plugin ID
            $this->icon = 'https://quickpay.sd/img/quickpay_logo.png'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'QuickPay Gateway';
            $this->method_description = 'بوابة الدفع الالكتروني عبر QuickPay'; // will be displayed on the options page
            $this->supports = array('products');
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->posturl = 'https://quickpay.sd/cpayment/exec';
            $this->url_app = $this->get_option( 'url_app' );
            $this->url_can = $this->get_option( 'url_can' );
            $this->url_dec = $this->get_option( 'url_dec' );
            $this->cln = $this->get_option( 'cln' );
            // This action hook saves the settings

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields(){
            $domain = parse_url(get_site_url(), PHP_URL_HOST);
            $domain_parts = explode('.', $domain);
            $url_can_api = wc_get_checkout_url() . "?cancel";
            $url_app_api = wc_get_checkout_url() . "?success";
            $url_dec_api = wc_get_checkout_url() . "?decline";

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable QuickPay Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'الدفع عن طريق بوابة QuickPay',
                    'default'     => 'َQuickPay',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'الدفع عن طريق بوابة ',
                    'default'     => 'Pay QuickPay payment gateway Sudan.',
                ),
                'cln' => array(
                    'title'       => 'Client ID',
                    'type' 			=> 'text',
                    'description' => 'رقم حسابك - للحصول علية الرجاء التواصل مع  <a href="https://quickpay.sd/" target="_blank">QuickPay</a>	  ',
                    'default'     => 'Client ID',
                    'desc_tip'    => false,
                ),
                'url_can' => array(
                    'title'       => 'Cancel url',
                    'type' 			=> 'select',
                    'options' 		=> $this->quick_get_pages('Select Cancel Page'),
                    'description'       => 'الرجاء اختيار صفحة فشل العملية <span style="background: #e2e2e2;padding: 1.5px;">' . $url_can_api . '</span>',
                    'desc_tip'    => false
                ),
                'url_app' => array(
                    'title'       => 'Success url',
                    'type' 			=> 'select',
                    'options' 		=> $this->quick_get_pages('Select Success Page'),
                    'description'       => 'الرجاء اختيار صفحة تجاح العمليةالرحاء اخيار صفحة  <span style="background: #e2e2e2;padding: 1.5px;">' . $url_app_api . '</span>',
                    'desc_tip'    => false
                ),
                'url_dec' => array(
                    'title'       => 'Decline url',
                    'type' 			=> 'select',
                    'options' 		=> $this->quick_get_pages('Select Decline Page'),
                    'description'       => 'الرجاء اختيار صفحة عدم اكتمال العملية <span style="background: #e2e2e2;padding: 1.5px;">' . $url_dec_api . '</span>',
                    'desc_tip'    => false
                ),


            );
        }

        public function process_payment($order_id){

            global $woocommerce;
            $order = wc_get_order($order_id);

            $url_can = get_permalink($this->url_can). "?oid=".$order_id."&status=cancel";
            $url_app = get_permalink($this->url_app) . '?'.'oid='.$order_id .'&status='.'success';
            $url_dec = get_permalink($this->url_dec) . '?'.'oid='.$order_id .'&status='.'decline';
            $quickpay_api = $this->posturl;
            $cln = $this->get_option('cln');
            $amount = $order->get_total();

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $data = "cln=".$cln."&amount=".$amount."&url_app=".$url_app."&url_can=".$url_can."&url_dec=".$url_dec."&additionalData=".$order_id;
            $url = $quickpay_api.'/corder';
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL,  $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER,  $headers  );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
            $ch_result = curl_exec( $ch );
            $error = curl_error( $ch );
            curl_close( $ch );

            //wc_add_notice( $ch_result );

            $response = json_decode($ch_result, true);
            $session_id = $response['session_id'];
            $serv_order_id =   $response['order_id'];
            $invoice_id = $response['invoice'];


            $data_get = "cln=".$cln."&order_id=".$serv_order_id."&session_id=".$session_id;

            update_post_meta( $order_id, 'session_id', $session_id);
            update_post_meta( $order_id, 'serv_order_id', $serv_order_id);
            update_post_meta( $order_id, 'invoice_id', $invoice_id);


            $url = $quickpay_api.'/getOrderStatus';
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL,  $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_HTTPHEADER,  $headers  );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_get);
            $ch_result = curl_exec( $ch );
            $error = curl_error( $ch );
            curl_close( $ch );


            $redirect_url = plugin_dir_url(__FILE__).'payForm.php?'.'cln='.$cln.'&order_id='.$serv_order_id.'&session_id='.$session_id;
            return array(
                'result' => 'success',
                'redirect' => $redirect_url
            );
        }
        function quick_get_pages($title = false, $indent = true) {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title) $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                // show indented child pages?
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while($has_parent) {
                        $prefix .=  ' - ';
                        $next_page = get_post($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }
    }
}

add_action( 'init', 'woocommerce_process_quickpay_payment' );
function woocommerce_process_quickpay_payment() {
    global $woocommerce;

    //check for paramter passed on the url. This will access the success and cancel payment callback
    if ( isset( $_GET['oid'] ) ) {

        $order_id = $_GET['oid'];


        $sev_order_id = get_post_meta($order_id, "serv_order_id", true);
        $session_id   = get_post_meta($order_id, "session_id", true);

        $options = get_option('woocommerce_quickpay_settings');
        $cln = str_replace('"', '', json_encode($options['cln']));

        $order = wc_get_order($order_id);

        $order_items = $order->get_items();

        foreach( $order_items as $item_id => $item ){

            // methods of WC_Order_Item class

            // The element ID can be obtained from an array key or from:
            $item_id = $item->get_id();

            // methods of WC_Order_Item_Product class

            $item_name = $item->get_name(); // Name of the product
            $item_type = $item->get_type(); // Type of the order item ("line_item")

            $product_id = $item->get_product_id(); // the Product id
            $wc_product = $item->get_product();    // the WC_Product object

            // order item data as an array
            $item_data = $item->get_data();
            $name =  $item_data['name'];
            $product_id =  $item_data['product_id'];
            $variation_id =   $item_data['variation_id'];
            $quantity =   $item_data['quantity'];
            $total =   $item_data['total'];


        }
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $url = 'https://quickpay.sd/cpayment/exec/getOrderStatus';
        $data_get = "cln=".$cln."&order_id=".$sev_order_id."&session_id=".$session_id;

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL,  $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER,  $header  );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_get);
        $ch_result = curl_exec( $ch );
        $error = curl_error( $ch );
        curl_close( $ch );


        $response = json_decode($ch_result, true);
        $sever_status = $response['status'];
        //wc_add_notice( $sever_status);
//completed
        if( $sever_status =='completed'){
            $order->update_status( 'completed' );
            $order->add_order_note('عملية الدفع إكتملت بنجاح');
            $woocommerce -> cart -> empty_cart();
            wc_mail( get_option('admin_email'), "[Success] Quickpay - Order #" . $order_id, "مرحياً!<br />عملية الدفع تمت بنجاح<br /><br />تفاصيل العملية<br />===========<br />" . 'إسم المنتج :' .$name .'<br />'.'ID :' .$product_id . '<br />'.'الكمية :' .$quantity . '<br />'.'مجموع المبلغ :' .$total, $headers = "Content-Type: text/htmlrn", $attachments = "" );
            wc_add_notice( __('Thank you for shopping with us.', 'woothemes') . "order placed successfully", 'success' );
        }else if($sever_status =='failed'){
            $order->add_order_note('The QuicPay transaction has been declined.');
            wc_add_notice( __('Thank you for shopping with us.', 'woothemes') . "عفواً: حدث خطاء العملية لم تكتمل !", 'error' );
        }else {
            wc_add_notice( __('Thank you for shopping with us.', 'woothemes') . "عفواً : تم الغاءالعملية", 'error' );
			$woocommerce -> cart -> empty_cart();
        }
    }
}


