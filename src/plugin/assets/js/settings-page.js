jQuery(document).ready(function() {
	jQuery("#settings-tab").submit(function() {
		jQuery(this).ajaxSubmit({
			success: function() {
				jQuery("#saveResult").html("<div id='saveMessage' class='successModal'></div>");
				jQuery("#saveMessage")
					.append(`<p>${settingsPage.saveMessage}</p>`)
					.show();
			},
			timeout: 5000,
		});
		setTimeout("jQuery('#saveMessage').hide('slow');", 5000);
		return false;
	});
});
