<?php
declare(strict_types=1);

namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["border"] = "borderBlock";
$blockOrder[30][] = "border";

class borderBlock extends maxBlock
{
	protected $blockname = "border";
	protected $fields = array(
						"radius_top_left" => array("default" => "4px",
									  "css" => "border-top-left-radius",
										'mixin' => 'radius',
									),
						"radius_top_right" => array("default" => "4px",
											 "css" => "border-top-right-radius",
											 'mixin' => 'radius',
											 ),
						"radius_bottom_left" => array("default" => "4px",
													 "css" => "border-bottom-left-radius",
													 'mixin' => 'radius',
											),
						"radius_bottom_right" => array("default" => "4px",
													 "css" => "border-bottom-right-radius",
													 'mixin' => 'radius',
													 ),
						"border_style" => array("default" => "solid",
												"css" => "border-style",
												'cssvalidate' => 'checkBorder',
												),
						"border_width" => array("default" => "2px",
												 "css" => "border-width"
												 ),
						"box_shadow_offset_left" => array("default" => "0px",
												 	"css" => "box-shadow-offset-left",
												 	"csspseudo" => "normal,hover",
													'mixin' => 'boxshadow',
												 ),
						"box_shadow_offset_top" => array("default" => "0px",
													"css" => "box-shadow-offset-top",
													"csspseudo" => "normal,hover",
													'mixin' => 'boxshadow',

												),
						"box_shadow_width" => array("default" => "2px",
													"css" => "box-shadow-width",
													"csspseudo" => "normal,hover",
													'mixin' => 'boxshadow',
												),
						'box_shadow_spread' => array('default' => '0px',
													'css' => 'box-shadow-spread',
													'csspseudo' => 'normal,hover',
													'mixin' => 'boxshadow',
												),

						);


	public function map_fields($map)
	{
		$map = parent::map_fields($map);
		$map["box_shadow_offset_left"]["func"] = "updateBoxShadow";
		$map["box_shadow_offset_top"]["func"] = "updateBoxShadow";
		$map["box_shadow_width"]["func"] = "updateBoxShadow";
		$map["box_shadow_spread"]["func"] = "updateBoxShadow";

		$map["radius_top_left"]["func"] = "updateRadius";
		$map["radius_top_right"]["func"] = "updateRadius";
		$map["radius_bottom_left"]["func"] = "updateRadius";
		$map["radius_bottom_right"]["func"] = "updateRadius";

		return $map;
	}


	protected function checkBorder($value, $field_id, $field_data, $screenObj)
	{
		$border_width = $screenObj->getValue($screenObj->getFieldID('border_width'));

		if ($border_width == 0)
		{
			return false;
		}

		return $value;
	}

	public function admin_fields($screen)
	{

		$data = $this->getBlockData();
		foreach($this->fields as $field => $options)
		{
 	 	    $default = (isset($options["default"])) ? $options["default"] : '';
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default;
		}

		 $maxbuttons_border_styles = array(
			'' => '',
			'dashed' => __('dashed','maxbuttons'),
			'dotted' => __('dotted','maxbuttons'),
			'double' => __('double','maxbuttons'),
			'groove' => __('groove','maxbuttons'),
			'inset'  => __('inset','maxbuttons'),
			'outset' => __('outset','maxbuttons'),
			'ridge'  => __('ridge','maxbuttons'),
			'solid'  => __('solid','maxbuttons')
		);

		$color_copy_self = __("Replace color from other field", "maxbuttons");
		$color_copy_move  = __("Copy Color to other field", "maxbuttons");

	//	$admin = MB()->getClass('admin');

				$start_block = new maxField('block_start');
				$start_block->name = __('border', 'maxbuttons');
				$start_block->label = __('Border', 'maxbuttons');
				$screen->addField($start_block);


					// Spacer
				$fspacer = new maxField('spacer');
				$fspacer->label = __('Radius','maxbuttons');
				$fspacer->name = 'radius';
				$screen->addField($fspacer, 'start');

				// Radius left top
				$radius_tleft = new maxField('number');
				$radius_tleft->id = $screen->getFieldID('radius_top_left');
				$radius_tleft->name = $radius_tleft->id;
				$radius_tleft->value = maxUtils::strip_px($screen->getValue($radius_tleft->id));
				$radius_tleft->min = 0;
				$radius_tleft->inputclass = 'tiny';
				$radius_tleft->publish = false;
				$rtl = $radius_tleft->output('');
				$screen->addField($radius_tleft, '', '');

				// Radius right top
				$radius_tright = new maxField('number');
				$radius_tright->id = $screen->getFieldID('radius_top_right');
				$radius_tright->value = maxUtils::strip_px($screen->getValue($radius_tright->id));
				$radius_tright->name = $radius_tright->id;
				$radius_tright->min = 0;
				$radius_tright->inputclass = 'tiny';
				$radius_tright->publish = false;
				$rtr = $radius_tright->output('', '');
				$screen->addField($radius_tright, '', '');

				// Radius bottom left
				$radius_bleft = new maxField('number');
				$radius_bleft->id = $screen->getFieldID('radius_bottom_left');
				$radius_bleft->value = maxUtils::strip_px($screen->getValue($radius_bleft->id));
				$radius_bleft->name = $radius_bleft->id;
				$radius_bleft->min = 0;
				$radius_bleft->inputclass = 'tiny';
				$radius_bleft->publish = false;
				$rbl = $radius_bleft->output('');
				$screen->addField($radius_bleft, '', '');

				// Radius bottom right
				$radius_bright = new maxField('number');
				$radius_bright->id = $screen->getFieldID('radius_bottom_right');
				$radius_bright->value = maxUtils::strip_px($screen->getValue($radius_bright->id));
				$radius_bright->name = $radius_bright->id;
				$radius_bright->min = 0;
				$radius_bright->inputclass = 'tiny';
				$radius_bright->publish = false;
				$rbr = $radius_bright->output('', '');
				$screen->addField($radius_bright, '', '');

				// If all same, lock the corners for simultanious change.
				if ($radius_tleft->value == $radius_tright->value &&
					$radius_tright->value == $radius_bleft->value &&
					$radius_bleft->value = $radius_bright->value)
				{
					$lock = 'lock';
				}
				else
					$lock = 'unlock';

				$radius = new maxField('radius');
				$radius->id = $screen->getFieldID('radius_toggle');
				$radius->radius_tl = $rtl;
				$radius->label_tl = __('Top Left','maxbuttons');
				$radius->radius_tr = $rtr;
				$radius->label_tr = __('Top Right','maxbuttons');
				$radius->radius_bl = $rbl;
				$radius->label_bl = __('Bottom Left','maxbuttons');
				$radius->radius_br = $rbr;
				$radius->label_br = __('Bottom Right','maxbuttons');
				$radius->lock = $lock;
				$screen->addField($radius, '', 'end');

				// Border style
				$bstyle = new maxField('generic');
 				$bstyle->label = __('Style','maxbuttons');
 				$bstyle->name = $screen->getFieldID('border_style');
 				$bstyle->id = $bstyle->name;
 				$bstyle->value= $screen->getValue($bstyle->id);
 				$bstyle->setDefault($screen->getDefault('border_style'));
 				$bstyle->content = maxUtils::selectify($bstyle->name, $maxbuttons_border_styles, $bstyle->value);
				$screen->addField($bstyle, 'start', 'end');

				// Border width
				$bwidth = new maxField('number');
				$bwidth->label = __('Width', 'maxbuttons');
				$bwidth->name = $screen->getFieldID('border_width');
				$bwidth->id = $bwidth->name;
				$bwidth->value = maxUtils::strip_px( $screen->getValue($bwidth->id) );
				$bwidth->min = 0;
				$bwidth->after_input = __('px', 'maxbuttons');
				$bwidth->inputclass = 'tiny';
				$screen->addField($bwidth, 'start', 'end');

				// Border Color
				$bcolor = new maxField('color');
				$bcolor->id = $screen->getFieldID('border_color');
				$bcolor->name = $bcolor->id;
				$bcolor->value = $screen->getColorValue($bcolor->id);
				$bcolor->label = __('Border Color','maxbuttons');
				$bcolor->copycolor = true;
 				$bcolor->bindto = $screen->getFieldID('border_color_hover');
				$bcolor->copypos = 'right';
				$bcolor->left_title = $color_copy_self;
				$bcolor->right_title = $color_copy_move;
				$screen->addField($bcolor ,'start');

				// Border Color Hover
				$bcolor_hover = new maxField('color');
				$bcolor_hover->id = $screen->getFieldID('border_color_hover');
				$bcolor_hover->name = $bcolor_hover->id;
				$bcolor_hover->value = $screen->getColorValue($bcolor_hover->id);
				$bcolor_hover->label = __('Hover','maxbuttons');
				$bcolor_hover->copycolor = true;
				$bcolor_hover->bindto = $screen->getFieldID('border_color');
				$bcolor_hover->copypos = 'left';
				$bcolor_hover->left_title = $color_copy_move;
				$bcolor_hover->right_title = $color_copy_self;
				$screen->addField($bcolor_hover, '', 'end');

				// Shadow offset left
				$bshadow = new maxField('number');
				$bshadow->label = __('Shadow Offset Left','maxbuttons');
				$bshadow->name = $screen->getFieldID('box_shadow_offset_left');
				$bshadow->id = $bshadow->name;
				$bshadow->value = maxUtils::strip_px( $screen->getValue($bshadow->id) );
				$bshadow->inputclass = 'tiny';
				$screen->addField($bshadow, 'start');

				// Shadow offset top
				$bshadow = new maxField('number');
				$bshadow->label = __('Shadow Offset Top','maxbuttons');
				$bshadow->name = $screen->getFieldID('box_shadow_offset_top');
				$bshadow->id = $bshadow->name;
				$bshadow->value = maxUtils::strip_px( $screen->getValue($bshadow->id) );
				$bshadow->inputclass = 'tiny';
				$screen->addField($bshadow, '', 'end');

				// Shadow width
				$bshadow = new maxField('number');
				$bshadow->label = __('Shadow Blur','maxbuttons');
				$bshadow->name = $screen->getFieldID('box_shadow_width');
				$bshadow->id = $bshadow->name;
				$bshadow->value = maxUtils::strip_px( $screen->getValue($bshadow->id) );
				$bshadow->inputclass = 'tiny';
				$bshadow->min = 0;
				$screen->addField($bshadow, 'start', '');

				$bspread = new maxField('number');
				$bspread->label = __('Shadow Spread', 'maxbuttons');
				$bspread->id = $screen->getFieldID('box_shadow_spread');
				$bspread->value = maxUtils::strip_px($screen->getValue($bspread->id));
				$bspread->name = $bspread->id;
				$bspread->inputclass = 'tiny';
				$screen->addField($bspread, '', 'end');

				// Border Shadow Color
				$scolor = new maxField('color');
				$scolor->id = $screen->getFieldID('box_shadow_color');
				$scolor->name = $scolor->id;
				$scolor->value = $screen->getColorValue($scolor->id);
				$scolor->label = __('Border Shadow Color','maxbuttons');
				$scolor->copycolor = true;
				$scolor->bindto = $screen->getFieldID('box_shadow_color_hover');
				$scolor->copypos = 'right';
				$scolor->left_title = $color_copy_self;
				$scolor->right_title = $color_copy_move;
				$screen->addField($scolor, 'start');

				// Border Shadow Color Hover
				$scolor_hover = new maxField('color');
				$scolor_hover->id = $screen->getFieldID('box_shadow_color_hover');
				$scolor_hover->name = $scolor_hover->id;
				$scolor_hover->value = $screen->getColorValue($scolor_hover->id);
				$scolor_hover->label = __('Hover','maxbuttons');
				$scolor_hover->copycolor = true;
				$scolor_hover->bindto = $screen->getFieldID('box_shadow_color');
				$scolor_hover->copypos = 'left';
				$scolor_hover->left_title = $color_copy_self;
				$scolor_hover->right_title = $color_copy_move;
				$screen->addField($scolor_hover, '','end');

				$this->sidebar($screen);
				$endblock = new maxField('block_end');
				$screen->addField($endblock);

			} // admin fields

  } // class
