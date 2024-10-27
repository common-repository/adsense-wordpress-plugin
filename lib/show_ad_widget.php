<?php
/**
 *	Contains code for the widget that shows ads in the sidebars.
 *
 */
class aswp_show_ad_widget extends WP_Widget
{
	/**
	 * Constructor
	 *
	 * @since	1.1
	 *
	 */
	function __construct()
	{
		parent::__construct(
	 		'aswp_show_ad_widget',
			'Show Ads',
			array(
				'description' => __('Adds selected ads to the sidebar', ASWP_UNIQUE_NAME),
				'classname' => 'widget_aswp_show_ad'
			)
		);
	}

	/**
	 * Front-end output.
	 *
	 * @since	1.1
	 * @param	array	$args
	 * @param	array	$instance
	 * @return	void
	 *
	 */
	function widget($args, $instance)
	{
		global $wpdb;

		extract($args);
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;

		if (!empty($title))
			echo $before_title . $title . $after_title;

		if (isset($instance['id']) == TRUE)
		{
			//	Get ad
			$ad = $wpdb->get_results($wpdb->prepare(
				"
				SELECT *
				FROM `".$wpdb->prefix.ASWP_DATA_TABLE."`
				WHERE `id` = '%d'
				", $instance['id']), ARRAY_A
			);

			if (isset($ad[0]) == TRUE)
			{
				//	Get random ads
				$useable_ads = array();
				$useable_ads = aswp_show_ads::get_random_ads($ad, TRUE);

				//	Generate the ads js code
				$ads_with_js_code = aswp_show_ads::create_ads_js_code($useable_ads);

				//	If FALSE is returned, do nothing
				if ($ads_with_js_code === FALSE)
				{
					return '';
				}

				if (count($ads_with_js_code) > 0)
				{
					foreach ($ads_with_js_code as $value)
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

						$ad_code = '<div style="'.$margins.'">'.$ads_with_js_code[0]['js_code'].'</div>';

						echo $ad_code;
					}
				}
				else
				{
					echo '';
				}
			}
			else
			{
				echo '';
			}
		}
		else
		{
			echo '';
		}

		echo $after_widget;
	}

	/**
	 * Save form values.
	 *
	 * @since	1.1
	 * @param	array	$new_instance
	 * @param	array	$old_instance
	 * @return	void
	 *
	 */
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['id'] = strip_tags($new_instance['id']);

		return $instance;
	}

	/**
	 * Back-end output.
	 *
	 * @since	1.1
	 * @param	array	$instance
	 * @return	void
	 *
	 */
	function form($instance)
	{
		global $wpdb;

		if (isset($instance['id']) == FALSE)
		{
			$instance['id'] = '';
		}

		if (isset($instance['title']) == FALSE)
		{
			$instance['title'] = '';
		}

		echo '<p><label>'.__('Title:', ASWP_UNIQUE_NAME).' <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($instance['title']).'"/></label></p>';

		echo '<p>';
		echo '<label for="'.$this->get_field_id('id').'">'.__('Select Ad:', ASWP_UNIQUE_NAME).'</label>';
		echo '<select class="widefat" name="'.$this->get_field_name('id').'" id="'.$this->get_field_id('id').'">';

		//	Get all ads
		$list_of_ads = $wpdb->get_results("
			SELECT *
			FROM `".$wpdb->prefix.ASWP_DATA_TABLE."`
			ORDER BY `date_created` DESC", ARRAY_A);

		if (count($list_of_ads) > 0)
		{
			//	Loop over the ads
			foreach ($list_of_ads as $value)
			{
				$ad_selected = '';
				if ($instance['id'] == $value['id'])
				{
					$ad_selected = ' selected="selected"';
				}

				echo '<option value="'.$value['id'].'"'.$ad_selected.'>'.$value['ad_name'].'</value>';
			}
		}

		echo '</select>';
		echo '</p>';
	}
}
?>