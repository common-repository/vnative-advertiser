<?php
/*
Plugin Name: vNative Advertiser
Plugin URI: http://vnative.com/
Description: A plugin to manage ALL your cost per accusition, cost per lead, cost per sale campaigns tracking pixel and conversion pixels. Compatible with Facebook Ads, Google Adwords, WooCommerce, Easy Digital Downloads, WP eCommerce.
Author: vNative
Author URI: http://vnative.com
Version: 1.0
*/
if(defined('vnad_PLUGIN_NAME')) {
    function vnad_admin_notices() {
        global $vnad; ?>
        <div style="clear:both"></div>
        <div class="error iwp" style="padding:10px;">
            <?php $vnad->Lang->P('PluginProAlreadyInstalled'); ?>
        </div>
        <div style="clear:both"></div>
    <?php }
    add_action('admin_notices', 'vnad_admin_notices');
    return;
}
define('vnad_PLUGIN_PREFIX', 'vnad_');
define('vnad_PLUGIN_FILE',__FILE__);
define('vnad_PLUGIN_SLUG', 'vnative-advertiser');
define('vnad_PLUGIN_NAME', 'vNative Advertiser');
define('vnad_PLUGIN_VERSION', '1.11.1');
define('vnad_PLUGIN_AUTHOR', 'vnative');

define('vnad_PLUGIN_DIR', dirname(__FILE__).'/');
define('vnad_PLUGIN_ASSETS_URI', plugins_url( 'assets/', __FILE__ ));
define('vnad_PLUGIN_IMAGES_URI', plugins_url( 'assets/images/', __FILE__ ));

define('vnad_LOGGER', FALSE);
define('vnad_AUTOSAVE_LANG', FALSE);

define('vnad_QUERY_POSTS_OF_TYPE', 1);
define('vnad_QUERY_POST_TYPES', 2);
define('vnad_QUERY_CATEGORIES', 3);
define('vnad_QUERY_TAGS', 4);
define('vnad_QUERY_CONVERSION_PLUGINS', 5);
define('vnad_QUERY_TAXONOMY_TYPES', 6);
define('vnad_QUERY_TAXONOMIES_OF_TYPE', 7);

define('vnad_vnative_SITE', 'http://www.vnative.com/');
define('vnad_vnative_ENDPOINT', vnad_vnative_SITE.'wp-content/plugins/vnative-manager/data.php');
define('vnad_PAGE_FAQ', vnad_vnative_SITE.'vnative-advertiser');
define('vnad_PAGE_PREMIUM', vnad_vnative_SITE.'vnative-advertiser');
define('vnad_PAGE_MANAGER', admin_url().'options-general.php?page='.vnad_PLUGIN_SLUG);
define('vnad_PLUGIN_URI', plugins_url('/', __FILE__ ));

define('vnad_POSITION_HEAD', 0);
define('vnad_POSITION_BODY', 1);
define('vnad_POSITION_FOOTER', 2);
define('vnad_POSITION_CONVERSION', 3);

define('vnad_TRACK_MODE_CODE', 0);
define('vnad_TRACK_PAGE_ALL', 0);
define('vnad_TRACK_PAGE_SPECIFIC', 1);

define('vnad_DEVICE_TYPE_MOBILE', 'mobile');
define('vnad_DEVICE_TYPE_TABLET', 'tablet');
define('vnad_DEVICE_TYPE_DESKTOP', 'desktop');
define('vnad_DEVICE_TYPE_ALL', 'all');

define('vnad_TAB_EDITOR', 'editor');
define('vnad_TAB_EDITOR_URI', vnad_PAGE_MANAGER.'&tab='.vnad_TAB_EDITOR);
define('vnad_TAB_MANAGER', 'manager');
define('vnad_TAB_MANAGER_URI', vnad_PAGE_MANAGER.'&tab='.vnad_TAB_MANAGER);
define('vnad_TAB_SETTINGS', 'settings');
define('vnad_TAB_SETTINGS_URI', vnad_PAGE_MANAGER.'&tab='.vnad_TAB_SETTINGS);
define('vnad_TAB_DOCS', 'docs');
define('vnad_TAB_DOCS_URI', 'http://vnative.com/support');
define('vnad_TAB_DOCS_DCV_URI', 'http://vnative.com/support');
define('vnad_TAB_ABOUT', 'about');
define('vnad_TAB_ABOUT_URI', vnad_PAGE_MANAGER.'&tab='.vnad_TAB_ABOUT);
define('vnad_TAB_WHATS_NEW', 'whatsnew');
define('vnad_TAB_WHATS_NEW_URI', vnad_PAGE_MANAGER.'&tab='.vnad_TAB_WHATS_NEW);
include_once(dirname(__FILE__).'/autoload.php');
vnad_include_php(dirname(__FILE__).'/includes/');

global $vnad;
$vnad=new vnad_Singleton();
$vnad->init();

