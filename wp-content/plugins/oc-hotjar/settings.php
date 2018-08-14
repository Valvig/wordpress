<?php
if(!class_exists('WP_HotJar_Settings'))
{
	class WP_HotJar_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
          add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register your plugin's settings
        	register_setting('hotjar-group', 'site_identifier', array(&$this, 'site_identifier_validation') );


        	// add your settings section
        	add_settings_section(
        	    'hotjar-section',
        	    'HotJar Settings',
        	    array(&$this, 'settings_section_hotjar'),
        	    'hotjar'
        	);

        	// add your setting's fields
            add_settings_field(
                'hotjar-site_identifier',
                'Site Identifier',
                array(&$this, 'settings_field_input_text'),
                'hotjar',
                'hotjar-section',
                array(
                    'field' => 'site_identifier'
                )
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate

        public function settings_section_hotjar()
        {
            // Think of this as help text for the section.
            echo 'Each site in HotJar has a 6-digit Site Identifier. Please enter this in the field below.';
        }

				/**
         * This function validates site_identifier (needs to be numeric)
         */
				public function site_identifier_validation( $input )
				{

						if ( is_numeric ($input) ) {
							$output = $input;
						} else {
							add_settings_error(
								'site-identifier',
								esc_attr( 'site-identifier' ),
								"Site Identifier must be numeric (example 123456)",
								"error"
							);
						}

						return $output;
				}

        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
        } // END public function settings_field_input_text($args)

        /**
         * add a menu
         */
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
        	add_options_page(
        	    'HotJar Settings',
        	    'HotJar',
        	    'manage_options',
        	    'hotjar',
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END public function add_menu()

        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}

        	// Render the settings template
        	include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WP_HotJar_Settings
} // END if(!class_exists('WP_HotJar_Settings'))
