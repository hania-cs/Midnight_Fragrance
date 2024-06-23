<?php
declare(strict_types=1);

namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["dimension"] = "dimensionBlock";
$blockOrder[20][] = "dimension";

class dimensionBlock extends maxBlock
{
	protected $blockname = "dimension";
	protected $fields = array("button_width" => array("default" => '160',
										"css" => "width",
										'cssvalidate' => 'checkDimension',  // function reference to validator.
										'unitfield' => 'button_size_unit_width',

										),
							  "button_height" => array("default" => '50',
							  			"css" => "height",
											'cssvalidate' => 'checkDimension',
											'unitfield' => 'button_size_unit_height',
								),
								"button_size_unit_width" => array(
											'default' => 'pixel',
											'is_sub_of' => 'button_width',

								),
								"button_size_unit_height" => array(
											'default' => 'pixel',
											'is_sub_of' => 'button_height',


								),
		);


/*	public function parse_css($css, $mode = 'normal', $screens)
	{

		$data = $this->data[$this->blockname];
		$css = parent::parse_css($css, $mode, $screens);

		return $css;
	} */

	protected function checkDimension($value, $field_id)
	{
		if (intval($value) == 0) // if zero, put auto to accomodate every screen.
		{
				return 'auto';
		}

		 return $value;
	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);

		$map["button_width"]["func"] = "updateDimension";
		$map["button_height"]["func"] = "updateDimension";
		$map["button_size_unit_width"]["func"] = "updateDimension";
		$map["button_size_unit_height"]["func"] = "updateDimension";
		return $map;

	}

	public function admin_fields($screen)
	{
	} // admin_fields

} // class
