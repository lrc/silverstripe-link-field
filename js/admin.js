(function($){
	$(function(){
		
		var custom_url = function () {
			if( $('.link-form-field-page select').val() == "" ) {
				$('.link-form-field-url').show();
			} else {
				$('.link-form-field-url').hide();
			}
		} 
		
		$('.link-form-field-page select').bind( 'change', custom_url );

		custom_url();
		
	});
})(jQuery);