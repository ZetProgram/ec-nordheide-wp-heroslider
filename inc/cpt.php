<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function hcs_register_cpt_tax() {
	register_post_type( 'hcs_slide', array(
		'labels' => array(
			'name'          => 'Hero Slides',
			'singular_name' => 'Hero Slide',
			'add_new_item'  => 'Neue Slide',
			'edit_item'     => 'Slide bearbeiten',
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-images-alt2',
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		'show_in_rest' => true,
		'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes'],
	) );

	register_taxonomy( 'hcs_slider', 'hcs_slide', array(
		'labels' => array(
			'name'          => 'Slider',
			'singular_name' => 'Slider',
			'add_new_item'  => 'Neuen Slider anlegen',
			'edit_item'     => 'Slider bearbeiten',
		),
		'public'            => false,
		'show_ui'           => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'hcs_register_cpt_tax' );
