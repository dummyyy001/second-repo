/*
 * kt admin swatches
 */

jQuery(document).ready(function ($) {
	// accordion open close
	$('.kt_swatches_attribute_panel .kt_attribute_label').click(function (event) {
		event.preventDefault();

		var panel = $(this).siblings('.kt_attribute_panel');

		if (panel.hasClass('panel_open')) {
			panel.removeClass('panel_open');
			$(this).removeClass('panel_active');
		} else {
			panel.addClass('panel_open');
			$(this).addClass('panel_active');
		}

		return false;

	});
	$('.kt_swatches_attribute_panel .kt_swatches_attribute_table_subhead').click(function (event) {
		event.preventDefault();

		var panel = $(this).siblings('.kt_swatches_attribute_single_options');

		if (panel.hasClass('panel_open')) {
			panel.removeClass('panel_open');
		} else {
			panel.addClass('panel_open');
		}

		return false;

	});
	$('.kt_swatches_attribute_panel .kt_swatch_color').each(function() {
	 	$(this).wpColorPicker({
            // a callback to fire whenever the color changes to a valid color
            change: function(event, ui){
                // Change only if the color picker is the user choice
                var btn = $(this);
                btn.closest('.kt_swatches_attribute_single').find('.kt_sas_preview_item').css('background-color', ui.color.toString());
            },
            // a callback to fire when the input is emptied or an invalid color
            clear: function() {},
            // hide the color picker controls on load
            hide: true,
            // show a group of common colors beneath the square
            // or, supply an array of colors to customize further
            palettes: true
        });
	});
	$('.kt_swatches_attribute_panel').on('change',  '.kt_select_swatches_type', function() {
		var value = $(this).find(':selected').val();
		if(value == 'color_image') {
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table').addClass('panel_open');
			$(this).closest('.kt_attribute_panel').find('.kt_attribute_extra_settings').addClass('panel_open');
		} else if(value == 'default') {
			$type = $(this).closest('.kt_swatches_attribute_panel').data('default-type');
			if($type == 'color_image') {
				$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table').addClass('panel_open');
				$(this).closest('.kt_attribute_panel').find('.kt_attribute_extra_settings').addClass('panel_open');
			} else {
				$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table').removeClass('panel_open');
				$(this).closest('.kt_attribute_panel').find('.kt_attribute_extra_settings').removeClass('panel_open');
			}
		} else {
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table').removeClass('panel_open');
			$(this).closest('.kt_attribute_panel').find('.kt_attribute_extra_settings').removeClass('panel_open');
		}
	});
	$('.kt_swatches_attribute_panel').on('change',  '.kt_select_swatches_size', function() {
		var value = $(this).find(':selected').val();
		if(value == 'default') {
			$size_value = $(this).closest('.kt_swatches_attribute_panel').data('default-size');
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_sas_preview_item').css('width',$size_value + 'px');
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_sas_preview_item').css('height',$size_value + 'px');
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table_subhead').css('line-height',$size_value + 'px');
		} else {
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_sas_preview_item').css('width',value + 'px');
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_sas_preview_item').css('height',value + 'px');
			$(this).closest('.kt_swatches_attribute_panel').find('.kt_swatches_attribute_table_subhead').css('line-height',value + 'px');
		}
	});
	$('.kt_swatches_attribute_single_options').on('change',  '.kt_select_swatches_type_single', function() {
		var value = $(this).find(':selected').val();
		var container = $(this).closest('.kt_swatches_attribute_single');
		if(value == 'color') {
			container.find('.kt_sas_option_image').css('display','none');
			container.find('.kt_sas_option_color').css('display','block');
			var color = container.find('.kt_swatch_color').val();
			container.find('.kt_sas_preview_item').css('background-color', color);
			container.find('.kt_sas_preview_item').css('background-image', 'none');
			container.find('.kt_sas_type').html('Color');
		} else { 
			container.find('.kt_sas_option_image').css('display','block');
			container.find('.kt_sas_option_color').css('display','none');
			var image = container.find('.kt_swatch_image').val();
			container.find('.kt_sas_preview_item').css('background-image', 'url(' + image + ')');
			container.find('.kt_sas_type').html('Image');
		}
	});

});

(function($){
	"use strict";

	$.kt_swatch_imgupload = $.kt_swatch_imgupload || {};

	$(document).ready(function () {
	     $.kt_swatch_imgupload();
	});
	$.kt_swatch_imgupload = function(){
	        // When the user clicks on the Add/Edit gallery button, we need to display the gallery editing
	        $('body').on({
	             click: function(event){
	                var current_imgupload = $(this).closest('.kt_swatches_attribute_single');

	                // Make sure the media gallery API exists
	                if ( typeof wp === 'undefined' || ! wp.media ) {
	                    return;
	                }
	                event.preventDefault();

	                var frame;
	                // Activate the media editor
	                var $$ = $(this);

	                // If the media frame already exists, reopen it.
	                if ( frame ) {
	                        frame.open();
	                        return;
	                    }

	                    // Create the media frame.
	                    frame = wp.media({
	                        multiple: false,
	                        library: {type: 'image'}
	                    });

	                        // When an image is selected, run a callback.
	                	frame.on( 'select', function() {

	                    // Grab the selected attachment.
	                    var attachment = frame.state().get('selection').first();
	                    frame.close();

	                    current_imgupload.find('.kt_swatch_image').val(attachment.attributes.url);
	                    current_imgupload.find('.kt_swatch_image_id').val(attachment.attributes.id);
	                    var thumbSrc = attachment.attributes.url;
	                    if (typeof attachment.attributes.sizes !== 'undefined' && typeof attachment.attributes.sizes.thumbnail !== 'undefined') {
	                        thumbSrc = attachment.attributes.sizes.thumbnail.url;
	                    } else {
	                        thumbSrc = attachment.attributes.icon;
	                    }
	                    current_imgupload.find('.kt_sas_preview_item').css('background-image', 'url(' + thumbSrc + ')');
	                });

	                // Finally, open the modal.
	                frame.open();
	            }

	        }, '.kt_swatches_upload_button');
	     };
})(jQuery);
