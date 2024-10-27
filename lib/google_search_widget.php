<?php
/**
 *	Contains code for the widget that shows the google search box in the sidebars.
 *
 */
class aswp_google_search_widget extends WP_Widget
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
	 		'aswp_google_search_widget',
			'Google Search',
			array(
				'description' => __('Adds Google Search to the sidebar', ASWP_UNIQUE_NAME),
				'classname' => 'widget_google_search_ad'
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
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;

		if (!empty($title))
			echo $before_title . $title . $after_title;

		//	Load options
		$options = get_option('aswp_options');

		//	If options don't exist, output nothing!
		if (isset($options['google_search']) == TRUE)
		{
			$gs_options = $options['google_search'];

			echo stripslashes($gs_options['aswp_google_box_code']);
		}
		else
		{
			echo __('To set up Google Search, go to: '.ASWP_NAME.' -> Google Search', ASWP_UNIQUE_NAME);
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
		if (isset($instance['title']) == FALSE)
		{
			$instance['title'] = '';
		}

		echo '<p><label>'.__('Title:', ASWP_UNIQUE_NAME).' <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($instance['title']).'"/></label></p>';

		echo '<p>'.__('To set up Google Search, go to: '.ASWP_NAME.' -> Google Search', ASWP_UNIQUE_NAME).'</p>';
	}
}
?>