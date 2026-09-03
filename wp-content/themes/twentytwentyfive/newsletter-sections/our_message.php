<?php
/**
 * Section: Our Message
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data         = $fls_section_data;
$portrait_url = wp_get_attachment_image_url( $data['portrait_image'], 'full' );
$portrait_alt = get_post_meta( $data['portrait_image'], '_wp_attachment_image_alt', true );
$play_url     = wp_get_attachment_image_url( $data['play_icon'], 'full' );
$video_embed  = $data['video_embed'] ?? '';
?>
<section class="q3-our-message" aria-labelledby="q3-our-message-title">
	<h2 id="q3-our-message-title" class="q3-our-message__title"><?php echo esc_html( $data['title'] ); ?></h2>
	<div class="q3-our-message__portrait">
		<?php if ( $video_embed ) : ?>
			<template class="q3-our-message__video-template"><?php echo wp_kses( $video_embed, array( 'iframe' => array( 'title' => true, 'width' => true, 'height' => true, 'src' => true, 'frameborder' => true, 'allow' => true, 'referrerpolicy' => true, 'allowfullscreen' => true ) ) ); ?></template>
			<button type="button" class="q3-our-message__video-trigger" aria-label="<?php echo esc_attr( 'Play video: ' . $data['title'] ); ?>">
				<img class="q3-our-message__video-poster" src="<?php echo esc_url( $portrait_url ); ?>" alt="<?php echo esc_attr( $portrait_alt ); ?>">
				<?php if ( $play_url ) : ?>
					<img class="q3-our-message__video-play" src="<?php echo esc_url( $play_url ); ?>" alt="" aria-hidden="true">
				<?php endif; ?>
			</button>
		<?php else : ?>
			<img src="<?php echo esc_url( $portrait_url ); ?>" alt="<?php echo esc_attr( $portrait_alt ); ?>">
		<?php endif; ?>
	</div>
	<?php if ( ! $video_embed && $play_url ) : ?>
		<img class="q3-our-message__play" src="<?php echo esc_url( $play_url ); ?>" alt="" aria-hidden="true">
	<?php endif; ?>
	<div class="q3-our-message__copy">
		<p><?php echo esc_html( $data['intro_text'] ); ?></p>
		<p><?php echo esc_html( $data['body_text'] ); ?></p>
		<p><?php echo esc_html( $data['closing_text'] ); ?></p>
	</div>
</section>
<?php if ( $video_embed ) : ?>
<script>
(function () {
	var section = document.querySelector( '.q3-our-message' );
	var trigger = section && section.querySelector( '.q3-our-message__video-trigger' );
	if ( ! trigger ) return;
	trigger.addEventListener( 'click', function () {
		var template = section.querySelector( '.q3-our-message__video-template' );
		var player = document.createElement( 'div' );
		player.className = 'q3-our-message__video-embed';
		player.appendChild( template.content.cloneNode( true ) );
		var iframe = player.querySelector( 'iframe' );
		if ( ! iframe ) return;
		iframe.src += ( iframe.src.indexOf( '?' ) === -1 ? '?' : '&' ) + 'autoplay=1';
		trigger.replaceWith( player );
		iframe.focus();
	} );
})();
</script>
<?php endif; ?>
