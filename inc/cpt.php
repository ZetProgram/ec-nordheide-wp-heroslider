<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CPT + Taxonomy für Hero Slides
 */
function hcs_register_cpt_tax() {
	register_post_type( 'hcs_slide', array(
		'labels' => array(
			'name'          => __('Hero Slides', 'hcs'),
			'singular_name' => __('Hero Slide', 'hcs'),
			'add_new_item'  => __('Neue Slide', 'hcs'),
			'edit_item'     => __('Slide bearbeiten', 'hcs'),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true, // wichtig für Gutenberg-Auswahl
		'rest_base'           => 'hcs_slide',
		'menu_icon'           => 'dashicons-images-alt2',
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'has_archive'         => false,
		'publicly_queryable'  => false,
		'rewrite'             => false,
	) );

	register_taxonomy( 'hcs_slider', 'hcs_slide', array(
		'labels' => array(
			'name'          => __('Slider', 'hcs'),
			'singular_name' => __('Slider', 'hcs'),
			'add_new_item'  => __('Neuen Slider anlegen', 'hcs'),
			'edit_item'     => __('Slider bearbeiten', 'hcs'),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'hcs_register_cpt_tax' );
