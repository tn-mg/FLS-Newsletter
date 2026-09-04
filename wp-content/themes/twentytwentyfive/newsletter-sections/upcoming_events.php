<?php
/**
 * Section: Upcoming Events
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data  = $fls_section_data;
$image = static function ( $attachment_id ) {
	return array(
		'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
};

$oktoberfest = $image( $data['oktoberfest_image'] );
$golf_tournament = $image( $data['golf_tournament_image'] );
$company_trip = $image( $data['company_trip_image'] );
$oktoberfest_mobile = $image( $data['oktoberfest_mobile_image'] ?: $data['oktoberfest_image'] );
$golf_tournament_mobile = $image( $data['golf_tournament_mobile_image'] ?: $data['golf_tournament_image'] );
$company_trip_mobile = $image( $data['company_trip_mobile_image'] ?: $data['company_trip_image'] );
?>
<section id="q3-upcoming-events" class="q3-upcoming-events" aria-labelledby="q3-upcoming-events-title" data-fls-section="upcoming_events">
	<h2 id="q3-upcoming-events-title" class="q3-section-heading"><?php echo esc_html( $data['section_title'] ); ?></h2>
	<div class="q3-section-rule" aria-hidden="true"></div>

	<div class="q3-upcoming-events__viewport" aria-roledescription="carousel" aria-label="Upcoming events">
		<div class="q3-upcoming-events__rail">
			<article class="q3-upcoming-events__item q3-upcoming-events__item--oktoberfest">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( $oktoberfest_mobile['url'] ); ?>">
					<img src="<?php echo esc_url( $oktoberfest['url'] ); ?>" alt="<?php echo esc_attr( $oktoberfest['alt'] ); ?>" draggable="false">
				</picture>
				<div class="q3-upcoming-events__content">
					<h3><?php echo esc_html( $data['oktoberfest_title'] ); ?></h3>
					<p><?php echo esc_html( $data['oktoberfest_copy'] ); ?></p>
				</div>
			</article>

			<article class="q3-upcoming-events__item q3-upcoming-events__item--golf">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( $golf_tournament_mobile['url'] ); ?>">
					<img src="<?php echo esc_url( $golf_tournament['url'] ); ?>" alt="<?php echo esc_attr( $golf_tournament['alt'] ); ?>" draggable="false">
				</picture>
				<div class="q3-upcoming-events__content">
					<h3><?php echo esc_html( $data['golf_title'] ); ?></h3>
					<p><?php echo esc_html( $data['golf_copy'] ); ?></p>
				</div>
			</article>

			<article class="q3-upcoming-events__item q3-upcoming-events__item--company-trip">
				<picture>
					<source media="(max-width: 767px)" srcset="<?php echo esc_url( $company_trip_mobile['url'] ); ?>">
					<img src="<?php echo esc_url( $company_trip['url'] ); ?>" alt="<?php echo esc_attr( $company_trip['alt'] ); ?>" draggable="false">
				</picture>
				<div class="q3-upcoming-events__content">
					<h3><?php echo esc_html( $data['company_trip_title'] ); ?></h3>
					<p><?php echo esc_html( $data['company_trip_copy'] ); ?></p>
				</div>
			</article>
		</div>
	</div>
	<div class="q3-upcoming-events__pagination" role="group" aria-label="Choose event">
		<?php for ( $index = 0; $index < 3; $index++ ) : ?>
			<button type="button" aria-label="<?php echo esc_attr( 'Show event ' . ( $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
		<?php endfor; ?>
	</div>
</section>
<script>
(function () {
	var section = document.getElementById( 'q3-upcoming-events' );
	if ( ! section ) return;
	var viewport = section.querySelector( '.q3-upcoming-events__viewport' );
	var rail = section.querySelector( '.q3-upcoming-events__rail' );
	var cards = section.querySelectorAll( '.q3-upcoming-events__item' );
	var dots = section.querySelectorAll( '.q3-upcoming-events__pagination button' );
	var current = 0;
	function show( index ) {
		var isMobile = window.matchMedia( '(max-width: 767px)' ).matches;
		var isTablet = window.matchMedia( '(min-width: 768px) and (max-width: 1199px)' ).matches;
		var perView = isTablet ? 2 : 1;
		var last = Math.max( 0, cards.length - perView );
		current = Math.max( 0, Math.min( cards.length - 1, index ) );
		current = Math.min( current, last );
		rail.style.transform = isMobile || isTablet ? 'translateX(' + ( current * ( isMobile ? -440 : -480 ) ) + 'px)' : '';
		dots.forEach( function ( dot, dotIndex ) {
			dot.hidden = last === 0 || dotIndex > last;
			dot.setAttribute( 'aria-current', dotIndex === current ? 'true' : 'false' );
		} );
	}
	dots.forEach( function ( dot, index ) { dot.addEventListener( 'click', function () { show( index ); } ); } );
	window.flsNewsletterBindSwipe( viewport, function ( direction ) {
		if ( window.matchMedia( '(max-width: 1199px)' ).matches ) show( current + direction );
	} );
	window.addEventListener( 'resize', function () { show( current ); } );
	show( current );
})();
</script>
