// Nach den defines:
require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

$hcs_update_checker = Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/ZetProgram/ec-nordheide-wp-heroslider/', // Repo-URL
  __FILE__,                                     // Hauptdatei
  'hero-countdown-slider'                       // Slug (Ordnername)
);

// Wenn dein "stable" Branch main ist:
$hcs_update_checker->setBranch('main');

// Falls du ZIPs an GitHub-Releases anhängst:
$api = $hcs_update_checker->getVcsApi();
if ($api) { $api->enableReleaseAssets(); }

// Private Repos (optional):
// $hcs_update_checker->setAuthentication('ghp_XXXXXXXXX'); // Oder ENV/const nutzen
