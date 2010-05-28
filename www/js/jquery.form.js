/**
 * Form helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2009 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	/**
	 * Input title hint
	 *
	 * @author  Antti Qvickström (password patch)
	 * @author  Remy Sharp (original)
	 * @url     http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
	 */
	$.fn.hint = function (blurClass) {
		blurClass = blurClass || 'blur';

	  return this.each(function () {

	    // Get jQuery version of 'this' and capture the rest of the variable to allow for reuse
	    var $input = $(this),
	      title = $input.attr('title'),
	      isPassword = $input.attr('type') == 'password',
	      $form = $(this.form),
	      $win = $(window);

	    // Clear hint
	    function remove() {
	    	if (isPassword) {
	    		$password.remove();
	    		$input.show();
	    	} else {
	      	if ($input.val() === title && $input.hasClass(blurClass)) {
	        	$input.val('').removeClass(blurClass);
	      	}
	    	}
	    }

	    // Only apply logic if the element has the attribute
	    if (title) {

	    	if (isPassword) {

	    		// Add text input to handle title
    			$input.attr('title', null);
    			var $password = $input.clone();
    			var display = $input.css('display');
    			$password.hide()
    				.attr({
   						type: 'text',
   						id: this.id + '-hint',
   						name: $input.attr('name') + '-hint'
    				})
    				.addClass(blurClass)
    				.val(title)
    				.insertAfter($input)
    				.focus(function() {
    					$password.hide();
    					$input.show().focus();
    				});
    			$input.blur(function() {
    				if (this.value === '') {
	    				$input.hide();
	    				$password.css('display', display);
    				}
    			});
    			if ($input.val() === '') {
    				$input.hide();
 	  				$password.css('display', display);
    			}

	    	} else {

		      // On blur, set value to title attr if text is blank
		      $input.blur(function () {
		        if (this.value === '') {
		          $input.addClass(blurClass).val(title);
		        }
		      }).focus(remove).blur();

	    	}

	      // Clear the pre-defined text when form is submitted
	      $form.submit(remove);

	      // Handles Firefox's autocomplete
	      $win.unload(remove);
	    }
	  });
	};
})(jQuery);
