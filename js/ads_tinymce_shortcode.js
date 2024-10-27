// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.aswp_ads_shortcode', {
		createControl : function(id, controlManager) {
			if (id == 'aswp_ads_shortcode_button') {
				// creates the button
				var button = controlManager.createButton('aswp_ads_shortcode_button', {
					title : 'Ads Shortcodes',
					image : '../wp-content/plugins/adsense-wordpress-plugin/images/icon_editor.png',
					onclick : function() {
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
						W = W - 80;
						H = H - 284;
						tb_show('Ads Shortcodes', '#TB_inline?height=300&width=500&inlineId=aswp_ads_shortcode_id');
					}
				});
				return button;
			}
			return null;
		}
	});

	// registers the plugin.
	tinymce.PluginManager.add('aswp_ads_shortcode', tinymce.plugins.aswp_ads_shortcode);

	jQuery(function(){
		jQuery('#content_aswp_ads_shortcode_button').live('click', function(){
			var h = jQuery(window).height();

			jQuery('#TB_window').addClass('aswp_tb_window');
			jQuery('.aswp_tb_window').css('width', 400+'px');
			jQuery('.aswp_tb_window').css('height', 200+'px');
			jQuery('#TB_window').css({marginLeft: '-200px'});
			jQuery('#TB_window').css({top: parseInt(((h/2 - 100)), 10)+'px'});

			jQuery('#TB_ajaxContent').addClass('aswp_tb_ajax_content');
			jQuery('.aswp_tb_ajax_content').css('width', 370+'px');
			jQuery('.aswp_tb_ajax_content').css('height', 156+'px');
		});

		aswp_tb_position = function() {
			if (jQuery('#TB_window').hasClass('aswp_tb_window')) {
				var h = jQuery(window).height();

				jQuery('.aswp_tb_window').css('width', 400+'px');
				jQuery('.aswp_tb_window').css('height', 200+'px');
				jQuery('#TB_window').css({marginLeft: '-200px'});
				jQuery('#TB_window').css({top: parseInt(((h/2 - 100)), 10)+'px'});
			}
			else {
				tb_position();
			}
        };

        jQuery(window).resize( function() { aswp_tb_position() } );
        jQuery(document).ready( function() { aswp_tb_position() } );

		var url_data = 'mode=load_shortcodes&action=aswp_ajax_call';
		var html_container = '';

		jQuery.ajax({
			url: ajaxurl,
			type: "POST",
			data: url_data,
			cache: false,
			dataType: "html",
			success: function (respond) {
				html_container = jQuery('<div id="aswp_ads_shortcode_id">'+respond+'</div>');
				html_container.appendTo('body').hide();
			}
		});

		jQuery('.aswp_more_details').live('click', function(){
			var id = jQuery(this).attr('id').slice(16);

			if (jQuery('#aswp_details_cont_'+id).is(':visible')) {
				jQuery('#aswp_details_cont_'+id).slideUp();
			} else {
				jQuery('#aswp_details_cont_'+id).slideDown();
			}

			return false;
		});

		jQuery('.aswp_insert_ad').live('click', function(){
			var id = jQuery(this).attr('id').slice(15);
			var shortcode = '[aswp id="'+id+'"]';

			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);

			// closes Thickbox
			tb_remove();

			return false;
		});
	});
})()