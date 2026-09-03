<?php
/**
 * Admin page for importing legacy newsletter folders
 */

class FLS_Legacy_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_bulk_zip_import' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

		// Ensure legacy directory exists
		if ( ! is_dir( FLS_LEGACY_DIR ) ) {
			wp_mkdir_p( FLS_LEGACY_DIR );
		}
	}

	public static function add_menu_page() {
		add_submenu_page(
			'edit.php?post_type=newsletter',
			__( 'Legacy Import', 'fls-newsletter' ),
			__( 'Legacy Import', 'fls-newsletter' ),
			'manage_options',
			'fls-legacy-import',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function enqueue( $hook ) {
		if ( $hook !== 'newsletter_page_fls-legacy-import' ) {
			return;
		}
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}

	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Upload Legacy Newsletters', 'fls-newsletter' ); ?></h1>
			<p><?php _e( 'Select one or more ZIP files. Each file will be extracted and imported automatically.', 'fls-newsletter' ); ?></p>
			<?php self::render_upload_tab(); ?>
		</div>
		<?php
	}

	private static function render_upload_tab() {
		?>
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'fls_zip_import', 'fls_zip_import_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="zip_file"><?php _e( 'ZIP File(s)', 'fls-newsletter' ); ?></label></th>
					<td>
						<input type="file" name="zip_files[]" id="zip_file" accept=".zip" multiple required>
						<p class="description"><?php _e( 'Upload one or more ZIP files. Each will be extracted and imported automatically.', 'fls-newsletter' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Upload &amp; Import All', 'fls-newsletter' ) ); ?>
		</form>
		<?php
	}

	public static function handle_bulk_zip_import() {
		if ( ! isset( $_POST['fls_zip_import_nonce'] ) || ! wp_verify_nonce( $_POST['fls_zip_import_nonce'], 'fls_zip_import' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_FILES['zip_files'] ) || empty( $_FILES['zip_files']['tmp_name'] ) ) {
			return;
		}

		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		$files = $_FILES['zip_files'];
		$count = count( $files['tmp_name'] );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( $files['error'][ $i ] !== UPLOAD_ERR_OK ) {
				$errors[] = $files['name'][ $i ] . ': Upload failed (error ' . $files['error'][ $i ] . ')';
				continue;
			}

			$tmp_file = $files['tmp_name'][ $i ];
			$zip_name = $files['name'][ $i ];

			$result = self::process_single_zip( $tmp_file, $zip_name );
			if ( is_wp_error( $result ) ) {
				$errors[] = $zip_name . ': ' . $result->get_error_message();
			} elseif ( $result === 'skipped' ) {
				$skipped++;
			} else {
				$imported++;
			}
		}

		if ( $imported > 0 ) {
			add_settings_error( 'fls_legacy', 'bulk_zip_success', sprintf( __( 'Successfully imported %d newsletter(s).', 'fls-newsletter' ), $imported ), 'success' );
		}
		if ( $skipped > 0 ) {
			add_settings_error( 'fls_legacy', 'bulk_zip_skipped', sprintf( __( '%d skipped (already exists).', 'fls-newsletter' ), $skipped ), 'warning' );
		}
		if ( ! empty( $errors ) ) {
			add_settings_error( 'fls_legacy', 'bulk_zip_errors', implode( '<br>', $errors ), 'error' );
		}
	}

	private static function process_single_zip( $tmp_file, $zip_name ) {
		$zip = new ZipArchive();
		$res = $zip->open( $tmp_file );

		if ( $res !== true ) {
			return new WP_Error( 'zip_open_error', __( 'Could not open ZIP file.', 'fls-newsletter' ) );
		}

		$extract_to = FLS_LEGACY_DIR;
		$top_items  = array();
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$name = $zip->getNameIndex( $i );
			if ( strpos( $name, '__MACOSX/' ) === 0 ) {
				continue; // macOS archive artifact, not real content.
			}
			$parts = explode( '/', $name );
			if ( count( $parts ) === 2 && $parts[1] === '' ) {
				$top_items[] = rtrim( $name, '/' );
			}
		}

		if ( count( $top_items ) === 1 ) {
			$folder_name = $top_items[0];
		} else {
			$folder_name = sanitize_file_name( pathinfo( $zip_name, PATHINFO_FILENAME ) );
			$extract_to  = FLS_LEGACY_DIR . $folder_name . '/';
			wp_mkdir_p( $extract_to );
		}

		$target_dir = FLS_LEGACY_DIR . $folder_name . '/';
		if ( is_dir( $target_dir ) ) {
			$zip->close();
			return 'skipped';
		}

		$zip->extractTo( $extract_to );
		$zip->close();

		// extractTo() writes __MACOSX/ regardless of the top-level filter above; remove it.
		if ( is_dir( $extract_to . '__MACOSX' ) ) {
			self::delete_dir_recursive( $extract_to . '__MACOSX' );
		}

		if ( count( $top_items ) === 1 && is_dir( $target_dir . $folder_name ) ) {
			$inner = $target_dir . $folder_name . '/';
			foreach ( glob( $inner . '*' ) as $file ) {
				rename( $file, $target_dir . basename( $file ) );
			}
			rmdir( $inner );
		}

		$title = str_replace( array( '-', '_' ), ' ', $folder_name );
		$title = ucwords( $title );

		$post_id = self::create_legacy_post( $folder_name, $title );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		return $post_id;
	}

	private static function create_legacy_post( $folder, $title ) {
		$post_data = array(
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_type'    => 'newsletter',
		);

		$post_id = wp_insert_post( $post_data, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Store exact folder name for URL matching (case-sensitive)
		update_post_meta( $post_id, '_fls_is_legacy', '1' );
		update_post_meta( $post_id, '_fls_legacy_folder', $folder );
		update_post_meta( $post_id, '_fls_legacy_slug', $folder );

		// Force the post_name to match folder name exactly (case-sensitive)
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_name' => $folder ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );

		return $post_id;
	}

	private static function delete_dir_recursive( $dir ) {
		foreach ( glob( $dir . '/*' ) ?: array() as $path ) {
			is_dir( $path ) ? self::delete_dir_recursive( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}
