<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function hcs_render_block( $attributes, $content, $block ) {
	$group_id = intval( $attributes['sliderGroup'] ?? 0 );
	if ( ! $group_id ) {
		return '<div class="hcs-slider"><div class="hcs-empty">Kein Slider ausgewählt.</div></div>';
	}

	$q = new WP_Query( array(
		'post_type'      => 'hcs_slide',
		'posts_per_page' => -1,
		'tax_query'      => array(
			array(
				'taxonomy' => 'hcs_slider',
				'field'    => 'term_id',
				'terms'    => $group_id,
			),
		),
		'orderby'       => 'menu_order',
		'order'         => 'ASC',
		'no_found_rows' => true,
	) );

	$slides = array();
	$now = current_time( 'timestamp' );

	while ( $q->have_posts() ) {
		$q->the_post();
		$id = get_the_ID();

		$is_active  = get_post_meta( $id, '_hcs_is_active', true ) === '1';
		$expires    = get_post_meta( $id, '_hcs_expires_at', true );
		$expires_ts = $expires ? strtotime( $expires ) : null;

		if ( ! $is_active ) continue;
		if ( $expires_ts && $expires_ts <= $now ) continue;

		$img = get_the_post_thumbnail_url( $id, 'full' );

		$slides[] = array(
			'imageUrl'    => $img ?: '',
			'title'       => get_post_meta( $id, '_hcs_title', true ),
			'subtitle'    => get_post_meta( $id, '_hcs_subtitle', true ),
			'logoUrl'     => ( $lid = get_post_meta( $id, '_hcs_logo_id', true ) ) ? wp_get_attachment_image_url( (int) $lid, 'full' ) : '',
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

	$props = array(
		'slides'       => $slides,
		'autoplay'     => ! empty( $attributes['autoplay'] ),
		'autoplayDelay'=> intval( $attributes['autoplayDelay'] ?? 5000 ),
		'showDots'     => ! empty( $attributes['showDots'] ),
		'showArrows'   => ! empty( $attributes['showArrows'] ),
		'height'       => $attributes['height'] ?? '60vh',
		'borderRadius' => $attributes['borderRadius'] ?? '24px',
	);

	$html = '<div class="hcs-slider" data-props=\'' . esc_attr( wp_json_encode( $props ) ) . '\'></div>';

	wp_enqueue_script( 'we-hero-slider-view' );
	wp_enqueue_style( 'we-hero-slider-style' );

	return $html;
}
