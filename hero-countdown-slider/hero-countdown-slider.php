<?php
/**
 * Plugin Name: Hero Countdown Slider
 * Version: 1.1.0
 * Update URI: https://github.com/ZetProgram/ec-nordheide-wp-heroslider
 */


if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once HCS_PLUGIN_DIR . 'inc/cpt.php';
require_once HCS_PLUGIN_DIR . 'inc/meta.php';

function hcs_register_assets_and_block() {
	// Assets
	$editor_js = HCS_PLUGIN_DIR . 'build/editor.js';
	$view_js   = HCS_PLUGIN_DIR . 'build/view.js';
	$style_css = HCS_PLUGIN_DIR . 'build/style.css';

	wp_register_script(
		'we-hero-slider-editor',
		HCS_PLUGIN_URL . 'build/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
		file_exists($editor_js)?filemtime($editor_js):null
	);

	// Slider-Taxonomie-Terme an den Editor durchreichen (für Dropdown)
	$terms = get_terms(array(
		'taxonomy' => 'hcs_slider',
		'hide_empty' => false,
	));
	$term_data = array_map(function($t){ return array('id'=>$t->term_id, 'name'=>$t->name); }, is_array($terms)?$terms:[]);
	wp_localize_script('we-hero-slider-editor', 'HCS_TERMS', $term_data);

	wp_register_script(
		'we-hero-slider-view',
		HCS_PLUGIN_URL . 'build/view.js',
		array(),
		file_exists($view_js)?filemtime($view_js):null,
		true
	);
	wp_register_style(
		'we-hero-slider-style',
		HCS_PLUGIN_URL . 'build/style.css',
		array(),
		file_exists($style_css)?filemtime($style_css):null
	);

	// Block dynamisch rendern
	register_block_type( __DIR__ . '/block.json', array(
		'render_callback' => 'hcs_render_block',
	) );
}
add_action('init', 'hcs_register_assets_and_block');

require_once HCS_PLUGIN_DIR . 'render.php';
