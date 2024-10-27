<?php
/**
 *	Contains functions for the google search page in the WP admin area.
 *
 */

class aswp_google_search
{
	/**
	 * Display the main page for the google search page.
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
			if (aswp_core::registration_form('aswp_google_search') !== TRUE)
			{
				return FALSE;
			}
		}

		//	Was form submitted?
		if (isset($_POST['aswp_gs_saved']) == TRUE && $_POST['aswp_gs_saved'] == 'yes')
		{
			$result_gs = $this->save_options($_POST);

			if ($result_gs == FALSE)
			{
				echo aswp_html::admin_notice('error', __('Oops, an error occurred!', ASWP_UNIQUE_NAME));
			}
			else
			{
				echo aswp_html::admin_notice('updated', __('Google Search options saved.', ASWP_UNIQUE_NAME));
			}
		}

		echo aswp_html::wrap_header();
?>
	<h2><?php _e('Google Search', ASWP_UNIQUE_NAME); ?></h2>
<?php
		echo aswp_html::show_banner();

		//	Load options
		$options = get_option('aswp_options');

		if (isset($options['google_search']) == TRUE)
		{
			$options = $options['google_search'];
		}

		//	Output the start of the form
		echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_google_search', array(
			array('full_input' => wp_nonce_field('aswp_gs', '_wpnonce', TRUE, FALSE)),
			array('name' => 'aswp_gs_saved', 'value' => 'yes')
		));

		//	Output the start of the table
		echo aswp_html::table_start();

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Replace WP search', ASWP_UNIQUE_NAME).'<br /><span class="description">'.__('This function works only if your theme does not have a file called "searchform.php".', ASWP_UNIQUE_NAME).'</span>', __('By checking this box, the default Wordpress search will be replaced by Google search', ASWP_UNIQUE_NAME), '1', 'aswp_replace_search', isset($options['aswp_replace_search']) == TRUE && $options['aswp_replace_search'] === '1' ? TRUE : FALSE);

		//	Output input field
		echo aswp_html::tr_row_input(__('Page Title', ASWP_UNIQUE_NAME), 'aswp_page_title', isset($options['aswp_page_title']) == TRUE ? esc_attr($options['aswp_page_title']) : __('Search', ASWP_UNIQUE_NAME), __('Title of the page where Google results will be shown.', ASWP_UNIQUE_NAME));

		//	Output input field
		echo aswp_html::tr_row_input(__('Page URL Slug', ASWP_UNIQUE_NAME), 'aswp_page_url', isset($options['aswp_page_url']) == TRUE ? esc_attr($options['aswp_page_url']) : 'search', __('Page url slug where the Google results will be shown.', ASWP_UNIQUE_NAME));

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_google_search', __('Google Search Codes', ASWP_UNIQUE_NAME));

		//	Output the textarea
		echo aswp_html::meta_box_textarea_field(__('Search Box Code', ASWP_UNIQUE_NAME), '', 'aswp_google_box_code', isset($options['aswp_google_box_code']) == TRUE ? esc_attr($options['aswp_google_box_code']) : '');

		//	Output the textarea
		echo aswp_html::meta_box_textarea_field(__('Search Results Code', ASWP_UNIQUE_NAME), '', 'aswp_google_search_results_code', isset($options['aswp_google_search_results_code']) == TRUE ? esc_attr($options['aswp_google_search_results_code']) : '');

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_gs_help', __('Help', ASWP_UNIQUE_NAME), '');

		//	Output the content meta box
		echo aswp_html::meta_box_insert_content(array(
			__('Not sure where and how to get the Google Search Codes? Visit this page for more information: ', ASWP_UNIQUE_NAME).'<a href="http://www.adsensewpplugin.com/plugin-training">http://www.adsensewpplugin.com/plugin-training</a>',
			__('You can also display the Google Search Box by calling it directly in your theme by using the following line:', ASWP_UNIQUE_NAME),
			'&lt;?php aswp_display_google_search_box(); ?&gt;'
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
	function save_options($post_data)
	{
		//	Check nonce first
		if (wp_verify_nonce($post_data['_wpnonce'], 'aswp_gs') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Load options
		$options = get_option('aswp_options');

		//	Checkbox checked?
		$gs_options['aswp_replace_search'] = isset($post_data['aswp_replace_search']) == TRUE && $post_data['aswp_replace_search'] === '1' ? '1' : '';

		//	Set the page title
		$gs_options['aswp_page_title'] = isset($post_data['aswp_page_title']) == TRUE ? $post_data['aswp_page_title'] : '';

		//	Set the page url
		$gs_options['aswp_page_url'] = isset($post_data['aswp_page_url']) == TRUE ? $post_data['aswp_page_url'] : '';

		//	Search Box Code
		$gs_options['aswp_google_box_code'] = isset($post_data['aswp_google_box_code']) == TRUE ? $post_data['aswp_google_box_code'] : '';

		//	Search Results Code
		$gs_options['aswp_google_search_results_code'] = isset($post_data['aswp_google_search_results_code']) == TRUE ? $post_data['aswp_google_search_results_code'] : '';

		//	Check if page exist
		$page_id = isset($options['google_search']['gs_page_id']) == TRUE ? $options['google_search']['gs_page_id'] : 0;
		$page_data = get_page($page_id);

		if (is_null($page_data) == TRUE)
		{
			$page_id = '';
		}

		//	Create/update the privacy policy page
		$post = array(
			'ID' => $page_id,
		    'comment_status' => 'closed',
		  	'ping_status' => 'closed',
		  	'post_author' => '1',
		  	'post_content' => '[aswp_gsr]',
		   	'post_name' => $gs_options['aswp_page_url'],
		  	'post_status' => 'publish',
		  	'post_title' => $gs_options['aswp_page_title'],
		  	'post_type' => 'page'
		);

		$id_of_page = wp_insert_post($post);

		if ($id_of_page !== 0)
		{
			$gs_options['gs_page_id'] = $id_of_page;

			$options['google_search'] = $gs_options;

			//	Update options
			update_option('aswp_options', $options);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
?>