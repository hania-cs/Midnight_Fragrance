<?php
declare(strict_types=1);

namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');

$blockClass["basic"] = "basicBlock";
$blockOrder[10][] = "basic";

class basicBlock extends maxBlock
{
	protected $blockname = "basic";
	protected $fields = array("name" => array("default" => ''),
							  "status" => array("default" => "publish"),
							  "description" => array("default" => ''),
							  "url" => array("default" => ''),
							  'link_title' => array('default' => ''),
							//  "text" => array("default" => ''),
							  "new_window" => array("default" => '0'),
							  "nofollow" => array("default" => '0'),
								'is_download' => array('default'=> '0'),
							 );
	protected $protocols;

	public function __construct()
	{
		parent::__construct();

		$this->protocols = maxUtils::getAllowedProcotols();
	}

	public function parse_css($css, $screens, string $mode = 'normal')
	{
		// emtpy string init is not like by PHP 7.1

		if (! is_array($css))
			$css = array();

		$data = $this->getBlockData();

		$css["maxbutton"]["normal"]["position"] = "relative";
		$css["maxbutton"]["normal"]["text-decoration"] = "none";
//		$css["maxbutton"]["normal"]["white-space"] = "nowrap";  // hinders correct rendering of oneline-multilines
		$css["maxbutton"]["normal"]["display"] = "inline-block";
		$css["maxbutton"]["normal"]["vertical-align"] = 'middle';
		//$css["maxbutton"]["normal"]["overflow"] = "hidden"; // hinder tooltip


		// option to show border boxed buttons in preview area.
		$border_box = get_option('maxbuttons_borderbox');

		if ($border_box == 1)
		{
			$css['maxbutton']['normal']['box-sizing']  = 'border-box';
		}

		$css = parent::parse_css($css, $screens, $mode);


		return $css;

	}

	public function save_fields($data, $post, $screens)
	{
		// Possible solution:
	//	$post["url"] = isset($post["url"]) ? urldecode(urldecode($post["url"])) : '';

		$description = false;

		if (isset($post["description"]) && $post["description"] != '')
		{
			$description = str_replace("\n", '-nwline-', $post["description"]);
			$description = sanitize_text_field($description);
			$description = str_replace('-nwline-', "\n", $description);
		}

		$data = parent::save_fields($data, $post, $screens);

		// bypass sanitize for description - causing the end of line-breaks
		if ($description)
			$data["basic"]["description"] = $description;

		// bypassing sanitize text field - causes problems with URLs and spaces
		$url = isset($post["url"]) ? trim($post["url"]) : '';

		// filter zero width space ( https://en.wikipedia.org/wiki/Zero-width_space ) in URL
		// https://stackoverflow.com/questions/22600235/remove-unicode-zero-width-space-php
		$url = str_replace("&#8203;", "", $url);
		$url = str_replace("\xE2\x80\x8C", "", $url);
		$url = str_replace("\xE2\x80\x8B", "", $url);

		$parsed_url = parse_url($url);

		$rawEncode = array("query","fragment");
		foreach($rawEncode as $item)
		{
			if (isset($parsed_url[$item]))
			{
				$parsed_url[$item] = rawurlencode($parsed_url[$item]);
			}
		}

		$url = $this->unParseURL($parsed_url);

		$url = str_replace(" ", "%20", trim($url) );

 		if (! $this->checkRelative($parsed_url))
			$url = esc_url_raw($url, $this->protocols);  // str replace - known WP issue with spaces

		$data[$this->blockname]["url"] = $url;

		if (isset($post["name"]))
			$data["name"] = sanitize_text_field($post["name"]);
		if (isset($post["status"]))
			$data["status"] = sanitize_text_field($post["status"]); // for conversion old - new.
 		return $data;
	}

	protected function unparseURL($parsed_url)
	{
		  // Don't add // to these schemes
		  $noslash_schemes = array('javascript', 'mailto', 'tel', 'sms');
		  if (isset($parsed_url['scheme']) && in_array($parsed_url['scheme'], $noslash_schemes) )
			  $scheme = $parsed_url["scheme"] . ":";
		  else
			  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';

		  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		  $pass     = ($user || $pass) ? "$pass@" : '';
		  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		  return "$scheme$user$pass$host$port$path$query$fragment";
	}

	/* Check for a relative URL that gets killed by esc_url ( if there is no / first ) */
	protected function checkRelative($parsed_url)
	{
		if (! isset($parsed_url['host']) && ! isset($parsed_url['scheme']) )
		{
			if (isset($parsed_url['path']) && $parsed_url['path'] !== '' && substr($parsed_url['path'], 0,1) !== '/')
			{
				return true;
			}
		}
		return false;
	}

	public function parse_button($domObj, $mode = 'normal')
	{

		$data = $this->getBlockData();
		$button_id = $this->data["id"];
		$rels = array();

		$anchor = $domObj->find("a",0);

		if (isset($data["nofollow"]) && $data["nofollow"] == 1)
		{
			$rels[] = 'nofollow';
			$rels[] = 'noopener';
		}

		if (isset($data["new_window"]) && $data["new_window"] == 1)
		{
			$anchor->target = "_blank";
			if (! in_array('noopener', $rels))
				$rels[] = 'noopener';
		}
		if (isset($data['link_title']) && strlen($data['link_title']) > 0)
			$anchor->title = esc_attr($data['link_title']);

		$rels = apply_filters('mb/button/rel', $rels);

		if (count($rels) > 0)
		{
			$anchor->rel = esc_attr(implode(' ', $rels));
		}

		if (isset($data["url"]) && $data["url"] != '')
		{
			$url = $data["url"];

			$parsed_url = parse_url($url);

			if (false === $this->checkRelative($parsed_url))
			{
				$url = esc_url($url, $this->protocols);
			}

		 	$url = rawurldecode($url);  // removes the + from a URL part.
			$url = apply_filters('mb-url', $url, $data['url']);  // passes processed url / raw url.
			$url = apply_filters('mb-url-' . $button_id, $url, $data['url']);

			// This esc_attr is security and can't be removed - even if some urls fail with it.
			$anchor->href = esc_attr($url);

		}
		else  // fixing an iOS problem which renders anchors without URL wrongly.
		{
			$anchor->href = 'javascript:void(0);';
		}

		if (isset($data['is_download']) && $data['is_download'] == 1)
		{
			 $anchor->download = '';
		}


		return $domObj;

	}

	public function map_fields($map)
	{
		$map = parent::map_fields($map);

		$map["url"]["attr"] = "href";
		$map["link_title"]["attr"] = "title";

		return $map;
	}

	public function check_unique_name($name)
	{
		global $wpdb;
		$table = maxUtils::get_table_name();

		$button_id = $this->data['id'];

		if ($name === false || strlen($name) == 0 || $name == '')
			return false;

		if ($button_id <= 0)
			return false;

		$sql = $wpdb->prepare("SELECT id from $table where name = %s and status = 'publish' and id <> %d ", $name, $button_id);

		$results = $wpdb->get_col($sql);

		if (count($results) > 0)
		{
			$message = __('Button name already used. Using non-unique names with the shortcode can cause issues', 'maxbuttons');
			$message .=  ' ' . __('Already used in : ');
			foreach($results as $id)
			{
				$url =  admin_url() . 'admin.php?page=maxbuttons-controller&action=edit&id=' . $id;
				$message .= ' <a href="' . esc_attr($url) . '" target="_blank">' . intval($id) . '</a> ';
			}

			return $message;
		}
	}

	public function admin_fields($screen)
	{

			$icon_url = MB()->get_plugin_url() . 'images/icons/';

					$start_block = new maxField('block_start');
					$start_block->name = __('basic', 'maxbuttons');
					$start_block->label = __('Basics', 'maxbuttons');
					$screen->addField($start_block);

					$color_copy_self = __("Replace color from other field", "maxbuttons");
					$color_copy_move  = __("Copy Color to other field", "maxbuttons");

					$check_name = $this->check_unique_name($screen->getValue('name'));

 					// Name
					$field_name = new maxField();
					$field_name->label = __('Button Name', 'maxbuttons');
					$field_name->id = $screen->getFieldID('name');
					$field_name->value = $screen->getValue($field_name->id);
					$field_name->name = $field_name->id;
					$field_name->is_responsive = false;
					$field_name->placeholder = __("Button Name","maxbuttons");
					$field_name->help = __('<p>Internal name for button. Specify purpose or use. Additional info can be added to description.</p> <p>You can use button name in the shortcode <br> [maxbutton name="button-name"] </p>
						<p class="shortcode"> Shortcode attribute : name </p>', 'maxbuttons');

					if ($check_name)
					{
						$field_name->warning = $check_name;
							//$field_name->error = $check_name;
					}
					$screen->addField($field_name, 'start', 'end');

					// URL
					$field_url = new maxField();
					$field_url->label = __('URL', 'maxbuttons');
				//	$field_url->note = __('The link when the button is clicked.', 'maxbuttons');
					$field_url->id = $screen->getFieldID('url');

					$the_url = $screen->getValue($field_url->id);
					if (false !== $the_url)
					{
						 $the_url = rawurldecode($the_url);
					}
					$field_url->value = $the_url;
					$field_url->placeholder = __("http://","maxbuttons");
					$field_url->name = $field_url->id;
					$field_url->is_responsive = false;
					$field_url->help = __("<p>Enter any URL you wish to link to. Use 'Select Site Content' button to search in your pages, posts and media </p>
					<p>Examples: <br><ul class='nowrap'><li> https://example.com/ </li>
								 <li> /local-page/ </li>
								 <li> javascript:window.history.back(); </li></ul></p>
					<p class='shortcode'> Shortcode attribute : url </p>", 'maxbuttons');
					$screen->addField($field_url,'start');

					$url_button = new maxField('button');
					$url_button->id = $screen->getFieldID('url_button');
					$url_button->name = $url_button->id;
					$url_button->button_label = __('Select Site Content', 'maxbuttons');
					$url_button->is_responsive = false;
					$screen->addField($url_button, '', 'end');


					if (isset($_GET['copied']))
					{
						$copyno = new maxField('generic');
						$copyno->label = '&nbsp;';
						$copyno->name = 'prevent-copy-message';
						$copyno->content = "<p>" . __('<strong>Tip: </strong> You don\'t need to copy buttons to change URL or Text. See the examples on top of page') . '</p>';
						$screen->addField($copyno, 'start','end');
					}

					$url_nonce = new maxField('hidden');
					$url_nonce->id = $screen->getFieldID('_ajax_linking_nonce');
					$url_nonce->name = $url_nonce->id;
					$url_nonce->value = wp_create_nonce('internal-linking');
					$url_nonce->is_responsive = false;

					$screen->addField($url_nonce);

					// Spacer
					$fspacer = new maxField('spacer');
					$fspacer->name = 'url_options';
					$fspacer->label = '&nbsp;';
					$fspacer->is_responsive = false;
				  $screen->addField($fspacer, 'start');

					// New Window
					$fwindow = new maxField('switch');
					$fwindow->label_after = __('Open in New Tab', 'maxbuttons');
					$fwindow->id = $screen->getFieldID('new_window');
					$fwindow->name = $fwindow->id;
					$fwindow->value = 1;
					$fwindow->is_responsive = false;
					$fwindow->input_class = 'small';
					$fwindow->checked = checked( $screen->getValue($fwindow->id), 1, false);
					$screen->addField($fwindow, '', '');

					// NoRel
				//	$screen->addField($fspacer, 'start');
					$ffollow = new maxField('switch');
					$ffollow->label_after = __('Use rel="nofollow"', 'maxbuttons');
					$ffollow->value = 1;
					$ffollow->id = $screen->getFieldID('nofollow');
					$ffollow->name = $ffollow->id;
					$ffollow->is_responsive = false;
					$ffollow->input_class = 'small';
					$ffollow->checked = checked( $screen->getValue($ffollow->id), 1, false);
					$screen->addField($ffollow, '','end');
					// TITLE

					$screen->addField($fspacer, 'start');
					$fdownload = new MaxField('switch');
					$fdownload->label_after = __('URL is download', 'maxbuttons');
					$fdownload->value = 1;
					$fdownload->id = $screen->getFieldID('is_download');
					$fdownload->name = $fdownload->id;
					$fdownload->is_responsive = false;
					$fdownload->input_class = 'small';
					$fdownload->checked = checked($screen->getValue($fdownload->id), 1, false);
					$fdownload->help = __('This will tell the browser to download the URL', 'maxbuttons');
					$screen->addField($fdownload, '', 'end');

					$field_title = new maxField();
					$field_title->label = __('Button Tooltip', 'maxbuttons');
					$field_title->id = $screen->getFieldID('link_title');
					$field_title->name = $field_title->id;  // title is too generic
					$field_title->value =  $screen->getValue($field_title->id);
					$field_title->is_responsive = false;
					$field_title->help = __('<p>This text will appear when hovering over the button. You can try this in the preview.</p>
					<p class="shortcode">Shortcode attribute : linktitle</p>', 'maxbuttons');
					$screen->addField($field_title, 'start', 'end');

					// TEXT
					$field_text = new maxField();
					$field_text->label = __('Text','maxbuttons');
					$field_text->id = $screen->getFieldID('text');
					$field_text->name = $field_text->id;
					$field_text->is_responsive = false;
					$field_text->value = esc_attr($screen->getValue($field_text->id));
					$field_text->help = __('Shortcode attribute: text');
					$screen->addField($field_text, 'start', 'end');


 					// FONTS
					$fonts = MB()->getClass('admin')->loadFonts();

 					$field_font = new maxField('option_select');
 					$field_font->label = __('Font','maxbuttons');

 					$field_font->id = $screen->getFieldID('font');
					$field_font->name = $field_font->id;
					$field_font->selected = $screen->getValue($field_font->id);
 					$field_font->options = $fonts;

					$screen->addField($field_font,'start');

					// FONT SIZE
					//global $maxbuttons_font_sizes;
				//	$sizes = apply_filters('mb/editor/fontsizes', maxUtils::generate_font_sizes(10,50) );

					$field_size = new maxField('number');
					$field_size->id= $screen->getFieldID('font_size');
					$field_size->name = $field_size->id;
					$field_size->inputclass = 'tiny';
					$field_size->min = 8;
					$field_size->after_input = __('px', 'maxbuttons');
					$field_size->value = maxUtils::strip_px($screen->getValue($field_size->id));
					$screen->addField($field_size);

					// Font style checkboxes
			 		$fweight = new maxField('checkbox');
			 		$fweight->icon = 'dashicons-editor-bold';
			 		$fweight->title = __("Bold",'maxbuttons');
			 		$fweight->id = $screen->getFieldID('check_fweight');
			 		$fweight->name = $screen->getFieldID('font_weight');
			 		$fweight->value = 'bold';
			 		$fweight->inputclass = 'check_button icon';
			 		$fweight->checked = checked( $screen->getValue($fweight->name), 'bold', false);

					$screen->addField($fweight, 'group_start');

			 		$fstyle = new maxField('checkbox');
			 		$fstyle->icon = 'dashicons-editor-italic';
			 		$fstyle->title = __("Italic",'maxbuttons');
			 		$fstyle->id = $screen->getFieldID('check_fstyle');
			 		$fstyle->name = $screen->getFieldID('font_style');
			 		$fstyle->value = 'italic';
			 		$fstyle->inputclass = 'check_button icon';
			 		$fstyle->checked = checked( $screen->getValue($fstyle->name), 'italic', false);
					$screen->addField($fstyle, '', 'group_end');

			 		$falign_left = new maxField('radio');
			 		$falign_left->icon = 'dashicons-editor-alignleft';
			 		$falign_left->title = __('Align left','maxbuttons');
			 		$falign_left->id = $screen->getFieldID('radio_talign_left');
			 		$falign_left->name = $screen->getFieldID('text_align');
			 		$falign_left->value = 'left';
			 		$falign_left->inputclass = 'check_button icon';
			 		$falign_left->checked = checked ( $screen->getValue($falign_left->name), 'left', false);
					$screen->addField($falign_left, 'group_start');

			 		$falign_center = new maxField('radio');
			 		$falign_center->icon = 'dashicons-editor-aligncenter';
			 		$falign_center->title = __('Align center','maxbuttons');
			 		$falign_center->id = $screen->getFieldID('radio_talign_center');
			 		$falign_center->name = $screen->getFieldID('text_align');
			 		$falign_center->value = 'center';
			 		$falign_center->inputclass = 'check_button icon';
			 		$falign_center->checked = checked( $screen->getValue($falign_center->name), 'center', false);
					$screen->addField($falign_center);

			 		$falign_right = new maxField('radio');
			 		$falign_right->icon = 'dashicons-editor-alignright';
			 		$falign_right->title = __('Align right','maxbuttons');
			 		$falign_right->id = $screen->getFieldID('radio_talign_right');
			 		$falign_right->name = $screen->getFieldID('text_align');
			 		$falign_right->value = 'right';
			 		$falign_right->inputclass = 'check_button icon';
			 		$falign_right->checked = checked( $screen->getValue($falign_right->name), 'right', false);
					$screen->addField($falign_right, '',  array('group_end','end') );

			 		// Padding - trouble
			 		$ptop = new maxField('number');
			 		$ptop->label = __('Padding', 'maxbuttons');
			 		$ptop->id = $screen->getFieldID('padding_top');
			 		$ptop->name = $ptop->id;
 					$ptop->min = 0;
			 		$ptop->inputclass = 'tiny';
			 		$ptop->before_input = '<img src="' . $icon_url . 'p_top.png" title="' . __("Padding Top","maxbuttons") . '" >';
			 		$ptop->value = maxUtils::strip_px( $screen->getValue($ptop->id));
					$screen->addField($ptop,'start');

			 		$pright = new maxField('number');
			 		$pright->id = $screen->getFieldID('padding_right');
			 		$pright->name = $pright->id;
 					$pright->min = 0;
			 		$pright->inputclass = 'tiny';
			 		$pright->before_input = '<img src="' . $icon_url . 'p_right.png" class="icon padding" title="' . __("Padding Right","maxbuttons") . '" >';
			 		$pright->value = maxUtils::strip_px($screen->getValue($pright->id));
					$screen->addField($pright);

			 		$pbottom = new maxField('number');
			 		$pbottom->id = $screen->getFieldID('padding_bottom');
			 		$pbottom->name = $pbottom->id;
 					$pbottom->min = 0;
			 		$pbottom->inputclass = 'tiny';
			 		$pbottom->before_input = '<img src="' . $icon_url . 'p_bottom.png" class="icon padding" title="' . __("Padding Bottom","maxbuttons") . '" >';
			 		$pbottom->value = maxUtils::strip_px($screen->getValue($pbottom->id));
					$screen->addField($pbottom);

				 	$pleft = new maxField('number');
			 		$pleft->id = $screen->getFieldID('padding_left');
			 		$pleft->name = $pleft->id;
 					$pleft->min = 0;
			 		$pleft->inputclass = 'tiny';
			 		$pleft->before_input = '<img src="' . $icon_url . 'p_left.png" class="icon padding" title="' . __("Padding Left","maxbuttons") . '" >';
			 		$pleft->value = maxUtils::strip_px($screen->getValue($pleft->id));
					$screen->addField($pleft,'', 'end');

 					// Text Color

 					$fcolor = new maxField('color');
 					$fcolor->id = $screen->getFieldID('text_color');
 					$fcolor->name = $fcolor->id;
 					$fcolor->value = $screen->getColorValue($fcolor->id);
 					$fcolor->label = __('Text Color','maxbuttons');
 					$fcolor->copycolor = true;
 					$fcolor->bindto = $screen->getFieldID('text_color_hover');
 					$fcolor->copypos = 'right';
					$fcolor->right_title = $color_copy_move;
					$fcolor->left_title = $color_copy_self;
					$screen->addField($fcolor, 'start');

 					// Text Color Hover
 					$fcolor_hover = new maxField('color');
 					$fcolor_hover->id = $screen->getFieldID('text_color_hover');
 					$fcolor_hover->name = $fcolor_hover->id;
 					$fcolor_hover->value = $screen->getColorValue($fcolor_hover->id);
 					$fcolor_hover->label = __('Text Color Hover','maxbuttons');
 					$fcolor_hover->copycolor = true;
 					$fcolor_hover->bindto = $fcolor->id;
 					$fcolor_hover->copypos = 'left';
					$fcolor_hover->right_title = $color_copy_self;
					$fcolor_hover->left_title = $color_copy_move;

					$screen->addField($fcolor_hover, '','end');

					// Fix label for px or %
					$after_input = ($screen->getValue($screen->getFieldID('button_size_unit_width')) == 'pixel') ? __('px', 'maxbuttons') : __('%','maxbuttons');
					$after_input = '<span class="unit">' . $after_input . '</span>';

 					// Dimension : width

 					$field_width = new maxField('number');
 					$field_width->label = __('Button Width','maxbuttons');
 					$field_width->id = $screen->getFieldID('button_width');
					$field_width->name = $field_width->id;
					$field_width->inputclass = 'small';
 					$field_width->min = 0;
					$field_width->after_input = $after_input;
 					$field_width->value = maxUtils::strip_px($screen->getValue($field_width->id));  // strippx?
					$screen->addField($field_width, 'start');

					// Fix label for px or %
					$after_input = ($screen->getValue($screen->getFieldID('button_size_unit_height')) == 'pixel') ? __('px', 'maxbuttons') : __('%','maxbuttons');
					$after_input = '<span class="unit">' . $after_input . '</span>';

 					// Dimension : height
 					$field_height = new maxField('number');
 					$field_height->label = __('Button Height','maxbuttons');
 					$field_height->name = $screen->getFieldID('button_height');
 					$field_height->id = $field_height->name;
 					$field_height->inputclass = 'small';
 					$field_height->min = 0;
					$field_height->after_input = $after_input;
					$field_height->help = __('Width and Height are optional. When set to 0, button size will be determined by text size plus padding', 'maxbuttons');
 					$field_height->value=  maxUtils::strip_px($screen->getValue($field_height->id));  // strippx?
					$screen->addField($field_height, '', 'end');

					$size_spacer = new maxField('spacer');
					$size_spacer->label = __('Width Unit', 'maxbuttons');
					$size_spacer->name = 'size_unit_spacer';

					$screen->addField($size_spacer, 'start', '');

					// Units for width
					$wsize_unit_px = new maxField('radio');
					$wsize_unit_px->label = __('px', 'maxbuttons');
					$wsize_unit_px->name = $screen->getFieldID('button_size_unit_width');
					$wsize_unit_px->id = $screen->getFieldID('wbutton_size_unit_px');
					$wsize_unit_px->value = 'pixel';
					//$wsize_unit_px->before_input = '<label>width</label>';

					$wsize_unit_px->checked = checked( $screen->getValue($wsize_unit_px->name), 'pixel', false);

					$screen->addField($wsize_unit_px, 'group_start', '');

					$wsize_unit_perc = new maxField('radio');
					$wsize_unit_perc->label = __('%', 'maxbuttons');
					$wsize_unit_perc->name = $screen->getFieldID('button_size_unit_width');
					$wsize_unit_perc->id = $screen->getFieldID('wbutton_size_unit_perc');
					$wsize_unit_perc->value = 'percent';
					$wsize_unit_perc->checked = checked( $screen->getValue($wsize_unit_perc->name), 'percent', false);

					$screen->addField($wsize_unit_perc, '', 'group_end');

					$sp = new maxField('spacer');
					$sp->name = 'unit-spacer ';
					$sp->label = __("Height Unit", 'maxbuttons');

					$screen->addField($sp);

					// Units for height.
					$hsize_unit_px = new maxField('radio');
					$hsize_unit_px->label = __('px', 'maxbuttons');
					$hsize_unit_px->name = $screen->getFieldID('button_size_unit_height');
					$hsize_unit_px->id = $screen->getFieldID('hbutton_size_unit_px');
					$hsize_unit_px->value = 'pixel';
					$hsize_unit_px->checked = checked( $screen->getValue($hsize_unit_px->name), 'pixel', false);
					$screen->addField($hsize_unit_px, 'group_start', '');

					$hsize_unit_perc = new maxField('radio');
					$hsize_unit_perc->label = __('%', 'maxbuttons');
					$hsize_unit_perc->name = $screen->getFieldID('button_size_unit_height');
					$hsize_unit_perc->id = $screen->getFieldID('hbutton_size_unit_perc');
					$hsize_unit_perc->value = 'percent';
					$hsize_unit_perc->checked = checked( $screen->getValue($hsize_unit_perc->name), 'percent', false);
					$hsize_unit_perc->help = __('Using percentages makes the button size to the page element. The live preview can be unreliable', 'maxbuttons');
					$screen->addField($hsize_unit_perc, '', array('group_end', 'end'));


 					// Description
 					$description_hide = get_option('maxbuttons_hidedescription');
 					if ($description_hide == 1)
 						$field_desc = new maxField('hidden');
					else
 	 					$field_desc = new maxField('textarea');

					$field_desc->label = __('Description', 'maxbuttons');
					$field_desc->id = $screen->getFieldID('description');
					$field_desc->name = $field_desc->id;
					$field_desc->esc_function = 'esc_textarea';
					$field_desc->value = $screen->getValue($field_desc->id);
					$field_desc->is_responsive = false;
					$field_desc->placeholder = __('Brief explanation about how and where the button is used.','maxbuttons');

					$screen->addField($field_desc, 'start', 'end');

					$this->sidebar($screen);
					$endblock = new maxField('block_end');
					$screen->addField($endblock);

 		}  // admin_display

 } // class
