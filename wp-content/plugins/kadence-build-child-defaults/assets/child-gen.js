/**
 * Custom jQuery for building child.
 */
(function($) {
    $(window).load(function() {

	// localization strings
	function kct_export() {
		var overlay = $( document.getElementById( 'kadence_gen_ajax_spinner' ) );
		overlay.fadeIn();
		
		// Do the ajax call
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: kadence_gen.ajax_url,
			data: {
				action: 'kct_export_child',
				security: kadence_gen.ajax_nonce,
				wp_customize: 'on',
			},
			success: function(response) {
				console.log( response );
				if ( response && response['response'] && response['response'] !== false ) {           
					setTimeout(function(){
						 window.location.href = response['url'] + '?var=cache' + Math.floor(Math.random() * 100);
						 var overlay = $( document.getElementById( 'kadence_gen_ajax_spinner' ) );
	           			overlay.fadeOut();
					}, 1000);
				} else {
					alert('Error while generating child theme');
					var overlay = $( document.getElementById( 'kadence_gen_ajax_spinner' ) );
           			overlay.fadeOut();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				alert('Error while generating child theme');
				console.log(XMLHttpRequest.responseText);
				var overlay = $( document.getElementById( 'kadence_gen_ajax_spinner' ) );
				overlay.fadeOut();
			},
		});
	}
	$( '.option-kadence_build_child_generate_config' ).on( 'click', '.kadence-generate-child-button', function() {
		kct_export();
	});
});
})(jQuery);

