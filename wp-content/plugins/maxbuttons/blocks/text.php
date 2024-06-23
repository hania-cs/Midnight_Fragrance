<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["text"] = "textBlock";
$blockOrder[50][] = "text";

class textBlock extends maxBlock
{

	protected $blockname = "text";
	protected $fields = array(

  					"text" =>   array("default" => '' ),
						"font" => array("default" => "Tahoma",
											  "css" => "font-family",
											  "csspart" => 'mb-text'
											  ),

						"font_size" => array("default" => "15px",
											  "css" => "font-size",
											  "csspart" => 'mb-text',
												'cssvalidate' => 'checkFontSize',
						),

						"text_align" => array(
										"default" => "center",
										 "css" => "text-align",
										 "csspart" => "mb-text",

										 ),

						"font_style" => array("default" => "normal",
											  "css" => "font-style",
											  "csspart" => 'mb-text',
												'unset_value' => 'normal',
						),
						"font_weight" => array("default" => "normal",
											  "css" => "font-weight",
											  "csspart" => 'mb-text',
												'unset_value' => 'normal',
											),
						"text_shadow_offset_left" => array("default" => "0px",
											  "css" => "text-shadow-left",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover",
												'mixin' => 'textshadow',
											  ),
						"text_shadow_offset_top" => array("default" => "0px",
											  "css" => "text-shadow-top",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover",
												'mixin' => 'textshadow',
												),
						"text_shadow_width" => array("default" => "0px",
											  "css" => "text-shadow-width",
											  "csspart" => 'mb-text',
											  "csspseudo" => "normal,hover",
												'mixin' => 'textshadow',
								),

						"padding_top" => array("default" => "18px",
											   "css" => "padding-top",
											   "csspart" => "mb-text"),
						"padding_right" => array("default" => "0px",
												"css" => "padding-right",
											   "csspart" => "mb-text"),
						"padding_bottom" => array("default" => "0px",
												"css" => "padding-bottom",
											   "csspart" => "mb-text"),
						"padding_left" => array("default" => "0px",
												"css" => "padding-left",
											   "csspart" => "mb-text")
						);


	public function __construct()
	{
		parent::__construct();
		$this->fields["text"]["default"] = __("YOUR TEXT","maxbuttons");
	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);

		$map["text"]["func"] = "updateAnchorText";
		$map["text_shadow_offset_left"]["func"] = "updateTextShadow";
		$map["text_shadow_offset_top"]["func"] = "updateTextShadow";
		$map["text_shadow_width"]["func"] = "updateTextShadow";

		return $map;
	}



	public function parse_css($css, $screens, string $mode = 'normal')
	{
		$css = parent::parse_css($css,$screens, $mode);

		// allow for font size not to be set, but default to theme
	/*	$font_size = isset($css["mb-text"]["normal"]["font-size"]) ? $css["mb-text"]["normal"]["font-size"] : $this->fields['font_size']['default'];
		if ($font_size == 0 || $font_size == '0px')
			unset($css["mb-text"]["normal"]["font-size"]); */

		$css["mb-text"]["normal"]["line-height"] = "1em";
		$css["mb-text"]["normal"]["box-sizing"] = "border-box";  // default.
		$css["mb-text"]["normal"]["display"] = "block";
		$css['mb-text']['normal']['background-color'] = 'unset'; // prevent bg overwriting

		return $css;
	}

	protected function checkFontSize($value)
	{
		 	if ($value == '' || intval($value) == 0)
				return false;

			return $value;
	}

	public function parse_button($domObj, $mode = 'normal')
	{
		$data = $this->getBlockData();
		$anchor = $domObj->find("a",0);

	 	if (isset($data["text"]) && $data["text"] != '' || $mode == 'preview')
		{
		/*	$allowed = allowed_tags();
			$allowed .= ' <br> ';
			$text = strip_tags($data['text'], $allowed); */
			$text = (isset($data["text"])) ? $data["text"] : "";
			$text = esc_html($text);
			$text = str_replace('\n', '<br>', $text);
			$anchor->innertext = "<span class='mb-text'>" . $text . "</span>";
		}
		return $domObj;

	}

	public function admin_fields($screen)
	{
		$data = $this->getBlockData();
	//	$admin = MB()->getClass('admin');

	/*	foreach($this->fields as $field => $options)
		{
 	 	    $default = (isset($options["default"])) ? $options["default"] : '';
			$$field = (isset($data[$field])) ? $data[$field] : $default;
			${$field  . "_default"} = $default;
		} */

		$color_copy_self = __("Replace color from other field", "maxbuttons");
		$color_copy_move  = __("Copy Color to other field", "maxbuttons");


				$start_block = new maxField('block_start');
				$start_block->name = __('shadow', 'maxbuttons');
				$start_block->label = __('Text Shadow', 'maxbuttons');
				$screen->addField($start_block);


 					// Shadow offset left
 					$field_shadow = new maxField('number') ;
					$field_shadow->label = __('Shadow Offset Left', 'maxbuttons');
					$field_shadow->id = $screen->getFieldID('text_shadow_offset_left');
					$field_shadow->value = maxUtils::strip_px($screen->getValue($field_shadow->id));

					$field_shadow->name = $field_shadow->id;
					$field_shadow->inputclass = 'tiny';
					$screen->addField($field_shadow, 'start');

					// Shadow offset top
 					$field_shadow = new maxField('number') ;
					$field_shadow->label = __('Shadow Offset Top', 'maxbuttons');
					$field_shadow->id = $screen->getFieldID('text_shadow_offset_top');
					$field_shadow->value = maxUtils::strip_px($screen->getValue($field_shadow->id));

					$field_shadow->name = $field_shadow->id;
					$field_shadow->inputclass = 'tiny';
					$screen->addField($field_shadow, '', 'end');

					// Shadow width
 					$field_shadow = new maxField('number') ;
					$field_shadow->label = __('Shadow Blur', 'maxbuttons');
					$field_shadow->id = $screen->getFieldID('text_shadow_width');
					$field_shadow->name = $field_shadow->id;
					$field_shadow->value = maxUtils::strip_px($screen->getValue($field_shadow->id));
					$field_shadow->min = 0;
					$field_shadow->inputclass = 'tiny';
					$screen->addField($field_shadow, 'start', 'end');

 					// Text Color
 					$fshadow = new maxField('color');
 					$fshadow->id = $screen->getFieldID('text_shadow_color');
 					$fshadow->name = $fshadow->id;
 					$fshadow->value = $screen->getColorValue($fshadow->id);
 					$fshadow->label = __('Shadow Color','maxbuttons');
 					$fshadow->copycolor = true;
 					$fshadow->bindto = $screen->getFieldID('text_shadow_color_hover');
 					$fshadow->copypos = 'right';
					$fshadow->left_title = $color_copy_self;
					$fshadow->right_title = $color_copy_move;
					$screen->addField($fshadow, 'start');

 					// Text Color Hover
 					$fshadow_hover = new maxField('color');
 					$fshadow_hover->id = $screen->getFieldID('text_shadow_color_hover');
 					$fshadow_hover->name = $fshadow_hover->id;
 					$fshadow_hover->value = $screen->getColorValue($fshadow_hover->id);
 					$fshadow_hover->label = __('Hover','maxbuttons');
 					$fshadow_hover->copycolor = true;
 					$fshadow_hover->bindto = $screen->getFieldID('text_shadow_color');
 					$fshadow_hover->copypos = 'left';
					$fshadow_hover->left_title = $color_copy_move;
					$fshadow_hover->right_title = $color_copy_self;
					$screen->addField($fshadow_hover, '', 'end');

					$this->sidebar($screen);
					$endblock = new maxField('block_end');
					$screen->addField($endblock);

			 } // admin fields
	} // class

?>
