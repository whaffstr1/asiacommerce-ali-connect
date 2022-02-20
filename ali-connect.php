<?php

/**

 * @package Ali_Connect

 * @version 2.2

 */



/*

Plugin Name: AsiaCommerce - Ali Connect

Description: AsiaCommerce Logistic Integration Plugin for WooCommerce.

Version: 2.2

Author: AsiaCommerce Networks Limited

Author URI: https://asiacommerce.net/

*/



require_once 'checker.php';

require_once 'fibu-helper.php';

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

require_once ABSPATH . 'wp-admin/includes/plugin.php';



class Ali_Connect_Wordpress

{

    protected $checker;

    protected $fibu;

    protected static $version = '2.1';



    public function __construct()

    {

        $this->checker = new ALI_Connect_Checker();

        $this->fibu = new AliFibuHelper();

    }



    public static function install()

    {

        add_option('ali_connect_version', static::$version);

    }



    public function return_personalised_catalog_data_to_api($product, $post, $request)

    {

        $product_id = $product->data['id'];

        $customer_id = $request['customer_id'];



        // Get customer-specific price from database table



        return $product;

    }



    public function init()

    {

        if (is_plugin_active('woocommerce/woocommerce.php')) {

            add_filter( 'woocommerce_get_price_html', array( $this, 'wc_uom_render_output' ), 10, 2 );

            add_filter( 'woocommerce_add_to_cart_validation', array($this,'custom_empty_cart'), 10,3);

            add_action('wp_enqueue_scripts', 'enqueue_load_fa');

            add_action('woocommerce_before_cart_contents', array($this, 'showProductDeliveryTimeCart'), 10);

            add_action('woocommerce_checkout_before_order_review', array($this, 'showProductDeliveryTimeCart'), 10);

            add_action('woocommerce_thankyou', array($this, 'showProductDeliveryTimeCart'), 10);

            add_action('woocommerce_after_add_to_cart_form', array($this, 'addProductDeliveryTime'));

            add_action('woocommerce_webhook_delivery', array($this, 'handleWebhookFromALI'), 10, 5);

            add_action('woocommerce_rest_insert_product_object', array($this, 'decorateProductResponse'), 10, 3);

            $this->checker->init();

            add_action('woocommerce_check_cart_items', array($this,'restrictProduct'), 10);
            add_filter( 'woocommerce_cart_item_name', array($this,'identifyProduct'), 10, 3);
            add_action( 'woocommerce_proceed_to_checkout', array($this,'disable_checkout_button_no_shipping'), 10);
            
            //add_action('rest_api_init',array($this,'register_routes_api'),10);
             add_action('rest_api_init', array($this,'deleteImagesApi'), 10);
            
        } else {

            add_action('admin_notices', array($this, 'wooCommerceNotActivated'));

        }

    }

    public function wc_uom_render_output( $price ) {
    global $post;
    // Check if uom text exists.
    $woo_uom_output = get_post_meta( $post->ID, '_uom', true );
    $woo_uom_output2 = strtoupper(get_post_meta( $post->ID, '_uom_base', true ));
    $woo_uom_output3 = get_post_meta( $post->ID, '_uom_base_qty', true );
    
    if( !empty($woo_uom_output) && strtolower($woo_uom_output)!=strtolower($woo_uom_output2)){
      $price = $price . ' <span class="uom">' . ' / ' .esc_attr( strtoupper($woo_uom_output), 'woocommerce-uom' ) . "(@$woo_uom_output3 $woo_uom_output2)" . '</span>';
      return $price;
    }
    else if( !empty($woo_uom_output) && strtolower($woo_uom_output)==strtolower($woo_uom_output2)){
      $price = $price . ' <span class="uom">' . ' / ' .esc_attr( strtoupper($woo_uom_output), 'woocommerce-uom' )  . '</span>';
      return $price;
    }
    else{
      return $price;
    }
    // Check if variable OR UOM text exists.
    
    // $base_uom = array('pcs','sheets','box');

    // if( !in_array(strtolower($woo_uom_output), $base_uom) && !empty($woo_uom_output)){}
    // if ( (strtolower($woo_uom_output)!='pcs' && strtolower($woo_uom_output)!='sheets' && strtolower($woo_uom_output)!='box') && !empty($woo_uom_output))
    // {
    //   $price = $price . ' <span class="uom">' . ' / ' .esc_attr( strtoupper($woo_uom_output), 'woocommerce-uom' ) . "(@$woo_uom_output3 $woo_uom_output2)" . '</span>';
    //   return $price;
    // }
    // else if(!empty($woo_uom_output) || $woo_uom_output!=''){
    //   $price = $price . ' <span class="uom">' . ' / ' .esc_attr( strtoupper($woo_uom_output), 'woocommerce-uom' )  . '</span>';
    //   return $price;
    // }
    // else
    //   return $price;
  }

    public function custom_empty_cart( $passed, $product_id, $quantity ) {

      // if(get_current_user_id()==1)
      // var_dump($_POST)
      $data_curr_prod = $_POST;
      $variation_idi = '';
      // if(get_current_blog_id()==2421){
      //   echo json_encode($_POST,true)."<br>";
      //   echo $passed."<br>";
      //   echo $product_id."<br>";
      //   echo $quantity."<br>";
        if(!empty($data_curr_prod['variation_id'])){
          $variation_idi = $data_curr_prod['variation_id'];
          $perma_link = get_permalink($variation_idi);
        }
        else{
          $perma_link = get_permalink($product_id);
        }
        // echo get_permalink($product_id)."<br>";
        // echo get_permalink($variation_idi)."<br>";
        // $variation = new WC_Product_Variation($variation_idi);
        // $nama_prod = $variation->get_name();
        // $variationName = implode(" / ", $variation->get_variation_attributes());

        // echo $nama_prod." - ".$variationName;

        
      // }

      

      
  
      $product_id_current = ''; 
      foreach( WC()->cart->get_cart() as $cart_item ){
        
        $product_id_current = $cart_item['product_id'];

      }

      if($product_id_current!=''){

        $ali_prod = get_post_meta($product_id_current,'ali__is_ali_product',true);
        $ali_prod2 = get_post_meta($product_id,'ali__is_ali_product',true);

        $kota_code = wc_get_base_location();
        $kota_code = $kota_code['country'];
        $kota_toko = WC()->countries->countries["$kota_code"];

        if($ali_prod!=$ali_prod2){         
          // wc_add_notice( __( 'Barang ini adalah barang dropship / ready stok dan berbeda dengan barang di cart. Klik Kosongkan cart untuk mengganti barang yang ada di cart dengan barang ini.', 'woocommerce' ), 'error' );
          
          if(metadata_exists( 'post', $product_id_current, 'country' )){
              $country_prod1 = get_post_meta($product_id_current,'country',true);
          }else{
              $kota_code = wc_get_base_location();
              $kota_code = $kota_code['country'];
              $kota_toko = WC()->countries->countries["$kota_code"];
              $country_prod1 = $kota_toko;
          }
          
          if(metadata_exists( 'post', $product_id, 'country' )){
              $country_prod2 = get_post_meta($product_id,'country',true);
          }else{
              $kota_code = wc_get_base_location();
              $kota_code = $kota_code['country'];
              $kota_toko = WC()->countries->countries["$kota_code"];
              $country_prod2 = $kota_toko;
          }
          
          if($country_prod1!=$country_prod2){
              wc_add_notice( __( "Barang ini berasal dari negara yang berbeda dengan barang di cart. Klik (Ya, Ganti Cart Saya) untuk mengganti barang yang ada di cart dengan barang ini.", 'woocommerce' ), 'error' );

              wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

              wp_localize_script( 'empty-cart-ali', 'myAjax', array( 
                'ajaxurl'     => admin_url( 'admin-ajax.php'),
                'vari_id'     => $variation_idi,
                'qty'         => $quantity,
                'prod_id'     => $product_id,
                'perma_link'  => $perma_link
              ));

              wp_enqueue_script( 'jquery' );
              wp_enqueue_script( 'empty-cart-ali' );

              return false;              
          }else{
              return $passed;
          }
          
        //   wc_add_notice( __( 'Barang ini dikirim dari gudang yang berbeda dengan barang di cart. Klik Kosongkan cart untuk mengganti barang yang ada di cart dengan barang ini.', 'woocommerce' ), 'error' );
         
        //   wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

        //   wp_localize_script( 'empty-cart-ali', 'myAjax', array( 
        //     'ajaxurl'    => admin_url( 'admin-ajax.php'),
        //     'vari_id'    => $variation_idi,
        //     'qty'        => $quantity,
        //     'prod_id'    => $product_id,
        //     'perma_link' => $perma_link
        //   ));

        //   wp_enqueue_script( 'jquery' );
        //   wp_enqueue_script( 'empty-cart-ali' );

        //   return false;
          // if($ali_prod==0||$ali_prod==''&&$ali_prod2==1){
          //   $country_prod2 = get_post_meta($product_id,'country',true);
          //   if($kota_toko==$country_prod2){
          //     return $passed;
          //   }
          //   else{
          //     wc_add_notice( __( 'Barang ini adalah barang dropship / ready stok dan berbeda dengan barang di cart. Harap kosongkan cart terlebih dahulu, lalu add barang ini ke cart.', 'woocommerce' ), 'error' );
          //     return false;
          //   }
          // }
          // else if($ali_prod2==0||$ali_prod2==''&&$ali_prod==1){
          //   $country_prod = get_post_meta($product_id_current,'country',true);

          //   if($kota_toko==$country_prod){
          //     return $passed;
          //   }
          //   else{
          //     wc_add_notice( __( 'Barang ini adalah barang dropship / ready stok dan berbeda dengan barang di cart. Harap kosongkan cart terlebih dahulu, lalu add barang ini ke cart.', 'woocommerce' ), 'error' );
          //     return false;
          //   }
          // }         
        }
        else{
          if($ali_prod==1){
            $country_prod = get_post_meta($product_id_current,'country',true);
            $country_prod2 = get_post_meta($product_id,'country',true);
            if($country_prod!=$country_prod2){
              wc_add_notice( __( "Barang ini berasal dari negara $country_prod2 dan berbeda dengan barang di cart. Klik (Ya, Ganti Cart Saya) untuk mengganti barang yang ada di cart dengan barang ini.", 'woocommerce' ), 'error' );

              wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

              wp_localize_script( 'empty-cart-ali', 'myAjax', array( 
                'ajaxurl'     => admin_url( 'admin-ajax.php'),
                'vari_id'     => $variation_idi,
                'qty'         => $quantity,
                'prod_id'     => $product_id,
                'perma_link'  => $perma_link
              ));

              wp_enqueue_script( 'jquery' );
              wp_enqueue_script( 'empty-cart-ali' );

              return false;              
            }else{
              if($country_prod==$kota_toko){
                $city_prod = get_post_meta($product_id_current,'origin_city',true);
                if(!$city_prod){
            		$city_prod = get_post_meta($product_id_current,'city',true);
            	}
                $city_prod2 = get_post_meta($product_id,'origin_city',true);
                if(!$city_prod2){
            		$city_prod2 = get_post_meta($product_id,'city',true);
            	}

                $supplier_prod = get_post_meta($product_id_current,'supplier_name',true);
                if(!$supplier_prod){
                	$supplier_prod = get_post_meta($product_id_current,'warehouse_name',true);
                }
                $supplier_prod2 = get_post_meta($product_id,'supplier_name',true);
                if(!$supplier_prod2){
                	$supplier_prod2 = get_post_meta($product_id,'warehouse_name',true);
                }

                if(($supplier_prod!=$supplier_prod2) || ($city_prod!=$city_prod2)){
            //       wc_add_notice( __( "Barang ini berasal dari Gudang $supplier_prod2, $city_prod2 dan berbeda dengan barang di cart. Klik Kosongkan cart untuk mengganti barang yang ada di cart dengan barang ini.", 'woocommerce' ), 'error' );

            //       wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

	           //   wp_localize_script( 'empty-cart-ali', 'myAjax', array( 
	           //     'ajaxurl'     => admin_url( 'admin-ajax.php'),
	           //     'vari_id'     => $variation_idi,
	           //     'qty'         => $quantity,
	           //     'prod_id'     => $product_id,
	           //     'perma_link'  => $perma_link
	           //   ));

	           //   wp_enqueue_script( 'jquery' );
	           //   wp_enqueue_script( 'empty-cart-ali' );
                  return $passed;  
                }
                else{
                  return $passed;
                }
              }
              else{
                return $passed;
              }
            }
          }
          else{
            //add to cart tidak ada masalah karena sama2 ready stock
          }
        }

      }

      return $passed;
    }
    
    public function deleteImagesApi(){
        register_rest_route( 'api/v1', '/deleteimage', array(
            'methods' => 'POST',
            'callback' => array($this, 'delete_wc_images')
        ));
    }

    public function delete_wc_images($req) {
            
        $response['id'] = $req['id'];
        $response['attach_id'] = $req['attach_id'];
        $json = $response['attach_id'];
        $post_id = $response['id'];
        
        global $wpdb;
        if ($post_id) {
            
            $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = $post_id AND post_type = 'attachment'");
            foreach ( $ids as $id ) {
                wp_delete_attachment($id, true);
            }
            
            $data = json_decode($json, true);
            
            foreach ($data as $entry) {
                $ids = $entry['id'];
                if(wp_delete_attachment($ids, true)){
                    $response['info'] = 'success delete images';
                }else{
                    $response['info'] = 'failed delete images';
                }
                
            }
            
        }else{
            $response['info'] = 'No id selected';
        }
        
        
        $res = new WP_REST_Response($response);
        $res->set_status(200);
        
        
        return $res;
        
    }


    public function restrictProduct(){
      global $woocommerce;
      $cart_contents    =  $woocommerce->cart->get_cart( );
      $cart_item_keys   =  array_keys ( $cart_contents );
      $cart_item_count  =  count ( $cart_item_keys );
  
      if ( ! $cart_contents || $cart_item_count == 1 ) {
            return null;
      }

      $first_item    =  $cart_item_keys[0];
      $first_item_id =  $cart_contents[$first_item]['product_id'];
        
      // if((get_post_meta($first_item_id,'ali__is_ali_product',true)=='0') || (get_post_meta($first_item_id,'ali__is_ali_product',true)=='')){  
      //   $first_item_type = 0;
      // }else if(get_post_meta($first_item_id,'ali__is_ali_product',true)=='1'){
      //   $first_item_type = 1;
      // }  

      // if(get_current_blog_id()==2421)
      // {
      //   echo get_post_meta($first_item_id,'ali__is_ali_product',true);
      // }
      $kota_code = WC_Countries::get_base_country();
      $kota_toko = WC()->countries->countries["$kota_code"];

      if((get_post_meta($first_item_id,'ali__is_ali_product',true)=='0') || (get_post_meta($first_item_id,'ali__is_ali_product',true)=='')){  
        $first_item_origin = $kota_toko;
      }else if(get_post_meta($first_item_id,'ali__is_ali_product',true)=='1'){
        if(metadata_exists('post',$first_item_id,'country')){
          $country_origin = get_post_meta($first_item_id,'country',true);
          $first_item_origin = $country_origin;
        }   
      }

      // if(metadata_exists('post',$first_item_id,'country')){
      //   $country_origin = get_post_meta($first_item_id,'country',true);
      //   if(strtolower($country_origin) == 'china'){
      //     $first_item_origin = 1;
      //   }else if(strtolower($country_origin) == 'thailand'){
      //     $first_item_origin = 2;
      //   }else if(strtolower($country_origin) == 'indonesia'){
      //     $first_item_origin = 0;
      //   }else{
      //     $first_item_origin = 3;
      //   }
      // }else{
      //   $first_item_origin = 0;
      // }
    //$first_item_type = get_product_validation($first_item_id);
    //$first_item_origin = get_product_origin($first_item_id);
    
    foreach ( $cart_item_keys as $key ) {
        if ( $key  ==  $first_item ) {
            continue;
        }else {
          $product_id =  $cart_contents[$key]['product_id'];

          if((get_post_meta($product_id,'ali__is_ali_product',true)=='0') || (get_post_meta($product_id,'ali__is_ali_product',true)=='')){  
            $other_item_origin = $kota_toko;
          }else if(get_post_meta($product_id,'ali__is_ali_product',true)=='1'){
            if(metadata_exists('post',$product_id,'country')){
              $country_origin = get_post_meta($product_id,'country',true);
              $other_item_origin = $country_origin;
            }
          }

          if($other_item_origin != $first_item_origin){
              $mismatched_type = 1;
          }

        }
    }

    if (isset($mismatched_type)) {
        wc_add_notice( sprintf( '<strong>You need to purchase products from the Same Source Country. Please delete one of the products in the cart</strong>'), 'error' );
    }  

    }


    public function identifyProduct( $item_name,  $cart_item,  $cart_item_key ) {
    // Display name and product id here instead
      $product = wc_get_product($cart_item['product_id']);
      $product_name = $product->get_name();
      $is_product = get_post_meta($cart_item['product_id'],'ali__is_ali_product',true);
      $url = get_permalink($cart_item['product_id']) ;

      // if(get_current_blog_id()==2421)
      // {
      //   // $stock = get_post_meta( $cart_item['product_id'], '_stock', true );
      //   var_dump($is_product);
      //   echo"<br>";
      // }

      $infoname = get_bloginfo( 'name' );

      $kota_code = WC_Countries::get_base_country();
      $kota_toko = WC()->countries->countries["$kota_code"];
      $stock = get_post_meta( $cart_item['product_id'], '_stock', true );

      // $kota_gudang_woongkir = get_option('origin_city_gudang');
      $kota_gudang_woongkir = WC_Countries::get_base_city();

      if($is_product == '0' || $is_product == ''){

        $display =  "<a href='".$url."'>".$product_name."</a></br><font color='black'><strong>Pengiriman dari Gudang ".$infoname.", ".$kota_gudang_woongkir.", ".$kota_toko."</strong></font>";

      }else if($is_product == '1'){        
       
        if(metadata_exists('post',$cart_item['product_id'],'country')){
          $country_origin = get_post_meta($cart_item['product_id'],'country',true);
          $country_lowercase = strtolower($country_origin);
          if($country_lowercase == strtolower($kota_toko)){
            $supplier_name = get_post_meta($cart_item['product_id'],'supplier_name',true);
            if(metadata_exists( 'post', $cart_item['product_id'], 'warehouse_name' )){
            	$supplier_name = get_post_meta($cart_item['product_id'],'warehouse_name',true);            	 
            }
            if(!empty($supplier_name) || $supplier_name!=''){
              $kota_produk = get_post_meta($cart_item['product_id'],'origin_city',true);
              if(!$kota_produk){
              	$kota_produk = get_post_meta($cart_item['product_id'],'city',true);
              }
              $display =  "<a href='".$url."'>".$product_name."</a></br><font color='black'><strong>Pengiriman dari Gudang ".$supplier_name.", ".$kota_produk.", ".$country_origin."</strong></font>";
            }
            else
            {
              $display =  "<a href='".$url."'>".$product_name."</a></br><font color='black'><strong>Pengiriman dari Gudang ".$country_origin."</strong></font>";
            }
          }else{
            $display =  "<a href='".$url."'>".$product_name."</a></br><font color='black'><strong>Pengiriman dari Gudang ".$country_origin."</strong></font>";
          }
          
        }else{
          $display =  "<a href='".$url."'>".$product_name."</a></br><font color='black'><strong>Pengiriman dari Gudang No Country</strong></font>";
        }

      }

      return $display;
    }



    public function disable_checkout_button_no_shipping() { 
      // global $woocommerce;
      // $cart_contents    =  $woocommerce->cart->get_cart( );
      // $cart_item_keys   =  array_keys ( $cart_contents );
      // $cart_item_count  =  count ( $cart_item_keys );
  
      // if ( ! $cart_contents || $cart_item_count == 1 ) {
      //       return null;
      //   }

      //   $first_item    =  $cart_item_keys[0];
      //   $first_item_id =  $cart_contents[$first_item]['product_id'];
        
      //   if(get_post_meta($first_item_id,'ali__is_ali_product',true)=='1'){  
      //     $first_item_type = 1;
      //   }else{
      //     $first_item_type = 0;
      //   }

      //   if(metadata_exists('post',$first_item_id,'country')){
      //       $country_origin = get_post_meta($first_item_id,'country',true);
      //       if(strtolower($country_origin) == 'china'){
      //         $first_item_origin = 1;
      //       }else if(strtolower($country_origin) == 'thailand'){
      //         $first_item_origin = 2;
      //       }else if(strtolower($country_origin) == 'indonesia'){
      //         $first_item_origin = 0;
      //       }else{
      //         $first_item_origin = 3;
      //       }
      //   }else{
      //       $first_item_origin = 0;
      //   }
      //   //$first_item_type = get_product_validation($first_item_id);
      //   //$first_item_origin = get_product_origin($first_item_id);
        
      //   foreach ( $cart_item_keys as $key ) {
      //       if ( $key  ==  $first_item ) {
      //           continue;
      //       }else {
      //         $product_id =  $cart_contents[$key]['product_id'];
      //         //$product_validation  =  get_product_validation($product_id);

      //         if(get_post_meta($product_id,'ali__is_ali_product',true)=='1'){  
      //           $product_validation = 1;
      //         }else{
      //           $product_validation = 0;
      //         }

      //         //$product_origin = get_product_origin($product_id);

      //         if(metadata_exists('post',$product_id,'country')){
      //             $country_originn = get_post_meta($product_id,'country',true);
      //             if(strtolower($country_originn) == 'china'){
      //               $product_origin = 1;
      //             }else if(strtolower($country_originn) == 'thailand'){
      //               $product_origin = 2;
      //             }else if(strtolower($country_originn) == 'indonesia'){
      //               $product_origin = 0;
      //             }else{
      //               $product_origin = 3;
      //             }
      //         }else{
      //             $product_origin = 0;
      //         }

      //         if ( $product_validation  !=  $first_item_type ) {
      //               //$woocommerce->cart->set_quantity ( $key, 0, true );
      //               $mismatched_type  =  1;
      //         }

      //         if($product_origin != $first_item_origin){
      //             $mismatched_type = 1;
      //         }

      //       }
      //   }
        global $woocommerce;
      $cart_contents    =  $woocommerce->cart->get_cart( );
      $cart_item_keys   =  array_keys ( $cart_contents );
      $cart_item_count  =  count ( $cart_item_keys );
  
      if ( ! $cart_contents || $cart_item_count == 1 ) {
            return null;
      }

      $first_item    =  $cart_item_keys[0];
      $first_item_id =  $cart_contents[$first_item]['product_id'];
        
      // if((get_post_meta($first_item_id,'ali__is_ali_product',true)=='0') || (get_post_meta($first_item_id,'ali__is_ali_product',true)=='')){  
      //   $first_item_type = 0;
      // }else if(get_post_meta($first_item_id,'ali__is_ali_product',true)=='1'){
      //   $first_item_type = 1;
      // }  

      // if(get_current_blog_id()==2421)
      // {
      //   echo get_post_meta($first_item_id,'ali__is_ali_product',true);
      // }
      $kota_code = WC_Countries::get_base_country();
      $kota_toko = WC()->countries->countries["$kota_code"];

      if((get_post_meta($first_item_id,'ali__is_ali_product',true)=='0') || (get_post_meta($first_item_id,'ali__is_ali_product',true)=='')){  
        $first_item_origin = $kota_toko;
      }else if(get_post_meta($first_item_id,'ali__is_ali_product',true)=='1'){
        if(metadata_exists('post',$first_item_id,'country')){
          $country_origin = get_post_meta($first_item_id,'country',true);
          $first_item_origin = $country_origin;
        }   
      }

      // if(metadata_exists('post',$first_item_id,'country')){
      //   $country_origin = get_post_meta($first_item_id,'country',true);
      //   if(strtolower($country_origin) == 'china'){
      //     $first_item_origin = 1;
      //   }else if(strtolower($country_origin) == 'thailand'){
      //     $first_item_origin = 2;
      //   }else if(strtolower($country_origin) == 'indonesia'){
      //     $first_item_origin = 0;
      //   }else{
      //     $first_item_origin = 3;
      //   }
      // }else{
      //   $first_item_origin = 0;
      // }
    //$first_item_type = get_product_validation($first_item_id);
    //$first_item_origin = get_product_origin($first_item_id);
    
    foreach ( $cart_item_keys as $key ) {
        if ( $key  ==  $first_item ) {
            continue;
        }else {
          $product_id =  $cart_contents[$key]['product_id'];

          if((get_post_meta($product_id,'ali__is_ali_product',true)=='0') || (get_post_meta($product_id,'ali__is_ali_product',true)=='')){  
            $other_item_origin = $kota_toko;
          }else if(get_post_meta($product_id,'ali__is_ali_product',true)=='1'){
            if(metadata_exists('post',$product_id,'country')){
              $country_origin = get_post_meta($product_id,'country',true);
              $other_item_origin = $country_origin;
            }
          }

          if($other_item_origin != $first_item_origin){
              $mismatched_type = 1;
          }

        }
    }

        if (isset($mismatched_type)) {
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
            echo '<a href="#" style="background-color:#95a5a6" class="checkout-button button alt wc-forward">Lanjut Bayar</a>';
        }  
    }

    public function enqueue_load_fa()

    {

        wp_enqueue_style('load-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');

    }



    public function decorateProductResponse($object, $request, $create)

    {

        $importFrom = $request->get_param('import_from');

        

        if (null !== $importFrom && $importFrom === 'ali') {

            $id = $object->get_id();

            

            $metaData = $request->get_param('meta_data');



			add_post_meta($id, 'ali__meta_data', $metaData, true);

            $this->saveOrUpdateAliProduct($id);

        }

        return $response;

    }





    public function saveOrUpdateAliProduct($id)

    {

        if (!$this->checker->isALIProduct($id)) {

            add_post_meta($id, 'ali__is_ali_product', true, true);

        }

    }



    public function handleWebhookFromALI($http_args, $response, $duration, $arg, $this_id)

    {

        $this->handleProductPush();

        $this->handleProductUnpush();

    }



    public function handleProductPush()

    {

        // Add product to our table if it's comes from ALI

    }



    public function handleProductUnpush()

    {

        // Remove product from our table when unpushed from ALI

    }



    public function wooCommerceNotActivated()

    {

        echo "<div class='error notice'><p>" .

            "WooCommerce is not activated yet." .

        "</p></div>";

    }



    public static function aliConnectActivated()

    {

        echo "<div class='updated notice'><p>" .

            "Ali Connect is activated." .

        "</p></div>";

    }



    public function addProductDeliveryTime($id)

    {

        global $product;



        if ($this->checker->isALIProduct($product->get_id())) {

            $this->showProductDeliveryTime($product->get_id());

        }

    }

    

    public function showProductDeliveryTime($d)
    {

        $format = get_option('date_format');

        $day = 24 * 60 * 60;

        $startDate = date($format, time() + (14 * $day));

        $endDate = date($format, time() + (21 * $day));
        $ali_proddd = get_post_meta($d,'ali__is_ali_product',true);

        if(metadata_exists('post',$d,'country')){

          $country_origin = get_post_meta($d,'country',true);
          $country_overwrite = get_post_meta($d,'origin_country',true);
          if($country_overwrite){
             $country_origin =  $country_overwrite;
          }          

          if($country_origin == 'China'){
            echo "<br><br>";
            echo 
"<style>" .
"* {  box-sizing: border-box; }" .
".column2 {  float: left;width: 50% ;   padding: 10px; border: 1px dotted #ddd}" .
".infoproduk:after {  content: '';   display: table;  clear: both; }" .
"@media screen and (max-width: 500px) { .column2 {width: 100%; padding: 12px; border: 1px dotted #ddd } } </style>" .
"<div class='infoproduk'>" .
" <div class='column2'>" .
" <h4 style='margin-top: 0; padding-top: 0'>Pengiriman dari</h4>" .
"     <i class='fas fa-globe'></i> <span>Origin : Gudang China<br><i class='fas fa-truck'></i>";
$arrival_info = get_post_meta($d,'arrival_info',true);
if($arrival_info){
  echo " Estimate Delivery : $arrival_info<br><br></span>";
}
else{
  echo " Estimate Delivery : 7-14 Hari<br><br></span>";
}

echo 
"   </div>" .
" <div class='column2'>" .
" <h4 style='margin-top: 0; padding-top: 0'>Notice </h4>" .
"     <i class='far fa-money-bill-alt'></i> <span>Barang ini dikirim dari luar negeri dan ada kemungkinan delay selama masa Covid-19</span>" .
"   </div>" .
"</div>" .

      "<!--<div style='background: #fff; padding: 12px; border: 1px dotted #ddd'>-->" .

            "<!--<p style='font-weight: 300'><i class='fa fa-plane'></i> Goods shipped from China.</p>-->" .

            "<!--<h4 style='margin-top: 0; padding-top: 0'>Barang Pre-Order</h4>-->" .

            "<!--<i class='fas fa-globe'></i> <span>Pengiriman dari : Luar Negeri<br><i class='fas fa-truck'></i> Lama Pengiriman : 14-21 Hari<br></span>-->" .
            "<!--<span>Wholesale order delivery within 30 days.</span>-->" .

            "<!--<span>{$startDate} - {$endDate}</span>-->" .

            "<!--</div></br>-->";

          }else if($country_origin == 'Thailand'){
            echo "<br><br>";
            echo 
"<style>" .
"* {  box-sizing: border-box; }" .
".column2 {  float: left;width: 50% ;   padding: 10px; border: 1px dotted #ddd}" .
".infoproduk:after {  content: '';   display: table;  clear: both; }" .
"@media screen and (max-width: 500px) { .column2 {width: 100%; padding: 12px; border: 1px dotted #ddd } } </style>" .

"<div class='infoproduk'>" .
" <div class='column2'>" .
" <h4 style='margin-top: 0; padding-top: 0'>Pengiriman dari</h4>" .
"     <i class='fas fa-globe'></i> <span>Origin : Gudang Thailand <br><i class='fas fa-truck'></i> Lama Pengiriman: 3-7 Hari<br></span>" .
"   </div>" .
" <div class='column2'>" .
" <h4 style='margin-top: 0; padding-top: 0'>Kualitas produk </h4>" .
"     <i class='far fa-money-bill-alt'></i> <span>Harga terbaik<br><i class='far fa-thumbs-up'></i> Best Collection<br></span>" .
"   </div>" .
"</div>" .

      "<!--<div style='background: #fff; padding: 12px; border: 1px dotted #ddd'>-->" .

            "<!--<p style='font-weight: 300'><i class='fa fa-plane'></i> Goods shipped from China.</p>-->" .

            "<!--<h4 style='margin-top: 0; padding-top: 0'>Barang Pre-Order</h4>-->" .

            "<!--<i class='fas fa-globe'></i> <span>Pengiriman dari : Luar Negeri<br><i class='fas fa-truck'></i> Lama Pengiriman : 14-21 Hari<br></span>-->" .
            "<!--<span>Wholesale order delivery within 30 days.</span>-->" .

            "<!--<span>{$startDate} - {$endDate}</span>-->" .

            "<!--</div></br>-->";
          }else if($country_origin == 'Indonesia'){
             echo "<br><br>";
             if($ali_proddd!='1'){
	             echo 
	"<style>" .
	"* {  box-sizing: border-box; }" .
	".column2 {  float: left;width: 50% ;   padding: 10px; border: 1px dotted #ddd}" .
	".infoproduk:after {  content: '';   display: table;  clear: both; }" .
	"@media screen and (max-width: 500px) { .column2 {width: 100%; padding: 12px; border: 1px dotted #ddd } } </style>" .

	"<div class='infoproduk'>" .
	" <div class='column2'>" .
	" <h4 style='margin-top: 0; padding-top: 0'>Pengiriman dari</h4>" .
	"     <i class='fas fa-globe'></i> <span>Origin : Gudang Indonesia <br><i class='fas fa-truck'></i></span>" .
	"   </div>" .
	" <div class='column2'>" .
	" <h4 style='margin-top: 0; padding-top: 0'>Kualitas produk </h4>" .
	"     <i class='far fa-money-bill-alt'></i> <span>Harga terbaik<br><i class='far fa-thumbs-up'></i> Best Collection<br></span>" .
	"   </div>" .
	"</div>" .

	      "<!--<div style='background: #fff; padding: 12px; border: 1px dotted #ddd'>-->" .

	            "<!--<p style='font-weight: 300'><i class='fa fa-plane'></i> Goods shipped from China.</p>-->" .

	            "<!--<h4 style='margin-top: 0; padding-top: 0'>Barang Pre-Order</h4>-->" .

	            "<!--<i class='fas fa-globe'></i> <span>Pengiriman dari : Luar Negeri<br><i class='fas fa-truck'></i> Lama Pengiriman : 14-21 Hari<br></span>-->" .
	            "<!--<span>Wholesale order delivery within 30 days.</span>-->" .

	            "<!--<span>{$startDate} - {$endDate}</span>-->" .

	            "<!--</div></br>-->";
        	}
        	else{
        	    
        		$supplier_proddd = get_post_meta($d,'supplier_name',true);
        		if(metadata_exists( 'post', $d, 'warehouse_name' )){
            		$supplier_proddd = get_post_meta($d,'warehouse_name',true);            	 
            	}
        		echo 
	"<style>" .
	"* {  box-sizing: border-box; }" .
	".column2 {  float: left;width: 50% ;   padding: 10px; border: 1px dotted #ddd}" .
	".infoproduk:after {  content: '';   display: table;  clear: both; }" .
	"@media screen and (max-width: 500px) { .column2 {width: 100%; padding: 12px; border: 1px dotted #ddd } } </style>" .

	"<div class='infoproduk'>" .
	" <div class='column2'>" .
	" <h4 style='margin-top: 0; padding-top: 0'>Pengiriman dari</h4>" .
	"     <i class='fas fa-globe'></i> <span>Origin : Gudang $supplier_proddd <br><i class='fas fa-truck'></i></span>" .
	"   </div>" .
	" <div class='column2'>" .
	" <h4 style='margin-top: 0; padding-top: 0'>Kualitas produk </h4>" .
	"     <i class='far fa-money-bill-alt'></i> <span>Harga terbaik<br><i class='far fa-thumbs-up'></i> Best Collection<br></span>" .
	"   </div>" .
	"</div>" .

	      "<!--<div style='background: #fff; padding: 12px; border: 1px dotted #ddd'>-->" .

	            "<!--<p style='font-weight: 300'><i class='fa fa-plane'></i> Goods shipped from China.</p>-->" .

	            "<!--<h4 style='margin-top: 0; padding-top: 0'>Barang Pre-Order</h4>-->" .

	            "<!--<i class='fas fa-globe'></i> <span>Pengiriman dari : Luar Negeri<br><i class='fas fa-truck'></i> Lama Pengiriman : 14-21 Hari<br></span>-->" .
	            "<!--<span>Wholesale order delivery within 30 days.</span>-->" .

	            "<!--<span>{$startDate} - {$endDate}</span>-->" .

	            "<!--</div></br>-->";
        	}
          }else{
            echo "<br><br>";
                         echo
              "<style>" .
              "* {  box-sizing: border-box; }" .
              ".column2 {  float: left;width: 50% ;   padding: 10px; border: 1px dotted #ddd}" .
              ".infoproduk:after {  content: '';   display: table;  clear: both; }" .
              "@media screen and (max-width: 500px) { .column2 {width: 100%; padding: 12px; border: 1px dotted #ddd } } </style>" .

              "<div class='infoproduk'>" .
              " <div class='column2'>" .
              " <h4 style='margin-top: 0; padding-top: 0'>Pengiriman dari Luar Negeri</h4>" .
              "     <i class='fas fa-globe'></i> <span>Origin : Gudang Luar Negeri <br><i class='fas fa-truck'></i> Lama Pengiriman: 3-14 Hari<br></span>" .
              "   </div>" .
              " <div class='column2'>" .
              " <h4 style='margin-top: 0; padding-top: 0'>Kualitas produk </h4>" .
              "     <i class='far fa-money-bill-alt'></i> <span>Harga terbaik<br><i class='far fa-thumbs-up'></i> Best Collection<br></span>" .
              "   </div>" .
              "</div>" .

                    "<!--<div style='background: #fff; padding: 12px; border: 1px dotted #ddd'>-->" .

                          "<!--<p style='font-weight: 300'><i class='fa fa-plane'></i> Goods shipped from China.</p>-->" .

                          "<!--<h4 style='margin-top: 0; padding-top: 0'>Barang Pre-Order</h4>-->" .

                          "<!--<i class='fas fa-globe'></i> <span>Pengiriman dari : Luar Negeri<br><i class='fas fa-truck'></i> Lama Pengiriman : 14-21 Hari<br></span>-->" .
                          "<!--<span>Wholesale order delivery within 30 days.</span>-->" .

                          "<!--<span>{$startDate} - {$endDate}</span>-->" .

                          "<!--</div></br>-->";
          }
        }
    }
    
    public function showProductDeliveryTimeCart($d)

    {

        $valid = false;

        if ($d === null || $d === '') {

            if ($this->checker->cartHasALIProduct()) {

                $valid = true;

            }

        } else {

            if ($this->checker->orderHasALIProduct($d)) {

                $valid = true;

            }

        }

        

        if ($valid) {

            $format = get_option('date_format');

            $day = 24 * 60 * 60;

            $startDate = date($format, time() + (7 * $day));

            $endDate = date($format, time() + (14 * $day));



            echo "<div style='background: #ededed; padding: 12px;'>" .

                "<p style='font-weight: 300'><i class='fa fa-plane'></i> Beberapa pesanan adalah barang dengan pengiriman dari luar negeri.</p>" .

                "<!--<h4 style='margin-top: 0; padding-top: 0'>Estimasi sampai</h4>-->" .

                "<span><b>Estimasi sampai</b> : 7-14 hari<br>{$startDate} - {$endDate}</span>" .

                "</div>";

        }

    }



}



$aliConnectWordpress = new Ali_Connect_Wordpress();



$aliConnectWordpress->init();



function Ali_Connect_Install()

{

    return Ali_Connect_Wordpress::install();

}


register_activation_hook(__FILE__, 'Ali_Connect_Install');

/**
 * @package   Pointer Wordpress
 * @author    OnlineStore ID
 * @license   GPL-3.0+
 * @link      https://onlinestore.id
 * @copyright 2019 GPL
 */

/**
 * Super pointer creation for WP Admin https://github.com/WPBP/PointerPlus
 */

class PointerPlus {

  /**
   * Prefix strings like styles, scripts and pointers IDs
   * @var string
   */
  var $prefix = 'pointerplus';
  var $pointers = array();

  /**
   * Construct the class parameter
   * 
   * @param array $args Parameters of class.
   * @return void
   */
  function __construct( $args = array() ) {
    if ( isset( $args[ 'prefix' ] ) ) {
	$this->prefix = $args[ 'prefix' ];
    }
    add_action( 'current_screen', array( $this, 'maybe_add_pointers' ) );
  }

  /**
   * Set pointers and its options
   *
   * @since 1.0.0
   */
  function initial_pointers() {
    global $pagenow;
    $defaults = array(
	  'class' => '',
	  'width' => 300, //only fixed value
	  'align' => 'middle',
	  'edge' => 'left',
	  'post_type' => array(),
	  'pages' => array(),
	  'icon_class' => ''
    );
    $screen = get_current_screen();
    $current_post_type = isset( $screen->post_type ) ? $screen->post_type : false;
    $search_pt = false;

    $pointers = apply_filters( $this->prefix . '-pointerplus_list', array(
		// Pointers are added through the 'initial_pointerplus' filter
		), $this->prefix );

    foreach ( $pointers as $key => $pointer ) {
	$pointers[ $key ] = wp_parse_args( $pointer, $defaults );
	$search_pt = false;
	// Clean from null ecc
	$pointers[ $key ][ 'post_type' ] = array_filter( $pointers[ $key ][ 'post_type' ] );
	if ( !empty( $pointers[ $key ][ 'post_type' ] ) ) {
	  if ( !empty( $current_post_type ) ) {
	    if ( is_array( $pointers[ $key ][ 'post_type' ] ) ) {
		// Search the post_type
		foreach ( $pointers[ $key ][ 'post_type' ] as $value ) {
		  if ( $value === $current_post_type ) {
		    $search_pt = true;
		  }
		}
		if ( $search_pt === false ) {
		  unset( $pointers[ $key ] );
		}
	    } else {
		new WP_Error( 'broke', __( 'PointerPlus Error: post_type is not an array!' ) );
	    }
	    // If not in CPT view remove all the pointers with post_type
	  } else {
	    unset( $pointers[ $key ] );
	  }
	}
	// Clean from null ecc
	if ( isset( $pointers[ $key ][ 'pages' ] ) ) {
	  if ( is_array( $pointers[ $key ][ 'pages' ] ) ) {
	    $pointers[ $key ][ 'pages' ] = array_filter( $pointers[ $key ][ 'pages' ] );
	  }
	  if ( !empty( $pointers[ $key ][ 'pages' ] ) ) {
	    if ( is_array( $pointers[ $key ][ 'pages' ] ) ) {
		// Search the page
		foreach ( $pointers[ $key ][ 'pages' ] as $value ) {
		  if ( $pagenow === $value ) {
		    $search_pt = true;
		  }
		}
		if ( $search_pt === false ) {
		  unset( $pointers[ $key ] );
		}
	    } else {
		new WP_Error( 'broke', __( 'PointerPlus Error: pages is not an array!' ) );
	    }
	  }
	}
    }

    return $pointers;
  }

  /**
   * Check that pointers haven't been dismissed already. If there are pointers to show, enqueue assets.
   * 
   * @since 1.0.0
   */
  function maybe_add_pointers() {
    // Get default pointers that we want to create
    $default_keys = $this->initial_pointers();

    // Get pointers dismissed by user
    $dismissed = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

    // Check that our pointers haven't been dismissed already
    $diff = array_diff_key( $default_keys, array_combine( $dismissed, $dismissed ) );

    // If we have some pointers to show, save them and start enqueuing assets to display them
    if ( !empty( $diff ) ) {
	$this->pointers = $diff;
	add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );

	foreach ( $diff as $pointer ) {
	  if ( isset( $pointer[ 'phpcode' ] ) ) {
	    add_action( 'admin_notices', $pointer[ 'phpcode' ] );
	  }
	}
    }
    $this->pointers[ 'l10n' ] = array( 'next' => __( 'Next' ) );
  }

  /**
   * Enqueue pointer styles and scripts to display them.
   *
   * @since 1.0.0
   */
  function admin_enqueue_assets() {
    $base_url = plugins_url( '', __FILE__ );
    wp_enqueue_style( $this->prefix, $base_url . '/pointerplus.css', array( 'wp-pointer' ) );
    wp_enqueue_script( $this->prefix, $base_url . '/pointerplus.js?var=' . str_replace( '-', '_', $this->prefix ) . '_pointerplus', array( 'wp-pointer' ) );
    wp_localize_script( $this->prefix, str_replace( '-', '_', $this->prefix ) . '_pointerplus', apply_filters( $this->prefix . '_pointerplus_js_vars', $this->pointers ) );
  }

  /**
   * Reset pointer
   *
   * @since 1.0.0
   */
  function reset_pointer() {
    add_action( 'current_screen', array( $this, '_reset_pointer' ), 0 );
  }

  /**
   * Reset pointer in hook
   *
   * @since 1.0.0
   */
  function _reset_pointer( $id = 'me' ) {
    if ( $id === 'me' ) {
	$id = get_current_user_id();
    }
    $pointers = explode( ',', get_user_meta( $id, 'dismissed_wp_pointers', true ) );
    foreach ( $pointers as $key => $pointer ) {
		if ( strpos( $pointer, $this->prefix ) === 0 ) {
	  		unset( $pointers[ $key ] );
		}
    }
    $meta = implode( ',', $pointers );

    update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $meta );
  }

}

add_action('wp_ajax_make_empty_ali_cart', 'make_empty_ali_cart');
add_action('wp_ajax_nopriv_make_empty_ali_cart', 'make_empty_ali_cart');
function make_empty_ali_cart() {
  if( ! WC()->cart->is_empty() ){
    WC()->cart->empty_cart();
    // if(get_current_blog_id()==2421){
      // echo json_encode($_POST,true);
      if(isset($_POST['vari_id']) && $_POST['vari_id']!=''){
        WC()->cart->add_to_cart( $_POST['prod_id'], $_POST['qty'], $_POST['vari_id'] );
        wc_add_notice( __( 'Terima kasih. Barang berhasil dimasukkan ke dalam cart.', 'woocommerce' ));
      }
      else{
        WC()->cart->add_to_cart( $_POST['prod_id'], $_POST['qty']);
        wc_add_notice( __( 'Terima kasih. Barang berhasil dimasukkan ke dalam cart.', 'woocommerce' ));
      }
    // }    
    echo 'sukses';
    wp_die(); 
  }
  else
  {
    echo "error";
    wp_die();
  }    
}

// add_action( 'init', 'script_enqueuer_ali');

function script_enqueuer_ali() {

        wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

        wp_localize_script( 'empty-cart-ali', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php')));

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'empty-cart-ali' );
      // }
}

add_action( 'woocommerce_product_options_inventory_product_data', 'wc_uom_product_fields');
add_action( 'woocommerce_process_product_meta', 'wc_uom_save_field_input' );

function wc_uom_product_fields() {
    // Security..... make sure the form request comes from the right place people.
    // wp_nonce_field( basename( __FILE__ ), 'wc_uom_product_fields_nonce' );

    echo '<div class="wc_uom_input">';
      // Woo_UOM fields will be created here.
      woocommerce_wp_text_input(
        array(
          'id'          => '_uom',
          'label'       => __( 'Unit of Measure', 'woocommerce' ),
          'placeholder' => '',
          'desc_tip'    => 'true',
          'description' => __( 'Enter your unit of measure for this product here. such as Box, Crt or etc', 'woocommerce' ),
        )
      );

      woocommerce_wp_text_input(
        array(
          'id'          => '_uom_base',
          'label'       => __( 'Base Unit of Measure', 'woocommerce' ),
          'placeholder' => '',
          'desc_tip'    => 'true',
          'description' => __( 'Enter your base unit of measure for this product here. if uom is Box, then this base unit is Pcs or etc', 'woocommerce' ),
        )
      );

      woocommerce_wp_text_input(
        array(
            'id' => '_uom_base_qty',
            'placeholder' => '',
            'label' => __('Quantity of Base Unit of Measure', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '1'
            ),
            'desc_tip'    => 'true',
          'description' => __( 'Enter your quantity base unit of measure for this product here. such as "20" if Unit of measure is "Box" and base unit is "Pcs"', 'woocommerce' ),
        )
      );

    echo '</div>';
}

add_action( 'woocommerce_checkout_create_order_line_item', 'hmma',10,4);
function hmma( $item, $cart_item_key, $values, $order ) {
    // Get a product custom field value
    $custom_field_value = get_post_meta( $item->get_product_id(), '_uom', true );
    // Update order item meta
    if ( ! empty( $custom_field_value ) ){
        $item->update_meta_data( '_uom', $custom_field_value );
    }
    // else{
    //     $item->update_meta_data( '_uom', 'pcs' );
    // }
}

function woa_custom_hours( $hours ) {
  // More explication on WooCommerce status : https://docs.woocommerce.com/document/managing-orders/
  
  $jam_custom = 48;
  // $status[] = 'wc-processing';
  //$status[] = 'wc-completed';
  //$status[] = 'wc-refunded';
  //$status[] = 'wc-failed';

  return $jam_custom;
}
add_filter( 'woo_cao_default_hours', 'woa_custom_hours', 10, 1 );

function woa_custom_statustocancel_hook( $status ) {
  // More explication on WooCommerce status : https://docs.woocommerce.com/document/managing-orders/
  $status[] = 'wc-pending';
  $status[] = 'wc-on-hold';
  // $status[] = 'wc-processing';
  //$status[] = 'wc-completed';
  //$status[] = 'wc-refunded';
  //$status[] = 'wc-failed';

  return $status;
}
add_filter( 'woo_cao_statustocancel', 'woa_custom_statustocancel_hook', 10, 1 );

// function wc_uom_save_field_input( $post_id ) {
//    if ( isset( $_POST['_woo_uom_input'], $_POST['wc_uom_product_fields_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wc_uom_product_fields_nonce'] ), basename( __FILE__ ) ) ) :
//      $woo_uom_input = sanitize_text_field( wp_unslash( $_POST['_woo_uom_input'] ) );
//      update_post_meta( $post_id, '_woo_uom_input', esc_attr( $woo_uom_input ) );
//    endif;
//  }

function wc_uom_save_field_input($post_id){
  if ( isset( $_POST['_uom'])){
    $woo_uom_input = sanitize_text_field( wp_unslash( $_POST['_uom'] ) );
    update_post_meta( $post_id, '_uom', esc_attr( $woo_uom_input ) );
  }

  if ( isset( $_POST['_uom_base'])){
    $woo_uom_input = sanitize_text_field( wp_unslash( $_POST['_uom_base'] ) );
    update_post_meta( $post_id, '_uom_base', esc_attr( $woo_uom_input ) );
  }

  if ( isset( $_POST['_uom_base_qty'])){
    $woo_uom_input = sanitize_text_field( wp_unslash( $_POST['_uom_base_qty'] ) );
    update_post_meta( $post_id, '_uom_base_qty', esc_attr( $woo_uom_input ) );
  }
}

function namespace_register_search_route() {
    register_rest_route('ali/v1', '/search', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'namespace_ajax_search',
        'args' => namespace_get_search_args()
    ]);
}
add_action( 'rest_api_init', 'namespace_register_search_route');

function namespace_get_search_args() {
    $args = [];
    $args['search'] = [
       'description' => esc_html__( 'The search term.', 'namespace' ),
       'type'        => 'string',
   ];
   return $args;
}

function namespace_ajax_search( $request ) {
    global $wpdb;
    $posts = [];
    $results = [];

    $param = $request['search'];

    if( isset($request['search'])){
      $ids = $wpdb->get_results("SELECT ID,guid FROM {$wpdb->posts} WHERE guid = '$param'");
      foreach ($ids as $valune) {
        $results[] = [
          //'source_url' => $valune->guid,
          'id' => (int)$valune->ID,
        ];
      }
    }
  
    if( empty($results) ) {
      $results[] = [
          'id' => null,
      ];
    }
    return rest_ensure_response( $results );
}

add_action('woocommerce_single_product_summary','cmk_additional_button_timer',20);
function cmk_additional_button_timer() {
    $productID = get_the_ID();
	  if($productID){
		$time_meta = get_post_meta($productID,'expired_date',true);
		// $time_meta = 
		if($time_meta){
			// echo '<p id="saleend" style="color:red">'.$time_meta.'</p>';
			echo "<br><br>";//.$time_meta;
			echo "Promo Group Buy :<br>";
      $datass = str_replace("T", " ", $time_meta);
			echo "Valid until : $datass<br>";
			echo '<p id="saleend" style="color:red;"></p>';
			$coutry = get_post_meta($productID,'origin_country',true);
			if($coutry){
				echo "Country Origin : $coutry<br>";
			}
			$arr_info = get_post_meta($productID,'arrival_info',true);
			if($arr_info){
				echo "Estimate Arrival : $arr_info<br>";
			}					
			
			// echo "<br>".$time_meta;

			wp_register_script( 'timer-gbuy-ali', plugin_dir_url(__FILE__).'/js/gbtime.js', array('jquery') );

			wp_localize_script( 'timer-gbuy-ali', 'myAjax', array( 
			  'ajaxurl'     => admin_url( 'admin-ajax.php'),
			  'waktu'     	=> $time_meta,
			));

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'timer-gbuy-ali' );

            // wp_register_script( "empty-cart-ali", plugin_dir_url(__FILE__).'/js/atca.js', array('jquery') );

			
		}
	}
}

add_filter('woocommerce_is_purchasable', 'woocommerce_cloudways_purchasable',20);
function woocommerce_cloudways_purchasable($cloudways_purchasable) {
	$productID = get_the_ID();
	if($productID){
		$time_meta = get_post_meta($productID,'expired_date',true);
		if($time_meta){
			$timestamp1 = strtotime($time_meta)-25200;
			$time_now = time();
			return (($time_now > $timestamp1) ? false : $cloudways_purchasable);	
		}
		// $productnya = wc_get_product($productID);
	}
	return $cloudways_purchasable;
}

if(is_plugin_active('woocommerce/woocommerce.php')){  
    function your_shipping_method_init2() {
        if ( ! class_exists( 'WC_Shipping_AsiaCom' ) ) {
            class WC_Shipping_AsiaCom extends WC_Shipping_Method {

                /**
                 * Constructor. The instance ID is passed to this.
                 */
                public function __construct( $instance_id = 0 ) {
                    $this->id                    = 'AsiaCom_Shipping';
                    $this->instance_id           = absint( $instance_id );
                    $this->method_title          = __( 'AsiaCommerce Shipping Method' );
                    $this->method_description    = __( 'AsiaCommerce Shipping method for your shipping rates. Please set your origin province and city for your original product' );
                    $this->supports              = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );
                    $kota_code = wc_get_base_location();
                    $kota_code = $kota_code['country'];
                    $kota_toko = WC()->countries->countries["$kota_code"];
                    
                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/country?filter[name][like]=$kota_toko");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                        'Content-Type: application/json')
                    );
                    $result = curl_exec($ch);
                    
                    $arr_res = json_decode($result,true);
                    $id_country = $arr_res['data'][0]['id'];
                    
                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/provinces?filter[country_id][is]=$id_country&page[limit]=100");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                        'Content-Type: application/json')
                    );
                    
                    $result = curl_exec($ch);
                    
                    $arr_res = json_decode($result,true);
                    curl_close($ch);
                    
                    $array_option = array();
                    $array_option[''] = '----- Select Province -----';
                    
                    foreach ($arr_res['data'] as $key => $value) {
                        $tampung = $value['id'];
                        $array_option["$tampung"] = $value['attributes']['name'];
                    }
                    
                    // city
                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=10000&filter[country_id][is]=$id_country");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                    // curl_setopt($ch, CURLOPT_GET, true);
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    
                    // Set HTTP Header for POST request 
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                        'Content-Type: application/json'
                    /*'Content-Length: ' . strlen($payload)*/)
                    );
                    
                    // Submit the POST request
                    $result = curl_exec($ch);
                    
                    $arr_res = json_decode($result,true);
                    
                    $array_option2 = array();
                    $array_option2[''] = '----- Select City -----';
                    
                    foreach ($arr_res['data'] as $key => $value) {
                        $tampung = $value['attributes']['name'];
                        $array_option2["$tampung"] = $value['attributes']['name'];
                    }
                    
                    // Close cURL session handle
                    curl_close($ch);
                    
                    // $this->instance_form_fields = array(
                    //   'calculate_inter' => array(
                    //     'title' 		=> __( 'Calculate International Rate' ),
                    //     'type' 			=> 'checkbox',
                    //     'label' 		=> __( 'Enable / Disable Calculate for International Shipping Product' ),
                    //     'default' 		=> 'yes',
                    //   ),
                    //   'ali_province' => array(
                    //     'title' => 'Province',
                    //     'description' => 'Select your origin province',
                    //     // 'type' => 'text|password|textarea|checkbox|select|multiselect',
                    //     'type' => 'select',
                    //     'default' => '',
                    //     'class' => 'ali_provi',
                    //     'css' => '',
                    //     // 'label' => 'Label', // checkbox only
                    //     'options' => $array_option,
                    //     'custom_attributes' => array(
                    //                             'required'=>'required'
                    //     )
                    //   ),
                    //   'ali_city' => array(
                    //     'title' => 'City',
                    //     'description' => 'Select your origin city',
                    //     // 'type' => 'text|password|textarea|checkbox|select|multiselect',
                    //     'type' => 'select',
                    //     'default' => '',
                    //     'class' => 'ali_citi',
                    //     'css' => '',
                    //     // 'label' => 'Label', // checkbox only
                    //     'options' => $array_option2
                    //   )
                    // );
                    
                    $this->instance_form_fields = array(
                        'AsiaCom_Product' => array(
                            'title' 		=> __( 'For Asiacommerce Product : ' ),
                            'type' 			=> 'text',
                            // 'label' 		=> __( '' )for checkbox only,
                            'css' => 'display:none;',
                            'description' => 'For some country routess, this plugin will calculate the Air Courier rates for AsiaCommerce products. ',
                            'default' 		=> 'For some country routes, this plugin will calculate the Air Courier rates for AsiaCommerce products. ',
                            'custom_attributes' => array(
                                'disabled'=>'disabled'
                            )
                        ),
                        'calculate_inter' => array(
                            'title' 		=> __( 'Calculate Air Courier Rate' ),
                            'type' 			=> 'checkbox',
                            'description' => 'If enable, The base air courier rates will be calculated.
                            If disable, the air couriers list will not displayed in checkout page. This means that you do not 
                            charge air courier fees to your customers, a.k.a FREE SHIPPING',
                            'label' 		=> __( 'Enable / Disable Calculate Air Courier Rate for Asiacommerce products' ),
                            'default' 		=> 'yes',
                        ),
                        'Not_AsiaCom_Product' => array(
                            'title' 		=> __( 'For Non Asiacommerce Product : ' ),
                            'type' 			=> 'text',
                            'css' => 'display:none;',
                            'description' => 'If you are selling Non Asiacommerce products, you can still use this shipping method to calculate domestic shipping rates. You would still need to setup the shipping origin of your store below.<br> 
                            The couriers list that will displayed in checkout, only those supported by AsiaCommerce.',
                            'default' 		=> 'For some country routes, this plugin will calculate the Air Courier rates for AsiaCommerce products. ',
                            'custom_attributes' => array(
                                'disabled'=>'disabled'
                            )
                        ),
                        'ali_province' => array(
                            'title' => 'Province',
                            'description' => 'Select your origin province',
                            'type' => 'select',
                            'default' => '',
                            'class' => 'ali_provi',
                            'css' => '',
                            'options' => $array_option,
                            'custom_attributes' => array(
                                'required'=>'required'
                            )
                        ),
                        'ali_city' => array(
                            'title' => 'City',
                            'description' => 'Select your origin city',
                            'type' => 'select',
                            'default' => '',
                            'class' => 'ali_citi',
                            'css' => '',
                            'options' => $array_option2
                        )
                    );
                    
                    // $this->enabled            = $this->get_option( 'enabled' );
                    $this->title                = 'AsiaCom_Shipping';
                    // $this->admin_set = $this->instance_form_fields;
                    $this->init_settings();
                    
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
  
                }
        
                private function hitung_country_( $package = array() ) {
                }
              
                /**
                * calculate_shipping function.
                *
                * @access public
                * @param mixed $package
                * @return void
                */
                public function calculate_shipping( $package = array() ) {
                    
                    // print("<pre>".print_r($package,true)."</pre>");
                    $infoname = get_bloginfo( 'name' );
                    
                    foreach ($package['contents'] as $key_cont => $value_conte) {
                        $id_prod = $value_conte['product_id'];
                        $ali_prod = get_post_meta($id_prod,'ali__is_ali_product',true);
                        $ori_city_prod = get_post_meta($id_prod,'origin_city',true);
                        if(!$ori_city_prod){
                            $ori_city_prod = get_post_meta($id_prod,'city',true);
                        }
                        $ori_country_prod = get_post_meta($id_prod,'country',true);
                    }
                    
                    $destination_city = $package['destination']['city'];
                    $destination_country = $package['destination']['country'];
                    $destination_country = WC()->countries->countries["$destination_country"];
                    
                    if($ali_prod && $ali_prod=="1"){            
                        // echo "ali prod = 1";
                        if($ori_country_prod){
                            if($ori_country_prod==$destination_country){
                                $origin_city = $ori_city_prod; /*? $ori_city_prod : $this->instance_settings['ali_city'];*/
                                if($origin_city){
                                    $url_endpoint = urlencode($origin_city);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][like]=$url_endpoint");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);         
                                    $id_origin_city = (int)$arr_res['data'][0]['id'];
                                    
                                    $url_endpoint = urlencode($destination_city);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][is]=$url_endpoint");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, 
                                    array(
                                      'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                      'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);     
                        
                                    if($arr_res['success']==1){
                                        $id_destination_city = (int)$arr_res['data'][0]['id'];
                                        // Close cURL session handle
                                        
                                        $ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[limit]=10&filter[origin_id][in]=$id_origin_city,&filter[destination_id][in]=$id_destination_city&include=courier");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                            'Content-Type: application/json')
                                        );
                                        $result = curl_exec($ch);
                                        $arr_res = json_decode($result,true); 
                                        curl_close($ch);
                                        
                                        $berat1 = 0;
                                        foreach ($package['contents'] as $key_content => $value_content) {
                                            $berat1 += (float)$value_content['data']->get_weight()*$value_content['quantity'];
                                        }
                                    
                                        if($berat1==0){
                                            $berat1=0.1;
                                        }         
                                    
                                    
                                        foreach ($arr_res['data'] as $key_id => $value_data) {
                                            $berat_max = $value_data['attributes']['maximum'];
                                            if($berat1>$berat_max){
                                                // wc_add_notice( 'Shipping not support weight more than 50Kg, Please decrease the quantity or replace item', 'notice' );
                                                continue;
                                            }
                                            
                                            $hitung1 = $value_data['attributes']['rate_per_weight'];
                                            $hasilbagi1 = (int)($berat1 /$hitung1);
                                            $selisih=(float)$berat1-$hasilbagi1;
                                            
                                            if($hasilbagi1==0){
                                                $hasilbagi1=1;
                                            }
                                            
                                            if($selisih<=0.3){
                                                $totalbayar = $value_data['attributes']['rate']*$hasilbagi1;
                                            }
                                            else{
                                                $totalbayar = $value_data['attributes']['rate']*($hasilbagi1+1);
                                            }
                                            
                                            $kurir_id = $value_data['attributes']['courier_id'];
                                            
                                            foreach ($arr_res['included']['courier'] as $id_kur => $isi_data) {
                                                if($kurir_id==$id_kur){
                                                    $courier_name = $isi_data['attributes']['name'];
                                                }
                                            }
                                            
                                            if ( metadata_exists( 'post', $id_prod, 'supplier_name' ) ) {
                                                $wh_meta = get_post_meta($id_prod,'supplier_name',true);
                                            }else{
                                                $wh_meta = $infoname;
                                            }
                                            
                                            if ( metadata_exists( 'post', $id_prod, 'warehouse_name' ) ) {
                                                $wh_meta = get_post_meta($id_prod,'warehouse_name',true);
                                            }
                                            
                                            $wh_name = "WH ".$wh_meta;
                                            $tipe_layanan = $value_data['attributes']['name'];
                                            $etd = $value_data['attributes']['remarks'];
                                            $label = $wh_name." : ".$courier_name." - ".$tipe_layanan." ( ".$etd." ) ";
                                            
                                            $rate = array(
                                                'id'        => $this->get_rate_id( $courier_name . ':' . $tipe_layanan ),
                                                'label'     => $label,
                                                'cost'      => $totalbayar,
                                                'calc_tax'  => 'per_item'
                                            );
                                            $this->add_rate( $rate );
                                        }
                                    }
                                    else{
                                    
                                    }
                                }
                            }
                            else if(strtolower($ori_country_prod)=="china" && strtolower($destination_country)=="indonesia"){
                                $origin_city = "Kota Jakarta Barat";
                                $url_endpoint = urlencode($origin_city);
                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][like]=$url_endpoint");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                    'Content-Type: application/json')
                                );
                                $result = curl_exec($ch);
                                $arr_res = json_decode($result,true);      
                                curl_close($ch);         
                                $id_origin_city = (int)$arr_res['data'][0]['id'];
                            
                                $url_endpoint = urlencode($destination_city);
                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][is]=$url_endpoint");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER,array(
                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                    'Content-Type: application/json')
                                );
                                $result = curl_exec($ch);
                                $arr_res = json_decode($result,true);      
                                curl_close($ch);     
                                $id_destination_city = (int)$arr_res['data'][0]['id'];
                                
                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[limit]=10&filter[origin_id][in]=$id_origin_city,&filter[destination_id][in]=$id_destination_city&include=courier");
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                    'Content-Type: application/json')
                                );
                                $result = curl_exec($ch);
                                $arr_res = json_decode($result,true); 
                                curl_close($ch);
                            
                                $berat1 = 0;
                                foreach ($package['contents'] as $key_content => $value_content) {
                                    $berat1 += (float)$value_content['data']->get_weight()*$value_content['quantity'];
                                }
                            
                                if($berat1==0){
                                    $berat1=0.1;
                                }         
                            
                                foreach ($arr_res['data'] as $key_id => $value_data) {
                                    $berat_max = $value_data['attributes']['maximum'];
                                    if($berat1>$berat_max){
                                        // wc_add_notice( 'Shipping not support weight more than 50Kg, Please decrease the quantity or replace item', 'notice' );
                                        continue;
                                    }
                            
                                    $hitung1 = $value_data['attributes']['rate_per_weight'];
                                    $hasilbagi1 = (int)($berat1 /$hitung1);
                                    $selisih=(float)$berat1-$hasilbagi1;
                                    
                                    if($selisih<=0.3){
                                        if($hasilbagi1==0){
                                            $hasilbagi1=1;
                                        }
                                        $totalbayar = $value_data['attributes']['rate']*($hasilbagi1);
                                    }
                                    else{
                                        $totalbayar = $value_data['attributes']['rate']*($hasilbagi1+1);
                                    }
                            
                                    $kurir_id = $value_data['attributes']['courier_id'];
                            
                                    foreach ($arr_res['included']['courier'] as $id_kur => $isi_data) {
                                        if($kurir_id==$id_kur){
                                            $courier_name = $isi_data['attributes']['name'];
                                        }
                                    }
                            
                                    if ( metadata_exists( 'post', $id_prod, 'supplier_name' ) ) {
                                        $wh_meta = get_post_meta($id_prod,'supplier_name',true);
                                    }else{
                                        $wh_meta = $infoname;
                                    }
                                    
                                    if ( metadata_exists( 'post', $id_prod, 'warehouse_name' ) ) {
                                        $wh_meta = get_post_meta($id_prod,'warehouse_name',true);
                                    }
                                    
                                    $wh_name = "WH ".$wh_meta;
                                    $tipe_layanan = $value_data['attributes']['name'];
                                    $etd = $value_data['attributes']['remarks'];
                                    $label = $wh_name." : ".$courier_name." - ".$tipe_layanan." ( ".$etd." ) ";
                                    
                                    // echo $hasilbagi1." ".$totalbayar."<br>";
                                    
                                    $rate = array(
                                        'id'        => $this->get_rate_id( $courier_name . ':' . $tipe_layanan ),
                                        'label'     => $label,
                                        'cost'      => $totalbayar,
                                        'calc_tax'  => 'per_item'
                                    );
                                    $this->add_rate( $rate );
                                }
                            }
                            else{
                                $enable_CI = $this->instance_settings['calculate_inter'];
                                $debug = 0;
                                if($enable_CI=='yes'){
                                    if($debug==1){
                                        echo $enable_CI."<br>";
                                    }
                                    $ori_country_prod = urlencode($ori_country_prod);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/country?page[num]=1&page[limit]=25&sort=name&filter[name][like]=$ori_country_prod");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);
                                    
                                    if($arr_res['success']==1){
                                        if($debug==1){
                                            echo $arr_res['data'][0]['id']."<br>";
                                        }
                                        $origin_country_id = $arr_res['data'][0]['id'];
                                        $destination_country = urlencode($destination_country);
                                        $ch = curl_init("https://api2.asiacommerce.net/api/v2/country?page[num]=1&page[limit]=25&sort=name&filter[name][like]=$destination_country");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                            'Content-Type: application/json')
                                        );
                                        $result = curl_exec($ch);
                                        $arr_res = json_decode($result,true);      
                                        curl_close($ch);
                                        
                                        if($arr_res['success']==1){
                                            if($debug==1){
                                                echo $arr_res['data'][0]['id']."<br>";
                                            }
                                            $dest_country_id = $arr_res['data'][0]['id'];
                                            
                                            $ch = curl_init("https://api2.asiacommerce.net/api/v2/country-hub/$origin_country_id/to/$dest_country_id");
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                'Content-Type: application/json')
                                            );
                                            $result = curl_exec($ch);
                                            $arr_res = json_decode($result,true);      
                                            curl_close($ch);   
                                            
                                            if($debug==1){
                                                // var_dump($arr_res);
                                                // echo $arr_res['data'][0]['id']."<br>";
                                            }
                                            
                                            if($arr_res['default_shipment_method']=='air_courier'){
                                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[num]=1&page[limit]=100&filter[origin_country_id][is]=$origin_country_id&filter[destination_country_id][is]=$dest_country_id&sort=-rate_per_weight&diverse=International&include=country,courier,destinationCountry");
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                    'Content-Type: application/json')
                                                );
                                                $result = curl_exec($ch);
                                                curl_close($ch);
                                                
                                                $berat_barang = 0;
                                                foreach ($package['contents'] as $key_content => $value_content) {
                                                    $berat_barang += (float)$value_content['data']->get_weight()*$value_content['quantity'];
                                                }
                                            
                                                if($berat_barang==0){
                                                    $berat_barang=0.1;
                                                }
                                                
                                                $arr_res = json_decode($result,true);
                                                $temp = array();
                                                $harga_rate = array();
                                                $currency_rate = array();
                                                $remarks = array();
                                                $selisih_min = array();
                                                $cour_id_now = '';
                                                $serv_nm_now = '';
                                                if($arr_res['success']==1){
                                                    foreach($arr_res['data'] as $number => $data){
                                                        $courier_id = $data['attributes']['courier_id'];
                                                        $service_name = $data['attributes']['name'];
                                                        
                                                        if($cour_id_now!=$courier_id){
                                                            $cour_id_now = $courier_id;
                                                        }
                                                        
                                                        if($serv_nm_now!=$service_name){
                                                            $serv_nm_now = $service_name;
                                                        }
                            
                                                        if(!isset($selisih_min[$cour_id_now][$serv_nm_now])){
                                                          $selisih_min[$cour_id_now][$serv_nm_now] = 100;
                                                        }
                            
                                                        $selisih_now = abs($berat_barang-$data['attributes']['rate_per_weight']);
                                                        
                                                        if($selisih_now<$selisih_min[$cour_id_now][$serv_nm_now]){
                                                            $selisih_min[$cour_id_now][$serv_nm_now] = $selisih_now;
                                                            $harga_rate[$cour_id_now][$serv_nm_now] = $data['attributes']['rate'];
                                                            $currency_rate[$cour_id_now][$serv_nm_now] = $data['attributes']['currency'];
                                                            $remarks[$cour_id_now][$serv_nm_now] = $data['attributes']['remarks'];
                                                        }
                                                    }
                                                }
                                                $market_currency = get_woocommerce_currency();
                                                
                                                if($debug==1){
                                                    var_dump($harga_rate);
                                                }
                                                
                                                foreach($harga_rate as $k1 => $v1){
                                                    foreach($v1 as $k2 => $v2){
                                                        if($selisih_min[$k1][$k2]<=1){
                                                            $label_rate = $arr_res['included']['courier'][$k1]['attributes']['name']." ".$k2." "." (".$remarks[$k1][$k2]." )";
                                                            if($currency_rate[$k1][$k2]!=$market_currency){
                                                                $currency_ret = $currency_rate[$k1][$k2];
                                                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/currencies?page[num]=1&page[limit]=10&sort=-id&filter[currency_code][like]=$currency_ret&filter[currency_code_target][like]=$market_currency");
                                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                                    'Content-Type: application/json')
                                                                );
                                                                $result = curl_exec($ch);
                                                                curl_close($ch);
                                                                $arr_res = json_decode($result,true);
                                                                
                                                                if($debug==1){
                                                                    var_dump($harga_rate[$k1][$k2]);
                                                                    var_dump($arr_res['data'][0]['attributes']['value']);
                                                                }
                                                                
                                                                if($arr_res['success']==1){
                                                                    $harga_rate_final = $harga_rate[$k1][$k2] * $arr_res['data'][0]['attributes']['value'];
                                                                }
                                                            }
                                                            else{
                                                                $harga_rate_final = $harga_rate[$k1][$k2];
                                                            }
                                                            
                                                            $rate = array(
                                                                'id'        => $this->get_rate_id( $k1 . ':' . $k2 ),
                                                                'label'     => $label_rate,
                                                                'cost'      => $harga_rate_final,
                                                                'calc_tax'  => 'per_item'
                                                            );
                                                            $this->add_rate( $rate );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else if($enable_CI!='yes'){
                                    // $rate = array(
                                    //     'id'        => $this->get_rate_id( 'ASIACOMMERCE' . ':' . 'air courier' ),
                                    //     'label'     => 'Free Shipping',
                                    //     'cost'      => 0,
                                    //     'calc_tax'  => 'per_item'
                                    // );
                                    // $this->add_rate( $rate ); 
                                }
                            }
                        }
                    }
                    else{
                        // echo "ali prod = tidak ada";
                        $country_code = wc_get_base_location();
                        $country_code = $country_code['country'];
                        $country_store = WC()->countries->countries["$country_code"];
                        if($country_store){
                            if($country_store==$destination_country){
                                $origin_city = $this->instance_settings['ali_city'];
                                if($origin_city){
                                    $url_endpoint = urlencode($origin_city);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][like]=$url_endpoint");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);         
                                    $id_origin_city = (int)$arr_res['data'][0]['id'];
                                    
                                    $url_endpoint = urlencode($destination_city);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/cities?page[limit]=25&filter[name][is]=$url_endpoint");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, 
                                    array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);     
                                    $id_destination_city = (int)$arr_res['data'][0]['id'];    
                                    
                                    // Close cURL session handle
                          
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[limit]=10&filter[origin_id][in]=$id_origin_city,&filter[destination_id][in]=$id_destination_city&include=courier");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true); 
                                    curl_close($ch);
                    
                                    $berat1 = 0;
                                    foreach ($package['contents'] as $key_content => $value_content) {
                                        $berat1 += (float)$value_content['data']->get_weight()*$value_content['quantity'];
                                    }
                                    
                                    if($berat1==0){
                                        $berat1=0.1;
                                    }         
                                    
                                    
                                    foreach ($arr_res['data'] as $key_id => $value_data) {
                                        $berat_max = $value_data['attributes']['maximum'];
                                        if($berat1>$berat_max){
                                            // wc_add_notice( 'Shipping not support weight more than 50Kg, Please decrease the quantity or replace item', 'notice' );
                                            continue;
                                        }
                                    
                                        $hitung1 = $value_data['attributes']['rate_per_weight'];
                                        $hasilbagi1 = (int)($berat1 /$hitung1);
                                        $selisih=(float)$berat1-$hasilbagi1;
                                        
                                        if($selisih<=0.3){
                                            if($hasilbagi1==0){
                                                $hasilbagi1=1;
                                            }
                                            $totalbayar = $value_data['attributes']['rate']*($hasilbagi1);
                                        }
                                        else{
                                            $totalbayar = $value_data['attributes']['rate']*($hasilbagi1+1);
                                        }
                                        
                                        $kurir_id = $value_data['attributes']['courier_id'];
                                        
                                        foreach ($arr_res['included']['courier'] as $id_kur => $isi_data) {
                                            if($kurir_id==$id_kur){
                                                $courier_name = $isi_data['attributes']['name'];
                                            }
                                        }
                                    
                                        if ( metadata_exists( 'post', $id_prod, 'supplier_name' ) ) {
                                            $wh_meta = get_post_meta($id_prod,'supplier_name',true);
                                        }else{
                                            $wh_meta = $infoname;
                                        }
                                        
                                        if ( metadata_exists( 'post', $id_prod, 'warehouse_name' ) ) {
                                            $wh_meta = get_post_meta($id_prod,'warehouse_name',true);
                                        }
                                        
                                        $wh_name = "WH ".$wh_meta;
                                        $tipe_layanan = $value_data['attributes']['name'];
                                        $etd = $value_data['attributes']['remarks'];
                                        $label = $wh_name." : ".$courier_name." - ".$tipe_layanan." ( ".$etd." ) ";
                                    
                                        // echo $hasilbagi1." ".$totalbayar."<br>";
                                        
                                        $rate = array(
                                              'id'        => $this->get_rate_id( $courier_name . ':' . $tipe_layanan ),
                                              'label'     => $label,
                                              'cost'      => $totalbayar,
                                              'calc_tax'  => 'per_item'
                                        );
                                        $this->add_rate( $rate );
                                    }
                                }
                            }
                            else{
                                $enable_CI = $this->instance_settings['calculate_inter'];
                                // var_dump($enable_CI);
                                $debug = 0;
                                if($enable_CI=='yes'){
                                    if($debug==1){
                                        echo $enable_CI."<br>";
                                    }
                                    $ori_country_prod = urlencode($country_store);
                                    $ch = curl_init("https://api2.asiacommerce.net/api/v2/country?page[num]=1&page[limit]=25&sort=name&filter[name][like]=$ori_country_prod");
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                        'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                        'Content-Type: application/json')
                                    );
                                    $result = curl_exec($ch);
                                    $arr_res = json_decode($result,true);      
                                    curl_close($ch);
                            
                                    if($arr_res['success']==1){
                                        if($debug==1){
                                            echo $arr_res['data'][0]['id']."<br>";
                                        }
                                        $origin_country_id = $arr_res['data'][0]['id'];
                                        $destination_country = urlencode($destination_country);
                                        $ch = curl_init("https://api2.asiacommerce.net/api/v2/country?page[num]=1&page[limit]=25&sort=name&filter[name][like]=$destination_country");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                            'Content-Type: application/json')
                                        );
                                        $result = curl_exec($ch);
                                        $arr_res = json_decode($result,true);      
                                        curl_close($ch);
                                
                                        if($arr_res['success']==1){
                                            if($debug==1){
                                                echo $arr_res['data'][0]['id']."<br>";
                                            }
                                            $dest_country_id = $arr_res['data'][0]['id'];
                                            
                                            $ch = curl_init("https://api2.asiacommerce.net/api/v2/country-hub/$origin_country_id/to/$dest_country_id");
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                'Content-Type: application/json')
                                            );
                                            $result = curl_exec($ch);
                                            $arr_res = json_decode($result,true);      
                                            curl_close($ch);   
                                    
                                            if($debug==1){
                                                // var_dump($arr_res);
                                                // echo $arr_res['data'][0]['id']."<br>";
                                            }
                                    
                                            if($arr_res['default_shipment_method']=='air_courier'){
                                        
                                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/logistic-rates?page[num]=1&page[limit]=100&filter[origin_country_id][is]=$origin_country_id&filter[destination_country_id][is]=$dest_country_id&sort=-rate_per_weight&diverse=International&include=country,courier,destinationCountry");
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                    'Content-Type: application/json')
                                                );
                                                $result = curl_exec($ch);
                                                curl_close($ch);
                                                
                                                $berat_barang = 0;
                                                foreach ($package['contents'] as $key_content => $value_content) {
                                                    $berat_barang += (float)$value_content['data']->get_weight()*$value_content['quantity'];
                                                }
                                            
                                                if($berat_barang==0){
                                                    $berat_barang=0.1;
                                                }
                                                
                                                $arr_res = json_decode($result,true);
                                                $temp = array();
                                                $harga_rate = array();
                                                $currency_rate = array();
                                                $remarks = array();
                                                // $selisih_now = array();
                                                $selisih_min = array();
                                                $cour_id_now = '';
                                                $serv_nm_now = '';
                                                if($arr_res['success']==1){
                                                    foreach($arr_res['data'] as $number => $data){
                                                        $courier_id = $data['attributes']['courier_id'];
                                                        $service_name = $data['attributes']['name'];
                                                        
                                                        if($cour_id_now!=$courier_id){
                                                            $cour_id_now = $courier_id;
                                                        }
                                                        if($serv_nm_now!=$service_name){
                                                            $serv_nm_now = $service_name;
                                                        }
                                                        if($selisih_min[$cour_id_now][$serv_nm_now]==NULL){
                                                            $selisih_min[$cour_id_now][$serv_nm_now] = 100;
                                                        }
                                                        
                                                        $selisih_now = abs($berat_barang-$data['attributes']['rate_per_weight']);
                                                        
                                                        if($selisih_now<$selisih_min[$cour_id_now][$serv_nm_now]){
                                                            $selisih_min[$cour_id_now][$serv_nm_now] = $selisih_now;
                                                            $harga_rate[$cour_id_now][$serv_nm_now] = $data['attributes']['rate'];
                                                            $currency_rate[$cour_id_now][$serv_nm_now] = $data['attributes']['currency'];
                                                            $remarks[$cour_id_now][$serv_nm_now] = $data['attributes']['remarks'];
                                                        }
                                                        // echo $number. "\t". $data['attributes']['rate_per_weight']. "\t". $selisih_now. "\t"."selisih minimum = $selisih_min"."\t"."rate now = $harga_rate"."\n";
                                                    }
                                                    // echo $arr_res['included']['courier'][$courier_id]['attributes']['name']." ".$service_name." (".$remarks.") "." $harga_rate"."\n";
                                                }
                                                
                                                $market_currency = get_woocommerce_currency();
                                                
                                                if($debug==1){
                                                    var_dump($harga_rate);
                                                }
                                                
                                                foreach($harga_rate as $k1 => $v1){
                                                    foreach($v1 as $k2 => $v2){
                                                        if($selisih_min[$k1][$k2]<=1){
                                                            $label_rate = $arr_res['included']['courier'][$k1]['attributes']['name']." ".$k2." "." (".$remarks[$k1][$k2]." )";
                                                            if($currency_rate[$k1][$k2]!=$market_currency){
                                                                if($debug==1){
                                                                    // echo "sjy $k1\n";
                                                                    // echo "ajy $k2\n";
                                                                }
                                                                $currency_ret = $currency_rate[$k1][$k2];
                                                                $ch = curl_init("https://api2.asiacommerce.net/api/v2/currencies?page[num]=1&page[limit]=10&sort=-id&filter[currency_code][like]=$currency_ret&filter[currency_code_target][like]=$market_currency");
                                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                                                    'Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjRjNzMxNmE4OGZhYzBmNjE1ZTBlOWQ5ZTZlYjc4YzY4OTBmNDRjZjJiNGY2MjIzNzE4YjI5ODVlMzNlNmU2ZDU0M2VkOTRiNGNhYjBhOTEzIn0.eyJhdWQiOiIyIiwianRpIjoiNGM3MzE2YTg4ZmFjMGY2MTVlMGU5ZDllNmViNzhjNjg5MGY0NGNmMmI0ZjYyMjM3MThiMjk4NWUzM2U2ZTZkNTQzZWQ5NGI0Y2FiMGE5MTMiLCJpYXQiOjE2MTQzMzA3MzYsIm5iZiI6MTYxNDMzMDczNiwiZXhwIjoxNjQ1ODY2NzM2LCJzdWIiOiIyNzciLCJzY29wZXMiOltdfQ.A9PxemJX-k3fWDko1o4yHiAQLt3X9OOrVhTDU8CTAd9LdwBUo9CJVaSvB_x5qnXtUZ_QyByUpz2Qq6HwL4io9uzijXIzPOLOdJHXl9i42gB1k1mKql7gktBoqXH6Zd6i_OirSfBKFOIe8rmxOgdXGNdOEgDcYMKGFjG5PoSmKPbpS1JPJyqbKF1zAc4KNAb5TwmoqfYxbj-HR0IEN_MP2rRMooYfKJvMie6egtxbw2hLvXJC1hDfNdAvHjgqW9te1BAhblDQkZ2H4hz9JuVI_KK-hMAxhQiFhdrMl3tjqEL2P6h3hm1G2JVvJBHR6wmosigH6GjhhNraEwNME9DYo5R0v-EtAqNdFO4NOJg3oLqOEgaeX36C9otoQQhjzxvzAw9YdxcaRBEMU5O6I1iLbxoB3sf_T6J-VvldPzDMB1F62VavSz7Ml-hkNXB6L3At5TBlmxUjS_KdqYxJAEsGFLwvbcZzMncFmjkSGyBfNLBrWm57pVmhxzXXAEx4YLafTILwFAzsgplHZ8n7ZbZ8PRoKAPA61_RXuB2gzKaFRGEwA0ir2UsbSkB2RaLXaRSss6teWmkPNd6CO4RrkJma6-ZcpiDuhrFq34EwXsC8zU_NxVxPYnivFHIODruYefR_oXq54Y8oSXv1sMNwoiraZwn6nw63i_zZgQwpBP0YUOU',
                                                                    'Content-Type: application/json')
                                                                );
                                                                $result = curl_exec($ch);
                                                                curl_close($ch);
                                                                $arr_res = json_decode($result,true);
                                                                
                                                                if($debug==1){
                                                                    var_dump($harga_rate[$k1][$k2]);
                                                                    var_dump($arr_res['data'][0]['attributes']['value']);
                                                                }
                                                                
                                                                if($arr_res['success']==1){
                                                                    $harga_rate_final = $harga_rate[$k1][$k2] * $arr_res['data'][0]['attributes']['value'];
                                                                }
                                                            }
                                                            else{
                                                                $harga_rate_final = $harga_rate[$k1][$k2];
                                                            }
                                                            
                                                            $rate = array(
                                                                'id'        => $this->get_rate_id( $k1 . ':' . $k2 ),
                                                                'label'     => $label_rate,
                                                                'cost'      => $harga_rate_final,
                                                                'calc_tax'  => 'per_item'
                                                            );
                                                        
                                                            $this->add_rate( $rate );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                else if($enable_CI!='yes'){
                                    // $rate = array(
                                    //     'id'        => $this->get_rate_id( 'ASIACOMMERCE' . ':' . 'air courier' ),
                                    //     'label'     => 'Free Shipping',
                                    //     'cost'      => 0,
                                    //     'calc_tax'  => 'per_item'
                                    // );
                                
                                    // $this->add_rate( $rate ); 
                                }
                            }
                        }
                    }
                }     
            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'your_shipping_method_init2' );
    
    add_filter( 'woocommerce_shipping_methods', 'register_tyche_method' );
    
    function register_tyche_method( $methods ) {
    
        // $method contains available shipping methods
        $methods[ 'AsiaCom_Shipping' ] = 'WC_Shipping_AsiaCom';
    
        return $methods;
    }
    
    add_action( 'admin_enqueue_scripts', 'select2onadmin2' );
        function select2onadmin2( $hook ) {
        wp_enqueue_script('select2-js2', plugins_url().'/woocommerce/assets/js/select2/select2.js');
    }
    
    add_action( 'admin_enqueue_scripts', 'ali_shipping_script2' );
        function ali_shipping_script2(){
        wp_enqueue_script( 
            'ali-custom-script2', 
            plugin_dir_url(__FILE__). '/js/ali-shipping-v2modal.js',
            array( 'jquery')
        );
    }
}

if (is_multisite()) {
    $activePlugins = get_site_option('active_sitewide_plugins', array());
    $activePlugins = array_keys($activePlugins);
} else {
    $activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
}

/**
 * Check if WooCommerce is active.
 */
if (in_array('woocommerce/woocommerce.php', $activePlugins)) {

    if (!class_exists('Academe_Multiple_Packages')) {
        // Include the main class.
        // We will keep classes each defined in their own files.
        include_once(dirname(__FILE__) . '/classes/Academe_Multiple_Packages.php');
    }

    // Add the filter to generate the packages.
    // At the moment, this plugin will discard any packages already created and then
    // generate its own from scratch. A future enhancement would see this plugin
    // taking the existing packages, and perhaps splitting them down further if
    // necessary, then adding the linking meta fields to the result.
    add_filter(
        'woocommerce_cart_shipping_packages',
        array(Academe_Multiple_Packages::get_instance(), 'generate_packages')
    );

    // This action allows plugins to add order item meta to shipping.
    add_action(
        'woocommerce_add_shipping_order_item',
        array(Academe_Multiple_Packages::get_instance(), 'link_shipping_line_item'),
        10, 3
    );

    // Add shipping line meta fields to the shipping line in the order API.
    add_filter(
        'woocommerce_api_order_response',
        array(Academe_Multiple_Packages::get_instance(), 'api_show_shipping_line_meta'),
        10, 4
    );

    /**
     * The settings are needed only when in the admin area.
     */
    if (is_admin()) {
        /**
         * Define the shipping method.
         */
        function academe_wc_multiple_packages_init()
        {
            if (!class_exists('Academe_Multiple_Packages_Settings')) {
                // Include the settings and create a new instance.
                require_once(dirname(__FILE__) . '/classes/Academe_Multiple_Packages_Settings.php');
            }
        }
        add_action('woocommerce_shipping_init', 'academe_wc_multiple_packages_init');

        /**
         * Add the shipping method to the WC list of methods.
         * It is not strictly a shipping method itself, but a tool for grouping other
         * shipping methods.
         */
        function academe_add_wc_multiple_packages($methods)
        {
            $methods[] = 'Academe_Multiple_Packages_Settings';
            return $methods;
        }
        add_filter('woocommerce_shipping_methods', 'academe_add_wc_multiple_packages');
    }
}