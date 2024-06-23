<?php
declare(strict_types=1);

namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["container"] = "containerBlock";
$blockOrder[70][] = "container";

//use \simple_html_dom as simple_html_dom;

class containerBlock extends maxBlock
{
	protected $blockname = "container";

	protected $fields = array("container_enabled" => array("default" => "0"),
						"container_center_div_wrap" => array("default" => "0"),

						"container_width" => array("default" => "0",
												   "css" => "width",
												   "csspart" => "mb-container",
													 'unitfield' => 'container_width_unit',
						),
						'container_width_unit' => array('default' => 'pixel'),
						"container_margin_top" => array("default" => "0px",
													"css" => "margin-top",
													"csspart" => "mb-container"),
						"container_margin_right" => array("default" => "0px",
												   "css" => "margin-right",
												   "csspart" => "mb-container"),
						"container_margin_bottom" => array("default" => "0px",
												   "css" => "margin-bottom",
												   "csspart" => "mb-container"),
						"container_margin_left" => array("default" => "0px",
												   "css" => "margin-left",
												   "csspart" => "mb-container"),
						"container_alignment" => array("default" => "",
												   "css" => "align",
												   "csspart" => "mb-container"),
						);


	public function parse_button($domObj, $mode = 'normal')
	{
		$data = $this->getBlockData();
		$id = $this->data["id"];

 		if ($mode == 'editor')
 			return $domObj; // in previews no container object

		if (isset($data["container_enabled"]) && $data["container_enabled"] == 1)
		{
			$anchor = $domObj->find("a",0);
			$anchor->outertext = "<span class='maxbutton-" . $id . "-container mb-container'>" . $anchor->outertext . "</span>";


			if ($data["container_center_div_wrap"] == 1) // I heard you like wrapping...
			{
				$anchor->outertext = "<span class='mb-center maxbutton-" . $id . "-center'>" . $anchor->outertext . "</span>";

			}
			// reload the dom model with new divs
			$newhtml = $domObj->save();
			$domObj =  new simple_html_dom();
			$domObj->load($newhtml);
		}

		return $domObj;
	}

	public function parse_css($css, $screens, string $mode = 'normal')
	{
		$css = parent::parse_css($css, $screens, $mode);
		$data = $this->getBlockData();

		$css["mb-container"]["normal"]["display"] = "block";
		$css["mb-center"]["normal"]["display"] = "block";
		$css["mb-center"]["normal"]["text-align"] = "center";

	 if (isset($css['mb-container']['normal']['align']) && $css['mb-container']['normal']['align'] != '')
	 {
			$stat = explode(":", $css['mb-container']['normal']["align"]);
			$css['mb-container']['normal'][ $stat[0] ] = $stat[1];
			unset($css['mb-container']['normal']["align"]);
	 }
	 if ( isset($css['mb-container']['normal']["width"]) && $data["container_width"] == 0)
	 {
		 unset($css['mb-container']['normal']["width"]);
	 }



		foreach($screens as $screen)
		{
			if ($screen->id == 'default')
				continue;

			if (! isset($css['mb-container']['responsive']) || ! isset($css['mb-container']['responsive'][$screen->id]) || ! isset($css['mb-container']['responsive'][$screen->id]['normal']) )
			{
				continue;
			}

			 if (isset($css['mb-container']['responsive'][$screen->id]['normal']['align']) && $css['mb-container']['responsive'][$screen->id]['normal']['align'] != '')
			 {
					$stat = explode(":", $css['mb-container']['responsive'][$screen->id]['normal']["align"]);
					$css['mb-container']['responsive'][$screen->id]['normal'][ $stat[0] ] = $stat[1];
					unset($css['mb-container']['responsive'][$screen->id]['normal']["align"]);
			 }
		}

		return $css;
	}


	public function map_fields($map)
	{
		$map = parent::map_fields($map);
		$map["container_width_unit"]["func"] = "updateContainerUnit";
		return $map;
	}

	public function admin_fields($screen)
	{
		$data = $this->getBlockData();

		/*foreach($this->fields as $field => $options)
		{
 	 	    $default = (isset($options["default"])) ? $options["default"] : '';
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default;


		} */

		 $maxbuttons_container_alignments = array(
	'' => '',
	'display: inline-block' => 'display: inline-block',
	'float: left' => 'float: left',
	'float: right' => 'float: right'
);
	  if ($screen->is_responsive())
		{
			$maxbuttons_container_alignments['float: none'] = 'float: none';
		}

		$icon_url = MB()->get_plugin_url() . 'images/icons/' ;

				$start_block = new maxField('block_start');
				$start_block->name = __('container', 'maxbuttons');
				$start_block->label = __('Container', 'maxbuttons');
				$screen->addField($start_block);


				$u_container = new maxField('switch');
				$u_container->label = __('Use Container', 'maxbuttons');
				$u_container->name = $screen->getFieldID('container_enabled');
				$u_container->id = $u_container->name;
				$u_container->help = __('Creates a container around the button which pushes other content', 'maxbuttons');
				$u_container->is_responsive = false;
				$u_container->value = 1;

				$u_container->checked = checked( $screen->getValue($u_container->id), 1, false);
        $screen->addField($u_container, 'start', 'end');

				$fspacer = new maxField('spacer');
				$fspacer->name = '';
				$fspacer->is_responsive = false;
        $screen->addField($fspacer, 'start');

				$wrap_cont = new maxField('switch');
				$wrap_cont->name = $screen->getFieldID('container_center_div_wrap');
				$wrap_cont->id = $wrap_cont->name;
				$wrap_cont->value = 1;
				$wrap_cont->checked = checked( $screen->getValue($wrap_cont->name), 1, false);
				$wrap_cont->label = __('Center the container', 'maxbuttons');
				$wrap_cont->is_responsive = false;
        $screen->addField($wrap_cont, '', 'end');

				$unit = $screen->getValue($screen->getFieldID('container_width_unit'));
				$unit = ($unit == 'percent') ? '%' : 'px';


				$container_width = new maxField('number');
				$container_width->name = $screen->getFieldID('container_width');
				$container_width->id = $container_width->name;
				$container_width->min = 0;
				$container_width->value = maxUtils::strip_px( $screen->getValue($container_width->id) );
				$container_width->label = __('Width', 'maxbuttons');
				$container_width->inputclass = 'small';
				$container_width->after_input = '<span class="unit">' . $unit . '</span>';
        $screen->addField($container_width, 'start', 'end');

				$size_spacer = new maxField('spacer');
				$size_spacer->label = __('Width Unit', 'maxbuttons');
				$size_spacer->name = 'size_unit_spacer';

				$screen->addField($size_spacer, 'start', '');

				// Units for width
				$cwidth_unit_px = new maxField('radio');
				$cwidth_unit_px->label = __('px', 'maxbuttons');
				$cwidth_unit_px->name = $screen->getFieldID('container_width_unit');
				$cwidth_unit_px->id = $screen->getFieldID('cwidth_unit_px');
				$cwidth_unit_px->value = 'pixel';
				//$wsize_unit_px->before_input = '<label>width</label>';
				$cwidth_unit_px->checked = checked( $screen->getValue($cwidth_unit_px->name), 'pixel', false);
				$screen->addField($cwidth_unit_px, 'group_start', '');

				$cwidth_unit_perc = new maxField('radio');
				$cwidth_unit_perc->label = __('%', 'maxbuttons');
				$cwidth_unit_perc->name = $screen->getFieldID('container_width_unit');
				$cwidth_unit_perc->id = $screen->getFieldID('cwidth_unit_perc');
				$cwidth_unit_perc->value = 'percent';
				$cwidth_unit_perc->checked = checked( $screen->getValue($cwidth_unit_perc->name), 'percent', false);
				$screen->addField($cwidth_unit_perc, '', array('group_end', 'end'));

			 		// Margin - trouble
			 		$ptop = new maxField('number');
			 		$ptop->label = __('Margin', 'maxbuttons');
			 		$ptop->id = $screen->getFieldID('container_margin_top');
			 		$ptop->name = $ptop->id;
 					$ptop->min = 0;
			 		$ptop->inputclass = 'tiny';
			 		$ptop->before_input = '<img src="' . $icon_url . 'p_top.png" title="' . __("Margin Top","maxbuttons") . '" >';
			 		$ptop->value = maxUtils::strip_px($screen->getValue($ptop->id));
         	$screen->addField($ptop, 'start');

			 		$pright = new maxField('number');
			 		$pright->id = $screen->getFieldID('container_margin_right');
			 		$pright->name = $pright->id;
 					$pright->min = 0;
			 		$pright->inputclass = 'tiny';
			 		$pright->before_input = '<img src="' . $icon_url . 'p_right.png" class="icon padding" title="' . __("Margin Right","maxbuttons") . '" >';
			 		$pright->value = maxUtils::strip_px($screen->getValue($pright->id));
          $screen->addField($pright);

			 		$pbottom = new maxField('number');
			 		$pbottom->id = $screen->getFieldID('container_margin_bottom');
			 		$pbottom->name = $pbottom->id;
 					$pbottom->min = 0;
			 		$pbottom->inputclass = 'tiny';
			 		$pbottom->before_input = '<img src="' . $icon_url . 'p_bottom.png" class="icon padding" title="' . __("Margin Bottom","maxbuttons") . '" >';
			 		$pbottom->value = maxUtils::strip_px($screen->getValue($pbottom->id));
        	$screen->addField($pbottom);

			 		$pleft = new maxField('number');
			 		$pleft->id = $screen->getFieldID('container_margin_left');
			 		$pleft->name = $pleft->id;
 					$pleft->min = 0;
			 		$pleft->inputclass = 'tiny';
			 		$pleft->before_input = '<img src="' . $icon_url . 'p_left.png" class="icon padding" title="' . __("Margin Left","maxbuttons") . '" >';
			 		$pleft->value = maxUtils::strip_px($screen->getValue($pleft->id));
          $screen->addField($pleft, '', 'end');

					$align = new maxField('option_select');
	 				$align->label = __('Alignment','maxbuttons');
	 				$align->name = $screen->getFieldID('container_alignment');
	 				$align->id = $align->name;
	 				$align->selected = $screen->getValue($align->id);
	 				$align->options = $maxbuttons_container_alignments; // maxUtils::selectify($align->name, $maxbuttons_container_alignments, $align->value);
					$align->help = __('Float can help to align the button and other content on the same line', 'maxbuttons');
          $screen->addField($align, 'start', 'end');

					$this->sidebar($screen);
					$endblock = new maxField('block_end');
					$screen->addField($endblock);

				} // admin_fields

} // class


?>
