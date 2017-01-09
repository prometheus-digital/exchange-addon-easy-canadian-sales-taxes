//var ECASTaxManager = ECASTaxManager || {};

jQuery(document).ready(function($) {    
	//var tax_manager = new ECASTaxManager.ListCertsView();
		
	$( '.it-exchange-easy-canadian-sales-taxes-addon-settings' ).on( 'click', "#new-tax-rate", function( event ) {
		event.preventDefault();
		var data = {
			'action': 'it-exchange-easy-canadian-sales-taxes-addon-add-new-rate',
			'count':  it_exchange_easy_canadian_sales_taxes_addon_iteration,
		}
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '#canadian-tax-rate-table' ).append( response );
		});
		it_exchange_easy_canadian_sales_taxes_addon_iteration++;
	});
	
	$( '.it-exchange-easy-canadian-sales-taxes-addon-settings' ).on( 'click', '.it-exchange-easy-canadian-sales-taxes-addon-delete-tax-rate', function( event ) {
		event.preventDefault();
		$( this ).closest( '.item-row' ).remove();
	});

});