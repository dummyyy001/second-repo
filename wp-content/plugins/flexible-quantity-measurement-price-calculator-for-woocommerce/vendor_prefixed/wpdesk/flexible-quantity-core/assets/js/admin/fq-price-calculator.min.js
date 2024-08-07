jQuery( function( $ ) {

	'use strict';

	/* global wc_price_calculator_admin_params */

	let variations_loaded = false;

	/**
	 * Toggles Stock fields visibility based if at lease one variation has stock management enabled
	 *
	 * @since 3.18.2
	 */
	function maybeToggleVariableProductStockFieldsVisibility() {

		if ( variations_loaded ) {

			let number_of_sock_managed_variations = $product_options.find( '.woocommerce_variations .checkbox.variable_manage_stock:checked' ).length;

			toggleStockFieldsVisibility( number_of_sock_managed_variations >= 1 );

			return;
		}
	}


	/**
	 * Toggles Stock fields visibility based on the given state
	 *
	 * @since 3.18.2
	 *
	 * @param {Boolean} show
	 */
	function toggleStockFieldsVisibility( show ) {

		let $stock_fields = $( 'p.stock_fields' );

		if ( show ) {
			$stock_fields.show();
		} else {
			$stock_fields.hide();
		}
	}


	/**
	 * Update the Product Data - General tab regular/sale price labels to include
	 * the 'per unit' for pricing calculator products
	 */
	function addPricingPerUnitLabel(pricingLabel) {

		var oldUnit = 'PLN';
		var newUnit = oldUnit + ' / ' + pricingLabel;

		// ie 'Regular Price ($ / ft)'
		var regPrice = $( 'label[for="_regular_price"]' );
		regPrice.html( regPrice.html().replace( oldUnit, newUnit ) );

		// ie 'Sale Price ($ / ft)'
		var salePrice = $( 'label[for="_sale_price"]' );
		salePrice.html( salePrice.html().replace( oldUnit, newUnit ) );

		// Variable product
		// ie 'Price ($ / ft)' and 'Sale Price
		$('.woocommerce_variable_attributes input[type="text"], .woocommerce_variable_attributes input[type="number"]').each(
			function(index,el) {
				el = $(el);
				if ( el.attr('name') && ( 'variable_price' === el.attr('name').substr(0, 14) || 'variable_regular_price' === el.attr('name').substr(0, 22) || 'variable_sale_price' === el.attr('name').substr(0, 19) ) ) {
					el.prev().html( el.prev().html().replace( oldUnit, newUnit ) );
				}
			}
		);

		// update the pricing table column headers
		var el = $( '.fq-calculator-pricing-table .measurement-range-column span.column-title' );
		el.text( el.data( 'text' ) + ' (' + pricingLabel + ')' );

		el = $( '.fq-calculator-pricing-table .price-per-unit-column span.column-title' );

		el = $( '.fq-calculator-pricing-table .sale-price-per-unit-column span.column-title' );
	}


	/**
	 * Update the Product Data - General tab regular/sale price labels to remove
	 * the 'per unit' for pricing calculator products
	 */
	function removePricingPerUnitLabel() {

		var oldUnit = new RegExp( /\(([^)]+)\)/ );

		// ie 'Regular Price ($)'
		var regPrice = $('label[for="_regular_price"]');
		regPrice.html( regPrice.html().replace( oldUnit, newUnit ) );

		// ie 'Sale Price ($)'
		var salePrice = $( 'label[for="_sale_price"]' );
		salePrice.html( salePrice.html().replace( oldUnit, newUnit ) );

		// Variable product
		// ie 'Price ($)' and 'Sale Price'
		$('.woocommerce_variable_attributes input[type="text"]').each(
			function(index,el) {
				el = $(el);
				if ( el.attr('name') && ( 'variable_price' === el.attr('name').substr(0, 14) || 'variable_regular_price' === el.attr('name').substr(0, 22) || 'variable_sale_price' === el.attr('name').substr(0, 19) ) ) {
					el.prev().html( el.prev().html().replace( oldUnit, newUnit ) );
				}
			}
		);
	}


	/**
	 * Update the Product Data - Inventory tab 'Stock Qty' label to include
	 * the 'per unit' for pricing calculator products with inventory management
	 * enabled
	 *
	 * @param string unitLabel the unit label to display
	 */
	function addInventoryPerUnitLabel(unitLabel) {
		// ie 'Stock Qty (ft)'
		removeInventoryPerUnitLabel();  // first clean the label in case we've already modified it
		var regularStockLabel = $('label[for="_stock"]').text() + ' (' + unitLabel + ')';
		$('label[for="_stock"]').text(regularStockLabel);

		// Variable product
		// ie 'Stock Qty (ft):'
		$('.woocommerce_variable_attributes input[type="number"]').each(
			function(index,el) {
				el = $(el);
				if ('variable_stock' === el.attr('name').substr(0, 14)) {
					var delimPos = el.prev().text().indexOf(':');
					var priceLabel = el.prev().text().substr(0, delimPos) + ' (' + unitLabel + '):';
					el.prev().text( priceLabel );
				}
			}
		);
	}


	/**
	 * Update the Product Data - General tab "Stock Qty" label to remove the
	 * 'per unit' for pricing calculator products with inventory management
	 * disabled
	 */
	function removeInventoryPerUnitLabel() {
		// ie 'Stock Qty (ft)'
		var regularStockLabel = $('label[for="_stock"]').text();
		if (-1 !== regularStockLabel.indexOf('(')) {
			regularStockLabel = regularStockLabel.substr(0, regularStockLabel.indexOf('(') - 1);
			$('label[for="_stock"]').text(regularStockLabel);
		}

		// Variable product
		// ie 'Stock Qty (ft):'
		$('.woocommerce_variable_attributes input[type="number"]').each(
			function(index,el) {
				el = $(el);
				if ('variable_stock' === el.attr('name').substr(0, 14)) {
					var regularStockLabel = el.prev().text();
					if (-1 !== regularStockLabel.indexOf('(')) {
						regularStockLabel = regularStockLabel.substr(0, regularStockLabel.indexOf('(') - 1) + ':';
						el.prev().text(regularStockLabel);
					}
				}
			}
		);
	}


	/**
	 * Update the Product Data - Shipping tab 'Weight (lbs)' label to include
	 * the 'per unit' for pricing calculator products with calculated weight
	 * enabled
	 *
	 * @param string unitLabel the unit label to display
	 */
	function addWeightPerUnitLabel( unitLabel ) {

		// ie 'Weight (lbs / ft)'
		removeWeightPerUnitLabel();  // first clean the label in case we've already modified it
		$('label[for="_weight"]').text(weightLabel);

		// Variable product
		// ie 'Weight (lbs / ft)'
		$( '.woocommerce_variable_attributes input[type="number"]' ).each(
			function( index,el ) {
				el = $( el );
				if ( 'variable_weight' === el.attr( 'name' ).substr( 0, 15 ) ) {
					el.prev().text( weightLabel );
				}
			}
		);
	}


	/**
	 * Update the Product Data - Inventory tab "Weight" label to remove the
	 * 'per unit' for pricing calculator products with calculated weight
	 * disabled
	 */
	function removeWeightPerUnitLabel() {
		// ie 'Weight (lbs / ft)'
		$('label[for="_weight"]').text( weightLabel );

		// Variable product
		// ie 'Weight'
		$( '.woocommerce_variable_attributes input[type="number"]' ).each(
			function( index,el ) {
				el = $( el );
				if ( 'variable_weight' === el.attr( 'name' ).substr( 0, 15 ) ) {
					var weightLabel = el.prev().text();
					if ( -1 !== weightLabel.indexOf( '(' ) ) {
						weightLabel = weightLabel.substr( 0, weightLabel.indexOf( '(' ) - 1 );
						el.prev().text( weightLabel );
					}
				}
			}
		);
	}


	/**
	 * Show the shipping weight field for simple/variation products
	 */
	function showShippingWeightField() {

		$( '._weight_field' ).show();

		$( '.woocommerce_variable_attributes input[type="number"]' ).each(
			function( index,el ) {
				el = $( el );
				if ( 'variable_weight' === el.attr( 'name' ).substr( 0, 15 ) ) {
					el.prev().show();
				}
			}
		);
	}


	/**
	 * Hide the shipping weight field for simple/variation products
	 */
	function hideShippingWeightField() {

		$( '._weight_field' ).hide();

		$( '.woocommerce_variable_attributes input[type="number"]' ).each(
			function( index,el ) {
				el = $( el );
				if ( 'variable_weight' === el.attr( 'name' ).substr( 0, 15 ) ) {
					el.prev().hide();
				}
			}
		);
	}


	// STOCK OPTIONS for our pricing inventory fields, hide/show based on the overall product inventory management
	$( 'input#_manage_stock' ).change( function () {

		toggleStockFieldsVisibility( $( this ).is( ':checked' ) );

		$( '._measurement_pricing_calculator_enabled' ).trigger( 'change' );
	} ).change();

	const $product_options = $( '#variable_product_options' );

	// Enable STOCK OPTIONS if at least one variation has stock management enabled
	$product_options.on( 'change', '.woocommerce_variations .checkbox.variable_manage_stock', maybeToggleVariableProductStockFieldsVisibility );

	// Make sure to display stock fields on page load if at lease one variation has stock management enabled
	maybeToggleVariableProductStockFieldsVisibility();

	// "Show Product Price Per Unit" checkbox handler: Show/hide the dependant
	// measurement pricing fields (label and unit), and "Set Product Pricing Per
	// Unit" (pricing calculator)
	$('._measurement_pricing').change(
		function() {

			// if the current measurement pricing toggle is associated with the currently selected measurement pricing calculator type (Dimensions, Area, Area (LxW), etc)
			if ('_measurement_' + $('#_measurement_price_calculator').val() + '_pricing' === $(this).attr('id')) {
				if ($(this).is(':checked')) {
					$(this).closest('div.measurement_fields').find('._measurement_pricing_fields').show(); // display the pricing fields
				} else {
					$(this).closest('div.measurement_fields').find('._measurement_pricing_fields').hide();
				}

				// let the dependent pricing per unit field know there was a change
				$(this).closest('div').find('._measurement_pricing_calculator_enabled').change();
			}
		}
	);


	// "Set Product Pricing Per Unit" checkbox handler: this enables the "pricing
	// calculator" mode by adding/removing the 'per unit' label for regular/sale
	// price for pricing calculator products, both on page load, and when the
	// checkbox element is toggled.  Also show/hide the measurement pricing per
	// unit dependent field: Inventory and Weight
	$('._measurement_pricing_calculator_enabled').change(
		function() {
			maybeToggleVariableProductStockFieldsVisibility();

			// if the current measurement pricing toggle is associated with the currently selected measurement pricing calculator type (Dimensions, Area, Area (LxW), etc)
			//  and the parent field 'Show Product Price Per Unit' is also enabled
			if ( '_measurement_' + $('#_measurement_price_calculator').val() + '_pricing_calculator_enabled' === $( this ).attr( 'id' ) ) {

				if ( $( this ).closest( 'div.measurement_fields' ).find( '._measurement_pricing' ).is( ':checked' ) && $(this).is(':checked') ) {
					addPricingPerUnitLabel($(this).closest('div').find('._measurement_pricing_unit option:selected').text());                 // update the product label like 'Price ($)' to 'Price ($/sq ft)'
					$(this).closest('div.measurement_fields').find('._measurement_editable').attr('checked','checked').attr('disabled','disabled'); // force the 'editable' field to be enabled, for consistency since this product is now customizable by the customer

					$(this).closest('div.measurement_fields').find('._measurement_pricing_calculator_fields').each(function(index, el) {

						var price_calculator_field = $(el);
						if ( price_calculator_field.hasClass( 'stock_fields' ) ) {
							if ( $( 'input#_manage_stock' ).is( ':checked' ) ) {
								price_calculator_field.show();
							}
						} else {
							price_calculator_field.show();
						}
					});

					// enable the pricing table
					$( '.wc-measurement-price-calculator-pricing-table' ).removeClass( 'disabled' );
					handlePricingRules( true );

					$( '.show_if_pricing_calculator' ).show();
				} else {
					// back to quantity calculator, unwind the above
					removePricingPerUnitLabel();
					if ($(this).closest('div.measurement_fields').find('._measurement_editable').is(':disabled')) { $(this).closest('div.measurement_fields').find('._measurement_editable').removeAttr('disabled'); }
					$(this).closest('div.measurement_fields').find('._measurement_pricing_calculator_fields').hide();
					// disable the pricing table
					$( '.wc-measurement-price-calculator-pricing-table' ).addClass( 'disabled' );
					handlePricingRules( false );

					$( '.show_if_pricing_calculator' ).hide();
				}

				// let the dependent pricing inventory and weight fields know there was a change
				var $parent = $( this ).closest( 'div' );
				$parent.find( '._measurement_pricing_inventory_enabled' ).trigger( 'change' );
				$parent.find( '._measurement_pricing_weight_enabled' ).trigger( 'change' );
				$parent.closest( '.measurement_fields' ).find( '._measurement_accepted_input' ).trigger( 'change' );
			}
		}
	);

	// Accepted input dropdown change
	$( '#measurement_product_data' ).on( 'change', '._measurement_accepted_input', function() {
		var $this                 = $( this ),
		    all_data_attributes   = $this.data(),
		    target_el_selector    = all_data_attributes[$this.val()],
		    $parent_div           = $( this ).closest( 'div' ),
		    is_pricing_calculator = $( '#_measurement_' + $( '#_measurement_price_calculator' ).val() + '_pricing_calculator_enabled' ).is( ':checked' );

		var all_selectors = [];
		for ( var data_key in all_data_attributes ) {
			if ( all_data_attributes.hasOwnProperty( data_key ) ) {
				all_selectors.push( all_data_attributes[data_key] );
			}
		}

		// hide all fields, then show only target one
		var $fields = $parent_div.find( all_selectors.join( ',' ) ).hide();
		if ( is_pricing_calculator ) {
			$fields.filter( target_el_selector ).show();
		}
	} )
	// when user clicks measurement input attributes label
	.on( 'click', '._measurement_input_attributes label', function() {
		// jump focus to the first attribute input
		$( this ).closest( 'p' ).find( '.wrap input:first-child' ).trigger( 'focus' );
	} );

	setTimeout( function() {
		// open measurements tab by default
		var hash = window.location.hash;
		if ( null !== hash.match( /\#[a-z0-9\-]+/ ) ) {
			try {
				// trigger click to open tab
				$( 'a[href="' + hash + '"]' ).trigger( 'click' );
			} catch ( ex ) {
				// avoid invalid exception selector errors
			}
		}
	}, 250 );

	// when toggling the measurement accepted input, ensure the alternate fieldsets are cleared to avoid incurring in browsers non-focusable errors that prevent submitting the product form
	$( 'select._measurement_accepted_input' ).on( 'change', function() {
		var $container = $( this ).closest( 'p' ),
			$freeform  = $container.next(),
			$limited   = $freeform.next();
		if ( 'free' === $( this ).val() ) {
			$limited.find( 'input' ).val( '' );
		} else {
			$freeform.find( 'input' ).val( '' );
		}
	} );

	// Trigger the  "Set Product Pricing Per Unit" when variations are loaded in WC
	// this ensures the variation price per unit label is set when variations are loaded via AJAX
	$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function() {
		$( '#_measurement_' + $('#_measurement_price_calculator').val() + '_pricing_calculator_enabled' ).change();
		variations_loaded = true;
	} );



	// "Inventory" checkbox handler: this enables the "per unit" inventory
	// management for pricing calculator products.  Adds/removes the 'per unit'
	// "Stock Qty" label for pricing calculator products with inventory management
	// enabled
	$('._measurement_pricing_inventory_enabled').change(
		function() {
			// if the current measurement pricing toggle is associated with the currently selected measurement pricing calculator type (Dimensions, Area, Area (LxW), etc)
			if ('_measurement_' + $('#_measurement_price_calculator').val() + '_pricing_inventory_enabled' === $(this).attr('id')) {
				// controlling element
				var measurementPricingEl = $('#_measurement_' + $('#_measurement_price_calculator').val() + '_pricing_calculator_enabled');

				if (measurementPricingEl.is(':checked') && $(this).is(':checked')) {
					// both the Pricing and Inventory fields must be checked
					addInventoryPerUnitLabel($(this).closest('div').find('._measurement_pricing_unit option:selected').text());
				} else {
					removeInventoryPerUnitLabel();
				}
			}
		}
	);


	// "Weight" checkbox handler: this enables the "per unit" weight
	// calculation for pricing calculator products.  Adds/removes the 'per unit'
	// "Weight" label for pricing calculator products with weight calculation
	// enabled
	$( '._measurement_pricing_weight_enabled' ).change(
		function() {

			// bail if product weight is disabled
			// if the current measurement pricing toggle is associated with the currently selected measurement pricing calculator type (Dimensions, Area, Area (LxW), etc)
			if ( '_measurement_' + $( '#_measurement_price_calculator' ).val() + '_pricing_weight_enabled' === $( this ).attr( 'id' ) ) {
				// controlling element
				var measurementPricingEl = $( '#_measurement_' + $( '#_measurement_price_calculator' ).val() + '_pricing_calculator_enabled' );

				// both the Pricing and Weight fields must be checked
				if ( measurementPricingEl.is( ':checked' ) && $( this ).is( ':checked' ) ) {

					// there's a special case for the weight calculator:  doesn't make sense to have the customer define the weight per unit of weight, so just hide the shipping weight fields
					if ( 'weight' === $( '#_measurement_price_calculator' ).val() ) {
						hideShippingWeightField();
					} else {
						// all other calculators: make sure the weight field is visible, and add the pricing unit
						showShippingWeightField();
						addWeightPerUnitLabel( $( this ).closest( 'div' ).find( '._measurement_pricing_unit option:selected' ).text() );
					}
				} else {

					// calculated weight option disabled:  make sure the weight field is visible, and remove the pricing unit
					showShippingWeightField();
					removeWeightPerUnitLabel();
				}
			}
		}
	);


	// when a variation is added, update the 'per unit' label on the variation
	//  price/quantity/weight field labels as needed
	$('.woocommerce_variations').bind('woocommerce_variations_added', function() {
		$('._measurement_pricing').change();
	} );


	// if the measurement pricing unit changes, update the price/sales/quantity/
	// weight labels as needed
	$('._measurement_pricing_unit').change( function() {
		$('._measurement_pricing').change();
	} );


	$('#_measurement_price_calculator').change(
		function() {
			var calculator = $(this).val();
			hide_measurement_fields();
			// disable the pricing calculator pricing table nav link
			$( '.wc-measurement-price-calculator-pricing-table' ).addClass( 'disabled' );

			// fire the pricing change event so the product price labels get updated
			$('._measurement_pricing').change();

			if (calculator) {
				// show calculator measurements and trigger accepted input change event
				$( '#' + calculator + '_measurements' ).show().find( '._measurement_accepted_input' ).trigger( 'change' );
				$( '#' + calculator + '_description' ).show();
			} else {
				// no calculator selected, so remove any unit pricing labels that may be displayed
				removePricingPerUnitLabel();
				$( '.show_if_pricing_calculator' ).hide();
			}

			// show the label/unit/editable fields for any selected dimension type (or hide for unselected dimension types)
			if ('dimension' === calculator) {
				$('._measurement_dimension').change();
			}
		}
	);


	// the "dimensions" measurement is handled specially by allowing any of the three (length/width/height) to be selected
	$('._measurement_dimension').change(
		function() {
			$('._measurement_dimension').each(function(index,el) {
				var id = $(el).attr('id');
				if ($('#'+id).is(':checked')) { $('#'+id+'_fields').show(); }
				else { $('#'+id+'_fields').hide(); }
			});
		}
	);


	/**
	 * Hide all measurement description and fields
	 */
	function hide_measurement_fields() {
		$('.measurement_description').hide();
		$('.measurement_fields').hide();
	}

	$( '.measurement-subnav a' ).click( function() {
		var el = $( this );

		// do nothing if the link is disabled
		if ( $( this ).hasClass( 'disabled' ) ) { return false; }

		$( '.measurement-subnav a' ).removeClass( 'active' );
		el.addClass( 'active' );
		$( '.calculator-subpanel' ).hide();
		$( el.attr( 'href' ) ).show();

		return false;
	} ).eq( 0 ).click();


	/**
	 * Show/hide the simple/variable price/sale fields depending on whether
	 * there are any pricing rules configured
	 */
	function handlePricingRules( pricingCalculatorEnabled ) {

		if ( pricingCalculatorEnabled && $( '.fq-calculator-pricing-table tbody tr' ).length > 0 ) {
			hidePriceFields();
		} else {
			showPriceFields();
		}
	}


	/**
	 * Hides the simple/variation price/sale fields, saving the current value if
	 * any to a data object
	 */
	function hidePriceFields() {
		// decided to hide just the simple price/sale fields rather than the entire pricing
		//  block in case other plugins are adding into there
		if ( '' !== $( '#_regular_price' ).val() ) {
			$( '#_regular_price' ).data( 'orig-value', $( '#_regular_price' ).val() ).val( '' );
		}
		$( '#_regular_price' ).closest( '.form-field' ).hide();

		if ( '' !== $( '#_sale_price' ).val() ) {
			$( '#_sale_price' ).data( 'orig-value', $( '#_sale_price' ).val() ).val( '' );
		}
		$( '#_sale_price' ).closest( '.form-field' ).hide();

		// Variable product
		$( '.woocommerce_variable_attributes input[type="text"], .woocommerce_variable_attributes input[type="number"]' ).each(
			function( index, el ) {
				el = $( el );
				if ( el.attr('name') && ( 'variable_price' === el.attr( 'name' ).substr( 0, 14 ) || 'variable_regular_price' === el.attr( 'name' ).substr( 0, 22 ) || 'variable_sale_price' === el.attr( 'name' ).substr( 0, 19 ) ) ) {

					el.prop( 'readonly', true );

					// only add the message if it doesn't exist for this field
					if ( ! $('.pricing-rules-enabled-notice.' + el.attr( 'id' ) ).length ) {
					}

					if ( '' !== el.val() ) {
						el.data( 'orig-value', el.val() );
					}
				}
			}
		);
	}


	/**
	 * Shows the simple/variation price/sale fields, retrieving the original value
	 * (if any) from the data object.
	 */
	function showPriceFields() {

		if ( undefined !== $( '#_regular_price' ).data( 'orig-value' ) && '' !== $( '#_regular_price' ).data( 'orig-value' ) ) {
			$( '#_regular_price' ).val( $( '#_regular_price' ).data( 'orig-value' ) );
			$( '#_regular_price' ).data( 'orig-value', '' );
		}
		$( '#_regular_price' ).closest( '.form-field' ).show();

		if ( undefined !== $( '#_sale_price' ).data( 'orig-value' ) && '' !== $( '#_sale_price' ).data( 'orig-value' ) ) {
			$( '#_sale_price' ).val( $( '#_sale_price' ).data( 'orig-value') );
			$( '#_sale_price' ).data( 'orig-value', '' );
		}
		$( '#_sale_price' ).closest( '.form-field' ).show();

		// Variable product
		$( '.woocommerce_variable_attributes input[type="text"], .woocommerce_variable_attributes input[type="number"]' ).each(
			function( index, el ) {
				el = $( el );
				if ( el.attr('name') && ( 'variable_price' === el.attr( 'name' ).substr( 0, 14 ) || 'variable_regular_price' === el.attr( 'name' ).substr( 0, 22 ) || 'variable_sale_price' === el.attr( 'name' ).substr( 0, 19 ) ) ) {

					el.prop( 'readonly', false );
					$( '.pricing-rules-enabled-notice' ).remove();

					if ( undefined !== el.data( 'orig-value' ) && '' !== el.data( 'orig-value' ) ) {
						el.val( el.data( 'orig-value' ) );
						el.data( 'orig-value', '' );
					}
				}
			}
		);
	}


	// action to add a pricing table rate
	$( 'button.fq-calculator-pricing-table-add-rule' ).click( function() {

		var index = $( 'table.fq-calculator-pricing-table tbody tr' ).length;

		$( 'table.fq-calculator-pricing-table > tbody' ).append(
			'<tr class="fq-calculator-pricing-rule"><td class="check-column"><input type="checkbox" name="select" /></td>\
			<td class="fq-calculator-pricing-rule-range"><input type="text" name="_wc_measurement_pricing_rule_range_start[' + index + ']" value="" /> - <input type="text" name="_wc_measurement_pricing_rule_range_end[' + index + ']" value="" /></td>\
			<td><input type="text" name="_wc_measurement_pricing_rule_regular_price[' + index + ']" value="" /></td>\
			<td><input type="text" name="_wc_measurement_pricing_rule_sale_price[' + index + ']" value="" /></td></tr>'
		);

		handlePricingRules( true );
		pricingRulesRowIndexes();
	} );


	// delete selected pricing rules
	$( 'button.fq-calculator-pricing-table-delete-rules' ).click( function() {
		$( 'table.fq-calculator-pricing-table td.check-column input:checked' ).each( function() {
			$( this ).closest( 'tr.fq-calculator-pricing-rule' ).fadeOut( '400', function() {
				$( this ).remove();

				$.queue( this, 'fx', function() { handlePricingRules( true ); } );
			} );
		} );

		// make sure the "check all" checkbox is unchecked
		$( '.fq-calculator-pricing-table thead .check-column input' ).removeAttr( 'checked' );

		handlePricingRules( true );
		pricingRulesRowIndexes();
	} );


	// pricing rules ordering
	$( 'table.fq-calculator-pricing-table tbody' ).sortable( {
		items  : 'tr',
		cursor : 'move',
		axis   : 'y',
		handle : 'td',
		scrollSensitivity : 40,
		start : function( event, ui ){
			ui.item.css( 'background-color','#f6f6f6' );
		},
		stop : function( event, ui ) {
			ui.item.removeAttr( 'style' );
			pricingRulesRowIndexes();
		}
	} );


	/**
	 * Re-index pricing rules keys
	 */
	function pricingRulesRowIndexes() {
		var loop = 0;
		$( 'table.fq-calculator-pricing-table tbody tr' ).each( function( index, row ) {
			$( 'input', row ).each( function( i, el ) {

				var t = jQuery( el );
				t.attr( 'name', t.attr( 'name' ).replace(/\[([^[]*)\]/, '[' + loop + ']' ) );

			} );
			loop++;
		} );
	}

	function change_field( $field ) { 
		let option_type = $field.val();
		let $field_type = $field.closest('.fq-field-type');
		$field_type.find('.decimals-fields-with-labels').addClass('hidden');
		$field_type.find('.'+option_type+'-fields').removeClass('hidden');
	}

	function init_fq_fields() { 
		$(document).find('.fq_field_type_selector').each(function () {
			change_field($(this));
		});	
	}

	function change_visiblity( $field, $table ) { 
		if ($field.is(':checked')) {
			$table.show();
		} else {
			$table.hide();
		}
	}

	function update_dimension_fields(unit, $container) { 
		$container.html(fq_admin_params.loader_img);
		$.ajax({
			type: "POST",
			cache: false,
			url: ajaxurl,
			data: {
				'action': fq_admin_params.get_dimensions,
				'product_id': fq_admin_params.product_id,
				'unit': unit,
				'nonce' : fq_admin_params.nonce
			},
			success: function (data) {
				if( data.result === true ){
					$container.html(data.content);
					init_fq_fields();
					$container.find( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( {
						'attribute': 'data-tip',
						'fadeIn': 50,
						'fadeOut': 50,
						'delay': 200,
						'keepAlive': true
					});
					//update_inputs_filters();
				} else {
					alert( data.content );
				}
			},
			error: function(){
				alert( fq_admin_params.server_error );
			}
		});
	}

	function update_inputs_filters() { 
		$(document).find('.fq_max_range, .fq_min_range, .fq_dec_user_length_increment, .fq_dec_user_length_max_quantity, .fq_dec_user_length_min_quantity').inputFilter(function(value) {
			console.log(value);
			let has_special_char = value.indexOf(',') === -1 || value.indexOf('.') === -1 || value.indexOf('e') === -1;
			return /^\d*$/.test(value) && has_special_char && value !== '';
		},fq_admin_params.input_filter);
	}

	function is_pricing_calculator_enabled(){ 
		return $('#fq_decimals_enabled').is(':checked');
	}

	function update_pricing_calculator_fields() {
		let $container = $('#fq-decimals-panel');
		let $label = $('#fq_label');
		if( is_pricing_calculator_enabled() ){
			$container.show();
			$label.attr("disabled", "disabled");
			update_dimension_fields($('#fq_unit').val(), $container);
		} else {
			$container.hide();
			$label.removeAttr("disabled");
		}
	}

	function update_product_fields() { 
		if (jQuery('#fq_enable').is(':checked') && jQuery('#product-type').val() !== 'grouped' && jQuery('#product-type').val() !== 'variable') {
			jQuery('.options_group.pricing').hide();
			jQuery('._sold_individually_field').hide();
		} else { 
			jQuery('.options_group.pricing').show();
			jQuery('._sold_individually_field').show();
		}

	}

	// initialize things
	init_fq_fields();
	hide_measurement_fields();
	$('#_measurement_price_calculator').change();
	$('._measurement_dimension').change();



	// make sure the minimum price is cleared if the product type is changed
	$( 'select#product-type' ).on( 'change', function() {
		$('input#_wc_measurement_price_calculator_min_price').val('');
		update_product_fields();
	});

	$( document ).on( 'change', '.fq_field_type_selector', function() {
		change_field( $(this) );
	});

	var $product_data = jQuery('#measurement_product_data');
	var $pricing_table = jQuery('.fcm-pricing-table');
	var $shipping_table = jQuery('.fcm-shipping-table');
	var $panels = jQuery('.fq-hidden-panels');
	var $enableCalculator = jQuery('#fq_enable');

	change_visiblity( $product_data.find('.show_table_pricing'), $pricing_table );
	change_visiblity($product_data.find('.show_table_shipping'), $shipping_table);
	change_visiblity($enableCalculator, $panels);

	//change_dimensions_visiblity($('#fq_unit'));

	$('#fq_unit').on('change', function () {
		update_dimension_fields( $(this).val(), $('#fq-decimals-panel') );
	});	

	$enableCalculator.on('click', function () {
		change_visiblity(jQuery(this), $panels);
		update_product_fields();
	});
	
	$product_data.on('click', '.show_table_pricing', function () {
		change_visiblity( jQuery(this), $pricing_table );
	});

	$product_data.on('click', '.show_table_shipping', function () {
		change_visiblity( jQuery(this), $shipping_table );
	});

	$product_data.on('click', 'td.actions a.insert', function () {
		let tr_object = jQuery(this).closest('tr').clone();
		$(this).closest('tr').after('<tr>' + tr_object.html() + '</tr>');

		return false;
	});

	$product_data.on('click', 'td.actions a.remove', function () {
		jQuery(this).closest('tr').remove();

		return false;
	});
	

	//update_inputs_filters();

	if ($('#fq_unit').length > 0) {
		update_pricing_calculator_fields();		
	}

	$('#fq_decimals_enabled').on('click', function () {
		update_pricing_calculator_fields();
	});


	$('#post').validate({
		errorPlacement: function (error, element) {
			element[0].setCustomValidity(error.html());
			element[0].reportValidity();
        },
	});

	update_product_fields();
});
