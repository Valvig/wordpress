<?php
/*
* Plugin Name: Native Emoji
* Plugin URI: http://native-emoji.davabuu.com/
* Description: This is not just a plugin, this is the plugin for use <cite>emoji</cite> in a native way in your <cite>posts and comments</cite>. When activated you will see a new button in your wordpress editor or comments box, from there you will be able to include more than 2,000 emojis.
* Version: 3.0
* Author: Daniel Brandenburg
* Author URI: http://davabuu.net
* Text Domain: native-emoji
* Domain Path: /languages
*/

// Define Plugin Class
class WP_nep_Native_Emoji{

  	// Constructor
	function __construct() {
						
		// Actions
        add_action( 'admin_notices', array( $this, 'nep_activation_msg' ) );
		add_action( 'plugins_loaded', array( $this, 'nep_localize_plugin' ) );
        add_action( 'admin_init', array( $this, 'nep_resgiter_plugin_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'nep_register_and_enqueue_admin_files' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'nep_register_and_enqueue_files' ) );
        add_action( 'admin_menu', array( $this, 'nep_add_options_page' ) );
        foreach ( array('post.php','post-new.php') as $hook ) {                                    
			add_action( "admin_head-$hook", array( $this, 'nep_plugin_js_vars' ) );
		}		
        
        // Filters
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'nep_link_actions' ) );
		add_filter( 'mce_buttons', array( $this, 'nep_tinymce_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'nep_tinymce_plugin' ) );
        add_filter( 'comment_form_field_comment', array( $this, 'nep_comments_template' ) );
		
		// Activation and desactivation hooks
		register_activation_hook( __FILE__, array( $this, 'nep_emoji_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'nep_emoji_uninstall' ) );
		
	}
    
    // Display Activation Message
	function nep_activation_msg() {	
	
		if(is_plugin_active('native-emoji/index.php') && !get_option('nep_native_emoji_active')){
			// Add plugin options
			add_option( 'nep_native_emoji_active', 'true' );
			
			// Display Message
			$settings_link = '<a href="'.get_admin_url().'options-general.php?page=nep_native_emoji">'.__('configure your settings', 'native-emoji').'</a>';
			echo '<div id="message" class="updated notice is-dismissible"><p>';
			printf(
				__( 'Thanks for installing Emoji Native Plugin, before using you must you must %1$s', 'native-emoji' ),
				$settings_link
			);
			echo '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Discard this notice', 'native-emoji').'</span></button></div>';
		}
		
	}
    
    // Plugin Link Actions
	function nep_link_actions( $links ) {		
		$mylinks = array(
            '<a href="' . admin_url( 'options-general.php?page=nep_native_emoji' ) . '">'.__('Settings', 'native-emoji').'</a>',
        );
        return array_merge( $links, $mylinks );
	}
	
	// Localize The Plugn
	function nep_localize_plugin() {		
		load_plugin_textdomain( 'native-emoji', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );	
	}
    
    // Register Plugin Settings
    function nep_resgiter_plugin_settings() {
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_admin_activation' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_close_panel' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_comments_activation' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_site_use_jquery' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_show_on_mobile' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_panel_color' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_panel_position' );
        register_setting( 'nep_native_emoji_settings', 'nep_plugin_close_panel_comments' );
    }
    
    // Register and enqueue admin CSS and JS files
	function nep_register_and_enqueue_admin_files(){
        global $pagenow;
        $screen = get_current_screen();
		// Register required files
		wp_register_style( 'nep_native_emoji_admin',  plugins_url('/css/native_emoji_admin.css',__FILE__), false, '3.0', 'all' );
		// Enqueue required files
        if($pagenow == 'post.php' && get_option('nep_plugin_admin_activation') || $pagenow == 'post-new.php' && get_option('nep_plugin_admin_activation')){
            wp_enqueue_style( 'nep_native_emoji_admin' );
            wp_enqueue_script( 'jquery' );
        }
        if($screen->id == 'settings_page_nep_native_emoji'){
            wp_enqueue_style( 'nep_native_emoji_admin' );
        }
	}
    
    // Register and enqueue front end CSS and JS files
    function nep_register_and_enqueue_files (){
        if(!get_option('nep_plugin_comments_activation'))
            return;
        
        // Register required files
        wp_register_style( 'nep_native_emoji',  plugins_url('/css/native_emoji.css',__FILE__), false, '3.0', 'all' );
        wp_register_script( 'nep_native_emoji', plugins_url('/js/native_emoji.js',__FILE__), 'jquery', '3.0', true );
        
        // Get Frequently used emojis
        global $wpdb;
        
        $fu_emojis_codes    = array();
        $plugin_url         = plugins_url( '/', __FILE__ );        
        $table_name         = $wpdb->prefix . 'nep_native_emoji';
        $uid                = get_current_user_id(); 
        
        $fu_emojis = $wpdb->get_results( "SELECT * FROM $table_name WHERE uid = '$uid' ORDER BY time DESC LIMIT 0,42" );
        
        foreach($fu_emojis as $emoji){
            $fu_emojis_codes[] = array('id' => $emoji->btn_id, 'class' => $emoji->class, 'code' => $emoji->code);
        }
        
        //Localize Script
        $nep_js_var = array(
            'nep_name'		   	    => __('Native Emoji', 'native-emoji'),
            'nep_frequently_used'	=> __('Frequently Used', 'native-emoji'),
            'nep_smileys_people'	=> __('Smileys & People', 'native-emoji'),
            'nep_animals_nature'    => __('Animals & Nature', 'native-emoji'),
            'nep_food_drink'	   	=> __('Food & Drink', 'native-emoji'),
            'nep_activity_sports'	=> __('Activity & Sports', 'native-emoji'),
            'nep_travel_places'     => __('Travel & Places', 'native-emoji'),
            'nep_objects'           => __('Objects', 'native-emoji'),
            'nep_symbols'           => __('Symbols', 'native-emoji'),
            'nep_flags' 			=> __('Flags', 'native-emoji'),
            'nep_yellow' 			=> __('No Skin Tone', 'native-emoji'),
            'nep_pale' 			    => __('Light Skin Tone', 'native-emoji'),
            'nep_cream' 			=> __('Medium Light Skin Tone', 'native-emoji'),
            'nep_moderate_brown' 	=> __('Medium Skin Tone', 'native-emoji'),
            'nep_dark_brown' 		=> __('Medium Dark Skin Tone', 'native-emoji'),
            'nep_black' 			=> __('Dark Skin Tone', 'native-emoji'),            
            'nep_url'				=> $plugin_url,
            'nep_close'      		=> __('Close')
        );
        wp_localize_script( 'nep_native_emoji', 'nep_plugin_vars', $nep_js_var );
        wp_localize_script( 'nep_native_emoji', 'nep_frequently_used', $fu_emojis_codes );
        
        // Enqueue required files
        if ( comments_open() || get_comments_number() ) {
            wp_enqueue_style( 'nep_native_emoji' );
            if(!get_option('nep_plugin_site_use_jquery')){
                wp_enqueue_script( 'jquery' );
            }
            wp_enqueue_script( 'nep_native_emoji' );
        }
    }
    
    // Add Options Page
    function nep_add_options_page(){
        add_options_page( __('Native Emoji', 'native-emoji'), __('Native Emoji', 'native-emoji'), 'activate_plugins', 'nep_native_emoji', array( $this, 'nep_options_page' ));
    }
    
    // Options Page
    function nep_options_page(){
        ?>
<div class="wrap">
    <h1><?php _e('Native Emoji', 'native-emoji');?></h1>
    <h2 class="nav-tab-wrapper">
		<a class="nav-tab nep-nav-tab-right nep-nav-tab-donate" href="//paypal.me/danybranding" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-heart"></span> <?php _e('Donate', 'native-emoji');?></a>
		<a class="nav-tab nep-nav-tab-right nep-nav-tab-review" href="//wordpress.org/support/plugin/native-emoji/reviews/" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-star-filled"></span> <?php _e('Review', 'native-emoji');?></a>
        <a class="nav-tab nep-nav-tab-right nep-nav-tab-live-demo" href="//native-emoji.davabuu.com" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-visibility"></span> <?php _e('Live Demo', 'native-emoji');?></a>
	</h2>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'nep_native_emoji_settings' ); ?>
        <?php do_settings_sections( 'nep_native_emoji_settings' ); ?>
        <table class="form-table">            
            <tr>
                <th scope="row"><?php _e('Admin editor','native-emoji');?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Enable plugin on admin editor','native-emoji');?></span></legend>
                        <label>
                            <input type="checkbox" name="nep_plugin_admin_activation" value="1" <?php checked(esc_attr( get_option('nep_plugin_admin_activation') ), '1', true );?>>
                            <?php _e('Enable','native-emoji');?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Other admin editor settings','native-emoji');?></th>
                <td>                                    
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Close panel after insert an emoji','native-emoji');?></span></legend>
                        <label>
                            <input type="checkbox" name="nep_plugin_close_panel" value="1" <?php checked(esc_attr( get_option('nep_plugin_close_panel') ), 1, true );?>>
                            <?php _e('Close panel after insert an emoji','native-emoji');?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row" colspan="2"><hr></th>                
            </tr>
            <tr>
                <th scope="row"><span class="dashicons dashicons-warning"></span> <?php _e('Notices','native-emoji');?></th>
                <td>
                    <p class="description"><?php _e('Make sure to check the box if your website uses jQuery, otherwise the plugin may cause errors in your website','native-emoji');?></p>
                    <p class="description"><?php _e('The plugin tries to preserve the css properties of the comments box, in case something has been omitted, use your custom css under the tag','native-emoji');?> <strong>#nep_fake_textarea</strong></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Front end comments','native-emoji');?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Enable plugin on front end comments','native-emoji');?></span></legend>
                        <label for="users_can_register">
                            <input type="checkbox" name="nep_plugin_comments_activation" value="1" <?php checked(esc_attr( get_option('nep_plugin_comments_activation') ), '1', true );?>>
                            <?php _e('Enable','native-emoji');?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Other comments settings','native-emoji');?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Does your website uses jQuery library?','native-emoji');?></span></legend>
                        <label for="users_can_register">
                            <input type="checkbox" name="nep_plugin_site_use_jquery" value="1" <?php checked(esc_attr( get_option('nep_plugin_site_use_jquery') ), '1', true );?>>
                            <?php _e('My website uses jQuery library','native-emoji');?>
                        </label>                        
                    </fieldset>
                    <br>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Do you want to display the plugin on mobile devices?','native-emoji');?></span></legend>
                        <label for="users_can_register">
                            <input type="checkbox" name="nep_plugin_show_on_mobile" value="1" <?php checked(esc_attr( get_option('nep_plugin_show_on_mobile') ), 1, true );?>>
                            <?php _e('Display on mobile devices','native-emoji');?>
                        </label>                        
                    </fieldset>
                    <br>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Close panel after insert an emoji','native-emoji');?></span></legend>
                        <label>
                            <input type="checkbox" name="nep_plugin_close_panel_comments" value="1" <?php checked(esc_attr( get_option('nep_plugin_close_panel_comments') ), 1, true );?>>
                            <?php _e('Close panel after insert an emoji','native-emoji');?>
                        </label>
                    </fieldset>
                    <br>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Comments emoji panel color','native-emoji');?></span></legend>
                        <label><strong><?php _e('Panel color','native-emoji');?></strong></label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_color" value="light" <?php checked(esc_attr( get_option('nep_plugin_panel_color', 'light') ), 'light', true );?>>
                            <?php _e('Light','native-emoji');?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_color" value="dark" <?php checked(esc_attr( get_option('nep_plugin_panel_color') ), 'dark', true );?>>
                            <?php _e('Dark','native-emoji');?>
                        </label>
                    </fieldset>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Comments emoji panel position','native-emoji');?></span></legend>
                        <label><strong><?php _e('Panel position','native-emoji');?></strong></label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_position" value="right_bottom" <?php checked(esc_attr( get_option('nep_plugin_panel_position', 'right_bottom') ), 'right_bottom', true );?>>
                            <?php _e('Right Bottom','native-emoji');?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_position" value="right_top" <?php checked(esc_attr( get_option('nep_plugin_panel_position') ), 'right_top', true );?>>
                            <?php _e('Right Top','native-emoji');?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_position" value="left_bottom" <?php checked(esc_attr( get_option('nep_plugin_panel_position') ), 'left_bottom', true );?>>
                            <?php _e('Left Bottom','native-emoji');?>
                        </label>
                        <br>
                        <label>
                            <input type="radio" name="nep_plugin_panel_position" value="left_top" <?php checked(esc_attr( get_option('nep_plugin_panel_position') ), 'left_top', true );?>>
                            <?php _e('Left Top','native-emoji');?>
                        </label>
                    </fieldset>
                </td>
            </tr>            
        </table>

        <?php submit_button(); ?>

    </form>
</div>
        <?php
    }        
    
    // Localize tinymce and add vars to js plugin
	function nep_plugin_js_vars() { 
        if(!get_option('nep_plugin_admin_activation'))
            return;
        
        // Add inline script
        global $wpdb, $locale; $i= 0;
        $fu_emojis_codes    = array();
        $plugin_url         = plugins_url( '/', __FILE__ );
        $table_name         = $wpdb->prefix . 'nep_native_emoji';
        $uid                = get_current_user_id(); 
        $fu_emojis          = $wpdb->get_results( "SELECT * FROM $table_name WHERE uid = '$uid' ORDER BY time DESC LIMIT 0,42");
        $close              = (get_option('nep_plugin_close_panel'))? 'true' : 'false';
        
        foreach($fu_emojis as $emoji){
            $fu_emojis_codes[] = '{"id":"'.$emoji->btn_id.'", "class":"'.$emoji->class.'", "code":"'.html_entity_decode($emoji->code).'"}';
        }
        $inlineScript = "<!-- TinyMCE Native Emoji Plugin -->\n";
        $inlineScript .= "<script type='text/javascript'>";
        $inlineScript .= "var nep_plugin_vars = {";  
            $inlineScript .= "'nep_name':'" . __('Native Emoji', 'native-emoji') . "',";
            $inlineScript .= "'nep_insert_emoji':'" . __('Insert Emoji', 'native-emoji') . "',";
            $inlineScript .= "'nep_frequently_used':'" . __('Frequently Used', 'native-emoji') . "',";
            $inlineScript .= "'nep_smileys_people':'" . __('Smileys & People', 'native-emoji') . "',";
            $inlineScript .= "'nep_animals_nature':'" . __('Animals & Nature', 'native-emoji') . "',";
            $inlineScript .= "'nep_food_drink':'" . __('Food & Drink', 'native-emoji') . "',";
            $inlineScript .= "'nep_activity_sports':'" . __('Activity & Sports', 'native-emoji') . "',";
            $inlineScript .= "'nep_travel_places':'" . __('Travel & Places', 'native-emoji') . "',";
            $inlineScript .= "'nep_objects':'" . __('Objects', 'native-emoji') . "',";
            $inlineScript .= "'nep_symbols':'" . __('Symbols', 'native-emoji') . "',";
            $inlineScript .= "'nep_flags':'" . __('Flags', 'native-emoji') . "',";
            $inlineScript .= "'nep_yellow':'" . __('No Skin Tone', 'native-emoji') . "',";
            $inlineScript .= "'nep_pale':'" . __('Light Skin Tone', 'native-emoji') . "',";
            $inlineScript .= "'nep_cream':'" . __('Medium Light Skin Tone', 'native-emoji') . "',";
            $inlineScript .= "'nep_moderate_brown':'" . __('Medium Skin Tone', 'native-emoji') . "',";
            $inlineScript .= "'nep_dark_brown':'" . __('Medium Dark Skin Tone', 'native-emoji') . "',";
            $inlineScript .= "'nep_black':'" . __('Dark Skin Tone', 'native-emoji') . "',";   
            $inlineScript .= "'nep_url':'". $plugin_url . "',";
            $inlineScript .= "'nep_close_panel':" . $close . ",";
            $inlineScript .= "'nep_close':'" . __('Close') . "',";
            $inlineScript .= "'nep_frequently_codes':[" . implode(',',$fu_emojis_codes) . "]";
        $inlineScript .= "};";
        $inlineScript .= "</script>\n";
        $inlineScript .= "<!-- TinyMCE Native Emoji Plugin -->\n";
        echo $inlineScript;
	} 
	
	// Register TinyMCE Button			
	function nep_tinymce_button( $buttons ) {
        if(!get_option('nep_plugin_admin_activation'))
            return $buttons;
        
        array_push($buttons, 'separator', 'nep_native_emoji');                    
        return $buttons;
	}
	
	// Register TinyMCE Pluglin
	function nep_tinymce_plugin( $plugin_array ) {
        if(!get_option('nep_plugin_admin_activation'))
            return;                
        $plugin_array['nep_native_emoji'] = plugins_url('/js/native_emoji_tinymce-plugin.js',__FILE__);
        return $plugin_array;        
	}
    
    // Comments Template
    function nep_comments_template( $field ) {
        if(!get_option('nep_plugin_comments_activation'))
            return $field;                       
    
        $theme          = get_option('nep_plugin_panel_color');
        $mobile         = (get_option('nep_plugin_show_on_mobile'))? 'true' : 'false';
        $close          = (get_option('nep_plugin_close_panel_comments'))? 'true' : 'false';
        $position       = get_option('nep_plugin_panel_position');
        $data_settings  = "{'theme':'". $theme ."', 'showOnMobile':" . $mobile . ", 'close':" . $close . ", 'position':'" . $position . "'}";
       
        $btn = "\t" .'<a id="nep_call_panel" class="nep_' . $theme . ' nep_'. $position .'" data-emoji-panel="'. $data_settings .'" rel="nofollow noreferrer" title="' . __('Insert Emoji', 'native-emoji') . '"></a>' . "\n";
        $fake = "\t" .'<div id="nep_fake_textarea" contenteditable="true" data-emoji-receptor></div>' . "\n";
        $replace = "\n" . '<div id="nep_container">' . "\n" . $btn . $fake . "\t" . '<textarea $1 data-emoji-textarea></textarea>'. "\n" .'</div>' . "\n";
        $output = preg_replace('/<textarea\s(.*?)><\/textarea>/', $replace, $field);        
        
        return $output;
    }
    
	// Install the plugin
    function nep_emoji_install() {
		// Required files
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// Create Frecuently Used Table
		global $wpdb;
		$table = $wpdb->prefix . 'nep_native_emoji';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            btn_id varchar(255) NOT NULL,
            class varchar(255) NOT NULL,
			code varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,			
			uid mediumint(9) NOT NULL,
			UNIQUE KEY id (id)
		)  $charset_collate;";			
		dbDelta( $sql );
    }
	
	// Uninstall the plugin
    function nep_emoji_uninstall() {
		global $wpdb;
        $table = $wpdb->prefix . 'nep_native_emoji';
		// Delete Plugin Options
		delete_option( 'nep_native_emoji_active' );
        delete_option( 'nep_plugin_admin_activation' );
        delete_option( 'nep_plugin_close_panel' );  
        delete_option( 'nep_plugin_comments_activation' );  
        delete_option( 'nep_plugin_site_use_jquery' );
        delete_option( 'nep_plugin_show_on_mobile' ); 
        delete_option( 'nep_plugin_panel_color' );
        delete_option( 'nep_plugin_panel_position' );
        delete_option( 'nep_plugin_close_panel_comments' );  
		// Delete Frecuently Used Table
		$wpdb->query("DROP TABLE IF EXISTS $table");
    }

}

new WP_nep_Native_Emoji();