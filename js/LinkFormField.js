(function($){
	
	function showHideCustomURL() {
//		console.log($('.LinkFormFieldPageID input',this).val(),!(parseInt($('.LinkFormFieldPageID input',this).val())))
		$('.LinkFormFieldCustomURL',this).css('display', !( parseInt($('.LinkFormFieldPageID input',this).val()) ) ? 'block' : 'none');
	}
	
	$('.LinkFormField').entwine({
		onadd: function(){
			var $this = this;
			
			// Add listener
			$this.find('.LinkFormFieldPageID input').on('change', function(){
				showHideCustomURL.call($this);
			});
			
			// Initial setup
			showHideCustomURL.call($this);
			
			// Fix bug in SS
			var $tree = $this.find('.TreeDropdownField');
			$tree.data('urlTree', $tree.data('urlTree').replace('[PageID]',''));
		}
	});
	
	
})(jQuery);