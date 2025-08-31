<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hilfsfunktion: formatiert ein Slide-Objekt für das Frontend-Script.
 */
function hcs_build_slide_props( $slide_id ) {
	$active     = get_post_meta( $slide_id, '_hcs_is_active', true ) === '1';
	$expires_at = trim( (string) get_post_meta( $slide_id, '_hcs_expires_at', true ) );
	if ( ! $active ) return null;
	if ( $expires_at !== '' ) {
		$exp_ts = strtotime( $expires_at );
		if ( $exp_ts && time() > $exp_ts ) return null; // abgelaufen
	}

	$title      = get_post_meta( $slide_id, '_hcs_title', true );
	if ( $title === '' ) $title = get_the_title( $slide_id );
	$subtitle   = (string) get_post_meta( $slide_id, '_hcs_subtitle', true );
	$logo_id    = intval( get_post_meta( $slide_id, '_hcs_logo_id', true ) );
	$show_logo  = get_post_meta( $slide_id, '_hcs_show_logo', true ) === '1';
	$countdown  = (string) get_post_meta( $slide_id, '_hcs_countdown', true );
	$cta_label  = (string) get_post_meta( $slide_id, '_hcs_cta_label', true );
	$cta_url    = (string) get_post_meta( $slide_id, '_hcs_cta_url', true );
	$cta_nf     = get_post_meta( $slide_id, '_hcs_cta_nf', true ) === '1';

	$image_url  = get_the_post_thumbnail_url( $slide_id, 'full' );
	$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';

	return array(
		'id'           => (int) $slide_id,
		'imageUrl'     => $image_url ?: '',
		'logoUrl'      => $logo_url ?: '',
		'showLogo'     => (bool) $show_logo,
		'title'        => $title ?: '',
		'subtitle'     => $subtitle ?: '',
		'countdownTo'  => $countdown ?: '',
		'ctaLabel'     => $cta_label ?: '',
		'ctaUrl'       => $cta_url ?: '',
		'ctaNofollow'  => (bool) $cta_nf,
		'isActive'     => true,
		'expiresAt'    => $expires_at ?: '',
	);
}

/**
 * Server-Side-Render: gibt nur ein Container-Element aus.
 * Das eigentliche Markup baut view.js, basierend auf data-props.
 */
function hcs_render_block( $attributes ) {
	$ids = isset( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? array_map( 'intval', $attributes['slides'] ) : array();
	if ( empty( $ids ) ) {
		return '<div class="hcs-slider is-empty" aria-hidden="true"></div>';
	}

	// Ausgewählte Slides in Menü-Reihenfolge laden
	$posts = get_posts( array(
		'post_type'      => 'hcs_slide',
		'posts_per_page' => -1,
		'post__in'       => $ids,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'suppress_filters' => true,
	) );

	$slides = array();
	foreach ( $posts as $p ) {
		if ( ! in_array( (int) $p->ID, $ids, true ) ) continue; // safety
		$props = hcs_build_slide_props( (int) $p->ID );
		if ( $props ) $slides[] = $props;
	}

	if ( empty( $slides ) ) {
		return '<div class="hcs-slider is-empty" aria-hidden="true"></div>';
	}

	$props = array(
		'slides'          => $slides,
		'autoplay'        => true,
		'interval'        => 5000,
		'pauseOnHover'    => true,
		'heightMode'      => 'adaptive', // clamp(min, vh, max)
		'vhHeight'        => '60vh',
		'minHeight'       => '320px',
		'maxHeight'       => '720px',
		'borderRadius'    => '24px',
		'showConfetti'    => true,
		'endScreenMessage'=> "🎉 Los geht's!",
	);

	$json = wp_json_encode( $props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( ! $json ) $json = '{}';

	return '<div class="hcs-slider" data-props="' . esc_attr( $json ) . '"></div>';
}

