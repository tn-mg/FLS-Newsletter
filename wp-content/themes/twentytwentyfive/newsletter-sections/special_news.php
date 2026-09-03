<?php
/**
 * Section: Special News
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data = $fls_section_data;

$image = static function ( $attachment_id ) {
	return array(
		'url' => wp_get_attachment_image_url( $attachment_id, 'full' ),
		'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
	);
};

$award_image          = $image( $data['award_image'] );
$award_mobile_image   = ! empty( $data['award_mobile_image'] ) ? $image( $data['award_mobile_image'] ) : null;
$gallery_ids          = $data['collage_image'] ?? array();
$gallery_ids          = is_array( $gallery_ids ) ? $gallery_ids : array( $gallery_ids );
$gallery_ids          = array_values( array_filter( array_map( 'absint', $gallery_ids ) ) );
$gallery_images       = array_map( $image, $gallery_ids );
$video_poster         = $image( $data['video_poster'] );
$video_frame          = $image( $data['video_frame'] );
$video_play           = $image( $data['video_play_icon'] );
$video_embed          = $data['video_embed'] ?? '';
$video_src            = '';

if ( preg_match( '/<iframe[^>]+src=["\']([^"\']+)/i', $video_embed, $video_match ) ) {
	$video_src = html_entity_decode( $video_match[1] );
} elseif ( preg_match( '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:embed/|watch\?(?:[^#\s]*&)?v=))([A-Za-z0-9_-]{11})~i', html_entity_decode( $video_embed ), $video_match ) ) {
	$video_src = 'https://www.youtube-nocookie.com/embed/' . $video_match[1];
}
?>
<section id="q3-special-news" class="q3-special-news" aria-labelledby="q3-special-news-title">
	<h2 id="q3-special-news-title" class="q3-special-news__section-title"><?php echo esc_html( $data['section_title'] ); ?></h2>
	<div class="q3-special-news__rule" aria-hidden="true"></div>

	<picture>
		<?php if ( $award_mobile_image ) : ?>
			<source media="(max-width: 767px)" srcset="<?php echo esc_url( $award_mobile_image['url'] ); ?>">
		<?php endif; ?>
		<img class="q3-special-news__award-image" src="<?php echo esc_url( $award_image['url'] ); ?>" alt="<?php echo esc_attr( $award_mobile_image['alt'] ?? $award_image['alt'] ); ?>">
	</picture>
	<h3 class="q3-special-news__award-title"><?php echo nl2br( esc_html( $data['award_title'] ) ); ?></h3>
	<span class="q3-special-news__tag q3-special-news__award-tag"><?php echo esc_html( $data['award_tag'] ); ?></span>
	<div class="q3-special-news__award-copy-wrap">
		<div class="q3-special-news__copy q3-special-news__award-copy-left"><?php echo wp_kses_post( $data['award_left_copy'] ); ?></div>
		<div class="q3-special-news__copy q3-special-news__award-copy-right"><?php echo wp_kses_post( $data['award_right_copy'] ); ?></div>
	</div>
	<?php if ( $data['award_cta_url'] ) : ?>
		<a class="q3-special-news__cta" href="<?php echo esc_url( $data['award_cta_url'] ); ?>"><?php echo esc_html( $data['award_cta_label'] ); ?></a>
	<?php else : ?>
		<span class="q3-special-news__cta"><?php echo esc_html( $data['award_cta_label'] ); ?></span>
	<?php endif; ?>
	<?php if ( $gallery_images ) : ?>
		<div class="q3-special-news__gallery" aria-label="EV Truck gallery">
			<div class="q3-special-news__gallery-desktop">
				<?php foreach ( $gallery_images as $index => $gallery_image ) : ?>
					<button type="button" class="q3-special-news__gallery-item" data-full-image="<?php echo esc_url( $gallery_image['url'] ); ?>" data-full-alt="<?php echo esc_attr( $gallery_image['alt'] ); ?>" aria-label="<?php echo esc_attr( 'View EV Truck image ' . ( $index + 1 ) . ' full size' ); ?>">
						<img src="<?php echo esc_url( $gallery_image['url'] ); ?>" alt="<?php echo esc_attr( $gallery_image['alt'] ); ?>" loading="lazy" decoding="async">
					</button>
				<?php endforeach; ?>
			</div>
			<div class="q3-special-news__gallery-slider" aria-roledescription="carousel" aria-label="EV Truck images">
				<div class="q3-special-news__gallery-viewport">
					<div class="q3-special-news__gallery-rail">
						<?php foreach ( $gallery_images as $index => $gallery_image ) : ?>
							<figure class="q3-special-news__gallery-slide" aria-label="<?php echo esc_attr( ( $index + 1 ) . ' of ' . count( $gallery_images ) ); ?>">
								<img src="<?php echo esc_url( $gallery_image['url'] ); ?>" alt="<?php echo esc_attr( $gallery_image['alt'] ); ?>" loading="lazy" decoding="async" draggable="false">
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ( count( $gallery_images ) > 1 ) : ?>
				<div class="q3-special-news__gallery-pagination" role="group" aria-label="Choose EV Truck image">
					<?php foreach ( $gallery_images as $index => $gallery_image ) : ?>
						<button type="button" aria-label="<?php echo esc_attr( 'Show EV Truck image ' . ( $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<h3 class="q3-special-news__breakbulk-title"><?php echo nl2br( esc_html( $data['breakbulk_title'] ) ); ?></h3>
	<span class="q3-special-news__tag q3-special-news__breakbulk-tag"><?php echo esc_html( $data['breakbulk_tag'] ); ?></span>
	<div class="q3-special-news__breakbulk-copy-wrap">
		<div class="q3-special-news__copy q3-special-news__breakbulk-copy-left"><?php echo wp_kses_post( $data['breakbulk_left_copy'] ); ?></div>
		<div class="q3-special-news__copy q3-special-news__breakbulk-copy-right"><?php echo wp_kses_post( $data['breakbulk_right_copy'] ); ?></div>
	</div>
	<div class="q3-special-news__video">
		<?php if ( $video_src ) : ?>
			<template class="q3-special-news__video-template"><iframe title="<?php echo esc_attr( $data['breakbulk_title'] ); ?>" src="<?php echo esc_url( $video_src ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></template>
			<button type="button" class="q3-special-news__video-trigger" aria-label="<?php echo esc_attr( 'Play video: ' . $data['breakbulk_title'] ); ?>">
				<img class="q3-special-news__video-poster" src="<?php echo esc_url( $video_poster['url'] ); ?>" alt="<?php echo esc_attr( $video_poster['alt'] ); ?>">
				<img class="q3-special-news__video-play" src="<?php echo esc_url( $video_play['url'] ); ?>" alt="" aria-hidden="true">
			</button>
		<?php else : ?>
			<img class="q3-special-news__video-poster" src="<?php echo esc_url( $video_poster['url'] ); ?>" alt="<?php echo esc_attr( $video_poster['alt'] ); ?>">
			<img class="q3-special-news__video-play" src="<?php echo esc_url( $video_play['url'] ); ?>" alt="" aria-hidden="true">
		<?php endif; ?>
		<img class="q3-special-news__video-frame" src="<?php echo esc_url( $video_frame['url'] ); ?>" alt="" aria-hidden="true">
	</div>
</section>
<script>
(function () {
	var section = document.querySelector( '.q3-special-news' );
	var videoTrigger = section && section.querySelector( '.q3-special-news__video-trigger' );
	if ( videoTrigger ) {
		videoTrigger.addEventListener( 'click', function () {
			var template = section.querySelector( '.q3-special-news__video-template' );
			var player = document.createElement( 'div' );
			player.className = 'q3-special-news__video-embed';
			player.appendChild( template.content.cloneNode( true ) );
			var iframe = player.querySelector( 'iframe' );
			if ( ! iframe ) return;
			iframe.src += ( iframe.src.indexOf( '?' ) === -1 ? '?' : '&' ) + 'autoplay=1';
			videoTrigger.replaceWith( player );
			iframe.focus();
		} );
	}

	var gallery = section && section.querySelector( '.q3-special-news__gallery' );
	if ( ! gallery ) return;
	var rail = gallery.querySelector( '.q3-special-news__gallery-rail' );
	var viewport = gallery.querySelector( '.q3-special-news__gallery-viewport' );
	var slides = gallery.querySelectorAll( '.q3-special-news__gallery-slide' );
	var dots = gallery.querySelectorAll( '.q3-special-news__gallery-pagination button' );
	var current = 0;
	function show( index ) {
		var perView = window.matchMedia( '(min-width: 768px) and (max-width: 1199px)' ).matches ? 2 : 1;
		var pages = Math.max( 1, slides.length - perView + 1 );
		current = ( index + pages ) % pages;
		rail.style.transform = 'translateX(' + ( current * -100 / perView ) + '%)';
		dots.forEach( function ( dot, dotIndex ) {
			dot.hidden = pages <= 1 || dotIndex >= pages;
			dot.setAttribute( 'aria-current', dotIndex === current ? 'true' : 'false' );
		} );
	}
	dots.forEach( function ( dot, index ) { dot.addEventListener( 'click', function () { show( index ); } ); } );
	window.flsNewsletterBindSwipe( viewport, function ( direction ) { show( current + direction ); } );
	window.addEventListener( 'resize', function () { show( current ); } );
	show( current );

	gallery.querySelectorAll( '.q3-special-news__gallery-item' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			var lightbox = document.createElement( 'div' );
			var fullImage = document.createElement( 'img' );
			var closeButton = document.createElement( 'button' );
			lightbox.className = 'q3-gallery-lightbox';
			lightbox.setAttribute( 'role', 'dialog' );
			lightbox.setAttribute( 'aria-modal', 'true' );
			lightbox.setAttribute( 'aria-label', 'Full-size EV Truck image' );
			fullImage.src = trigger.dataset.fullImage;
			fullImage.alt = trigger.dataset.fullAlt;
			closeButton.type = 'button';
			closeButton.className = 'q3-gallery-lightbox__close';
			closeButton.setAttribute( 'aria-label', 'Close full-size image' );
			closeButton.textContent = '×';
			lightbox.appendChild( fullImage );
			lightbox.appendChild( closeButton );
			document.body.appendChild( lightbox );
			document.body.classList.add( 'q3-gallery-lightbox-open' );
			closeButton.focus();

			function close() {
				lightbox.remove();
				document.body.classList.remove( 'q3-gallery-lightbox-open' );
				document.removeEventListener( 'keydown', onKeydown );
				trigger.focus();
			}
			function onKeydown( event ) {
				if ( event.key === 'Escape' ) close();
			}
			closeButton.addEventListener( 'click', close );
			lightbox.addEventListener( 'click', function ( event ) {
				if ( event.target === lightbox ) close();
			} );
			document.addEventListener( 'keydown', onKeydown );
		} );
	} );
})();
</script>
