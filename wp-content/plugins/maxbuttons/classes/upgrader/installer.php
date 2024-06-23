<?php
declare(strict_types=1);
namespace MaxButtons\Upgrader;

class ProInstaller
{
	private $plugin = 'maxbuttons-pro';
	private static $instance;
	private $free_slug = 'maxbuttons/maxbuttons.php';
	private $pro_slug = 'maxbuttons-pro/maxbuttons-pro.php';


	public static function getInstance()
	{
  		if (is_null(self::$instance))
				self::$instance = new ProInstaller();

			return self::$instance;
	}

	public function installPRO($download_url) {

	// Install the addon.
	$result = array(
		'success' => false,
		'error_message' => __('Unspecified error while installing plugin', 'maxbuttons'),
		'base' => null,
	);

	$plugins = get_plugins();
	if (isset($plugins[$this->pro_slug]))
	{
		 $result['error_message'] = __('Installation failed. The PRO plugin seems already installed on your website! You can activate it manually via the plugin screen', 'maxbuttons');
		 return $result;
	}

	if ( ! is_null($download_url ) ) {

		//$download_url = esc_url_raw( wp_unslash( $_POST['plugin'] ) );
		global $hook_suffix;

		// Set the current screen to avoid undefined notices.
		set_current_screen();

		require_once (ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
		require_once (ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php');


		$url = admin_url('wp-admin');
		$method = 'POST';

		// Create the plugin upgrader with our custom skin.
		$skin      = new \Automatic_Upgrader_Skin();
		$installer = new \Plugin_Upgrader( $skin );
		$installer_result = $installer->install( $download_url );


		if (is_wp_error($installer_result))
		{
				$result['error_message'] = $installer_result->get_error_message();
				return $result;
		}

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();

		//ob_clean();
		$result['basename'] = $plugin_basename;
		$result['success'] = true;
		return $result;

		}
		else
		{
					// Check filesystem perms to see if user is allowed to install.
					// If not this annoying FTP permission screen pops up.
					$bool = ob_start();

					$creds = request_filesystem_credentials( $url, $method, false, false, null );
					if ( false === $creds ) {

							 $result['error_message'] = sprintf(__('Installation failed. WordPress doesn\'t have permission to install plugin(s). Please check and correct %s your permissions %s', 'maxbuttons'), '<a href="https://wordpress.org/support/article/changing-file-permissions/" target="_blank">', '</a>');
							 $result['success'] = false;
							 $bool = ob_end_clean();
							 return $result;
					}

		}
	}
	else
	{
		 $result['error_message'] = __('Failed to find Download URL', 'maxbuttons');
	}

// failed, unreasonably.
return $result;

}

 public function switchPlugins() {

			$result = array(
				'success' => false,
				'error_message' => __('Unspecified error while switching plugins', 'maxbuttons'),
				'base' => null,
			);


	 		$plugins = get_plugins();

			$redirect = admin_url('admin.php?page=maxbuttons-license&installedfrom=maxbuttons-free');

			if (is_plugin_active($this->free_slug))
			{
				 	 if (! is_plugin_active($this->pro_slug) && isset($plugins[$this->pro_slug]))
					 {
						  // Deactivate before activate, because activate requires a reditect. Suboptimal.
							 deactivate_plugins($this->free_slug);
						 	 $res = activate_plugin($this->pro_slug, $redirect);
							 if (is_wp_error($res))
							 {
									$result['error_message'] = $res->get_error_message();
								 	return $result;
							 }
					 }
					 else
					 {
						 $result['error_message'] = __('MaxButtons PRO seems not to be installed.', 'maxbuttons');
					 }
			}
			else
			{
				 	$result['error_message'] = __('This plugin reported as not active(?)  - Aborting', 'maxbuttons');
			}


			return $result;

		}

} // class
