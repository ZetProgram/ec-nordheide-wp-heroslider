<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Slide -> Props
 */
function hcs_build_slide_props( $slide_id ) {
	$active     = get_post_meta( $slide_id, '_hcs_is_active', true ) === '1';
	$expires_at = trim( (string) get_post_meta( $slide_id, '_hcs_expires_at', true ) );
	if ( ! $active ) return null;
	if ( $expires_at !== '' ) {
		$exp_ts = strtotime( $expires_at );
		if ( $exp_ts && time() > $exp_ts ) return null;
	}

	$title      = get_post_meta( $slide_id, '_hcs_title', true );
	$subtitle   = get_post_meta( $slide_id, '_hcs_subtitle', true );
	$text       = wpautop( (string) get_post_meta( $slide_id, '_hcs_text', true ) );
	$img_id     = (int) get_post_meta( $slide_id, '_hcs_bg_id', true );
	$img_url    = $img_id ? wp_get_attachment_image_url( $img_id, 'full' ) : '';
	$logo_id    = (int) get_post_meta( $slide_id, '_hcs_logo_id', true );
	$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$cta_label  = get_post_meta( $slide_id, '_hcs_cta_label', true );
	$cta_url    = get_post_meta( $slide_id, '_hcs_cta_url', true );
	$cta_nf     = get_post_meta( $slide_id, '_hcs_cta_nf', true ) === '1';
	$expires_at = trim( (string) get_post_meta( $slide_id, '_hcs_expires_at', true ) );

	return array(
		'title'      => (string) $title,
		'subtitle'   => (string) $subtitle,
		'text'       => (string) $text,
		'img'        => (string) $img_url,
		'logo'       => (string) $logo_url,
		'cta'        => array(
			'label' => (string) $cta_label,
			'url'   => (string) $cta_url,
			'nf'    => (bool) $cta_nf,
		),
		'expiresAt'  => (string) $expires_at,
	);
}

/**
 * Render Callback
 */
function hcs_render_block( $attributes = array(), $content = '' ) {
	$slides = array();
	if ( ! empty( $attributes['slides'] ) && is_array( $attributes['slides'] ) ) {
		foreach ( $attributes['slides'] as $sid ) {
			$p = hcs_build_slide_props( (int) $sid );
			if ( $p ) $slides[] = $p;
		}
	}

	// Defaults + attribute overrides
	$defaults = array(
		'mode'           => 'hero',        // hero | image
		'fullWidth'      => false,
		'autoplay'       => true,
		'autoplayDelay'  => 5000,
		'pauseOnHover'   => true,
		'showArrows'     => true,
		'showDots'       => true,
		'loop'           => true,
		'heightMode'     => 'adaptive',    // adaptive | fixed
		'vhHeight'       => '60vh',
		'minHeight'      => '320px',
		'maxHeight'      => '720px',
		'height'         => '60vh',
		'keyboard'       => true,
		'swipe'          => true,
		'showConfetti'   => true,
		'endScreenMessage'=> "🎉 Los geht's!",
	);
	$props = wp_parse_args( is_array( $attributes ) ? $attributes : array(), $defaults );
	$props['slides'] = $slides;

	$json = wp_json_encode( $props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! $json ) $json = '{}';

	$classes = array( 'hcs-slider' );
	if ( ! empty( $props['fullWidth'] ) ) $classes[] = 'hcs--fullvw';
	if ( $props['mode'] === 'image' ) $classes[] = 'hcs--image-mode';

	return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-props="' . esc_attr( $json ) . '"></div>';
}
