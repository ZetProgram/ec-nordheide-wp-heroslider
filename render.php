<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function hcs_render_block( $attributes, $content, $block ) {
	$ids = array_map( 'intval', $attributes['slideIds'] ?? [] );
	if ( empty( $ids ) ) {
	return '<div class="hcs-slider"><div class="hcs-empty">Keine Slides ausgewählt.</div></div>';
	}

	$q = new WP_Query( array(
		'post_type'      => 'hcs_slide',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	) );
	$now = time();
	$slides = array();
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			$id = get_the_ID();

			$is_active  = get_post_meta( $id, '_hcs_is_active', true ) === '1';
			$expires    = get_post_meta( $id, '_hcs_expires_at', true );
			$expires_ts = $expires ? strtotime( $expires ) : null;

			if ( ! $is_active ) continue;
			if ( $expires_ts && $expires_ts <= $now ) continue;

			$img = get_the_post_thumbnail_url( $id, 'full' );
			$lid = get_post_meta( $id, '_hcs_logo_id', true );
			$slides[] = array(
				'imageUrl'    => $img ?: '',
				'title'       => get_post_meta( $id, '_hcs_title', true ),
				'subtitle'    => get_post_meta( $id, '_hcs_subtitle', true ),
				'logoUrl'     => $lid ? wp_get_attachment_image_url( (int) $lid, 'full' ) : '',
				'showLogo'    => get_post_meta( $id, '_hcs_show_logo', true ) === '1',
				'countdownTo' => get_post_meta( $id, '_hcs_countdown', true ),
				'ctaLabel'    => get_post_meta( $id, '_hcs_cta_label', true ),
				'ctaUrl'      => get_post_meta( $id, '_hcs_cta_url', true ),
				'ctaNofollow' => get_post_meta( $id, '_hcs_cta_nf', true ) === '1',
				'expiresAt'   => $expires ?: '',
				'isActive'    => true,
			);
		}
		wp_reset_postdata();
	}

	$props = array(
		'slides'         => $slides,
		'autoplay'       => ! empty( $attributes['autoplay'] ),
		'autoplayDelay'  => intval( $attributes['autoplayDelay'] ?? 5000 ),
		'pauseOnHover'   => ! empty( $attributes['pauseOnHover'] ),
		'showDots'       => ! empty( $attributes['showDots'] ),
		'showArrows'     => ! empty( $attributes['showArrows'] ),
		'heightMode'     => $attributes['heightMode'] ?? 'adaptive',
		'minHeight'      => $attributes['minHeight'] ?? '320px',
		'vhHeight'       => $attributes['vhHeight'] ?? '60vh',
		'maxHeight'      => $attributes['maxHeight'] ?? '680px',
		'height'         => $attributes['height'] ?? '60vh',
		'borderRadius'   => $attributes['borderRadius'] ?? '24px',
		'showConfetti'   => ! empty( $attributes['showConfetti'] ),
		'endScreenMessage' => $attributes['endScreenMessage'] ?? '🎉 Los geht\'s!',
	);
	$classes = 'hcs-slider';
	if ( !empty($attributes['heroMode']) ) {
		$classes .= ' hcs-hero';
	}

	$html = '<div class="' . esc_attr($classes) . '" data-props=\'' . esc_attr( wp_json_encode( $props ) ) . '\'></div>';

	wp_enqueue_script( 'we-hero-slider-view' );
	wp_enqueue_style( 'we-hero-slider-style' );

	return $html;
}
