<?php
/**
 *	Contains functions for google search
 *
 */

class aswp_google_search_output
{
	/**
	 * Constructor
	 *
	 * @since	1.1
	 *
	 */
	function __construct()
	{
		//	Short code
		add_shortcode('aswp_gsr', array($this, 'aswp_gs_results'));

		//	This will replace the default Wordpress search with google search box if the "searchform.php" file is not included
		//	in the current theme.
		add_filter('get_search_form', array($this, 'replace_wp_search'));
	}

	/**
	 * Shortcode used for display the Google Search results.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function aswp_gs_results()
	{
		//	Load options
		$options = get_option('aswp_options');

		//	If options don't exist, output nothing!
		if (isset($options['google_search']) == TRUE)
		{
			$gs_options = $options['google_search'];

			return stripslashes($gs_options['aswp_google_search_results_code']);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Outputs the search box. Used by calling this function directly from a theme.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function create_search_box()
	{
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
			echo '';
		}
	}

	/**
	 * Replaces the default WP search box by Google Search box.
	 * This only works if the theme doesn't have "searchform.php" file.
	 *
	 * @since	1.0
	 * @param	string	$form
	 * @return	string
	 *
	 */
	function replace_wp_search($form)
	{
		//	Load options
		$options = get_option('aswp_options');

		//	If options don't exist, output nothing!
		if (isset($options['google_search']) == TRUE && isset($options['google_search']['aswp_replace_search']) == TRUE && $options['google_search']['aswp_replace_search'] === '1')
		{
			$gs_options = $options['google_search'];

			$form = stripslashes($gs_options['aswp_google_box_code']);

			return $form;
		}
		else
		{
			return $form;
		}
	}
}
?>