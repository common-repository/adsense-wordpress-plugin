<?php
/*
Plugin Name: AdSense WP Plugin
Plugin URI: http://www.AdSenseWPPlugin.com
Description: AdSense WP Plugin provides a quick an easy way for you to create and manage AdSense across your entire Wordpress site.
Version: 1.2.4
Author: Adsense-Plugin
Author URI: http://www.AdSenseWPPlugin.com
License: GPL2
*/

/*	Copyright 2012  AdSense WP Plugin  (email : admin@AdSenseWPPlugin.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb;

//	Define some stuff for plugin..
define('ASWP_NAME', 'AdSense WP Plugin');
define('ASWP_VERSION', '1.2.4');
define('ASWP_CURRENT_TABLE_VERSION', '1');
define('ASWP_DATA_TABLE', 'aswp_data');
define('ASWP_UNIQUE_NAME', 'aswp');
define('ASWP_FILE', plugin_dir_path(__FILE__).'adsense-wordpress-plugin.php');
define('ASWP_BASENAME_FOLDER', basename(dirname(__FILE__)));
define('ASWP_BASENAME', plugin_basename(__FILE__));
define('ASWP_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));

//	Core functions
require_once(plugin_dir_path(__FILE__).'lib/core.php');
if (class_exists('aswp_core') == TRUE)
{
	new aswp_core($wpdb);
}

//	Include admin classes only in admin area of WP
if (is_admin() == TRUE)
{
	//	HTML helper functions
	require_once(plugin_dir_path(__FILE__).'lib/html.php');
	if (class_exists('aswp_html') == TRUE)
	{
		new aswp_html();
	}

	//	Loads the file with class that generates the main page
	require_once(plugin_dir_path(__FILE__).'lib/ads.php');

	//	Loads the file with class that generates the create new ad page
	require_once(plugin_dir_path(__FILE__).'lib/top_link_ad.php');

	//	Loads the file with class that generates the top link ad page
	require_once(plugin_dir_path(__FILE__).'lib/create_ad.php');

	//	Loads the file with class that generates the google search page
	require_once(plugin_dir_path(__FILE__).'lib/google_search.php');

	//	Loads the file with class that generates the privacy policy page
	require_once(plugin_dir_path(__FILE__).'lib/privacy_policy.php');

	//	Loads the file with class that generates the options page
	require_once(plugin_dir_path(__FILE__).'lib/options.php');
}
else
{
	//	Load only when not in wp admin area
	//	Adds the ads to the content stuff
	require_once(plugin_dir_path(__FILE__).'lib/show_ads.php');
	if (class_exists('aswp_show_ads') == TRUE)
	{
		new aswp_show_ads($wpdb);
	}

	//	Function that can be called directly from a theme and will display the top link ad
	function aswp_display_top_link_ad()
	{
		echo aswp_show_ads::create_link_ad(FALSE, TRUE);
	}

	//	Google search shortcode and stuff :)
	require_once(plugin_dir_path(__FILE__).'lib/google_search_output.php');
	if (class_exists('aswp_google_search_output') == TRUE)
	{
		new aswp_google_search_output();
	}

	//	Function that can be called directly from a theme and will display the google search box
	function aswp_display_google_search_box()
	{
		echo aswp_google_search_output::create_search_box();
	}
}

//	Loaded in both front and back-end.
//	Load display ads widget.
require_once(plugin_dir_path(__FILE__).'lib/show_ad_widget.php');
if (class_exists('aswp_show_ad_widget') == TRUE)
{
	/**
	 * Register show ad widget.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function aswp_register_show_ad_widget()
	{
		register_widget('aswp_show_ad_widget');
	}

	//	Register the widgets
	add_action('widgets_init', 'aswp_register_show_ad_widget');
}

//	Load display ads widget.
require_once(plugin_dir_path(__FILE__).'lib/google_search_widget.php');
if (class_exists('aswp_google_search_widget') == TRUE)
{
	/**
	 * Register google search widget.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	void
	 *
	 */
	function aswp_register_google_search_widget()
	{
		register_widget('aswp_google_search_widget');
	}

	//	Register the widgets
	add_action('widgets_init', 'aswp_register_google_search_widget');
}
?>