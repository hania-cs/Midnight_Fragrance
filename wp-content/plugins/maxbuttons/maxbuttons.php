<?php
/*
Plugin Name: MaxButtons
Plugin URI: http://maxbuttons.com
Description: The best WordPress button generator. This is the free version; the Pro version <a href="http://maxbuttons.com/?ref=mbfree">can be found here</a>.
Version: 9.7.8
Author: Max Foundry
Author URI: http://maxfoundry.com
Text Domain: maxbuttons
Domain Path: /languages

Copyright 2023 Max Foundry, LLC (http://maxfoundry.com)
*/
namespace MaxButtons;

if (! defined('MAXBUTTONS_ROOT_FILE'))
	define("MAXBUTTONS_ROOT_FILE", __FILE__);
if (! defined('MAXBUTTONS_VERSION_NUM'))
	define('MAXBUTTONS_VERSION_NUM', '9.7.8');

define('MAXBUTTONS_RELEASE',"8 April 2023");

if (! function_exists('MaxButtons\maxbutton_double_load'))
{
	function maxbutton_double_load()
	{
		$message =  __("Already found an instance of MaxButtons running. Please check if you are trying to activate two MaxButtons plugins and deactivate one. ","maxbuttons" );
		echo "<div class='error'><h4>$message</h4></div>";
		return;
	}
}

if (function_exists("MaxButtons\MB"))
{
	add_action('admin_notices', 'MaxButtons\maxbutton_double_load');
	return;
}

if (! function_exists('MaxButtons\maxbuttons_php56_nono'))
{
	function maxbuttons_php56_nono()
	{
		$message = sprintf( __("MaxButtons requires at least PHP 7 . You are running version: %s ","maxbuttons"), PHP_VERSION);
		echo"<div class='error'> <h4>$message</h4></div>";
		return;
	}
}
if ( version_compare(PHP_VERSION, '7', '<' ) ) {

	add_action( 'admin_notices', 'MaxButtons\maxbuttons_php56_nono' );
	return;
}

// In case of development, copy this to wp-config.php
// define("MAXBUTTONS_DEBUG", true);
// define("MAXBUTTONS_BENCHMARK",true);

require_once( trailingslashit(dirname(MAXBUTTONS_ROOT_FILE)) . "classes/maxbuttons-class.php");

// runtime.
if (! function_exists("MaxButtons\MB"))	{
	function MB()
	{

		return maxButtonsPlugin::getInstance();
	}
}
$maxbuttons = new maxButtonsPlugin();


do_action('maxbuttons/plugin/init' , $maxbuttons);

// Activation / deactivation
register_activation_hook(__FILE__, array(maxUtils::namespaceit("maxInstall"),'activation_hook') );
register_deactivation_hook(__FILE__,array(maxUtils::namespaceit("maxInstall"), 'deactivation_hook') );
