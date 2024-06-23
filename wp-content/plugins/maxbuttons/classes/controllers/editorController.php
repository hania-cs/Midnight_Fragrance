<?php
declare(strict_types=1);

namespace MaxButtons;

// main thing for the button editor
class editorController extends MaxController
{
  protected $view_template = 'maxbuttons-button';

  public function __construct()
  {
      parent::__construct();

      MB()->load_library('simple_template');

      add_filter( 'wp_link_query_args', array($this,'queryMediaOnLink') );
      add_filter( 'wp_link_query', array($this, 'fixResultsOnMediaLink'), 10, 2 );

  }

	public function  load()
	{
		  $this->loadView();
			parent::load();
	}

  public function view()
  {
    /*  if ($_POST) {
      $this->handlePost();
    } */
		$this->loadView();

    parent::view();
  }

  protected function loadView()
  {
    $button = MB()->getClass('button');
    $button_id = 0;

    if (isset($_GET['id']) && $_GET['id'] != '') {
      $button_id = intval($_GET["id"]);

      if ($button_id == 0)
      {
        $error = __("Maxbuttons button id is zero. Your data is not saved correctly! Please check your database.","maxbuttons");
        MB()->add_notice('error', $error);
      }
        // returns bool
      $return = $button->set($button_id);

      if ($return === false)
      {
        $error = __("MaxButtons could not find this button in the database. It might not be possible to save this button! Please check your database or contact support! ", "maxbuttons");
        MB()->add_notice('error', $error);
      }
      $this->view->button_is_new = false;
    }
    else
    {
      $this->view->button_is_new = true;
    }

    $screens = $button->getResponsiveScreens();
    $this->view->button = $button;
    $this->view->button_id = $button_id;
    $this->view->screens = $screens;
    // This is a flaw because PHP can't gather the fragment #hash of the URL, this on load will always be this.
    $this->view->currentScreen = 'default';
  }

  protected function getCurrentScreen()
  {
    if ( isset($this->view->screens[$this->view->currentScreen]))
      return $this->view->screens[$this->view->currentScreen];

    return null; // this should never happen.

  }

  // Load the editor for screen.
  protected function showScreenEditor($screen)
  {
      $blocks = $this->view->button->getBlocks();

    //  $screen = new Screen($show_screen);
      $is_responsive = $screen->is_responsive();
      $is_new = $screen->is_new();
      $is_default = $screen->is_default();

      $map = array();
      $the_map = array();


      foreach($blocks as $block)
      {

          if ($is_default && ! $block->is_default() )
            continue;
          if ($is_responsive && ! $block->is_responsive())
            continue;
          if ($is_new && ! $block->is_new())
            continue;

            $map = $block->map_fields($map);

            $block->admin_fields($screen);

            $screen->display_fields();
      }

      // Map Fields *after* doing all, otherwise not all fields might be defined.
      $the_map = $screen->mapFields($map);
      $screen->displayFieldMap($the_map);

  }


  protected function handlePost()
  {
    if (! check_admin_referer("button-edit","maxbuttons_button"))
  	{
  		exit("Request not valid");
  	}


    $this->updateButton();
  }

  protected function updateButton()
  {

    $button = MB()->getClass('button');
    $button_id = intval($_POST["button_id"]);
		$new_button = false;

    $current_screen = 'default';
    if (isset($_POST['current_screen']))
    {
      $current_screen = $_POST['current_screen'];
      unset($_POST['current_screen']);
    }

  	if ($button_id > 0)
  		$button->set($button_id);

  	$return = $button->save($_POST, $this->view->screens);

  	if (is_int($return) && $button_id <= 0)
		{
  		$button_id = $return;
			$new_button = true;
		}

   	if ($button_id === 0)
   	{
   		error_log(__("Maxbuttons Error: Button id should never be zero","maxbuttons"));
   	}

  	$button->set($button_id);
    $url = $this->getButtonLink($button_id);

    if ($current_screen != 'default')
    {
      $url .= '#' . urlencode($current_screen);
    }

		if ($new_button)
		{
  	 		wp_redirect($url);
  	 		exit();
		}

  } // handlePost.

  protected function addScreen()
  {
    $button = MB()->getClass('button');
    $button_id = intval($_POST["button_id"]);

    if ($button_id > 0)
      $button->set($button_id);
    // update here on the button class?
  }

  public function queryMediaOnLink($query)
  {
       if (! isset($query['s']) || $query['s'] == '')
        return $query;

       $query['post_status'] = (array) $query['post_status'];
       $query['post_status'][] = 'inherit';

       return $query;
  }

  public function  fixResultsOnMediaLink($results)
  {
    foreach ( $results as $key => $result ) {
            if ( 'Media' === $result['info'] ) {
                    $results[$key]['permalink'] = wp_get_attachment_url( $result['ID'] );
            }
    }
    return $results;
  }



} // Class editorController
