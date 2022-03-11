<?php

class ALI_Connect_Checker
{
    public function init()
    {
    }

    public function isALIProduct($id)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'ali_connect_products';

        $integration = $wpdb->get_row("SELECT * FROM {$tableName} WHERE `product_id` = {$id}");

        if (null !== $integration) {
            return true;
        }

        return get_post_meta($id, 'ali__is_ali_product', true);
    }

    public function cartHasALIProduct()
    {
        $kota_code = wc_get_base_location();
        $kota_code = $kota_code['country'];
        $kota_toko = WC()->countries->countries["$kota_code"];

        foreach (WC()->cart->get_cart() as $items) {
            if (!empty($items)) {
                $kota_product = get_post_meta($items['product_id'],'country',true); 
                if (($this->isALIProduct($items['product_id'])) && ($kota_toko!=$kota_product)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function orderHasALIProduct($orderId)
    {
        $kota_code = wc_get_base_location();
        $kota_code = $kota_code['country'];
        $kota_toko = WC()->countries->countries["$kota_code"];
        
        foreach (wc_get_order($orderId)->get_items() as $product) {
            $id_prod = $product->get_product_id();
            $kota_product = get_post_meta($id_prod,'country',true); 
            if ($this->isALIProduct($product->get_product_id()) && ($kota_toko!=$kota_product)) {
                return true;
            }
        }

        return false;
    }
}
