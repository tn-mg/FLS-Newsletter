<?php
/**
 * Section: Newsletter Footer
 * Expects $fls_section_data from the matching ACF Group field.
 */

$data    = $fls_section_data;
$map_url = wp_get_attachment_image_url( $data['map_image'], 'full' );
$map_alt = get_post_meta( $data['map_image'], '_wp_attachment_image_alt', true );
$email   = sanitize_email( $data['email'] );
?>
<footer id="q3-contact" class="q3-newsletter-footer">
	<h2><?php echo nl2br( esc_html( $data['title'] ) ); ?></h2>
	<p class="q3-newsletter-footer__copy"><?php echo esc_html( $data['copy'] ); ?></p>
	<a class="q3-newsletter-footer__email" href="mailto:<?php echo esc_attr( $email ); ?>"><span><?php echo esc_html( $data['email_label'] ); ?> <strong><?php echo esc_html( $email ); ?></strong></span></a>
	<img class="q3-newsletter-footer__map" src="<?php echo esc_url( $map_url ); ?>" alt="<?php echo esc_attr( $map_alt ); ?>">
	<p class="q3-newsletter-footer__social-label"><?php echo esc_html( $data['social_label'] ); ?></p>
	<div class="q3-newsletter-footer__socials" aria-label="FLS Group social platforms">
		<a class="q3-newsletter-footer__website" href="https://fls-group.com/" target="_blank" rel="noopener noreferrer" aria-label="FLS Group website"><img src="<?php echo esc_url( plugins_url( 'fls-newsletter/assets/q3-2026/social-website.svg' ) ); ?>" alt=""></a>
		<a class="q3-newsletter-footer__facebook" href="https://www.facebook.com/FLSGroup1993" target="_blank" rel="noopener noreferrer" aria-label="FLS Group on Facebook"><img src="<?php echo esc_url( plugins_url( 'fls-newsletter/assets/q3-2026/social-facebook.svg' ) ); ?>" alt=""></a>
		<a class="q3-newsletter-footer__linkedin" href="https://www.linkedin.com/company/flsgroup/" target="_blank" rel="noopener noreferrer" aria-label="FLS Group on LinkedIn"><img src="<?php echo esc_url( plugins_url( 'fls-newsletter/assets/q3-2026/social-linkedin.svg' ) ); ?>" alt=""></a>
		<a class="q3-newsletter-footer__youtube" href="https://www.youtube.com/c/FLSGroup" target="_blank" rel="noopener noreferrer" aria-label="FLS Group on YouTube"><img src="<?php echo esc_url( plugins_url( 'fls-newsletter/assets/q3-2026/social-youtube.svg' ) ); ?>" alt=""></a>
	</div>
	<p class="q3-newsletter-footer__countries"><?php echo esc_html( $data['countries'] ); ?></p>
</footer>
