<?php
declare(strict_types=1);
namespace MaxButtons;

class listController extends MaxController
{
  protected $view_template = 'maxbuttons-list';

  protected $mbadmin;
  protected $button;

  public function __construct()
  {
    $this->mbadmin = MB()->getClass('admin');
    $this->button = MB()->getClass('button');
    parent::__construct();
  }

  // view Loader.
  public function view()
  {
    $this->loadView();
    parent::view();
  }

	public function load()
	{
    if (isset($_POST) && isset($_POST["mb-list-nonce"])  ) {
        $this->handlePost();
    }

	}


  public function loadView()
  {
    if (! isset($this->view->listView)) // Can be set by handlePost
      $this->view->listView = (isset($_GET["view"])) ? sanitize_text_field($_GET["view"]) : "all";

    $this->loadButtons();
    $this->view->published_buttons_count = $this->mbadmin->getButtonCount(array());
    $this->view->trashed_buttons_count = $this->mbadmin->getButtonCount(array("status" => "trash"));

  }

 protected function loadButtons()
 {
   $args = array();

   $args['orderby'] = isset($_GET["orderby"]) ? sanitize_text_field($_GET["orderby"]) : 'id';
   $args['order'] = isset($_GET["order"]) ? sanitize_text_field($_GET["order"]) : 'DESC';


   if (isset($_GET["paged"]) && $_GET["paged"] != '')
   {
   	$page = intval($_GET["paged"]);
   	$args["paged"] = $page;
   }

   if ($this->view->listView == 'trash')
   	$args["status"] = "trash";



   $published_buttons = $this->mbadmin->getButtons($args);

	 if (count($published_buttons) == 0) //  This can be new installation, but check problem with Database.
	 {
		 $install = MB()->getClass("install");
		 $bool = $install::maxbuttons_database_table_exists(maxUtils::get_table_name());
		 if ($bool === false)
		 {
			  $install::create_database_table();
		 }
	 }

   $this->view->published_buttons = $published_buttons;
   $this->view->pageArgs = $args;
 }

 protected function handlePost()
 {
   $verify = wp_verify_nonce( $_POST['mb-list-nonce'], 'mb-list' );
   if (! $verify )
   {
     $this->messages[] = __('Something went wrong with the form, nonce not verified', 'maxbuttons');
     return false;
   }

   $bulk_action = isset($_POST['bulk-action-select']) ? $_POST['bulk-action-select'] : false;
   $button_id = isset($_POST['button-id']) ? $_POST['button-id'] : false;

   if ($button_id && $bulk_action == 'trash') {
       $count = 0;
       foreach ($button_id as $id) {
         $id = intval($id);
         $this->button->set($id);
         $this->button->setStatus('trash');
         $count++;
       }

       if ($count == 1) {
         $this->messages[] = __('Moved 1 button to the trash.', 'maxbuttons');
       }

       if ($count > 1) {
         $this->messages[] = __('Moved ', 'maxbuttons') . $count . __(' buttons to the trash.', 'maxbuttons');
       }
   }
   elseif ($button_id && $bulk_action == 'restore') {
       $count = 0;

       foreach ($button_id as $id) {
         $id = intval($id);
         $set = $this->button->set($id,'','trash');
         $this->button->setStatus('publish');

         //maxbuttons_button_restore($id);
         $count++;
       }

       if ($count == 1) {
         $this->messages[] = __('Restored 1 button.', 'maxbuttons');
       }

       if ($count > 1) {
         $this->messages[] = __('Restored ', 'maxbuttons') . $count . __(' buttons.', 'maxbuttons');
       }
       $this->view->listView = 'all'; // switch to normal list.
   }

   if ($button_id && $bulk_action == 'delete') {
     $count = 0;

     foreach ($button_id as $id) {
       $id = intval($id);
       $this->button->delete($id);
       $count++;
     }

     if ($count == 1) {
       $this->messages[] = __('Deleted 1 button.', 'maxbuttons');
     }

     if ($count > 1) {
       $this->messages[] = __('Deleted ', 'maxbuttons') . $count . __(' buttons.', 'maxbuttons');
     }
   }
} // handlePost


protected function handleMessages()
{
  if (isset($_GET['message']) && $_GET['message'] == '1') {
  	$this->messages[] = __('Moved 1 button to the trash.', 'maxbuttons');
  }

  if (isset($_GET['message']) && $_GET['message'] == '1restore') {
  	$this->messages[] = __('Restored 1 button.', 'maxbuttons');
  }

  if (isset($_GET['message']) && $_GET['message'] == '1delete') {
  	$this->messages[] = __('Deleted 1 button.', 'maxbuttons');
  }

  if (isset($_GET['message']) && $_GET['message'] == 'empty-trash')
  {
  	$this->messages[] = __('Emptied Trash', 'maxbuttons');
  }

}

protected function getButtonScreenInfo()
{
	$screensInfo = '';

	$screens = $this->button->getResponsiveScreens();

	$screen_count = 0;
	$responsiveScreens = array();

	foreach($screens as $screen)
	{
		 if (! $screen->is_responsive() || $screen->is_new() || $screen->is_default())
		 	continue;

		 $screen_count++;
		 $responsiveScreens[] = $screen;
	}

	$has_responsive = ($screen_count > 0) ? true : false;

	if ($has_responsive)
	{
		 $screensInfo .= '<div class="screen-overview">
		 									<span class="dashicons dashicons-smartphone">&nbsp;</span>
		 									<span class="number">' . $screen_count . '</span>' ;
		 $screensInfo .= '<div class="screen-details">';
		 foreach($responsiveScreens as $screen)
		 {
			 if (! $screen->is_responsive())
			 {
			  	continue;
			 }
			list($screen_display, $screen_title ) = $screen->getScreenTitle();
			$icon = $screen->getScreenIcon();

			//$min_width = $this->getValue($this->getFieldID('min_width'));
			//$max_width = $this->getValue($this->getFieldID('max_width'));

			$screensInfo .= '<span><i class="dashicons ' . $icon . '" ></i>'  . $screen_display . '</span>';
		 }
		 $screensInfo .= '</div>'; // mouseover
		 $screensInfo .= '</div>';
	}

   return $screensInfo;
}

} // class listController
