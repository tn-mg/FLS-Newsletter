<?php
/**
 * Standalone template for newsletter posts.
 * Each newsletter is its own full page (bespoke Figma design per issue, built
 * from scratch with no shared template/toggle/viewer) — no theme header/footer.
 * Legacy (Flowpaper) newsletters redirect to their serve endpoint before any
 * output, per FLS_Legacy_Serve.
 */

$post_id       = get_the_ID();
$is_legacy     = get_post_meta( $post_id, '_fls_is_legacy', true );
$legacy_folder = get_post_meta( $post_id, '_fls_legacy_folder', true );

if ( $is_legacy && $legacy_folder ) {
	wp_redirect( home_url( '/' . $legacy_folder . '/' ) );
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title><?php echo esc_html( wp_get_document_title() ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php
		// Sections are built one at a time as individual ACF Group fields (ACF
		// Free has no Flexible Content field type), so this list only grows as
		// each section is finished — order here is the render order.
		$fls_show_people_matter = ! metadata_exists( 'post', $post_id, 'show_people_matter' ) || (bool) get_field( 'show_people_matter' );
		$fls_sections           = array( 'cover_header', 'our_message', 'special_news', 'whats_happening', 'upcoming_events', 'sponsorships', 'people_matter', 'newsletter_footer' );
		if ( ! $fls_show_people_matter ) {
			$fls_sections = array_values( array_diff( $fls_sections, array( 'people_matter' ) ) );
		}
		$fls_has_acf_sections = false;
		foreach ( $fls_sections as $fls_section_key ) {
			if ( get_field( $fls_section_key ) ) {
				$fls_has_acf_sections = true;
				break;
			}
		}
		$fls_cover_data = get_field( 'cover_header' );
		$fls_bg_url     = $fls_cover_data ? wp_get_attachment_image_url( $fls_cover_data['background_image'], 'full' ) : '';
		$fls_mobile_bg_url = $fls_cover_data ? wp_get_attachment_image_url( $fls_cover_data['mobile_background_image'] ?? 0, 'full' ) : '';
		$fls_mobile_corner_url = $fls_cover_data ? wp_get_attachment_image_url( $fls_cover_data['mobile_corner_mask'] ?? 0, 'full' ) : '';
		?>
	<div class="q3-page"<?php echo $fls_bg_url ? ' style="background-image:url(' . esc_url( $fls_bg_url ) . ');"' : ''; ?>>
		<script>
		window.flsNewsletterBindSwipe = window.flsNewsletterBindSwipe || function ( viewport, onSwipe ) {
			if ( ! viewport || typeof onSwipe !== 'function' || viewport.dataset.swipeBound === 'true' ) return;
			viewport.dataset.swipeBound = 'true';
			viewport.style.touchAction = 'pan-y';
			var pointerId = null;
			var startX = 0;
			var startY = 0;

			viewport.addEventListener( 'dragstart', function ( event ) { event.preventDefault(); } );
			viewport.addEventListener( 'pointerdown', function ( event ) {
				if ( event.pointerType === 'mouse' && event.button !== 0 ) return;
				if ( event.target.closest( 'button, a, input, select, textarea' ) ) return;
				pointerId = event.pointerId;
				startX = event.clientX;
				startY = event.clientY;
				if ( viewport.setPointerCapture ) viewport.setPointerCapture( pointerId );
			} );
			viewport.addEventListener( 'pointerup', function ( event ) {
				if ( event.pointerId !== pointerId ) return;
				var deltaX = event.clientX - startX;
				var deltaY = event.clientY - startY;
				pointerId = null;
				if ( Math.abs( deltaX ) > 40 && Math.abs( deltaX ) > Math.abs( deltaY ) ) {
					onSwipe( deltaX < 0 ? 1 : -1 );
				}
			} );
			viewport.addEventListener( 'pointercancel', function () { pointerId = null; } );
		};
		</script>
		<?php if ( $fls_mobile_bg_url && $fls_bg_url && $fls_mobile_corner_url ) : ?>
			<div class="q3-mobile-hero-layers" aria-hidden="true">
				<img class="q3-mobile-hero-layers__base" src="<?php echo esc_url( $fls_mobile_bg_url ); ?>" alt="">
				<img class="q3-mobile-hero-layers__artboard" src="<?php echo esc_url( $fls_bg_url ); ?>" alt="">
				<img class="q3-mobile-hero-layers__corner" src="<?php echo esc_url( $fls_mobile_corner_url ); ?>" alt="">
			</div>
		<?php endif; ?>
		<?php if ( $fls_has_acf_sections ) : ?>
			<?php foreach ( $fls_sections as $fls_section_key ) : ?>
				<?php
				$fls_section_data = get_field( $fls_section_key );
				$fls_partial       = locate_template( 'newsletter-sections/' . $fls_section_key . '.php' );
				if ( $fls_section_data && $fls_partial ) {
					include $fls_partial;
				}
				?>
			<?php endforeach; ?>
		<?php else : ?>
			<?php
			remove_filter( 'the_content', 'wpautop' );
			the_content();
			add_filter( 'the_content', 'wpautop' );
			?>
		<?php endif; ?>
	</div>
	<?php if ( $fls_has_acf_sections ) : ?>
		<script>
		(function () {
			// Fit the dedicated 440px mobile, 960px tablet, or 1920px desktop canvas.
			var page = document.querySelector( '.q3-page' );
			if ( ! page ) return;
			function fit() {
				var isTablet = window.matchMedia( '(min-width: 768px) and (max-width: 1199px)' ).matches;
				var isMobile = window.matchMedia( '(max-width: 767px)' ).matches;
				var designWidth = isMobile ? 440 : ( isTablet ? 960 : 1920 );
				var scale = isTablet ? window.innerWidth / designWidth : Math.min( 1, window.innerWidth / designWidth );
				var renderedWidth = designWidth * scale;
				page.style.zoom = scale;
				page.style.marginLeft = Math.max( 0, ( window.innerWidth - renderedWidth ) / ( 2 * scale ) ) + 'px';
				document.body.style.height = '';
			}
			fit();
			window.addEventListener( 'resize', fit );
		})();
		</script>
	<?php endif; ?>
<?php endwhile; ?>
<?php wp_footer(); ?>
</body>
</html>
