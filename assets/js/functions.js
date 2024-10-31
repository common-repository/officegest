jQuery( function( $ ) {
	if ( $( 'form.checkout' ).length === 0 ) {
		return;
	}
	if ( $( "#billing_pp" ).length ) {
		$("#billing_pp").css('width','100%!important').select2({
		    theme: "classic",
			dropdownAutoWidth : true
		}).trigger('select:select');
	}
	var current_nif = '';
	//Address changed?
	var checkout_form = $( 'form.checkout' );
	checkout_form.on( 'change', '#billing_country', function() {
		var country = $( '#billing_country' ).val();
		if ( country == 'PT' ) {
			if ( $( '#billing_nif_field' ).is( ':hidden' ) ) {
				$( '#billing_nif_field' ).show();
				if ( current_nif != '' ) {
					$( '#billing_nif' ).val( current_nif );
				}
				current_nif = '';
			}
		} else {
			if ( $( '#billing_nif_field' ).is( ':visible' ) ) {
				current_nif = $( '#billing_nif' ).val();
				$( '#billing_nif' ).val( '' );
				$( '#billing_nif_field' ).hide();
			}
		}
	} );

});