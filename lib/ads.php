<?php
/**
 *	Contains functions for the ads page in the WP admin area.
 *
 */

class aswp_ads
{
	/**
	 * Constructor
	 *
	 * @since	1.0
	 *
	 */
	function __construct($wpdb)
	{
		$this->wpdb = $wpdb;
	}

	/**
	 * Display the main page for ads.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function display_main()
	{
		//	Load options
		$options = get_option('aswp_options');

		$check_registration_status = aswp_core::check_registration();

		if ($check_registration_status === FALSE || $check_registration_status === 'confirm')
		{
			if (aswp_core::registration_form('aswp_ads') !== TRUE)
			{
				return FALSE;
			}
		}

		$editing_ad = FALSE;

		//	Check if user is trying to edit an ad
		if (isset($_GET['action']) == TRUE && $_GET['action'] == 'edit' && isset($_GET['ad']) == TRUE)
		{
			$ad_exist_result = $this->ad_id_exist($_GET['ad']);

			//	Check if ad even exist.
			if ($ad_exist_result == FALSE)
			{
				echo aswp_html::admin_notice('error', __('Selected ad doesn\'t exist!', ASWP_UNIQUE_NAME));
				die();
			}
			else
			{
				//	Display the edit page.
				$this->display_edit($ad_exist_result);
				$editing_ad = TRUE;
			}
		}

		if ($editing_ad == FALSE)
		{
			echo aswp_html::wrap_header();

			//	Check if user clicked on delete button.
			if (isset($_GET['action']) == TRUE && $_GET['action'] == 'delete' && isset($_GET['ad']) == TRUE)
			{
				//	Delete the selected ad
				if ($this->delete_ad($_GET['ad']) == TRUE)
				{
					echo aswp_html::admin_notice('updated', __('Selected ad was successfully deleted.', ASWP_UNIQUE_NAME));
				}
			}

			//	Check if we have to delete one or more ads
			if (isset($_POST['aswp_delete_ads']) == TRUE && $_POST['aswp_delete_ads'] == 'yes' && isset($_POST['ad']) == TRUE && count($_POST['ad']) > 0)
			{
				//	Delete the selected ads
				if ($this->delete_ads($_POST['ad']) == TRUE)
				{
					echo aswp_html::admin_notice('updated', __('Selected ads were successfully deleted.', ASWP_UNIQUE_NAME));
				}
			}

			//	Check if user has entered adsense pub id
			if (isset($options['options']['aswp_publisher_id']) == FALSE || $options['options']['aswp_publisher_id'] === '')
			{
				echo aswp_html::admin_notice('error', __('Please enter your AdSense Publisher ID on the', ASWP_UNIQUE_NAME).' <a href="'.admin_url().'admin.php?page=aswp_options">'.__('options page', ASWP_UNIQUE_NAME).'</a>');
			}
?>
	<h2><?php _e('Ads', ASWP_UNIQUE_NAME); ?> <a href="<?php echo admin_url(); ?>admin.php?page=aswp_create_new_ad" class="add-new-h2"><?php _e('Create New Ad', ASWP_UNIQUE_NAME); ?></a></h2>
<?php
			echo aswp_html::show_banner();

			//	Output the start of the form
			echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_ads', array(
				array('full_input' => wp_nonce_field('aswp_delete_ads', '_wpnonce', TRUE, FALSE)),
				array('name' => 'aswp_delete_ads', 'value' => 'yes')
			));

			//	Output the top table options bar
			echo aswp_html::list_table_option();

			//	Outputs the titles
			echo aswp_html::list_table_start(array(
				array(
					'type' => 'input',
					'field_id' => 'aswp_cb',
				),
				array(
					'type' => 'text',
					'field_id' => 'aswp_ad_name',
					'title' => __('Ad Name', ASWP_UNIQUE_NAME),
				),
				array(
					'type' => 'text',
					'field_id' => 'aswp_ad_shortcode',
					'title' => __('Ad Shortcode', ASWP_UNIQUE_NAME),
				),
				array(
					'type' => 'text',
					'field_id' => 'aswp_ad_type',
					'title' => __('Ad Type', ASWP_UNIQUE_NAME),
				),
				array(
					'type' => 'text',
					'field_id' => 'aswp_ad_formats',
					'title' => __('Ad Format(s)', ASWP_UNIQUE_NAME),
				),
				array(
					'type' => 'text',
					'field_id' => 'aswp_ad_date_created',
					'title' => __('Date Created', ASWP_UNIQUE_NAME),
				)
			));

			//	Get all ads
			$list_of_ads = $this->wpdb->get_results("
				SELECT *
				FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
				ORDER BY `date_created` DESC
			", ARRAY_A);

			if (count($list_of_ads) > 0)
			{
				//	Loop over the ads
				foreach ($list_of_ads as $value)
				{
					//	Check which type of ad we have here
					if ($value['ad_type'] === '1')
					{
						$value['ad_type'] = __('Text', ASWP_UNIQUE_NAME);
					}
					else if ($value['ad_type'] === '2')
					{
						$value['ad_type'] = __('Image', ASWP_UNIQUE_NAME);
					}
					else if ($value['ad_type'] === '3')
					{
						$value['ad_type'] = __('Text & Image', ASWP_UNIQUE_NAME);
					}

					//	Get the ad design fields
					$ad_design_cont = unserialize($value['ad_design']);

					$ad_formats = array();

					//	Get ad formats
					if (isset($ad_design_cont['aswp_format_1']) == TRUE && $ad_design_cont['aswp_format_1'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_1']);
					}
					if (isset($ad_design_cont['aswp_format_2']) == TRUE && $ad_design_cont['aswp_format_2'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_2']);
					}
					if (isset($ad_design_cont['aswp_format_3']) == TRUE && $ad_design_cont['aswp_format_3'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_3']);
					}
					if (isset($ad_design_cont['aswp_format_4']) == TRUE && $ad_design_cont['aswp_format_4'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_4']);
					}
					if (isset($ad_design_cont['aswp_format_5']) == TRUE && $ad_design_cont['aswp_format_5'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_5']);
					}
					if (isset($ad_design_cont['aswp_format_6']) == TRUE && $ad_design_cont['aswp_format_6'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_6']);
					}
					if (isset($ad_design_cont['aswp_format_7']) == TRUE && $ad_design_cont['aswp_format_7'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_7']);
					}
					if (isset($ad_design_cont['aswp_format_8']) == TRUE && $ad_design_cont['aswp_format_8'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_8']);
					}
					if (isset($ad_design_cont['aswp_format_9']) == TRUE && $ad_design_cont['aswp_format_9'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_9']);
					}
					if (isset($ad_design_cont['aswp_format_10']) == TRUE && $ad_design_cont['aswp_format_10'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_10']);
					}
					if (isset($ad_design_cont['aswp_format_11']) == TRUE && $ad_design_cont['aswp_format_11'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_11']);
					}
					if (isset($ad_design_cont['aswp_format_12']) == TRUE && $ad_design_cont['aswp_format_12'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_12']);
					}
					if (isset($ad_design_cont['aswp_format_13']) == TRUE && $ad_design_cont['aswp_format_13'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_13']);
					}
					if (isset($ad_design_cont['aswp_format_14']) == TRUE && $ad_design_cont['aswp_format_14'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_14']);
					}
					if (isset($ad_design_cont['aswp_format_15']) == TRUE && $ad_design_cont['aswp_format_15'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_15']);
					}
					if (isset($ad_design_cont['aswp_format_16']) == TRUE && $ad_design_cont['aswp_format_16'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_16']);
					}
					if (isset($ad_design_cont['aswp_format_17']) == TRUE && $ad_design_cont['aswp_format_17'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_17']);
					}
					if (isset($ad_design_cont['aswp_format_18']) == TRUE && $ad_design_cont['aswp_format_18'] !== '')
					{
						$ad_formats[] = str_replace('_', ' x ', $ad_design_cont['aswp_format_18']);
					}

					//	Output the row html
					echo '
					<tr id="aswp_entry_id_'.$value['id'].'" class="post" valign="top">
						<th scope="row" class="check-column"><input type="checkbox" name="ad[]" value="'.$value['id'].'"></th>
						<td class="post-title page-title column-title">
							<strong><a class="row-title" href="'.admin_url().'admin.php?page=aswp_ads&amp;action=edit&amp;ad='.$value['id'].'" title="'.__('Edit', ASWP_UNIQUE_NAME).'">'.esc_attr($value['ad_name']).'</a></strong>
							<div class="row-actions">
								<span class="edit"><a href="'.admin_url().'admin.php?page=aswp_ads&amp;action=edit&amp;ad='.$value['id'].'" title="'.__('Edit', ASWP_UNIQUE_NAME).'">'.__('Edit', ASWP_UNIQUE_NAME).'</a> | </span>
								<span class="trash"><a class="submitdelete" title="'.__('Delete this ad', ASWP_UNIQUE_NAME).'" href="'.wp_nonce_url(admin_url().'admin.php?page=aswp_ads&amp;action=delete&amp;ad='.$value['id'], 'aswp_delete_ad').'">'.__('Delete', ASWP_UNIQUE_NAME).'</a></span>
							</div>
						</td>
						<td class="post-title page-title column-title">[aswp id="'.$value['id'].'"]</td>
						<td class="post-title page-title column-title">'.$value['ad_type'].'</td>
						<td class="post-title page-title column-title">'.implode(', ', $ad_formats).'</td>
						<td class="post-title page-title column-title">
							'.esc_attr(date(get_option('date_format'), strtotime($value['date_created']))).' - '.esc_attr(date(get_option('time_format'), strtotime($value['date_created']))).'
						</td>
					</tr>';
				}
			}
			else
			{
				echo '<tr><td colspan="5">'.__('No ads found.', ASWP_UNIQUE_NAME).'</td></tr>';
			}

			//	Outputs the closing tabs for table
			echo aswp_html::list_table_end();

			//	Output the bottom table options bar
			echo aswp_html::list_table_option();

			//	Output the end of the form
			echo aswp_html::form_end();

			echo aswp_html::wrap_footer();
		}
	}

	/**
	 * Delete single ad.
	 *
	 * @since	1.0
	 * @param	integer	$ad_id
	 * @return	boolean
	 *
	 */
	function delete_ad($ad_id)
	{
		//	Check nonce first
		$nonce = isset($_GET['_wpnonce']) == TRUE ? $_GET['_wpnonce'] : '';
		if (wp_verify_nonce($nonce, 'aswp_delete_ad') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		$result = $this->wpdb->query($this->wpdb->prepare("
			DELETE FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
			WHERE `id` = %d
			", $ad_id));

		if ($result !== 0 && $result !== FALSE)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Delete multiple ads
	 *
	 * @since	1.0
	 * @param	integer	$ad_ids
	 * @return	boolean
	 *
	 */
	function delete_ads($ad_ids)
	{
		//	Check nonce first
		$nonce = isset($_POST['_wpnonce']) == TRUE ? $_POST['_wpnonce'] : '';
		if (wp_verify_nonce($nonce, 'aswp_delete_ads') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		if (count($ad_ids) > 0)
		{
			foreach ($ad_ids as $value)
			{
				$result = $this->wpdb->query($this->wpdb->prepare("
					DELETE FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
					WHERE `id` = %d
					", $value));
			}

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Check if ID of the ad that the user is trying to edit really exist
	 *
	 * @since	1.0
	 * @param	integer	$ad_id
	 * @return	boolean
	 *
	 */
	function ad_id_exist($ad_id)
	{
		$result = $this->wpdb->get_row($this->wpdb->prepare("
			SELECT * FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
			WHERE `id` = %d", $ad_id), ARRAY_A);

		if ($result !== null)
		{
			return $result;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Shows the edit ad page.
	 *
	 * @since	1.0
	 * @param	array	$record_data
	 * @return	void
	 *
	 */
	function display_edit($record_data)
	{
		$check_registration_status = aswp_core::check_registration();

		if ($check_registration_status === FALSE || $check_registration_status === 'confirm')
		{
			if (aswp_core::registration_form('aswp_ads') !== TRUE)
			{
				return FALSE;
			}
		}

		//	Check if form was submitted
		if (isset($_POST['aswp_edit_ad_saved']) == TRUE && $_POST['aswp_edit_ad_saved'] == 'yes')
		{
			$save_ad_result = $this->save_ad($_POST, $record_data['id']);

			if ($save_ad_result !== TRUE)
			{
				echo aswp_html::admin_notice($save_ad_result['message'][0]['type'], $save_ad_result['message'][0]['message']);
			}
		}

		//	Unserialize placement and design data
		$record_data_placement = unserialize($record_data['ad_placement']);
		$record_data_design = unserialize($record_data['ad_design']);
		$record_data_advance = unserialize($record_data['ad_advance']);

		//	Load options
		$options = get_option('aswp_options');

		$options_reward_author = isset($options['reward_author']) == TRUE ? $options['reward_author'] : '0';

		//	Default values for fields
		$default_values = array(
			'aswp_ad_name' => isset($save_ad_result['fields']['aswp_ad_name']) == TRUE ? esc_attr($save_ad_result['fields']['aswp_ad_name']) : esc_attr($record_data['ad_name']),
			'aswp_ad_type' => isset($save_ad_result['fields']['aswp_ad_type']) == TRUE ? esc_attr($save_ad_result['fields']['aswp_ad_type']) : esc_attr($record_data['ad_type']),

			'aswp_ad_per_page' => isset($save_ad_result['fields']['ad_placement']['aswp_ad_per_page']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_ad_per_page']) : esc_attr($record_data_placement['aswp_ad_per_page']),
			'aswp_ad_per_post' => isset($save_ad_result['fields']['ad_placement']['aswp_ad_per_post']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_ad_per_post']) : esc_attr($record_data_placement['aswp_ad_per_post']),
			'aswp_show_ad_on_home' => isset($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_home']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_home']) : esc_attr($record_data_placement['aswp_show_ad_on_home']),
			'aswp_show_ad_on_category' => isset($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_category']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_category']) : esc_attr($record_data_placement['aswp_show_ad_on_category']),
			'aswp_show_ad_on_archive' => isset($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_archive']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_show_ad_on_archive']) : esc_attr($record_data_placement['aswp_show_ad_on_archive']),
			'aswp_top_left' => isset($save_ad_result['fields']['ad_placement']['aswp_top_left']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_top_left']) : esc_attr($record_data_placement['aswp_top_left']),
			'aswp_top_center' => isset($save_ad_result['fields']['ad_placement']['aswp_top_center']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_top_center']) : esc_attr($record_data_placement['aswp_top_center']),
			'aswp_top_right' => isset($save_ad_result['fields']['ad_placement']['aswp_top_right']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_top_right']) : esc_attr($record_data_placement['aswp_top_right']),
			'aswp_middle_left' => isset($save_ad_result['fields']['ad_placement']['aswp_middle_left']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_middle_left']) : esc_attr($record_data_placement['aswp_middle_left']),
			'aswp_middle_center' => isset($save_ad_result['fields']['ad_placement']['aswp_middle_center']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_middle_center']) : esc_attr($record_data_placement['aswp_middle_center']),
			'aswp_middle_right' => isset($save_ad_result['fields']['ad_placement']['aswp_middle_right']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_middle_right']) : esc_attr($record_data_placement['aswp_middle_right']),
			'aswp_bottom_left' => isset($save_ad_result['fields']['ad_placement']['aswp_bottom_left']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_bottom_left']) : esc_attr($record_data_placement['aswp_bottom_left']),
			'aswp_bottom_center' => isset($save_ad_result['fields']['ad_placement']['aswp_bottom_center']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_bottom_center']) : esc_attr($record_data_placement['aswp_bottom_center']),
			'aswp_bottom_right' => isset($save_ad_result['fields']['ad_placement']['aswp_bottom_right']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_bottom_right']) : esc_attr($record_data_placement['aswp_bottom_right']),
			'aswp_margin_top' => isset($save_ad_result['fields']['ad_placement']['aswp_margin_top']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_margin_top']) : esc_attr($record_data_placement['aswp_margin_top']),
			'aswp_margin_right' => isset($save_ad_result['fields']['ad_placement']['aswp_margin_right']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_margin_right']) : esc_attr($record_data_placement['aswp_margin_right']),
			'aswp_margin_bottom' => isset($save_ad_result['fields']['ad_placement']['aswp_margin_bottom']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_margin_bottom']) : esc_attr($record_data_placement['aswp_margin_bottom']),
			'aswp_margin_left' => isset($save_ad_result['fields']['ad_placement']['aswp_margin_left']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_margin_left']) : esc_attr($record_data_placement['aswp_margin_left']),
			'aswp_placement_type' => isset($save_ad_result['fields']['ad_placement']['aswp_placement_type']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_placement_type']) : esc_attr($record_data_placement['aswp_placement_type']),
			'aswp_insert_after_n_paragraph' => isset($save_ad_result['fields']['ad_placement']['aswp_insert_after_n_paragraph']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_insert_after_n_paragraph']) : esc_attr($record_data_placement['aswp_insert_after_n_paragraph']),
			'aswp_paragraph_position_left' => isset($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_left']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_left']) : esc_attr($record_data_placement['aswp_paragraph_position_left']),
			'aswp_paragraph_position_center' => isset($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_center']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_center']) : esc_attr($record_data_placement['aswp_paragraph_position_center']),
			'aswp_paragraph_position_right' => isset($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_right']) == TRUE ? esc_attr($save_ad_result['fields']['ad_placement']['aswp_paragraph_position_right']) : esc_attr($record_data_placement['aswp_paragraph_position_right']),


			'aswp_format_1' => isset($save_ad_result['fields']['ad_design']['aswp_format_1']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_1']) : esc_attr($record_data_design['aswp_format_1']),
			'aswp_format_2' => isset($save_ad_result['fields']['ad_design']['aswp_format_2']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_2']) : esc_attr($record_data_design['aswp_format_2']),
			'aswp_format_3' => isset($save_ad_result['fields']['ad_design']['aswp_format_3']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_3']) : esc_attr($record_data_design['aswp_format_3']),
			'aswp_format_4' => isset($save_ad_result['fields']['ad_design']['aswp_format_4']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_4']) : esc_attr($record_data_design['aswp_format_4']),
			'aswp_format_5' => isset($save_ad_result['fields']['ad_design']['aswp_format_5']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_5']) : esc_attr($record_data_design['aswp_format_5']),
			'aswp_format_6' => isset($save_ad_result['fields']['ad_design']['aswp_format_6']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_6']) : esc_attr($record_data_design['aswp_format_6']),
			'aswp_format_7' => isset($save_ad_result['fields']['ad_design']['aswp_format_7']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_7']) : esc_attr($record_data_design['aswp_format_7']),
			'aswp_format_8' => isset($save_ad_result['fields']['ad_design']['aswp_format_8']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_8']) : esc_attr($record_data_design['aswp_format_8']),
			'aswp_format_9' => isset($save_ad_result['fields']['ad_design']['aswp_format_9']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_9']) : esc_attr($record_data_design['aswp_format_9']),
			'aswp_format_10' => isset($save_ad_result['fields']['ad_design']['aswp_format_10']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_10']) : esc_attr($record_data_design['aswp_format_10']),
			'aswp_format_11' => isset($save_ad_result['fields']['ad_design']['aswp_format_11']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_11']) : esc_attr($record_data_design['aswp_format_11']),
			'aswp_format_12' => isset($save_ad_result['fields']['ad_design']['aswp_format_12']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_12']) : esc_attr($record_data_design['aswp_format_12']),
			'aswp_format_13' => isset($save_ad_result['fields']['ad_design']['aswp_format_13']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_13']) : isset($record_data_design['aswp_format_13']) == TRUE ? esc_attr($record_data_design['aswp_format_13']) : '',
			'aswp_format_14' => isset($save_ad_result['fields']['ad_design']['aswp_format_14']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_14']) : isset($record_data_design['aswp_format_14']) == TRUE ? esc_attr($record_data_design['aswp_format_14']) : '',
			'aswp_format_15' => isset($save_ad_result['fields']['ad_design']['aswp_format_15']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_15']) : isset($record_data_design['aswp_format_15']) == TRUE ? esc_attr($record_data_design['aswp_format_15']) : '',
			'aswp_format_16' => isset($save_ad_result['fields']['ad_design']['aswp_format_16']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_16']) : isset($record_data_design['aswp_format_16']) == TRUE ? esc_attr($record_data_design['aswp_format_16']) : '',
			'aswp_format_17' => isset($save_ad_result['fields']['ad_design']['aswp_format_17']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_17']) : isset($record_data_design['aswp_format_17']) == TRUE ? esc_attr($record_data_design['aswp_format_17']) : '',
			'aswp_format_18' => isset($save_ad_result['fields']['ad_design']['aswp_format_18']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_format_18']) : isset($record_data_design['aswp_format_18']) == TRUE ? esc_attr($record_data_design['aswp_format_18']) : '',
			'aswp_ad_corner_style' => isset($save_ad_result['fields']['ad_design']['aswp_ad_corner_style']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_ad_corner_style']) : esc_attr($record_data_design['aswp_ad_corner_style']),
			'aswp_ad_font_family' => isset($save_ad_result['fields']['ad_design']['aswp_ad_font_family']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_ad_font_family']) : esc_attr($record_data_design['aswp_ad_font_family']),
			//'aswp_ad_font_size' => isset($save_ad_result['fields']['ad_design']['aswp_ad_font_size']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_ad_font_size']) : esc_attr($record_data_design['aswp_ad_font_size']),
			'aswp_border_color' => isset($save_ad_result['fields']['ad_design']['aswp_border_color']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_border_color']) : esc_attr($record_data_design['aswp_border_color']),
			'aswp_background_color' => isset($save_ad_result['fields']['ad_design']['aswp_background_color']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_background_color']) : esc_attr($record_data_design['aswp_background_color']),
			'aswp_title_color' => isset($save_ad_result['fields']['ad_design']['aswp_title_color']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_title_color']) : esc_attr($record_data_design['aswp_title_color']),
			'aswp_text_color' => isset($save_ad_result['fields']['ad_design']['aswp_text_color']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_text_color']) : esc_attr($record_data_design['aswp_text_color']),
			'aswp_url_color' => isset($save_ad_result['fields']['ad_design']['aswp_url_color']) == TRUE ? esc_attr($save_ad_result['fields']['ad_design']['aswp_url_color']) : esc_attr($record_data_design['aswp_url_color']),

			'aswp_adv_publisher_id' => isset($save_ad_result['fields']['ad_advance']['aswp_adv_publisher_id']) == TRUE ? esc_attr($save_ad_result['fields']['ad_advance']['aswp_adv_publisher_id']) : esc_attr($record_data_advance['aswp_adv_publisher_id']),
			'aswp_adv_custom_channel' => isset($save_ad_result['fields']['ad_advance']['aswp_adv_custom_channel']) == TRUE ? esc_attr($save_ad_result['fields']['ad_advance']['aswp_adv_custom_channel']) : esc_attr($record_data_advance['aswp_adv_custom_channel']),

			'aswp_donation' => isset($save_ad_result['fields']['ad_reward_author']) == TRUE ? esc_attr($save_ad_result['fields']['ad_reward_author']) : esc_attr($options_reward_author),
		);

		if (isset($_GET['noheader']) == TRUE)
            require_once(ABSPATH . 'wp-admin/admin-header.php');

		echo aswp_html::wrap_header();

		//	Check if user has entered adsense pub id
		if (isset($options['options']['aswp_publisher_id']) == FALSE || $options['options']['aswp_publisher_id'] === '')
		{
			echo aswp_html::admin_notice('error', __('Please enter your AdSense Publisher ID on the', ASWP_UNIQUE_NAME).' <a href="'.admin_url().'admin.php?page=aswp_options">'.__('options page', ASWP_UNIQUE_NAME).'</a>');
		}
?>
	<h2><?php _e('Edit Ad', ASWP_UNIQUE_NAME); ?></h2>
<?php
		echo aswp_html::show_banner();

		//	Output the start of the form
		echo aswp_html::form_start('post', admin_url().'admin.php?page=aswp_ads&noheader=true&action=edit&ad='.$record_data['id'], array(
			array('full_input' => wp_nonce_field('aswp_create_new_ad', '_wpnonce', TRUE, FALSE)),
			array('name' => 'aswp_edit_ad_saved', 'value' => 'yes')
		));

		//	Output the start of the table
		echo aswp_html::table_start('margin-bottom: 20px;');

		//	Output input field
		echo aswp_html::tr_row_input(__('Ad Name', ASWP_UNIQUE_NAME), 'aswp_ad_name', $default_values['aswp_ad_name'], __('For your eyes only.', ASWP_UNIQUE_NAME));

		//	Output select field
		echo aswp_html::tr_select_field(__('Ad Type', ASWP_UNIQUE_NAME), 'aswp_ad_type', array(
			array(
				'selected' => $default_values['aswp_ad_type'] === '1' ? 'yes' : '',
				'value' => '1',
				'text' => __('Text', ASWP_UNIQUE_NAME),
			),
			array(
				'selected' => $default_values['aswp_ad_type'] === '2' ? 'yes' : '',
				'value' => '2',
				'text' => __('Image', ASWP_UNIQUE_NAME),
			),
			array(
				'selected' => $default_values['aswp_ad_type'] === '3' ? 'yes' : '',
				'value' => '3',
				'text' => __('Text & Image', ASWP_UNIQUE_NAME),
			)
		));

		//	Output the end of the table
		echo aswp_html::table_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_reward_author', __('Reward Author', ASWP_UNIQUE_NAME));

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Donation<br /><span class="description">5 means 5% or exactly once per 20 page view, 0 will disable donations. Default is off.</span>', ASWP_UNIQUE_NAME), 'aswp_donation', $default_values['aswp_donation'], 'width: 30px;');

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_placement', __('Ad Placement', ASWP_UNIQUE_NAME));

		//	Output select field
		echo aswp_html::meta_box_select_field(__('Number of Ads per Page<br /><span class="description">Set to 0 to disable ads in pages.</span>', ASWP_UNIQUE_NAME), 'aswp_ad_per_page', array(
			array(
				'value' => '0',
				'selected' => $default_values['aswp_ad_per_page'] === '0' ? 'yes' : '',
				'text' => __('0', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '1',
				'selected' => $default_values['aswp_ad_per_page'] === '1' ? 'yes' : '',
				'text' => __('1', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '2',
				'selected' => $default_values['aswp_ad_per_page'] === '2' ? 'yes' : '',
				'text' => __('2', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '3',
				'selected' => $default_values['aswp_ad_per_page'] === '3' ? 'yes' : '',
				'text' => __('3', ASWP_UNIQUE_NAME),
			)
		));

		//	Output select field
		echo aswp_html::meta_box_select_field(__('Number of Ads per Post<br /><span class="description">Set to 0 to disable ads in posts.</span>', ASWP_UNIQUE_NAME), 'aswp_ad_per_post', array(
			array(
				'value' => '0',
				'selected' => $default_values['aswp_ad_per_post'] === '0' ? 'yes' : '',
				'text' => __('0', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '1',
				'selected' => $default_values['aswp_ad_per_post'] === '1' ? 'yes' : '',
				'text' => __('1', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '2',
				'selected' => $default_values['aswp_ad_per_post'] === '2' ? 'yes' : '',
				'text' => __('2', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '3',
				'selected' => $default_values['aswp_ad_per_post'] === '3' ? 'yes' : '',
				'text' => __('3', ASWP_UNIQUE_NAME),
			)
		));

		//	Output the checkboxes
		echo aswp_html::meta_box_checkbox_field(__('Show ads on selected pages', ASWP_UNIQUE_NAME), array(
			array(
				'checked' => $default_values['aswp_show_ad_on_home'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_ad_on_home',
				'value' => '1',
				'text' => __('Home', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_show_ad_on_category'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_ad_on_category',
				'value' => '1',
				'text' => __('Category', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_show_ad_on_archive'] === '1' ? 'yes' : '',
				'name' => 'aswp_show_ad_on_archive',
				'value' => '1',
				'text' => __('Archive', ASWP_UNIQUE_NAME),
			),
		));

		//	Output the placement image with checkboxes
		echo aswp_html::meta_box_placements(__('In Post Positions<br /><span class="description">If you check more positions then total "Number of Ads per",<br />ads will be randomly distributed between the checked positions.</span>', ASWP_UNIQUE_NAME),
			array(
				array(
					'checked' => $default_values['aswp_placement_type'] === '1' ? 'yes' : '',
					'name' => 'aswp_placement_type',
					'value' => '1',
				),
				array(
					'checked' => $default_values['aswp_placement_type'] === '2' ? 'yes' : '',
					'name' => 'aswp_placement_type',
					'value' => '2',
				)
			),
			__('Or', ASWP_UNIQUE_NAME),
			array(
				array(
					'checked' => $default_values['aswp_top_left'] === '1' ? 'yes' : '',
					'name' => 'aswp_top_left',
					'value' => '1',
					'margins_style' => 'top: 40px; left: 11px;'
				),
				array(
					'checked' => $default_values['aswp_top_center'] === '1' ? 'yes' : '',
					'name' => 'aswp_top_center',
					'value' => '1',
					'margins_style' => 'top: 40px; left: 59px;'
				),
				array(
					'checked' => $default_values['aswp_top_right'] === '1' ? 'yes' : '',
					'name' => 'aswp_top_right',
					'value' => '1',
					'margins_style' => 'top: 40px; left: 107px;'
				),
				array(
					'checked' => $default_values['aswp_middle_left'] === '1' ? 'yes' : '',
					'name' => 'aswp_middle_left',
					'value' => '1',
					'margins_style' => 'top: 117px; left: 11px;'
				),
				array(
					'checked' => $default_values['aswp_middle_center'] === '1' ? 'yes' : '',
					'name' => 'aswp_middle_center',
					'value' => '1',
					'margins_style' => 'top: 117px; left: 59px;'
				),
				array(
					'checked' => $default_values['aswp_middle_right'] === '1' ? 'yes' : '',
					'name' => 'aswp_middle_right',
					'value' => '1',
					'margins_style' => 'top: 117px; left: 107px;'
				),
				array(
					'checked' => $default_values['aswp_bottom_left'] === '1' ? 'yes' : '',
					'name' => 'aswp_bottom_left',
					'value' => '1',
					'margins_style' => 'top: 189px; left: 11px;'
				),
				array(
					'checked' => $default_values['aswp_bottom_center'] === '1' ? 'yes' : '',
					'name' => 'aswp_bottom_center',
					'value' => '1',
					'margins_style' => 'top: 189px; left: 59px;'
				),
				array(
					'checked' => $default_values['aswp_bottom_right'] === '1' ? 'yes' : '',
					'name' => 'aswp_bottom_right',
					'value' => '1',
					'margins_style' => 'top: 189px; left: 107px;'
				),
			),
			array(
				'pre_text' => __('Insert ad after', ASWP_UNIQUE_NAME),
				'after_text' => __('paragraph', ASWP_UNIQUE_NAME),
				'name' => 'aswp_insert_after_n_paragraph',
				'value' => $default_values['aswp_insert_after_n_paragraph']
			),
			array(
				'checked_left' => $default_values['aswp_paragraph_position_left'] === '1' ? 'yes' : '',
				'name_left' => 'aswp_paragraph_position_left',
				'value_left' => '1',
				'style_left' => 'top: 73px; left: 11px;',
				'checked_center' => $default_values['aswp_paragraph_position_center'] === '1' ? 'yes' : '',
				'name_center' => 'aswp_paragraph_position_center',
				'value_center' => '1',
				'style_center' => 'top: 73px; left: 59px;',
				'checked_right' => $default_values['aswp_paragraph_position_right'] === '1' ? 'yes' : '',
				'name_right' => 'aswp_paragraph_position_right',
				'value_right' => '1',
				'style_right' => 'top: 73px; left: 107px;'
			)
		);

		//	Output margins field
		echo aswp_html::meta_box_margins_field(__('Margins<br /><span class="description">Enter margins that will separate ad from content.</span>', ASWP_UNIQUE_NAME), array(
			array(
				'name' => 'aswp_margin_top',
				'field_title' => 'Top',
				'value' => $default_values['aswp_margin_top'] !== '0' && $default_values['aswp_margin_top'] !== '' ? $default_values['aswp_margin_top'] : '0'
			),
			array(
				'name' => 'aswp_margin_right',
				'field_title' => 'Right',
				'value' => $default_values['aswp_margin_right'] !== '0' && $default_values['aswp_margin_right'] !== '' ? $default_values['aswp_margin_right'] : '0'
			),
			array(
				'name' => 'aswp_margin_bottom',
				'field_title' => 'Bottom',
				'value' => $default_values['aswp_margin_bottom'] !== '0' && $default_values['aswp_margin_bottom'] !== '' ? $default_values['aswp_margin_bottom'] : '0'
			),
			array(
				'name' => 'aswp_margin_left',
				'field_title' => 'Left',
				'value' => $default_values['aswp_margin_left'] !== '0' && $default_values['aswp_margin_left'] !== '' ? $default_values['aswp_margin_left'] : '0'
			),
		));

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_design', __('Ad Design', ASWP_UNIQUE_NAME));

		//	Output the checkboxes
		echo aswp_html::meta_box_checkbox_field(__('Formats<br /><span class="description">Choose more than 1 to show random format.</span>', ASWP_UNIQUE_NAME), array(
			array(
				'checked' => $default_values['aswp_format_1'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_1',
				'value' => '120_600',
				'text' => __('120 x 600', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_2'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_2',
				'value' => '120_240',
				'text' => __('120 x 240', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_13'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_13',
				'value' => '120_90',
				'text' => __('120 x 90', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_3'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_3',
				'value' => '125_125',
				'text' => __('125 x 125', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_14'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_14',
				'value' => '160_90',
				'text' => __('160 x 90', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_4'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_4',
				'value' => '160_600',
				'text' => __('160 x 600', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_15'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_15',
				'value' => '180_90',
				'text' => __('180 x 90', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_5'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_5',
				'value' => '180_150',
				'text' => __('180 x 150', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_16'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_16',
				'value' => '200_90',
				'text' => __('200 x 90', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_6'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_6',
				'value' => '200_200',
				'text' => __('200 x 200', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_7'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_7',
				'value' => '234_60',
				'text' => __('234 x 60', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_8'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_8',
				'value' => '250_250',
				'text' => __('250 x 250', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_9'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_9',
				'value' => '300_250',
				'text' => __('300 x 250', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_10'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_10',
				'value' => '336_280',
				'text' => __('336 x 280', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_17'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_17',
				'value' => '468_15',
				'text' => __('468 x 15', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_11'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_11',
				'value' => '468_60',
				'text' => __('468 x 60', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_18'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_18',
				'value' => '728_15',
				'text' => __('728 x 15', ASWP_UNIQUE_NAME),
			),
			array(
				'checked' => $default_values['aswp_format_12'] !== '' ? 'yes' : '',
				'name' => 'aswp_format_12',
				'value' => '728_90',
				'text' => __('728 x 90', ASWP_UNIQUE_NAME),
			)
		));

		//	Output select field
		echo aswp_html::meta_box_select_field(__('Corner Style', ASWP_UNIQUE_NAME), 'aswp_ad_corner_style', array(
			array(
				'value' => '1',
				'selected' => $default_values['aswp_ad_corner_style'] === '1' ? 'yes' : '',
				'text' => __('Square', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '2',
				'selected' => $default_values['aswp_ad_corner_style'] === '2' ? 'yes' : '',
				'text' => __('Slightly rounded', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => '3',
				'selected' => $default_values['aswp_ad_corner_style'] === '3' ? 'yes' : '',
				'text' => __('Very rounded', ASWP_UNIQUE_NAME),
			)
		));

		//	Output select field
		echo aswp_html::meta_box_select_field(__('Font Family', ASWP_UNIQUE_NAME), 'aswp_ad_font_family', array(
			array(
				'value' => 'Use account default',
				'selected' => $default_values['aswp_ad_font_family'] === 'Use account default' ? 'yes' : '',
				'text' => __('Use account default', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'AdSense default font family',
				'selected' => $default_values['aswp_ad_font_family'] === 'AdSense default font family' ? 'yes' : '',
				'text' => __('AdSense default font family', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Arial',
				'selected' => $default_values['aswp_ad_font_family'] === 'Arial' ? 'yes' : '',
				'text' => __('Arial', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Verdana',
				'selected' => $default_values['aswp_ad_font_family'] === 'Verdana' ? 'yes' : '',
				'text' => __('Verdana', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Times',
				'selected' => $default_values['aswp_ad_font_family'] === 'Times' ? 'yes' : '',
				'text' => __('Times', ASWP_UNIQUE_NAME),
			)
		));

		//	Output select field
		/*echo aswp_html::meta_box_select_field(__('Font Size', ASWP_UNIQUE_NAME), 'aswp_ad_font_size', array(
			array(
				'value' => 'Use account default',
				'selected' => $default_values['aswp_ad_font_size'] === 'Use account default' ? 'yes' : '',
				'text' => __('Use account default', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'AdSense default font size',
				'selected' => $default_values['aswp_ad_font_size'] === 'AdSense default font size' ? 'yes' : '',
				'text' => __('AdSense default font size', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Small',
				'selected' => $default_values['aswp_ad_font_size'] === 'Small' ? 'yes' : '',
				'text' => __('Small', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Medium',
				'selected' => $default_values['aswp_ad_font_size'] === 'Medium' ? 'yes' : '',
				'text' => __('Medium', ASWP_UNIQUE_NAME),
			),
			array(
				'value' => 'Large',
				'selected' => $default_values['aswp_ad_font_size'] === 'Large' ? 'yes' : '',
				'text' => __('Large', ASWP_UNIQUE_NAME),
			)
		));*/

		//	Output color pickers
		echo aswp_html::meta_box_color_pickers(__('Colors<br /><a href="#" id="aswp_restore_default">Restore Default</a>', ASWP_UNIQUE_NAME), array(
			array(
				'text' => __('Border', ASWP_UNIQUE_NAME),
				'picker_id' => '1',
				'input_name' => 'aswp_border_color',
				'input_value' => $default_values['aswp_border_color']
			),
			array(
				'text' => __('Background', ASWP_UNIQUE_NAME),
				'picker_id' => '2',
				'picker' => '',
				'input_name' => 'aswp_background_color',
				'input_value' => $default_values['aswp_background_color']
			),
			array(
				'text' => __('Title', ASWP_UNIQUE_NAME),
				'picker_id' => '3',
				'picker' => '',
				'input_name' => 'aswp_title_color',
				'input_value' => $default_values['aswp_title_color']
			),
			array(
				'text' => __('Text', ASWP_UNIQUE_NAME),
				'picker_id' => '4',
				'picker' => '',
				'input_name' => 'aswp_text_color',
				'input_value' => $default_values['aswp_text_color']
			),
			array(
				'text' => __('Url', ASWP_UNIQUE_NAME),
				'picker_id' => '5',
				'picker' => '',
				'input_name' => 'aswp_url_color',
				'input_value' => $default_values['aswp_url_color']
			),
		));

		//	Output live ad preview
		echo aswp_html::meta_box_ad_preview(__('Ad Preview', ASWP_UNIQUE_NAME), array(
			'aswp_border_color' => $default_values['aswp_border_color'],
			'aswp_background_color' => $default_values['aswp_background_color'],
			'aswp_title_color' => $default_values['aswp_title_color'],
			'aswp_text_color' => $default_values['aswp_text_color'],
			'aswp_url_color' => $default_values['aswp_url_color']
		));

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the start of the post box
		echo aswp_html::insert_post_box_start('aswp_advanced', __('Advanced', ASWP_UNIQUE_NAME));

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Publisher ID<br /><span class="description">You can enter different Publisher ID for this ad only or<br />leave it empty to use the global Publisher ID set in options page.</span>', ASWP_UNIQUE_NAME), 'aswp_adv_publisher_id', $default_values['aswp_adv_publisher_id']);

		//	Output the input field
		echo aswp_html::meta_box_input_field(__('Custom Channel', ASWP_UNIQUE_NAME), 'aswp_adv_custom_channel', $default_values['aswp_adv_custom_channel']);

		//	Output the end of the post box
		echo aswp_html::insert_post_box_end();

		//	Output the save button
		echo aswp_html::blue_button(__('Update Ad', ASWP_UNIQUE_NAME));

		//	Output the end of the form
		echo aswp_html::form_end();

		echo aswp_html::wrap_footer();
	}

	/**
	 * Saves the ad into table
	 *
	 * @since	1.0
	 * @param	array	$post_data
	 * @param	integer	$record_id
	 * @return	array/boolean
	 *
	 */
	function save_ad($post_data, $record_id)
	{
		$error_found = FALSE;
		$error_message = FALSE;

		//	Check nonce first
		if (wp_verify_nonce($post_data['_wpnonce'], 'aswp_create_new_ad') == FALSE)
		{
			_e('Sorry, your nonce did not verify.', ASWP_UNIQUE_NAME);
   			die();
		}

		//	Check if we have ad name.
		if (isset($post_data['aswp_ad_name']) == FALSE || $post_data['aswp_ad_name'] === '')
		{
			$error_found = TRUE;
			$error_message[] = array(
				'type' => 'error',
				'message' => __('Please enter Ad Name.', ASWP_UNIQUE_NAME)
			);
		}

		//	Check if ad type is selected
		if (isset($post_data['aswp_ad_type']) == FALSE || in_array($post_data['aswp_ad_type'], array('1', '2', '3')) == FALSE)
		{
			$error_found = TRUE;
			$error_message[] = array(
				'type' => 'error',
				'message' => __('Please select Ad Type.', ASWP_UNIQUE_NAME)
			);
		}

		//	Check if at least 1 positions is selected
		if (isset($post_data['aswp_placement_type']) == TRUE && $post_data['aswp_placement_type'] === '1')
		{
			if (isset($post_data['aswp_top_left']) == FALSE && isset($post_data['aswp_top_center']) == FALSE && isset($post_data['aswp_top_right']) == FALSE
				&& isset($post_data['aswp_middle_left']) == FALSE && isset($post_data['aswp_middle_center']) == FALSE && isset($post_data['aswp_middle_right']) == FALSE
				&& isset($post_data['aswp_bottom_left']) == FALSE && isset($post_data['aswp_bottom_center']) == FALSE && isset($post_data['aswp_bottom_right']) == FALSE)
			{
				$error_found = TRUE;
				$error_message[] = array(
					'type' => 'error',
					'message' => __('Please select at least one In Post Position.', ASWP_UNIQUE_NAME)
				);
			}
		}
		else if (isset($post_data['aswp_placement_type']) == TRUE && $post_data['aswp_placement_type'] === '2')
		{
			if (isset($post_data['aswp_insert_after_n_paragraph']) == FALSE || $post_data['aswp_insert_after_n_paragraph'] === '0' || $post_data['aswp_insert_after_n_paragraph'] === '')
			{
				$error_found = TRUE;
				$error_message[] = array(
					'type' => 'error',
					'message' => __('Please enter the number of paragraphs.', ASWP_UNIQUE_NAME)
				);
			}

			if (isset($post_data['aswp_paragraph_position_left']) == FALSE && isset($post_data['aswp_paragraph_position_center']) == FALSE && isset($post_data['aswp_paragraph_position_right']) == FALSE)
			{
				$error_found = TRUE;
				$error_message[] = array(
					'type' => 'error',
					'message' => __('Please select at least one In Post Position.', ASWP_UNIQUE_NAME)
				);
			}
		}

		//	Combine placement fields
		$placement = array(
			'aswp_ad_per_page' => isset($post_data['aswp_ad_per_page']) == TRUE ? $post_data['aswp_ad_per_page'] : '1',
			'aswp_ad_per_post' => isset($post_data['aswp_ad_per_post']) == TRUE ? $post_data['aswp_ad_per_post'] : '1',
			'aswp_show_ad_on_home' => isset($post_data['aswp_show_ad_on_home']) == TRUE ? $post_data['aswp_show_ad_on_home'] : '',
			'aswp_show_ad_on_category' => isset($post_data['aswp_show_ad_on_category']) == TRUE ? $post_data['aswp_show_ad_on_category'] : '',
			'aswp_show_ad_on_archive' => isset($post_data['aswp_show_ad_on_archive']) == TRUE ? $post_data['aswp_show_ad_on_archive'] : '',
			'aswp_top_left' => isset($post_data['aswp_top_left']) == TRUE ? $post_data['aswp_top_left'] : '',
			'aswp_top_center' => isset($post_data['aswp_top_center']) == TRUE ? $post_data['aswp_top_center'] : '',
			'aswp_top_right' => isset($post_data['aswp_top_right']) == TRUE ? $post_data['aswp_top_right'] : '',
			'aswp_middle_left' => isset($post_data['aswp_middle_left']) == TRUE ? $post_data['aswp_middle_left'] : '',
			'aswp_middle_center' => isset($post_data['aswp_middle_center']) == TRUE ? $post_data['aswp_middle_center'] : '',
			'aswp_middle_right' => isset($post_data['aswp_middle_right']) == TRUE ? $post_data['aswp_middle_right'] : '',
			'aswp_bottom_left' => isset($post_data['aswp_bottom_left']) == TRUE ? $post_data['aswp_bottom_left'] : '',
			'aswp_bottom_center' => isset($post_data['aswp_bottom_center']) == TRUE ? $post_data['aswp_bottom_center'] : '',
			'aswp_bottom_right' => isset($post_data['aswp_bottom_right']) == TRUE ? $post_data['aswp_bottom_right'] : '',
			'aswp_margin_top' => isset($post_data['aswp_margin_top']) == TRUE ? $post_data['aswp_margin_top'] : '',
			'aswp_margin_right' => isset($post_data['aswp_margin_right']) == TRUE ? $post_data['aswp_margin_right'] : '0',
			'aswp_margin_bottom' => isset($post_data['aswp_margin_bottom']) == TRUE ? $post_data['aswp_margin_bottom'] : '0',
			'aswp_margin_left' => isset($post_data['aswp_margin_left']) == TRUE ? $post_data['aswp_margin_left'] : '0',
			'aswp_placement_type' => isset($post_data['aswp_placement_type']) == TRUE ? $post_data['aswp_placement_type'] : '1',
			'aswp_insert_after_n_paragraph' => isset($post_data['aswp_insert_after_n_paragraph']) == TRUE ? $post_data['aswp_insert_after_n_paragraph'] : '0',
			'aswp_paragraph_position_left' => isset($post_data['aswp_paragraph_position_left']) == TRUE ? $post_data['aswp_paragraph_position_left'] : '',
			'aswp_paragraph_position_center' => isset($post_data['aswp_paragraph_position_center']) == TRUE ? $post_data['aswp_paragraph_position_center'] : '',
			'aswp_paragraph_position_right' => isset($post_data['aswp_paragraph_position_right']) == TRUE ? $post_data['aswp_paragraph_position_right'] : '',
		);

		//	Check if at least 1 ad format is selected
		if (isset($post_data['aswp_format_1']) == FALSE && isset($post_data['aswp_format_2']) == FALSE && isset($post_data['aswp_format_3']) == FALSE
			&& isset($post_data['aswp_format_4']) == FALSE && isset($post_data['aswp_format_5']) == FALSE && isset($post_data['aswp_format_6']) == FALSE
			&& isset($post_data['aswp_format_7']) == FALSE && isset($post_data['aswp_format_8']) == FALSE && isset($post_data['aswp_format_9']) == FALSE
			&& isset($post_data['aswp_format_10']) == FALSE && isset($post_data['aswp_format_11']) == FALSE && isset($post_data['aswp_format_12']) == FALSE
			&& isset($post_data['aswp_format_13']) == FALSE && isset($post_data['aswp_format_14']) == FALSE && isset($post_data['aswp_format_15']) == FALSE
			&& isset($post_data['aswp_format_16']) == FALSE && isset($post_data['aswp_format_17']) == FALSE && isset($post_data['aswp_format_18']) == FALSE)
		{
			$error_found = TRUE;
			$error_message[] = array(
				'type' => 'error',
				'message' => __('Please select at least one ad Format.', ASWP_UNIQUE_NAME)
			);
		}

		//	Combine design fields
		$design = array(
			'aswp_format_1' => isset($post_data['aswp_format_1']) == TRUE ? $post_data['aswp_format_1'] : '',
			'aswp_format_2' => isset($post_data['aswp_format_2']) == TRUE ? $post_data['aswp_format_2'] : '',
			'aswp_format_3' => isset($post_data['aswp_format_3']) == TRUE ? $post_data['aswp_format_3'] : '',
			'aswp_format_4' => isset($post_data['aswp_format_4']) == TRUE ? $post_data['aswp_format_4'] : '',
			'aswp_format_5' => isset($post_data['aswp_format_5']) == TRUE ? $post_data['aswp_format_5'] : '',
			'aswp_format_6' => isset($post_data['aswp_format_6']) == TRUE ? $post_data['aswp_format_6'] : '',
			'aswp_format_7' => isset($post_data['aswp_format_7']) == TRUE ? $post_data['aswp_format_7'] : '',
			'aswp_format_8' => isset($post_data['aswp_format_8']) == TRUE ? $post_data['aswp_format_8'] : '',
			'aswp_format_9' => isset($post_data['aswp_format_9']) == TRUE ? $post_data['aswp_format_9'] : '',
			'aswp_format_10' => isset($post_data['aswp_format_10']) == TRUE ? $post_data['aswp_format_10'] : '',
			'aswp_format_11' => isset($post_data['aswp_format_11']) == TRUE ? $post_data['aswp_format_11'] : '',
			'aswp_format_12' => isset($post_data['aswp_format_12']) == TRUE ? $post_data['aswp_format_12'] : '',
			'aswp_format_13' => isset($post_data['aswp_format_13']) == TRUE ? $post_data['aswp_format_13'] : '',
			'aswp_format_14' => isset($post_data['aswp_format_14']) == TRUE ? $post_data['aswp_format_14'] : '',
			'aswp_format_15' => isset($post_data['aswp_format_15']) == TRUE ? $post_data['aswp_format_15'] : '',
			'aswp_format_16' => isset($post_data['aswp_format_16']) == TRUE ? $post_data['aswp_format_16'] : '',
			'aswp_format_17' => isset($post_data['aswp_format_17']) == TRUE ? $post_data['aswp_format_17'] : '',
			'aswp_format_18' => isset($post_data['aswp_format_18']) == TRUE ? $post_data['aswp_format_18'] : '',
			'aswp_ad_corner_style' => isset($post_data['aswp_ad_corner_style']) == TRUE ? $post_data['aswp_ad_corner_style'] : '1',
			'aswp_ad_font_family' => isset($post_data['aswp_ad_font_family']) == TRUE ? $post_data['aswp_ad_font_family'] : 'Use account default',
			'aswp_ad_font_size' => isset($post_data['aswp_ad_font_size']) == TRUE ? $post_data['aswp_ad_font_size'] : 'Use account default',
			'aswp_border_color' => isset($post_data['aswp_border_color']) == TRUE ? $post_data['aswp_border_color'] : 'FFFFFF',
			'aswp_background_color' => isset($post_data['aswp_background_color']) == TRUE ? $post_data['aswp_background_color'] : 'FFFFFF',
			'aswp_title_color' => isset($post_data['aswp_title_color']) == TRUE ? $post_data['aswp_title_color'] : '0000ff',
			'aswp_text_color' => isset($post_data['aswp_text_color']) == TRUE ? $post_data['aswp_text_color'] : '000000',
			'aswp_url_color' => isset($post_data['aswp_url_color']) == TRUE ? $post_data['aswp_url_color'] : '008000'
		);

		//	Combine Advance fields
		$advance = array(
			'aswp_adv_publisher_id' => isset($post_data['aswp_adv_publisher_id']) == TRUE ? $post_data['aswp_adv_publisher_id'] : '',
			'aswp_adv_custom_channel' => isset($post_data['aswp_adv_custom_channel']) == TRUE ? $post_data['aswp_adv_custom_channel'] : ''
		);

		//	Reward author field
		$reward_author = isset($post_data['aswp_donation']) == TRUE && $post_data['aswp_donation'] !== '' ? $post_data['aswp_donation'] : '5';

		if ($error_found == FALSE)
		{
			//	Well, if we are still here it means all is good, so let's save the data to table.
			$this->wpdb->update(
				$this->wpdb->prefix.ASWP_DATA_TABLE,
				array(
					'ad_name' => $post_data['aswp_ad_name'],
					'ad_type' => $post_data['aswp_ad_type'],
					'ad_placement' => serialize($placement),
					'ad_design' => serialize($design),
					'ad_advance' => serialize($advance)
				),
				array('ID' => $record_id),
				array(
					'%s',
					'%d',
					'%s',
					'%s',
					'%s'
				),
				array('%d')
			);

			//	Load options
			$options = get_option('aswp_options');

			//	Update donation number
			$options['reward_author'] = $reward_author;

			//	Update options
			update_option('aswp_options', $options);

			wp_redirect(admin_url().'admin.php?page=aswp_ads');
		}
		else
		{
			return array(
				'message' => $error_message,
				'fields' => array(
					'aswp_ad_name' => $post_data['aswp_ad_name'],
					'aswp_ad_type' => $post_data['aswp_ad_type'],
					'ad_placement' => $placement,
					'ad_design' => $design,
					'ad_advance' => $advance,
					'ad_reward_author' => $reward_author
				)
			);
		}
	}
}
?>