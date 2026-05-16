<?php
namespace FlashSite\Design;
if (!defined('ABSPATH')) exit;

final class PreviewDataBuilder {
    public static function hero(array $hero, array $theme): array {
        $hero = DesignEngine::resolveHero($hero, $theme);
        return [
            'hero' => $hero,
            'button_radius' => DesignEngine::buttonRadiusByStyle((string) ($hero['button_style'] ?? 'pill')),
            'desktop_content_width' => max(180, min(320, (int) round(((int) ($hero['content_max_width'] ?? 760)) * 0.34))),
            'mobile_content_width' => max(180, min(260, (int) round(((int) ($hero['content_max_width_mobile'] ?? 100)) * 2.1))),
        ];
    }

    public static function theme(array $theme): array {
        $theme = DesignEngine::normalizeTheme($theme);
        return [
            'theme' => $theme,
            'button_radius' => DesignEngine::buttonRadiusByStyle((string) ($theme['button_radius'] ?? 'pill')),
        ];
    }

    public static function topBar(array $topBar, array $theme): array {
        return [
            'topbar' => DesignEngine::resolveTopBar($topBar, $theme),
        ];
    }
}
