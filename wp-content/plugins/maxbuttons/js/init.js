var $ = jQuery;

jQuery(document).ready(function(jq) {

	$ = jq; // to counter noConflict bandits

	function runMaxInit()
	{

		if (typeof window.maxFoundry === 'undefined')
			window.maxFoundry = {};

		// editor loading when needed.
		if (typeof maxLivePreview === 'function')
		{
		 	window.maxFoundry.livePreview = maxLivePreview;
		}
		else
		{
		}

		window.maxFoundry.maxadmin = new maxAdmin();
	 	window.maxFoundry.maxadmin.init();

		window.maxFoundry.maxmodal = new maxModal();
		window.maxFoundry.maxmodal.init();

		window.maxFoundry.maxAjax = new maxAjax();
		window.maxFoundry.maxAjax.init();

		window.maxFoundry.maxTabs = new maxTabs();
		window.maxFoundry.maxTabs.init();

		$(window).trigger('maxbuttons-js-init-done');

	}

	runMaxInit();

}); /* END OF JQUERY */

var mbErrors = [];

window.setTimeout(function () {

	 if (typeof window.maxFoundry === 'undefined')
	 {
 		 	console.error('Detected MaxButtons load failure');

		  if (typeof maxbuttons_init === 'undefined' || typeof maxbuttons_init.initFailed === 'undefined')
			{
				 console.error('Detected MaxButtons is not loaded, but can\'t find the text to express it');
				 return false;
			}
		//	el = document.getElementById('maxbuttons');
			var el = document.querySelector('h1.title');
			var errormsg = document.createElement('div');
			errormsg.className = 'notice notice-error';
			errormsg.innerHTML = '<h1>' + maxbuttons_init.initFailedTitle + '</h1><p>' + maxbuttons_init.initFailed + '</p>';

			if (mbErrors.length > 0)
			{
				 errormsg.innerHTML += maxbuttons_init.initFailedDetectedErrors;
				 for(i =0; i < mbErrors.length; i++)
				 	 errormsg.innerHTML += mbErrors[i];
			}

			el.parentNode.insertBefore(errormsg, el.nextSibling);

	 }
}, 4000);


window.addEventListener('error', function (event)
{
		var note = event.message + ' - ' + event.filename + ':' + event.lineno + '<br>';
		console.error('Detected Error ' + note);
		mbErrors.push(note);
});
