<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function hcs_render_block( $attributes, $content = '', $block = null ) {
  $a = wp_parse_args( $attributes, [
    'slides' => [],
    'mode' => 'hero',
    'fullWidth' => false,
    'heightMode' => 'adaptive',
    'minHeight' => '320px',
    'vhHeight' => '60vh',
    'maxHeight' => '720px',
    'height' => '',
    'showArrows' => true,
    'showDots' => true,
    'loop' => true,
    'keyboard' => true,
    'swipe' => true,
    'autoplay' => false,
    'autoplayDelay' => 5000,
    'pauseOnHover' => true,
    'borderRadius' => '24px',
    'endScreenMessage' => "🎉 Los geht's!",
    'showConfetti' => false,
  ]);

  $ids = array_map('intval', $a['slides'] ?: []);
  $slides = [];

  if ( $ids ) {
    $posts = get_posts([
      'post_type'   => 'hcs_slide',
      'post__in'    => $ids,
      'orderby'     => 'post__in',
      'numberposts' => -1,
    ]);

    $now = current_time( 'timestamp', true ); // UTC

    foreach ( $posts as $p ) {
      // Metadaten wie in deiner meta.php
      $title      = get_post_meta( $p->ID, '_hcs_title', true );
      $subtitle   = get_post_meta( $p->ID, '_hcs_subtitle', true );
      $logo_id    = (int) get_post_meta( $p->ID, '_hcs_logo_id', true );
      $show_logo  = get_post_meta( $p->ID, '_hcs_show_logo', true ) === '1';
      $countdown  = get_post_meta( $p->ID, '_hcs_countdown', true );
      $cta_label  = get_post_meta( $p->ID, '_hcs_cta_label', true );
      $cta_url    = get_post_meta( $p->ID, '_hcs_cta_url', true );
      $cta_nf     = get_post_meta( $p->ID, '_hcs_cta_nf', true ) === '1';
      $expires_at = get_post_meta( $p->ID, '_hcs_expires_at', true );
      $active_meta= get_post_meta( $p->ID, '_hcs_is_active', true );

      // Standard: Wenn nicht gesetzt, als AKTIV werten
      $is_active  = ($active_meta === '') ? true : ($active_meta === '1');

      // Ablaufdatum kann deaktivieren
      if ( $expires_at ) {
        $exp = strtotime( $expires_at );
        if ( $exp && $exp <= $now ) { $is_active = false; }
      }

      // Medien-URLs
      $image_id  = get_post_thumbnail_id( $p->ID );
      $imageUrl  = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
      $logoUrl   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

      $slides[] = [
        'id'           => $p->ID,
        'title'        => ($title !== '') ? $title : html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
        'subtitle'     => (string) $subtitle,
        'imageUrl'     => (string) $imageUrl,              // <— WICHTIG: imageUrl
        'logoUrl'      => (string) $logoUrl,               // <— WICHTIG: logoUrl
        'showLogo'     => (bool) $show_logo,
        'countdownTo'  => (string) $countdown,
        'ctaLabel'     => (string) $cta_label,
        'ctaUrl'       => (string) $cta_url,
        'ctaNofollow'  => (bool) $cta_nf,
        'isActive'     => (bool) $is_active,
      ];
    }
  }

  $props = [
    'slides'           => $slides,
    'mode'             => (string) $a['mode'],
    'fullWidth'        => (bool) $a['fullWidth'],
    'heightMode'       => (string) $a['heightMode'],
    'minHeight'        => (string) $a['minHeight'],
    'vhHeight'         => (string) $a['vhHeight'],
    'maxHeight'        => (string) $a['maxHeight'],
    'height'           => (string) $a['height'],
    'showArrows'       => (bool) $a['showArrows'],
    'showDots'         => (bool) $a['showDots'],
    'loop'             => (bool) $a['loop'],
    'keyboard'         => (bool) $a['keyboard'],
    'swipe'            => (bool) $a['swipe'],
    'autoplay'         => (bool) $a['autoplay'],
    'autoplayDelay'    => (int) $a['autoplayDelay'],
    'pauseOnHover'     => (bool) $a['pauseOnHover'],
    'borderRadius'     => (string) $a['borderRadius'],
    'endScreenMessage' => (string) $a['endScreenMessage'],
    'showConfetti'     => (bool) $a['showConfetti'],
  ];

  $json = wp_json_encode( $props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

  $classes = 'hcs-slider';
  if ( ! empty( $a['fullWidth'] ) ) $classes .= ' hcs--fullvw';

  return sprintf(
    '<div class="%s" data-props="%s"></div>',
    esc_attr( $classes ),
    esc_attr( $json )
  );
}
