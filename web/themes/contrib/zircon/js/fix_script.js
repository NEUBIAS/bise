(jQuery)(function () {
	
	fix_placeholder();
	fix_contact_form();
	
	function fix_placeholder() {
		(jQuery)('[placeholder]').focus(function() {
			var input = (jQuery)(this);
			if (input.val() == input.attr('placeholder')) {
				input.val('');
				input.removeClass('placeholder');
			}
		}).blur(function() {
			var input = (jQuery)(this);
			if (input.val() == '' || input.val() == input.attr('placeholder')) {
				input.addClass('placeholder');
				input.val(input.attr('placeholder'));
			}
		}).blur().parents('form').submit(function() {
			(jQuery)(this).find('[placeholder]').each(function() {
				var input = (jQuery)(this);
				if (input.val() == input.attr('placeholder')) {
				input.val('');
				}
			})
		});	
	}
	
	function fix_contact_form() {
		var userAgent = window.navigator.userAgent;
		if (userAgent.indexOf('Firefox')) {
			var form = (jQuery)('#contact-message-feedback-form');
			var parent = form.parent();
			var formHTML = parent.html();
			parent.empty();
			parent.html(formHTML);
		}	
	}
});