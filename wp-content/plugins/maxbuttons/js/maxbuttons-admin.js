'use strict';
var $ = jQuery;

function maxAdmin()
{ }

maxAdmin.prototype = {
	//initialized: false,
 	colorUpdateTime: true,
  colorPalettes: true,
 	fields: null,
 	button_id: null,
 	form_updated: false,
 	tabs: null,
}; // MaxAdmin

maxAdmin.prototype.init = function () {


		this.button_id = $('input[name="button_id"]').val();
 		// Prevents the output button from being clickable (also in admin list view )
		$(document).on('click', ".maxbutton-preview", function(e) { e.preventDefault(); e.stopPropagation(); });

 		// overview input paging
 		$('#maxbuttons .input-paging').on('change', $.proxy(this.do_paging, this));

		$('.manual-toggle').on('click', $.proxy(this.toggleManual, this));
		$('.manual-entry').draggable({
			cancel: 'p, li',
		});

		// copy / delete / trash action buttons via ajax
		$(document).on('click', '[data-buttonaction]', $.proxy(this.button_action, this ));

		// conditionals
			//$(document).on('reInitConditionals', $.proxy(this.initConditionials, this));
			this.initConditionials(); // conditional options


  	// range inputs
		$('#maxbuttons').on('change, input', 'input[type="range"]', $.proxy(this.updateRange, this ));
		this.updateRange(null);

		$('#bulk-action-all').on('click', $.proxy(this.checkAllInputs, this));

		/*
		****
		 ### After this only init for button main edit screen
		****

		*/
		if ($('#new-button-form').length == 0)
			return;

		if (this.button_id > 0) {
			$("#maxbuttons .mb-message").show();
		}

    // this events happens when live preview updates something.
    $(document).on('livePreviewUpdate', $.proxy(this.saveIndicator, this) );

    // screen changer.
    $('.screen-option').on('click', $.proxy(this.change_screen_event, this));
    $('.remove-screen').on('click', $.proxy(this.remove_screen, this));

		 $("#maxbuttons .output").draggable({
			cancel: '.nodrag',
		});


    /*$(document).on('click', '.iris-picker-inner .iris-square-value', function (e) {
        e.preventDefault(); e.stopPropagation(); return false; });*/

		/* Copy Color Interface */
		$('.input.mbcolor .arrows').on('click', $.proxy(this.copyColor, this) );

		$('[id$="radius_toggle"]').on('click', $.proxy(this.toggleRadiusLock,this));
    $('.output .preview-toggle').on('click', $.proxy(this.toggle_preview, this));

 		$('#maxbuttons input.mb-color-field').on('focus', $.proxy(this.select_field, this));

		$(window).on('beforeunload', $.proxy(function () { if (this.form_updated) return maxajax.leave_page; }, this));
		$(document).on('keyup', 'input', function (e) {

			if (e.keyCode && e.keyCode == 13)
			{
				$(":input")[$(":input").index(document.activeElement) + 1].focus();
				return false;
			}
		});

		$(".button-save").on('click', $.proxy(function(e) {
			this.saveIndicator(null, false); // prevent alert when saving.

      if ($(e.target).attr('id') == 'new_add_screen')
      {
       $('#add_new_screen').val('yes');
     }
			$("#new-button-form").trigger('submit');
			return false;
		}, this) );

		// Expand shortcode tabs for more examples.
		$('.shortcode-expand').on('click', this.toggleShortcode);

    // URL Linker.
    $('#url_button').on('click', $.proxy(this.openURLDialog, this) );

    // Sidebar slider
    $('.block_sidebar .open_control').on('click', this.toggleSidebar);

    $(window).on('maxbuttons-js-init-done', $.proxy(this.loadLivePreview, this));

    if ( $('input[name="button_is_new"]').val() == 1)
      this.saveIndicator(null, true);

		this.saveOnKey();

}; // INIT

maxAdmin.prototype.loadLivePreview = function()
{
  if (typeof window.maxFoundry.livePreview == 'function' )
  {
    this.livePreview = new window.maxFoundry.livePreview();
    this.livePreview.init();
    $(document).trigger('livepreview-loaded');

  }
  else {
    alert('Live Preview not loaded, button preview not functional');
		return false;
  }

	// See screen to responsive / what it's hash unless it's default.
  if (typeof window.location.hash !== 'undefined' && window.location.hash.length > 0 && window.location.hash !== '#default')
  {
    var screenid = window.location.hash.replace('#', '');
    // check if target exists and not some other's hash tag, otherwise load default.
    if (null !== this.getScreenTarget(screenid))
    {
      this.change_screen(screenid);
      return true;
    }
  }

	this.livePreview.screenInit('default'); // still needs to init one-time on default if so.
	this.livePreview.setCurrentScreen('default');

}

maxAdmin.prototype.change_screen_event = function(e)
{
  e.preventDefault();
  var $target = $(e.target);
  if (typeof ($target.data('screenid')) == 'undefined')
    $target = $target.parents('.screen-option');

  var change_screen = $target.data('screenid');
  if (typeof change_screen !== 'undefined')
    this.change_screen(change_screen);
}

/* Get target of a screen, see if it exists. Can return object or null */
maxAdmin.prototype.getScreenTarget = function(screenid)
{
  var target = document.querySelector('.screen-option[data-screenid="' + screenid + '"]');
  return target;
}

maxAdmin.prototype.change_screen = function(change_screen)
{

  var active_screen = $('.screen-option.option-active').data('screenid');
	var target  = this.getScreenTarget(change_screen);
  //= document.querySelector('.screen-option[data-screenid="' + change_screen + '"]');

  if (change_screen != 'new')
  {
    $('#current_screen').val(change_screen);
  }

  // not the same. and check for now, random #page id's can appear
  if (active_screen !== change_screen && target !== null)
  {
			// Change hash in URL
      window.location.hash = change_screen;

      $('.screen-option').removeClass('option-active');
      target.classList.add('option-active');
      $(document).trigger('changed_screen', change_screen);

      $('.mbscreen-editor').removeClass('current-screen');
      $('#screen_' + change_screen).addClass('current-screen');

			var title = target.getAttribute('title');
			var classes = target.querySelector('[data-screenicon]').dataset.screenicon;
			var name = target.querySelector('.screen_name').textContent;

			var previewModal = document.getElementById('live-preview-modal');
			if (previewModal)
			{
				 var icon = document.getElementById('live-preview-icon');
				 icon.className = '';
				 icon.classList.add('dashicons', classes);
				 document.getElementById('live-preview-screenname').textContent = name;
				 document.getElementById('live-preview-screentitle').textContent = title;

			}

			var form = document.getElementById('new-button-form');
			form.action = window.location;

  }
  //change_screen
}

maxAdmin.prototype.remove_screen = function(e)
{
  if (confirm(maxadmin_settings.remove_confirm))
  {
    var $target = $(e.target);
    var screen_id = $target.parents('.mbscreen-editor').data('screenid');
    $target.parents('.mbscreen-editor').remove(); // remove editor screen

    $('.screen-option[data-screenid="' + screen_id + '"]').remove(); // remove the menu tab
    $('input[name="screens[]"][value="' + screen_id + '"]').remove(); // remove the input hidden registration

    // indicator for add new screen when limited.
    $('.removed-note').show();

    this.change_screen('default');

    this.saveIndicator(null, true);
  }
}

maxAdmin.prototype.toggle_preview = function (e)
{
	if ( $('.output .inner').is(':hidden') )
	{
		$('.output .inner').show();
		$('.output').css('height', 'auto');
		$('.preview .preview-toggle').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
	}
	else
	{
		$('.output .inner').hide();
		$('.output').css('height', 'auto');
		$('.preview .preview-toggle').removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
	}
};

maxAdmin.prototype.select_field = function(e)
{
	$(e.target).select();
}

maxAdmin.prototype.button_action = function(e)
{
	e.preventDefault();
	var action = $(e.target).data('buttonaction');
  var confirm = $(e.target).data('confirm');


	this.form_updated = false;

  if (typeof confirm !== 'undefined')
  {
    var ret = window.confirm(confirm);
    if (! ret)
      return;
  }

	var button_id = $(e.target).data('buttonid');
	var nonce = $('input[name="' + action + '_nonce"]').val();
  var paged = $('input[name="paged"]').val();


	var url = maxajax.ajax_url;
	var data =
	{
		action: 'mb_button_action',
		button_action: action,
		button_id: button_id,
		nonce: nonce,

	};

  if (typeof paged !== 'undefined')
    data['paged'] = paged;

	$.post({
		url: url,
		data: data,
		success: function (data) {
			var response = JSON.parse(data);

			if (typeof response.redirection != 'undefined')
			{
				window.location = response.redirection;
			}
		},
		error: function () {
			console.error('error in button action' + action);
		},
	});
}

/* Check the copy modal and display a warning if the button has been changes */
maxAdmin.prototype.checkCopyModal = function(modal)
{
	if (this.form_updated)
	{
		modal.currentModal.find('.mb-message').show();

	}
	else
		$(modal.currentModal).find('.mb-message').hide();
}


maxAdmin.prototype.copyColor = function (e)
{
	e.preventDefault();
	e.stopPropagation(); // stop the color picker from closing itself.

	var target = $(e.target);
	var bindto = $(e.target).parents('[data-bind]');
	var fieldId = '#' + bindto.data('id'); // Field which is used
	var bindId = '#' + bindto.data('bind'); // Field is bound to.

	var field = document.getElementById(bindto.data('id'));
  var bindField = document.getElementById(bindto.data('bind'));

	// check which arrow was pressed
	if (target.hasClass('arrow-right'))
		var arrow_click = 'right';
	else
		var arrow_click = 'left';

	// check on which side the interface is. If arrows are on right side, it's the left side (...)
	if (bindto.hasClass('right') )
		var if_side = 'left';
	else
		var if_side = 'right';

	/* Decide which color to replace. If interface is left - then right click is copy to other element, but if interface is right, right is overwrite current element.
		Left : right click - copy, left replace.
		Right : right click - replace, left copy.
	*/
	if (if_side == 'left')
	{
		if (arrow_click == 'right')
			var copy = true;
		else
			var copy = false;
	}
	else if (if_side == 'right')
	{
		if (arrow_click == 'right')
			var copy = false;
		else
			var copy = true;
	}

	if ( copy )
	{
//		$(bindId).val( $(fieldId).val() );
		bindField.value = field.value;
		$(bindId).trigger('change');
		$(bindId).wpColorPicker('color', $(fieldId).val());
	}
	else
	{
		field.value = bindField.value;
		$(fieldId).trigger('change');
		$(fieldId).wpColorPicker('color', $(bindId).val());
 	}

}

maxAdmin.prototype.toggleRadiusLock = function (event)
{
	var target = $(event.target);
	var lock = $(target).data('lock');
	if (lock == 'lock')
	{
		$(target).removeClass('dashicons-lock').addClass('dashicons-unlock');
		$(target).data('lock', 'unlock');
	}
	else if (lock == 'unlock')
	{
		$(target).removeClass('dashicons-unlock').addClass('dashicons-lock');
		$(target).data('lock', 'lock');
	}

}

maxAdmin.prototype.initResponsive = function()
{

	//window.maxFoundry.maxadmin.responsive = new mbResponsive();
	//window.maxFoundry.maxadmin.responsive.init(this);

}


maxAdmin.prototype.do_paging = function(e)
{
	var page = parseInt($(e.target).val());

	if (page <= parseInt($(e.target).attr('max')) )
	{
		var url = $(e.target).data("url");
		window.location = url + "&paged=" + page;

	}
}


maxAdmin.prototype.toggleShortcode = function (e)
{
	if ($('.shortcode-expand').hasClass('closed'))
	{
		$(' .mb-message.shortcode .expanded').css('display','inline-block');
		$('.shortcode-expand span').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
		$('.shortcode-expand').removeClass('closed').addClass('open');
	}
	else
	{
		$(' .mb-message.shortcode .expanded').css('display','none');
		$('.shortcode-expand span').addClass('dashicons-arrow-down').removeClass('dashicons-arrow-up');
		$('.shortcode-expand').addClass('closed').removeClass('open');
	}

}

maxAdmin.prototype.toggleManual = function (e)
{
  e.preventDefault();
	var $target = $(e.target);

	var subject = $target.data("target");
	var $newWindow = $('.manual-entry[data-manual="' + subject + '"]');

	if ($newWindow.is(':visible'))
	{
		$newWindow.hide();
		return true;
	}
  var $destination = $target.parents('.option-container');

	$newWindow.css('top', '0px');
	$newWindow.css('right','-25%');
	$newWindow.prependTo($destination);
  $newWindow.show();
}

maxAdmin.prototype.resetConditionals = function()
{
		var fields = document.querySelectorAll('[data-show], [data-has]');

		/*fields.forEach(function (field) {
        var condition  = $(this).data('show');
        if (typeof condition === 'undefined')
        {
          condition = $(this).data('has');
        }
        if (typeof condition === 'undefined')
        {
          console.error($(this) + 'has a improperly set conditional');
          return;
        }
        var target = condition.target;
        $(document).off('change','[name="' + target + '"]'); // turn off event
    });*/



    $('[data-show], [data-has]').each(function () {
        var condition  = $(this).data('show');
        if (typeof condition === 'undefined')
        {
          condition = $(this).data('has');
        }
        if (typeof condition === 'undefined')
        {
          console.error($(this) + 'has a improperly set conditional');
          return;
        }
        var target = condition.target;
        $(document).off('change','[name="' + target + '"]'); // turn off event
    });


}

maxAdmin.prototype.initConditionials = function ()
{
	var mAP = this;
  //this.resetConditionals();
	var conditionals = document.querySelectorAll('[data-show]');
	var targets = {};


	conditionals.forEach(function(conditionalField)
	{
			if (typeof conditionalField.dataset.show !== 'undefined')
			{
		  	var condition = conditionalField.dataset.show;
				var condition_type = 'show';
			}
			else {
				var condition = conditionalField.dataset.has;
				var condition_type = 'has';
			}

			try
			{
				condition = JSON.parse(condition);
			}
			catch(errorVar)
			{
				 console.log('Data show error: ' + errorVar);
				 return;
			}
			var target = condition.target;
			var values = condition.values;

			// Collect all the targets that should trigger change.
		  if (! targets[target])
			{
				 targets[target] = [];
			}
			targets[target].push({'child' : conditionalField, 'values' : values, 'eventType': condition_type});

	});

	var changeEvent = new Event('change');


	// Target is the conditional field that want to be checked, on a target value.
	for (const target in targets)
	{
		  var element;

			if ( (element = document.getElementById(target)) !== null)
			{
				element.addEventListener('change', this.conditionalEvent.bind(this,targets[target]));
				element.dispatchEvent(changeEvent);
			}
			else if (document.querySelector('input[name="' + target + '"]') !== null)
			{
				var elements = document.querySelectorAll('input[name="' + target + '"]');

				for (var i = 0; i < elements.length; i++)
				{
					var element = elements[i];

					element.addEventListener('change', this.conditionalEvent.bind(this,targets[target]));

					if ( (element.type !== 'radio' && element.type !== 'checkbox') || element.checked === true )
					{
						elements[i].dispatchEvent(changeEvent);
					}

				}

			}

	};


	// Jquery has conditions. Can't be converted to vanilla Jscript, because that would require adding eventListener to all input[] array values and more important;  checking for updates / changes to the DOM and rebind them when getting active.

	var updatelist = [];

	$('[data-has]').each(function () {
		var condition = $(this).data('has');
		var target = condition.target;
		var values = condition.values;


	$('[name="' + target + '"]').on('change', {target: target, child: this, values: values}, $.proxy(mAP.updateHasConditional, mAP) );

	 var targetdecl = '[name="' + target + '"]';
	 if (! $.inArray(targetdecl, updatelist));
		updatelist.push(targetdecl);
	});

	if (updatelist.length > 0)
	{
		// the issue will a lot of event checking still exist..
		$(updatelist.toString()).first().trigger('change', ['conditional']);
	}

}

maxAdmin.prototype.conditionalEvent = function(data, event)
{
		// Do and extract data in loop
		for (var i = 0; i < data.length; i++)
		{
				if (data.eventType = 'show')
					this.updateConditional(data[i].values, data[i].child, event.target);
				else
					this.updateHasConditional(data[i].values, data[i].child, event.target);
		}

}

maxAdmin.prototype.updateConditional = function (cond_values, cond_child, eventTarget)
{
//	var data = event.data;
	//var cond_values = data.values;
	//var cond_child = data.child;

//	var target = eventTarget;
	var value = eventTarget.value;
	var name = eventTarget.name;
	var targetValues = [];
	//var elements = document.querySelector('')

	// if type = checkbox: cond_value checked means it has to be 'checked' to show. Otherwise 'unchecked' go hide.
	if (eventTarget.type === 'checkbox')
	{
		var checked = eventTarget.checked

		if (cond_values == 'checked' && checked)
			var value = 'checked';
		else if (cond_values == 'unchecked' && !checked)
			var value = 'unchecked';
		else
			var value = 0;
	}

	var $cond_child;
	var fadeIn = [ { opacity: 0 }, { opacity: 1 }];
	var fadeOut = [ { opacity: 1 }, { opacity: 0 }];
	var timing = { duration: 1000, iterations: 1};

	if (cond_values.indexOf(value) >= 0)
	{
  	$(cond_child).addClass('condshow').animate({'opacity': 1}, 300);
		$(cond_child).find('input, select').trigger('change');
	}
	else
	{
    $(cond_child).animate({'opacity': 0}, 300, function () { $(cond_child).removeClass('condshow')});
		$(cond_child).find('input, select').trigger('change');
	}
}

maxAdmin.prototype.updateHasConditional = function(event)
{
	var mAP = this;
	var data = event.data;

	var cond_values = data.values;
	var cond_child = data.child;

	var target = data.target;

	var hascond = false;

/** The issue here is to change this calls, to searches directly for value form cond_values ( mostly 1-3 options ) and not run the entire DOM each time.
*/
  var filter = [];
  $(cond_values).each(function (el)
  {

    filter.push( '[value=' + this + ']');
  } );

  if ($('[name="' + target + '"]').filter( filter.toString() ).length > 0)
  {
      hascond = true;
  }
  else {
      hascond = false
  }

	if (hascond)
	{
        $(cond_child).addClass('condshow').animate({'opacity': 1}, 300);
				$(cond_child).find('input, select').trigger('change');
	}
	else
	{
        $(cond_child).animate({'opacity': 0}, 300, function () { $(cond_child).removeClass('condshow')});
				$(cond_child).find('input, select').trigger('change');

	}

}

maxAdmin.prototype.updateRange = function (event)
{
	if (typeof event == 'undefined' || event === null )
	{
		var targets = $('input[type="range"]');
	}
	else
	{
		var targets = [event.target];
	}

	$(targets).each(function () {
		var value = $(this).val();
		$(this).parents('.input').find('.range_value output').val(value + '%');

	});

}

maxAdmin.prototype.saveIndicator = function(e, toggle)
{
	if (toggle)
	{
    	this.form_updated = true;
      $('.button-save').removeClass('disabled').addClass('button-primary');
  }
	else
  {
		this.form_updated = false;
    $('.button-save').addClass('disabled').removeClass('button-primary');
  }
}

// General AJAX form save
maxAdmin.prototype.formAjaxSave = function (e)
{
	e.preventDefault();
	var url = mb_ajax.ajaxurl;
	var form = $(e.target);

	var data = form.serialize();


	$.ajax({
	  type: "POST",
	  url: url,
	  data: data,

	}).done($.proxy(this.saveDone, this));
}

maxAdmin.prototype.saveDone = function (res)
{
	$('[data-form]').prop('disabled', false);

	var json = JSON.parse(res);

	var result = json.result;
	var title = json.title;

	var collection_id = json.data.id;

	if (typeof json.data.new_nonce !== 'undefined')
	{
		var nonce = json.data.new_nonce;
	 	$('input[name="nonce"]').val(json.data.new_nonce);
	}

	if (result)
	{
		// if collection is new - add collection_id to the field
		$('input[name="collection_id"]').val(collection_id);

		// replace the location to the correct collection
		var href = window.location.href;
		if (href.indexOf('collection_id') === -1)
			window.history.replaceState({}, '', href + '&collection_id=' + collection_id);

		// trigger other updates if needed
		$(document).trigger('mbFormSaved');

		// update previous selection to current state;
		var order = $('input[name="sorted"]').val();
		$('input[name="previous_selection"]').val(order);

		// in case the interface needs to be reloaded.
		if (json.data.reload)
		{
			document.location.reload(true);
		}

	}
	if (! result)
	{
		$modal = window.maxFoundry.maxModal;
		$modal.newModal('collection_error');
		$modal.setTitle(title);
		$modal.setContent(json.body);

		$modal.setControls('<button class="modal_close button-primary">' + json.close_text + '</button>');
		$modal.show();

	}
}

maxAdmin.prototype.openURLDialog = function(e)
{
  window.wpActiveEditor = 'url'; // $('input[name="url"]'); //true; //we need to override this var as the link dialogue is expecting an actual wp_editor instance

  if (window.ajaxurl.indexOf('ajax_maxbuttons') == -1) // otherwise it will start to echo
    window.ajaxurl = window.ajaxurl + '?ajax_maxbuttons=editor'; // This is to catch the Ajax Queryies for Link Dialog.

  wpLink.open(); //open the link popup

  $('#link-options').hide();
  $('.query-results').css('top', '70px');
  $('#wp-link-submit').off('click keyup change');
  $('#wp-link-submit').on('click', $.proxy(this.updateLink, this) );
  return false;
}

maxAdmin.prototype.updateLink = function (e)
{
   e.preventDefault();
   var old_url = $('#url').val();
   var url = $('#wp-link-url').val();
   var host = maxadmin_settings.homeurl;

   url = url.replace(host, '');

   $('#url').val(url);

   if (old_url != url)
   { // trigger update when changing URL
     $(document).trigger('livePreviewUpdate', true);
   }

   wpLink.close();
   return false;
}

maxAdmin.prototype.toggleSidebar = function(e)
{
  var target = e.target;
  var $sidebar = $(target).parents('.block_sidebar');

  if ($sidebar.hasClass('active'))
  {
    $sidebar.removeClass('active');
  }
  else {
    $sidebar.addClass('active');
  }

}

maxAdmin.prototype.saveOnKey = function()
{
	var saveForm = document.getElementById('new-button-form');
	if (saveForm === null)
		return false; // no form no save.

	var self = this;

	window.addEventListener('keydown', function(event) {

    if (! (event.key == 's' || event.key == 'S')  || ! event.ctrlKey)
		{
			return true;
		}
		self.saveIndicator(null, false);

		document.getElementById('new-button-form').submit();
    event.preventDefault();
    return false;
	});
}

maxAdmin.prototype.checkAllInputs = function (e)
{
	 e.preventDefault;
	 var mainbox = document.getElementById('bulk-action-all');

	 var selectMode = mainbox.checked;
	 var boxes = document.querySelectorAll('input[name="button-id[]"]');
	 for (var i = 0; i < boxes.length; i++)
	 {
		   boxes[i].checked = selectMode;
	 }


}
