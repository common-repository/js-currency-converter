jQuery( document ).ready( function ( $ ) {

	/**
	 * Add a new currency row
	 */
	$( 'button.new_row_button' ).on( 'click', function ( event ) {
		event.preventDefault();
		$( '.new_row' ).clone().appendTo( '.currency_rows' );

		$( '.currency_rows .new_row' )
			.attr( 'style', 'display:block;' )
			.removeClass( 'new_row' );

		order_exchange_rates();
	} );

	/**
	 * Remove a currency row
	 */
	$( '.remove_this_row' ).on( 'click', function ( event ) {
		event.preventDefault();
		$( this ).parents( '.currency_row' ).remove();

		order_exchange_rates();
	} );

	/**
	 * Make sure the exchange rate options are unique
	 */
	order_exchange_rates = function () {
		var i         = 0,
		    base_name = $( '.currency_row' ).attr( 'data-basename' );

		/*
		 * Update the name numbers
		 */
		$( '.currency_row' ).each( function () {
			$( ' .jcc_currency_admin_flag', this ).attr( 'name', base_name + '[' + i + '][flag]' );
			$( ' .jcc_currency_admin_title', this ).attr( 'name', base_name + '[' + i + '][title]' );
			i ++;
		} );
	};
	
	/**
	 * Adding a flag to the option
	 *
	 * @param state
	 * @returns {*|jQuery|HTMLElement}
	 */
	formatState = function ( state ) {
		if ( ! state.id ) {
			return state.text;
		}

		return $( '<span><img src="' + $( state.element ).attr( 'data-image' ) + '" class="js_currency_converter_img-flag" /> ' + state.text + '</span>' );
	};
	/**
	 * Show a flag in the selected item
	 *
	 * @param event
	 */
	formatSelect = function ( event ) {
		var current_item = $( 'option[value="' + event.currentTarget.value + '"]' );
		$( 'span[title="' + current_item.html() + '"]' ).html( '<span><img src="' + current_item.attr( 'data-image' ) + '" class="js_currency_converter_img-flag" /> ' + event.currentTarget.value + '</span>' );
	};


	$( '.jcc_currency_image_menu' )
		.select2( {
			templateResult : formatState
		} )
		.on( 'select2:select', formatSelect )
		.trigger( 'select2:select' );
} );
