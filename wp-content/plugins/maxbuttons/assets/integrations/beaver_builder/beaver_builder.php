<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');


class BeaverBuilderFree
{
    public function __construct()
    {
        add_filter('mb_shortcode_display_args', array($this, 'check_builder'));
    }

    // if button is loading inside the editor, compile and inline the shortcode output
    public function check_builder($args)
    {
       if (isset($_GET['fl_builder']))
       {
            $args['load_css'] = 'inline';
            $args['compile'] = true;
       }

       return $args;
    }

}

$b = new BeaverBuilderFree();
