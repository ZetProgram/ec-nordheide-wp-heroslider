<?php
/**
 * Plugin Name: Hero Countdown Slider
 * Description: Hero-Bannerslider mit Countdown/CTA. Slides im Backend pflegen, Block zeigt eine gewählte Slider-Gruppe.
 * Version: 1.1.4
 * Author: Fabian Bross
 * Requires at least: 5.8
 * Tested up to: 6.6
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
		array('wp-blocks','wp-element','wp-i18n','wp-components','wp-block-editor'),
		file_exists($editor_js) ? filemtime($editor_js) : null
	);

	// Slider-Taxonomie-Terme an Editor durchreichen (Dropdown)
	$terms = get_terms(array(
		'taxonomy'   => 'hcs_slider',
		'hide_empty' => false,
	));
	$term_data = array_map(
		function($t){ return array('id' => $t->term_id, 'name' => $t->name); },
		is_array($terms) ? $terms : array()
	);
	wp_localize_script('we-hero-slider-editor', 'HCS_TERMS', $term_data);

	// Frontend-Script (Slider/Countdown)
	wp_register_script(
		'we-hero-slider-view',
		HCS_PLUGIN_URL . 'build/view.js',
		array(),
		file_exists($view_js) ? filemtime($view_js) : null,
		true
	);

	// Style (Editor + Frontend)
	wp_register_style(
		'we-hero-slider-style',
		HCS_PLUGIN_URL . 'build/style.css',
		array(),
		file_exists($style_css) ? filemtime($style_css) : null
	);

	// Block (dynamisches Rendern via render.php)
	register_block_type(__DIR__ . '/block.json', array(
		'render_callback' => 'hcs_render_block',
	));
}
add_action('init', 'hcs_register_assets_and_block');

require_once HCS_PLUGIN_DIR . 'render.php';

/**
 * Plugin Update Checker (GitHub Releases, privates Repo)
 * - Repo: ZetProgram/ec-nordheide-wp-heroslider
 * - Branch: production
 * - nutzt Release Assets (ZIP vom Release)
 */
add_action('plugins_loaded', function () {
    // 1) Library laden
    $puc = __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($puc)) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p><strong>Hero Countdown Slider:</strong> Update-Checker nicht gefunden. Erwarte <code>vendor/plugin-update-checker/plugin-update-checker.php</code>.</p></div>';
        });
        return;
    }
    require_once $puc;

    // 2) Factory (v5 bevorzugt, Fallback v4)
    $factory = null;
    if (class_exists('\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
        $factory = '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory';
    } elseif (class_exists('Puc_v5_Factory')) {
        $factory = 'Puc_v5_Factory';
    } elseif (class_exists('Puc_v4_Factory')) {
        $factory = 'Puc_v4_Factory';
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p><strong>Hero Countdown Slider:</strong> Keine PUC-Factory gefunden (v5/v4). Prüfe deinen <code>vendor/</code>-Ordner.</p></div>';
        });
        return;
    }

    // 3) Update-Checker instanziieren
    $updateChecker = $factory::buildUpdateChecker(
        'https://github.com/ZetProgram/ec-nordheide-wp-heroslider/', // privates Repo
        __FILE__,                                                     // lokale Hauptdatei
        'hero-countdown-slider'                                       // Plugin-Slug (Ordnername)
    );

    // 4) Branch + Releases (ZIP)
    $updateChecker->setBranch('production');
    if (method_exists($updateChecker, 'getVcsApi')) {
        $api = $updateChecker->getVcsApi();

        // *** WICHTIG: Pfad zur Plugin-Hauptdatei IM REPO setzen (Unterordner!) ***
        // Diese Methode ist in neueren PUC-Versionen vorhanden. Wir rufen sie nur auf, wenn es sie gibt.
        if ($api && method_exists($api, 'setFilePath')) {
            $api->setFilePath('hero-countdown-slider/hero-countdown-slider.php');
        }
        // Fallbacks für ältere Varianten (werden einfach übersprungen, falls nicht vorhanden):
        if ($api && method_exists($api, 'setPath')) {
            $api->setPath('hero-countdown-slider');
        }

        // ZIP-Assets aus GitHub-Releases verwenden
        if ($api && method_exists($api, 'enableReleaseAssets')) {
            $api->enableReleaseAssets();
        }
    }

    // 5) PRIVATES Repo → Token setzen (EINE Variante genügt)
    // A) Hart codiert (nur "Contents: Read" für dieses Repo!)
    $token = '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';
    if (!empty($token) && $token !== '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') {
        if (method_exists($updateChecker, 'setAuthentication')) {
            $updateChecker->setAuthentication($token);
        }
        return;
    }
    // B) Alternativ aus wp-config.php
    if (defined('HCS_GH_TOKEN') && HCS_GH_TOKEN) {
        if (method_exists($updateChecker, 'setAuthentication')) {
            $updateChecker->setAuthentication(HCS_GH_TOKEN);
        }
        return;
    }

    // Info, falls gar kein Token gesetzt ist (kein Fatal)
    add_action('admin_notices', function () {
        echo '<div class="notice notice-info"><p><strong>Hero Countdown Slider:</strong> Kein GitHub-Token gesetzt. Updates aus privatem Repo sind ohne Token nicht möglich.</p></div>';
    });
});