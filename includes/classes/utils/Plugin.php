<?php
if (!defined('ABSPATH')) exit;

define('vnad_PLUGINS_NO_PLUGINS', 10000);
define('vnad_PLUGINS_WOOCOMMERCE', 10001);
define('vnad_PLUGINS_EDD', 10002);
define('vnad_PLUGINS_WP_ECOMMERCE', 10003);
define('vnad_PLUGINS_WP_SPSC', 10004);
define('vnad_PLUGINS_S2MEMBER', 10005);
define('vnad_PLUGINS_MEMBERS', 10006);
define('vnad_PLUGINS_CART66', 10007);
define('vnad_PLUGINS_ESHOP', 10008);
define('vnad_PLUGINS_JIGOSHOP', 10009);
define('vnad_PLUGINS_MARKETPRESS', 10010);
define('vnad_PLUGINS_SHOPP', 10011);
define('vnad_PLUGINS_SIMPLE_WP_ECOMMERCE', 10012);
define('vnad_PLUGINS_CF7', 10013);
define('vnad_PLUGINS_GRAVITY', 10014);
define('vnad_PLUGINS_TRACKING_CODE_MANAGER', 10015);
define('vnad_PLUGINS_TRACKING_CODE_MANAGER_PRO', 10016);

class vnad_Plugin {
    function __construct() {
    }

    function getName($pluginId) {
        $result='';
        switch ($pluginId) {
            case vnad_PLUGINS_WOOCOMMERCE:
                $result='WooCommerce';
                break;
            case vnad_PLUGINS_EDD:
                $result='Easy Digital Download';
                break;
            case vnad_PLUGINS_WP_ECOMMERCE:
                $result='WP eCommerce';
                break;
            case vnad_PLUGINS_WP_SPSC:
                $result='WordPress Simple Paypal Shopping Cart';
                break;
            case vnad_PLUGINS_S2MEMBER:
                $result='s2member';
                break;
            case vnad_PLUGINS_MEMBERS:
                $result='Members';
                break;
            case vnad_PLUGINS_CART66:
                $result='Cart66 Lite :: WordPress Ecommerce';
                break;
            case vnad_PLUGINS_ESHOP:
                $result='eShop';
                break;
            case vnad_PLUGINS_JIGOSHOP:
                $result='Jigoshop';
                break;
            case vnad_PLUGINS_MARKETPRESS:
                $result='MarketPress - WordPress eCommerce';
                break;
            case vnad_PLUGINS_SHOPP:
                $result='Shopp';
                break;
            case vnad_PLUGINS_SIMPLE_WP_ECOMMERCE:
                $result='iThemes Exchange: Simple WP Ecommerce';
                break;
            case vnad_PLUGINS_CF7:
                $result='Contact Form 7';
                break;
            case vnad_PLUGINS_GRAVITY:
                $result='Gravity Form';
                break;
            case vnad_PLUGINS_TRACKING_CODE_MANAGER:
                $result='vNative Advertiser';
                break;
            case vnad_PLUGINS_TRACKING_CODE_MANAGER_PRO:
                $result='vNative Advertiser PRO';
                break;
        }
        return $result;
    }
    function isActive($pluginId) {
        global $vnad;

        $php='';
        $class='';
        $constant='';
        switch ($pluginId) {
            case vnad_PLUGINS_WOOCOMMERCE:
                $php='woocommerce/woocommerce.php';
                $class='WooCommerce';
                $constant='WOOCOMMERCE_VERSION';
                break;
            case vnad_PLUGINS_EDD:
                $php='easy-digital-downloads/easy-digital-downloads.php';
                $class='Easy_Digital_Downloads';
                $constant='EDD_SL_VERSION';
                break;
            case vnad_PLUGINS_WP_ECOMMERCE:
                $class='WP_eCommerce';
                $constant='WPSC_VERSION';
                break;
            case vnad_PLUGINS_WP_SPSC:
                $constant='WP_CART_VERSION';
                break;
            case vnad_PLUGINS_S2MEMBER:
                $constant='WS_PLUGIN__S2MEMBER_VERSION';
                break;
            case vnad_PLUGINS_MEMBERS:
                $constant='MEMBERS_VERSION';
                break;
            case vnad_PLUGINS_CART66:
                $class='Cart66';
                $constant='CART66_VERSION_NUMBER';
                break;
            case vnad_PLUGINS_ESHOP:
                $constant='ESHOP_VERSION';
                break;
            case vnad_PLUGINS_JIGOSHOP:
                $constant='JIGOSHOP_VERSION';
                break;
            case vnad_PLUGINS_MARKETPRESS:
                $class='MarketPress';
                $constant='MP_LITE';
                break;
            case vnad_PLUGINS_SHOPP:
                $constant='ESHOP_VERSION';
                break;
            case vnad_PLUGINS_SIMPLE_WP_ECOMMERCE:
                $class='IT_Exchange';
                $constant='';
                break;
            case vnad_PLUGINS_CF7:
                $constant='WPCF7_VERSION';
                break;
            case vnad_PLUGINS_GRAVITY:
                $constant='';
                break;
            case vnad_PLUGINS_TRACKING_CODE_MANAGER:
                $constant='vnad_PLUGIN_VERSION';
                break;
            case vnad_PLUGINS_TRACKING_CODE_MANAGER_PRO:
                $constant='vnad_PLUGIN_VERSION';
                break;
        }
        $result=$this->isPluginActive($class, $constant, $php);
        return $result;
    }

    private function isPluginActive($class='', $constant='', $plugin='') {
        $result=FALSE;
        $result=($result || ($class!='' && class_exists($class)));
        $result=($result || ($constant!='' && defined($constant)));
        //require plugin.php
        //$result=($result || ($plugin!='' && is_plugin_active($plugin)));
        return $result;
    }

    function getVersion($pluginId) {
        $constant='';
        $version='';
        switch ($pluginId) {
            case vnad_PLUGINS_WOOCOMMERCE:
                $constant='WOOCOMMERCE_VERSION';
                break;
            case vnad_PLUGINS_EDD:
                $constant='EDD_SL_VERSION';
                break;
            case vnad_PLUGINS_WP_ECOMMERCE:
                $constant='WPSC_VERSION';
                break;
            case vnad_PLUGINS_WP_SPSC:
                $constant='WP_CART_VERSION';
                break;
            case vnad_PLUGINS_S2MEMBER:
                $constant='WS_PLUGIN__S2MEMBER_VERSION';
                break;
            case vnad_PLUGINS_MEMBERS:
                $constant='MEMBERS_VERSION';
                break;
            case vnad_PLUGINS_CART66:
                $constant='CART66_VERSION_NUMBER';
                break;
            case vnad_PLUGINS_ESHOP:
                $constant='ESHOP_VERSION';
                break;
            case vnad_PLUGINS_JIGOSHOP:
                $constant='JIGOSHOP_VERSION';
                break;
            case vnad_PLUGINS_MARKETPRESS:
                global $mp;
                $version=$mp->version;
                break;
            case vnad_PLUGINS_SHOPP:
                $constant='ESHOP_VERSION';
                break;
            case vnad_PLUGINS_SIMPLE_WP_ECOMMERCE:
                $version=$GLOBALS['it_exchange']['version'];
                break;
            case vnad_PLUGINS_CF7:
                $constant='WPCF7_VERSION';
                break;
            case vnad_PLUGINS_GRAVITY:
                $constant='';
                break;
        }
        if($version=='' && $constant!='') {
            $version=(defined($constant) ? constant($constant) : '');
        }
        return $version;
    }

    function getActivePlugins($ids) {
        return $this->getActivePlugins($ids, TRUE);
    }
    function getPlugins($ids, $onlyActive=TRUE) {
        $array=array();
        if(!is_array($ids)) {
            $ids=array(intval($ids));
        }

        foreach($ids as $id) {
            if(!$onlyActive || $this->isActive($id)) {
                $name=$this->getName($id);
                $version=$this->getVersion($id);

                $v=array(
                    'id'=>$id
                    , 'name'=>$name
                    , 'version'=>$version
                    , 'active'=>$this->isActive($id)
                );
                $array[$name]=$v;
            }
        }
        ksort($array);
        return $array;
    }
}
