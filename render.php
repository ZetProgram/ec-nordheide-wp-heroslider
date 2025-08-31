<?php
function hcs_render_hero_slider($attributes) {
    if (empty($attributes['slides'])) {
        return '<p>' . __('Keine Slides ausgewählt.', 'hcs') . '</p>';
    }

    $html = '<div class="hcs-hero-slider w-full">';

    foreach ($attributes['slides'] as $slide_id) {
        $title = get_the_title($slide_id);
        $content = apply_filters('the_content', get_post_field('post_content', $slide_id));
        $image = get_the_post_thumbnail_url($slide_id, 'full');

        $html .= '<div class="hcs-slide" style="background-image:url(' . esc_url($image) . ')">';
        $html .= '<div class="hcs-slide-inner">';
        $html .= '<h2 class="hcs-slide-title">' . esc_html($title) . '</h2>';
        $html .= '<div class="hcs-slide-content">' . $content . '</div>';
        $html .= '</div>'; // inner
        $html .= '</div>'; // slide
    }

    $html .= '</div>';

    return $html;
}

register_block_type('hcs/hero-slider', [
    'render_callback' => 'hcs_render_hero_slider',
]);
