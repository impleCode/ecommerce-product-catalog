/*!
	impleCode Admin scripts v1.0.0 - 2014-02-11
	Adds appropriate scripts to admin settings
	(c) 2014 Norbert Dreszer - http://implecode.com
*/

jQuery(document).ready(function() {
var fixHelper = function(e, ui) {
	ui.children().each(function() {
		jQuery(this).width(jQuery(this).width());
	});
	return ui;
};

jQuery('.sort-settings tbody').sortable({
	update: function(event, ui){  
              jQuery('.sort-settings tbody tr').each(function(){
			  var r = jQuery(this).index() + 1;
			  jQuery(this).find('td .attribute-label').attr('name', '_attribute-label'+r);
			  jQuery(this).find('td .attribute-value').attr('name', '_attribute'+r);
			  jQuery(this).find('td .attribute-unit').attr('name', '_attribute-unit'+r);
			  
			  jQuery(this).find('td .shipping-label').attr('name', '_shipping-label'+r);
			  jQuery(this).find('td .shipping-value').attr('name', '_shipping'+r);
              })
             },
	helper: fixHelper,
	placeholder: 'sort-settings-placeholder',	
});
jQuery('.attributes .ui-sortable').height(jQuery('.attributes .ui-sortable').height());
jQuery('.shipping .ui-sortable').height(jQuery('.shipping .ui-sortable').height());
});