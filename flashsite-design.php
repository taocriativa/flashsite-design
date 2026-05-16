<?php
/**
 * Plugin Name: FlashSite Design
 * Description: Camada visual do FlashSite para edição controlada de Hero, Top Bar e Tema Visual.
 * Plugin URI: https://www.flashsite.pt
 * Version: 1.1.0
 * Author: FlashSite
 * Text Domain: flashsite-design
 */
if (!defined('ABSPATH')) exit;

define('FLASHSITE_DESIGN_VERSION', '1.1.0');
define('FLASHSITE_DESIGN_FILE', __FILE__);
define('FLASHSITE_DESIGN_PATH', plugin_dir_path(__FILE__));
define('FLASHSITE_DESIGN_URL', plugin_dir_url(__FILE__));

define('FLASHSITE_DESIGN_OPTION_HERO', 'fsd_hero_banners');
define('FLASHSITE_DESIGN_OPTION_TOPBAR', 'fsd_top_bar');
define('FLASHSITE_DESIGN_OPTION_THEME', 'fsd_theme_settings');

require_once FLASHSITE_DESIGN_PATH . 'includes/class-fsd-update-checker.php';
require_once FLASHSITE_DESIGN_PATH . 'includes/class-fsd-design-engine.php';
require_once FLASHSITE_DESIGN_PATH . 'includes/class-fsd-preview-data-builder.php';
require_once FLASHSITE_DESIGN_PATH . 'includes/class-fsd-hero-renderer.php';
require_once FLASHSITE_DESIGN_PATH . 'includes/class-fsd-topbar-renderer.php';
require_once FLASHSITE_DESIGN_PATH . 'includes/class-flashsite-design-plugin.php';

function flashsite_design(){ return \FlashSite\Design\Plugin::instance(); }
function flashsite_design_version(){ return FLASHSITE_DESIGN_VERSION; }

if (is_admin()) {
    (new FSD_UpdateChecker(
        plugin_basename(__FILE__),
        'flashsite-design',
        FLASHSITE_DESIGN_VERSION
    ))->register();
}

add_action('plugins_loaded', function(){ flashsite_design()->boot(); });
