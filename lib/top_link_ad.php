<?php
/**
 *	Contains functions for the creation of new ad.
 *
 */

class aswp_top_link_ad
{
	/**
	 * Display the main page for the create ad page.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function display_main()
	{
		$check_registration_status = aswp_core::check_registration();

		if ($check_registration_status === FALSE || $check_registration_status === 'confirm')
		{
			if (aswp_core::registration_form('aswp_top_link_ad') !== TRUE)
			{
				return FALSE;
			}
		}

		$save_ad_result = TRUE;

		//	Check if form was submitted
		if (isset($_POST['aswp_top_link_ad_save']) == TRUE && $_POST['aswp_top_link_ad_save'] == 'yes')
		{
			$save_ad_result = $this->save_ad($_POST);
		}

		echo aswp_html::wrap_header();

		//	Load options
		$options = get_option('aswp_options');

		//	Get just the top link ad options
		$tla_options = isset($options['top_link_ad']) == TRUE ? $options['top_link_ad'] : FALSE;

		//	If top link ad not found in options, set the default settings
		if ($tla_options === FALSE)
		{
			$tla_options = array(
				'aswp_tla_enable' => '',
				'aswp_show_tla_on_home' => '1',
				'aswp_show_tla_on_category' => '1',
				'aswp_show_tla_on_archive' => '1',
				'aswp_tla_format_1' => '',
				'aswp_tla_format_2' => '',
				'aswp_border_color' => 'ffffff',
				'aswp_background_color' => 'ffffff',
				'aswp_title_color' => '0000ff',
				'aswp_adv_publisher_id' => '',
				'aswp_adv_custom_channel' => '',
				'aswp_donation' => isset($options['reward_author']) == TRUE ? $options['reward_author'] : '0',
			);
		}

?>
	<h2><?php _e('Top Link Ad', ASWP_UNIQUE_NAME); ?></h2>
<?php
		if ($save_ad_result !== TRUE)
		{
			echo aswp_html::admin_notice($save_ad_result['message'][0]['type'], $save_ad_result['message'][0]['message']);
		}
		else if (is_array($save_ad_result) == TRUE)
		{
			echo aswp_html::admin_notice('updated', __('Top Link Ad successfully saved.', ASWP_UNIQUE_NAME));
		}

		//	Check if user has entered adsense pub id
		if (isset($options['options']['aswp_publisher_id']) == FALSE || $options['options']['aswp_publisher_id'] === '')
		{
			echo aswp_html::admin_notice('error', __('Please enter your AdSense Publisher ID on the', ASWP_UNIQUE_NAME).' <a href="'.admin_url().'admin.php?page=aswp_options">'.__('options page', ASWP_UNIQUE_NAME).'</a>');
		}

		echo aswp_html::show_banner();

		//	Output the start of the form
		echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_top_link_ad', array(
			array('full_input' => wp_nonce_field('aswp_top_link_ad', '_wpnonce', TRUE, FALSE)),
			array('name' => 'aswp_top_link_ad_save', 'value' => 'yes')
		));

		//	Output the start of the table
		echo aswp_html::table_start('margin-bottom: 20px;');

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Enable Top Link Ad', ASWP_UNIQUE_NAME), '', '1', 'aswp_tla_enable', isset($tla_options['aswp_tla_enable']) == TRUE && $tla_options['aswp_tla_enable'] === '1' ? TRUE : FALSE);

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_reward_author', __('Reward Author', ASWP_UNIQUE_NAME));

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Donation<br /><span class="description">5 means 5% or exactly once per 20 page view, 0 will disable donations. Default is off.</span>', ASWP_UNIQUE_NAME), 'aswp_donation', $tla_options['aswp_donation'], 'width: 30px;', '%');

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_placement', __('Ad Placement', ASWP_UNIQUE_NAME));

		//	Output the checkboxes
		echo aswp_html::meta_box_checkbox_field(__('Show top link ad on selected pages', ASWP_UNIQUE_NAME), array(
			array(
				'checked' => isset($tla_options['aswp_show_tla_on_home']) == TRUE && $tla_options['aswp_show_tla_on_home'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_tla_on_home',
				'value' => '1',
				'text' => __('Home', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => isset($tla_options['aswp_show_tla_on_category']) == TRUE && $tla_options['aswp_show_tla_on_category'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_tla_on_category',
				'value' => '1',
				'text' => __('Category', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => isset($tla_options['aswp_show_tla_on_archive']) == TRUE && $tla_options['aswp_show_tla_on_archive'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_tla_on_archive',
				'value' => '1',
				'text' => __('Archive', ASWP_UNIQUE_NAME),
			),
		));

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_design', __('Ad Design', ASWP_UNIQUE_NAME));

		//	Output the checkboxes
		echo aswp_html::meta_box_checkbox_field(__('Formats<br /><span class="description">Choose more than 1 to show random format.</span>', ASWP_UNIQUE_NAME), array(
			array(
				'checked' => isset($tla_options['aswp_tla_format_1']) == TRUE && $tla_options['aswp_tla_format_1'] !== '' ? 'yes' : '',
				'name' => 'aswp_tla_format_1',
				'value' => '468_15',
				'text' => __('468 x 15', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => isset($tla_options['aswp_tla_format_2']) == TRUE && $tla_options['aswp_tla_format_2'] !== '' ? 'yes' : '',
				'name' => 'aswp_tla_format_2',
				'value' => '728_15',
				'text' => __('728 x 15', ASWP_UNIQUE_NAME),
			)
		));

		//	Output color pickers
		echo aswp_html::meta_box_color_pickers(__('Colors<br /><a href="#" id="aswp_restore_default">Restore Default</a>', ASWP_UNIQUE_NAME), array(
			array(
				'text' => __('Border', ASWP_UNIQUE_NAME),
				'picker_id' => '1',
				'input_name' => 'aswp_border_color',
				'input_value' => $tla_options['aswp_border_color']
			),
			array(
				'text' => __('Background', ASWP_UNIQUE_NAME),
				'picker_id' => '2',
				'picker' => '',
				'input_name' => 'aswp_background_color',
				'input_value' => $tla_options['aswp_background_color']
			),
			array(
				'text' => __('Title', ASWP_UNIQUE_NAME),
				'picker_id' => '3',
				'picker' => '',
				'input_name' => 'aswp_title_color',
				'input_value' => $tla_options['aswp_title_color']
			)
		));

		//	Output live ad preview
		echo aswp_html::meta_box_tla_preview(__('Ad Preview', ASWP_UNIQUE_NAME), array(
			'aswp_border_color' => $tla_options['aswp_border_color'],
			'aswp_background_color' => $tla_options['aswp_background_color'],
			'aswp_title_color' => $tla_options['aswp_title_color']
		));

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_advanced', __('Advanced', ASWP_UNIQUE_NAME), '', TRUE);

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Publisher ID<br /><span class="description">You can enter different Publisher ID for this ad only or<br />leave it empty to use the global Publisher ID set in options page.</span>', ASWP_UNIQUE_NAME), 'aswp_adv_publisher_id', $tla_options['aswp_adv_publisher_id']);

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Custom Channel', ASWP_UNIQUE_NAME), 'aswp_adv_custom_channel', $tla_options['aswp_adv_custom_channel']);

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_help', __('Help', ASWP_UNIQUE_NAME), '');

		//	Output the content meta box
		echo aswp_html::meta_box_insert_content(array(
			__('Top Ads will not be visible on your site while you are logged into the WordPress Admin. Please log out of WordPress in order to view these Ads.', ASWP_UNIQUE_NAME),
			__('If you wish to include the top link ad in some other position, add the following code to your theme where you want the link ad to appear:', ASWP_UNIQUE_NAME),
			'&lt;?php aswp_display_top_link_ad(); ?&gt;',
			__("<strong>Note:</strong> Some themes don't allow for this Ad to populate correctly. If it is cutting off the top of your theme, try another theme or disable this feature.", ASWP_UNIQUE_NAME),
		));

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the save button
		echo aswp_html::blue_button(__('Save', ASWP_UNIQUE_NAME));

		//	Output the end of the form
		echo aswp_html::form_end();

		echo aswp_html::wrap_footer();
	}

	/**
	 * Saves the options form into "options" table.
	 *
	 * @since	1.1
	 * @param	string	$post_data
	 * @return	void
	 *
	 */
	function save_ad($post_data)
	{
		//	Check nonce first
		if (wp_verify_nonce($post_data['_wpnonce'], 'aswp_top_link_ad') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Reward author field
		$reward_author = isset($post_data['aswp_donation']) == TRUE && $post_data['aswp_donation'] !== '' ? $post_data['aswp_donation'] : '5';

		//	Load options
		$options = get_option('aswp_options');

		//	Update donation number
		$options['reward_author'] = $reward_author;

		$options['top_link_ad'] = $post_data;

		//	Update options
		update_option('aswp_options', $options);
	}
}
?>