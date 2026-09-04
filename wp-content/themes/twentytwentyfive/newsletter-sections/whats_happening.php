<?php
/**
 * Section: What's Been Happening
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data      = $fls_section_data;
$luzon     = $data['luzon'];
$carousel  = $data['carousel'];
$cards     = $carousel['cards'] ?? array();
$warehouse = $data['warehouse'];
$trading   = $data['trading'];

$image = static function ( $attachment_id ) {
	return array(
		'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
};

$luzon_background = $image( $luzon['video_background'] );
$luzon_frame      = $image( $luzon['video_frame'] );
$luzon_play       = $image( $luzon['video_play_icon'] );
$luzon_video      = $luzon['video_embed'] ?? '';
$carousel_arrow   = $image( $carousel['arrow_icon'] );
$warehouse_top    = $image( $warehouse['image_top'] );
$warehouse_bottom = $image( $warehouse['image_bottom'] );
$trading_top      = $image( $trading['image_top'] );
$trading_bottom   = $image( $trading['image_bottom'] );
$rail_width       = $cards ? ( ( count( $cards ) - 1 ) * 1300.764 ) + 1263 : 0;
?>
<section id="q3-whats-happening" class="q3-whats-happening" aria-labelledby="q3-whats-happening-title" data-fls-section="project_updates">
	<h2 id="q3-whats-happening-title" class="q3-whats-happening__section-title"><?php echo esc_html( $data['section_title'] ); ?></h2>
	<div class="q3-whats-happening__rule" aria-hidden="true"></div>
	<p class="q3-whats-happening__intro"><?php echo esc_html( $data['intro_text'] ); ?></p>

	<h3 class="q3-whats-happening__luzon-title"><?php echo nl2br( esc_html( $luzon['title'] ) ); ?></h3>
	<div class="q3-whats-happening__tags q3-whats-happening__luzon-tags">
		<span><?php echo esc_html( $luzon['country'] ); ?></span>
		<span><?php echo esc_html( $luzon['category'] ); ?></span>
	</div>
	<div class="q3-whats-happening__luzon-copy-wrap">
		<p class="q3-whats-happening__luzon-copy-left"><?php echo esc_html( $luzon['left_copy'] ); ?></p>
		<p class="q3-whats-happening__luzon-copy-right"><?php echo esc_html( $luzon['right_copy'] ); ?></p>
	</div>
	<div class="q3-whats-happening__luzon-video">
		<?php if ( $luzon_video ) : ?>
			<template class="q3-whats-happening__luzon-video-template"><?php echo wp_kses( $luzon_video, array( 'iframe' => array( 'title' => true, 'width' => true, 'height' => true, 'src' => true, 'frameborder' => true, 'allow' => true, 'referrerpolicy' => true, 'allowfullscreen' => true ) ) ); ?></template>
			<button type="button" class="q3-whats-happening__luzon-video-trigger" aria-label="<?php echo esc_attr( 'Play video: ' . $luzon['title'] ); ?>" data-fls-track="video_play" data-fls-video="luzon_project">
				<img class="q3-whats-happening__luzon-video-background" src="<?php echo esc_url( $luzon_background['url'] ); ?>" alt="<?php echo esc_attr( $luzon_background['alt'] ); ?>">
				<img class="q3-whats-happening__luzon-video-play" src="<?php echo esc_url( $luzon_play['url'] ); ?>" alt="" aria-hidden="true">
			</button>
		<?php else : ?>
			<img class="q3-whats-happening__luzon-video-background" src="<?php echo esc_url( $luzon_background['url'] ); ?>" alt="<?php echo esc_attr( $luzon_background['alt'] ); ?>">
			<img class="q3-whats-happening__luzon-video-play" src="<?php echo esc_url( $luzon_play['url'] ); ?>" alt="" aria-hidden="true">
		<?php endif; ?>
		<img class="q3-whats-happening__luzon-video-frame" src="<?php echo esc_url( $luzon_frame['url'] ); ?>" alt="" aria-hidden="true">
	</div>

	<div class="q3-whats-happening__carousel-viewport">
		<div class="q3-whats-happening__carousel-rail" style="width:<?php echo esc_attr( $rail_width ); ?>px;">
			<?php foreach ( $cards as $index => $card ) : ?>
				<?php $card_image = $image( $card['image'] ); ?>
				<article class="q3-whats-happening__carousel-card q3-whats-happening__carousel-card--<?php echo 0 === $index ? 'first' : 'subsequent'; ?>" style="left:<?php echo esc_attr( $index * 1300.764 ); ?>px;">
					<img class="q3-whats-happening__carousel-image" src="<?php echo esc_url( $card_image['url'] ); ?>" alt="<?php echo esc_attr( $card_image['alt'] ); ?>">
					<div class="q3-whats-happening__tags q3-whats-happening__carousel-tags">
						<span><?php echo esc_html( $card['country'] ); ?></span>
						<?php if ( $card['country_2'] ) : ?><span><?php echo esc_html( $card['country_2'] ); ?></span><?php endif; ?>
						<span><?php echo esc_html( $card['category'] ); ?></span>
					</div>
					<h3><?php echo nl2br( esc_html( $card['title'] ) ); ?></h3>
					<div class="q3-whats-happening__carousel-copy"><?php echo wp_kses_post( $card['copy'] ); ?></div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="q3-whats-happening__carousel-pagination" role="group" aria-label="Choose project">
		<?php foreach ( $cards as $index => $card ) : ?>
			<button type="button" aria-label="<?php echo esc_attr( 'Show project ' . ( $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
		<?php endforeach; ?>
	</div>
	<?php if ( count( $cards ) > 1 ) : ?>
		<button type="button" class="q3-whats-happening__carousel-back" aria-label="Previous project"><img src="<?php echo esc_url( $carousel_arrow['url'] ); ?>" alt=""></button>
		<button type="button" class="q3-whats-happening__carousel-next" aria-label="Next project"><img src="<?php echo esc_url( $carousel_arrow['url'] ); ?>" alt=""></button>
	<?php endif; ?>

	<article class="q3-whats-happening__warehouse">
		<div class="q3-whats-happening__story-gallery" aria-roledescription="carousel" aria-label="Warehousing project images">
			<div class="q3-whats-happening__story-gallery-rail">
				<img class="q3-whats-happening__warehouse-image-bottom" src="<?php echo esc_url( $warehouse_bottom['url'] ); ?>" alt="<?php echo esc_attr( $warehouse_bottom['alt'] ); ?>" draggable="false">
				<img class="q3-whats-happening__warehouse-image-top" src="<?php echo esc_url( $warehouse_top['url'] ); ?>" alt="<?php echo esc_attr( $warehouse_top['alt'] ); ?>" draggable="false">
			</div>
			<div class="q3-whats-happening__story-gallery-pagination" role="group" aria-label="Choose warehousing image">
				<button type="button" aria-label="Show warehousing image 1" aria-current="true"></button>
				<button type="button" aria-label="Show warehousing image 2" aria-current="false"></button>
			</div>
		</div>
		<div class="q3-whats-happening__tags q3-whats-happening__warehouse-tags"><span><?php echo esc_html( $warehouse['country'] ); ?></span><span class="q3-whats-happening__tag-wide"><?php echo esc_html( $warehouse['category'] ); ?></span></div>
		<h3><?php echo nl2br( esc_html( $warehouse['title'] ) ); ?></h3>
		<div class="q3-whats-happening__story-copy"><?php echo wp_kses_post( $warehouse['copy'] ); ?></div>
	</article>

	<article class="q3-whats-happening__trading">
		<div class="q3-whats-happening__story-gallery" aria-roledescription="carousel" aria-label="Trading project images">
			<div class="q3-whats-happening__story-gallery-rail">
				<img class="q3-whats-happening__trading-image-top" src="<?php echo esc_url( $trading_top['url'] ); ?>" alt="<?php echo esc_attr( $trading_top['alt'] ); ?>" draggable="false">
				<img class="q3-whats-happening__trading-image-bottom" src="<?php echo esc_url( $trading_bottom['url'] ); ?>" alt="<?php echo esc_attr( $trading_bottom['alt'] ); ?>" draggable="false">
			</div>
			<div class="q3-whats-happening__story-gallery-pagination" role="group" aria-label="Choose trading image">
				<button type="button" aria-label="Show trading image 1" aria-current="true"></button>
				<button type="button" aria-label="Show trading image 2" aria-current="false"></button>
			</div>
		</div>
		<div class="q3-whats-happening__tags q3-whats-happening__trading-tags"><span><?php echo esc_html( $trading['country'] ); ?></span><span><?php echo esc_html( $trading['category'] ); ?></span></div>
		<h3><?php echo nl2br( esc_html( $trading['title'] ) ); ?></h3>
		<div class="q3-whats-happening__story-copy"><?php echo wp_kses_post( $trading['copy'] ); ?></div>
	</article>
</section>
<script>
(function () {
	var section = document.getElementById( 'q3-whats-happening' );
	if ( ! section ) return;
	var videoTrigger = section.querySelector( '.q3-whats-happening__luzon-video-trigger' );
	if ( videoTrigger ) {
		videoTrigger.addEventListener( 'click', function () {
			var template = section.querySelector( '.q3-whats-happening__luzon-video-template' );
			var player = document.createElement( 'div' );
			player.className = 'q3-whats-happening__luzon-video-embed';
			player.appendChild( template.content.cloneNode( true ) );
			var iframe = player.querySelector( 'iframe' );
			iframe.src += ( iframe.src.indexOf( '?' ) === -1 ? '?' : '&' ) + 'autoplay=1';
			videoTrigger.replaceWith( player );
			iframe.focus();
		} );
	}
	var rail = section.querySelector( '.q3-whats-happening__carousel-rail' );
	var viewport = section.querySelector( '.q3-whats-happening__carousel-viewport' );
	var dots = section.querySelectorAll( '.q3-whats-happening__carousel-pagination button' );
	var back = section.querySelector( '.q3-whats-happening__carousel-back' );
	var next = section.querySelector( '.q3-whats-happening__carousel-next' );
	var cards = rail.querySelectorAll( '.q3-whats-happening__carousel-card' );
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
	section.querySelectorAll( '.q3-whats-happening__story-gallery' ).forEach( function ( gallery ) {
		var galleryRail = gallery.querySelector( '.q3-whats-happening__story-gallery-rail' );
		var galleryDots = gallery.querySelectorAll( '.q3-whats-happening__story-gallery-pagination button' );
		var galleryCurrent = 0;
		function showGalleryImage( index ) {
			galleryCurrent = Math.max( 0, Math.min( galleryDots.length - 1, index ) );
			galleryRail.style.transform = window.matchMedia( '(max-width: 767px)' ).matches ? 'translateX(' + ( galleryCurrent * -440 ) + 'px)' : '';
			galleryDots.forEach( function ( dot, dotIndex ) {
				dot.setAttribute( 'aria-current', dotIndex === galleryCurrent ? 'true' : 'false' );
			} );
		}
		galleryDots.forEach( function ( dot, index ) { dot.addEventListener( 'click', function () { showGalleryImage( index ); } ); } );
		window.flsNewsletterBindSwipe( gallery, function ( direction ) {
			if ( window.matchMedia( '(max-width: 767px)' ).matches ) showGalleryImage( galleryCurrent + direction );
		} );
		window.addEventListener( 'resize', function () { showGalleryImage( galleryCurrent ); } );
	} );
	window.addEventListener( 'resize', function () { show( current ); } );
	show( current );
})();
</script>
