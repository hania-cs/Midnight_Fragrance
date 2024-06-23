<?php
declare(strict_types=1);
namespace MaxButtons;

class settingsController extends MaxController
{

  protected $view_template = 'maxbuttons-settings';

  public function __construct()
  {
    MB()->load_library('simple_template');
    parent::__construct();
  }

  // view Loader.
  public function view()
  {
    parent::view();
  }


  public function handlePost()
  {
		 if (! check_admin_referer('action-settings-form', 'maxbuttons-settings-nonce'))
		 {
			 exit('Invalid Nonce');
		 }
    if(isset($_POST['alter_charset'])) {

        global $maxbuttons_installed_version;
        global $wpdb;
        $table_name = maxUtils::get_table_name();

        $sql = "ALTER TABLE " . $table_name . " CONVERT TO CHARACTER SET utf8";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->query($sql);
        $this->view->response = 'CHARSET now utf_8 COLLATE utf8_general_ci';

    } else {
        $this->view->response = '';
    }

    if (isset($_POST["reset_cache"]))
    {
    	$button = MB()->getClass('button');
    	$button->reset_cache();
			MB()->add_notice('', __('Cache reset', 'maxbuttons'));

    }

    if (isset($_POST["remigrate"]))
    {
     	$install = MB()->getClass("install");
    	$install::create_database_table();
    	$install::migrate();
    }

    if (isset($_POST['remigrateresponsive']))
    {
       $install = MB()->getClass("install");
       $install::migrateResponsive();
    
    }

    if (isset($_POST["replace"]) && check_admin_referer('mb_bulk_edit', 'bulk_edit'))
    {
    	$search = sanitize_text_field($_POST["search"]);
    	$replace = sanitize_text_field($_POST["replace"]);
    	$field = sanitize_text_field($_POST["replace_field"]);

    	$button = MB()->getClass('button');

    	if ($field == '')
    		exit("FATAL");

    	$admin = MB()->getClass('admin');
    	$buttonsIDS = $admin->getButtons(array('limit' => -1));

    	$data_found = false;

    	foreach($buttonsIDS as $row)
    	{
    		$button_id = $row["id"];
    		$button->set($button_id);
    		$data = $button->get();
    		foreach($data as $block => $fields)
    		{
    			if (isset($fields[$field]))
    			{
    				$value = $fields[$field];
    				$data[$block][$field] = str_replace($search, $replace, $value);
    				$button->update($data);

    				$data_found = true;
    				continue;
    			}

    			if ($data_found)
    			{
    				$data_found = false;
    				continue;
    			}
    		}


    	}

    }
  } // handlePost

} // settingsController
