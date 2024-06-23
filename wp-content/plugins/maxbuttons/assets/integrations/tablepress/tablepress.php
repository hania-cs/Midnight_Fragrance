<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

class MBTablePress
{

  protected $enabled = false;

	protected static $loaded = array();

  public function __construct()
  {
      add_filter('tablepress_table_raw_render_data', array($this, 'render_on'), 10, 2);
      add_filter('tablepress_table_output', array($this, 'render_off'));
      add_filter('mb_shortcode_display_args', array($this, 'shortcode_args'), 5, 3);
  }

  public function render_on($table, $options)
  {
    $this->enabled = true;
    return $table;
  }

  public function render_off($output)
  {
    $this->enabled = false;
    return $output;
  }

  public function shortcode_args($args, $button_id, $data)
  {
			// Don't load repetitions as inline.
			if (in_array($button_id, self::$loaded))
			{
				return $args;
			}

      $auto_press = apply_filters('mb/integrations/auto_inline_tablepress', true);
      if ($this->enabled && $auto_press)
      {
        $args['load_css'] = 'inline'; // force inline css, because tablepress caches
				self::$loaded[] = $button_id;
      }
      return $args;
  }


}

$table = new MBTablePress();
