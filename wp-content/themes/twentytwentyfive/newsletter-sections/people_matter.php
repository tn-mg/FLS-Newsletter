<?php
/**
 * Section: People Matter
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data  = $fls_section_data;
$image = static function ( $attachment_id ) {
	return array(
		'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
};

$arrow  = $image( $data['arrow_icon'] );
$people = array();
foreach ( $data['people'] ?? array() as $person ) {
	$people[] = $image( $person['image'] );
}

$initial_positions = array(
	'is-far-left',
	'is-left',
	'is-center',
	'is-right',
	'is-far-right',
);
?>
<section id="q3-people-matter" class="q3-people-matter" aria-labelledby="q3-people-matter-title" data-fls-section="people_matter">
	<h2 id="q3-people-matter-title" class="q3-section-heading"><?php echo esc_html( $data['section_title'] ); ?></h2>
	<div class="q3-section-rule" aria-hidden="true"></div>
	<p class="q3-people-matter__label"><?php echo esc_html( $data['label'] ); ?></p>

	<div class="q3-people-matter__carousel">
		<?php foreach ( $people as $index => $person ) : ?>
			<?php $position = $initial_positions[ $index ] ?? 'is-hidden-right'; ?>
			<div class="q3-people-matter__card <?php echo esc_attr( $position ); ?>" data-person-index="<?php echo esc_attr( $index ); ?>" <?php echo $index > 4 ? 'aria-hidden="true"' : ''; ?>>
				<img src="<?php echo esc_url( $person['url'] ); ?>" alt="<?php echo esc_attr( $person['alt'] ); ?>" loading="lazy" decoding="async">
			</div>
		<?php endforeach; ?>
		<button type="button" class="q3-people-matter__back" aria-label="Previous newcomer"><img src="<?php echo esc_url( $arrow['url'] ); ?>" alt=""></button>
		<button type="button" class="q3-people-matter__next" aria-label="Next newcomer"><img src="<?php echo esc_url( $arrow['url'] ); ?>" alt=""></button>
	</div>
</section>
<script>
(function () {
	var section = document.getElementById( 'q3-people-matter' );
	if ( ! section ) return;
	var cards = Array.prototype.slice.call( section.querySelectorAll( '.q3-people-matter__card' ) );
	if ( ! cards.length ) return;

	var activeIndex = Math.min( 2, cards.length - 1 );
	var positionClasses = [ 'is-hidden-left', 'is-far-left', 'is-left', 'is-center', 'is-right', 'is-far-right', 'is-hidden-right' ];

	function getCircularDistance( index ) {
		var distance = index - activeIndex;
		var midpoint = Math.floor( cards.length / 2 );
		if ( distance > midpoint ) distance -= cards.length;
		if ( distance < -midpoint ) distance += cards.length;
		return distance;
	}

	function render() {
		cards.forEach( function ( card, index ) {
			var distance = getCircularDistance( index );
			var position = distance < -2 ? 'is-hidden-left' : distance > 2 ? 'is-hidden-right' : positionClasses[ distance + 3 ];
			positionClasses.forEach( function ( className ) { card.classList.remove( className ); } );
			card.classList.add( position );
			card.setAttribute( 'aria-hidden', Math.abs( distance ) > 2 ? 'true' : 'false' );
			if ( distance === 0 ) {
				card.setAttribute( 'aria-current', 'true' );
			} else {
				card.removeAttribute( 'aria-current' );
			}
		} );
	}

	function move( step ) {
		activeIndex = ( activeIndex + step + cards.length ) % cards.length;
		render();
	}

	render();
	window.requestAnimationFrame( function () {
		window.requestAnimationFrame( function () { section.classList.add( 'is-ready' ); } );
	} );
	section.querySelector( '.q3-people-matter__back' ).addEventListener( 'click', function () { move( -1 ); } );
	section.querySelector( '.q3-people-matter__next' ).addEventListener( 'click', function () { move( 1 ); } );
	window.flsNewsletterBindSwipe( section.querySelector( '.q3-people-matter__carousel' ), move );
})();
</script>
