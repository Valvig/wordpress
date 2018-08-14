<?php
/**
 * CF7 Skins Admin Class
 * 
 * Implement all functionality for CF7 Skins Admin on CF7 plugin page.
 * 
 * @package cf7skins
 * @author Neil Murray
 * @since 0.0.1
 */ 


class CF7_Skins_Admin {
	
	/**
     * Class constructor
	 * 
     * @since 0.0.1
     */	
    function __construct() {
	
		/**
		 * Return if current user does not have capability access
		 * @since 1.1.1
		 */		
		if ( ! current_user_can( WPCF7_ADMIN_READ_WRITE_CAPABILITY ) )
			return;
		
		// Create the metabox for CF7 Skins
		add_action( 'wpcf7_add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
		add_action( 'wpcf7_admin_footer', array( &$this, 'add_meta_boxes_42' ) );
		
		// Push the styles and scripts to the admin header
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
		
		add_filter( 'wpcf7_copy', array( $this, 'store_copy_id' ), 1, 2 ); // @since 1.2
		
		// Action hooks to store selected template and style while updating or creating new CF7
		add_action( 'wpcf7_after_create', array( &$this, 'skin_update' ) );
		add_action( 'wpcf7_after_create', array( &$this, 'cf7skins_copy' ) ); // @since 1.2
		add_action( 'wpcf7_after_update', array( &$this, 'skin_update' ) );
		add_action( 'wpcf7_admin_footer', array( $this, 'cf7skins_admin_footer' ) ); // @since 1.1.1			
	}	
	
	
	/**
     * Store current form id while duplicating
	 * 
     * @param $new (object) new created contact form
     *		  $wpcf7 (class) WPCF7_ContactForm
     * @since 1.2
     */	
	function store_copy_id( $new, $wpcf7 ) {		
		update_option( '_cf7skins_copy_id', $wpcf7->id() ); // store copied ID to database
		return $new;
	}
	
	
	/**
     * Update current form post meta data with selected style and/or template
	 *
     * Get post id (contact form 7 form id) in $_POST['cf7s-template'] and $_POST['cf7s-style']
     * Post meta name 'cf7s_template' for template and 'cf7s_style' for style
	 * 
	 * @action cf7s_update
     * @param $cf7 is the contact form 7 object data
     * @since 0.0.1
     */	
	function skin_update( $cf7 ) {
		// Get the current contact form 7 id
		$form_id = CF7_Skins_Contact::get_form_id( $cf7 );
		
		// Update the post meta
		if( isset( $_POST['cf7s-template'] ) )
			update_post_meta( $form_id, 'cf7s_template', esc_attr( $_POST['cf7s-template'] ) );
		
		if( isset( $_POST['cf7s-style'] ) )
			update_post_meta( $form_id, 'cf7s_style', esc_attr( $_POST['cf7s-style'] ) );
		
		
		// Update metabox state
		if( isset( $_POST['cf7s-postbox'] ) )
			update_post_meta( $form_id, 'cf7s_postbox', $_POST['cf7s-postbox'] );
		
		// Add action while saving the contact form 7
		do_action( 'cf7s_update', $cf7 );
	}		
	
	
	/**
     * Copy function
	 * 
     * @param $cf7 (class) WPCF7_ContactForm
     * @since 1.2
     */	
	function cf7skins_copy( $cf7 ) {

		if( ! $copy_id = get_option( '_cf7skins_copy_id' ) ) // make sure this is a copy action only
			return;					
		
		$cf7->copy_id = $copy_id; // add new property to the class for further use
		
		// Get current skins		
		$template = get_post_meta( $copy_id, 'cf7s_template', true );
		$style = get_post_meta( $copy_id, 'cf7s_style', true );
		$postbox = get_post_meta( $copy_id, 'cf7s_postbox', true );
		
		// Copy skins
		update_post_meta( $cf7->id(), 'cf7s_template', $template );		
		update_post_meta( $cf7->id(), 'cf7s_style', $style );
		update_post_meta( $cf7->id(), 'cf7s_postbox', $postbox );		
				
		/**
		 * Copy hook action
		 * 
		 * @param $cf7 (class) WPCF7_ContactForm.
		 * 		  $cf7->id() is the new created CF7 ID
		 * 		  $cf7->copy_id is the original copied CF7 ID
		 * @since 1.1.2
		 */	
		do_action( 'cf7skins_copy', $cf7 );
		
		// Delete to avoid this function running twice while doing default saving
		delete_option( '_cf7skins_copy_id' );
	}	
	
	
	/**
     * Enqueue CF7 Skins admin styles and scripts
	 *
	 * @action cf7s_admin_enqueue_scripts
     * @param $hook_suffix is the current page hook
     * @since 0.0.1
     */
	function admin_enqueue_scripts( $hook_suffix ) {
		if ( false === strpos( $hook_suffix, 'wpcf7' ) )
			return;
		
		wp_enqueue_style( 'tipsy',
			CF7SKINS_URL . 'css/tipsy.css',
			array( 'contact-form-7-admin' ), '1.0.0a', 'all' );
					
		wp_enqueue_style( 'typicons',
			CF7SKINS_URL . 'css/typicons/typicons.min.css',
			array( 'contact-form-7-admin' ), '2.0.7', 'all' );	
			
		wp_enqueue_style( 'cf7s-admin',
			CF7SKINS_URL . 'css/admin.css',
			array( 'contact-form-7-admin' ), CF7SKINS_VERSION, 'all' );

		wp_enqueue_script( 'tipsy',
			CF7SKINS_URL . 'js/jquery.tipsy.js',
			array( 'jquery' ), '1.0.0a', true );
		
		wp_enqueue_script( 'cf7s-admin',
			CF7SKINS_URL . 'js/jquery.admin.js',
			array( 'jquery', 'underscore' ), CF7SKINS_VERSION, true );
		
		wp_localize_script( 'cf7s-admin', 'cf7s', array(
			'nonce'		=> wp_create_nonce( 'cf7s' ),   // generate a nonce for security checking
			'load'		=> 'load_template',  			// post action for reading and loading selected template
			'sort'		=> 'cf7s_sort_skin',  			// post action for sorting skin		
			'l10n'		=> array(
				'loading'		=> __('Loading template...', CF7SKINS_TEXTDOMAIN ),
				'emptyfilter'	=> __('Empty filter, please select a filter.', CF7SKINS_TEXTDOMAIN ),
				'select' 		=> __('Select', CF7SKINS_TEXTDOMAIN ),
				'selected' 		=> __('Selected', CF7SKINS_TEXTDOMAIN ),				
				'deselect' 		=> __('Deselect', CF7SKINS_TEXTDOMAIN ),				
				'expanded'		=> __('Expanded View', CF7SKINS_TEXTDOMAIN ),
				'deselect_style'	=> __('Click to remove this Style from your form.', CF7SKINS_TEXTDOMAIN ),
				'deselect_template'	=> __('Click to remove this Template from your form.', CF7SKINS_TEXTDOMAIN ),
				'goto_form_tab'	=> __('Unable to use this Skin. Please go to Form Tab.', CF7SKINS_TEXTDOMAIN ),
			)
		));
		
		do_action( 'cf7s_admin_enqueue_scripts', $hook_suffix );
	}
	
	
	/**
     * Create the skins metabox
	 *
	 * @action cf7s_add_meta_boxes
     * @param $post_id is the current post editing ID
     * @since 0.0.1
     */	
	function add_meta_boxes( $post_id ) {
		add_meta_box( 'cf7s', __( 'Skins', CF7SKINS_TEXTDOMAIN ),
			array( &$this, 'skins_meta_box' ), null, 'mail', 'core' );
	
		// Add action while creating the skins metabox
		do_action( 'cf7s_add_meta_boxes', $post_id );
	}
	
	
	/**
     * Custom skins dialog added in the CF7 Footer for version 4.2
	 *
	 * @action wpcf7_admin_footer
     * @param $post contact form object
     * @since 1.0.1
     */	
	function add_meta_boxes_42( $cf7 ) {
		if (version_compare(WPCF7_VERSION, '4.2') >= 0) {
			
			$postbox_meta = get_post_meta( $cf7->id(), 'cf7s_postbox', true );
			$postbox_class = isset( $postbox_meta[CF7SKINS_OPTIONS] ) ? $postbox_meta[CF7SKINS_OPTIONS] : '';
	
			// Create the container id for javascript pointer
			// This is added if using add_meta_box() function
			echo '<div class="wrap">';
				echo '<div id="cf7skins-42" class="postbox '. $postbox_class  .'">';
				echo '<input type="hidden" value="'.$postbox_class.'" class="cf7skins-42 cf7s-postbox" name="cf7s-postbox['. CF7SKINS_OPTIONS .']" />'; // postbox expand/collapse
				echo '<div title="'. __('Click to toggle', CF7SKINS_TEXTDOMAIN ) .'" class="handlediv"><br></div>';
					echo '<h3 class="hndle"><span>'. __('Skins', CF7SKINS_TEXTDOMAIN ) .'</span></h3>';
					echo '<div class="inside">';
						echo '<div id="cf7s" class="cf7-42">';
							$this->generate_tab( $cf7, null );  // in tab.php	
						echo '</div>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
		}
	}
	
	
	/**
     * Display the skins metabox
	 * 
     * @param $post current post object
     * @param $box metabox arguments
     * @since 0.0.1
     */	
	function skins_meta_box( $post, $box ) {
		$this->generate_tab( $post, $box );  // in tab.php
	}
	
	
	/**
	 * Check if current admin page is contact form editing page
	 * @since 1.0.2
	 */
	public static function edit_page() {
		global $hook_suffix;
		
		// Don't show at the Contact Form 7 list wp-admin/admin.php?page=wpcf7
		if ( 'toplevel_page_wpcf7' == $hook_suffix && isset( $_GET['post'] ) && ! empty( $_GET['post'] ) )
			return true;
		
		// Don't show in the add new page
		if ( version_compare( WPCF7_VERSION, '4.4' ) <= 0 ) {
			if ( 'contact_page_wpcf7-new' == $hook_suffix && isset( $_GET['locale'] ) )
				return true;
		} else {
			if ( 'contact_page_wpcf7-new' == $hook_suffix )
				return true;
		}

		return false;
	}
	
	
	/**
	 * Add CF7 admin footer hook
	 * All other addons should use this action hook
	 * 
	 * @since 1.1.1
	 */
	function cf7skins_admin_footer() {
		do_action( 'cf7skins_admin_footer' );
	}
}