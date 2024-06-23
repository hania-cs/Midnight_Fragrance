<?php
declare(strict_types=1);
namespace MaxButtons;
use MaxButtons\ScssPhp\ScssPhp\Compiler as Compiler;

defined('ABSPATH') or die('No direct access permitted');

/* Class to Parse CSS. Load as array with diffent pseudo-types,
	ability to add nested and new root blocks
	parses via scss
	ability to use complicated css stuff via scss mixins parsing like gradient
	auto-discovery for -unit field types to set units (like px, or %)
	auto-discovery of fields via domobj.

*/

use \Exception as Exception;

class maxCSSParser
{
	protected $struct = array();
	protected $domObj = '';
	protected $pseudo = array("hover","active","responsive");

	protected $data;
	protected $screens;
	protected $output_css;

	protected $has_compile_error = false;
	protected $compile_error = array();

	protected $inline = array();
	protected $responsive = array();

	public $anchor_class = '.maxbutton'; // used for matching buttons in parse_part

	// settings
	protected $elements_ignore_zero = array(
		'text-shadow-left',
		'text_shadow-top',
		'text-shadow-width',
		'box-shadow-offset-left',
		'box-shadow-offset-top',
		'box-shadow-width',
		'box-shadow-spread',
	);  // items to ignore if value is zero, otherwise they become unremovable ( where 0 is still something on display)

	protected $important = false;

	// log possible problems and incidents for debugging;
	protected $parse_log = array();

	public function __construct()
	{
		//$root[] = array("a" => array("hover","active","responsive"));
		MB()->load_library('scss');
	}

	public function loadDom($domObj)
	{
		$this->domObj = $domObj;

	  $root = $domObj->root;

		$struct[$root->tag] = array();

		$children = $root->children();

		if (count($children) > 0)
			$struct[$root->tag] = $this->loadRecursive(array(), $children);


		// find the full and complete statement class defining maxbutton. This is needed for proper parsing on parse_part.
		$anchor_element = $root->find('.maxbutton', 0);
		if (! is_null($anchor_element))
		{
			$anchor_class = str_replace(' ', '.', $anchor_element->class);
			$this->anchor_class = $anchor_class;
		}

		$this->struct = $struct;

	}


	protected function loadRecursive($struct, $children)
	{
		foreach($children as $domChild)
		{

			$class = $domChild->class;

			// Class doesn't have to be set / string
			if (is_string($class))
			{
				$class = str_replace(" ",".", $class); // combine seperate classes
			}
  		$struct[$class]["tag"] = $domChild->tag;

			$child_children = $domChild->children();

			if (count($child_children) > 0)
			{

				$struct[$class]["children"] = $this->loadRecursive(array(), $child_children);
			}
		}

		return $struct;
	}

	public function setScreens($screens)
	{
		$this->screens = $screens;
	}

	public function parse($data)
	{
		$this->clear();

		$struct = $this->struct;

		$this->data = $data;

 		if (isset($data["settings"]))  // room for settings in parser
 		{
 			$settings = $data["settings"];
 			$this->important = (isset($settings["important"])) ? $settings["important"] : false;

 			unset($this->data["settings"]);
 		}

		$elements = array_shift($struct); // first element is a 'stub' root.

		if ( is_null($elements) )
			return;

		foreach($elements as $el => $el_data)
		{

			$this->parse_part($el,$el_data);
		}

		$this->parse_responsive($elements);

		maxUtils::startTime('compile CSS');
		$css = $this->compile($this->output_css);

		maxUtils::endTime('compile CSS');

		return $css;
	}

	// reset output values.
	protected function clear()
	{
		$this->data = '';
		$this->output_css = '';
		$this->inline = array();
		$this->responsive = array();
	}

	public function compile($css)
	{
		$scss = new Compiler();
		$scss->setImportPaths(MB()->get_plugin_path() . "assets/scss");

		$minify = get_option("maxbuttons_minify", 1);

		if ($minify == 1)
		{
				$scss->setOutputStyle(\MaxButtons\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
		}

		$compile = ' @import "_mixins.scss";' . $css;

		//maxUtils::addTime("CSSParser: Compile start ");
		try
		{
				$css = $scss->compileString($compile)->getCss();
		} catch (\Exception $e) {
			$this->has_compile_error = true;
			$this->compile_error = array('error' => $e,
									//		 'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4),
																	 'string' => $compile,
																 );
			$css = $this->output_css;
		}

		return $css;
	}

	public function get_compile_errors()
	{
		if ($this->has_compile_error)
		{
			return $this->compile_error;
		}
		return false;
	}

	// Returns the CSS selector for the responsive screen.  Used for custom CSS.
	public function getResponsiveScreenSelector($screen)
	{
		// $deef = $screen->;
			$def = $this->renderResponsiveDefinition();
	}

	/** Element is the current element that being parsed. El_add is the parent element that should be put before the subpart
	* @param $element CSS class Definition
	* @param $el_data DomDoc Element data of this element.
	* @param $el_add Indicated child element .css to add to def. to make proper cascase
	* @param $screenName String Name of the screen when parsing responsive view.
	*/
	protected function parse_part($element, $el_data, $el_add = '', $screenName = false)
	{
		maxUtils::addTime("CSSParser: Parse $element ");

		$data = $this->data;
		$tag = $el_data["tag"];

		// Element can be unset / int if the tag doesn't have element / tag associated so needs no further parsing (html element)
		if (! is_string($element))
		{
			return;
		}

		// returns all data from this element.
		$element_data = $this->findData($data, $element);

		if ($screenName !== false)
			$element_data = $this->findResponsiveData($element_data, $screenName);

		// not using scss selectors here since there is trouble w/ the :pseudo selector, which should be put on the maxbutton / a tag.
		if ($element != '')
		{	$el_add .= " ." . $element;

 		}
	 	if (isset($element_data["responsive"]))
	 	{
	 		$responsive = $element_data["responsive"]; // doing that at the end
	 		unset($element_data["responsive"]);

	 		$this->responsive[$el_add] = $responsive;
	 	}

		foreach($element_data as $pseudo => $values)
		{

			if ($pseudo != 'normal')
			{
				// select the maxbutton case, ending with either space or next class -dot.
				// Anchor class in default situation should be .maxbutton
				$anchor_class = $this->anchor_class;

				$count = 0;

			/* If PS Selector replacement doesn't match anchor class selector this probably means the parse is done in a higher level
			   e.g. container level, so no proper will be set. In case 0 count replacement, just put it on current */
		$ps_selector = preg_replace('/' . $anchor_class . '$|' . $anchor_class . '([.| ])/i',"$anchor_class:$pseudo\$1",$el_add, -1, $count);

				if ($count === 0)
				{
					$ps_selector = $el_add . ":" . $pseudo;
				}


				$this->output_css .= "$ps_selector{ ";
			}
			else {
				$this->output_css .= "$el_add{ ";
			}

			$values = $this->combineStatements($values);
			$values = $this->doMixins($values);

			$this->inline[$pseudo][$element] = $values;

			foreach($values as $cssTag => $cssVal)
			{

				$statement =  $this->parse_cssline($values,$cssTag,$cssVal); ///"$cssTag $css_sep $cssVal$unit$css_end ";

				if ($statement)
				{
					$this->output_css .= $statement ;

				/*	if (! isset($this->inline[$pseudo][$element]))
							$this->inline[$pseudo][$element] = array();

					if (! isset($this->inline[$pseudo][$element][$cssTag]))
							$this->inline[$pseudo][$element][$cssTag] = '';

					$this->inline[$pseudo][$element][$cssTag] = $cssVal; */
				}
			}

		 	$this->output_css .= "} ";
		}
			if (isset($el_data["children"]))
			{
				foreach($el_data["children"] as $child_id => $child_data)
				{
					$this->parse_part($child_id, $child_data, $el_add, $screenName);
				}
			}


	}

	protected function parse_cssline($values, $cssTag, $cssVal, $css_end = ';')
	{

		// unit check - two ways; either unitable items is first or unit declaration.
		if (isset($values[$cssTag . "_unit"]))
		{
			$unit = $values[$cssTag . "_unit"];
		}
		elseif(strpos($cssTag, "_unit") !== false)
		{
			return false; // no print, should be found under first def.
		}
		else $unit = '';


		$important = ($this->is_important()) ? " !important" : "";
		$important = ($cssTag == '@include') ? "" : $important; // mixin's problem, no checking here.

		$css_sep = ($cssTag == '@include') ? $css_sep = '' : ':';

		if ($cssVal == 0 && in_array($cssTag, $this->elements_ignore_zero))
			return false;

		if($cssVal !== '' && $cssTag !== '')
		{
			$statement = "$cssTag $css_sep $cssVal$unit$important$css_end ";
			return $statement;
		}
		return false;

	}

	protected function parse_responsive($elements)
	{

		$responsive = $this->responsive;
		if (! is_array($responsive) || count($responsive) == 0)
			return;


		$query_array = array();
		$screens = $this->screens;

		$definitions = array();

		// Find the responsive definitions and remove that part.
		foreach($responsive as $part => $screen_ids)
		{
			 foreach($screen_ids as $screen_id => $screen_data)
			 {

				 	if (isset($screens[$screen_id]) && $screens[$screen_id]->is_responsive() )
					{
						if (isset($screen_data['definition']))
						{
							$definitions[$screen_id]['def'] = $this->renderResponsiveDefinition($screen_data['definition']);
							// Hide screen on this size.
							if (isset($screen_data['definition']['hide_screen']) && $screen_data['definition']['hide_screen'] == 1)
							{
 									$this->doHideScreen($screen_id);
									// This line
									$screen_data['data'] = array('normal' =>  array('display' => 'none') );
							}
							unset($screen_data['definition']);
						}
						if (count($screen_data) > 0)
							$definitions[$screen_id]['data'][$part] = $screen_data;


					}
			 }
		}

		foreach($definitions as $screen_id => $def_array)
		{
				if (isset($def_array['def']) && isset($def_array['data']))
				{
					$this->parse_responsive_definition($elements, $def_array['def'], $screen_id);
				}
		}
	}

	/* Hide screen by removing all data and putting display none there */
	private function doHideScreen($screen_id)
	{
			foreach($this->data as $element => $eldata)
			{
				 if (isset($eldata['responsive']) && isset($eldata['responsive'][$screen_id]))
				 {
					 	$this->data[$element]['responsive'][$screen_id] = array('normal' => array('display' => 'none') );
				 }
			}

	}

	protected function renderResponsiveDefinition($definition)
	{
		 $def = 'only screen ';
		 if (isset($definition['min_width']))
		 	 $def .= ' and (min-width: ' . $definition['min_width'] . 'px) ';
		 if (isset($definition['max_width']))
		 	 $def .= ' and (max-width: ' . $definition['max_width'] . 'px) ';

			return $def;
	}

	/**
	* @param $element DOMDoc to feed parse_part with
	* @param $qdef Query Definition
	* @param $vdata Data. -probably not needed */
	protected function parse_responsive_definition($elements, $qdef, $screenName)
	{
		if (! isset($qdef) || $qdef == '')  {

				return; // no definition.
			}

			$this->output_css .= "@media ". $qdef . " { ";

		  foreach($elements as $el => $el_data)
				$this->parse_part($el, $el_data, '', $screenName);

			$this->output_css .= "}";
	}

	private function is_important()
	{

		if ($this->important == 1)
			return true;
		else
			return false;
	}

	/* Find Data in the dataset for this specific CSS selector */
	protected function findData($data, $el)
	{
		$classes = explode(".", $el);

		foreach($data as $part => $values)
		{
			if (in_array($part, $classes))
			{
				return $data[$part];
			}
		}
 		return array();
	}

	/** Filters a Data definition found with FindData for the correct ScreenName and returns that */
	protected function findResponsiveData($data, $screenName)
	{
		 if (isset($data["responsive"]) && isset($data['responsive'][$screenName]))
		 {
			 	$resp_data = $data['responsive'][$screenName];
				if (isset($resp_data['definition'])) // don't need the definition, already output that.
					unset($resp_data['definition']);

				return $resp_data;
		 }

		 return array();
	}

	protected function doMixins($values)
	{
		$mixins = array("gradient", "box-shadow", "text-shadow", "keyframes");

		foreach($mixins as $mixin)
		{

			$results = preg_grep("/^$mixin/i",array_keys($values) );
			if (count($results) === 0)
				continue; // no mixins.

			$mixin_array = array();
		 	foreach($results as $result)
		 	{
		 		$mixin_array[$result] = $values[$result];
		 	}

			if (count($mixin_array) > 0)
			{
				switch($mixin)
				{
					case "gradient":
						$values = $this->mixin_gradient($mixin_array, $values);
					break;
					case "box-shadow":
						$values = $this->mixin_boxshadow($mixin_array, $values);
					break;
					case "text-shadow":
						$values = $this->mixin_textshadow($mixin_array, $values);
					break;
					case 'keyframes':
						$values = $this->mixin_keyframes($mixin_array, $values);
					break;
					default:
						// Do Nothing, just for compat.
					break;
				}
			}


		}
		return $values;
	}

	/** Put various statements in their one-line shorthand. */
	protected function combineStatements($values)
	{
		$combiners = array(
				'border' => array('border-width', 'border-style', 'border-color'),
				'border-radius' => array('border-top-left-radius', 'border-top-right-radius', 'border-bottom-right-radius', 'border-bottom-left-radius'),
				'margin' => array('margin-top', 'margin-right', 'margin-bottom', 'margin-left'),
				'padding' => array('padding-top', 'padding-right', 'padding-bottom', 'padding-left'),
				'background' => array('background-image','background-position','background-size', 'background-repeat'),
		);

		foreach($combiners as $shorthand => $reqs)
		{
			 // All fields in combiner must be set. Check this against the keys (fields) in values, to ensure all are there ( results in 0 diff )
			 if (count(array_diff($reqs, array_keys($values) )) == 0)
			 {
				 $values[$shorthand]  = '';

				 foreach($reqs as $item)
				 {
					  if ($item == 'background-size') // exceptions there must be
							$values[$shorthand] .= '/ ';

					  $values[$shorthand] .= $values[$item] . ' ';
						unset($values[$item]);
				 }

			 }
		}

		return $values;
	}


	/** Parse the keyframes. Not a real mixin */
	protected function mixin_keyframes($results, $values)
	{
	  $keyframes_name = isset($results['keyframes-name']) ? $results['keyframes-name'] : false;
		$keyframes = isset($results['keyframes']) ? $results['keyframes'] : false;

		$key_output = ' @keyframes ' . $keyframes_name . ' { '.
									$keyframes . '}';

		$this->output_css = $key_output . $this->output_css;

		$values = array_diff_key($values, $results);
		return $values;
	}

	protected function mixin_gradient($results, $values)
	{

		$background = ( isset($values['background'])) ? $values['background'] : false;


		$start = isset($results["gradient-start-color"]) ? $results["gradient-start-color"] : '';
		$end = isset(  $results["gradient-end-color"]  ) ?  $results["gradient-end-color"] : '';
		$start_opacity = isset(  $results["gradient-start-opacity"]  ) ?  intval($results["gradient-start-opacity"]) : '';
		$end_opacity = isset(  $results["gradient-end-opacity"]  ) ?  intval($results["gradient-end-opacity"]) : '';
		$stop =  (isset( $results["gradient-stop"]) && $results["gradient-stop"] != '') ?  $results["gradient-stop"] . "%" : '45%';
		// default to use ( old situation )
		$use_gradient = (isset($results['gradient-use-gradient']) && $results['gradient-use-gradient'] != '') ? $results['gradient-use-gradient'] : 1;


		$start = maxUtils::hex2rgba($start, $start_opacity);
		$end = maxUtils::hex2rgba($end, $end_opacity);

		$important = ($this->is_important()) ? "!important" : false;
		//$values = $this->add_include($values, "linear-gradient($start,$end,$stop,$important)");

		if ($use_gradient == 1)
		{
			if ($background)
				unset($values['background']);

			$linear = "linear-gradient($start,$end,$stop";
			if ($important)
				$linear .= ','. $important;
			elseif(! $important && $background)
				$linear .= ',null,' . $background;
			elseif ($important && $background)
				$linear .= ',' . $background;

			$values = $this->add_include($values, $linear . ')');
		}
		else {
			$values['background-color'] = $start;
		}

		// remove the non-css keys from the value array ( field names )
		$values = array_diff_key($values, $results);

		return $values;


	}

	protected function mixin_boxshadow($results, $values)
	{
		$width = isset($results["box-shadow-width"]) ? $results["box-shadow-width"] : 0;
		$left = isset($results["box-shadow-offset-left"]) ? $results["box-shadow-offset-left"] : 0;
		$top = isset($results["box-shadow-offset-top"]) ? $results["box-shadow-offset-top"] : 0;
		$spread = isset($results['box-shadow-spread']) ? $results['box-shadow-spread'] : 0;
		$color = isset($results["box-shadow-color"]) ? $results["box-shadow-color"] : "rgba(0,0,0,0)";

		$important = ($this->is_important()) ? "!important" : "";

		$values = array_diff_key($values, $results); // always remove these fields from CSS since they are not valid.

		if ($width == 0 && $left == 0 && $top == 0 && $spread == 0)
		{
			$values['box-shadow'] = 'none'; // if no box-shadow, prevent it in total
			return $values;
		}

		$values = $this->add_include($values, "box-shadow($left, $top, $width, $color,$spread, false, $important) ");


		return $values;
	}

	protected function mixin_textshadow($results, $values)
	{
		$width = isset($results["text-shadow-width"]) ? $results["text-shadow-width"] : 0;
		$left = isset($results["text-shadow-left"]) ? $results["text-shadow-left"] : 0;
		$top = isset($results["text-shadow-top"]) ? $results["text-shadow-top"] : 0;
		$color = isset($results["text-shadow-color"]) ? $results["text-shadow-color"] : "rgba(0,0,0,0)";
		$important = ($this->is_important()) ? "!important" : "";

 		if ($width == 0 && $left == 0 && $top == 0)
		{
			$values = array_diff_key($values, $results); // remove them from the values, prevent incorrect output.
			return $values;
		}

		$values = $this->add_include($values, "text-shadow ($left,$top,$width,$color $important)");
		$values = array_diff_key($values, $results);

		return $values;
	}

	private function add_include($values, $include)
	{
		if (isset($values["@include"]))
			$values["@include"] .= "; @include " . $include;
		else
			$values["@include"] = $include;
		return $values;
	}

	public	function outputInline($domObj, $pseudo = 'normal')
	{
		$domObj = $domObj->load($domObj->save());

		$inline = $this->inline;
		// ISSUE #43 Sometimes this breaks
		if (! isset($inline[$pseudo]))
			return $domObj;

		$elements = array_keys($inline[$pseudo]);

		if ($pseudo != 'normal') // gather all elements
			$elements = array_merge($elements, array_keys($inline["normal"]));

		foreach($elements as $element)
		{
			$styles = isset($inline[$pseudo][$element]) ? $inline[$pseudo][$element] : array();

			if ($pseudo != 'normal')
			{
				$styles =  array_merge($inline['normal'][$element],$styles);
			}
			$normstyle = '';
	/*		if ($pseudo != 'normal') // parse all possible missing styles from pseudo el
			{

				$tocompile = $this->dummify($inline['normal'][$element]);
				$normstyle = $this->compile($tocompile);
				$normstyle = $this->undummify($normstyle);
			} */
			maxUtils::addTime("CSSParser: Parse inline done");
			foreach($styles as $cssTag => $cssVal)
			{
				$normstyle .=  $this->parse_cssline($styles, $cssTag,$cssVal);
			}
			// add dummy {} here because new scssphp parse doesn't like styles without it. Remove it after compile, since this is inline.
			$style_output = $this->compile($this->dummify($normstyle));
			$style_output = $this->undummify($style_output);

			//$styles = $normstyle . $styles;

			$element = trim(str_replace("."," ", $element)); // molten css class, seperator.

			$el = $domObj->find('[class*="' . $element . '"]', 0);

			$el->style = $style_output;

		}

		return $domObj;

	}

	/** Dummyfi, because Scssphp compiler doesn't like CSS without {} */
	private function dummify($string)
	{
		return 'dummy{' . $string . '}';
	}

	private function undummify($string)
	{
		 $string = trim(str_replace(array('dummy','{','}'),'', $string)); // remove the dummy
		 if (substr($string,-1) !== ';') // the last ; might not be there, put it so we can glue more CSS there.
			$string .= ';';

		return $string;
	}


}

class compileException extends Exception {
	protected $code = -1;

}
