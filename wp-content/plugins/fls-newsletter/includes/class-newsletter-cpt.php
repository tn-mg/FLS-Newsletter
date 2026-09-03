<?php
/**
 * Custom Post Type: Newsletter
 * Meta boxes: image gallery (pages), enable flipbook toggle, template selector, case-sensitive slug
 * URL routing: case-sensitive slugs without /newsletter/ prefix
 */

class FLS_Newsletter_CPT {

	public static function init() {
		self::register_cpt();
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ) );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'newsletter_post_link' ), 10, 2 );
		add_action( 'parse_request', array( __CLASS__, 'parse_case_sensitive_request' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'lowercase_redirect' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'disable_archive_redirect' ), 9 );
	}

	public static function register_cpt() {
		$labels = array(
			'name'                  => __( 'Newsletters', 'fls-newsletter' ),
			'singular_name'         => __( 'Newsletter', 'fls-newsletter' ),
			'add_new'               => __( 'Add New', 'fls-newsletter' ),
			'add_new_item'          => __( 'Add New Newsletter', 'fls-newsletter' ),
			'edit_item'             => __( 'Edit Newsletter', 'fls-newsletter' ),
			'new_item'              => __( 'New Newsletter', 'fls-newsletter' ),
			'view_item'             => __( 'View Newsletter', 'fls-newsletter' ),
			'search_items'          => __( 'Search Newsletters', 'fls-newsletter' ),
			'not_found'             => __( 'No newsletters found.', 'fls-newsletter' ),
			'not_found_in_trash'    => __( 'No newsletters found in Trash.', 'fls-newsletter' ),
			'all_items'             => __( 'All Newsletters', 'fls-newsletter' ),
			'archives'              => __( 'Newsletter Archives', 'fls-newsletter' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-email-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'rewrite'            => array( 'slug' => 'newsletter' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'newsletter', $args );
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'fls_newsletter_legacy',
			__( 'Legacy Folder', 'fls-newsletter' ),
			array( __CLASS__, 'render_legacy_meta_box' ),
			'newsletter',
			'side',
			'high'
		);

		add_meta_box(
			'fls_newsletter_slug',
			__( 'Case-Sensitive Slug', 'fls-newsletter' ),
			array( __CLASS__, 'render_slug_meta_box' ),
			'newsletter',
			'side',
			'high'
		);
	}

	public static function render_legacy_meta_box( $post ) {
		wp_nonce_field( 'fls_newsletter_meta', 'fls_newsletter_meta_nonce' );
		$is_legacy = get_post_meta( $post->ID, '_fls_is_legacy', true );
		$folder    = get_post_meta( $post->ID, '_fls_legacy_folder', true );
		?>
		<label>
			<input type="checkbox" name="fls_is_legacy" value="1" <?php checked( $is_legacy, '1' ); ?>>
			<?php _e( 'This is a legacy Flowpaper newsletter', 'fls-newsletter' ); ?>
		</label>
		<p>
			<label><?php _e( 'Folder name:', 'fls-newsletter' ); ?></label><br>
			<input type="text" name="fls_legacy_folder" value="<?php echo esc_attr( $folder ); ?>" style="width:100%;" placeholder="e.g. int-fls-newsletter-q1-2026">
		</p>
		<p class="description">
			<?php _e( 'Must match the folder name in uploads/legacy-newsletters/', 'fls-newsletter' ); ?>
		</p>
		<?php
	}

	public static function render_slug_meta_box( $post ) {
		$slug = get_post_meta( $post->ID, '_fls_newsletter_slug', true );
		?>
		<p>
			<label for="fls_newsletter_slug"><?php _e( 'Case-sensitive URL slug:', 'fls-newsletter' ); ?></label><br>
			<input type="text" name="fls_newsletter_slug" id="fls_newsletter_slug" value="<?php echo esc_attr( $slug ); ?>" style="width:100%;" placeholder="e.g. INT-FLS-Newsletter-Q2-2026">
		</p>
		<p class="description">
			<?php _e( 'The public URL will be: /{slug}/. Must match exact case. Lowercase visits will 301 redirect here.', 'fls-newsletter' ); ?>
		</p>
		<?php
	}

	public static function save_meta( $post_id ) {
		if ( ! isset( $_POST['fls_newsletter_meta_nonce'] ) || ! wp_verify_nonce( $_POST['fls_newsletter_meta_nonce'], 'fls_newsletter_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Legacy
		$is_legacy = isset( $_POST['fls_is_legacy'] ) ? '1' : '';
		update_post_meta( $post_id, '_fls_is_legacy', $is_legacy );

		if ( isset( $_POST['fls_legacy_folder'] ) ) {
			update_post_meta( $post_id, '_fls_legacy_folder', sanitize_text_field( $_POST['fls_legacy_folder'] ) );
		}

		// Case-sensitive slug
		if ( isset( $_POST['fls_newsletter_slug'] ) ) {
			$raw = sanitize_text_field( $_POST['fls_newsletter_slug'] );
			$clean = preg_replace( '/[^a-zA-Z0-9_-]/', '', $raw );
			update_post_meta( $post_id, '_fls_newsletter_slug', $clean );
		}
	}

	public static function newsletter_post_link( $post_link, $post ) {
		if ( $post->post_type !== 'newsletter' ) {
			return $post_link;
		}

		// Use case-sensitive slug if available
		$case_slug = get_post_meta( $post->ID, '_fls_newsletter_slug', true );
		if ( ! empty( $case_slug ) ) {
			return home_url( '/' . $case_slug . '/' );
		}

		// Fallback to post_name (lowercase)
		return home_url( '/' . $post->post_name . '/' );
	}

	public static function parse_case_sensitive_request( $wp ) {
		if ( ! isset( $wp->request ) ) {
			return;
		}

		$request_path = trim( $wp->request, '/' );
		if ( strpos( $request_path, '/' ) !== false || empty( $request_path ) ) {
			return; // Not a root-level path
		}

		global $wpdb;

		// 1. Exact case-sensitive match
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_fls_newsletter_slug' AND meta_value = %s LIMIT 1",
				$request_path
			)
		);

		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post && $post->post_type === 'newsletter' ) {
				$wp->query_vars = array( 'post_type' => 'newsletter', 'name' => $post->post_name );
				$wp->matched_rule = 'fls_newsletter_case_slug';
				$wp->matched_query = 'post_type=newsletter&name=' . $post->post_name;
			}
			return;
		}

		// 2. Lowercase match -> 301 redirect to correct case
		$lc_path = strtolower( $request_path );
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_fls_newsletter_slug' AND LOWER(meta_value) = %s",
				$lc_path
			),
			ARRAY_A
		);

		if ( ! empty( $results ) ) {
			$case_slug = $results[0]['meta_value'];
			$redirect_url = home_url( '/' . $case_slug . '/' );
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

	public static function lowercase_redirect() {
		if ( is_singular( 'newsletter' ) ) {
			$post_id = get_queried_object_id();
			$case_slug = get_post_meta( $post_id, '_fls_newsletter_slug', true );
			if ( empty( $case_slug ) ) {
				return;
			}

			$current_path = trim( $_SERVER['REQUEST_URI'], '/' );
			$parts = explode( '?', $current_path );
			$path = $parts[0];

			// If current path is lowercase and different from case slug
			if ( strtolower( $path ) === $path && $path !== $case_slug ) {
				$redirect_url = home_url( '/' . $case_slug . '/' );
				if ( ! empty( $parts[1] ) ) {
					$redirect_url .= '?' . $parts[1];
				}
				wp_redirect( $redirect_url, 301 );
				exit;
			}
		}
	}

	public static function disable_archive_redirect() {
		$request_path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
		if ( 'newsletter' !== $request_path ) {
			return;
		}
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		$template_404 = get_query_template( '404' );
		if ( $template_404 ) {
			include $template_404;
			exit;
		}
	}

	public static function template_include( $template ) {
		if ( is_singular( 'newsletter' ) ) {
			$legacy = get_post_meta( get_the_ID(), '_fls_is_legacy', true );
			if ( $legacy ) {
				return $template;
			}

			$custom = locate_template( 'single-newsletter.php' );
			if ( $custom ) {
				return $custom;
			}
		}
		return $template;
	}
}
