<?php
/*
Plugin Name: HotJar
Plugin URI: <to be determined>
Description: A simple way to get HotJar integrated into your WordPress site
Version: 0.2
Author: Owen Cutajar
Author URI: http://www.u-g-h.com
License: GPL2
*/
/*
Copyright 2017  Owen Cutajar  (email : owen@cutajar.net)

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

if(!class_exists('WP_HotJar'))
{
	class WP_HotJar
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// Initialize Settings
			require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			$WP_HotJar_Settings = new WP_HotJar_Settings();

			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));

			add_action('wp_head', array( $this, 'add_hotjar_js' ));
		} // END public function __construct

		/**
		 * Add Hotjar code to page header if a site has been defined
		 */
		public static function add_hotjar_js()
		{
				$site_identifier = get_option("site_identifier");

				if ($site_identifier) {

					echo <<<HOTJAR
<!-- HotJar Tracking Code -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:$site_identifier,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
<!-- End of HotJar Tracking Code -->
HOTJAR;

				}

		} // END public static function add_hotjar_js


		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			// Do nothing
		} // END public static function activate

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			// Do nothing
		} // END public static function deactivate

		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=hotjar">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}


	} // END class WP_Plugin_Template
} // END if(!class_exists('WP_Plugin_Template'))

if(class_exists('WP_HotJar'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WP_HotJar', 'activate'));
	register_deactivation_hook(__FILE__, array('WP_HotJar', 'deactivate'));

	// instantiate the plugin class
	$wp_hotjar = new WP_HotJar();

}
