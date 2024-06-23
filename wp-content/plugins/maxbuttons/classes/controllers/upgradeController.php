<?php
declare(strict_types=1);
namespace MaxButtons;

use MaxButtons\Upgrader\upgradeLicense as upgradeLicense;
use MaxButtons\Upgrader\proInstaller as proInstaller;

class upgradeController extends MaxController
{

  protected $view_template = 'maxbuttons-pro';

  public function view()
  {
		if (! property_exists($this->view, 'licenseKey'))
		{
			$this->view->licenseKey = '';
		}

    if ($this->page == 'social-share')
		{
      $this->view_template = 'social-share';
		}

    parent::view();
  }

  // no posts.
  public function handlePost()
  {
		if (check_admin_referer('upgrade', 'upgrade_nonce'))
		{

				$licenseKey = isset($_POST['license_key']) ? trim(sanitize_text_field($_POST['license_key'])) : false;
				$this->view->licenseKey = $licenseKey;

				if (strlen($licenseKey) == 0 )
					$this->view->PostError = __('Enter a valid license key to proceed', 'maxbuttons');
				else
					$this->checkLicense($licenseKey);
		}

    return false;
  }

	private function checkLicense($licenseKey)
	{
			require_once(MB()->get_plugin_path() . 'classes/upgrader/license.php');

			$licenseController = upgradeLicense::getInstance();
			$result = $licenseController->activate_license($licenseKey);

			if ($result->success == false)
			{
				 $this->view->PostError = $result->additional_info;
			}
			elseif ($result->success)
			{
				 	if ($result->license == 'valid')
					{
							 $url = $licenseController->getPackageUrl($licenseKey);

							 if ($url !== false)
							 {
							 	 $this->installPlugin($url);
							 }
							 else
							 {
							 	 $this->view->PostError =  __('License seems active, but something went wrong acquiring the download', 'maxbuttons');
							 }

					}
			}

	}

	private function installPlugin($url)
	{
			require_once(MB()->get_plugin_path() . 'classes/upgrader/installer.php');

			$installer = proInstaller::getInstance();
			$result = $installer->installPro($url);

			if ($result['success'])
			{
				 $result = $installer->switchPlugins();
				 if ($result['success'])
				 {
					  $this->view->PostError = __('Everything seems fine, plugin should have redirected to the PRO version', 'maxbuttons');
				 }
				 else
				 {
					  $this->view->PostError = $result['error_message'];
				 }
			}
			else
			{
					$this->view->PostError = $result['error_message'];
			}
	}

	private function activateRemoteLicense()
	{

	}

	private function updateLicense()
	{
			update_option('maxbuttons_pro_license_expires', $expires );
			update_option('maxbuttons_pro_license_activated', false, true);
	}

	private function downloadPlugin()
	{

	}

} // controller
