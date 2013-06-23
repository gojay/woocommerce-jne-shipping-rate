<?php
/*
Plugin Name: WooCommerce JNE Shipping Rate
Plugin URI: http://codex.wordpress.org
Description: Menampilkan daftar ongkos pengiriman JNE
Version: 1.1
Author: Dani Gojay
Author URI: http://gojayincode.com
License: GPL2
*/
global $wp_version;
define( 'JNE_REQUIRED_WP_VERSION', '3.0' );

// define environment 
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));

/**
 * set debug mode
 * wp_debug_mode(true);
 */

/**
 * Check minimum wordpress version required > 3.0
 * if lastest wordpress version < 3.0, send message for updating wordpress version
 */ 
$exit_msg = 'Plugin JNE Shipping Rate requires WordPress '. JNE_REQUIRED_WP_VERSION .' or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
if ( version_compare( $wp_version, JNE_REQUIRED_WP_VERSION, "<" ) ) {
    exit($exit_msg);
}

/**
 *
 * Globals Define
 *
 * JNE_PLUGIN_BASENAME 		= plugin basename 				-> "webroot\wp-content\plugins"
 * JNE_PLUGIN_NAME 			= plugin name 					-> "jne-shipping-rate"
 * JNE_PLUGIN_DIR 			= plugin directory 				-> "webroot\wp-content\plugins\jne-shipping-rate"
 * JNE_PLUGIN_URL 			= plugin URL 					-> "http://yoursite.com/wp-content/plugins/jne-shipping-rate"
 * JNE_PLUGIN_ASSET_URL 	= Asset URL 					-> "http://yoursite.com/wp-content/plugins/jne-shipping-rate/assets"
 * JNE_PLUGIN_ASSET_DIR 	= plugin directory assets  		-> "webroot\wp-content\plugins\jne-shipping-rate\assets"
 * JNE_PLUGIN_DATA_DIR 		= plugin directory data  		-> "webroot\wp-content\plugins\jne-shipping-rate\data"
 * JNE_PLUGIN_INC_DIR 		= plugin directory includes 	-> "webroot\wp-content\plugins\jne-shipping-rate\includes"
 * JNE_PLUGIN_TPL_DIR 		= plugin directory templates  	-> "webroot\wp-content\plugins\jne-shipping-rate\templates"
 * 
 */
if ( ! defined( 'JNE_PLUGIN_BASENAME' ) )
    define( 'JNE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'JNE_PLUGIN_NAME' ) )
    define( 'JNE_PLUGIN_NAME', trim( dirname( JNE_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'JNE_PLUGIN_DIR' ) )
    define( 'JNE_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . JNE_PLUGIN_NAME );

if ( ! defined( 'JNE_PLUGIN_URL' ) )
    define( 'JNE_PLUGIN_URL', WP_PLUGIN_URL . '/' . JNE_PLUGIN_NAME );

if ( ! defined( 'JNE_PLUGIN_ASSET_URL' ) )
    define( 'JNE_PLUGIN_ASSET_URL', JNE_PLUGIN_URL . '/assets' );

if ( ! defined( 'JNE_PLUGIN_ASSET_DIR' ) )
    define( 'JNE_PLUGIN_ASSET_DIR', JNE_PLUGIN_DIR . '/assets' );

if ( ! defined( 'JNE_PLUGIN_DATA_DIR' ) )
    define( 'JNE_PLUGIN_DATA_DIR', JNE_PLUGIN_DIR . '/data' );

if ( ! defined( 'JNE_PLUGIN_INC_DIR' ) )
    define( 'JNE_PLUGIN_INC_DIR', JNE_PLUGIN_DIR . '/includes' );

if ( ! defined( 'JNE_PLUGIN_TPL_DIR' ) )
    define( 'JNE_PLUGIN_TPL_DIR', JNE_PLUGIN_DIR . '/templates' );
	
// WOOCOMMERCE

if ( ! defined( 'JNE_PLUGIN_WOO_DIR' ) )
    define( 'JNE_PLUGIN_WOO_DIR', JNE_PLUGIN_DIR . '/woocommerce' );

if ( ! defined( 'JNE_PLUGIN_WOO_URL' ) )
    define( 'JNE_PLUGIN_WOO_URL', JNE_PLUGIN_URL . '/woocommerce' );
	

include 'jne-shipping-rate-functions.php';
include 'jne-shipping-rate-init.php';
// include 'includes/class-parse-jne.php'; 
include 'includes/class-parse-jne2.php';	

if( class_exists('JNE_Shipping_Rate') )
{
    $JNE = new JNE_Shipping_Rate();
    /**
     * register_activation_hook
     * @param file
     * @param callback
     */
    register_activation_hook( __FILE__, array( &$JNE, 'install' ) );
}
	
/*
 * add woocommerce shipping method
 */
// include 'woocommerce/woocommerce-jne-shipping.php';

// Global variable jne
// $GLOBALS['jne'] = new Parse_JNE( 'Jakarta.xls' );
$Parse_JNE2 = new Parse_JNE2();
$Parse_JNE2->filename = '04-2013';
$Parse_JNE2->start = 5;
$Parse_JNE2->populate();
$GLOBALS['jne'] = $Parse_JNE2;