// --- GitHub Update-Checker (privates Repo) ---
add_action('plugins_loaded', function () {
    // PUC laden (defensiv)
    $puc_path = __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($puc_path)) {
        // Hinweis statt Fatal Error
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p><strong>Hero Countdown Slider:</strong> Update-Checker nicht gefunden. Erwarte <code>vendor/plugin-update-checker/plugin-update-checker.php</code>.</p></div>';
        });
        return;
    }
    require_once $puc_path;

    // Update-Checker konfigurieren
    $hcs_update_checker = Puc_v4_Factory::buildUpdateChecker(
        'https://github.com/ZetProgram/ec-nordheide-wp-heroslider/', // Repo-URL
        __FILE__,                                                    // Hauptdatei
        'hero-countdown-slider'                                      // Slug (Ordnername)
    );

    // Du releast über Branch "production"
    $hcs_update_checker->setBranch('production');

    // Releases mit ZIP-Asset verwenden
    if ($api = $hcs_update_checker->getVcsApi()) {
        $api->enableReleaseAssets();
    }

    // PRIVATES REPO → Token setzen (eine von beiden Varianten nutzen)

    // A) Hart codiert (nur Read auf dieses Repo vergeben!)
    $token = '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'; // <--- ersetzen
    if ($token && $token !== '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') {
        $hcs_update_checker->setAuthentication($token);
        return;
    }

    // B) Alternativ aus wp-config.php
    if (defined('HCS_GH_TOKEN') && HCS_GH_TOKEN) {
        $hcs_update_checker->setAuthentication(HCS_GH_TOKEN);
        return;
    }
});
