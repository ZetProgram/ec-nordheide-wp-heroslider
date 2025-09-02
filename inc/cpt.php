<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Minimaler CPT für Hero-Slides
 * - im Admin sichtbar (show_ui)
 * - in der REST-API sichtbar (show_in_rest) -> nötig für Editor-Auswahl
 * - unterstützt Titel + Beitragsbild
 * - keine Permalinks/Frontend-Queries
 */
function hcs_register_cpt() {
	register_post_type( 'hcs_slide', array(
		'labels' => array(
			'name'          => __( 'Hero Slides', 'hcs' ),
			'singular_name' => __( 'Hero Slide', 'hcs' ),
		),
		'public'             => false,            // kein öffentliches Frontend
		'show_ui'            => true,             // im Admin-Menü sichtbar
		'show_in_menu'       => true,
		'show_in_rest'       => true,             // wichtig für Gutenberg (useSelect / REST)
		'rest_base'          => 'hcs_slide',
		'supports'           => array( 'title', 'thumbnail' ),
		'menu_icon'          => 'dashicons-images-alt2',

		// alles, was wir NICHT brauchen, wird hier deaktiviert:
		'has_archive'        => false,
		'publicly_queryable' => false,
		'rewrite'            => false,
	) );
}
add_action( 'init', 'hcs_register_cpt' );
