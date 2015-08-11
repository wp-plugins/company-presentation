jQuery(document).ready(function($) {
	prodii_getShortcodeHtml = function(view) {
		$group = $('#prodii_shortcode_form');
		$('#prodii-admin-loading').show();
		
		var data = {};
		data.action = 'prodii_shortcode_content';
		data.prodii_templateid = $group.find('#prodii_templateid').val();
		data.prodii_css = $group.find('#prodii_css').val();
		data.prodii_companyid = $group.find('#prodii_companyid').val();
		teamidsArray = $group.find("#prodii_teamids option:selected").map(function(){return this.value;});
		data.prodii_teamids = data.prodii_companyid == 0 || data.prodii_templateid == '' ? '' : teamidsArray.get().join(',');
		data.prodii_teamid = $group.find('#prodii_teamid').val() > 0 ? $group.find('#prodii_teamid').val() : 0;
		data.prodii_memberid = $group.find('#prodii_memberid').val() > 0 ? $group.find('#prodii_memberid').val() : 0;
		data.prodii_view = view;
		data.prodii_nonce = prodii_vars.prodii_nonce

		console.log(data);
		$.post(ajaxurl, data, function(response) {
			console.log(response);
			$('#prodii_shortcode_form').html(response);
			$('#prodii-admin-loading').hide();
		});
		
		return false;
	}
	
	prodii_toggleCss = function(adv) {
		$group = $('#prodii_shortcode_form');
		
		if (adv == 'adv') {
			$group.find('.prodii-noadv').hide();
			$group.find('.prodii-adv').show();
		} else {
			$group.find('.prodii-noadv').show();
			$group.find('.prodii-adv').hide();
		}
		
		return false;
	}
	
	prodii_selectTab = function($obj, tab) {
		$group = $('#prodii_shortcode_form');
		
		$group.find('.prodii-tab').hide();
		$group.find('.'+tab).show();
		
		$group.find('.nav-tab').removeClass('nav-tab-active');
		$obj.addClass('nav-tab-active');
		
		return false;
	}
});
