<?php
declare(strict_types=1);
namespace MaxButtons;
defined('ABSPATH') or die('No direct access permitted');
$blockClass["advanced"] = "advancedBlock";
$blockOrder[80][] = "advanced";


class advancedBlock extends maxBlock
{
	protected $blockname = "advanced";
	protected $is_responsive = false;
	protected $fields = array("important_css" => array("default" => "0"),
						"custom_rel" => array('default' => ''),
						"extra_classes" => array('default' => ''),
						"external_css" => array("default" => "0"),



						);

 	public function __construct()
 	{
 		parent::__construct();
 		add_filter('mb/button/rawcss', array($this, 'parse_css_advanced'), 1001, 2);  // run once
 		//add_filter('mb-css-blocks', array($this, 'preview_external_css'), 100 )
	}

	public function parse_css($css, $screens, string $mode = 'normal')
	{
		$css = parent::parse_css($css, $screens, $mode);
		$data = $this->getBlockData();

		if (isset($data["important_css"]) && $data["important_css"] == 1)
		{
			$css["settings"]["important"] = 1;
		}

		return $css;
	}

	public function parse_css_advanced($css, $mode)
	{
		$data = $this->getBlockData();

		if (isset($data["external_css"]) && $data["external_css"] == 1 && $mode == 'normal')
		{

			return array(
				"normal" => array(),
				"hover" => array());
		}

		return $css;
	}

 	public function parse_button($domObj, $mode = 'normal')
	{

		$data = $this->getBlockData();

		$button_id = $this->data["id"];
		$anchor = $domObj->find("a",0);

 		if (isset($data["custom_rel"]) && trim($data["custom_rel"]) != '')
 		{
 			$rel = '';
 			$custom_rel = trim($data["custom_rel"]);
 			if (isset($anchor->rel) && $anchor->rel != '')
 			{
 				$anchor->rel .= ' ';
 			}

 			$anchor->rel .= esc_attr($custom_rel);
 		}

 		if (isset($data["external_css"]) && $data["external_css"] == 1)
 		{
  			$anchor->class .= ' external-css ' ;
 		}

 		return $domObj;

 	}

	public function post_parse_button($domObj, $mode = 'normal')
	{
		$data = $this->getBlockData();
		$button_id = $this->data["id"];
		$anchor = $domObj->find("a",0);

		if (isset($data["extra_classes"]) && trim($data["extra_classes"]) != '')
		{
			$extra = trim($data["extra_classes"]);
			$anchor->class .= esc_attr(' ' . $extra);
		}

		return $domObj;
	}


	public function admin_fields($screen)
	{
		$data = $this->getBlockData();

				$start_block = new maxField('block_start');
				$start_block->name = __('advanced', 'maxbuttons');
				$start_block->label = __('Advanced', 'maxbuttons');
				$screen->addField($start_block);

				$imp = new maxField('switch');
				$imp->note = __('Adding !important to the button styles can help avoid potential conflicts with your theme styles.', 'maxbuttons') ;
				$imp->id = $screen->getFieldID('important_css');
				$imp->name = $imp->id;
				$imp->value = 1;
				$imp->label = __('Use !Important', 'maxbuttons');
				$imp->checked = checked($screen->getValue('important_css'), 1, false);
				$imp->is_responsive = false;
				$screen->addField($imp, 'start', 'end');

				$class = new maxField();
				$class->id = $screen->getFieldID('extra_classes');
				$class->name = $class->id;
				$class->label = __("Extra classes","maxbuttons");
				$class->value = $screen->getValue($class->id);
				$class->note = __("Useful for custom code or other plugins who target classes", "maxbuttons");
				$class->help = "<p class='shortcode'>Shortcode attribute : extraclass </p> <p>Using attribute will add classes, not replace them </p> ";
				$class->is_responsive = false;
				$screen->addField($class, 'start', 'end');

				$rel = new maxField();
				$rel->id = $screen->getFieldID('custom_rel');
				$rel->name = $rel->id;
				$rel->label = __("Custom Rel Tag","maxbuttons");
				$rel->value = $screen->getValue($rel->id);
				$rel->note = __("Useful when button is targeting lightbox and/or popup plugins that use this method", "maxbuttons");
				$rel->is_responsive = false;
				$screen->addField($rel, 'start', 'end');

			//	do_action('mb-after-advanced');

				$nocss = new maxField('switch');
				$nocss->note = __('Enabling the "Use External CSS" will stop loading any button styling. You will need to put the button style manually', 'maxbuttons');
				$nocss->label = __('Use External CSS', 'maxbuttons');
				$nocss->id = $screen->getFieldID('external_css');
				$nocss->value = 1;
				$nocss->name = $nocss->id;
				$nocss->checked = checked($screen->getValue($nocss->id), 1, false);
				$nocss->is_responsive = false;
				$screen->addField($nocss, 'start');

				$nospace = new maxField('spacer');
				$nospace->content = __("Warning: This will remove all styling of the buttons!","maxbuttons");
				$nospace->is_responsive = false;
			//	$nospace->output('','end');
				$screen->addField($nospace, '', 'end');

				$viewcss = new maxField('button');
				$viewcss->id = $screen->getFieldID('view_css_modal');
				$viewcss->name = $viewcss->id;
				$viewcss->label = '&nbsp;';
				$viewcss->button_label = __('View CSS', 'maxbuttons');
				$viewcss->inputclass = 'maxmodal';
				$viewcss->modal = 'view-css';
				$viewcss->is_responsive = false;
				$screen->addField($viewcss, 'start', 'end');

				$this->sidebar($screen);
				$endblock = new maxField('block_end');
				$screen->addField($endblock);

						if (false === $screen->is_responsive() && false === $screen->is_new()):

						?>

						<div id="view-css" class="maxmodal-data" >
								<h3 class="title"><?php _e("External CSS","maxbuttons"); ?></h3>
							<div class="content">
								<p><?php _e('If the "Use External CSS" option is enabled for this button, copy and paste the CSS code below into your theme stylesheet.', 'maxbuttons') ?></p>

							<textarea id="maxbutton-css" readonly="readonly">
							<?php
								if (isset($this->data["id"]) && $this->data['id'] > 0)
								{
									$id = $this->data["id"];
									$b = MB()->getClass('button');

									$b->set($id);
									$b->parse_button();
									$b->parse_css("preview");

									echo $b->getparsedCSS();

								}
 								else
 								{ _e("Please save the button first","maxbuttons"); }

							 ?></textarea>
							 </div>
							 <div class='controls'>
							 	<p><a class='button-primary modal_close' href='javascript:void(0);'><?php _e("Close","maxbuttons"); ?></a>
							 	</p>
							 </div>
						</div>

						<?php
						endif; // not-responsive
} // admin_fields

} // class
