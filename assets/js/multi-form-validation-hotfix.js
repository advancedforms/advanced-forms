(function ($) {

	// Direct copy from ACF 5.11 (unmodified).
	// See advanced-custom-fields-pro/assets/build/js/acf-input.js
	var ensureFieldPostBoxIsVisible = function ($el) {
		// Find the postbox element containing this field.
		var $postbox = $el.parents('.acf-postbox');

		if ($postbox.length) {
			var acf_postbox = acf.getPostbox($postbox);

			if (acf_postbox && acf_postbox.isHiddenByScreenOptions()) {
				// Rather than using .show() here, we don't want the field to appear next reload.
				// So just temporarily show the field group so validation can complete.
				acf_postbox.$el.removeClass('hide-if-js');
				acf_postbox.$el.css('display', '');
			}
		}
	};

	// Copied from ACF 5.11 and modified to use the current form element as context.
	// See advanced-custom-fields-pro/assets/build/js/acf-input.js
	var ensureInvalidFieldVisibility = function ($form) {

		// ACF does this and gets all field inputs on the current page.
		// var $inputs = $('.acf-field input');
		// Let's just get the inputs for the current form instead.
		var $inputs = $form.find('.acf-field input');

		$inputs.each(function () {
			if (!this.checkValidity()) {
				ensureFieldPostBoxIsVisible($(this));
			}
		});
	};

	// Copied from ACF 5.11 and modified to find the current form and pass it as context for validation.
	// This prevents validation running across multiple forms on the one page.
	// See advanced-custom-fields-pro/assets/build/js/acf-input.js
	acf.validation.onClickSubmit = function (e, $el) {
		var $form = $el.closest('form');
		if (!$form.length) {
			return;
		}
		ensureInvalidFieldVisibility($form);
		this.set('originalEvent', e);
	};

})(jQuery);