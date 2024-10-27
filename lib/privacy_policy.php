<?php
/**
 *	Contains functions for the privacy policy page in the WP admin area.
 *
 */

class aswp_privacy_policy
{
	/**
	 * Display the main page for the privacy policy page.
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
			if (aswp_core::registration_form('aswp_privacy_policy_creator') !== TRUE)
			{
				return FALSE;
			}
		}

		//	Was form submitted?
		if (isset($_POST['aswp_privacy_policy_saved']) == TRUE && $_POST['aswp_privacy_policy_saved'] == 'yes')
		{
			$result_pp = $this->create_privacy_policy($_POST);

			if ($result_pp == FALSE)
			{
				echo aswp_html::admin_notice('error', __('Oops, an error occurred!', ASWP_UNIQUE_NAME));
			}
			else
			{
				echo aswp_html::admin_notice('updated', __('Privacy policy page was successfully created.', ASWP_UNIQUE_NAME));
			}
		}

		//	Load options
		$options = get_option('aswp_options');

		//	Get just the privacy policy options
		$pp_options = isset($options['privacy_policy']) == TRUE ? $options['privacy_policy'] : FALSE;

		//	If privacy policy not found in options, set the default settings
		if ($pp_options === FALSE)
		{
			$pp_options = $this->get_default_texts();
		}

		echo aswp_html::wrap_header();
?>
	<h2><?php _e('Privacy Policy Creator', ASWP_UNIQUE_NAME); ?></h2>
<?php
		echo aswp_html::show_banner();

		//	Output the start of the form
		echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_privacy_policy_creator', array(
			array('full_input' => wp_nonce_field('aswp_privacy_policy', '_wpnonce', TRUE, FALSE)),
			array('name' => 'aswp_privacy_policy_saved', 'value' => 'yes')
		));

		//	Output the start of the table
		echo aswp_html::table_start();

		//	Output input field
		echo aswp_html::tr_row_input(__('Blog Name', ASWP_UNIQUE_NAME), 'aswp_pp_blog_name', $pp_options['blog_name'], __('Use [blogname] for substitution within the privacy text. You can use it as many times as you like.', ASWP_UNIQUE_NAME));

		//	Output input field
		echo aswp_html::tr_row_input(__('Email', ASWP_UNIQUE_NAME), 'aswp_pp_email', $pp_options['email'], __('Use [email] for substitution within the privacy text. You can use it as many times as you like.', ASWP_UNIQUE_NAME));

		//	Output input field
		echo aswp_html::tr_row_input(__('Blog URL', ASWP_UNIQUE_NAME), 'aswp_pp_blog_url', $pp_options['blog_url'], __('Use [blogurl] for substitution within the privacy text. You can use it as many times as you like.', ASWP_UNIQUE_NAME));

		//	Output input field
		echo aswp_html::tr_row_input(__('Page Title', ASWP_UNIQUE_NAME), 'aswp_pp_page_title', $pp_options['page_title']);

		//	Output input field
		echo aswp_html::tr_row_input(__('Page URL', ASWP_UNIQUE_NAME), 'aswp_pp_page_url', $pp_options['page_url']);

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the save button
		echo aswp_html::blue_button(__('Create Privacy Policy', ASWP_UNIQUE_NAME));

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_privacy_policy_content', __('Privacy Policy Content', ASWP_UNIQUE_NAME));

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 1', ASWP_UNIQUE_NAME), 'aswp_pp_s1_cb', $pp_options['aswp_pp_s1_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('This is the basic privacy statement of intent that your blog supports. You will nearly always want to include this section.', ASWP_UNIQUE_NAME), 'aswp_pp_s1_input', $pp_options['aswp_pp_s1_input'] !== '' ? $pp_options['aswp_pp_s1_input'] : '', 'width: 50%;', 'aswp_pp_s1_textarea', $pp_options['aswp_pp_s1_textarea'] !== '' ? $pp_options['aswp_pp_s1_textarea'] : '', 'width: 98%;');

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 2', ASWP_UNIQUE_NAME), 'aswp_pp_s2_cb', $pp_options['aswp_pp_s2_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('Required by Google AdSense Terms and Conditions.', ASWP_UNIQUE_NAME), 'aswp_pp_s2_input', $pp_options['aswp_pp_s2_input'] !== '' ? $pp_options['aswp_pp_s2_input'] : '', 'width: 50%;', 'aswp_pp_s2_textarea', $pp_options['aswp_pp_s2_textarea'] !== '' ? $pp_options['aswp_pp_s2_textarea'] : '', 'width: 98%;');

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 3', ASWP_UNIQUE_NAME), 'aswp_pp_s3_cb', $pp_options['aswp_pp_s3_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('Is your website or blog suitable for people under the age of 18? Does it contain adult content or themes that are not suitable for children?', ASWP_UNIQUE_NAME), 'aswp_pp_s3_input', $pp_options['aswp_pp_s3_input'] !== '' ? $pp_options['aswp_pp_s3_input'] : '', 'width: 50%;', 'aswp_pp_s3_textarea', $pp_options['aswp_pp_s3_textarea'] !== '' ? $pp_options['aswp_pp_s3_textarea'] : '', 'width: 98%;');

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 4', ASWP_UNIQUE_NAME), 'aswp_pp_s4_cb', $pp_options['aswp_pp_s4_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('This section states what information your site might record, typically IP addresses in web logs for example, or perhaps Google Analytics.', ASWP_UNIQUE_NAME), 'aswp_pp_s4_input', $pp_options['aswp_pp_s4_input'] !== '' ? $pp_options['aswp_pp_s4_input'] : '', 'width: 50%;', 'aswp_pp_s4_textarea', $pp_options['aswp_pp_s4_textarea'] !== '' ? $pp_options['aswp_pp_s4_textarea'] : '', 'width: 98%;');

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 5', ASWP_UNIQUE_NAME), 'aswp_pp_s5_cb', $pp_options['aswp_pp_s5_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('If you blog links to other sites, this section mitigates you against whatever content might be contained by third party websites.', ASWP_UNIQUE_NAME), 'aswp_pp_s5_input', $pp_options['aswp_pp_s5_input'] !== '' ? $pp_options['aswp_pp_s5_input'] : '', 'width: 50%;', 'aswp_pp_s5_textarea', $pp_options['aswp_pp_s5_textarea'] !== '' ? $pp_options['aswp_pp_s5_textarea'] : '', 'width: 98%;');

		//	Output the privacy policy section
		echo aswp_html::meta_box_input_pp_field(__('Section 6', ASWP_UNIQUE_NAME), 'aswp_pp_s6_cb', $pp_options['aswp_pp_s6_cb'] === '1' ? '1' : '', __(' Include this section', ASWP_UNIQUE_NAME), __('Put any other information here, for example state that changes may be made to the policy at any time.', ASWP_UNIQUE_NAME), 'aswp_pp_s6_input', $pp_options['aswp_pp_s6_input'] !== '' ? $pp_options['aswp_pp_s6_input'] : '', 'width: 50%;', 'aswp_pp_s6_textarea', $pp_options['aswp_pp_s6_textarea'] !== '' ? $pp_options['aswp_pp_s6_textarea'] : '', 'width: 98%;');

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the table
		echo aswp_html::table_start();

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Include a last updated date stamp?', ASWP_UNIQUE_NAME), '', '1', 'aswp_pp_timestamp', isset($pp_options['include_timestamp']) == TRUE && $pp_options['include_timestamp'] === '1' ? TRUE : FALSE);

		//	Output the checkbox
		echo aswp_html::tr_row_checkbox(__('Include a credit link for the AdSense Wordpress Plugin?', ASWP_UNIQUE_NAME), '', '1', 'aswp_pp_credit_link', isset($pp_options['include_credit_link']) == TRUE && $pp_options['include_credit_link'] === '1' ? TRUE : FALSE);

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the save button
		echo aswp_html::blue_button(__('Create Privacy Policy', ASWP_UNIQUE_NAME));

		//	Output the end of the form
		echo aswp_html::form_end();

		echo aswp_html::wrap_footer();
	}

	/**
	 * It creates or updates the privacy policy page.
	 *
	 * @since	1.0
	 * @param	array	$post_data
	 * @return	boolean
	 *
	 */
	function create_privacy_policy($post_data)
	{
		//	Check nonce first
		if (wp_verify_nonce($post_data['_wpnonce'], 'aswp_privacy_policy') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Load options
		$options = get_option('aswp_options');

		//	Get just the privacy policy options
		$pp_options = isset($options['privacy_policy']) == TRUE ? $options['privacy_policy'] : FALSE;

		//	If privacy policy not found in options, set the default settings
		if ($pp_options === FALSE)
		{
			$pp_options = $this->get_default_texts();
		}

		//	Combine all the sections into one text
		$privacy_policy_text = '';

		if (isset($post_data['aswp_pp_s1_cb']) == TRUE && $post_data['aswp_pp_s1_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s1_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s1_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s1_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s1_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s1_input'] = isset($post_data['aswp_pp_s1_input']) == TRUE ? $post_data['aswp_pp_s1_input'] : '';
			$pp_options['aswp_pp_s1_textarea'] = isset($post_data['aswp_pp_s1_textarea']) == TRUE ? $post_data['aswp_pp_s1_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s1_cb'] = '';
		}

		if (isset($post_data['aswp_pp_s2_cb']) == TRUE && $post_data['aswp_pp_s2_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s2_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s2_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s2_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s2_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s2_input'] = isset($post_data['aswp_pp_s2_input']) == TRUE ? $post_data['aswp_pp_s2_input'] : '';
			$pp_options['aswp_pp_s2_textarea'] = isset($post_data['aswp_pp_s2_textarea']) == TRUE ? $post_data['aswp_pp_s2_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s2_cb'] = '';
		}

		if (isset($post_data['aswp_pp_s3_cb']) == TRUE && $post_data['aswp_pp_s3_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s3_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s3_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s3_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s3_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s3_input'] = isset($post_data['aswp_pp_s3_input']) == TRUE ? $post_data['aswp_pp_s3_input'] : '';
			$pp_options['aswp_pp_s3_textarea'] = isset($post_data['aswp_pp_s3_textarea']) == TRUE ? $post_data['aswp_pp_s3_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s3_cb'] = '';
		}

		if (isset($post_data['aswp_pp_s4_cb']) == TRUE && $post_data['aswp_pp_s4_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s4_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s4_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s4_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s4_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s4_input'] = isset($post_data['aswp_pp_s4_input']) == TRUE ? $post_data['aswp_pp_s4_input'] : '';
			$pp_options['aswp_pp_s4_textarea'] = isset($post_data['aswp_pp_s4_textarea']) == TRUE ? $post_data['aswp_pp_s4_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s4_cb'] = '';
		}

		if (isset($post_data['aswp_pp_s5_cb']) == TRUE && $post_data['aswp_pp_s5_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s5_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s5_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s5_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s5_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s5_input'] = isset($post_data['aswp_pp_s5_input']) == TRUE ? $post_data['aswp_pp_s5_input'] : '';
			$pp_options['aswp_pp_s5_textarea'] = isset($post_data['aswp_pp_s5_textarea']) == TRUE ? $post_data['aswp_pp_s5_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s5_cb'] = '';
		}

		if (isset($post_data['aswp_pp_s6_cb']) == TRUE && $post_data['aswp_pp_s6_cb'] === '1')
		{
			$privacy_policy_text .= isset($post_data['aswp_pp_s6_input']) == TRUE ? '<h2>'.$post_data['aswp_pp_s6_input'].'</h2>' : '';
			$privacy_policy_text .= isset($post_data['aswp_pp_s6_textarea']) == TRUE ? '<p>'.str_replace(array("\r", "\n"), '<br />', $post_data['aswp_pp_s6_textarea']).'</p>' : '';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_s6_input'] = isset($post_data['aswp_pp_s6_input']) == TRUE ? $post_data['aswp_pp_s6_input'] : '';
			$pp_options['aswp_pp_s6_textarea'] = isset($post_data['aswp_pp_s6_textarea']) == TRUE ? $post_data['aswp_pp_s6_textarea'] : '';
		}
		else
		{
			$pp_options['aswp_pp_s6_cb'] = '';
		}

		//	Blog name
		$blog_name = isset($post_data['aswp_pp_blog_name']) == TRUE ? $post_data['aswp_pp_blog_name'] : '';
		$email = isset($post_data['aswp_pp_email']) == TRUE ? $post_data['aswp_pp_email'] : '';
		$blog_url = isset($post_data['aswp_pp_blog_url']) == TRUE ? $post_data['aswp_pp_blog_url'] : '';

		//	Now let's replace [blogname], [email] and [blogurl]
		$privacy_policy_text = str_replace(array('[blogname]', '[email]', '[blogurl]'), array($blog_name, $email, $blog_url), $privacy_policy_text);

		// Let's check if page title is set, if not, just leave it blank
		$page_title = isset($post_data['aswp_pp_page_title']) == TRUE ? $post_data['aswp_pp_page_title'] : '';

		// Also check if page url is set, if not, set it to "privacy-policy"
		$page_url = isset($post_data['aswp_pp_page_url']) == TRUE && $post_data['aswp_pp_page_url'] !== '' ? $post_data['aswp_pp_page_url'] : 'privacy-policy';

		//	Set the credit link
		if (isset($post_data['aswp_pp_credit_link']) == TRUE && $post_data['aswp_pp_credit_link'] === '1')
		{
			$privacy_policy_text .= '<p style="float: right;">'.__('Created By', ASWP_UNIQUE_NAME).' <a href="http://www.adsensewpplugin.com">Adsense Wordpress Plugin</a></p>';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_credit_link'] = isset($post_data['aswp_pp_credit_link']) == TRUE ? '1' : '';
		}

		//	Set the timestamp
		if (isset($post_data['aswp_pp_timestamp']) == TRUE && $post_data['aswp_pp_timestamp'] === '1')
		{
			$privacy_policy_text .= '<p>'.__('Updated on', ASWP_UNIQUE_NAME).' '.date(get_option('date_format'), strtotime("now")).'</p>';

			//	Save the new texts to $pp_options which will then be saved into "options" table
			$pp_options['aswp_pp_timestamp'] = isset($post_data['aswp_pp_timestamp']) == TRUE ? '1' : '';
		}

		//	Set the blogname
		$pp_options['blog_name'] = isset($post_data['aswp_pp_blog_name']) == TRUE ? $post_data['aswp_pp_blog_name'] : '';

		//	Set the email
		$pp_options['email'] = isset($post_data['aswp_pp_email']) == TRUE ? $post_data['aswp_pp_email'] : '';

		//	Set the blog url
		$pp_options['blog_url'] = isset($post_data['aswp_pp_blog_url']) == TRUE ? $post_data['aswp_pp_blog_url'] : '';

		//	Set the page title
		$pp_options['page_title'] = isset($post_data['aswp_pp_page_title']) == TRUE ? $post_data['aswp_pp_page_title'] : '';

		//	Set the page url
		$pp_options['page_url'] = isset($post_data['aswp_pp_page_url']) == TRUE ? $post_data['aswp_pp_page_url'] : '';

		//	Check if page exist
		$page_id = isset($pp_options['privacy_policy_page_id']) == TRUE ? $pp_options['privacy_policy_page_id'] : 0;
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
		  	'post_content' => $privacy_policy_text,
		   	'post_name' => $page_url,
		  	'post_status' => 'publish',
		  	'post_title' => $page_title,
		  	'post_type' => 'page'
		);

		$id_of_page = wp_insert_post($post);

		if ($id_of_page !== 0)
		{
			$pp_options['privacy_policy_page_id'] = $id_of_page;

			$options['privacy_policy'] = $pp_options;

			//	Update the options
			update_option('aswp_options', $options);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Gets the default texts.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	array
	 *
	 */
	function get_default_texts()
	{
		$pp_options = array(
			'blog_name' => get_option('blogname'),
			'email' => get_option('admin_email'),
			'blog_url' => get_option('siteurl'),
			'page_title' => __('Privacy Policy', ASWP_UNIQUE_NAME),
			'page_url' => 'privacy-policy',
			'aswp_pp_s1_cb' => esc_attr('1'),
			'aswp_pp_s1_input' => esc_attr(__('Privacy Policy for [blogurl]', ASWP_UNIQUE_NAME)),
			'aswp_pp_s1_textarea' => esc_attr(__('Your privacy is important to us. To better protect your privacy we provide this notice explaining our online information practices and the choices you can make about the way your information is collected and used. To make this notice easy to find, we make it available on our homepage and at every point where personally identifiable information may be requested.', ASWP_UNIQUE_NAME)),
			'aswp_pp_s2_cb' => esc_attr('1'),
			'aswp_pp_s2_input' => esc_attr(__('Google Adsense and the DoubleClick DART Cookie', ASWP_UNIQUE_NAME)),
			'aswp_pp_s2_textarea' => esc_attr(__("Google, as a third party advertisement vendor, uses cookies to serve ads on this site. The use of DART cookies by Google enables them to serve adverts to visitors that are based on their visits to this website as well as other sites on the internet.

To opt out of the DART cookies you may visit the Google ad and content network privacy policy at the following url http://www.google.com/privacy_ads.html Tracking of users through the DART cookie mechanisms are subject to Google's own privacy policies.

Other Third Party ad servers or ad networks may also use cookies to track users activities on this website to measure advertisement effectiveness and other reasons that will be provided in their own privacy policies, [blogname] has no access or control over these cookies that may be used by third party advertisers.", ASWP_UNIQUE_NAME)),
			'aswp_pp_s3_cb' => esc_attr(''),
			'aswp_pp_s3_input' => esc_attr(__('Our Commitment To Childrens Privacy', ASWP_UNIQUE_NAME)),
			'aswp_pp_s3_textarea' => esc_attr(__('Protecting the privacy of the very young is especially important. For that reason, [blogname] will never collect or maintain information at our website from those we actually know are under 18, and no part of our website is structured to attract anyone under 18.
Under our Terms of Service, children under 18 are not allowed to access our service.', ASWP_UNIQUE_NAME)),
			'aswp_pp_s4_cb' => esc_attr('1'),
			'aswp_pp_s4_input' => esc_attr(__('Collection of Personal Information', ASWP_UNIQUE_NAME)),
			'aswp_pp_s4_textarea' => esc_attr(__('When visiting [blogname], the IP address used to access the site will be logged along with the dates and times of access. This information is purely used to analyze trends, administer the site, track users movement and gather broad demographic information for internal use. Most importantly, any recorded IP addresses are not linked to personally identifiable information.', ASWP_UNIQUE_NAME)),
			'aswp_pp_s5_cb' => esc_attr('1'),
			'aswp_pp_s5_input' => esc_attr(__('Links to third party Websites', ASWP_UNIQUE_NAME)),
			'aswp_pp_s5_textarea' => esc_attr(__('We have included links on this site for your use and reference. We are not responsible for the privacy policies on these websites. You should be aware that the privacy policies of these sites may differ from our own.', ASWP_UNIQUE_NAME)),
			'aswp_pp_s6_cb' => esc_attr('1'),
			'aswp_pp_s6_input' => esc_attr(__('Changes to this Privacy Statement', ASWP_UNIQUE_NAME)),
			'aswp_pp_s6_textarea' => esc_attr(__('The contents of this statement may be altered at any time, at our discretion.

If you have any questions regarding the privacy policy of [blogname] then you may contact us at [email]', ASWP_UNIQUE_NAME)),
			'include_timestamp' => esc_attr('1'),
			'include_credit_link' => '',
		);

		return $pp_options;
	}
}
?>