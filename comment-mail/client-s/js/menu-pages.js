(function($)
{
	'use strict'; // Standards.

	var plugin = {}, $window = $(window), $document = $(document);

	plugin.onReady = function() // jQuery DOM ready event handler.
	{
		var $menuPage = $('#comment-mail-menu-page');
		var $menuPageTable = $('#comment-mail-menu-page-table');
		var i18n = window['comment_mail_i18n'];

		$menuPage.find('.pmp-panels-open').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').addClass('open')
				.next('.pmp-panel-body').addClass('open');
		});
		$menuPage.find('.pmp-panels-close').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').removeClass('open')
				.next('.pmp-panel-body').removeClass('open');
		});
		$menuPage.find('.pmp-panel-heading').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$(this).toggleClass('open') // Toggle this panel now.
				.next('.pmp-panel-body').toggleClass('open');
		});
		$menuPage.find('[data-pmp-action]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), data = $this.data();
			if(typeof data.pmpConfirmation !== 'string' || confirm(data.pmpConfirmation))
				location.href = data.action;
		});
		$menuPage.find('select[name$="_enable\\]"], select[name$="_enable_flavor\\]"]').not('.no-if-enabled').on('change', function()
		{
			var $this = $(this), thisName = $this[0].name, thisValue = $this.val(),
				$thisPanel = $this.closest('.pmp-panel');

			if((thisName.indexOf('_enable]') !== -1 && (thisValue === '' || thisValue === '1'))
			   || (thisName.indexOf('_flavor]') !== -1 && thisValue !== '0')) // Enabled?
				$thisPanel.find('.pmp-panel-if-enabled').css('opacity', 1).find(':input').removeAttr('readonly');
			else $thisPanel.find('.pmp-panel-if-enabled').css('opacity', 0.4).find(':input').attr('readonly', 'readonly');
		})
			.trigger('change'); // Initialize.

		$menuPageTable.find('> form').on('submit', function()
		{
			var $this = $(this), // Initialize vars.
				$bulkTop = $this.find('#bulk-action-selector-top'),
				$bulkBottom = $this.find('#bulk-action-selector-bottom'),
				bulkTopVal = $bulkTop.val(), bulkBottomVal = $bulkBottom.val();

			if(bulkTopVal === 'reconfirm' || bulkBottomVal === 'reconfirm')
				return confirm(i18n.bulk_reconfirm_confirmation);

			else if(bulkTopVal === 'delete' || bulkBottomVal === 'delete')
				return confirm(i18n.bulk_delete_confirmation);

			return true; // Default behavior.
		});
	};
	$document.ready(plugin.onReady); // On DOM ready.
})(jQuery);