<?php
/**
 * Plugin Name: FLS Newsletter Analytics
 * Description: Adds the dedicated FLS Newsletter GTM container and a reusable, privacy-safe dataLayer event contract for newsletter posts.
 * Version: 1.0.0
 * Author: FLS Group
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FLS_NEWSLETTER_ANALYTICS_VERSION', '1.0.0' );
define( 'FLS_NEWSLETTER_ANALYTICS_GTM_ID', 'GTM-NSKQ344W' );

/**
 * Restrict this integration to the Newsletter custom post type.
 *
 * @return bool
 */
function fls_newsletter_analytics_should_load() {
	return ! is_admin() && is_singular( 'newsletter' );
}

/**
 * Add the GTM head script. No direct gtag.js is added, preventing duplicate GA4 delivery.
 */
function fls_newsletter_analytics_render_gtm_head() {
	if ( ! fls_newsletter_analytics_should_load() ) {
		return;
	}

	if ( ! apply_filters( 'fls_newsletter_analytics_load_gtm', true, FLS_NEWSLETTER_ANALYTICS_GTM_ID ) ) {
		return;
	}
	?>
	<!-- Google Tag Manager: FLS Newsletter -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( FLS_NEWSLETTER_ANALYTICS_GTM_ID ); ?>');</script>
	<!-- End Google Tag Manager -->
	<?php
}
add_action( 'wp_head', 'fls_newsletter_analytics_render_gtm_head', 1 );

/**
 * Add the GTM noscript fallback directly after body through wp_body_open().
 */
function fls_newsletter_analytics_render_gtm_body() {
	if ( ! fls_newsletter_analytics_should_load() ) {
		return;
	}

	if ( ! apply_filters( 'fls_newsletter_analytics_load_gtm', true, FLS_NEWSLETTER_ANALYTICS_GTM_ID ) ) {
		return;
	}
	?>
	<!-- Google Tag Manager (noscript): FLS Newsletter -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( FLS_NEWSLETTER_ANALYTICS_GTM_ID ); ?>" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}
add_action( 'wp_body_open', 'fls_newsletter_analytics_render_gtm_body', 1 );

/**
 * Load the generic event producer after newsletter markup and issue scripts.
 */
function fls_newsletter_analytics_enqueue_script() {
	if ( ! fls_newsletter_analytics_should_load() ) {
		return;
	}

	$relative_path = 'assets/js/fls-newsletter-tracking.js';
	$file_path     = plugin_dir_path( __FILE__ ) . $relative_path;
	$version       = file_exists( $file_path ) ? (string) filemtime( $file_path ) : FLS_NEWSLETTER_ANALYTICS_VERSION;

	wp_enqueue_script(
		'fls-newsletter-analytics',
		plugins_url( $relative_path, __FILE__ ),
		array(),
		$version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'fls_newsletter_analytics_enqueue_script' );
