/*
 * kt variations plugin
 */
var kt_woo_extra_isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (kt_woo_extra_isMobile.Android() || kt_woo_extra_isMobile.BlackBerry() || kt_woo_extra_isMobile.iOS() || kt_woo_extra_isMobile.Opera() || kt_woo_extra_isMobile.Windows());
    }
};

jQuery(document).ready(function ($) {
	$('.kadence-wp-tips-container').each(function() {
		var $container = $( this );
		var item =  $( this ).find( '#' + $container.attr('aria-describedby') );
		if ( item ) {
			$( this ).on( 'mouseenter', function( e ) {
				item.attr('aria-hidden', 'false');
			} ).on( 'mouseleave', function( e ) {
				item.attr('aria-hidden', 'true');
			} );
		}
	});
	var select2Enable = (typeof $().select2 == 'function');

	function init_swatches() {
		$('.variations_form').each(function(){
			var $vform = $(this);
			var $vform_select = $vform.find('.variations td.product_value select');
			var $variations = $vform.find( '.single_variation_wrap' );
			var $use_ajax = $vform.data( 'product_variations' ) === false;
			var $product_variations = $vform.data( 'product_variations' );
			var count_variations = $vform.find('.variations td.value').length;
			
			$vform.on( 'click', '.reset_variations', function() {
				$vform.find('.kad_radio_variations .selectedValue').removeClass('selectedValue');
				$vform.find('.kad_radio_variations label' ).removeClass( 'kt_disabled ');
				$vform.find('.kad_radio_variations input[type="radio"]:checked' ).prop('checked', false);
				return false;
			} );
			$vform.on( 'reset_data', function() {
				$vform.find( '.single_variation_wrap_kad' ).find('.quantity').hide();
				$vform.find( '.single_variation .price').hide();
			} );
			$vform.on('click', '.select-option', function (e) {
				e.preventDefault();
			});
			$vform.on('change', '.variations input[type="radio"]', function (e) {
					var $this = $(this);

					//Get the wrapper select div
					var $option_wrapper = $this.closest('.kt-radio-variation-container');

					//Select the option.
					var $wc_select_box = $option_wrapper.find('select').first();

					// Decode entities
					var attr_val = $this.val();
					if ( attr_val.indexOf('"') !== -1 ) {
						var current_attr_val = "value='" + attr_val + "'";
					} else {
						var current_attr_val = 'value="' + attr_val + '"';
					}
					//$wc_select_box.trigger('focusin').children("[value='" + attr_val + "']").prop("selected", "selected").change();
					$wc_select_box.trigger('focusin');
					if ( $wc_select_box.find( 'option[' + current_attr_val + ']' ).length ) {
						$wc_select_box.trigger('focusin').val(attr_val).trigger('change');
					} else {
						$vform.find( '.variations select' ).val( '' ).change();
						$vform.find('.kad_radio_variations .selectedValue').removeClass('selectedValue');
						$vform.find('.kad_radio_variations label' ).removeClass( 'kt_disabled ');
						if ( select2Enable ) {
							$('.variations .kad-select').select2({minimumResultsForSearch: -1 });
						}
						$vform.find('.kad_radio_variations input[type="radio"]:checked' ).prop('checked', false);
						$vform.trigger( 'reset_data' );
						// add in the selection
						$vform.find('.kad_radio_variations input[type="radio"][' + current_attr_val + ']' ).prop('checked', true);
						$wc_select_box.trigger('focusin').val(attr_val).trigger('change');
					}
					$vform.find('.kad_radio_variations .selectedValue').removeClass('selectedValue');
					$vform.find('.kad_radio_variations input[type="radio"]:checked').next().addClass('selectedValue');

				});
				$vform.on('woocommerce_variation_has_changed', function() {
					$('.kad-select').trigger('update');
					if ( $use_ajax ) {
						if( $(window).width() > 790 && !kt_woo_extra_isMobile.any() ) {
							if ( select2Enable ) {
								$('.kad-select').select2({minimumResultsForSearch: -1 });
							}
						}
					}
				} );
				// Disable option fields that are unavaiable for current set of attributes
				$vform.on('woocommerce_variation_has_changed', function () {
					if ( $use_ajax ) {
						return;
					}
					var kt_current_settings = {};
					var variations = [];
					$vform.find( '.variations select' ).each( function() {
						$(this).trigger('focusin');
						$(this).find( 'option.enabled' ).each( function(){
							variations.push($( this ).val());
						});
					});
					$vform.find( '.variations .kad_radio_variations' ).each( function( index, el ) {

						var current_attr_radio = $( el );
						var current_attr_name = current_attr_radio.data( 'attribute_name' );
						current_attr_radio.find( 'input' ).removeClass( 'attached' );
						current_attr_radio.find( 'input' ).removeClass( 'enabled' );
						current_attr_radio.find( 'label' ).removeClass( 'kt_disabled ');

						// Loop through variations
						var i;
						for (i = 0; i < variations.length; ++i) {
							if ( variations[i].indexOf('"') !== -1 ) {
								current_attr_radio.find("input[value='" + variations[i] + "']").addClass( 'attached ' + 'enabled');
							} else {
								current_attr_radio.find('input[value="' + variations[i] + '"]').addClass( 'attached ' + 'enabled');
							}
						}
						current_attr_radio.find( 'input:not(.attached)' ).next().addClass('kt_disabled');
						});
				} );


				$variations.on('hide_variation', function() {
					$(this).css('height', 'auto');
				} );
				// Upon gaining focus
				$vform_select.on( 'select2-opening', function() {
					if ( ! $use_ajax ) {
						$vform.trigger( 'woocommerce_variation_select_focusin' );
						$vform.trigger( 'check_variations', [ $( this ).data( 'attribute_name' ) || $( this ).attr( 'name' ), true ] );
					}
				} );
			$(function() {
		    	if ( typeof wc_add_to_cart_variation_params !== 'undefined' ) {
		    		$( '.variations_form' ).each( function() {
						$( this ).find('.variations input[type="radio"]:checked').change();
					});
		    	}
		    });
		});
	}
	$(document).on( 'woosq_loaded', function() {
		init_swatches();
	} );
	$( '.bundle_form .bundle_data' ).each( function() {
		$( this ).on( 'woocommerce-product-bundle-initialized', function() {
			init_swatches();
		} );
	} );
	init_swatches();
});

