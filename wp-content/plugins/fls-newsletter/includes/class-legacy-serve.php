<?php
/**
 * Serve legacy Flowpaper newsletter folders securely via WordPress endpoints.
 *
 * URL format: /{folder-name}/ → serves index.html
 *             /{folder-name}/css/flowpaper.css → serves file from uploads
 */

class FLS_Legacy_Serve {

	public static function init() {
		add_action( 'parse_request', array( __CLASS__, 'intercept_legacy_request' ), 1 );
	}

	public static function add_query_vars( $vars ) {
		$vars[] = 'fls_legacy_folder';
		$vars[] = 'fls_legacy_file';
		return $vars;
	}

	public static function intercept_legacy_request( $wp ) {
		// Only handle front-end requests
		if ( is_admin() ) {
			return;
		}

		$request_uri = $_SERVER['REQUEST_URI'];
		$path        = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

		if ( empty( $path ) ) {
			return;
		}

		// Split path: first segment is folder, rest is file
		$parts  = explode( '/', $path );
		$folder = $parts[0];

		// Handle /newsletter/{folder-name}/ URLs (WordPress default permalink)
		if ( $folder === 'newsletter' && isset( $parts[1] ) ) {
			$folder = $parts[1];
			// Rebuild parts without the 'newsletter' prefix
			$parts = array_merge( array( $folder ), array_slice( $parts, 2 ) );
		}

		// Check if this is a legacy folder (case-sensitive match)
		$legacy_base = FLS_LEGACY_DIR . $folder . '/';
		if ( ! is_dir( $legacy_base ) ) {
			return; // Not a legacy folder, let WordPress handle it
		}

		// Find the legacy post by exact slug case
		$post = self::get_legacy_post_by_slug_exact( $folder );
		if ( ! $post ) {
			// No exact match — check for case-insensitive match to redirect
			$redirect_post = self::get_legacy_post_by_slug( $folder );
			if ( $redirect_post && $redirect_post->post_status === 'publish' ) {
				$stored_slug = get_post_meta( $redirect_post->ID, '_fls_legacy_slug', true );
				if ( $stored_slug && $stored_slug !== $folder ) {
					$correct_url = home_url( '/' . $stored_slug . '/' );
					if ( ! empty( $parts[1] ) ) {
						$correct_url .= implode( '/', array_slice( $parts, 1 ) );
					}
					wp_redirect( $correct_url, 301 );
					exit;
				}
			}
			// No matching post at all — but folder exists, serve it anyway
		} elseif ( $post->post_status === 'publish' ) {
			$is_legacy = get_post_meta( $post->ID, '_fls_is_legacy', true );
			if ( ! $is_legacy ) {
				return; // Let WordPress serve the real post
			}
		}

		// Build file path
		array_shift( $parts );
		$file = implode( '/', $parts );
		if ( empty( $file ) ) {
			$file = 'index.html';
		}

		self::serve_file( $folder, $file );
	}

	private static function get_legacy_post_by_slug( $slug ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'newsletter',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_fls_legacy_slug',
						'value'   => $slug,
						'compare' => '=',
					),
				),
			)
		);
		if ( $query->have_posts() ) {
			return $query->posts[0];
		}
		return false;
	}

	private static function get_legacy_post_by_slug_exact( $slug ) {
		$post = self::get_legacy_post_by_slug( $slug );
		if ( $post ) {
			$stored = get_post_meta( $post->ID, '_fls_legacy_slug', true );
			if ( $stored === $slug ) {
				return $post;
			}
		}
		return false;
	}

	private static function serve_file( $folder, $file ) {
		// Sanitize
		$folder = sanitize_file_name( $folder );
		$file   = sanitize_text_field( $file );
		$file   = ltrim( $file, '/' );

		// Block path traversal
		if ( strpos( $file, '..' ) !== false || strpos( $folder, '..' ) !== false ) {
			wp_die( 'Invalid path.', 403 );
		}

		$base_dir  = FLS_LEGACY_DIR . $folder . '/';
		$file_path = realpath( $base_dir . $file );

		// Ensure file is inside the allowed directory
		if ( false === $file_path || strpos( $file_path, realpath( $base_dir ) ) !== 0 ) {
			wp_die( 'File not found.', 404 );
		}

		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			wp_die( 'File not found.', 404 );
		}

		// Don't serve PHP
		$ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
		if ( $ext === 'php' ) {
			wp_die( 'Forbidden.', 403 );
		}

		// Determine MIME type
		$mimes = array(
			'html' => 'text/html',
			'css'  => 'text/css',
			'js'   => 'application/javascript',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'svg'  => 'image/svg+xml',
			'xml'  => 'application/xml',
			'bin'  => 'application/octet-stream',
			'mp4'  => 'video/mp4',
			'webm' => 'video/webm',
			'woff' => 'font/woff',
			'woff2'=> 'font/woff2',
			'ttf'  => 'font/ttf',
			'otf'  => 'font/otf',
			'eot'  => 'application/vnd.ms-fontobject',
		);

		$mime = isset( $mimes[ $ext ] ) ? $mimes[ $ext ] : 'application/octet-stream';

		// Serve index.html with path rewriting
		if ( $ext === 'html' ) {
			$content = file_get_contents( $file_path );

			// Inject base tag so relative paths resolve correctly
			if ( stripos( $content, '<base' ) === false ) {
				$base_url = home_url( '/' . $folder . '/' );
				$base_tag = '<base href="' . esc_url( $base_url ) . '">' . "\n";
				$content  = preg_replace( '/(<head[^>]*>)/i', '$1' . "\n" . $base_tag, $content, 1 );
			}

			// Fix absolute paths in JS/CSS references if needed
			$content = str_replace( 'href="css/', 'href="' . home_url( '/' . $folder . '/css/' ), $content );
			$content = str_replace( 'href="locale/', 'href="' . home_url( '/' . $folder . '/locale/' ), $content );
			$content = str_replace( 'src="js/', 'src="' . home_url( '/' . $folder . '/js/' ), $content );
			$content = str_replace( 'src="images/', 'src="' . home_url( '/' . $folder . '/images/' ), $content );

			header( 'Content-Type: text/html; charset=utf-8' );
			echo $content;
			exit;
		}

		// Serve static assets directly
		if ( function_exists( 'wp_get_mime_types' ) ) {
			$wp_mimes = wp_get_mime_types();
			if ( isset( $wp_mimes[ $ext ] ) ) {
				$mime = is_array( $wp_mimes[ $ext ] ) ? $wp_mimes[ $ext ][0] : $wp_mimes[ $ext ];
			}
		}

		$file_size = filesize( $file_path );
		header( 'Accept-Ranges: bytes' );
		header( 'Cache-Control: public, max-age=86400' );

		set_time_limit( 0 );
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		// Support Range requests (required for <video> seek/playback of large files, esp. Safari).
		if ( isset( $_SERVER['HTTP_RANGE'] ) && preg_match( '/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches ) ) {
			$start = ( '' === $matches[1] ) ? 0 : (int) $matches[1];
			$end   = ( '' === $matches[2] ) ? $file_size - 1 : min( (int) $matches[2], $file_size - 1 );

			if ( $start > $end || $start >= $file_size ) {
				header( 'HTTP/1.1 416 Range Not Satisfiable' );
				header( 'Content-Range: bytes */' . $file_size );
				exit;
			}

			header( 'HTTP/1.1 206 Partial Content' );
			header( 'Content-Type: ' . $mime );
			header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size );
			header( 'Content-Length: ' . ( $end - $start + 1 ) );

			$fp = fopen( $file_path, 'rb' );
			fseek( $fp, $start );
			$remaining = $end - $start + 1;
			while ( $remaining > 0 && ! feof( $fp ) ) {
				$chunk = fread( $fp, min( 1048576, $remaining ) );
				echo $chunk;
				flush();
				$remaining -= strlen( $chunk );
			}
			fclose( $fp );
			exit;
		}

		header( 'Content-Type: ' . $mime );
		header( 'Content-Length: ' . $file_size );
		readfile( $file_path );
		exit;
	}
}
