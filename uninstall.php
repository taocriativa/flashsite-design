<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }

/**
 * Política conservadora de uninstall.
 *
 * Por padrão, o FlashSite Design NÃO apaga os dados visuais ao excluir o plugin.
 * Isso evita perda acidental durante trocas manuais de ZIP, rollback ou reinstalação.
 *
 * Para purga deliberada, definir antes do uninstall:
 * define('FLASHSITE_DESIGN_PURGE_ON_UNINSTALL', true);
 *
 * ou guardar a opção:
 * flashsite_design_allow_data_deletion = true
 */
$allowPurge = defined('FLASHSITE_DESIGN_PURGE_ON_UNINSTALL') && FLASHSITE_DESIGN_PURGE_ON_UNINSTALL === true;
$allowPurge = $allowPurge || get_option('flashsite_design_allow_data_deletion', false) === true;

if (!$allowPurge) {
    return;
}

$options = [
    'fsd_hero_banners',
    'fsd_top_bar',
    'fsd_theme_settings',
    'flashsite_design_hero_banners',
    'flashsite_design_top_bar',
    'flashsite_design_theme_mode',
    'flashsite_design_version',
    'flashsite_design_hero_settings',
    'flashsite_design_allow_data_deletion',
];

foreach ($options as $option) {
    delete_option($option);
    delete_site_option($option);
}
