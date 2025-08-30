<?php
/**
 * Plugin Name: Hero Countdown Slider
 * Description: Hero-Bannerslider mit Countdown/CTA. Slides im Backend pflegen, Block zeigt eine gewählte Slider-Gruppe.
 * Version: 1.1.0
 * Author: Fabian Bross
 * Requires at least: 5.8
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Update URI: https://github.com/ZetProgram/ec-nordheide-wp-heroslider
 * License: GPL-2.0-or-later
 * Text Domain: hcs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once HCS_PLUGIN_DIR . 'inc/cpt.php';
require_once HCS_PLUGIN_DIR . 'inc/meta.php';

/**
 * Assets und Block registrieren (Block wird dynamisch gerendert).
 */
function hcs_register_assets_and_block() {
	$editor_js = HCS_PLUGIN_DIR . 'build/editor.js';
	$view_js   = HCS_PLUGIN_DIR . 'build/view.js';
	$style_css = HCS_PLUGIN_DIR . 'build/style.css';

	// Editor-Script (nur Auswahl der Gruppe + Optionen)
	wp_register_script(
		'we-hero-slider-editor',
		HCS_PLUGIN_URL . 'build/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		file_exists( $editor_js ) ? filemtime( $editor_js ) : null
	);

	// Slider-Taxonomie-Terme an Editor durchreichen (Dropdown)
	$terms = get_terms(array(
		'taxonomy'   => 'hcs_slider',
		'hide_empty' => false,
	));
	$term_data = array_map(
		function( $t ){ return array( 'id' => $t->term_id, 'name' => $t->name ); },
		is_array( $terms ) ? $terms : array()
	);
	wp_localize_script( 'we-hero-slider-editor', 'HCS_TERMS', $term_data );

	// Frontend-Script (Slider/Countdown)
	wp_register_script(
		'we-hero-slider-view',
		HCS_PLUGIN_URL . 'build/view.js',
		array(),
		file_exists( $view_js ) ? filemtime( $view_js ) : null,
		true
	);

	// Style (Editor + Frontend)
	wp_register_style(
		'we-hero-slider-style',
		HCS_PLUGIN_URL . 'build/style.css',
		array(),
		file_exists( $style_css ) ? filemtime( $style_css ) : null
	);

	// Block (dynamisches Rendern via render.php)
	register_block_type( __DIR__ . '/block.json', array(
		'render_callback' => 'hcs_render_block',
	) );
}
add_action( 'init', 'hcs_register_assets_and_block' );

require_once HCS_PLUGIN_DIR . 'render.php';

/**
 * Plugin Update Checker (GitHub Releases)
 * - Repo: ZetProgram/ec-nordheide-wp-heroslider
 * - Branch: production
 * - nutzt Release Assets (ZIP vom Release)
 */
function hcs_setup_updates() {
	$vendor = HCS_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';
	if ( file_exists( $vendor ) ) {
		require_once $vendor;

		$update_checker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/ZetProgram/ec-nordheide-wp-heroslider/',
			__FILE__,
			'hero-countdown-slider' // Slug = Ordnername
		);

		// Wenn du Releases mit Assets verwendest (empfohlen):
		$api = $update_checker->getVcsApi();
		if ( $api ) {
			$api->enableReleaseAssets();
		}

		// Fallback (nur falls du mal ohne Releases arbeiten würdest):
		$update_checker->setBranch( 'production' );

		// Private Repos (optional):
		// $update_checker->setAuthentication( 'ghp_XXXX' );
	}
}
add_action( 'plugins_loaded', 'hcs_setup_updates' );
