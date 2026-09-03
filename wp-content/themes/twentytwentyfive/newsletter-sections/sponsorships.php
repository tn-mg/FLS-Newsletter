<?php
/**
 * Section: Sponsorships
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data       = $fls_section_data;
$image = static function ( $attachment_id ) {
	return array(
		'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
};

$cards      = $data['cards'] ?? array();
$arrow      = $image( $data['arrow_icon'] );
$rail_width = $cards ? ( ( count( $cards ) - 1 ) * 1300.764 ) + 1263 : 0;
?>
<section id="q3-sponsorships" class="q3-sponsorships" aria-labelledby="q3-sponsorships-title">
	<h2 id="q3-sponsorships-title" class="q3-section-heading"><?php echo esc_html( $data['section_title'] ); ?></h2>
	<div class="q3-section-rule" aria-hidden="true"></div>
	<div class="q3-sponsorships__intro"><?php echo wp_kses_post( $data['intro_copy'] ); ?></div>

	<div class="q3-sponsorships__viewport">
		<div class="q3-sponsorships__rail" style="width:<?php echo esc_attr( $rail_width ); ?>px;">
			<?php foreach ( $cards as $index => $card ) : ?>
				<?php $card_image = $image( $card['image'] ); ?>
				<article class="q3-sponsorships__card q3-sponsorships__card--<?php echo 0 === $index ? 'first' : 'subsequent'; ?>" style="left:<?php echo esc_attr( $index * 1300.764 ); ?>px;">
					<img class="q3-sponsorships__image" src="<?php echo esc_url( $card_image['url'] ); ?>" alt="<?php echo esc_attr( $card_image['alt'] ); ?>">
					<h3><?php echo nl2br( esc_html( $card['title'] ) ); ?></h3>
					<div class="q3-sponsorships__copy"><?php echo wp_kses_post( $card['copy'] ); ?></div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="q3-sponsorships__pagination" role="group" aria-label="Choose sponsorship">
		<?php foreach ( $cards as $index => $card ) : ?>
			<button type="button" aria-label="<?php echo esc_attr( 'Show sponsorship ' . ( $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
		<?php endforeach; ?>
	</div>
	<?php if ( count( $cards ) > 1 ) : ?>
		<button type="button" class="q3-sponsorships__back" aria-label="Previous sponsorship"><img src="<?php echo esc_url( $arrow['url'] ); ?>" alt=""></button>
		<button type="button" class="q3-sponsorships__next" aria-label="Next sponsorship"><img src="<?php echo esc_url( $arrow['url'] ); ?>" alt=""></button>
	<?php endif; ?>
</section>
<script>
(function () {
	var section = document.getElementById( 'q3-sponsorships' );
	if ( ! section ) return;
	var rail = section.querySelector( '.q3-sponsorships__rail' );
	var viewport = section.querySelector( '.q3-sponsorships__viewport' );
	var dots = section.querySelectorAll( '.q3-sponsorships__pagination button' );
	var back = section.querySelector( '.q3-sponsorships__back' );
	var next = section.querySelector( '.q3-sponsorships__next' );
	var cards = rail.querySelectorAll( '.q3-sponsorships__card' );
	var current = 0;
	function show( index ) {
		var isTablet = window.matchMedia( '(min-width: 768px) and (max-width: 1199px)' ).matches;
		var perView = isTablet ? 2 : 1;
		var last = Math.max( 0, cards.length - perView );
		current = Math.max( 0, Math.min( last, index ) );
		var step = window.matchMedia( '(max-width: 767px)' ).matches ? 440 : ( isTablet ? 480 : 1300.764 );
		rail.style.transform = 'translateX(' + ( current * -step ) + 'px)';
		dots.forEach( function ( dot, dotIndex ) {
			dot.hidden = last === 0 || dotIndex > last;
			dot.setAttribute( 'aria-current', dotIndex === current ? 'true' : 'false' );
		} );
	}
	dots.forEach( function ( dot, index ) { dot.addEventListener( 'click', function () { show( index ); } ); } );
	if ( back ) back.addEventListener( 'click', function () { show( current - 1 ); } );
	if ( next ) next.addEventListener( 'click', function () { show( current + 1 ); } );
	window.flsNewsletterBindSwipe( viewport, function ( direction ) { show( current + direction ); } );
	window.addEventListener( 'resize', function () { show( current ); } );
	show( current );
})();
</script>
