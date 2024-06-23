<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["responsive"] = "responsiveBlock";
$blockOrder[5][] = "responsive";

class responsiveBlock extends maxBlock
{
	protected $blockname = 'responsive';

	protected $is_new = true;
	protected $is_default = false;

	protected $fields = array(
		//	'dimension' => array(''),
			'min_width' => array('default' => 0),
			'max_width' => array('default' => 0),
			'hide_screen' => array('default' => 0),
			'screen_name' => array('default' => ''),
	);


	public function parse_css($css, $screens, string $mode = 'normal')
	{
		 if ($mode != 'normal')
		 	return $css;

		 $data = $this->data[$this->blockname];

		 $option_autoresponsive = get_option('maxbuttons_autoresponsive', 1);
		 $option_fontsize = 	get_option('maxbuttons_autor_font', 80);
		 $option_buttonwidth = get_option('maxbuttons_autor_width', 90);

		 if (! Screen::hasResponsive() && $option_autoresponsive == 1 && ! isset($screens['rsauto']) )
		 {
			 	$prefix = 'rsauto';

				$this->data['responsive'][$prefix . '_min_width'] = 0;
				$this->data['responsive'][$prefix . '_max_width'] = 480;
				$this->data['responsive'][$prefix . '_screen_name'] = 'AutoResponsive';
				$this->data['responsive']['screens'][] = $prefix;

				$this->data['dimension'][$prefix . '_button_width'] = $option_buttonwidth;
				$this->data['dimension'][$prefix . '_button_size_unit_width'] = 'percent';

				$this->data['container'][$prefix . '_container_width']  = $option_buttonwidth;
				$this->data['container'][$prefix . '_container_width_unit']  = 'percent';
				$this->data['container'][$prefix . '_container_alignment']  = 'float:none';

				$css['maxbutton']['responsive'][$prefix] = array(
							'definition' => array('min_width' => 0, 'max_width' => 480, 'screen_name' => 'autoresponsive'),
							'normal' => array('width' => $option_buttonwidth . '%'),
				 );

				 $css['mb-container']['responsive'][$prefix] = array('normal' => array('width' => $option_buttonwidth . '%', 'float' => 'none'));

				 $css["mb-text"]["responsive"]["phone"][0]["font-size"] = $option_buttonwidth . "%";

				 if (isset($this->data['text']['font_size']))
				 {
						$css['mb-text']['responsive'][$prefix] = array('normal' => array('font-size' => floor(intval($this->data["text"]["font_size"]) * ($option_fontsize/100) ) . 'px'));
				 }

	 			Screen::setupScreens($this->data);
		 }

		 foreach($screens as $screenObj)
		 {
			 $screen_id = $screenObj->id;
			 foreach($this->fields as $field_id => $notused)
			 {
				 $r_field_id = $screenObj->getFieldID($field_id);
				 if (isset($data[$r_field_id]))
			 	 	$css['maxbutton']['responsive'][$screen_id]['definition'][$field_id] = $data[$r_field_id];
			 }
		 }

		 return $css;
	}


	public function save_fields($data, $post, $screens)
	{

		if (isset($post['screens']))
		{
			$block = isset($data[$this->blockname]) ? $data[$this->blockname] : array();

			$standards = array('default', 'new'); // non-flexible screens (need a better var name!)
			$screens_ar = $post['screens'];
			$screens_ar = array_diff($screens_ar, $standards);

			if (isset($post['add_new_screen']) && $post['add_new_screen'] == 'yes')
			{
				 $id = $nid = 0;
				 foreach($screens_ar as $screen_id)
				 {
				 	$nid = filter_var($screen_id, FILTER_SANITIZE_NUMBER_INT);
					if ($nid > $id)
						$id = $nid; // find highest screen number
				 }

				 $id++;
				 $screen_id = 'rs' . $id;
				 $screens_ar[] = $screen_id;

				 $screens[] = new Screen($screen_id); // add screen so parent::save can extract values from responsive block.

				 // update new to id of new screen
				 foreach($post as $pkey => $pval)
			 	 {
					 	if (strpos($pkey, 'new_') == 0)
						{
							unset($post[$pkey]); // unset new_
							$pkey = str_replace('new_', $screen_id . '_', $pkey); // rename to new screen name.
							$post[$pkey] = $pval; // add that.
						}
				 }


			}

		//	if (count($screens_ar) > 0)
		//	{
				$block['screens'] = $screens_ar;
				$data[$this->blockname] = $block;
		//	}
		}

		$data = parent::save_fields($data, $post, $screens);

		// the unprefixed field (for default screen) will return default values since it doesn't exist. Don't save those.
		// off for now, somehow this impedes saving properly.
	/*	foreach($this->fields as $fieldname => $data)
		{
			if (isset($data[$this->blockname][$fieldname]))
					unset($data[$this->blockname][$fieldname]);
		} */

		return $data;

	}

	protected function getPresets($screen)
	{
		 $presets = array(
					'1080px' => array('name' => __('Modern Smartphone (1080px)', 'maxbuttons'), 'minwidth' => 0, 'maxwidth' => 1080),
					'768px' => array('name' =>  __('Medium Smartphone (768px)', 'maxbuttons'), 'minwidth' => 768, 'maxwidth' => 1080),
					'640px' => array('name' =>  __('Small screen (640px)', 'maxbuttons'), 'minwidth' => 640, 'maxwidth' => 768),
					'480px' => array('name' => __('Old Phone (480px)', 'maxbuttons'), 'minwidth' => 480, 'maxwidth' => 640),
		 );

		 if ($screen->is_new())
		 {
			  $presets = array_merge(array('none' => array('name' => __('No Preset', 'maxbuttons') ) ), $presets);
		 }

		 return $presets;
	}


	public function admin_fields($screen)
	{
		$admin = MB()->getClass('admin');
		if ($screen->is_new())
		{
			$screens = Screen::getScreens();
			$count = 0;
			foreach($screens as $id => $sobj)
			{
				if ($sobj->is_responsive())
					$count++;
			}
			if ($count >= 2 && $admin->screenLimit($count) === true)
			{
						$this->admin_limited($screen);
						return;
			}
		}


	//	return;
		$data = isset($this->data[$this->blockname]) ? $this->data[$this->blockname] : array();

		$start_block = new maxField('block_start');
		$start_block->name = __('responsive', 'maxbuttons');
		$start_block->label = __('Responsive Screen Settings', 'maxbuttons');
		$start_block->is_new = true;
		$screen->addField($start_block);

		$explain = new maxField('generic');
		$explain->id = $screen->getFieldID('responsive_explain_new');
		$explain->name = $explain->id;
		$explain->label = '&nbsp;';
		$explain->content = __("<p>Add a Responsive Screen to prepare your button for mobile devices. <br> Put the width and height of the view screen you want this design to be active at. <br> Then click the 'save changes and add screen' button.</p> ", 'maxbuttons');
		$explain->is_new = true;
		$explain->is_responsive =false;
		$screen->addField($explain, 'start', 'end');

		$rexplain = new maxField('generic');
		$rexplain->id = $screen->getFieldID('responsive_explain');
		$rexplain->name = $rexplain->id;
		$rexplain->label = '&nbsp';
		$rexplain->content = sprintf(__('%s The button on this screen will show with this layout on the defined screen conditions only. %s %s You can see what fields are different from the main button by the border marking. %s When hovering over the field it will show you the value of the main button :  %s %s', 'maxbuttons'), '<p>','</p>', '<p>','<br>', '<input type="text" class="responsive-changed example medium disabled" disabled value="' . __('example', 'maxbuttons') . '" title="' . __('Original value: example', 'maxbuttons') . '">' , '</p>');

		$rexplain->is_new = false;
		$rexplain->is_default = false;
		$screen->addfield($rexplain, 'start', 'end');


		$name = new maxField('text');
		$name->id = $screen->getFieldID('screen_name');
		$name->name = $name->id;
		$name->label = __('Screen Name', 'maxbuttons');
		$name->is_new = true;
		$name->value = $screen->getValue($name->id);
		$name->note = __('The internal name for this screen. [optional]');
		$screen->addField($name, 'start', 'end');

		$presets = $this->getPresets($screen);

		$preset_options = array();
		foreach($presets as $id => $data)
		{
			$preset_options[$id] = $data['name'];
		}

		$preset = new maxField('option_select');
		$preset->id = $screen->getFieldID('preset');
		$preset->name = $preset->id;
		$preset->label = __('Select a Preset');
		$preset->is_new = true;
		$preset->options = $preset_options;
		$screen->addField($preset, 'start', '');

		$preset_hidden = new maxField('hidden');
		$preset_hidden->id = $screen->getFieldID('preset-hidden');
		$preset_hidden->name = $preset_hidden->id;
		$preset_hidden->value = htmlentities(json_encode($presets));
		$preset_hidden->is_new = true;
		$screen->addfield($preset_hidden);

		$presetgo = new maxField('button');
		$presetgo->id = $screen->getFieldId('presetgo');
		$presetgo->name = $presetgo->id;
		$presetgo->button_label = __('Apply preset', 'maxbuttons');
		$presetgo->is_new = true;
		$presetgo->dataaction = 'set-preset';
		$presetgo->help = __('This will fill out the width and height fields according to the chosen preset. Select a preset and click "Apply Preset"', 'maxbuttons');
		$screen->addField($presetgo, '', 'end');

		$spacer = new maxField('spacer');
		$spacer->content = __('Selecting a preset will overwrite screen settings below', 'maxbuttons');
		$spacer->label = '&nbsp';
		$spacer->is_new = true;
		$spacer->name = '';
		$screen->addField($spacer, 'start', 'end');

		$minwidth = new maxField('number');
		$minwidth->id = $screen->getFieldID('min_width');
		$minwidth->name = $minwidth->id;
		$minwidth->label = __('Minimum Width', 'maxbuttons');
		$minwidth->after_input = __('px',  'maxbuttons');
		$minwidth->inputclass = 'small';
		$minwidth->min = 0;
		$minwidth->value = $screen->getValue($minwidth->id);
		$minwidth->help = __('Smallest screen size this screen will show. 0 for all', 'maxbuttons');
		$minwidth->is_new = true;
		$screen->addField($minwidth, 'start', '');

		$maxwidth = new maxField('number');
		$maxwidth->id = $screen->getFieldID('max_width');
		$maxwidth->name = $maxwidth->id;
		$maxwidth->label = __('Maximum Width', 'maxbuttons');
		$maxwidth->inputclass = 'small';
		$maxwidth->min = -1;
		$maxwidth->value = $screen->getValue($maxwidth->id);
		$maxwidth->help = __('Biggest screen size this screen will show. 0 for no maximum', 'maxbuttons');
		$maxwidth->is_new = true;
		$maxwidth->after_input = __('px',  'maxbuttons');
		$screen->addField($maxwidth, '', 'end');

		// checkbox - hide mb on this view
		$hide = new maxField('switch');
		$hide->id = $screen->getFieldID('hide_screen');
		$hide->name = $hide->id;
		$hide->label = __('Hide button in this screen', 'maxbuttons');
		$hide->value = 1;
		$hide->checked = checked($screen->getValue($hide->id), 1, false);
		$hide->help = __('When active, the button will not display at all in this screen. This can be used to hide the butt on devices', 'maxbuttons');
		$hide->is_new = true;
		$screen->addField($hide, 'start', 'end');

		// only responsive screens.
		$remove = new maxField('button');
		$remove->id = $screen->getFieldID('remove_screen');
		$remove->name = $remove->id;
		$remove->inputclass = 'block-button remove-screen';
		$remove->button_label = __('Remove this screen', 'maxbuttons');
		$remove->value = 'BAH';
		$screen->addField($remove, 'start', 'end');

		// when this hidden thing is filled with yes ( via js and the button ) a new screen should be added.
		$add = new maxField('hidden');
		$add->id = 'add_new_screen';
		$add->value = 'no';
		$add->name = $add->id;
		$add->is_new = true;
		$add->is_responsive = false;
		$screen->addField($add, '','');

		$save = new maxField('button');
		$save->id = $screen->getFieldID('add_screen');
		$save->name = $save->id;
		$save->button_label = __('Save changes and add new responsive screen', 'maxbuttons');
		$save->inputclass = 'button-save block-button';
		$save->is_responsive = false;
		$save->is_new = true;
		$screen->addField($save, 'start', 'end');


		// remove this screen
		$endblock = new maxField('block_end');
		$endblock->is_new = true;
		$screen->addField($endblock);

	}

	private function admin_limited($screen)
	{
		$start_block = new maxField('block_start');
		$start_block->name = __('upgrade', 'maxbuttons');
		$start_block->label = __('Upgrade Today', 'maxbuttons');
		$start_block->is_new = true;
		$screen->addField($start_block);

			$output = "<div class='upgrade-responsive'>";
			$output .= "<div class='removed-note'>" . __("Save your settings first to remove the screen", 'maxbuttons') . '</div>';
			$output .= '<p><h4>' . sprintf(__('You already have %s screens. To add more responsive screens to your buttons, upgrade to MaxButtons PRO.', 'maxbuttons'), $screen::countScreens() ) . '</b></h4>';
			$output .= '<h4>' . __('The best button editor for WordPress includes: ') . '</h4>';
			$output .= '<ul>';
			$output .= '<li>' . __('Infinite amount of screens', 'maxbuttons') . '</li>';
			$output .= '<li>' . __('Icons and Images', 'maxbuttons') . '</li>';
			$output .= '<li>' . __('Effects', 'maxbuttons') . '</li>';
			$output .= '<li>' . __('Google Fonts and Font-Awesome', 'maxbuttons') . '</li></ul>';

			$output .= '<div class="button-row"><a class="button button-primary buynow" href="https://maxbuttons.com" target="_blank">' . __('Buy Now', 'maxbuttons') . '</a></div>';
			$output .= '<div class="button-row"><a class="button features" href="' . admin_url('/admin.php?page=maxbuttons-pro') . '">' . __('See all features', 'maxbuttons') . '</a></div>';

			$output .= '</div>';

			$lim = new MaxField('generic');
			$lim->content = $output;
			$lim->is_new = true;
			$lim->is_responsive = false;
			$lim->id = 'upgrade';
			$screen->addField($lim,'start','end');

			$endblock = new maxField('block_end');
			$endblock->is_new = true;
			$screen->addField($endblock);

	}

} // class
