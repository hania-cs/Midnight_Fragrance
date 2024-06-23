<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

define('MAXBUTTONS_VERSION_KEY', 'maxbuttons_version');

class maxButtonsPlugin
{
	protected $installed_version = 0;
	protected $plugin_name;
	protected $plugin_url;
	protected $plugin_path;
	protected $version;
	protected $js_url;
	protected $debug_mode = false;
	protected $footer = array();

	protected static $notices = array();

	protected $mainClasses = array();

	protected static $instance;

	private $paths = array('classes', 'classes/controllers');
	protected $admin_pages = array(); // our defined menu pages.

	/* Class constructor
	   Add hooks and actions used by this plugin. Sets plugin environment information
	*/
	public function __construct()
	{
		if ( defined('MAXBUTTONS_DEBUG') && MAXBUTTONS_DEBUG)
 			$this->debug_mode = true;

		$this->load(); // loads classes

		maxUtils::timeInit(); // benchmark timer init.
		maxAdmin::init(); // helper class hook init

		$this->plugin_url =  self::get_plugin_url(); //plugins_url() . '/' . $this->plugin_name;
		$this->plugin_path = self::get_plugin_path(); //plugin_dir_path($rootfile);
		$this->plugin_name = trim(basename($this->plugin_path), '/');

		$this->installed_version = get_option(MAXBUTTONS_VERSION_KEY);

		$this->version = MAXBUTTONS_VERSION_NUM;

		$js_url = trailingslashit($this->plugin_url . 'js');

		$this->js_url = $js_url;


		self::$instance = $this;

		$this->hooks();

	 		maxIntegrations::init(); // fire the integrations.

			// Core libraries.
			MB()->load_library('simplehtmldom');
			MB()->load_library('simple_template');
			MB()->load_library('polyfill-ctype'); // when missing ctype module
	}

	public function load()
	{
		$plugin_path = plugin_dir_path(MAXBUTTONS_ROOT_FILE);
		foreach($this->paths as $short_path)
		{
			$directory_path = realpath($plugin_path . $short_path);

			if ($directory_path !== false && is_dir($directory_path))
			{
				try
				{
					$it = new \DirectoryIterator($directory_path);
				}
				catch (\UnexpectedValueException $e)
				{
					echo '<div style="margin-left:170px"><h1>' . __('Fatal Problem with MaxButtons', 'maxbuttons') . '</h1>';
					echo sprintf(__('Can\'t read directory. %s Reinstall the plugin or check directory permissions', 'maxbuttons'), $directory_path);
					echo '</div>';
					return;
				}

				foreach($it as $file)
				{
					$file_path = $file->getRealPath();
					if ($file->isFile() && pathinfo($file_path, PATHINFO_EXTENSION) == 'php')
					{
						require_once($file_path);
					}
				}
			}
		}

	}

	public function hooks()
	{

		add_action('plugins_loaded', array($this, 'load_textdomain'));

		add_filter('widget_text', 'do_shortcode');
		add_shortcode('maxbutton', array($this, 'shortcode'));

		add_action("mb-footer", array($this, 'do_footer'),10,3);
		add_action("wp_footer", array($this, "footer"));

		add_filter('plugin_action_links', array($this, "plugin_action_links"), 10, 2);
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);

		if( is_admin())
		{
			//add_action('admin_enqueue_scripts', array($this,'add_admin_styles'));
			add_action('admin_enqueue_scripts', array($this,'add_admin_scripts'));
			add_action('admin_enqueue_scripts', array(maxUtils::namespaceit('maxUtils'), 'fixFAConflict'),999);

			add_action('admin_init', array($this,'register_settings' ));

			add_action('admin_init', array(maxUtils::namespaceit('maxAdmin'), 'do_review_notice')); // Ask for review
			add_action('admin_init', array(maxUtils::namespaceit('maxInstall'),'check_database'));

			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_footer', array($this, "footer"));
			add_filter("admin_footer_text",array($this, "admin_footer_text"));

			// errors in user space. No internal error but user output friendly issues
			add_action("mb/editor/display_notices", array($this,"display_notices"), 99);
			add_action("mb/collection/display_notices", array($this,"display_notices"), 99);
			add_action('mb/header/display_notices', array($this, 'display_notices'), 99);

			//add_action("wp_ajax_getAjaxButtons", array(maxUtils::namespaceit('maxButtonsAdmin'), 'getAjaxButtons'));
			add_action('maxbuttons/ajax/getAjaxButtons', array(maxUtils::namespaceit('maxButtonsAdmin'), 'getAjaxButtons') );
			add_action('maxbuttons/ajax/mediaShortcodeOptions', array(maxUtils::namespaceit('maxButtonsAdmin'), 'mediaShortcodeOptions'));
			add_action('maxbuttons/ajax/save_review_notice_status', array(maxUtils::namespaceit('maxAdmin'), "setReviewNoticeStatus") );

			//add_action("wp_ajax_set_review_notice_status", array($this, "setReviewNoticeStatus"));
			add_action('wp_ajax_mb_button_action', array(maxUtils::namespaceit('maxButtons'), "ajax_action"));

			add_action('wp_ajax_maxajax', array(maxUtils::namespaceit('maxUtils'), 'ajax_action'));

			add_action('admin_init', array($this,'init_wp_editor_options') );

			// This is used in maxbuttons.js for wp-link-dialog to make it show media files as well. Catches filter hooks.
			if (isset($_REQUEST['ajax_maxbuttons']) && $_REQUEST['ajax_maxbuttons'] == 'editor')
			{
					$controller = new editorController();
			}
		}


		add_action('wp_ajax_maxbuttons_front_css', array(maxUtils::namespaceit('maxButtons'), 'generate_css'));
		add_action('wp_ajax_nopriv_maxbuttons_front_css', array(maxUtils::namespaceit('maxButtons'), 'generate_css'));

		// front scripts
		add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
		//add_action('wp_enqueue_scripts', array(maxUtils::namespaceit('maxUtils'), 'fixFAConflict'),999);

		$this->setMainClasses(); // struct for override functionality

 		// The second the blocks are being loaded, check dbase integrity
 		//add_action("mb_blockclassesloaded", array($this, "check_database"));

 		// setup page hooks and shortcode
		add_shortcode('maxcollection', array($this, 'collection_shortcode'));

	}

	public static function getInstance()
	{
		return self::$instance;
	}

	public function setMainClasses()
	{
		$classes = array(
			"button" => "maxButton",
			"buttons" => "maxButtons",
			"block" => "maxBlock",
			"admin" => "maxButtonsAdmin",
			"install" => "maxInstall",
			"groups" => "maxGroups",
			"pack" => "maxPack",
		);

		$this->mainClasses = $classes;
	}

	// from block loader action. Checks if all parts of the table are there, or panic if not.
	/*public function check_database($blocks)
	{
		maxUtils::addTime("Check database");

		$sql = "SELECT id,name,status,cache, created ";
		foreach ($blocks as $block => $class)
		{
			$sql .= ", $block";
		}
		$sql .= " from " . maxUtils::get_table_name() . " limit 1";

		global $wpdb;
		$wpdb->hide_errors();
		$result = $wpdb->get_results($sql);

		// check this query for errors. If there is an error, one or more database fields are missing. Fix that.
		if (isset($wpdb->last_error) && $wpdb->last_error != '')
		{

		 	$install = $this->getClass("install");
			$install::create_database_table();
			$install::migrate();
		}

		maxUtils::addTime("End check database");
	} */

	public function getClass($class)
	{
		if (isset($this->mainClasses[$class]))
		{
			$load_class = maxUtils::namespaceit($this->mainClasses[$class]);
			if (method_exists($load_class,'getInstance'))
			{
				return $load_class::getInstance();
			}
			return new $load_class;
		}
	}

	/* Load the plugin textdomain */
	public function load_textdomain()
	{
		// see: http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
		$domain = 'maxbuttons';
		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR.'/maxbuttons/'.$domain.'-'.$locale.'.mo');
		$res = load_plugin_textdomain('maxbuttons', false, $this->plugin_name . '/languages/');

 	}


 	/** WP Settings framework. Registers settings used on maxbuttons-settings.php page */
 	public function register_settings()
 	{
 		register_setting( 'maxbuttons_settings', 'maxbuttons_user_level' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_noshowtinymce' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_minify' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_hidedescription' );
 		register_setting( 'maxbuttons_settings', 'maxbuttons_forcefa') ;
 		register_setting( 'maxbuttons_settings', 'maxbuttons_borderbox');
		register_setting( 'maxbuttons_settings', 'maxbuttons_protocol');
		register_setting( 'maxbuttons_settings', 'maxbuttons_autoresponsive');

		register_setting( 'maxbuttons_settings', 'maxbuttons_autor_font');
		register_setting( 'maxbuttons_settings', 'maxbuttons_autor_width');

	}

	protected function checkbox_option($options)
	{
		if (! isset($options["maxbuttons_minify"]))
			$options["maxbuttons_minify"] = 0;

		return $options;

	}


	/** Returns the full path of the plugin installation directory */
 	public static function get_plugin_path()
 	{
 		return plugin_dir_path(MAXBUTTONS_ROOT_FILE);
 	}

 	/** Returns the full URL of the plugin installation path */
 	public static function get_plugin_url()
 	{
 		$url = plugin_dir_url(MAXBUTTONS_ROOT_FILE);
 		return $url;
 	}

 	/** Returns the current installed version */
 	public function get_installed_version()
 	{
 		return $this->installed_version;
 	}

	public function get_user_level()
	{
		$maxbuttons_capabilities = get_option('maxbuttons_user_level');
		if(!$maxbuttons_capabilities) {
			$maxbuttons_capabilities = 'manage_options';
			settings_fields( 'maxbuttons_settings' );
			update_option('maxbuttons_user_level', $maxbuttons_capabilities);
		}

		return $maxbuttons_capabilities;
	}

	/** Installs and adds the main menu and the submenu items */
	public function admin_menu() {


		$page_title = __('MaxButtons: Buttons', 'maxbuttons');
		$menu_title = __('MaxButtons', 'maxbuttons');
		$capability = $this->get_user_level();
		$admin_capability = 'manage_options';
		$menu_slug = 'maxbuttons-controller';
		$function =  array($this, 'load_admin_page');
		$icon_url = $this->plugin_url . 'images/mb-peach-icon.png';
		$submenu_function = array($this, 'load_admin_page');



		$this->admin_pages[] = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, 81);

		// We add this submenu page with the same slug as the parent to ensure we don't get duplicates
		$sub_menu_title = __('Buttons', 'maxbuttons');
		$menu_slug = 'maxbuttons-controller';
		$this->admin_pages[] = add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);

		// Now add the submenu page for the Add New page
		$submenu_page_title = __('MaxButtons: Add/Edit Button', 'maxbuttons');
		$submenu_title = __('Add New', 'maxbuttons');
		$submenu_slug = 'maxbuttons-controller&action=edit'; // action here for writing to layout header to be consistent.

		//$submenu_function = 'maxbuttons_button';
		$this->admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Go Pro page
		$submenu_page_title = __('MaxButtons: Upgrade to Pro', 'maxbuttons');
		$submenu_title = __('Upgrade to Pro', 'maxbuttons');
		$submenu_slug = 'maxbuttons-pro';
		//$submenu_function = 'maxbuttons_pro';
		$this->admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Settings page
		$submenu_page_title = __('MaxButtons: Settings', 'maxbuttons');
		$submenu_title = __('Settings', 'maxbuttons');
		$submenu_slug = 'maxbuttons-settings';
		//$submenu_function = 'maxbuttons_settings';
		$this->admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $admin_capability, $submenu_slug, $submenu_function);

		// Now add the submenu page for the Support page
		$submenu_page_title = __('MaxButtons: Support', 'maxbuttons');
		$submenu_title = __('Support', 'maxbuttons');
		$submenu_slug = 'maxbuttons-support';
		//$submenu_function = 'maxbuttons_support';
		$this->admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $admin_capability, $submenu_slug, $submenu_function);

		if (! MaxInstall::hasAddon('socialshare'))
		{
			$submenu_page_title = __('MaxButtons: Share Buttons', 'maxbuttons');
			$submenu_title = __('Share Buttons', 'maxbuttons');
			$submenu_slug = 'social-share';
			$this->admin_pages[] = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
		}


		$this->admin_pages = apply_filters('maxbuttons/plugin/admin_pages',$this->admin_pages);

		//add_action('load-maxbuttons-')
		$unique = array_unique($this->admin_pages);
		foreach($unique as $hook)
		{
				add_action('load-' . $hook, array($this, 'post_admin_page'));
		}

	}

	/** Loads before output and page, to check on $_POSTs */
	public function post_admin_page()
	{
			global $plugin_page;
			$page = sanitize_text_field($_GET["page"]);
			$url = menu_page_url($plugin_page, false);

			$controller = $this->getController($page);
			$controller->setPage($page);
			$controller->setUrl($url);
			$controller->load();

	}

	// Loads admin controller based on page
	public function load_admin_page()
	{
		global $plugin_page;
		$page = sanitize_text_field($_GET["page"]);
	//	$url = menu_page_url($plugin_page, false);

		 $controller = $this->getController($page);
		//$pagepath = $this->plugin_path . $pagepath;
//		$controller->setPage($page);
	//	$controller->setUrl($url);
		$controller->view();
	//	include(apply_filters("mb-load-admin-page-$page", $pagepath));
	}

	private function getController($page)
	{

		 		if ($page == 'maxbuttons-controller')
		 		{
		 			$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : null;
		 			if (! is_null($action))
		 			{
		 				$page = 'maxbuttons-list'; // default;

		 				if ($action == 'button' || $action == 'edit')
		 				{
		 					$page = 'maxbuttons-button';
		 				}

		 			}
		 		}
		 		switch($page)
		 		{
		 			case "maxbuttons-button":
		 				$controller = editorController::getInstance();
		 			break;
		 			case "maxbuttons-support":
		 				$controller = supportController::getInstance();
		 			break;
		 			case "maxbuttons-settings":
		 				$controller = settingsController::getInstance();
		 			break;
		 			case "maxbuttons-pro":
		 			case "social-share":
		 				$controller = upgradeController::getInstance();
		 			break;
		 			default:
		 				$controller = listController::getInstance();
		 			break;
		 		}

				return $controller;
	}

	/** Load a library. This can be a non-standard Javascript / CSS combination or external PHP scripting
	*
	*  @param $libname String Known library name
	*/
	public function load_library($libname)
	{
			$version = MAXBUTTONS_VERSION_NUM;
			$js_url = trailingslashit($this->plugin_url . 'js');

		 if ($libname == 'review_notice')
		 {
			 wp_register_style('maxbuttons-review-notice-css',$this->plugin_url . 'assets/css/review_notice.css', array(), $version);
			 wp_register_script('maxbuttons-review-notice', $js_url . 'review-notice.js',  array('jquery'), $version);

			 $local = array();
			 $local["ajaxurl"] = admin_url( 'admin-ajax.php' );
			 $local['nonce'] = wp_create_nonce('maxajax');
			 wp_localize_script('maxbuttons-review-notice', 'mb_ajax_review', $local);

			 wp_enqueue_style('maxbuttons-review-notice-css');
			 wp_enqueue_script('maxbuttons-review-notice');
		 }

		 if ($libname == 'scss')
		 {
					 require_once($this->get_plugin_path() . "assets/libraries/scssphp/scss.inc.php");
		 }

		 if ($libname == 'simple_template')
		 {
				 require_once($this->get_plugin_path() . "assets/libraries/simple-template/simple_template.php");
		 }

		 if ($libname == 'simplehtmldom')
	 	 {
			//	 if (! class_exists('simple_html_dom_node'))
					 require_once($this->get_plugin_path() . "assets/libraries/simplehtmldom/simple_html_dom.php");
		 }

		 if ($libname == 'polyfill-ctype')
		 {
			 		 require_once($this->get_plugin_path() . "assets/libraries/polyfill-ctype/bootstrap.php");
		 }

	}

	/** Load general styles when visiting pages set by the plugin */
	public function load_plugin_styles() {
		// only hook in maxbuttons realm.
		/*if ( strpos($hook,'maxbuttons') === false && $hook != 'post.php' && $hook != 'post-new.php' )
		{ */
		//if (! isset($_GET['fl_builder'])) // exception for beaver builder
		//	return;
 		//}

		if (is_rtl())
			wp_register_style('maxbuttons-css', $this->plugin_url . 'assets/css/style.rtl.css', array(), $this->version);
		else
			wp_register_style('maxbuttons-css', $this->plugin_url . 'assets/css/style.css', array(), $this->version);


		wp_enqueue_style('maxbuttons-css');

		wp_register_style('mb-alpha-color', $this->plugin_url . 'assets/libraries/alpha-color/alpha-color-picker.css', array(), $this->version);

		wp_enqueue_style('mb-alpha-color');

	}

	public function add_admin_scripts($hook)
	{
		if (! in_array($hook, $this->admin_pages))
			return; // not our circus ...

		$is_editor = false;
		if ($hook == 'toplevel_page_maxbuttons-controller' && isset($_GET['action']) && $_GET['action'] == 'edit')
			$is_editor = true;
		elseif ($hook == 'maxbuttons_page_maxbuttons-button')
		 	$is_editor = true;

		$this->load_plugin_scripts();
		$this->load_plugin_styles();
		$this->load_ajax_script();
		$this->load_modal_script();

		if ($is_editor)
		{
			$this->load_editor_scripts();
		}

	}

	/** Load general scripts when visiting pages set by the plugin */
	protected function load_plugin_scripts() {
		// only hook in maxbuttons realm.

		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('wplink');

		wp_register_script('maxbutton-admin', $this->js_url . 'maxbuttons-admin.js', array('jquery', 'jquery-ui-draggable', 'maxbuttons-tabs','maxbuttons-modal', 'maxbuttons-tabs', 'maxbuttons-ajax', 'maxbutton-alpha-picker', 'underscore', 'maxbuttons-ajax', 'wplink'),$this->version, true);

		wp_localize_script('maxbutton-admin', 'maxadmin_settings', array(
				'homeurl' => home_url(),
				'remove_confirm' => __('Are you sure you want to remove this screen?', 'maxbuttons'),
			));

		wp_register_script('maxbutton-alpha-picker', $this->plugin_url . 'assets/libraries/alpha-color/alpha-color-picker.js', array('jquery', 'wp-color-picker'), $this->version, true);

		wp_localize_script(
				'maxbutton-alpha-picker',
				'wpColorPickerL10n',
				array(
					'clear'            => __( 'Clear' ),
					'clearAriaLabel'   => __( 'Clear color' ),
					'defaultString'    => __( 'Default' ),
					'defaultAriaLabel' => __( 'Select default color' ),
					'pick'             => __( 'Select Color' ),
					'defaultLabel'     => __( 'Color value' ),
				)
			);


		wp_register_script('maxbutton-js-init', $this->js_url . 'init.js', array('maxbutton-admin'),$this->version, true);

		wp_localize_script('maxbutton-js-init', 'maxbuttons_init',  array(
				'initFailedTitle' => __('Maxbuttons detected an Error', 'maxbuttons'),
				'initFailed' => sprintf(__('MaxButtons has detected Javascript not loading as expected. Often this is caused by other plugins conflicting or crashing. %s
					- Disable other plugins and see if this error goes away %s
					- Check developer console (F12) on your browser to check which script is causing the issue %s
					- Contact our support %s
				', 'maxbuttons'), '<br>','<br>','<br>','<br>'),
				'initFailedDetectedErrors' => '<h3>' . __('Detected Errors:', 'maxbuttons') . '</h3>',
 		));
		wp_register_script('maxbuttons-tabs', $this->js_url . 'maxtabs.js', array('jquery') ,$this->version, true);

		wp_enqueue_script('maxbutton-alpha-picker');
		wp_enqueue_script('maxbutton-admin');
		wp_enqueue_script('maxbutton-js-init', $this->js_url . 'init.js', array('maxbutton-admin'),$this->version, true);
		wp_enqueue_script('maxbuttons-tabs', $this->js_url . 'maxtabs.js', array('jquery') ,$this->version, true);

		wp_enqueue_style('editor-buttons'); // style for WP-link

	//	$this->load_ajax_script();
	//	$this->load_modal_script();
	}

	/* Load scripts specifically for the editor */
	protected function load_editor_scripts()
	{
			wp_register_script('maxbutton-live-preview', $this->js_url . 'live-preview.js',array('maxbutton-admin'), $this->version, true);

			wp_localize_script('maxbutton-live-preview', 'lptranslations', array(
					'originalValue' => __('Original Value: ', 'maxbuttons'),
			));

			wp_enqueue_script('maxbutton-live-preview');

			$this->load_library('fontawesome');
			wp_enqueue_style('wp-color-picker');

			// Costly annoying scripts
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('wp_print_styles', 'print_emoji_styles');
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );

			do_action('maxbuttons/init/editor_scripts');
	}

	/** Load the Modal Script
	*	The modal script is the generic solution for all popups within the plugin.
	*/
	public function load_modal_script()
	{
		wp_register_script('maxbuttons-modal', $this->js_url . 'maxmodal.js', array('jquery','jquery-ui-draggable'), $this->version, true);
 		// translations of controls and other elements that can be used in maxmodal
 		$translations = array(
 				'yes' => __("Yes","maxbuttons"),
 				'no' => __("No","maxbuttons"),
 				'ok' => __("OK","maxbuttons"),
 				'cancel' => __("Cancel","maxbuttons"),
 		);
 		wp_localize_script('maxbuttons-modal', 'modaltext', $translations);
		wp_enqueue_script('maxbuttons-modal');

		wp_enqueue_style('maxbuttons-maxmodal', $this->plugin_url . 'assets/css/maxmodal.css', array(), $this->version);

	}

	/** Load MaxAjax services
	*
	* MaxButtons Ajax Library.
	*/
	public function load_ajax_script()
	{
		wp_register_script('maxbuttons-ajax', $this->js_url . 'maxajax.js', array('jquery'), $this->version, true);
		wp_localize_script('maxbuttons-ajax', 'maxajax',
							array(
									'ajax_url' => admin_url( 'admin-ajax.php' ),
									'ajax_action' => 'maxajax',
									'nonce' => wp_create_nonce('maxajax'),
									'leave_page' => __("You have unsaved data, are you sure you want to leave the page?","maxbuttons"),
						 ));

		wp_enqueue_script('maxbuttons-ajax');
	}

	/** Load Media Buttons Script
	*
	*	Useful for integrations that don't implement the media button but uses the media button JS for loading the button picker
	*
	*/
	public function load_media_script()
	{

		wp_register_script('mb-media-button', $this->js_url . 'media_button.js', array('jquery', 'maxbuttons-modal', 'maxbuttons-ajax'), $this->version, true);

		$this->load_modal_script();
		$this->load_ajax_script();

		wp_add_inline_script( 'maxbuttons-modal', '$ = jQuery;' );

		$translations = array(
		'insert' => __('Insert Button into Editor', 'maxbuttons'),
		'use' => __('Use this Button', 'maxbuttons'),
		'loading' => __("Loading your buttons","maxbuttons"),
		'select' => __('Click on a button from the list below to place the button shortcode in the editor.', 'maxbuttons'),
		'cancel' => __('Cancel', 'maxbuttons'),
		'windowtitle' => __("Select a MaxButton","maxbuttons"),
		'icon' => $this->plugin_url . 'images/mb-peach-32.png',
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'short_url_label' => __('Button URL', 'maxbuttons'),
		'short_text_label' => __('Button Text', 'maxbuttons'),
		'short_options_explain' => __('If you want to change the URL or Text of the Button, enter the appropiate field. If you want to use the button values, just click Add to editor', 'maxbuttons'),
		'short_add_button' => __('Add to Editor', 'maxbuttons'),
		);

		wp_localize_script('mb-media-button','mbtrans', $translations);
		wp_enqueue_script('mb-media-button');

		wp_enqueue_style('maxbuttons-media-button', $this->plugin_url . 'assets/css/media_button.css', array(), $this->version);


	}

		/** Inits the options for WP editor, like tinymce and other buttons **/
		public function init_wp_editor_options()
		{

			// option
			if (get_option('maxbuttons_noshowtinymce') == 1) return;

			// Media buttons
			add_action('media_buttons', array($this,'media_button'), 20);

			add_filter('mce_buttons', array($this, 'tinymce_button'));
			add_filter('mce_external_plugins', array($this, 'add_tinymce_button'));

		}

		/** Load Media Button in WP editor
		*
		* The 'add button' interface in WP post and page editor to simplify adding buttons. Loads button plus required Javascript.
		*/
		public function media_button($editor_id) {
			$output = '';

			$this->load_media_script();

			// Only run in post/page creation and edit screens

				$title = __('Add Button', 'maxbuttons');
				$icon = $this->plugin_url . 'images/mb-peach-icon.png';
				//$nonce = wp_create_nonce('maxajax');
				$img = '<span class="wp-media-buttons-icon" style="background-image: url(' . $icon . '); width: 16px; height: 16px; margin-top: 1px;"></span>';
				//$img = '';
				$output = '<button id="maxbutton-add-button" type="button" class="button maxbutton_media_button" onclick="var mm = new window.maxFoundry.maxMedia();
				mm.init();
				mm.openModal();"
				 title="' . $title . '" style="padding-left: .4em;" data-editor=' . $editor_id . '>' . $img . ' ' . $title . '</button>';

			echo $output;
	}

	public function tinymce_button($buttons)
	{

			$buttons[] = 'maxbutton';
			return $buttons;
	}

	public function add_tinymce_button($plugin_array)
	{

		$this->load_media_script(); // enqueue button handler

		$plugin_array['maxButtons_tinymce'] = $this->js_url . 'tinymce.js' ;
		return $plugin_array;

	}


	/** Scripts run on front-end
	*	Load font-awesome, and limited javascript for the front-end. This is being kept extremely limited for performance reasons.
	*/
	public function front_scripts()
	{

		// load backend script on front in Beaver Builder
		if (isset($_GET['fl_builder']))
		{
			$this->admin_pages[] = 'beaverfront';
			$this->add_admin_scripts('beaverfront');
		}

	}

	/** Extra text to display in admin footer */
	public function admin_footer_text($text)
	{
		if (! isset($_GET["page"]))
			return $text;

		if ( strpos($_GET["page"],'maxbuttons') === false)
			return $text;
		$text = '';

		$text .=   sprintf("If you like MaxButtons please give us a  %s★★★★★%s rating!",
			"<a href='https://wordpress.org/support/view/plugin-reviews/maxbuttons#postform' target='_blank'>",
			"</a>")  ;
		return $text;

	}

	/** Function for maxbuttons shortcode */
	public function shortcode($atts)
	{
		 $button = $this->getClass("button");

		 return $button->shortcode($atts);
	}

	/** Function for collection shortcode [deprecated] **/
	public function collection_shortcode($atts, $content = null)
	{
		return false; // no more. silent fail.

	}


	public function plugin_action_links($links, $file) {

		if ($file == plugin_basename(dirname(MAXBUTTONS_ROOT_FILE) . '/maxbuttons.php')) {
			$label = __('Buttons', 'maxbuttons');
			$dashboard_link = '<a href="' . admin_url() . 'admin.php?page=maxbuttons-controller&action=list">' . $label . '</a>';
			array_unshift($links, $dashboard_link);
		}

		return $links;
	}


	public function plugin_row_meta($links, $file) {
		if ($file == plugin_basename(dirname(__FILE__) . '/maxbuttons.php')) {
			$links[] = sprintf(__('%sUpgrade to Pro Version%s', 'maxbuttons'), '<a href="http://maxbuttons.com/?ref=mbfree" target="_blank">', '</a>');
		}

		return $links;
	}


	public function do_footer($id, $code, $type = "css")
	{
		if ($type == 'preview')
			$this->footer['preview'] = true;
		else
			$this->footer[$type][$id] = $code;
	}


	/** Output footer styles and scripts
	*
	*	Outputs loaded styles, and scripts to the footer for display. Email_off is to prevent cloudfare from 'obfuscating' the minified CSS
	* No optimize prevent autoptimize from mutilating the already optimized CSS.
	*/
	public function footer()
	{
		if(count($this->footer) == 0) return; // nothing

		$is_preview = false;
		if (isset($this->footer['preview']))
		{
				$is_preview = true;
				unset($this->footer['preview']);
		}

		$button_ids = array();
		$use_file = get_option('maxbuttons_usecssfile', false);

		foreach ($this->footer as $type => $part)
		{
			if ($type == 'css' && $use_file && ! $is_preview) // use file output to a CSS filebased output, don't put it inline.
			{
				foreach($part as $id => $statements)
				{
					if (is_numeric($id))
						$button_ids[] = $id;
			//		else
			//				echo "nonum $id";
				}
				continue;
			}

			// add tag
			if ($type == 'css')
			{
				echo "<!--noptimize--><!--email_off--><style type='text/css'>";
			}

			foreach ($part as $id => $statements)
			{

				if (is_string($statements) && strlen($statements) > 0) // prevent whitespace
						echo $statements . "\n";
			}

			if ($type == 'css')
			{
				echo "</style><!--/email_off--><!--/noptimize-->\n";
			}
		}

		if (is_array($button_ids) && count($button_ids) > 0 && $use_file && ! $is_preview)
		{
			wp_enqueue_style('maxbuttons-front', admin_url('admin-ajax.php').'?action=maxbuttons_front_css&id=' . implode(',',array_unique($button_ids)), array(), MAXBUTTONS_VERSION_NUM, 'screen' );
		}
	}

		/*	Add a notice

			The added notice will be displayed to the user in WordPress format.
			@see display_notices

			@param $type string message | notice | error | fatal
			@param $message string User understandable message

		*/
		public static function add_notice($type, $message)
		{
			self::$notices[] = array("type" => $type,
									"message" => $message
								);

		}

		/* Display all notices

		Then notices added by @see add_notice will be displayed. This function is called by an action hook

		@param $echo echo the results or silently return.
		@return string|null If not written to screen via echo, the HTML output will be returned
		*/
		public function display_notices($echo = true)
		{

			if ($echo === '') $echo = true;
			$notices = self::$notices;
			$output = '';
			if (count($notices) == 0)
				return;

			foreach($notices as $index => $notice)
			{
				$type = $notice["type"];
				$message = $notice["message"];
				$output .= "<div class='mb-message $type'> ";
				$output .= $message ;
				$output .= "</div>";
			}

			self::$notices = array(); // empty notices to prevent double display

			if ($echo) echo $output;
			else return $output;
		}

}  // class
