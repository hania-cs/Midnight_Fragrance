'use strict';
var $ = jQuery;

// Here be gathering everything related to live updating MB.
function maxLivePreview()
{

}

maxLivePreview.prototype = {
  fields: {},
	fieldData: {},
  screens: {},
  currentScreen: '',
 // currentFields: {},
  reloadInProgress: false,
  reloaded: {}, // for partly reloads when doing a screen update.
	translations: {},
	tooltipListeners: {},
	screenDidInit: {}, // colorPickers are loaded on changeScreen (performance) but should run once. Hence this. Perhaps for other inits later
	preview_button_normal: null,
	preview_button_hover: null,
}

maxLivePreview.prototype.init = function ()
{
  this.loadScreens();
  this.bindFields();

	this.translations = lptranslations;

	this.preview_button_normal = $(document.getElementById('maxbuttons_preview_normal'));
	this.preview_button_hover = $(document.getElementById('maxbuttons_preview_hover'));

	this.initColorPicker(document.getElementById('button_preview'));
}

maxLivePreview.prototype.bindFields = function()
{
  // bind to all inputs, except for color-field or items with different handler.
  $('#maxbuttons input,#maxbuttons textarea').not('.mb-color-field').on('keyup change', $.proxy(this.update_preview_event,this));
  $('#maxbuttons select').on('change', $.proxy(this.update_preview_event, this));


   // Presets
 $('[data-action="set-preset"]').on('click', $.proxy(this.setPreset, this));

  $(document).on('changed_screen', $.proxy(this.changed_screen, this));

}

// on screen change, reload preview.
maxLivePreview.prototype.changed_screen = function(e, screen)
{
  // this needs a check to not run the default setup twice.
  //this.setCurrentScreen('default');
  //this.reloadFields();  // 'reload/reset' the preview to the main screen.
	this.screenInit(screen);

 // if (screen != 'default') // no need to reload if not default
  //{
    this.setCurrentScreen(screen);
    this.reloadFields(); // implement overrides of the screen.
 // }

}

// Function to on demand load certain heavy interface elements when screen is loaded. Should have to load only once!
maxLivePreview.prototype.screenInit = function(screen)
{
	if (this.screenDidInit[screen] === true)
	{
		console.log('Did already init, skip');
		return false;
	}

	var colorFields = document.querySelectorAll('#screen_' + screen + '.mbscreen-editor .mb-color-field');

	for (var i = 0; i < colorFields.length; i++)
	{
			this.initColorPicker(colorFields[i]);
	}
 	// Potential slowdown, but shitty to do elsehhow.
	// clicking on color picker circle scrolls to top and loads # on the page, which is bad.
	document.querySelectorAll('.iris-square-value').forEach ( function (e){ e.removeAttribute('href'); });

	this.screenDidInit[screen] = true;

}

maxLivePreview.prototype.initColorPicker = function(field)
{
	$(field).wpColorPicker(
	{
			width: 300,
			alpha: true,
			palettes: this.colorPalettes,
			change: $.proxy( _.throttle(function(event, ui) {
					this.update_color_event(event,ui);
			}, 300), this),
	});

}

maxLivePreview.prototype.reloadFields = function()
{
  $(document).trigger('livepreview-reload-start');
  this.reloadInProgress = true;
	this.reloaded = {};

  for(var mapfield in this.fields[this.currentScreen])
  {
     var data = this.fieldData[this.currentScreen][mapfield];

     this.update_preview( this.fields[this.currentScreen][mapfield], data);
  }
  this.reloadInProgress = false;


  $(document).trigger('livepreview-reload-end');
}

maxLivePreview.prototype.loadScreens = function ()
{
  var self = this;

  $('.mbscreen-editor .fieldmap').each(function()
  {
      var $screen = $(this).parents('.mbscreen-editor');
      var screen_id = $screen.attr('id').replace('screen_', '');
      var map = $(this).text();
      if (typeof map != 'undefined')
			{
				var fields = JSON.parse(map);
				var fieldcache = [] ;
				var fieldData = [];
				for (var field in fields)
				{
						var fcheck = document.getElementById(field);
						if (fcheck)
						{
							fieldcache[field] = fcheck;
							fieldData[field] = fields[field];
						}
						else { // checkboxes and radio shizzle.
							 var inputs = document.querySelectorAll('input[name="' + field + '"]');
							 for (var i = 0; i < inputs.length; i++)
							 {
								  var inputId = inputs[i].getAttribute('id');
								  fieldcache[inputId] = inputs[i];
									fieldData[inputId] = fields[field];
							 }

						}
				}
        self.fields[screen_id] = fieldcache;
				self.fieldData[screen_id] = fieldData;
			}
      /* Seems not to work / responsible.
			if ($screen.hasClass('current-screen'))
      {
        self.setCurrentScreen(screen_id);
      } */
  });

}

// function to help static function to get the proper ID's for the current field.
// byName is optional, signaling to search the field by form name instead of #id ( checkbox, radio )
// byName can result in multiple fields. (pass null not to use)
// getparent is to return the field  as defined in default screen. Many elements only exist there.
maxLivePreview.prototype.getFieldByID = function(name, byName, getparent)
{
  if (typeof byName == 'undefined' || byName == null)
    byName = false;

	if (typeof getparent == 'undefined')
	{
		getparent = false;
	}

  if (this.currentScreen  == 'default')
    var id = name;
  else
	{
		if (name.indexOf(this.currentScreen) == -1 && getparent === false)
		{
    	var id = this.currentScreen + '_' + name;
		}
		else if (getparent === true && this.isResponsiveScreen())
		{
			// Remove current screen prefix from id.
			var id = name.replace(this.currentScreen + '_', '');
			//	name.substring(name.indexOf('_') + 1);
		}
		else {
			var id = name;
		}
	}

  if (byName)
  {
    	var field = document.querySelectorAll('input[name="' + id + '"]');
  }
  else
  {
		if (typeof this.fields[this.currentScreen][id] == 'object' )
		{
			var field = this.fields[this.currentScreen][id];
		}
		else
		{
    	var field = document.getElementById(id); // $('#' + id);
		}
  }

  return field;
}

// returns object with definitions for CSS.
maxLivePreview.prototype.getFieldData = function(id)
{
		return this.fieldData[this.currentScreen][id];
}

maxLivePreview.prototype.getFields = function(id)
{
	 return this.fields[this.currentScreen];
}

maxLivePreview.prototype.setCurrentScreen = function(id)
{
  this.currentScreen = id;
  $(document).trigger('livepreview-screen-set', [id] );
}

maxLivePreview.prototype.isResponsiveScreen= function ()
{
		if (this.currentScreen.indexOf('rs') >= 0)
		{
			return true;
		}
		return false;
	  /*var screenDat
		a = document.querySelector('.screen-option.option-active');
		if (screenData && screenData.dataset.screentype == 'responsive')
		{
			 return true;
		}
	 	return false; */
}

maxLivePreview.prototype.isResponseField = function(field)
{

	 	if (field.id.indexOf(this.currentScreen) == -1)
		 {
			 return false;
		 }
		return true;
}

maxLivePreview.prototype.update_preview_event = function(e)
{
  e.preventDefault();

  // migration to data field
  var targetfield = e.target.dataset.field
  var id = e.target.getAttribute('id'); // this should change to be ready for the option to have two the same fields on multi locations.

  var field = this.fields[this.currentScreen][id];
	var data = this.fieldData[this.currentScreen][id];


  $(document).trigger('livePreviewUpdate', true);

  if (data !== null)
  {
    this.update_preview(field, data);
  }

  $(document).trigger('livepreview-field-updated', [e, field, data, id]);
}

/** Updates the preview buttons with new CSS lines. Extracts several fields from the fieldmap.
*  state = csspseudo
* field_id is ID of form field, data is corresponding field data in fieldmap.
*/
maxLivePreview.prototype.update_preview = function(field, data)
{

  var state = null;

	if (typeof data == 'undefined')
	{
		return; // field doesn't have updates
	}

  // check all attributes. Fields can use any of those for different processes.
  if (typeof data.css != 'undefined' && typeof data.func == 'undefined')
	{
		var value = field.value;

		// a target that is checkbox but not checked should unset (empty) value.
		if (field.type == 'checkbox' && ! field.checked )
		{
			value = '';
		}

    if (field.type == 'radio' && ! field.checked )
		{
      return; // not our radio update.
		}
    if (typeof data.pseudo !== 'undefined')
    {
      state = data.pseudo;
    }
		this.updateResponsiveFieldChanged(field, value);
		this.putCSS(data, value, state);
  }
	if (typeof data.attr !== 'undefined')
	{
		$('.output .result').find('a').attr(data.attr, field.value);
	}

  if (typeof data.func !== 'undefined')
  {
      var funcName = data.func;
      var self = this;
      if (funcName.indexOf('.') < 0)
      {
          funcName = 'self.' + funcName + '(target)';
      }
      else {
         funcName = funcName + '(target)';
      }

      try
      {
          var callFunc = new Function ('target', 'self', funcName);
          callFunc(field, this);
      }
      catch(err)
      {
        console.error(err, data, field);
      }

  }
};

maxLivePreview.prototype.updateResponsiveFieldChanged = function(field, value)
{
	  if (! this.isResponsiveScreen())
		{
			 return;
		}
		else if (field.type == 'radio')
		{
			if( ! field.checked)
			{
      	return; // not our radio update.
			}
			else {
				value = 1;
			}
		}
		else if (field.type == 'checkbox' )
		{
			if (! field.checked)
				var value = 0;
			else {
				var value = 1;
			}
		}

		var parentDefault = this.getFieldByID(field.id, false, true);

		if (parentDefault.type == 'checkbox' || parentDefault.type == 'radio')
		{
				if (parentDefault.checked)
			 		var parentValue = 1;
				else {
					var parentValue = 0;
				}
		}
		else {
			 var parentValue = parentDefault.value;
		}

 	// find label for input fields, or use field itself if not ?
		var field_id = field.id;

		if ( field.classList.contains('wp-color-picker'))
		{
			// shown field is first button from picker container, hopefully.
			 var field = field.closest('.wp-picker-container').children[0];

		}

		if (value != parentValue)
		{

				field.classList.add('responsive-changed');
				field.dataset.responseOriginal = parentValue;

				if (! this.tooltipListeners[field_id] || this.tooltipListeners[field_id] == false)
				{
					var handler = this.showOriginalValue.bind(this);
					this.tooltipListeners[field_id] = handler;
					field.addEventListener('mouseover', handler);
				}
		}
		else
		{
				  field.classList.remove('responsive-changed');
					delete field.dataset.responseOriginal;
					if (this.tooltipListeners[field_id])
					{
						field.removeEventListener('mouseover', this.tooltipListeners[field_id]);
						this.tooltipListeners[field_id] = false;
					}
		}
}

maxLivePreview.prototype.showOriginalValue = function(e)
{
	var field = e.target;

	// Their originals are pretty obvious
	if (field.type == 'radio' || field.type == 'checkbox')
		return;

	// I.e. color picker has a child span that should look at parent elemenet.
	if (typeof field.dataset.responseOriginal == 'undefined' && typeof field.offsetParent.dataset.responseOriginal !== 'undefined')
	{
	  var text = field.offsetParent.dataset.responseOriginal;
	}
	else {
		var text = field.dataset.responseOriginal;
	}
	var tooltip = document.createElement('span');
	tooltip.innerText = this.translations.originalValue + ' ' + text;
	tooltip.classList.add('original-tooltip');

	// probably many cases to add here.
	var tooltarget = field.closest('.input');

	field.addEventListener('mouseout', function () {
			var els = document.querySelectorAll('.original-tooltip');
			for (var i = 0; i < els.length; i++)
			{
				var el = els[i];
				el.parentNode.removeChild(el);
			}
	}, {once:true});

	tooltarget.appendChild(tooltip);
}

maxLivePreview.prototype.putCSS = function(data,value,state)
{
	state = state || 'both';
  if (typeof data == 'undefined')
    return false;

	//var element = '.maxbutton';
  var updateHover = true;
	var updateNormal = true;

	if (state == 'hover')
	{
	//	element = 'a.hover ';
		updateNormal = false;
	}
	else if(state == 'normal')
	{
	//	element = 'a.normal ';
		updateHover = false;
	}

  if (typeof data.unitfield != 'undefined')
  {

     var unitfielddata = this.getFieldByID(data.unitfield, true); //.filter(":checked"); // get by name, radio button
		 for (var i = 0; i < unitfielddata.length; i++)
		 {
			  if (unitfielddata[i].checked == true)
				{
					 var unitvalue = unitfielddata[i].value;
				}
		 }

     if (value == 0)
       value = 'auto';
     else if (unitvalue == 'percent' || unitvalue == '%')
       value += '%';
     else if(unitvalue == 'pixel' || unitvalue == 'px')
       value += 'px';

  }
  else if (typeof data.css_unit != 'undefined' && value.indexOf(data.css_unit) == -1)
  {
    if (value.indexOf(data.css_unit) == -1)
		  value += data.css_unit;
  }

	if (typeof data.csspart != 'undefined')
	{
		var parts = data.csspart.split(",");
		for(var i = 0; i < parts.length; i++)
		{
			var cpart = parts[i];
			//var fullpart = element + " ." + cpart;

			if (true === updateNormal)
			{
				 this.preview_button_normal.find('.' + cpart).css(data.css, value);
			}
			if (true === updateHover)
			{
				 this.preview_button_hover.find('.' + cpart).css(data.css, value);
			}
		  }
	}
	else // update on the main thing.
	{
		if (true === updateNormal)
		{
			 this.preview_button_normal.css(data.css, value);
		}
		if (true === updateHover)
		{
			 this.preview_button_hover.css(data.css, value);
		}
	}


}

maxLivePreview.prototype.update_color_event = function(event, ui)
{
		var target = event.target.getAttribute('id');
		var field = this.getFieldByID(target);
    var color = (ui.color.to_s('hex')); // since Alphapicker 3.0
    this.update_color(field, ui, color);
    $(document).trigger('livePreviewUpdate', true);

}

maxLivePreview.prototype.update_color = function(field, ui, color)
{
      var self = this;
      var id = field.getAttribute('id');

			if (color.indexOf('#') === -1 && color.indexOf('rgba') < 0)
      {
				color = '#' + color; // add # to color
      }

      field.value = color; //(color); // otherwise field value is running 1 click behind.
			this.updateResponsiveFieldChanged(field, color);

      // toggle transparency when needed.
      if ( field.value == '')
      {
        $(field).parents('.mbcolor').find('.wp-color-result').children('.the_color').css('background-image', 'url(' + maxadmin_settings.icon_transparent + ')');
        if (typeof event.type !== 'undefined' && event.type == 'change')
          this.update_color(field, null, 'rgba(0,0,0,0)');
      }
      else {
        $(field).parents('.mbcolor').find('.wp-color-result').children('.the_color').css('background-image', 'none');
      }

			if(id.indexOf('box_shadow') !== -1)
			{
				this.updateBoxShadow(field);
			}
			else if(id.indexOf('text_shadow') !== -1)
			{
				this.updateTextShadow(field);
			}
			else if (id.indexOf('gradient') !== -1)
			{
				if (id.indexOf('hover') == -1)
					this.updateGradient();
				else
					this.updateGradient(true);
			}
			else if (id == 'button_preview')
			{
        if (color.indexOf('rgba') >= 0)
        {
        }
				$(".output .result").css('backgroundColor',  color);
			}
			else  // simple update
			{

				var data = this.getFieldData(id);
        var state = 'normal';
        if (typeof data !== 'undefined' && typeof data.pseudo !== 'undefined')
        {
          state = data.pseudo;
        }

				this.putCSS(data, color, state);
			}
};

maxLivePreview.prototype.updateBoxShadow = function (field)
	{
	//	target = target || null;
  if (this.reloadInProgress && typeof this.reloaded.boxshadow !== 'undefined')
      return;

    var id = field.getAttribute('id');

  	var left = this.getFieldByID('box_shadow_offset_left').value;
		var top = this.getFieldByID("box_shadow_offset_top").value;
		var width = this.getFieldByID("box_shadow_width").value;
		var spread = this.getFieldByID('box_shadow_spread').value;

		var color = this.getFieldByID("box_shadow_color").value;
		var hovcolor = this.getFieldByID("box_shadow_color_hover").value;

    if (color == '') color = 'rgba(0,0,0,0)';
    if (hovcolor == '') hovcolor = 'rgba(0,0,0,0)';


		var data = this.getFieldData(id);
    if (typeof data == 'undefined') // field not defined.
      return;
		data.css = 'boxShadow';

    var value = left + 'px ' + top + 'px ' + width + 'px ' + spread + 'px ' + color;
    var value_hover = left + 'px ' + top + 'px ' + width + 'px ' + spread + 'px ' + hovcolor;

    this.putCSS(data, value, 'normal');
    this.putCSS(data, value_hover, 'hover');

    this.reloaded.boxshadow = true;

	}

maxLivePreview.prototype.updateTextShadow = function(field)
	{
	//	hover = hover || false;
  if (this.reloadInProgress && typeof this.reloaded.textshadow !== 'undefined')
      return;

		var left = this.getFieldByID("text_shadow_offset_left").value;
		var top = this.getFieldByID("text_shadow_offset_top").value;
		var width = this.getFieldByID("text_shadow_width").value;

		var color = this.getFieldByID("text_shadow_color").value;
		var hovcolor = this.getFieldByID("text_shadow_color_hover").value;

		var id = field.getAttribute('id');
		var data = this.getFieldData(id);

    if (typeof data == 'undefined') // field not defined.
      return;
		data.css = 'textShadow';

    if (color == '') color = 'rgba(0,0,0,0)';
    if (hovcolor == '') hovcolor = 'rgba(0,0,0,0)';

		var value = left + 'px ' + top + 'px ' + width + 'px ' + color;
		this.putCSS(data, value, 'normal');

		value = left + 'px ' + top + 'px ' + width + 'px ' + hovcolor;
		this.putCSS(data, value, 'hover');

    this.reloaded.textshadow = true;
	}

maxLivePreview.prototype.updateAnchorText = function (field)
	{
		var preview_text = $('.output .result').find('a .mb-text');

		// This can happen when the text is removed, button is saved, so the preview doesn't load the text element.
		if (preview_text.length === 0)
		{
			$('.output .result').find('a').append('<span class="mb-text"></span>');
		$('.output .result').find('a .mb-text').css({'display':'block','line-height':'1em','box-sizing':'border-box'});

			this.reloadFields();
		}
		$('.output .result').find('a .mb-text').text(field.value);
	}

maxLivePreview.prototype.updateGradientOpacity = function(field)
	{
		this.updateGradient(true);
		this.updateGradient(false);
	}

maxLivePreview.prototype.updateDimension = function (field)
{
  if (this.reloadInProgress && typeof this.reloaded.dimension !== 'undefined')
	{
      return;
	}

    // Set on input like radio / checkbox which have multi-input for same field data.
    if (typeof field.dataset !== 'undefined' && typeof field.dataset.field !== 'undefined')
    {
        var field_id = field.dataset.field;
    }
    else if (typeof field.id !== 'undefined')
    {
       var field_id = field.id;
    }
    else {
      return;
    }

    var data = {}; // to pass on to updateCS
    var fieldData = this.getFieldData(field.id);
    var state = '';


    // New: see if this doesn't bug somehow
    if (field_id.indexOf('width') >= 0)
      var type = 'width';
    else if (field_id.indexOf('height') >= 0)
      var type = 'height';

    // is_sub_of defined a sub relation to main field. So if set, the unitfield has been updated.
    if (typeof fieldData.is_sub_of !== 'undefined')
    {
        var unitfields = this.getFieldByID(field_id, true);;
        var field = this.getFieldByID(fieldData.is_sub_of);
        var fieldData = this.getFieldData(field.id);
    }
    else {
        var field = this.getFieldByID(field_id);
        var unitfields = this.getFieldByID(fieldData.unitfield, true);
        var fieldData = this.getFieldData(field.id);
    }

    var unitUpdate = '.input.' + field.getAttribute('name') + ' .unit';
    data.css = type;

    var updatePreview = '.preview_border_' + type + ' span';


    // Support for changing the width only on certain states ( hover / normal)
    // check if the button hover in effects is active, is so, the normal width/ height should only apply to normal in live preview
    var enable = document.getElementById('button_hover_enable');

    if (null !== enable && true === enable.checked && (field.id == 'button_width' || field_id == 'button_height'))
    {
        fieldData.pseudo = 'normal';
    }

    // If some pseudo is passed, update only the sizes for that one.
    if (fieldData.pseudo)
    {
       state = fieldData.pseudo;
       updatePreview = '.' + fieldData.pseudo + updatePreview;
    }

    var dimension = field.value;
		for (var i = 0; i < unitfields.length; i++)
		{
			 	if (unitfields[i].checked == true)
				{
						var unit = unitfields[i].value;
				}
		}

    if (dimension == 0)
    {
       unit = '';
       dimension = 'auto';
       this.putCSS(data, 'auto', state);
    }

    if (unit == 'percent')
      unit = '%';
    if (unit == 'pixel')
      unit = 'px';

    data.css_unit = unit;

    $(updatePreview).text(dimension + unit);
    $(updatePreview).css('width', dimension + unit);

    this.putCSS(data, dimension, state);
    $(unitUpdate).text(unit);

    this.reloaded.dimension = true;
}

// Update the numbers on the rulers of the live preview ( preview_border )
maxLivePreview.prototype.updatePreviewUnit = function(dimension, unit, type)
{

    var updatePreview = '.preview_border_' + type + ' span';

    $(updatePreview).text(dimension + unit);
    $(updatePreview).css('width', dimension + unit);

}

maxLivePreview.prototype.updateRadius = function(field)
{
  if (this.reloadInProgress && typeof this.reloaded.radius !== 'undefined')
      return;

  var value = field.value;
  var fields = ['radius_bottom_left', 'radius_bottom_right', 'radius_top_left', 'radius_top_right'];

  var toggle = this.getFieldByID('radius_toggle');

  if ( $(toggle).data('lock') == 'lock')
  {
  	for(var i = 0; i < fields.length; i++)
  	{
  		var field = this.getFieldByID(fields[i]); // find the real field.
      field.value = value; // set value via locking
      var id = field.getAttribute('id'); // get the real id, from element.
  		var data = this.getFieldData(id);
  		this.putCSS(data,value + 'px'); // update

  	}
  }
  else {  // update as regular single field
    var value = field.value;
    var id = field.getAttribute('id');
    var data = this.getFieldData(id);
    this.putCSS(data, value);
  }

  this.reloaded.radius = true;

}

maxLivePreview.prototype.getGradient = function(hover)
{
		hover = hover || false;

		var hovtarget = '';
		if (hover)
			hovtarget = "_hover";

		var stop = parseInt(this.getFieldByID('gradient_stop').value);

		if (isNaN(stop) )
			stop = 45;

		var gradients_on = this.getFieldByID('use_gradient').checked;

    var color = this.getFieldByID('gradient_start_color' + hovtarget).value;
    var endcolor = this.getFieldByID('gradient_end_color' + hovtarget).value;

    if (color == '') color = 'rgba(0,0,0,0)';
    if (endcolor == '') endcolor = 'rgba(0,0,0,0)';

		var start = this.hexToRgb(color);
		var end = this.hexToRgb(endcolor);
		var startop = parseInt(this.getFieldByID('gradient_start_opacity' + hovtarget).value);
		var endop = parseInt(this.getFieldByID('gradient_end_opacity' + hovtarget).value);

			if (! gradients_on)
			{
				end = start;
				endop = startop;
			}

			if(isNaN(startop)) startop = 100;
			if(isNaN(endop)) endop = 100;

    if (start.indexOf('rgba') < 0)
      var startrgba = "rgba(" + start + "," + (startop/100) + ") ";
    else
      var startrgba = start;

    if (end.indexOf('rgba')  < 0)
      var endrgba = "rgba(" + end + "," + (endop/100) + ") ";
    else
      var endrgba = end;

    var gradient = 'linear-gradient(' + startrgba + stop + "%," +  endrgba + ')';

    return gradient;
}

maxLivePreview.prototype.updateGradient = function(hover)
{
	var reloaded = (hover) ? typeof this.reloaded.gradienthover : typeof this.reloaded.gradient;
  if (this.reloadInProgress && reloaded !== 'undefined')
	{
		 return;
	}

  var gradient = this.getGradient(hover);

  if (!hover)
		var button = this.preview_button_normal;
	else
		var button = this.preview_button_hover

  button.css("background", gradient);

  $(document).trigger('livepreview/gradient/updated', [gradient, hover]);

	if (hover)
	{
    this.reloaded.gradienthover =  true;
	}
	else {
	   this.reloaded.gradient =  true;
	}
}

maxLivePreview.prototype.updateContainerUnit = function(unused)
{
  var field = this.getFieldByID('container_width_unit', true);

	for (var i = 0; i < field.length; i++)
	{
		 if (field[i].checked == true)
		 {
			  var val = field[i].value;
		 }
	}

  if (val == 'pixel')
    val = 'px';
  else
    val = '%';

  var cwidth = this.getFieldByID('container_width').getAttribute('name');

  $('.option.' + cwidth + ' .unit').text(val);
}

maxLivePreview.prototype.setPreset = function(e)
{
	var options = document.querySelector('#' + this.currentScreen + '_preset-hidden').value;
  var setPreset = document.querySelector('#' + this.currentScreen + '_preset option:checked').value;
  options = JSON.parse(options);


  var $minfield = $('#' + this.currentScreen + '_min_width');
  var $maxfield = $('#' + this.currentScreen + '_max_width');

  if (options[setPreset] && setPreset !== 'none')
  {
     var option = options[setPreset];
     var minwidth = option.minwidth;
     var maxwidth = option.maxwidth;

     if (minwidth <= 0) minwidth = 0;
     if (maxwidth <= 0) maxwidth = 0;

     $minfield.val(minwidth);
     $maxfield.val(maxwidth);
  }

}

maxLivePreview.prototype.hexToRgb = function(hex) {
      if (hex.indexOf('rgba') >= 0)
        return hex;

			hex = hex.replace('#','');
			var bigint = parseInt(hex, 16);
			var r = (bigint >> 16) & 255;
			var g = (bigint >> 8) & 255;
			var b = bigint & 255;

			return r + "," + g + "," + b;
}
