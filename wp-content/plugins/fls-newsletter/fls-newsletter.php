<?php
/**
 * Plugin Name: FLS Newsletter
 * Description: Custom newsletter management for FLS — legacy Flowpaper support + new Figma-based newsletters.
 * Version: 1.0.0
 * Author: FLS
 * Text Domain: fls-newsletter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FLS_NEWSLETTER_VERSION', '1.0.0' );
define( 'FLS_NEWSLETTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'FLS_NEWSLETTER_URL', plugin_dir_url( __FILE__ ) );
define( 'FLS_LEGACY_DIR', WP_CONTENT_DIR . '/uploads/legacy-newsletters/' );

// Load core classes
require_once FLS_NEWSLETTER_DIR . 'includes/class-legacy-serve.php';
require_once FLS_NEWSLETTER_DIR . 'includes/class-legacy-admin.php';
require_once FLS_NEWSLETTER_DIR . 'includes/class-newsletter-cpt.php';

add_action( 'init', 'fls_newsletter_init' );
add_action( 'wp_enqueue_scripts', 'fls_newsletter_enqueue_assets' );
add_action( 'wp_head', 'fls_newsletter_viewport_meta', 0 );
add_filter( 'body_class', 'fls_newsletter_body_class' );

function fls_newsletter_enqueue_assets() {
	if ( ! is_singular( 'newsletter' ) || get_post_meta( get_the_ID(), '_fls_is_legacy', true ) ) {
		return;
	}
	$slug = get_post_meta( get_the_ID(), '_fls_newsletter_slug', true );
	if ( in_array( $slug, array( 'INT-FLS-Newsletter-Q3-2026', 'EXT-FLS-Newsletter-Q3-2026' ), true ) ) {
		wp_enqueue_style(
			'fls-newsletter-int-q3-2026',
			FLS_NEWSLETTER_URL . 'assets/newsletter-int-q3-2026.css',
			array(),
			filemtime( FLS_NEWSLETTER_DIR . 'assets/newsletter-int-q3-2026.css' )
		);
	}
}

function fls_newsletter_body_class( $classes ) {
	if ( is_singular( 'newsletter' ) && ! get_post_meta( get_the_ID(), '_fls_is_legacy', true ) ) {
		$classes[] = 'fls-landing-page';
	}
	return $classes;
}

function fls_newsletter_viewport_meta() {
	if ( is_singular( 'newsletter' ) ) {
		echo '<meta name="viewport" content="width=device-width, initial-scale=1" />' . "\n";
	}
}

function fls_newsletter_init() {
	FLS_Legacy_Serve::init();
	FLS_Legacy_Admin::init();
	FLS_Newsletter_CPT::init();
}
