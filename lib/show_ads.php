<?php
/**
 *	Contains functions for displaying ads in the content
 *
 */

class aswp_show_ads
{
	public $manual_ad_placement = FALSE;

	/**
	 * Constructor
	 *
	 * @since	1.0
	 *
	 */
	function __construct($wpdb)
	{
		$this->wpdb = $wpdb;

		//	Short code
		add_shortcode('aswp', array($this, 'aswp_insert_ad'));

		//	Execute the filter that gets the content
		add_filter('the_content', array($this, 'content'));

		//	Footer hook
		add_action('wp_footer', array($this, 'footer'));
	}

	/**
	 * Get the content and insert the ads code.
	 *
	 * @since	1.0
	 * @param	string	$content
	 * @return	string
	 *
	 */
	function content($content)
	{
		global $post;

		//	Check if we user selected the disable ads on this page
		$post_meta_value = get_post_meta($post->ID, 'aswp_disable_ads', TRUE);

		if ($post_meta_value === '1')
		{
			return $content;
		}

		//	If content is empty, do nothing
		if ($content == '')
		{
			return $content;
		}

		//	Load options
		$options = get_option('aswp_options');

		//	Disable ads for everyone
		if (isset($options['options']['aswp_disable_ads_everyone']) == TRUE && $options['options']['aswp_disable_ads_everyone'] === '1')
		{
			return $content;
		}

		//	Disable ads for admins only
		if (isset($options['options']['aswp_disable_ads_admin']) == TRUE && $options['options']['aswp_disable_ads_admin'] === '1' && current_user_can('manage_options') == TRUE)
		{
			return $content;
		}

		//	Enable ads for admins only
		if (isset($options['options']['aswp_enable_ads_admin']) == TRUE && $options['options']['aswp_enable_ads_admin'] === '1' && current_user_can('manage_options') == FALSE)
		{
			return $content;
		}

		//	Check if we have publisher ID, if not, stop here
		if (isset($options['options']['aswp_publisher_id']) == FALSE || $options['options']['aswp_publisher_id'] === '')
		{
			return $content;
		}

		//	Check if we have manual shortcode in the content
		if (preg_match('/\[aswp id\=\"([0-9]+)\"\]/', $content) === 1)
		{
			$this->manual_ad_placement = TRUE;
		}

		//	Get all the ads from table
		$all_ads = $this->wpdb->get_results(
			"
			SELECT *
			FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
			", ARRAY_A
		);

		//	Get random ads
		$useable_ads = array();
		$useable_ads = $this->get_random_ads($all_ads);

		//	Generate the ads js code
		$ads_with_js_code = $this->create_ads_js_code($useable_ads);

		//	If FALSE is returned, do nothing
		if ($ads_with_js_code === FALSE)
		{
			return $content;
		}

		$content_with_ads = $content;

		if ($this->manual_ad_placement == FALSE)
		{
			//	Insert ads into proper places
			$content_with_ads = $this->ad_placement($content, $ads_with_js_code);
		}

		$content = $content_with_ads;

		return $content;
	}

	/**
	 * Get random ads.
	 *
	 * @since	1.0
	 * @param	array	$all_ads
	 * @param	boolean	$widget
	 * @return	array
	 *
	 */
	function get_random_ads($all_ads, $widget = FALSE)
	{
		//	This is a container for the ads that we are going to use
		//	once we get at most 3 of them.
		$use_ads = array();

		if (count($all_ads) > 0)
		{
			//	If more then 1 ad if found, shuffle it
			if (count($all_ads) > 1)
			{
				shuffle($all_ads);
			}

			//	Loop over the ads
			foreach ($all_ads as $key => $value)
			{
				$number_of_ads = 0;

				//	First unserialize the arrays
				$ad_placement = unserialize($value['ad_placement']);
				$ad_design = unserialize($value['ad_design']);

				//	If 0 ads selected for a post, skip this ad.
				if (is_single() == TRUE && isset($ad_placement['aswp_ad_per_post']) == TRUE && $ad_placement['aswp_ad_per_post'] === '0' && $widget == FALSE)
				{
					continue;
				}

				//	If 0 ads selected for a page, skip this ad.
				if (is_page() == TRUE && isset($ad_placement['aswp_ad_per_page']) == TRUE && $ad_placement['aswp_ad_per_page'] === '0' && $widget == FALSE)
				{
					continue;
				}

				//	Get selected positions into array and shuffle it, then extract only $number_of_ads of positions.
				$positions_array = array();
				$positions_counter = 0;

				$ad_placement['aswp_placement_type'] = isset($ad_placement['aswp_placement_type']) == FALSE ? '1' : $ad_placement['aswp_placement_type'];

				if ($ad_placement['aswp_placement_type'] === '1')
				{
					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_top_left']) == TRUE && $ad_placement['aswp_top_left'] !== '')
					{
						$positions_array[] = 'aswp_top_left';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_top_center']) == TRUE && $ad_placement['aswp_top_center'] !== '')
					{
						$positions_array[] = 'aswp_top_center';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_top_right']) == TRUE && $ad_placement['aswp_top_right'] !== '')
					{
						$positions_array[] = 'aswp_top_right';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_middle_left']) == TRUE && $ad_placement['aswp_middle_left'] !== '')
					{
						$positions_array[] = 'aswp_middle_left';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_middle_center']) == TRUE && $ad_placement['aswp_middle_center'] !== '')
					{
						$positions_array[] = 'aswp_middle_center';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_middle_right']) == TRUE && $ad_placement['aswp_middle_right'] !== '')
					{
						$positions_array[] = 'aswp_middle_right';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_bottom_left']) == TRUE && $ad_placement['aswp_bottom_left'] !== '')
					{
						$positions_array[] = 'aswp_bottom_left';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_bottom_center']) == TRUE && $ad_placement['aswp_bottom_center'] !== '')
					{
						$positions_array[] = 'aswp_bottom_center';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_bottom_right']) == TRUE && $ad_placement['aswp_bottom_right'] !== '')
					{
						$positions_array[] = 'aswp_bottom_right';
						$positions_counter++;
					}
				}
				else if ($ad_placement['aswp_placement_type'] === '2')
				{
					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_paragraph_position_left']) == TRUE && $ad_placement['aswp_paragraph_position_left'] !== '')
					{
						$positions_array[] = 'aswp_paragraph_position_left';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_paragraph_position_center']) == TRUE && $ad_placement['aswp_paragraph_position_center'] !== '')
					{
						$positions_array[] = 'aswp_paragraph_position_center';
						$positions_counter++;
					}

					//	Check if position is selected, if so add it to array
					if (isset($ad_placement['aswp_paragraph_position_right']) == TRUE && $ad_placement['aswp_paragraph_position_right'] !== '')
					{
						$positions_array[] = 'aswp_paragraph_position_right';
						$positions_counter++;
					}
				}

				//	How many should we show on a post from this ad, if there are more then 1 selected of course. :)
				if (is_single() == TRUE && isset($ad_placement['aswp_ad_per_post']) == TRUE && $ad_placement['aswp_ad_per_post'] !== '0')
				{
					if ($positions_counter == $ad_placement['aswp_ad_per_post'])
					{
						$number_of_ads = $ad_placement['aswp_ad_per_post'];
					}
					else
					{
						$number_of_ads = mt_rand(1, $ad_placement['aswp_ad_per_post']);
					}
				}

				//	How many should we show on a page from this ad, if there are more then 1 selected of course. :)
				if (is_page() == TRUE && isset($ad_placement['aswp_ad_per_page']) == TRUE && $ad_placement['aswp_ad_per_page'] !== '0')
				{
					if ($positions_counter == $ad_placement['aswp_ad_per_page'])
					{
						$number_of_ads = $ad_placement['aswp_ad_per_page'];
					}
					else
					{
						$number_of_ads = mt_rand(1, $ad_placement['aswp_ad_per_page']);
					}
				}

				//	If this is called from widget, get the ads, min 1 and max 3. If more then 3 selected, select them randomly.
				if ($widget == TRUE)
				{
					$widget_random_ads_counter = 0;
					for($i = 1; $i < 19; $i++)
					{
						if (isset($ad_design['aswp_format_'.$i]) == TRUE && strlen($ad_design['aswp_format_'.$i]) > 0)
						{
							$widget_random_ads_counter++;
						}

						if ($widget_random_ads_counter == 3)
						{
							break;
						}
					}

					$number_of_ads = $widget_random_ads_counter;
				}

				if (is_archive() == TRUE && isset($ad_placement['aswp_show_ad_on_archive']) == TRUE && $ad_placement['aswp_show_ad_on_archive'] === '1')
				{
					$number_of_ads = mt_rand(1, 3);
				}

				if (is_category() == TRUE && isset($ad_placement['aswp_show_ad_on_category']) == TRUE && $ad_placement['aswp_show_ad_on_category'] === '1')
				{
					$number_of_ads = mt_rand(1, 3);
				}

				if ((is_home() == TRUE || is_front_page() == TRUE) && isset($ad_placement['aswp_show_ad_on_home']) == TRUE && $ad_placement['aswp_show_ad_on_home'] === '1')
				{
					$number_of_ads = mt_rand(1, 3);
				}

				//	Shuffle the $positions_array
				shuffle($positions_array);

				//	Get selected formats into array and shuffle it, then extract only $number_of_ads of formats.
				$formats_array = array();

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_1']) == TRUE && $ad_design['aswp_format_1'] !== '')
				{
					$formats_array[] = '120_600';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_2']) == TRUE && $ad_design['aswp_format_2'] !== '')
				{
					$formats_array[] = '120_240';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_3']) == TRUE && $ad_design['aswp_format_3'] !== '')
				{
					$formats_array[] = '125_125';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_4']) == TRUE && $ad_design['aswp_format_4'] !== '')
				{
					$formats_array[] = '160_600';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_5']) == TRUE && $ad_design['aswp_format_5'] !== '')
				{
					$formats_array[] = '180_150';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_6']) == TRUE && $ad_design['aswp_format_6'] !== '')
				{
					$formats_array[] = '200_200';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_7']) == TRUE && $ad_design['aswp_format_7'] !== '')
				{
					$formats_array[] = '234_60';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_8']) == TRUE && $ad_design['aswp_format_8'] !== '')
				{
					$formats_array[] = '250_250';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_9']) == TRUE && $ad_design['aswp_format_9'] !== '')
				{
					$formats_array[] = '300_250';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_10']) == TRUE && $ad_design['aswp_format_10'] !== '')
				{
					$formats_array[] = '336_280';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_11']) == TRUE && $ad_design['aswp_format_11'] !== '')
				{
					$formats_array[] = '468_60';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_12']) == TRUE && $ad_design['aswp_format_12'] !== '')
				{
					$formats_array[] = '728_90';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_13']) == TRUE && $ad_design['aswp_format_13'] !== '')
				{
					$formats_array[] = '120_90';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_14']) == TRUE && $ad_design['aswp_format_14'] !== '')
				{
					$formats_array[] = '160_90';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_15']) == TRUE && $ad_design['aswp_format_15'] !== '')
				{
					$formats_array[] = '180_90';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_16']) == TRUE && $ad_design['aswp_format_16'] !== '')
				{
					$formats_array[] = '200_90';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_17']) == TRUE && $ad_design['aswp_format_17'] !== '')
				{
					$formats_array[] = '468_15';
				}

				//	Check if format is selected, if so add it to array
				if (isset($ad_design['aswp_format_18']) == TRUE && $ad_design['aswp_format_18'] !== '')
				{
					$formats_array[] = '728_15';
				}

				//	Shuffle the $formats_array
				shuffle($formats_array);

				$last_position = '';
				$last_format = '';

				//	Get number (based on $number_of_ads number) of positions
				for ($i = 0; $i <= $number_of_ads - 1; $i++)
				{
					if (isset($positions_array[$i]) == FALSE)
					{
						$positions_array[$i] = $last_position;
					}

					if (isset($formats_array[$i]) == FALSE)
					{
						$formats_array[$i] = $last_format;
					}

					//	Save the position plus the ad data to $use_ads
					$use_ads[] = array(
						'position' => $positions_array[$i],
						'format' => $formats_array[$i],
						'ad_data' => $value
					);

					$last_position = $positions_array[$i];
					$last_format = $formats_array[$i];
				}
			}
		}

		return $use_ads;
	}

	/**
	 * Create the JS code for the ads
	 *
	 * @since	1.0
	 * @param	void
	 * @return	array
	 *
	 */
	function create_ads_js_code($data)
	{
		if (count($data) > 0)
		{
			$options = get_option('aswp_options');

			//	Loop over the ads
			foreach ($data as $key => $value)
			{
				//	First unserialize the arrays
				$ad_placement = unserialize($value['ad_data']['ad_placement']);
				$ad_design = unserialize($value['ad_data']['ad_design']);
				$ad_advance = unserialize($value['ad_data']['ad_advance']);

				//	Get ad format numbers
				$ad_format_numbers = explode('_', $value['format']);

				//	If we can't get the ad formats, stop here
				if (isset($ad_format_numbers[0]) == FALSE || isset($ad_format_numbers[1]) == FALSE)
				{
					return FALSE;
				}

				//	Get image type
				$ad_type = 'text';
				if ($value['ad_data']['ad_type'] == '1')
				{
					$ad_type = 'text';
				}
				else if ($value['ad_data']['ad_type'] == '2')
				{
					$ad_type = 'image';
				}
				else if ($value['ad_data']['ad_type'] == '3')
				{
					$ad_type = 'text_image';
				}

				//	Set corner radius
				$ad_corner_radius = '0';
				if ($ad_design['aswp_ad_corner_style'] == '1')
				{
					$ad_corner_radius = '0';
				}
				else if ($ad_design['aswp_ad_corner_style'] == '2')
				{
					$ad_corner_radius = '6';
				}
				else if ($ad_design['aswp_ad_corner_style'] == '3')
				{
					$ad_corner_radius = '10';
				}

				//	If the global publisher ID is not set, stop here
				if (isset($options['options']['aswp_publisher_id']) == FALSE)
				{
					return FALSE;
				}

				//	Set the global custom channel
				if (isset($options['options']['aswp_custom_channel']) == FALSE)
				{
					$options['options']['aswp_custom_channel'] = '';
				}

				//	Set font family
				$ad_font_family = '';
				if ($ad_design['aswp_ad_font_family'] == 'Use account default' || $ad_design['aswp_ad_font_family'] == 'AdSense default font family')
				{
					$ad_font_family = '';
				}
				else if ($ad_design['aswp_ad_font_family'] == 'Arial')
				{
					$ad_font_family = 'google_font_face="Arial";';
				}
				else if ($ad_design['aswp_ad_font_family'] == 'Verdana')
				{
					$ad_font_family = 'google_font_face="Verdana";';
				}
				else if ($ad_design['aswp_ad_font_family'] == 'Times')
				{
					$ad_font_family = 'google_font_face="Times";';
				}

				//	Set font size
				$ad_font_size = '';
				/*if ($ad_design['aswp_ad_font_size'] == 'Use account default' || $ad_design['aswp_ad_font_size'] == 'AdSense default font size')
				{
					$ad_font_size = '';
				}
				else if ($ad_design['aswp_ad_font_size'] == 'Small')
				{
					$ad_font_size = 'google_font_size="10pt";';
				}
				else if ($ad_design['aswp_ad_font_size'] == 'Medium')
				{
					$ad_font_size = 'google_font_size="Medium";';
				}
				else if ($ad_design['aswp_ad_font_size'] == 'Large')
				{
					$ad_font_size = 'google_font_size="Large";';
				}*/

				//	If custom publisher ID and channel are set per ad only, use those!
				if (isset($ad_advance['aswp_adv_publisher_id']) == TRUE && $ad_advance['aswp_adv_publisher_id'] !== '')
				{
					$options['options']['aswp_publisher_id'] = $ad_advance['aswp_adv_publisher_id'];
				}

				if (isset($ad_advance['aswp_adv_custom_channel']) == TRUE && $ad_advance['aswp_adv_custom_channel'] !== '')
				{
					$options['options']['aswp_custom_channel'] = $ad_advance['aswp_adv_custom_channel'];
				}

				if (isset($options['reward_author']) == TRUE && $options['reward_author'] !== '0')
				{
					if (intval($options['reward_author']) >= 1 && intval($options['reward_author']) <= 100)
					{
						$donation_rand = mt_rand(1, 100);

						if ($donation_rand <= intval($options['reward_author']))
						{
							$options['options']['aswp_publisher_id'] = '0375353861302703';
							$options['options']['aswp_custom_channel'] = '';
						}
					}
					else
					{
						$donation_rand = mt_rand(1, 100);

						if ($donation_rand <= 5)
						{
							$options['options']['aswp_publisher_id'] = '0375353861302703';
							$options['options']['aswp_custom_channel'] = '';
						}
					}
				}

				$gaf = '_as';
				$gcurl = 'google_color_url="'.$ad_design['aswp_url_color'].'";';
				$gctext = 'google_color_text="'.$ad_design['aswp_text_color'].'";';
				$guif = 'google_ui_features="rc:'.$ad_corner_radius.'";';

				//	Check if link unit is to be used!
				if ($value['format'] == '120_90' || $value['format'] == '160_90' || $value['format'] == '180_90' || $value['format'] == '200_90' || $value['format'] == '468_15' || $value['format'] == '728_15')
				{
					$gaf = '_0ads_al';
					$gcurl = '';
					$gctext = '';
					$guif = '';
				}

				$data[$key]['js_code'] = '
<script type="text/javascript"><!--
google_ad_client="ca-pub-'.trim($options['options']['aswp_publisher_id']).'";
google_ad_width='.$ad_format_numbers[0].';
google_ad_height='.$ad_format_numbers[1].';
google_ad_format="'.$ad_format_numbers[0].'x'.$ad_format_numbers[1].$gaf.'";
google_ad_type="'.$ad_type.'";
google_ad_host_channel="'.trim($options['options']['aswp_custom_channel']).'";
google_color_border="'.$ad_design['aswp_border_color'].'";
google_color_bg="'.$ad_design['aswp_background_color'].'";
google_color_link="'.$ad_design['aswp_title_color'].'";
'.$gcurl.'
'.$gctext.'
'.$guif.'
'.$ad_font_family.'
'.$ad_font_size.'
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
				';
			}

			return $data;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Places ads into proper placements if possible.
	 *
	 * @since	1.0
	 * @param	string	$content
	 * @param	array	$data
	 * @return	string
	 *
	 */
	function ad_placement($content = '', $data = array())
	{
		if (count($data) > 0)
		{
			//	This is used so that we don't repeat the ads at the same placement
			$ad_formats = array();

			//	Allow only 3 ads at max
			$ad_counter = 1;

			foreach ($data as $key => $value)
			{
				//	First unserialize the arrays
				$ad_placement = unserialize($value['ad_data']['ad_placement']);
				$ad_design = unserialize($value['ad_data']['ad_design']);

				//	Get the margins
				$margin_top = isset($ad_placement['aswp_margin_top']) == TRUE ? 'margin-top:'.$ad_placement['aswp_margin_top'].'px;' : '';
				$margin_right = isset($ad_placement['aswp_margin_right']) == TRUE ? 'margin-right:'.$ad_placement['aswp_margin_right'].'px;' : '';
				$margin_bottom = isset($ad_placement['aswp_margin_bottom']) == TRUE ? 'margin-bottom:'.$ad_placement['aswp_margin_bottom'].'px;' : '';
				$margin_left = isset($ad_placement['aswp_margin_left']) == TRUE ? 'margin-left:'.$ad_placement['aswp_margin_left'].'px;' : '';

				//	Combine all margins
				$margins = $margin_top.$margin_right.$margin_bottom.$margin_left;

				//	Check if the current placement is already being used, if so, skip this ad!
				if (in_array($value['position'], $ad_formats) == TRUE)
				{
					continue;
				}

				$ad_placement['aswp_placement_type'] = isset($ad_placement['aswp_placement_type']) == FALSE ? '1' : $ad_placement['aswp_placement_type'];

				//	Set ad to selected position
				if ($ad_placement['aswp_placement_type'] === '1')
				{
					if ($value['position'] == 'aswp_top_left')
					{
						$content = '<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>'.$content;
						$ad_formats[] = 'aswp_top_left';
					}
					else if ($value['position'] == 'aswp_top_center')
					{
						$content = '<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>'.$content;
						$ad_formats[] = 'aswp_top_center';
					}
					else if ($value['position'] == 'aswp_top_right')
					{
						$content = '<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>'.$content;
						$ad_formats[] = 'aswp_top_right';
					}
					else if ($value['position'] == 'aswp_bottom_left')
					{
						$content = $content.'<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>';
						$ad_formats[] = 'aswp_bottom_left';
					}
					else if ($value['position'] == 'aswp_bottom_center')
					{
						$content = $content.'<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>';
						$ad_formats[] = 'aswp_bottom_center';
					}
					else if ($value['position'] == 'aswp_bottom_right')
					{
						$content = $content.'<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>';
						$ad_formats[] = 'aswp_bottom_right';
					}
					else
					{
						//	This one is a bit trickier.
						//	For adding ads in the middle of the content,
						//	we need to count <p> and divide them by 2 so that we get the middle.
						//	If no <p> are found or only 1, set them to bottom.

						$last_position = 0;
						$p_positions = array();

						$p_positions[] = strpos($content, '<p', $last_position);

						while (strpos($content, '<p', $last_position + 1) !== FALSE)
						{
							$p_positions[] = $last_position = strpos($content, '<p', $last_position + 1);
						}

						if (count($p_positions) == 0 || count($p_positions) == 1)
						{
							if ($value['position'] == 'aswp_middle_left')
							{
								$content = $content.'<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>';
								$ad_formats[] = 'aswp_middle_left';
							}
							else if ($value['position'] == 'aswp_middle_center')
							{
								$content = $content.'<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>';
								$ad_formats[] = 'aswp_middle_center';
							}
							else if ($value['position'] == 'aswp_middle_right')
							{
								$content = $content.'<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>';
								$ad_formats[] = 'aswp_middle_right';
							}
						}
						else
						{
							$middle_p = round(count($p_positions) / 2);

							if ($value['position'] == 'aswp_middle_left')
							{
								$content = substr_replace($content, '<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>', $p_positions[$middle_p], 0);
								$ad_formats[] = 'aswp_middle_left';
							}
							else if ($value['position'] == 'aswp_middle_center')
							{
								$content = substr_replace($content, '<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>', $p_positions[$middle_p], 0);
								$ad_formats[] = 'aswp_middle_center';
							}
							else if ($value['position'] == 'aswp_middle_right')
							{
								$content = substr_replace($content, '<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>', $p_positions[$middle_p], 0);
								$ad_formats[] = 'aswp_middle_right';
							}
						}
					}
				}
				//	Add ads after N number of paragraphs
				else if ($ad_placement['aswp_placement_type'] === '2')
				{
					$last_position = 0;
					$p_positions = array();

					while (strpos($content, '</p>', $last_position + 1) !== FALSE)
					{
						$p_positions[] = $last_position = strpos($content, '</p>', $last_position + 1);
					}

					$ad_placement['aswp_insert_after_n_paragraph'] = isset($ad_placement['aswp_insert_after_n_paragraph']) == FALSE ? '1' : $ad_placement['aswp_insert_after_n_paragraph'];

					//	Check if we have the required paragraphs as user set
					if ($ad_placement['aswp_insert_after_n_paragraph'] <= count($p_positions))
					{
						//	Get the position of the </p> that will be used
						$current_p_pos = $p_positions[$ad_placement['aswp_insert_after_n_paragraph'] - 1];
					}
					//	If the number is higher then the actual P tags, show the ads at the bottom
					else
					{
						$current_p_pos = FALSE;
					}

					//	Insert ads into content at selected position
					if ($value['position'] == 'aswp_paragraph_position_left')
					{
						$content = $current_p_pos !== FALSE ? substr_replace($content, '<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>', $current_p_pos, 0) : $content.'<div style="float: left;'.$margins.'">'.$value['js_code'].'</div>';;
						$ad_formats[] = 'aswp_paragraph_position_left';
					}
					else if ($value['position'] == 'aswp_paragraph_position_center')
					{
						$content = $current_p_pos !== FALSE ? substr_replace($content, '<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>', $current_p_pos, 0) : $content.'<div style="text-align: center;'.$margins.'">'.$value['js_code'].'</div>';;
						$ad_formats[] = 'aswp_paragraph_position_center';
					}
					else if ($value['position'] == 'aswp_paragraph_position_right')
					{
						$content = $current_p_pos !== FALSE ? substr_replace($content, '<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>', $current_p_pos, 0) : $content.'<div style="float: right;'.$margins.'">'.$value['js_code'].'</div>';;
						$ad_formats[] = 'aswp_paragraph_position_right';
					}
				}

				//	Check if we already added 3 ads, if so stop it!
				if ($ad_counter == 3)
				{
					break;
				}

				$ad_counter++;
			}
		}

		return $content;
	}

	/**
	 * Shortcode used for display ads manually.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	void
	 *
	 */
	function aswp_insert_ad($attr)
	{
		if (isset($attr['id']) == TRUE)
		{
			//	Get ad
			$ad = $this->wpdb->get_results($this->wpdb->prepare(
				"
				SELECT *
				FROM `".$this->wpdb->prefix.ASWP_DATA_TABLE."`
				WHERE `id` = '%d'
				", $attr['id']), ARRAY_A
			);

			if (isset($ad[0]) == TRUE)
			{
				//	Get random ads
				$useable_ads = array();
				$useable_ads = $this->get_random_ads($ad);

				//	Generate the ads js code
				$ads_with_js_code = $this->create_ads_js_code($useable_ads);

				//	If FALSE is returned, do nothing
				if ($ads_with_js_code === FALSE)
				{
					return '';
				}

				//	First unserialize the arrays
				$ad_placement = unserialize($ads_with_js_code[0]['ad_data']['ad_placement']);
				$ad_design = unserialize($ads_with_js_code[0]['ad_data']['ad_design']);

				//	Get the margins
				$margin_top = isset($ad_placement['aswp_margin_top']) == TRUE ? 'margin-top:'.$ad_placement['aswp_margin_top'].'px;' : '';
				$margin_right = isset($ad_placement['aswp_margin_right']) == TRUE ? 'margin-right:'.$ad_placement['aswp_margin_right'].'px;' : '';
				$margin_bottom = isset($ad_placement['aswp_margin_bottom']) == TRUE ? 'margin-bottom:'.$ad_placement['aswp_margin_bottom'].'px;' : '';
				$margin_left = isset($ad_placement['aswp_margin_left']) == TRUE ? 'margin-left:'.$ad_placement['aswp_margin_left'].'px;' : '';

				//	Combine all margins
				$margins = $margin_top.$margin_right.$margin_bottom.$margin_left;

				$ad_code = '<span style="display: inline-block;'.$margins.'">'.$ads_with_js_code[0]['js_code'].'</span>';

				return $ad_code;
			}
			else
			{
				return '';
			}
		}
		else
		{
			return '';
		}
	}

	/**
	 * Create top link ad
	 *
	 * @since	1.0
	 * @param	boolean	$output_div_style
	 * @param	boolean	$is_called_directly	Set to TRUE when calling this function directly in the theme using aswp_display_top_link_ad();
	 * @return	string
	 *
	 */
	function create_link_ad($output_div_style = FALSE, $is_called_directly = FALSE)
	{
		//	Load options
		$options = get_option('aswp_options');

		//	Disable ads for everyone
		if (isset($options['options']['aswp_disable_ads_everyone']) == TRUE && $options['options']['aswp_disable_ads_everyone'] === '1')
		{
			return '';
		}

		//	Disable ads for admins only
		if (isset($options['options']['aswp_disable_ads_admin']) == TRUE && $options['options']['aswp_disable_ads_admin'] === '1' && current_user_can('manage_options') == TRUE)
		{
			return '';
		}

		//	Enable ads for admins only
		if (isset($options['options']['aswp_enable_ads_admin']) == TRUE && $options['options']['aswp_enable_ads_admin'] === '1' && current_user_can('manage_options') == FALSE)
		{
			return '';
		}

		//	Check if we have publisher ID, if not, stop here
		if (isset($options['options']['aswp_publisher_id']) == FALSE || $options['options']['aswp_publisher_id'] === '')
		{
			return '';
		}

		//	Get just the top link ad options
		$tla_options = isset($options['top_link_ad']) == TRUE ? $options['top_link_ad'] : FALSE;

		//	If top link ad not found in options, do nothing
		if ($tla_options === FALSE)
		{
			return '';
		}
		else
		{
			//	If disabled is checked, stop here..
			if ((isset($tla_options['aswp_tla_enable']) == FALSE || $tla_options['aswp_tla_enable'] !== '1') && $is_called_directly == FALSE)
			{
				return '';
			}

			//	Are we on the home page?
			if (is_home() == TRUE && (isset($tla_options['aswp_show_tla_on_home']) == FALSE || $tla_options['aswp_show_tla_on_home'] !== '1'))
			{
				return '';
			}

			//	Are we on the categories page?
			if (is_category() == TRUE && (isset($tla_options['aswp_show_tla_on_category']) == FALSE || $tla_options['aswp_show_tla_on_category'] !== '1'))
			{
				return '';
			}

			//	Are we on the archive page?
			if (is_category() == TRUE && (isset($tla_options['aswp_show_tla_on_archive']) == FALSE || $tla_options['aswp_show_tla_on_archive'] !== '1'))
			{
				return '';
			}

			//	Which ad format is selected? If both, go random! :)
			$tla_format = array();
			if (isset($tla_options['aswp_tla_format_1']) == TRUE && $tla_options['aswp_tla_format_1'] === '468_15')
			{
				$tla_format[] = '468_15';
			}

			if (isset($tla_options['aswp_tla_format_2']) == TRUE && $tla_options['aswp_tla_format_2'] === '728_15')
			{
				$tla_format[] = '728_15';
			}

			if (count($tla_format) == 0)
			{
				return '';
			}

			//	This is the ad size that will be used!
			$tla_current_ad_format = array_rand($tla_format);

			//	If custom publisher ID and channel are set per ad only, use those!
			if (isset($tla_options['aswp_adv_publisher_id']) == TRUE && $tla_options['aswp_adv_publisher_id'] !== '')
			{
				$options['options']['aswp_publisher_id'] = $tla_options['aswp_adv_publisher_id'];
			}

			if (isset($tla_options['aswp_adv_custom_channel']) == TRUE && $tla_options['aswp_adv_custom_channel'] !== '')
			{
				$options['options']['aswp_custom_channel'] = $tla_options['aswp_adv_custom_channel'];
			}

			//	Calculate donations ratio
			if (isset($tla_options['aswp_donation']) == TRUE && $tla_options['aswp_donation'] !== '0')
			{
				if (intval($tla_options['aswp_donation']) >= 1 && intval($tla_options['aswp_donation']) <= 100)
				{
					$donation_rand = mt_rand(1, 100);

					if ($donation_rand <= intval($tla_options['aswp_donation']))
					{
						$options['options']['aswp_publisher_id'] = '0375353861302703';
						$options['options']['aswp_custom_channel'] = '';
					}
				}
				else
				{
					//	If the field is empty, set it to 5% by default
					$donation_rand = mt_rand(1, 100);

					if ($donation_rand <= 5)
					{
						$options['options']['aswp_publisher_id'] = '0375353861302703';
						$options['options']['aswp_custom_channel'] = '';
					}
				}
			}

			//	Separate the ad size numbers
			$ad_format_numbers = explode('_', $tla_format[$tla_current_ad_format]);

			if (isset($ad_format_numbers[0]) == FALSE || isset($ad_format_numbers[1]) == FALSE)
			{
				return '';
			}

			//	Colors
			$tla_options['aswp_border_color'] = isset($tla_options['aswp_border_color']) == TRUE ? $tla_options['aswp_border_color'] : 'ffffff';
			$tla_options['aswp_background_color'] = isset($tla_options['aswp_background_color']) == TRUE ? $tla_options['aswp_background_color'] : 'ffffff';
			$tla_options['aswp_title_color'] = isset($tla_options['aswp_title_color']) == TRUE ? $tla_options['aswp_title_color'] : '0000ff';

			$tla_div_style = '';
			if ($output_div_style == TRUE)
			{
				$tla_div_style = ' style="position: absolute; top: 0; left: 0; height: 17px; width: 100%; background-color: #'.$tla_options['aswp_background_color'].'; text-align: center;"';
			}

			$tla_ad_output = '
<div id="aswp_tla"'.$tla_div_style.'>
<script type="text/javascript"><!--
google_ad_client="ca-pub-'.trim($options['options']['aswp_publisher_id']).'";
google_ad_width='.$ad_format_numbers[0].';
google_ad_height='.$ad_format_numbers[1].';
google_ad_format="'.$ad_format_numbers[0].'x'.$ad_format_numbers[1].'_0ads_al";
google_ad_type="text";
google_ad_host_channel="'.trim($options['options']['aswp_custom_channel']).'";
google_color_border="'.$tla_options['aswp_border_color'].'";
google_color_bg="'.$tla_options['aswp_background_color'].'";
google_color_link="'.$tla_options['aswp_title_color'].'";
//--></script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>
				';

			return $tla_ad_output;
		}
	}


	/**
	 *
	 *
	 * @since	1.1
	 * @param	void
	 * @return	boolean/string
	 *
	 */
	function footer()
	{
		$output = '';

		$output = $this->create_link_ad(TRUE);

		echo $output;
	}
}
?>