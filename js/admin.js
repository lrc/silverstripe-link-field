(function($){
	$(function(){
		$('#fieldgroupFieldPage').change(custom_url);

		function custom_url() {
			if( $('#fieldgroupFieldPage select').val() == "" ) {
				$('#fieldgroupFieldCustomURL').show();
			} else {
				$('#fieldgroupFieldCustomURL').hide();
			}
		}

		custom_url();
	});
})(jQuery);