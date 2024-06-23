<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

/** A block is a combination of related settings.
*
*  Blocks are grouped and put into the same Database table row. This way related data, it's executing, display and other decision
*   making is seperate from other blocks improving realiability and readability of the code.
*/
	use MaxButtons\maxBlocks  as maxBlocks;
	use MaxButtons\maxField   as maxField;

abstract class maxBlock
{
	protected $data = array();
	protected $is_default = true; // exclude  / include a whole block in default screen
	protected $is_responsive = true; // exclude / include a whole block in responsive screens.
	protected $is_new = false; // only display when adding a screen.

	/** Block constructor
	*
	* Constructor for a button block. Hooks up all needed filters and inits data for a block.
	*
	*/
	public function __construct($priority = 10)
	{
		$this->fields = apply_filters($this->blockname. "-block-fields",$this->fields);
		$this->data[$this->blockname] = array(); //empty init
	}

	/** Save fields runs the POST variable through all blocks
	*
	*  Taking the post variable from the form, the function will attach the submitted data to the block - field logic and
	*   return a data object to save to the Database. If no value is submitted, the default will be loaded.
	*
	*	@param $data Array Data in blockname - field logic format
	*	@param $post Array $_POST style data
	*
	*	@return $data Array
	*/
	public function save_fields($data, $post, $screens)
	{
		$block = isset($data[$this->blockname]) ? $data[$this->blockname] : array();

		foreach($this->fields as $field => $options)
		{
			$block[$field]= $this->getFieldSaveValue($field, $options, $post, false);

			if (is_array($screens))
			{
					foreach($screens as $screenObj)
					{
						if ($screenObj->is_responsive())
						{
							$r_field_id = $screenObj->getFieldID($field);
							$r_field_val = $this->getFieldSaveValue($r_field_id, $options, $post, true);

							// if responsive value is different than the main field -> changes has been made, only save changes.
							if ($r_field_val !== false && $r_field_val != $block[$field])
							{
									$block[$r_field_id] = $r_field_val;
							}
							elseif (isset($block[$r_field_id])) // if field is same as main, but exists (changing values), remove it.
							{
								unset($block[$r_field_id]);
							}
						}
					}
			}

		}
		$data[$this->blockname] = $block;
		return $data;
	}

	/**
	* @param $field ID of the field ( with screen prefix )
	* @param $options Options contained in the field.
	* @param $post  Array of what was in the post
	* @param $is_responsive This field is part of a responsive screen
	*/
	private function getFieldSaveValue($field, $options, $post, $is_responsive = false)
	{

		$default = (isset($options["default"])) ? $options["default"] : '';

		if ($is_responsive)
			$default = false; // don't write defaults for responsive screens, omit.

		$value = $default;

		if (is_string($default) && strpos($default,"px") !== false)
			$value  = (isset($post[$field]) ) ? intval($post[$field]) : $default;
		elseif( isset($post[$field]) && is_array($post[$field]))
		{
			$value = $post[$field];
		}
		elseif (isset($post[$field]))
		{
			$value = sanitize_text_field($post[$field]);
		}
		elseif (isset($options['unset_value'])) // option when field is unset in post, give it a value still ( checkboxes etc ) . Overrides default.
		{
			$value = $options['unset_value'];
		}

		return $value;
	}

	/** Return fields of current block
	*
	* 	Will return fields of current block only
	* @return Array $fields
	*/
	public function get_fields()
	{
		return $this->fields;
	}

	/** Returns Blockname of current block
	*
	*
	* @return $string | boolean Name of the block, if set, otherwise false
	*/
	public function get_name()
	{
		if (isset($this->blockname))
			return $this->blockname;

		return false;
	}

	public function is_responsive()
	{
			return $this->is_responsive;
	}

	public function is_default()
	{
		 return $this->is_default;
	}

	public function is_new()
	{
		 return $this->is_new;
	}

	/* Build the Block admin interface
	*
	*   Builds admin interface via Admin class ( addfield ). After building, display fields should be called.
	*  @abstract
	*/
	abstract public function admin_fields($screen);


	/** Adding a sidebar **
	*
	* This function can be used to add a sidebar to the block. The sidebar should be called before display_fields is put out but after the last field
	*
	* */
	public function sidebar($screen)
	{
		return;
	}

	/** Parse HTML portion of button
	*
	*   This filter is passed through to modify the HTML parts of the button.
	*
	*   Note: When changing parts of the DomObj writing new tags / DOM to elements, it's needed to regenerate the Object.
	*
	*   @param $button DomObj SimpleDOMObject
	*   @param $mode String[normal|preview] Flag to check if loading in preview
	*
	*   @return DomObj
	*/
	public function parse_button($button, $mode) { return $button;  }
	public function post_parse_button($domObj, $mode) { return $domObj; }

	/* Parse CSS of the button
	*
	*	This function will go through the blocks, matching the $css definitions of a fields and putting them in the
	*	correct tags ( partly using csspart ) .
	*
	*	@param $css Array [normal|hover|other][cssline] = css declaration
	*	@param $mode String [normal|preview]
	*
	*	@return $css Array
	*/
	public function parse_css($css, $screens, string $mode = 'normal') {

		$data = $this->getBlockData();

 		// get all fields from this block
		foreach($screens as $screenObj) // these are our screens.
		{
			$mixins = array();

			foreach($this->fields as $field => $field_data)
			{
 						 $field_id = $screenObj->getFieldID($field);
						 if (isset($data[$field_id]) )
						 {

							 	$css = $this->parseField($css, $field_id, $data[$field_id], $field_data, $screenObj);
								if (isset($field_data['mixin']) && $screenObj->is_responsive()) // garantuee the whole mixin is present.
								{
										$mixin = $field_data['mixin'];
									 	maxBlocks::addMixin($mixin, $field, $screenObj->id);
								}
						 }

			 } // fields

			 // check the mixins. This checks the field, not field_id since for responsive it'll need to add from the default screen when completing the data.
		}  // screens

		return $css;
	}

	public function parsefix_mixins($css, $screens)
	{
//		$mixins = MaxBLocks::getMixins();
		$data = $this->getBlockData();

		foreach($screens as $screenObj) // these are our screens.
		{
				if (! $screenObj->is_responsive())
					continue;

						foreach($this->fields as $field => $field_data)
						{
							 $field_id = $screenObj->getFieldID($field);
							 if (isset($field_data['mixin']))
							 {
									$mixarray = maxBlocks::getMixins($field_data['mixin'], $screenObj->id); //$mixins[$field_data['mixin']];

									if (count($mixarray) == 0) // mixin not in use.
										continue;

									if (! in_array($field, $mixarray))
									{
										$mixvalue = isset($data[$field]) ? $data[$field] : ''; // get extra data from the default screen
										$css = $this->parseField($css, $field, $mixvalue, $field_data, $screenObj);
									}
							 }
						}
			// }
		 }
		 return $css;

	}

	/** Parses a field.
	*
	* @param $css The CSS collection Being parsed
	* @param $field_id The name of the field, this can be with screen_id prefixed
	* @param $value The value of the field.
	* @param $field_data Data of the field
	* @param $screenObj Object of the current screen.
	* @return Array CSS after parsed field.
	*/
	protected function parseField($css, $field_id, $value, $field_data, $screenObj)
	{
		// get cssparts, can be comma-seperated value
		$csspart = (isset($field_data["csspart"])) ? explode(",",$field_data["csspart"]) : array('maxbutton');
		$csspseudo = (isset($field_data["csspseudo"])) ? explode(",", $field_data["csspseudo"]) : 'normal';

		$is_responsive = ($screenObj->is_responsive() ) ? true : false;
		$screen_id = $screenObj->id;

		// if this field has a css property
		if (isset($field_data["css"]))
		{

			// value can be int or otherwise.
			if (is_string($value))
			{
				$value = str_replace(array(";"), '', $value);  //sanitize
			}
			else {
				$value = strval($value); // cast to string for further checks
			}

			if (isset($field_data['unitfield']) && ! strpos($value,"px"))
			{
				if ($value == '') $value = '0'; // pixel values, no empty but 0
				 $unitfield = $screenObj->getFieldID($field_data['unitfield']);

				 if (isset($this->data[$this->blockname][$unitfield]))
				 {
					  $unit = $this->data[$this->blockname][$unitfield]; // unitfield found
				 }
				 elseif ($is_responsive && isset($this->data[$this->blockname][$field_data['unitfield']])) // Unitfield is not stored if same as main, but can be non-default (e.g. % )
				 {
					  $unit = $this->data[$this->blockname][$field_data['unitfield']];
				 }
				 else
				 	  $unit =false;

				 $value .= ($unit  == 'percent') ? '%' : 'px';

			}
			elseif (isset($field_data["default"]) && strpos($field_data["default"],"px") && ! strpos($value,"px"))
			{
				if ($value == '')
				{
					 $value = '0'; // pixel values, no empty but 0
				}
				$value .= "px";
			}
			elseif (isset($field_data["default"]) && strpos($field_data["default"],"%") && ! strpos($value,"%"))
			{
				if ($value == '') $value = '0'; // pixel values, no empty but 0
				$value .= "%";
			}

//echo "VALUE AFTER $field_id - $value <BR>";
			/** CSSvalidate is a function reference for further shaping the value based on specific wishes.
			* Can return a new value, or false which indicates removal. This aims to replace block specific parse_css functions.
			*/
			if (isset($field_data['cssvalidate']))
			{
					$value = $this->{$field_data['cssvalidate']}($value, $field_id, $field_data, $screenObj);
					if ($value == false) // don't add this field anywhere.
						return $css;
			}

			 foreach($csspart as $part)
			 {
					if (is_array($csspseudo))
					{
						foreach($csspseudo as $pseudo)
						{
							if ($is_responsive)
								$css[$part]['responsive'][$screen_id][$pseudo][$field_data["css"]] = $value ;
							else
								$css[$part][$pseudo][$field_data["css"]] = $value ;
						}
					}
					else
					{
						if ($is_responsive)
							$css[$part]['responsive'][$screen_id][$csspseudo][$field_data["css"]] = $value ;
						else
							$css[$part][$csspseudo][$field_data["css"]] = $value ;
					}
				}
		}

		return $css;
	}

	/* Ability to output custom JS for each button */
	public function parse_js($js, $mode = 'normal')
	{
		return $js;
	}


	/** Map the Block fields
	*
	*	This function will take the field name and link it to the defined CSS definition to use in providing the live preview in the
	*	button editor. I.e. a field with name x will be linked to CSS-property X . Or to a custom Javascript function.
	*
	*	@param $map Array [$field_id][css|attr|func|] = property/function
	*
	*	@return Array
	*/
	public function map_fields($map)
	{
		foreach($this->fields as $field => $field_data)
		{
 			if (isset($field_data["css"]))
			{
				$cssdef = $field_data["css"];
				$multidef = explode('-',$cssdef);
				if ( count($multidef) > 1)
				{
					$cssdef = "";
 					for($i = 0; $i < count($multidef); $i++)
 					{
 						if ($i == 0)
 							$cssdef .= $multidef[$i];
 						else
 							$cssdef .= ucfirst($multidef[$i]);
 					}
				}
				$map[$field]["css"] = $cssdef;
				if ( isset($field_data["default"]) && strpos($field_data["default"],"px") != false )
					$map[$field]["css_unit"] = 'px';
				else if ( isset($field_data["default"]) && strpos($field_data["default"],"%") != false )
			 		$map[$field]["css_unit"] = '%';

				if (isset($field_data['unitfield']))
				{
					$map[$field]['unitfield'] = $field_data['unitfield'];
				}
			}
			if (isset($field_data["csspart"]))
				$map[$field]["csspart"] = $field_data["csspart"];
			if (isset($field_data['csspseudo']))
				$map[$field]['pseudo'] = $field_data['csspseudo'];


				// Attempt to new relationship type.
			if (isset($field_data['is_sub_of'] ))
			{
				 $map[$field]['is_sub_of'] = $field_data['is_sub_of'];
			}
		}
		return $map;
	}

	/** Sets the data
	*
	*	This action is called from button class when data is pulled from the database and populates the dataArray to all blocks
	*
	*/
	public function set($dataArray)
	{
		$this->data = $dataArray;
	}

	// simple util function to get blockdata.
	protected function getBlockData($blockName = null)
	{
		 $blockName =  (! is_null($blockName)) ? $blockName : $this->blockname;
		 return (isset($this->data[$blockName])) ? $this->data[$blockName] : array();
	}

} // class
