<?php
declare(strict_types=1);

namespace MaxButtons;
// instance of screen, to keep all data from one screen together.

class Screen
{
  protected $fields;
  protected $defined_fields = array(); // fields defined in admin
  protected $named_fields = array();  // fields that have an unique ID, but data field is based on name ( checkbox, radio etc )
  protected $mapped_fields = array();
  protected $templates_by_name = array('radio', 'checkbox', 'switch');

  protected $data;
  protected static $screens;

  public $id;
  public $name;

  private $prefix = '';
  private $is_responsive = false;
  private $is_new = false;
  private $is_default = false;


  public function __construct($id, $args = array())
  {
     $this->id = $id;

     if ($id == 'default')
     {
       $this->is_default = true;
     }
     elseif ($id == 'new')
     {
       $this->is_new = true;
     }
     else
     {
       $this->is_responsive = true;
     }

     if ($id !== 'default')
     {
      $this->prefix = $id . '_';
     }

     $this->name = isset($args['name']) ? $args['name'] : 'placeholder';
  }

  protected function setData($data)
	{

		$new_data = array(); //egalite
		if (! is_array($data) || count($data) == 0) // no data
			return false;

		foreach($data as $block => $fields)
		{
			if (is_array($fields))
				$new_data = array_merge($new_data, $fields);
		}

    $this->data = $new_data;
	}


  public static function setupScreens($data)
  {
  	$responsive = isset($data['responsive']) ? $data['responsive'] : array();
		$screens['default'] = array('name' => __('Main', 'maxbuttons'));

		if (is_array($responsive) && isset($responsive['screens']))
		{
			$responsive_screens = array_filter($responsive['screens']); // remove anything empty.

			foreach($responsive_screens as $screen_id)
			{
				$name = isset($responsive[$screen_id . '_screen_name']) ? $responsive[$screen_id . '_screen_name'] : false;
				$screens[$screen_id] = array('name' => $name);
			}
		}

		$screens['new'] = array('name' => __('Add', 'maxbuttons'));

    $screenObjs = array();

    foreach($screens as $screen_name => $this_screen_data)
    {
      $screen = new Screen($screen_name, $this_screen_data);
      $screen->setData($data);
      $screensObjs[$screen_name] = $screen;
    }

    self::$screens = $screensObjs;
    return $screensObjs;
  }

  public static function hasResponsive()
  {
     foreach(self::$screens as $screen)
     {
         if ($screen->is_responsive())
            return true;
     }

     return false;
  }

  public static function getScreens()
  {
     return self::$screens;
  }

  public static function countScreens()
  {
      return count(self::getScreens()) -1; // don't count new screen
  }

  // function to get field id, eventually converting it for responsive.
  public function getFieldID($name)
  {
    return $this->prefix . $name;
  }

  public function is_responsive()
  {
     return $this->is_responsive;
  }

  public function is_new()
  {
     return $this->is_new;
  }

  public function is_default()
  {
     return $this->is_default;
  }


  // add a maxfield to be displayed on the admin.
  public function addfield($field, $start = '', $end = '')
  {
    $field_id = isset($field->id) ? $field->id : $field->template . \rand(0,1000);

    // don't add if it's not our screen.
    if (! $this->isFieldThisScreen($field))
    {
      return;
    }

    $this->fields[$field_id] = array('field' => $field,
                   'start' => $start,
                   'end' => $end);
    $this->fields = apply_filters('mb/editor/addfield', $this->fields, $field, $this);

    $this->defined_fields[] = $field_id;
    // Radio and checkboxes more define as field_name ( same fieldname for all) as field_id, instead of an unique id. If not added here, the mapfields will skip it, because not in defined fields.
    if (in_array($field->template, $this->templates_by_name) )
    {
       $this->named_fields[$field_id] = $field->name;
    }
    do_action('mb/editor/afterfield/'. $field_id, $field, $this);
  }

  /** Insert a field before or after a existing field
  *
  * @param String $insert_field Existing field to perform operation on
  * @param Object $field The new field to insert
  * @param String $start Start Template
  * @param String $end End template
  * @param String $op Insert before of after specified insert field
  */
  public function insertField($insert_field, $field, $start = 'start', $end = 'end', $op = 'before')
  {
    $insert_pos = 0;

    // don't add if it's not our screen.
    if (! $this->isFieldThisScreen($field))
      return;

    $this->addField($field, $start, $end); // add the field to the array

		$added_field_index = array_search($field->id, array_keys($this->fields), true);

		$insert_field_index = array_search($insert_field, array_keys($this->fields), true);
		if ($insert_field_index === false)
		{
			 $insert_field_index = count($this->fields) -2;  // after the start block field, in the begin of block
		//	 $op = 'after';
		}

		if ($op == 'after')
		{
			$insert_field_index++;
		}

			$this->fields = array_slice($this->fields, 0, $insert_field_index, true) + array_slice($this->fields, $added_field_index, 1, true) +
			array_slice($this->fields, ($insert_field_index), null, true);

		return ;

		// old, @todo can  go if no bugs arise.
    foreach($this->fields as $field_id => $array)
    {
      if ($field_id == $insert_field)
      {

        break;
      }
      $insert_pos++;
    }

    // Find inserted field and remove it from array.
    $new_field_id = $field->id;
    $new_field_ar = $this->fields[$new_field_id];
    unset($this->fields[$new_field_id]);

    // Find position to insert new field
    // Yes this could be more efficient.
    $new_fields = array(); //$this->fields;
		$inserted = false;
    $i = 0;

    foreach($this->fields as $field_id => $array)
    {
      if ($i == $insert_pos && $op == 'before')
      {
        $new_fields[$new_field_id] = $new_field_ar;
				$inserted = true;
      }
       $new_fields[$field_id] = $array;
       if ($i == $insert_pos && $op == 'after')
       {
          $new_fields[$new_field_id] = $new_field_ar;
					$inserted = true;
       }
       $i++;
    }
		// This can happen if inserted example is not in responsive screen
		if ( $inserted === false)
		{
			 $new_fields[$new_field_id] = $new_field_ar;
		}

    $this->fields = $new_fields;
  }

  public function isFieldThisScreen($field)
  {
    if ($this->is_responsive && ! $field->is_responsive)
      return false;
    if ($this->is_default && ! $field->is_default)
      return false;
    if ($this->is_new && ! $field->is_new)
      return false;

    return true;
  }

  public function getFields()
  {
    return $this->fields;
  }

  // Maps fields to available fields, this is connecting the JS live Preview.
  public function mapFields($map)
  {
    $newmap = array();

    foreach($map as $name => $data) // make conversions for the screens.
    {
      $field_id = $this->getFieldID($name);

      if (in_array($field_id, $this->defined_fields))
        $newmap[$field_id] = $data;

      if (in_array($field_id, $this->named_fields))
      {
        //$matches = array_keys($this->named_fields, $field_id);
        // $fid == form field id, fname is form field.,
        foreach($this->named_fields as $fid => $fname)
        {
          if ($fname == $field_id)
          {
            $newmap[$fid] = $data;
          }

        }

        $newmap[$field_id] = $data;
      }


    }
    $this->mapped_fields = $newmap;
    return $newmap;
  }

  /** This is all a bit hacky, map can be delivered by param, or take the main thing */
  public function displayFieldMap($map = null)
  {
      if (is_null($map))
        $map = $this->mapped_fields;

// JS expects a fieldmap always.
        echo "<span class='fieldmap'>";
          echo json_encode($map);
        echo "</span>";
  }

  public function display_fields($clean = true, $return = false)
  {
    $fields = apply_filters('mb/display_fields', $this->fields);
    $output = '';

    if (! is_array($fields))
      return;

    foreach($fields as $id => $item)
    {
      $field = $item['field'];

      if ($field->publish == false) // don't publish this via screen, this is something manual.
        continue;

      $output .= $field->output($item['start'], $item['end']);
    }

    if ($clean)
    {
      $this->fields = array();
    }

    if (! $return)
      echo $output;
    else
      return $output;

  }

  protected function getParentFieldName($fieldname)
  {
  		return $prefixless = str_replace($this->prefix, '', $fieldname);
  }

  public function getValue($fieldname)
  {
    $value = false;

    if(isset($this->data[$fieldname]))
  	{
      $value = $this->data[$fieldname];
  	}
    elseif($this->is_responsive && strpos($fieldname, $this->prefix) >= 0) // try to retrieve original value of the responsive field.
    {
      $prefixless = $this->getParentFieldName($fieldname);

      if (isset($this->data[$prefixless]))
      {
        $value = $this->data[$prefixless];
      }
    }
    elseif ($this->getDefault($fieldname))
    {
      $value = $this->getDefault($fieldname);
    }

    return $value; // dunno.
  }

  public function getColorValue($fieldname)
  {
    $value = $this->getValue($fieldname);
    if (maxUtils::isrgba($value))
      return $value;

    if (! $value )
      return false;
    if (substr($value,0,1) !== '#')
    {
      $value = '#' . $value;
    }
    return $value;
  }

  public function getScreenIcon()
  {
  	$screen_type = $this->getScreenType();
  	$min_width = $this->getValue($this->getFieldID('min_width'));

  	switch ($screen_type)
  	{
  		case 'new':
  		 $icon = 'dashicons-plus';
  		break;
  		case 'responsive':
  		 if ($min_width >= 1024)
  			 $icon = 'dashicons-laptop';
  		 else
  			 $icon =  'dashicons-smartphone';
  		break;
  		case 'default':
  		default:
  		 $icon = 'dashicons-desktop';
  		break;
  	}

  	return $icon;
  }

  public function getScreenTitle()
  {
  	$min_width = $this->getValue($this->getFieldID('min_width'));
  	$max_width = $this->getValue($this->getFieldID('max_width'));

  	$display = $title = '';

  	if ($this->is_default())
  	{
  		$title =  __('Your main button for all screens. ', 'maxbuttons');

  	}
  	elseif ($this->is_new())
  	{
  		 $title = __('Add a new responsive screen for mobile devices', 'maxbuttons');
  		 $display = __('for mobile', 'maxbuttons');
  	}
  	elseif ($min_width && $max_width)
  	{
  		 $display = $min_width . __('px', 'maxbuttons') . ' - ' . $max_width . __('px', 'maxbuttons');
  		 $title = sprintf(__('Shows at screen size from %s to %s', 'maxbuttons'), $min_width . __('px', 'maxbuttons'), $max_width . __('px', 'maxbuttons'));
  	}
  	elseif (! $min_width && $max_width)
  	{
  		 $display = '< ' . $max_width . __('px', 'maxbuttons');
  		 $title = sprintf(__('Shows at screen size smaller than %s', 'maxbuttons'), $max_width . __('px', 'maxbuttons'));

  	}
  	elseif (! $max_width && $min_width)
  	{
  		 $display = '> ' . $min_width . __('px', 'maxbuttons');
  		 $title = sprintf(__('Shows at screen size bigger than %s', 'maxbuttons'), $min_width . __('px', 'maxbuttons'));
  	}
  	elseif (! $min_width && ! $max_width) // if somebody does something like this :/
  	{
  		 $display = '';
  		 $title = sprintf(__('Set width and height to use this responsive screen', 'maxbuttons'));
  	}

  	return array($display, $title);
  }

  public function getScreenType()
  {
  	$screen_type = ($this->is_responsive()) ? 'responsive' : 'default';
  	$screen_type = ($this->is_new()) ? 'new' : $screen_type;

  	return $screen_type;
  }

  public function getDefault($fieldname)
  {
    $fieldDef = maxBlocks::getFieldDefinition($fieldname) ;

    if ($fieldDef && isset($fieldDef['default']) )
    {
         return $fieldDef['default'];
    }


    return false; // doesn't know
  }

  public function removeScreen($data)
  {
     foreach($data as $block_name => $blockdata)
     {
       if (is_array($blockdata)) // document_id block has a non-array blockdata
       {
         foreach($blockdata as $field_name => $field_data)
         {
            $has_id = strpos($field_name, $this->id);
            if ($has_id !== false && $has_id === 0) // strict checks to fail-safe deletion.
            {
              unset($data[$block_name][$field_name]);
            }
         }
       }
     }
     return $data;
  }

} // class
