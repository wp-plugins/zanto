<?php
/*
Plugin Name: Zanto WordPress Transaltion
Plugin URI: http://zanto.org/
Description: This plugin helps you Translate all Wordpress the proper way. 
Version: 0.2.1
Author: Zanto Translate
Author URI: http://zanto.org
Text Domain: Zanto
Domain Path: /languages/
*/ 

if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );
define('GTP_ZANTO_VERSION', '0.2.1');
define('GTP_NAME',	'Zanto Wordpress Translaton Plugin' );
define('GTP_REQUIRED_WP_VERSION', '3.1' );	// because of esc_textarea()
define('GTP_PLUGIN_PATH', dirname(__FILE__));
define('GTP_PLUGIN_FOLDER', basename(GTP_PLUGIN_PATH));
define('GTP_PLUGIN_URL', plugin_dir_url(__FILE__) );
(!function_exists('is_multisite') || !is_multisite() )?define('GTP_MULTISITE', false): define('GTP_MULTISITE', true);
if(!defined('GTP_lS_THEME_PATH')) {
   define('GTP_lS_THEME_PATH', get_template_directory().'/zanto' );
}

/**
 * Checks if the system requirements are met
 * @author Zanto Translate
 * @return array 0 to indicate un-met conditions.
 */
 $zwt_icon_url= GTP_PLUGIN_URL . 'images/logo-admin.gif';
 $zwt_menu_url= GTP_PLUGIN_URL . 'images/menu-icon.png';
 require_once( GTP_PLUGIN_PATH . '/includes/install-requirements.php' );
 $zwt_unfullfilled_requirments = zwt_requirements_missing();
 

// Check if requirements are missing and load main class
// The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
   
	
if( !$zwt_unfullfilled_requirments)
{
	require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-module.php' );
	require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-base.php' );
	require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-lang-switcher.php' );
    require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-widgets.php');
	require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-mo.php');
	require_once( GTP_PLUGIN_PATH . '/classes/class.zwt-download-mo.php');
	require_once( GTP_PLUGIN_PATH . '/includes/functions.php');
	require_once( GTP_PLUGIN_PATH . '/includes/template-functions.php');
	
	
	if( class_exists( 'ZWT_Base' ) )
	{
		 $zwt_site_obj = ZWT_Base::getInstance();
		 $zwt_language_switcher = new ZWT_Lang_Switcher();

		register_activation_hook( __FILE__,		array( $zwt_site_obj, 'activate' ) );  
		register_deactivation_hook( __FILE__,	array( $zwt_site_obj, 'deactivate' ) );
	}
}
else{
	add_action( 'admin_notices', 'zwt_requirements_error' );
	zwt_deactivate_zanto();
	}
	
/**
 * Prints an error and de-activates Zanto when the system requirements aren't met.
 * @author Zanto Translate
 */
	function zwt_requirements_error(){
	global $wp_version;
	require_once( GTP_PLUGIN_PATH . '/views/requirements-error.php' );
}

?>