jQuery(document).ready(function($) {
    $('#aswp_color_picker_1').ColorPicker({
		color: '#ffffff',
		onChange: function (hsb, hex, rgb) {
			$('#aswp_color_picker_1 div').css('backgroundColor', '#' + hex);
			$('#aswp_border_color').val(hex);
			$('#aswp_ad_live_preview').css('borderColor', '#' + hex);
			$('#aswp_tla_lp_google').css('backgroundColor', '#' + hex);
			$('#aswp_tla_lp_google').css('color', isDark($('#aswp_color_picker_1 div').css("background-color")) ? 'white' : 'black');
		}
	});
	$('#aswp_color_picker_2').ColorPicker({
		color: '#ffffff',
		onChange: function (hsb, hex, rgb) {
			$('#aswp_color_picker_2 div').css('backgroundColor', '#' + hex);
			$('#aswp_background_color').val(hex);
			$('#aswp_ad_live_preview, #aswp_tla_live_preview').css('backgroundColor', '#' + hex);
		}
	});
	$('#aswp_color_picker_3').ColorPicker({
		color: '#0000ff',
		onChange: function (hsb, hex, rgb) {
			$('#aswp_color_picker_3 div').css('backgroundColor', '#' + hex);
			$('#aswp_title_color').val(hex);
			$('#aswp_ad_lp_title, #aswp_tla_lp_title').css('color', '#' + hex);
		}
	});
	$('#aswp_color_picker_4').ColorPicker({
		color: '#000000',
		onChange: function (hsb, hex, rgb) {
			$('#aswp_color_picker_4 div').css('backgroundColor', '#' + hex);
			$('#aswp_text_color').val(hex);
			$('#aswp_ad_lp_text').css('color', '#' + hex);
		}
	});
	$('#aswp_color_picker_5').ColorPicker({
		color: '#008000',
		onChange: function (hsb, hex, rgb) {
			$('#aswp_color_picker_5 div').css('backgroundColor', '#' + hex);
			$('#aswp_url_color').val(hex);
			$('#aswp_ad_lp_url').css('color', '#' + hex);
		}
	});

	$('#aswp_border_color').keyup(function(){
		$('#aswp_color_picker_1 div').css('backgroundColor', '#'+$(this).val());
		$('#aswp_ad_live_preview').css('borderColor', '#'+$(this).val());
		$('#aswp_color_picker_1').ColorPickerSetColor($(this).val());
		$('#aswp_tla_lp_google').css('backgroundColor', '#'+$(this).val());
	});
	$('#aswp_background_color').keyup(function(){
		$('#aswp_color_picker_2 div').css('backgroundColor', '#'+$(this).val());
		$('#aswp_ad_live_preview, #aswp_tla_live_preview').css('backgroundColor', '#'+$(this).val());
		$('#aswp_color_picker_2').ColorPickerSetColor($(this).val());
	});
	$('#aswp_title_color').keyup(function(){
		$('#aswp_color_picker_3 div').css('backgroundColor', '#'+$(this).val());
		$('#aswp_ad_lp_title, #aswp_tla_lp_title').css('color', '#'+$(this).val());
		$('#aswp_color_picker_3').ColorPickerSetColor($(this).val());
	});
	$('#aswp_text_color').keyup(function(){
		$('#aswp_color_picker_4 div').css('backgroundColor', '#'+$(this).val());
		$('#aswp_ad_lp_text').css('color', '#'+$(this).val());
		$('#aswp_color_picker_4').ColorPickerSetColor($(this).val());
	});
	$('#aswp_url_color').keyup(function(){
		$('#aswp_color_picker_5 div').css('backgroundColor', '#'+$(this).val());
		$('#aswp_ad_lp_url').css('color', '#'+$(this).val());
		$('#aswp_color_picker_5').ColorPickerSetColor($(this).val());
	});

	$('.handlediv').click(function(){
		$('#inside_'+$(this).attr('id')).toggle();
	});

	$('#aswp_ad_corner_style').change(function(){
		if ($(this).val() == '1') {
			$('#aswp_ad_live_preview').css('border-radius', '0');
		} else if ($(this).val() == '2') {
			$('#aswp_ad_live_preview').css('border-radius', '6px');
		} else if ($(this).val() == '3') {
			$('#aswp_ad_live_preview').css('border-radius', '10px');
		}
	});

	$('#aswp_ad_font_size').change(function(){
		if ($(this).val() == 'Small') {
			$('#aswp_ad_lp_title').css('fontSize', '13px');
			$('#aswp_ad_lp_text').css('fontSize', '12px');
		} else if ($(this).val() == 'Medium') {
			$('#aswp_ad_lp_title').css('fontSize', '14px');
			$('#aswp_ad_lp_text').css('fontSize', '14px');
		} else if ($(this).val() == 'Large') {
			$('#aswp_ad_lp_title').css('fontSize', '15px');
			$('#aswp_ad_lp_text').css('fontSize', '15px');
		} else {
			$('#aswp_ad_lp_title').css('fontSize', '14px');
			$('#aswp_ad_lp_text').css('fontSize', '14px');
		}
	});

	$('#aswp_ad_font_family').change(function(){
		if ($(this).val() == 'Arial') {
			$('#aswp_ad_lp_title').css('fontFamily', 'Arial,sans-serif');
			$('#aswp_ad_lp_text').css('fontFamily', 'Arial,sans-serif');
			$('#aswp_ad_lp_url').css('fontFamily', 'Arial,sans-serif');
		} else if ($(this).val() == 'Verdana') {
			$('#aswp_ad_lp_title').css('fontFamily', 'Verdana,Arial,sans-serif');
			$('#aswp_ad_lp_text').css('fontFamily', 'Verdana,Arial,sans-serif');
			$('#aswp_ad_lp_url').css('fontFamily', 'Verdana,Arial,sans-serif');
		} else if ($(this).val() == 'Times') {
			$('#aswp_ad_lp_title').css('fontFamily', 'Times,Arial,sans-serif');
			$('#aswp_ad_lp_text').css('fontFamily', 'Times,Arial,sans-serif');
			$('#aswp_ad_lp_url').css('fontFamily', 'Times,Arial,sans-serif');
		} else {
			$('#aswp_ad_lp_title').css('fontFamily', 'Verdana,Arial,sans-serif');
			$('#aswp_ad_lp_text').css('fontFamily', 'Verdana,Arial,sans-serif');
			$('#aswp_ad_lp_url').css('fontFamily', 'Verdana,Arial,sans-serif');
		}
	});

	$('#aswp_restore_default').click(function(){
		$('#aswp_color_picker_1 div').css('backgroundColor', '#ffffff');
		$('#aswp_border_color').val('ffffff');
		$('#aswp_ad_live_preview').css('borderColor', '#ffffff');
		$('#aswp_color_picker_1').ColorPickerSetColor('ffffff');
		$('#aswp_tla_lp_google').css('backgroundColor', '#ffffff');

		$('#aswp_color_picker_2 div').css('backgroundColor', '#ffffff');
		$('#aswp_background_color').val('ffffff');
		$('#aswp_ad_live_preview, #aswp_tla_live_preview').css('backgroundColor', '#ffffff');
		$('#aswp_color_picker_2').ColorPickerSetColor('ffffff');

		$('#aswp_color_picker_3 div').css('backgroundColor', '#0000ff');
		$('#aswp_title_color').val('0000ff');
		$('#aswp_ad_lp_title, #aswp_tla_lp_title').css('color', '#0000ff');
		$('#aswp_color_picker_3').ColorPickerSetColor('0000ff');

		$('#aswp_color_picker_4 div').css('backgroundColor', '#000000');
		$('#aswp_text_color').val('000000');
		$('#aswp_ad_lp_text').css('color', '#000000');
		$('#aswp_color_picker_4').ColorPickerSetColor('000000');

		$('#aswp_color_picker_5 div').css('backgroundColor', '#008000');
		$('#aswp_url_color').val('008000');
		$('#aswp_ad_lp_url').css('color', '#008000');
		$('#aswp_color_picker_5').ColorPickerSetColor('008000');

		return false;
	});

	$('#aswp_ad_type').change(function(){
		if ($(this).val() == '1' || $(this).val() == '3') {
			$('#aswp_format_2_span').show();
			$('#aswp_format_3_span').show();
			$('#aswp_format_5_span').show();
			$('#aswp_format_7_span').show();
			$('#aswp_format_13_span').show();
			$('#aswp_format_14_span').show();
			$('#aswp_format_15_span').show();
			$('#aswp_format_16_span').show();
			$('#aswp_format_17_span').show();
			$('#aswp_format_18_span').show();
		} else if ($(this).val() == '2') {
			$('#aswp_format_2_span').hide();
			$('#aswp_format_3_span').hide();
			$('#aswp_format_5_span').hide();
			$('#aswp_format_7_span').hide();
			$('#aswp_format_13_span').hide();
			$('#aswp_format_14_span').hide();
			$('#aswp_format_15_span').hide();
			$('#aswp_format_16_span').hide();
			$('#aswp_format_17_span').hide();
			$('#aswp_format_18_span').hide();
		}
	});

	function isDark(color) {
	    var match = /rgb\((\d+).*?(\d+).*?(\d+)\)/.exec(color);
	    return parseFloat(match[1])
	         + parseFloat(match[2])
	         + parseFloat(match[3])
	           < 3 * 256 / 2; // r+g+b should be less than half of max (3 * 256)
	}
});