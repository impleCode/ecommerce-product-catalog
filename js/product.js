/*!
 impleCode Product Scripts v1.0.0 - 2014-02-11
 Manages product related scripts
 (c) 2014 Norbert Dreszer - http://implecode.com
 */

jQuery(document).ready(function($) {
	reponsive_product_catalog();
        setTimeout("modern_grid_font_size()",0)
	$(window).resize( function() {
		reponsive_product_catalog();
                setTimeout("modern_grid_font_size()",0)
	});
        if (typeof colorbox == 'object') {
        $(".a-product-image").colorbox(product_object.lightbox_settings);
        }
        
        jQuery("#product_order_selector").change(function() { 
            jQuery("#product_order").submit(); 
        });
	
  $.ic = {
    /**
     * Implement a WordPress-link Hook System for Javascript 
     * TODO: Change 'tag' to 'args', allow number (priority), string (tag), object (priority+tag)
     */
    hooks: { action: {}, filter: {} },
    addAction: function( action, callable, tag ) {
      jQuery.ic.addHook( 'action', action, callable, tag );
    },
    addFilter: function( action, callable, tag ) {
      jQuery.ic.addHook( 'filter', action, callable, tag );
    },
    doAction: function( action, args ) {
      jQuery.ic.doHook( 'action', action, null, args );
    },
    applyFilters: function( action, value, args ) {
      return jQuery.ic.doHook( 'filter', action, value, args );
    },
    removeAction: function( action, tag ) {
      jQuery.ic.removeHook( 'action', action, tag );
    },
    removeFilter: function( action, tag ) {
      jQuery.ic.removeHook( 'filter', action, tag );
    },
    addHook: function( hookType, action, callable, tag ) {
      if ( undefined == jQuery.ic.hooks[hookType][action] ) {
        jQuery.ic.hooks[hookType][action] = [];
      }
      var hooks = jQuery.ic.hooks[hookType][action];
      if ( undefined == tag ) {
        tag = action + '_' + hooks.length;
      }
      jQuery.ic.hooks[hookType][action].push( { tag:tag, callable:callable } );
    },
    doHook: function( hookType, action, value, args ) {
      if ( undefined != jQuery.ic.hooks[hookType][action] ) {
        var hooks = jQuery.ic.hooks[hookType][action];
        for( var i=0; i<hooks.length; i++) {
          if ( 'action'==hookType ) {
            hooks[i].callable(args);
          } else {
            value = hooks[i].callable(value, args);
          }
        }
      }
      if ( 'filter'==hookType ) {
        return value;
      }
    },
    removeHook: function( hookType, action, tag ) {
      if ( undefined != jQuery.ic.hooks[hookType][action] ) {
        var hooks = jQuery.ic.hooks[hookType][action];
        for( var i=hooks.length-1; i>=0; i--) {
          if (undefined==tag||tag==hooks[i].tag)
            hooks.splice(i,1);
          }
        }
      }
  }
});

function reponsive_product_catalog() {
var list_width = jQuery(".product-list").width();
var product_page_width = jQuery("article.al_product").width();
if (list_width < 600) {
	jQuery(".product-list").addClass("responsive");
}
else {
	jQuery(".product-list").removeClass("responsive");
}
if (product_page_width < 600) {
	jQuery("article.al_product").addClass("responsive");
}
else {
	jQuery("article.al_product").removeClass("responsive");
}
}

function modern_grid_font_size() {
    var fontSize = jQuery(".modern-grid-element").width() * 0.08; // 10% of container width
    if (fontSize < 16) {
    jQuery(".modern-grid-element h3").css('font-size', fontSize);
    jQuery(".modern-grid-element .product-price").css('font-size', fontSize);
    fontSize = fontSize * 0.8;
    jQuery(".modern-grid-element .product-attributes table").css('font-size', fontSize);
}
}