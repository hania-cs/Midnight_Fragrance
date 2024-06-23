(function() {
     /* Register the buttons */
     tinymce.create('tinymce.plugins.maxButtons', {
          init : function(ed, url) {
               /**
               * Inserts shortcode content
               */
               ed.addButton( 'maxbutton', {
                    title : 'Insert MaxButton',
                    image : (typeof mbtrans !== 'undefined') ? mbtrans.icon : null,
                    onclick : function() {
                        var mm = new window.maxFoundry.maxMedia();
                 				mm.init();
                 				mm.openModal();
                    }
               });
          },
          createControl : function(n, cm) {
               return null;
          },
     });
     /* Start the buttons */
     tinymce.PluginManager.add( 'maxButtons_tinymce', tinymce.plugins.maxButtons );
})();
