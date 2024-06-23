<?php
namespace maxButtons;
defined('ABSPATH') or die('No direct access permitted');


class gutenBerg{

  public static function init()
  {
      add_action( 'enqueue_block_editor_assets', array(maxUtils::namespaceit('gutenBerg'), 'editor_scripts') );
      add_action( 'maxbuttons/ajax/gutenberg_button', array(maxUtils::namespaceit('gutenBerg'), 'generate_button'));
      add_action('init', array(maxUtils::namespaceit('gutenBerg'), 'register_block'));

  }

  public static function editor_scripts()
  {
    $version = MAXBUTTONS_VERSION_NUM;

    wp_register_script(
  		'maxbuttons_gutenberg-js', // Handle.
  		MB()->get_plugin_url() . 'assets/integrations/gutenberg/blocks.build.js', // Block.build.js: We register the block here. Built with Webpack.
  		array( 'wp-blocks', 'wp-i18n', 'wp-element' ), // Dependencies, defined above.
  		$version,
  		true // Enqueue the script in the footer.
  	);

    wp_localize_script('maxbuttons_gutenberg-js', 'mb_gutenberg', array(
        'ispro' => (defined('MAXBUTTONS_PRO_ROOT_FILE')) ? 1 : 0,
        'icon_url' => MB()->get_plugin_url() . '/images/mb-32.png',
    ));


    wp_enqueue_script('maxbuttons_gutenberg-js');

  	// Styles.
  	wp_enqueue_style(
  		'maxbuttons_block-editor-css', // Handle.
  		MB()->get_plugin_url() . 'assets/integrations/gutenberg/blocks.editor.build.css', // Block editor CSS.
  		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
  		$version
  	);

    MB()->load_media_script();

  }

  public static function register_block()
  {
    if (function_exists('register_block_type'))
    {
      register_block_type( 'maxbuttons/maxbuttons-block', array(
         'render_callback' => array(maxUtils::namespaceit('gutenBerg'), 'render_shortcode'),
        ) );
    }
  }

  // Ajax function used in Gutenberg editor to generate buttons
  public static function generate_button($post)
  {
    $id = isset($post['id']) ? $post['id'] : false;
    $text = isset($post['text']) ? $post['text'] : null;
    $text2 = isset($post['text2']) ? $post['text2']: null;
    $url = isset($post['url']) ? $post['url'] : null;
    $linktitle = isset($post['linktitle']) ? $post['linktitle'] : null;
    $window = isset($post['newwindow']) ? $post['newwindow']: null;
    $nofollow = isset($post['nofollow']) ? $post['nofollow'] : null;
    $is_download = isset($post['is_download']) ? $post['is_download'] : null;
    $extraclass = isset($post['extraclass']) ? $post['extraclass'] : null;
    $reset = isset($post['reset']) ? $post['reset'] : false;
    $compile = isset($post['compile']) ? true : false;

    if (strlen(trim($text)) == 0 )
      $text = null;

    if (strlen(trim($text2)) == 0 )
      $text2 = null;

    if ($window == 'true')
      $window = 'new';

    $button = MB()->getClass("button");

    if ($reset == 'true')
    {
      $shortcode_args = array('id' => $id);
    }
    else {
      $shortcode_args = array(
          'id' => $id,
          'text' => $text,
          'text2' => $text2,
          'url' => $url,
          'linktitle' => $linktitle,
          'window' => $window,
          'nofollow' => $nofollow,
          'is_download' => $is_download,
          'extraclass' => $extraclass,
        );
    }

    $the_button = $button->shortcode($shortcode_args);

    $response = array(
        'button' => $the_button,
        'style' => admin_url('admin-ajax.php'). '?action=maxbuttons_front_css&id=' . $id,
        'attributes' => false,
      );

    if ($reset == 'true') // on load new button, put all fields to the buttons values
    {
      $data = $button->get();

      $text = isset($data['text']['text']) ? $data['text']['text'] : '';
      $text2 = isset($data['text']['text2'])? $data['text']['text2'] : '';

      $url = isset($data['basic']['url']) ? $data['basic']['url'] : '';
      $linktitle = isset($data['basic']['link_title'])? $data['basic']['link_title'] : '';
      $window = isset($data['basic']['new_window']) ? $data['basic']['new_window'] : '';
      $nofollow= isset($data['basic']['nofollow']) ? $data['basic']['nofollow'] : '';
      $is_download = isset($data['basic']['is_download']) ? $data['basic']['is_download'] : '';

      if (isset($data['google']))
      {
        $google_enable = isset($data['google']['gtrack_enable']) ? $data['google']['gtrack_enable'] : '';
        $glabel = isset($data['google']['gtrack_label']) ? $data['google']['gtrack_label'] : '';
        $gcat = isset($data['google']['gtrack_cat']) ? $data['google']['gtrack_cat'] : '';
        $gaction = isset($data['google']['gtrack_action']) ? $data['google']['gtrack_action'] : '';
      }

      $response['attributes'] = array(
         'id' => $id,
         'text' => $text,
         'text2' => $text2,
         'url' => $url,
         'tooltip' => $linktitle,
         'newwindow' => ($window == 1) ? true : false,
         'relnofollow' => ($nofollow == 1) ? true : false,
         'is_download' => ($is_download == 1) ? true : false,

      );

      if (isset($data['google']))
      {
        $response['attributes']['google_action'] = $gaction;
        $response['attributes']['google_label'] = $glabel;
        $response['attributes']['google_category'] = $gcat;
        $response['attributes']['google_enable'] = $google_enable;
      }
    }

    wp_send_json_success($response);
  }

  // Render the shortcode on the front.
  public static function render_shortcode($atts)
  {
      if (! isset($atts['id'])) // no id, no button
      {
        return;
      }
      $id = $atts['id'];
       $args = array(
          'id' => $atts['id'],
          'text' => isset($atts['text']) ? $atts['text'] : null,
          'text2' => isset($atts['text2']) ? $atts['text2'] : null,
          'url' => isset($atts['url']) ? $atts['url'] : null,
          'linktitle' => isset($atts['tooltip']) ? $atts['tooltip'] : null,
          'window' => isset($atts['newwindow']) && $atts['newwindow'] == 1 ? 'new' : 'same',
          'nofollow' => isset($atts['relnofollow']) && $atts['relnofollow'] == 1 ? 'true' : 'false',
          'is_download' => isset($atts['is_download']) && $atts['is_download'] == 1 ? 'true' : 'false',
          'extraclass' => isset($atts['className']) ? $atts['className'] : null,
          'nocache' => isset($atts['compile']) ? true : null,
          'style' => isset($atts['style']) ? 'inline' : 'footer',
          'google_action' => isset($atts['google_action']) ? $atts['google_action'] : null,
          'google_label' => isset($atts['google_label']) ? $atts['google_label'] : null,
          'google_category' => isset($atts['google_category']) ? $atts['google_category'] : null,
      );

      $button = MB()->getClass("button");
      $thebutton = $button->shortcode($args);

      return $thebutton;
  }

} // class



gutenBerg::init();
