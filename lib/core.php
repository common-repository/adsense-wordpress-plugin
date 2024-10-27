<?php
/**
 *	Contains the core functions.
 *
 */

class aswp_core
{
	public $wpdb = null;

	/**
	 * Constructor
	 *
	 * @since	1.0
	 *
	 */
	function __construct($wpdb)
	{
		$this->wpdb = $wpdb;

		//	Executed when plugin is activated
		register_activation_hook(ASWP_FILE, array($this, 'activate_plugin'));

		//	Executed when plugin is deactivated
		register_deactivation_hook(ASWP_FILE, array($this, 'deactivate_plugin'));

		//	Action that creates the WP admin menu
		add_action('admin_menu', array($this, 'create_admin_menu'));

		//	Action that call function that registeres the stylesheet
		add_action('admin_init', array($this, 'admin_init'));

		//	Adds checkbox to the post/page edit page for disabling the ads
		add_action('add_meta_boxes', array($this, 'disable_ads_on_page'));

		//	Save the data from meta box on editing posts/pages
		add_action('save_post', array($this, 'save_disable_ads_mb'));

		//	Init hook
		add_action('init', array($this, 'init'));

		//	Ajax callback function
		add_action('wp_ajax_aswp_ajax_call', array($this, 'ajax_callback'));

		//	When plugin is loaded
		add_action('plugins_loaded', array($this, 'plugin_loaded'));
	}

	/**
	 * Creates the menu in the WP admin area.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function create_admin_menu()
	{
		//	Create the main menu
		add_menu_page(__(ASWP_NAME, ASWP_UNIQUE_NAME), __(ASWP_NAME, ASWP_UNIQUE_NAME), 'manage_options', 'aswp_ads', '', plugins_url('images/icon.png', dirname(__FILE__)));

		//	Create the submenus
		$ads_page = add_submenu_page('aswp_ads', __('Ads', ASWP_UNIQUE_NAME), __('Ads', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_ads', array(new aswp_ads($this->wpdb), 'display_main'));
		$create_new_ad_page = add_submenu_page('aswp_ads', __('Create New Ad', ASWP_UNIQUE_NAME), __('Create New Ad', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_create_new_ad', array(new aswp_create_ad($this->wpdb), 'display_main'));
		$top_link_page = add_submenu_page('aswp_ads', __('Top Link Ad', ASWP_UNIQUE_NAME), __('Top Link Ad', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_top_link_ad', array(new aswp_top_link_ad, 'display_main'));
		$google_search_page = add_submenu_page('aswp_ads', __('Google Search', ASWP_UNIQUE_NAME), __('Google Search', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_google_search', array(new aswp_google_search, 'display_main'));
		$privacy_policy_page = add_submenu_page('aswp_ads', __('Privacy Policy Creator', ASWP_UNIQUE_NAME), __('Privacy Policy Creator', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_privacy_policy_creator', array(new aswp_privacy_policy, 'display_main'));
		$options_page = add_submenu_page('aswp_ads', __('Options', ASWP_UNIQUE_NAME), __('Options', ASWP_UNIQUE_NAME), 'manage_options', 'aswp_options', array(new aswp_options, 'display_main'));

		//	Stylesheet action
		add_action('admin_print_styles-'.$ads_page, array($this, 'add_stylesheet'));
		add_action('admin_print_styles-'.$create_new_ad_page, array($this, 'add_stylesheet'));
		add_action('admin_print_styles-'.$top_link_page, array($this, 'add_stylesheet'));
		add_action('admin_print_styles-'.$google_search_page, array($this, 'add_stylesheet'));
		add_action('admin_print_styles-'.$privacy_policy_page, array($this, 'add_stylesheet'));
		add_action('admin_print_styles-'.$options_page, array($this, 'add_stylesheet'));

		//	Javascript action
		add_action('admin_print_styles-'.$ads_page, array($this, 'add_scripts'));
		add_action('admin_print_styles-'.$create_new_ad_page, array($this, 'add_scripts'));
		add_action('admin_print_styles-'.$top_link_page, array($this, 'add_scripts'));
		add_action('admin_print_styles-'.$google_search_page, array($this, 'add_scripts'));
		add_action('admin_print_styles-'.$privacy_policy_page, array($this, 'add_scripts'));
	}

	/**
	 * Executed when the plugin is activated.
	 * Calls a function that creates the required tables and adds some default data.
	 *
	 * @since 	1.0
	 * @param	void
	 * @return	boolean
	 *
	 */
	function activate_plugin()
	{
		//	Check if multisite function exist and multisite is turned on
		if (function_exists('is_multisite') == TRUE && is_multisite() == TRUE)
		{
			//	Check if the user is activation this plugin network wide.
			if (isset($_GET['networkwide']) == TRUE && $_GET['networkwide'] === '1')
			{
				//	Get the ID of the blog currently activated
				$current_blog = $this->wpdb->blogid;

				//	Get all blog ID's
				$blog_ids = $this->wpdb->get_col($this->wpdb->prepare("SELECT `blog_id` FROM `".$this->wpdb->blogs."`"));

				//	Loop over each blog
				foreach ($blog_ids as $blog_id)
				{
					//	Activate the current blog
					switch_to_blog($blog_id);

					//	Run the function that creates the required tables and such
					$this->_aswp_activate();
				}

				//	Switch back to the blog that was activated at the start
				switch_to_blog($current_blog);

				return TRUE;
			}
		}
		else
		{
			//	If this is not a network activation, run the function just once
			$this->_aswp_activate();

			return TRUE;
		}
	}

	/**
	 * Function that is called when the plugin is activated.
	 * It creates the required tables and adds some default data to tables.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function _aswp_activate()
	{
		//	Create table if it doesn't exist
		if ($this->wpdb->get_var("show tables like '".$this->wpdb->prefix.ASWP_DATA_TABLE."'") != $this->wpdb->prefix.ASWP_DATA_TABLE)
		{
			$sql = "CREATE TABLE  `".$this->wpdb->prefix.ASWP_DATA_TABLE."` (
					`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`ad_name` VARCHAR( 200 ) NOT NULL ,
					`ad_type` TINYINT( 1 ) NOT NULL DEFAULT  '1',
					`ad_placement` TEXT NOT NULL ,
					`ad_design` TEXT NOT NULL ,
					`date_created` DATETIME NOT NULL
					) ENGINE = MYISAM ;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}

	/**
	 * Deactivate the plugin.
	 * It's basically the same as the activate_plugin function except it calls the deactivate function.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	boolean
	 *
	 */
	function deactivate_plugin()
	{
		//	Check if multisite function exist and multisite is turned on
		if (function_exists('is_multisite') == TRUE && is_multisite() == TRUE)
		{
			//	Check if the user is activation this plugin network wide.
			if (isset($_GET['networkwide']) == TRUE && $_GET['networkwide'] === '1')
			{
				//	Get the ID of the blog currently activated
				$current_blog = $this->wpdb->blogid;

				//	Get all blog ID's
				$blog_ids = $this->wpdb->get_col($this->wpdb->prepare("SELECT `blog_id` FROM `".$this->wpdb->blogs."`"));

				//	Loop over each blog
				foreach ($blog_ids as $blog_id)
				{
					//	Activate the current blog
					switch_to_blog($blog_id);

					//	Run the function that creates the required tables and such
					$this->_aswp_deactivate();
				}

				//	Switch back to the blog that was activated at the start
				switch_to_blog($current_blog);

				return TRUE;
			}
		}
		else
		{
			//	If this is not a network activation, run the function just once
			$this->_aswp_deactivate();

			return TRUE;
		}
	}

	/**
	 * Function that is called when the plugin is deactivated.
	 * Remove the table and any extra settings from options table if there are any.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function _aswp_deactivate()
	{
		//	Load options
		$options = get_option('aswp_options');

		if (isset($options['options']['aswp_delete_data_tables']) == TRUE && $options['options']['aswp_delete_data_tables'] === '1')
		{
			//	Delete the table
			$sql = "DROP TABLE `".$this->wpdb->prefix.ASWP_DATA_TABLE."`";
			$this->wpdb->query($sql);

			//	Also delete options from "options" table
			delete_option('aswp_options');
		}
	}

	/**
	 * Admin init
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function admin_init()
	{
		//	Register stylesheets and javascripts for admin area
		wp_register_style('aswp_admin_css', plugins_url('css/admin_styles.css', dirname(__FILE__)));
		wp_register_script('aswp_admin_js_cp', plugins_url('js/colorpicker.js', dirname(__FILE__)));
		wp_register_script('aswp_admin_js_functions', plugins_url('js/functions.js', dirname(__FILE__)));

		//	Check if user has the required permissions
		if (current_user_can('edit_posts') == TRUE && current_user_can('edit_pages') == TRUE)
		{
			add_filter('mce_buttons', array($this, 'filter_mce_button'));
			add_filter('mce_external_plugins', array($this, 'filter_mce_plugin'));
		}
	}

	/**
	 * Adds custom stylesheet for the plugin admin pages only.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function add_stylesheet()
	{
		wp_enqueue_style('aswp_admin_css');
	}

	/**
	 * Adds custom scripts for the plugin admin pages only.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function add_scripts()
	{
		wp_enqueue_script('aswp_admin_js_cp');
		wp_enqueue_script('aswp_admin_js_functions');
	}

	/**
	 * Function that displays the meta box in the edit page for pages/posts.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function disable_ads_on_page()
	{
		add_meta_box(
	        'aswp_disable_ads',
	        __('Disable Ads', ASWP_UNIQUE_NAME),
	        array($this, 'inner_disable_ads'),
	     	'post',
			'normal',
			'high'
	    );

		add_meta_box(
	        'aswp_disable_ads',
	        __('Disable Ads', ASWP_UNIQUE_NAME),
	        array($this, 'inner_disable_ads'),
	     	'page',
			'normal',
			'high'
	    );
	}

	/**
	 * Outputs the HTML code for the meta box to disable ads.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function inner_disable_ads()
	{
		global $post;

		$post_id = 0;
		if (isset($post->ID) == TRUE)
		{
			$post_id = $post->ID;
		}

		$checkbox_checked = '';
		if (get_post_meta($post_id, 'aswp_disable_ads', TRUE) === '1')
		{
			$checkbox_checked = ' checked="checked"';
		}

		// Use nonce for verification
  		wp_nonce_field(ASWP_BASENAME, 'aswp_noncename');

  		echo '<label for="aswp_disable_ads">'.__('Check to disable ads on this page', ASWP_UNIQUE_NAME).'</label>';
  		echo ' <input type="checkbox" id="aswp_disable_ads" name="aswp_disable_ads" value="1"'.$checkbox_checked.'/>';
	}

	/**
	 * Function that saves the custom meta box.
	 *
	 * @since	1.0
	 * @param	integer	$post_id
	 * @return	void
	 *
	 */
	function save_disable_ads_mb($post_id)
	{
		// verify if this is an auto save routine.
	  	// If it is our form has not been submitted, so we dont want to do anything
	  	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	      	return;

  		// verify this came from the our screen and with proper authorization,
  		// because save_post can be triggered at other times
  		if (isset($_POST['aswp_noncename']) == FALSE || wp_verify_nonce($_POST['aswp_noncename'], ASWP_BASENAME) == FALSE)
      		return;


  		// Check permissions
  		if (isset($_POST['post_type']) == TRUE && $_POST['post_type'] == 'page')
  		{
    		if (current_user_can('edit_page', $post_id) == FALSE)
        		return;
  		}
  		else
  		{
    		if (current_user_can('edit_post', $post_id) == FALSE)
        		return;
  		}

		if (isset($_POST['aswp_disable_ads']) == TRUE && $_POST['aswp_disable_ads'] === '1')
		{
			update_post_meta($post_id, 'aswp_disable_ads', '1');
		}
		else
		{
			update_post_meta($post_id, 'aswp_disable_ads', '0');
		}
	}

	/**
	 * Initialize
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function init()
	{
		//	Load language
 		load_plugin_textdomain(ASWP_UNIQUE_NAME, FALSE, ASWP_BASENAME_FOLDER.'/lang');
	}

	/**
	 * Adds new button to tinyMCE
	 *
	 * @since	1.1
	 * @param	array	$buttons
	 * @return	array
	 *
	 */
	function filter_mce_button($buttons)
	{
		//	We add a separator (|) before our button
		array_push($buttons, '|', 'aswp_ads_shortcode_button');

		return $buttons;
	}

	/**
	 *
	 *
	 * @since	1.1
	 * @param	array	$plugins
	 * @return	array
	 *
	 */
	function filter_mce_plugin($plugins)
	{
		$plugins['aswp_ads_shortcode'] = ASWP_PLUGIN_BASE_URL.'js/ads_tinymce_shortcode.js';

		return $plugins;
	}

	/**
	 * Ajax callback for admin
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function ajax_callback()
	{
		//	Get mode
		$mode = isset($_POST['mode']) == TRUE ? $_POST['mode'] : '';

		if ($_POST['mode'] != 'load_shortcodes')
		{
			if (isset($_POST['security']) == TRUE)
			{
				check_ajax_referer('aswp_check_nonce', 'security');
			}
			else
			{
				die();
			}
		}

		//	Loads shortcodes that are shown on the edit page
		if ($mode == 'load_shortcodes')
		{
			$output_load_shortcodes = '<p>'.__('Oops, error!', ASWP_UNIQUE_NAME).'</p>';

			//	Get all ads
			$list_of_ads = $this->wpdb->get_results("
				SELECT *
				FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
				ORDER BY `date_created` DESC
			", ARRAY_A);

			//	If any ads found
			if (count($list_of_ads) > 0)
			{
				$output_load_shortcodes = '';

				//	Loop over all the ads
				foreach ($list_of_ads as $value)
				{
					if ($value['ad_type'] === '1')
					{
						$ad_type = esc_html(__('Text', ASWP_UNIQUE_NAME));
					}
					else if ($value['ad_type'] === '2')
					{
						$ad_type = esc_html(__('Image', ASWP_UNIQUE_NAME));
					}
					else if ($value['ad_type'] === '3')
					{
						$ad_type = esc_html(__('Text & Image', ASWP_UNIQUE_NAME));
					}

					//	Get the ad design fields
					$ad_design_cont = unserialize($value['ad_design']);

					$ad_formats = array();

					if (isset($ad_design_cont['aswp_format_1']) == TRUE && $ad_design_cont['aswp_format_1'] !== '')
					{
						$ad_formats[] = '120 x 600';
					}
					if (isset($ad_design_cont['aswp_format_2']) == TRUE && $ad_design_cont['aswp_format_2'] !== '')
					{
						$ad_formats[] = '120 x 240';
					}
					if (isset($ad_design_cont['aswp_format_3']) == TRUE && $ad_design_cont['aswp_format_3'] !== '')
					{
						$ad_formats[] = '125 x 125';
					}
					if (isset($ad_design_cont['aswp_format_4']) == TRUE && $ad_design_cont['aswp_format_4'] !== '')
					{
						$ad_formats[] = '160 x 600';
					}
					if (isset($ad_design_cont['aswp_format_5']) == TRUE && $ad_design_cont['aswp_format_5'] !== '')
					{
						$ad_formats[] = '180 x 150';
					}
					if (isset($ad_design_cont['aswp_format_6']) == TRUE && $ad_design_cont['aswp_format_6'] !== '')
					{
						$ad_formats[] = '200 x 200';
					}
					if (isset($ad_design_cont['aswp_format_7']) == TRUE && $ad_design_cont['aswp_format_7'] !== '')
					{
						$ad_formats[] = '234 x 60';
					}
					if (isset($ad_design_cont['aswp_format_8']) == TRUE && $ad_design_cont['aswp_format_8'] !== '')
					{
						$ad_formats[] = '250 x 250';
					}
					if (isset($ad_design_cont['aswp_format_9']) == TRUE && $ad_design_cont['aswp_format_9'] !== '')
					{
						$ad_formats[] = '300 x 250';
					}
					if (isset($ad_design_cont['aswp_format_10']) == TRUE && $ad_design_cont['aswp_format_10'] !== '')
					{
						$ad_formats[] = '336 x 280';
					}
					if (isset($ad_design_cont['aswp_format_11']) == TRUE && $ad_design_cont['aswp_format_11'] !== '')
					{
						$ad_formats[] = '468 x 60';
					}
					if (isset($ad_design_cont['aswp_format_12']) == TRUE && $ad_design_cont['aswp_format_12'] !== '')
					{
						$ad_formats[] = '728 x 90';
					}

					$ad_formats_output = implode(', ', $ad_formats);

					$output_load_shortcodes .= '
<div id="aswp_shortcode_'.$value['id'].'" style="border-bottom: 1px solid #cccccc; overflow: hidden; width: 340px; padding: 10px;">
	<div style="float: left; width: 200px;"><strong>'.esc_html($value['ad_name']).'</strong><br /><span style="font-size: 10px;"><a href="#" class="aswp_more_details" id="aswp_ad_details_'.$value['id'].'">'.esc_html(__('Ad Details', ASWP_UNIQUE_NAME)).'</a></span></div>
	<div style="float: right; width: 140px; text-align: right;"><a href="#" class="aswp_insert_ad" id="aswp_insert_id_'.$value['id'].'">'.esc_html(__('Insert Ad', ASWP_UNIQUE_NAME)).'</a></div>
	<div id="aswp_details_cont_'.$value['id'].'" style="clear: both; width: 350px; display: none;">
		<span><strong>'.esc_html(__('Ad Type', ASWP_UNIQUE_NAME)).':</strong> '.$ad_type.'</span>
		<br />
		<span><strong>'.esc_html(__('Ad Formats', ASWP_UNIQUE_NAME)).':</strong> '.$ad_formats_output.'</span>
	</div>
</div>';
				}
			}
			else
			{
				$output_load_shortcodes = '<p>'.__('No ads found.', ASWP_UNIQUE_NAME).'</p>';
			}

			echo $output_load_shortcodes;
		}

		die();
	}

	/**
	 * Check if user already registered.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	boolean/string
	 *
	 */
	function check_registration()
	{
		//	Load options
		$options = get_option('aswp_options');

		if (isset($options['registration']['email']) == FALSE || isset($options['registration']['name']) == FALSE || isset($options['registration']['status']) == FALSE)
		{
			return FALSE;
		}
		else if ($options['registration']['status'] == 'confirm')
		{
			return 'confirm';
		}

		return TRUE;
	}

	/**
	 * Output the registration form.
	 *
	 * @since	1.1
	 * @param	string	$page_url
	 * @return	string
	 *
	 */
	function registration_form($page_url)
	{
		//	Check if form was submitted
		if (isset($_POST['aswp_registration_saved']) == TRUE && $_POST['aswp_registration_saved'] == 'yes')
		{
			$register_form_result = aswp_core::register_form_submitted($_POST);

			if ($register_form_result === TRUE)
			{
				return TRUE;
			}

			echo aswp_html::admin_notice($register_form_result['status'], $register_form_result['message']);
		}

		//	Load options
		$options = get_option('aswp_options');

		echo aswp_html::wrap_header();

?>
	<h2><?php _e('Registration', ASWP_UNIQUE_NAME); ?></h2>
<?php
		echo aswp_html::show_banner();

		if (isset($options['registration']['status']) == FALSE || $options['registration']['status'] === '')
		{
			//	Output the start of the post box
			echo aswp_html::insert_post_box_start('aswp_registration_help', __('Registration Help', ASWP_UNIQUE_NAME), 'margin-right: 0;');

			//	Output the content meta box
			echo aswp_html::meta_box_insert_content(array(
				__('This plugin requires you to register.', ASWP_UNIQUE_NAME),
				__('You are required to enter your name and valid email.', ASWP_UNIQUE_NAME),
				sprintf(__('Your Name and Email address will be sent to AdSenseWPPlugin.com and then the data will be sent to our Aweber subscription list. You can view the terms at our Privacy Policy Page here: %1$shttp://www.adsensewpplugin.com/privacy%2$s and the Aweber Privacy Policy here: %3$shttp://www.aweber.com/privacy.htm%4$s.', ASWP_UNIQUE_NAME), '<a href="http://www.adsensewpplugin.com/privacy">', '</a>', '<a href="http://www.aweber.com/privacy.htm">', '</a>'),
				__('Note: If you have already registered, simply enter your name and email again and the plugin will be activated right away. Your email will not be added to the AWeber subscription list again.', ASWP_UNIQUE_NAME),
			));

			//	Output the end of the post box
			echo aswp_html::insert_post_box_end();

			//	Output the start of the form
			echo aswp_html::form_start('post', admin_url().'admin.php?page='.$page_url, array(
				array('full_input' => wp_nonce_field('aswp_registration', '_wpnonce', TRUE, FALSE)),
				array('name' => 'aswp_registration_saved', 'value' => 'yes')
			));

			//	Output the start of the table
			echo aswp_html::table_start();

			//	Output the input field
			echo aswp_html::tr_row_input(__('Your Name', ASWP_UNIQUE_NAME), 'aswp_register_name', '', '');

			//	Output the input field
			echo aswp_html::tr_row_input(__('Your Email', ASWP_UNIQUE_NAME), 'aswp_register_email', '', '');

			//	Output the end of the table
			echo aswp_html::table_end();

			//	Output the save button
			echo aswp_html::blue_button(__('Submit', ASWP_UNIQUE_NAME));

			//	Output the end of the form
			echo aswp_html::form_end();
		}
		else if (isset($options['registration']['status']) == TRUE && $options['registration']['status'] == 'confirm')
		{
			//	Output the start of the post box
			echo aswp_html::insert_post_box_start('aswp_registration_help', __('Registration Help', ASWP_UNIQUE_NAME), 'margin-right: 0;');

			//	Output the content meta box
			echo aswp_html::meta_box_insert_content(array(
				__('Please check your email and confirm the subscription.', ASWP_UNIQUE_NAME),
				__('Once you confirm the subscription, click on the Activate Plugin button to finish the registration.', ASWP_UNIQUE_NAME),
			));

			//	Output the end of the post box
			echo aswp_html::insert_post_box_end();

			//	Output the start of the form
			echo aswp_html::form_start('post', admin_url().'admin.php?page='.$page_url, array(
				array('full_input' => wp_nonce_field('aswp_registration', '_wpnonce', TRUE, FALSE)),
				array('name' => 'aswp_registration_saved', 'value' => 'yes')
			));

			//	Output the save button
			echo aswp_html::blue_button(__('Activate Plugin', ASWP_UNIQUE_NAME), 'submit', '<p class="submit">', '');
			echo aswp_html::secondary_button(__('Reset Name & Email', ASWP_UNIQUE_NAME), 'aswp_reset_registration', ' ', '</p>');

			//	Output the end of the form
			echo aswp_html::form_end();
		}

		echo aswp_html::wrap_footer();
	}

	/**
	 * Check submitted registration form.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function register_form_submitted()
	{
		//	Check nonce first
		if (wp_verify_nonce($_POST['_wpnonce'], 'aswp_registration') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Load options
		$options = get_option('aswp_options');

		//	Reset the registration data
		if (isset($_POST['aswp_reset_registration']) == TRUE)
		{
			$options['registration']['email'] = '';
			$options['registration']['name'] = '';
			$options['registration']['status'] = '';

			update_option('aswp_options', $options);

			return array('status' => '', 'message' => '');
		}

		if (isset($options['registration']['status']) == FALSE || $options['registration']['status'] === '')
		{
			//	Check name field
			$name = isset($_POST['aswp_register_name']) == TRUE ? trim($_POST['aswp_register_name']) : FALSE;

			if ($name == FALSE)
			{
				return array('status' => 'error', 'message' => __('Please enter your name.', ASWP_UNIQUE_NAME));
			}

			//	Check email field
			$email = isset($_POST['aswp_register_email']) == TRUE ? trim($_POST['aswp_register_email']) : FALSE;

			if ($email == FALSE || preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $email) == FALSE)
			{
				return array('status' => 'error', 'message' => __('Please enter valid email address.', ASWP_UNIQUE_NAME));
			}

			$submit_data = array('email' => $email, 'name' => $name);
		}
		else if (isset($options['registration']['status']) == TRUE && $options['registration']['status'] == 'confirm')
		{
			$submit_data = array('email' => $options['registration']['email'], 'name' => $options['registration']['name']);
		}

		//	If all good, send data to aweber
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://adsensewpplugin.com/aweber_api/check_aweber.php');
		curl_setopt($ch, CURLOPT_USERAGENT, "AdSense Wordpress Plugin");
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $submit_data);

		$output = curl_exec($ch);

		curl_close($ch);

		$aweber_respond = json_decode($output, TRUE);

		//	Show the confirm page
		if (isset($aweber_respond['status']) == TRUE && $aweber_respond['status'] == 'confirm')
		{
			$options['registration']['email'] = $submit_data['email'];
			$options['registration']['name'] = $submit_data['name'];
			$options['registration']['status'] = 'confirm';

			update_option('aswp_options', $options);

			return array('status' => 'updated', 'message' => __('Please check your email and confirm the subscription.', ASWP_UNIQUE_NAME));
		}

		//	All is good! Registration completed!
		if (isset($aweber_respond['status']) == TRUE && $aweber_respond['status'] == 'success')
		{
			$options['registration']['email'] = $submit_data['email'];
			$options['registration']['name'] = $submit_data['name'];
			$options['registration']['status'] = 'success';

			update_option('aswp_options', $options);

			return TRUE;
		}
	}

	/**
	 *	When plugin is loaded do some stuff. Mostly table upgrades..
	 *
	 * @since	1.2.2
	 * @param	void
	 * @return	void
	 *
	 */
	function plugin_loaded()
	{
		global $wpdb;

		//	Load options
		$options = get_option('aswp_options');

		//	Check current table version
		$current_table_version = (int)isset($options['current_table_version']) == TRUE ? $options['current_table_version'] : '0';

		if ($current_table_version < 1)
		{
			$sql = "ALTER TABLE  `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
					ADD `ad_advance` TEXT NOT NULL AFTER `ad_design`";
            $wpdb->query($sql);

			$options['current_table_version'] = ASWP_CURRENT_TABLE_VERSION;
			update_option('aswp_options', $options);
		}
	}
}
?>