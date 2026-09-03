<?php
/**
 * Section: Navigation + Cover Header
 * Expects $fls_section_data — the 'cover_header' ACF Group field array —
 * set by single-newsletter.php before including this file.
 */

$data = $fls_section_data;
$fls_has_people_matter = ! isset( $fls_show_people_matter ) || $fls_show_people_matter;

if ( ! $fls_has_people_matter ) {
	$data['nav_link_5'] = '';
}

$nav_links = array_filter( array(
	$data['nav_link_1'],
	$data['nav_link_2'],
	$data['nav_link_3'],
	$data['nav_link_4'],
	$data['nav_link_5'],
) );
$nav_left    = $fls_has_people_matter ? array( 310, 496, 799, 1043, 1221 ) : array( 310, 566, 928, 1227 );
$nav_targets = array( '#q3-special-news', '#q3-whats-happening', '#q3-upcoming-events', '#q3-sponsorships', '#q3-people-matter' );
?>
<nav class="q3-cover__nav<?php echo $fls_has_people_matter ? '' : ' q3-cover__nav--without-people'; ?>">
		<?php foreach ( array_values( $nav_links ) as $i => $label ) : ?>
			<a class="q3-cover__nav-link" href="<?php echo esc_attr( $nav_targets[ $i ] ?? '#q3-contact' ); ?>" style="left:<?php echo esc_attr( $nav_left[ $i ] ?? 0 ); ?>px;"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
		<a class="q3-cover__contact-btn" href="https://fls-group.com/contact/"><?php echo esc_html( $data['contact_button_text'] ); ?></a>
		<button type="button" class="q3-cover__menu-icon" aria-expanded="false" aria-controls="q3-mobile-menu" aria-label="Open navigation"><i></i><i></i><i></i></button>
		<div id="q3-mobile-menu" class="q3-cover__mobile-menu" aria-hidden="true">
			<?php foreach ( array_values( $nav_links ) as $i => $label ) : ?>
				<a href="<?php echo esc_attr( $nav_targets[ $i ] ?? '#q3-contact' ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<a class="q3-cover__mobile-contact" href="https://fls-group.com/contact/"><?php echo esc_html( $data['contact_button_text'] ); ?></a>
		</div>
</nav>

<div class="q3-cover">
	<div class="q3-cover__masthead">
		<div class="q3-cover__eyebrow-row">
			<span class="q3-cover__rule"></span>
			<p class="q3-cover__eyebrow"><?php echo esc_html( $data['eyebrow_top'] ); ?></p>
			<span class="q3-cover__rule"></span>
		</div>
		<h1 class="q3-cover__title"><?php echo esc_html( $data['title_main'] ); ?></h1>
		<div class="q3-cover__meta">
			<span class="q3-cover__subtitle"><?php echo esc_html( $data['subtitle_label'] ); ?></span>
			<span class="q3-cover__divider"></span>
			<span class="q3-cover__issue"><?php echo esc_html( $data['issue_label'] ); ?></span>
		</div>
	</div>
</div>
<script>
(function () {
	var nav = document.querySelector( '.q3-cover__nav' );
	if ( ! nav ) return;
	var cover = document.querySelector( '.q3-cover' );
	var toggle = nav.querySelector( '.q3-cover__menu-icon' );
	var menu = nav.querySelector( '.q3-cover__mobile-menu' );
	function updateScrolled() {
		nav.classList.toggle( 'is-scrolled', window.scrollY > 8 );
	}
	updateScrolled();
	window.addEventListener( 'scroll', updateScrolled, { passive: true } );

	function setOpen( open ) {
		nav.classList.toggle( 'is-menu-open', open );
		if ( cover ) cover.classList.toggle( 'is-menu-open', open );
		document.body.classList.toggle( 'q3-menu-open', open );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', open ? 'Close navigation' : 'Open navigation' );
		menu.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
	}

	toggle.addEventListener( 'click', function () {
		setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
	} );
	menu.addEventListener( 'click', function ( event ) {
		if ( event.target === menu ) setOpen( false );
	} );

	nav.addEventListener( 'click', function ( event ) {
		var link = event.target.closest( 'a[href^="#"]' );
		if ( ! link ) return;
		var target = document.querySelector( link.hash );
		if ( ! target ) return;
		event.preventDefault();
		setOpen( false );
		window.scrollTo( {
			top: window.scrollY + target.getBoundingClientRect().top - nav.getBoundingClientRect().height,
			behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth'
		} );
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! nav.contains( event.target ) ) setOpen( false );
	} );
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' || toggle.getAttribute( 'aria-expanded' ) !== 'true' ) return;
		setOpen( false );
		toggle.focus();
	} );
	window.addEventListener( 'resize', function () {
		if ( window.matchMedia( '(min-width: 768px)' ).matches ) setOpen( false );
	} );
})();
</script>
