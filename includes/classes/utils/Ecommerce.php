<?php
if (!defined('ABSPATH')) exit;

class vnad_Ecommerce {
    var $_orderId;

    function __construct() {
        add_action('woocommerce_thankyou', array(&$this, 'wooCommerceThankYou'), -10);
        //add_action('woocommerce_thankyou_order_id', array(&$this, 'wooCommerceThankYou'), -10);

        add_action('edd_payment_receipt_after_table', array(&$this, 'eddThankYou'));
        add_action('wpsc_transaction_result_cart_item', array(&$this, 'eCommerceThankYou'));
    }

    public function getCustomPostType($pluginId) {
        $result='';
        switch (intval($pluginId)) {
            case vnad_PLUGINS_WOOCOMMERCE:
                $result='product';
                break;
            case vnad_PLUGINS_EDD:
                $result='download';
                break;
            case vnad_PLUGINS_WP_ECOMMERCE:
                $result='wpsc-product';
                break;
        }
        return $result;
    }

    //WPSC_Purchase_Log_Customer_HTML_Notification
    function eCommerceThankYou($order) {
        global $vnad;
        $purchase=new vnad_EcommercePurchase();

        $orderId=intval($order['purchase_id']);
        $purchase->orderId=$orderId;
        $vnad->Log->debug('Ecommerce: ECOMMERCE THANKYOU');
        $vnad->Log->debug('Ecommerce: NEW ECOMMERCE ORDERID=%s', $orderId);

        $order=new WPSC_Purchase_Log($orderId);
        $items=$order->get_cart_contents();
        $productsIds=array();
        foreach ($items as $v) {
            if(isset($v->prodid)) {
                $k=intval($v->prodid);
                if($k) {
                    $v=$v->name;
                    $purchase->products[]=$v;
                    $productsIds[]=$k;
                    $vnad->Log->debug('Ecommerce: ITEM %s=%s IN CART', $k, $v);
                }
            }
        }

        $args=array(
            'pluginId'=>vnad_PLUGINS_WP_ECOMMERCE
            , 'productsIds'=>$productsIds
            , 'categoriesIds'=>array()
            , 'tagsIds'=>array()
        );
        $vnad->Options->pushConversionSnippets($args, $purchase);
        return '';
    }

    function eddThankYou($payment, $edd_receipt_args=NULL) {
        global $vnad;
        if(!class_exists('EDD_Customer')) {
            return;
        }

        /* @var $payment WP_Post */
        $purchase=new vnad_EcommercePurchase();
        $purchase->orderId=$vnad->Utils->get($payment, 'ID');
        $purchase->userId=$vnad->Utils->get($payment, 'post_author', FALSE);

        $settings=edd_get_settings();
        if(isset($settings['currency'])) {
            $purchase->currency=$settings['currency'];
        }

        $vnad->Log->debug('Ecommerce: EDD THANKYOU');
        $vnad->Log->debug('Ecommerce: NEW EDD ORDERID=%s', $purchase->orderId);
        $cart=edd_get_payment_meta_cart_details($purchase->orderId, TRUE);
        $productsIds=array();
        $purchase->amount=0;
        $purchase->total=0;
        foreach ($cart as $key=>$item) {
            if(isset($item['id'])) {
                $k=intval($item['id']);
                if($k) {
                    $v=$item['name'];
                    $purchase->products[]=$v;
                    $productsIds[]=$k;
                    $vnad->Log->debug('Ecommerce: ITEM %s=%s IN CART', $k, $v);
                }
            }
        }

        $args=array(
            'pluginId'=>vnad_PLUGINS_EDD
            , 'productsIds'=>$productsIds
            , 'categoriesIds'=>array()
            , 'tagsIds'=>array()
        );
        $vnad->Options->pushConversionSnippets($args, $purchase);
    }
    function wooCommerceThankYou($orderId) {
        global $vnad;
        if(!$orderId) {
            return;
        }
        if($this->_orderId===$orderId) {
            return;
        }

        $this->_orderId=$orderId;
        $purchase=new vnad_EcommercePurchase();
        $purchase->orderId=$orderId;
        $vnad->Log->debug('Ecommerce: WOOCOMMERCE THANKYOU');

        $order=new WC_Order($orderId);
        $purchase->email=$order->billing_email;
        $purchase->fullname=$order->billing_first_name;
        if($order->billing_last_name!='') {
            $purchase->fullname.=' '.$order->billing_last_name;
        }

        $items=$order->get_items();
        $vnad->Log->debug('Ecommerce: NEW WOOCOMMERCE ORDERID=%s', $orderId);
        $productsIds=array();
        foreach($items as $k=>$v) {
            $k=intval($v['product_id']);
            if($k>0) {
                $v=$v['name'];
                $purchase->products[]=$v;
                $vnad->Log->debug('Ecommerce: ITEM %s=%s IN CART', $k, $v);
                $productsIds[]=$k;
            }
        }

        $args=array(
            'pluginId'=>vnad_PLUGINS_WOOCOMMERCE
            , 'productsIds'=>$productsIds
            , 'categoriesIds'=>array()
            , 'tagsIds'=>array()
        );
        $vnad->Options->pushConversionSnippets($args, $purchase);
    }

    function getActivePlugins() {
        return $this->getPlugins(TRUE);
    }
    function getPlugins($onlyActive=TRUE) {
        global $vnad;

        $array=array();
        $array[]=vnad_PLUGINS_WOOCOMMERCE;
        $array[]=vnad_PLUGINS_EDD;
        $array[]=vnad_PLUGINS_WP_ECOMMERCE;
        /*
        $array[]=vnad_PLUGINS_WP_SPSC;
        $array[]=vnad_PLUGINS_S2MEMBER;
        $array[]=vnad_PLUGINS_MEMBERS;
        $array[]=vnad_PLUGINS_CART66;
        $array[]=vnad_PLUGINS_ESHOP;
        $array[]=vnad_PLUGINS_JIGOSHOP;
        $array[]=vnad_PLUGINS_MARKETPRESS;
        $array[]=vnad_PLUGINS_SHOPP;
        $array[]=vnad_PLUGINS_SIMPLE_WP_ECOMMERCE;
        $array[]=vnad_PLUGINS_CF7;
        $array[]=vnad_PLUGINS_GRAVITY;
        */

        $array=$vnad->Plugin->getPlugins($array, $onlyActive);
        return $array;
    }
}
