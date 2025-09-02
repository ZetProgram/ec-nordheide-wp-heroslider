<?php
/**
 * Plugin Name: Hero Countdown Slider
 * Description: Hero-Bannerslider mit Countdown/CTA. Slides im Backend pflegen, Block zeigt ausgewählte Slides.
 * Version: 1.3.2
 * Author: Fabian Bross
 * Requires at least: 5.8
 * Tested up to: 6.8.2
 * Requires PHP: 7.4
 * Update URI: https://github.com/ZetProgram/ec-nordheide-wp-heroslider
 * License: GPL-2.0-or-later
 * Text Domain: hcs
 */

if (!defined('ABSPATH')) exit;

define('HCS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HCS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once HCS_PLUGIN_DIR . 'inc/cpt.php';
require_once HCS_PLUGIN_DIR . 'inc/meta.php';
require_once HCS_PLUGIN_DIR . 'render.php';

/**
 * Assets und Block registrieren (Block wird dynamisch gerendert).
 */
function hcs_register_assets_and_block() {
    $editor_js = HCS_PLUGIN_DIR . 'build/editor.js';
    $view_js   = HCS_PLUGIN_DIR . 'build/view.js';
    $style_css = HCS_PLUGIN_DIR . 'build/style.css';

    // Editor-Script (Block-UI im Editor)
    wp_register_script(
        'we-hero-slider-editor',
        HCS_PLUGIN_URL . 'build/editor.js',
        array('wp-blocks','wp-element','wp-i18n','wp-components','wp-block-editor','wp-data'), // <-- wp-data ergänzt
        file_exists($editor_js) ? filemtime($editor_js) : null,
        true
    );

    // Frontend-Script (Slider/Countdown)
    wp_register_script(
        'we-hero-slider-view',
        HCS_PLUGIN_URL . 'build/view.js',
        array(), // kein WP-Global nötig
        file_exists($view_js) ? filemtime($view_js) : null,
        true
    );
    if ( function_exists('wp_script_add_data') ) {
        // Frontend-Script darf deferred sein
        wp_script_add_data( 'we-hero-slider-view', 'strategy', 'defer' );
    }

    // Style (Editor + Frontend)
    wp_register_style(
        'we-hero-slider-style',
        HCS_PLUGIN_URL . 'build/style.css',
        array(),
        file_exists($style_css) ? filemtime($style_css) : null
    );

    // Block anhand block.json registrieren – Script/Style-Handles kommen aus block.json
    register_block_type( __DIR__ . '/block.json', array(
        'render_callback' => 'hcs_render_block', // render.php stellt die Funktion bereit
    ) );
}
add_action('init', 'hcs_register_assets_and_block');

/**
 * Plugin Update Checker (GitHub Releases, privates Repo)
 */
add_action('plugins_loaded', function () {
    $puc = __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($puc)) return;
    require_once $puc;

    $factory = class_exists('\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')
        ? '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory'
        : (class_exists('Puc_v4_Factory') ? 'Puc_v4_Factory' : null);
    if (!$factory) return;

    $uc = $factory::buildUpdateChecker(
        'https://github.com/ZetProgram/ec-nordheide-wp-heroslider/',
        __FILE__,
        'hero-countdown-slider'
    );

    $uc->setBranch('production');
    if ($api = $uc->getVcsApi()) {
        $api->enableReleaseAssets();
    }
});
