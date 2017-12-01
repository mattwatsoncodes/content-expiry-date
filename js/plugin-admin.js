jQuery( document ).ready( function( $ ) {

	var changed = 0;

	Date.prototype.getMonthName = function(lang) {
	    lang = lang && (lang in Date.locale) ? lang : 'en';
	    return Date.locale[lang].month_names[this.getMonth()];
	};

	Date.prototype.getMonthNameShort = function(lang) {
	    lang = lang && (lang in Date.locale) ? lang : 'en';
	    return Date.locale[lang].month_names_short[this.getMonth()];
	};

	Date.locale = {
	    en: {
	       month_names: [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
	       month_names_short: [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ]
	    }
	};

	// Enable the expiry date edit fields.
	$( '.submitbox .edit-expires' ).click( function(){
		$(this).hide();
		$( '.submitbox #expiresdiv' ).slideDown();
		$( '.submitbox #expiresdiv input' ).removeAttr( 'disabled' );
		$( '.submitbox #expiresdiv select' ).removeAttr( 'disabled' );
	} );

	// Cancel the expiry date change
	$( '.submitbox .cancel-expiry' ).click( function( e ){
		e.preventDefault();
		$( '.submitbox #expiresdiv' ).slideUp();
		$( '.submitbox .edit-expires' ).show();
		if ( 0 === changed ) {
			if ( '' === $( '.submitbox #expiry-previous-value' ).val() ) {
				$( '.submitbox #expiresdiv input' ).attr( 'disabled', 'true' );
				$( '.submitbox #expiresdiv select' ).attr( 'disabled', 'true' );
			}
		}
		return false;
	} );

	// Save the expiry date change
	$( '.submitbox .save-expiry' ).click( function( e ){
		e.preventDefault();
		$( '.submitbox #expiresdiv' ).slideUp();
		$( '.submitbox .edit-expires' ).show();
		$( '.submitbox #expiry-remove' ).val( 'false' );
		change_label();
		changed++;
		return false;
	} );

	// Remove the expiry date
	$( '.submitbox .remove-expiry' ).click( function( e ){
		e.preventDefault();
		$( '.submitbox #expiresdiv' ).slideUp();
		$( '.submitbox .edit-expires' ).show();
		$( '.submitbox #expiry-remove' ).val( 'true' );
		change_label();
		return false;
	} );

	// Change the label to reflect the last action
	function change_label() {
		var year         = $( '.submitbox #expiresdiv #aa').val();
		var month        = $( '.submitbox #expiresdiv #mm').val();
		var day          = $( '.submitbox #expiresdiv #jj').val();
		var hour         = $( '.submitbox #expiresdiv #hh').val();
		var minute       = $( '.submitbox #expiresdiv #mn').val();
		var save_date    = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':00';

		if ( 'false' === $( '.submitbox #expiry-remove' ).val() ) {
			var date         = new Date( save_date );
			var display_date = date.getMonthNameShort() + ' ' + date.getDate() + ', ' + date.getFullYear() + ' @ ' + date.getHours() + ':' + ( '0' + ( date.getMinutes() ) ).slice( -2 );
			$( '.submitbox .misc-pub-curtime #expires #expires-default').hide();
			$( '.submitbox .misc-pub-curtime #expires #expires-never').hide();
			$( '.submitbox .misc-pub-curtime #expires #expires-scheduled b').html( display_date );
			$( '.submitbox .misc-pub-curtime #expires #expires-scheduled').show();
		} else {
			$( '.submitbox .misc-pub-curtime #expires #expires-default').hide();
			$( '.submitbox .misc-pub-curtime #expires #expires-scheduled').hide();
			$( '.submitbox .misc-pub-curtime #expires #expires-never').show();
		}

	}
});
