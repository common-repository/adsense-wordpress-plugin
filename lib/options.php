<?php
/**
 *	Contains functions for the options page in the WP admin area.
 *
 */

class aswp_options
{
	/**
	 * Display the main page for the options page.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function display_main()
	{
		$check_registration_status = aswp_core::check_registration();

		if ($check_registration_status === FALSE || $check_registration_status === 'confirm')
		{
			if (aswp_core::registration_form('aswp_options') !== TRUE)
			{
				return FALSE;
			}
		}

		//	Was form submitted?
		if (isset($_POST['aswp_options_saved']) == TRUE && $_POST['aswp_options_saved'] == 'yes')
		{
			$this->save_options($_POST);

			echo aswp_html::admin_notice('updated', __('Options saved.', ASWP_UNIQUE_NAME));
		}

		echo aswp_html::wrap_header();
?>
	<h2><?php _e('Options', ASWP_UNIQUE_NAME); ?></h2>
<?php
		echo aswp_html::show_banner();

		//	Load options
		$options = get_option('aswp_options');

		if (isset($options['options']) == TRUE)
		{
			$options = $options['options'];
		}

		//	Output the start of the form
		echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_options', array(
			array('full_input' => wp_nonce_field('aswp_options', '_wpnonce', TRUE, FALSE)),
			array('name' => 'aswp_options_saved', 'value' => 'yes')
		));

		//	Output the start of the table
		echo aswp_html::table_start();

		//	Output the input field
		echo aswp_html::tr_row_input(__('Publisher ID <strong>(Numbers Only!)</strong>', ASWP_UNIQUE_NAME), 'aswp_publisher_id', isset($options['aswp_publisher_id']) == TRUE && $options['aswp_publisher_id'] !== '' ? esc_attr($options['aswp_publisher_id']) : '', __('Your AdSense Publisher ID can be found in your AdSense panel in the top right corner, eg: pub-<strong>1234567890123456</strong>', ASWP_UNIQUE_NAME));

		//	Output the input field
		echo aswp_html::tr_row_input(__('Custom Channel', ASWP_UNIQUE_NAME), 'aswp_custom_channel', isset($options['aswp_custom_channel']) == TRUE && $options['aswp_custom_channel'] !== '' ? esc_attr($options['aswp_custom_channel']) : '', __('(optional)', ASWP_UNIQUE_NAME));

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Disable all ads for everybody', ASWP_UNIQUE_NAME), '', '1', 'aswp_disable_ads_everyone', isset($options['aswp_disable_ads_everyone']) == TRUE && $options['aswp_disable_ads_everyone'] === '1' ? TRUE : FALSE);

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Disable all ads for administrators', ASWP_UNIQUE_NAME), '', '1', 'aswp_disable_ads_admin',  isset($options['aswp_disable_ads_admin']) == TRUE && $options['aswp_disable_ads_admin'] === '1' ? TRUE : FALSE);

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Enable all ads for administrators only', ASWP_UNIQUE_NAME), '', '1', 'aswp_enable_ads_admin',  isset($options['aswp_enable_ads_admin']) == TRUE && $options['aswp_enable_ads_admin'] === '1' ? TRUE : FALSE);

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Delete data on plugin deactivate', ASWP_UNIQUE_NAME), __('Check to delete the data tables and any other data that was created by this plugin', ASWP_UNIQUE_NAME), '1', 'aswp_delete_data_tables',  isset($options['aswp_delete_data_tables']) == TRUE && $options['aswp_delete_data_tables'] === '1' ? TRUE : FALSE);

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the save button
		echo aswp_html::blue_button(__('Save Changes', ASWP_UNIQUE_NAME));

		//	Output the end of the form
		echo aswp_html::form_end();

		echo aswp_html::wrap_footer();
	}

	/**
	 * Saves the options form into "options" table.
	 *
	 * @since	1.0
	 * @param	string	$post_data
	 * @return	void
	 *
	 */
	function save_options($post_data)
	{
		//	Check nonce first
		if (wp_verify_nonce($post_data['_wpnonce'], 'aswp_options') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Load options
		$options = get_option('aswp_options');

		$options['options'] = $post_data;

		//	Update options
		update_option('aswp_options', $options);
	}
}
?>