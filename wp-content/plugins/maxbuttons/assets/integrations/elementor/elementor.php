<?php
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');


class MBElementor
{

    public function __construct()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

        if ( ($action == 'elementor' || $action == 'elementor_ajax') && is_admin())
        {
            add_filter('mb_shortcode_display_args', array($this, 'shortcode_args'));
        }
    }

    public function shortcode_args($args)
    {

        $args['load_css'] = 'inline';
        return $args;
    }

}

$mbelem = new MBElementor();
