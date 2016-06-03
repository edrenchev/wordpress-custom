<?php
/**
 * Class MetaboxExample
 */
class MetaboxExample {

	/**
	 * Defines the whitelist for allowed screens (post_types)
	 */
	private $_allowedScreens = array( 'SCREENS_TO_ALLOW_METABOX' );

	/**
	 * Get parameter for the error box error code
	 */
	const GET_METABOX_ERROR_PARAM = 'meta-error';

	/**
	 * Defines admin hooks
	 */
	public function __construct() {
		add_action('add_meta_boxes', array($this, 'addMetabox'), 50);
		add_action('save_post', array($this, 'saveMetabox'), 50);
		add_action('edit_form_top', array($this, 'adminNotices')); // NOTE: admin_notices doesn't position this right on custom post type pages, haven't testes this on POST or PAGE but I don't see this an issue
	}

	/**
	 * Adds the metabox to specified post types
	 */
	public function addMetabox() {
// 		foreach ( $this->_allowedScreens as $screen ) {
			add_meta_box(
					'PLUGIN_METABOX',
					__( 'TITLE', 'text_domain' ),
					array($this, 'metaBox'),
					'post',
					'side',
					'high'
					);
// 		}
	}

	/**
	 * Output metabox content
	 * @param $post
	 */
	public function metaBox($post) {
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'metaboxnonce', 'metaboxnonce' );
		// Load meta data for this metabox
		$someValue = get_post_meta( $post->ID, 'META_KEY_IDENTIFIER', true );
		?>
        <p>
            <label for="some-value" style="width: 120px; display: inline-block;">
                <?php _e( 'Some Field:', 'text_domain' ); ?>
            </label>
            &nbsp;
            <input type="text" id="some-value" name="some_value" value="<?php esc_attr_e( $someValue ); ?>" size="25" />
        </p>
    <?php
    }

    /**
     * Save method for the metabox
     * @param $post_id
     */
    public function saveMetabox($post_id) {
        global $wpdb;
        
        // Check if our nonce is set.
        if ( ! isset( $_POST['metaboxnonce'] ) ) {
            return $post_id;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['metaboxnonce'], 'metaboxnonce' ) ) {
            return $post_id;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        // Make sure that it is set.
        if ( !isset( $_POST['some_value'] ) ) {
            return $post_id;
        }
        // Sanitize user input.
        $someValue = sanitize_text_field( $_POST['some_value'] );
        // Check to make sure there is a value
        if (empty($someValue)) {
            // Add our error code
            add_filter('redirect_post_location', function($loc) {
                return add_query_arg( self::GET_METABOX_ERROR_PARAM, 1, $loc );
            });
            return $post_id; // make sure to return so we don't allow further processing
        }
        // Update the meta field in the database.
        update_post_meta( $post_id, 'META_KEY_IDENTIFIER', $someValue );
    }

    /**
     * Metabox admin notices
     */
    public function adminNotices() {
        if (isset($_GET[self::GET_METABOX_ERROR_PARAM])) {
            $screen = get_current_screen();
            // Make sure we are in the proper post type
//             if (in_array($screen->post_type, $this->_allowedScreens)) {
                $errorCode = (int) $_GET[self::GET_METABOX_ERROR_PARAM];
                switch($errorCode) {
                    case 1:
                        $this->_showAdminNotice( __('Some error happened', 'text_domain') );
                        break;
                    // More error codes go here for outputting errors
                }
//             }
        }
    }

    /**
     * Shows the admin notice for the metabox
     * @param $message
     * @param string $type
     */
    private function _showAdminNotice($message, $type='error') {
        ?>
        <div class="<?php esc_attr_e($type); ?> below-h2">
            <p><?php echo $message; ?></p>
        </div>
    <?php
    }

}
new MetaboxExample();