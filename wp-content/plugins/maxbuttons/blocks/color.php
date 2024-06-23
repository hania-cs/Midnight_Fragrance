<?php
declare(strict_types=1);

namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$blockClass["color"] = "colorBlock";
$blockOrder[10][] = "color";


class colorBlock extends maxBlock
{
	protected $blockname = "color";
	protected $fields = array("text_color" => array("default" => "#ffffff",
													"css" => "color",
													"csspart" => "mb-text",
													'cssvalidate' => 'checkColor',
													),
						"text_shadow_color" => array("default" => "#505ac7",
													"css" => "text-shadow-color",
													"csspart" => "mb-text",
													'mixin' => 'textshadow',
													'cssvalidate' => 'checkColor',
													),
						"gradient_start_color" => array("default" => "#505ac7",
													"css" => "gradient-start-color",
													"mixin" => 'gradient',
													'cssvalidate' => 'checkColor',
													),
						"gradient_end_color" => array("default" => "#505ac7",
													"css" => "gradient-end-color",
													"mixin" => 'gradient',
													'cssvalidate' => 'checkColor',
													),
						"border_color" => array("default" => "#505ac7",
													"css" => "border-color",
													'cssvalidate' => 'checkBorderColor',
												    ),
						"box_shadow_color" => array("default" => "#333333",
													"css" => "box-shadow-color",
													'mixin' => 'boxshadow',
													'cssvalidate' => 'checkColor',
													),
						"text_color_hover" => array("default" => "#505ac7",
													"css" => "color",
													"csspart" => "mb-text",
													"csspseudo" => "hover",
													'cssvalidate' => 'checkColor',
													),
						"text_shadow_color_hover" => array("default" => "#333333",
													"css" => "text-shadow-color",
													"csspart" => "mb-text",
													"csspseudo" => "hover",
													'mixin' => 'textshadow',
													'cssvalidate' => 'checkColor',
						),

						"gradient_start_color_hover" => array("default" => "#ffffff",
													"css" => "gradient-start-color",
													"csspseudo" => "hover",
													"mixin" => 'gradient',
													'cssvalidate' => 'checkColor',
												),

						"gradient_end_color_hover" => array("default" => "#ffffff",
													"css" => "gradient-end-color",
													"csspseudo" => "hover",
													'mixin' => 'gradient',
													'cssvalidate' => 'checkColor',
													),

						"border_color_hover" => array("default" => "#505ac7",
													"css" => "border-color",
													"csspseudo" => "hover",
													'cssvalidate' => 'checkBorderColor',
						),

 						"box_shadow_color_hover" => array("default" => "#333333",
													"css" => "box-shadow-color",
													"csspseudo" => "hover",
													'mixin' => 'boxshadow',
													'cssvalidate' => 'checkColor',
													),

 						"icon_color" 	=> array( "default" => '#ffffff',
													"css" => "color",
													"csspart" => "mb-icon",
													'cssvalidate' => 'checkColor',
						),

						"icon_color_hover"		 => array( "default" => '#2b469e',
													"css" => "color",
													"csspart" => "mb-icon,mb-icon-hover",
													"csspseudo" => "hover",
													'cssvalidate' => 'checkColor',
													),
						);


	public function parse_css($css, $screens, string $mode = 'normal') {

		$data = $this->getBlockData();

		$css = parent::parse_css($css, $screens, $mode);


		$border_width = isset($this->data['border']['border_width']) ? $this->data['border']['border_width'] : 0;
		if ( intval($border_width) == 0) // if no border, then don't output other border properties.
		{
			unset($css['maxbutton']['normal']['border-color']);
			unset($css['maxbutton']['hover']['border-color']);
		}

		return $css;
	}

	protected function checkColor($value)
	{
		 if (! $value)  // if no color value, then transparent.
		 	 $value = 'rgba(0,0,0,0)';
		 elseif (! maxUtils::isrgba($value) && substr($value,0,1) !== '#')
		 {
				 $value = '#' . $value;
		 }

		 return $value;
	}

	protected function checkBorderColor($value, $field_id, $field_data, $screenObj)
	{
		 $border_width = $screenObj->getValue($screenObj->getFieldID('border_width'));

		 if ($border_width == 0)
		 {
			 return false;
		 }
		 else
		   return $this->checkColor($value);
	}

	public function admin_fields($screen)  {}
} // class
