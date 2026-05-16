<?php
namespace FlashSite\Design;
if (!defined('ABSPATH')) exit;

final class DesignEngine {
    public static function defaultTheme(): array {
        return [
            'enabled' => false,
            'primary_color' => '#8224e3',
            'secondary_color' => '#5f6672',
            'text_color' => '#ffffff',
            'font_family' => 'inherit',
            'button_radius' => 'pill',
            'button_text_color' => '#ffffff',
            'button_bg_color' => '#8224e3',
            'button_hover_text_color' => '#ffffff',
            'button_hover_bg_color' => '#5b21b6',
            'apply_hero' => true,
            'apply_topbar' => true,
        ];
    }

    public static function defaultTopBar(): array {
        return [
            'enabled' => false,
            'text' => '',
            'link' => '',
            'background_color' => '#111111',
            'text_color' => '#ffffff',
        ];
    }

    public static function defaultHero(): array {
        return [
            'title' => '',
            'subtitle' => '',
            'button_text' => '',
            'button_url' => '',
            'image_id' => 0,
            'mobile_image_id' => 0,
            'status' => 'draft',
            'layout_model' => 'background',
            'layout_side' => 'right',
            'image_focus' => 'center',
            'image_focus_mobile' => 'center',
            'readability' => 'soft',
            'text_position' => 'left',
            'vertical_position' => 'bottom',
            'text_position_mobile' => 'center',
            'vertical_position_mobile' => 'center',
            'radius_top_left' => 16,
            'radius_top_right' => 16,
            'radius_bottom_right' => 16,
            'radius_bottom_left' => 16,
            'padding_top' => 36,
            'padding_right' => 36,
            'padding_bottom' => 36,
            'padding_left' => 36,
            'padding_top_mobile' => 24,
            'padding_right_mobile' => 20,
            'padding_bottom_mobile' => 24,
            'padding_left_mobile' => 20,
            'backdrop_type' => 'none',
            'backdrop_color' => 'rgba(0,0,0,0.45)',
            'transition' => 'fade',
            'delay' => 5000,
            'overlay_opacity' => 0.35,
            'content_max_width' => 760,
            'content_max_width_mobile' => 100,
            'use_global_style' => true,
            'use_global_banner_style' => true,
            'use_global_button_style' => true,
            'button_style' => 'pill',
            'button_text_color' => '#ffffff',
            'button_bg_color' => '#8224e3',
            'button_hover_text_color' => '#ffffff',
            'button_hover_bg_color' => '#5b21b6',
        ];
    }

    public static function normalizeTheme(array $theme): array {
        $theme = array_merge(self::defaultTheme(), [
            'primary_color' => $theme['primary_color'] ?? ($theme['primary'] ?? null),
            'secondary_color' => $theme['secondary_color'] ?? ($theme['secondary'] ?? null),
            'button_radius' => $theme['button_radius'] ?? ($theme['button_style'] ?? null),
        ], $theme);

        return [
            'enabled' => !empty($theme['enabled']),
            'primary_color' => self::hex($theme['primary_color'], '#8224e3'),
            'secondary_color' => self::hex($theme['secondary_color'], '#5f6672'),
            'text_color' => self::hex($theme['text_color'], '#ffffff'),
            'font_family' => sanitize_text_field((string) $theme['font_family']),
            'button_radius' => self::enum($theme['button_radius'], ['pill','rounded','square','sharp'], 'pill'),
            'button_text_color' => self::hex($theme['button_text_color'], '#ffffff'),
            'button_bg_color' => self::hex($theme['button_bg_color'], '#8224e3'),
            'button_hover_text_color' => self::hex($theme['button_hover_text_color'], '#ffffff'),
            'button_hover_bg_color' => self::hex($theme['button_hover_bg_color'], '#5b21b6'),
            'apply_hero' => !empty($theme['apply_hero']),
            'apply_topbar' => !empty($theme['apply_topbar']),
        ];
    }

    public static function normalizeTopBar(array $topBar): array {
        $topBar = array_merge(self::defaultTopBar(), [
            'text' => $topBar['text'] ?? ($topBar['message'] ?? ''),
            'link' => $topBar['link'] ?? ($topBar['link_url'] ?? ''),
            'background_color' => $topBar['background_color'] ?? ($topBar['background'] ?? ''),
            'text_color' => $topBar['text_color'] ?? ($topBar['color'] ?? ''),
        ], $topBar);

        return [
            'enabled' => !empty($topBar['enabled']),
            'text' => sanitize_text_field((string) $topBar['text']),
            'link' => esc_url_raw((string) $topBar['link']),
            'background_color' => self::hex($topBar['background_color'], '#111111'),
            'text_color' => self::hex($topBar['text_color'], '#ffffff'),
        ];
    }

    public static function normalizeHero(array $hero): array {
        $hero = array_merge(self::defaultHero(), [
            'status' => $hero['status'] ?? (!empty($hero['active']) ? 'active' : 'draft'),
            'use_global_style' => array_key_exists('use_global_style', $hero) ? $hero['use_global_style'] : (!empty($hero['use_global_banner_style']) || !empty($hero['use_global_button_style'])),
        ], $hero);

        $hero['status'] = self::enum($hero['status'], ['draft','active','inactive'], 'draft');
        $hero['layout_model'] = self::enum($hero['layout_model'] ?? 'background', ['split','background','centered'], 'background');
        $hero['layout_side'] = self::enum($hero['layout_side'] ?? 'right', ['left','right'], 'right');
        $hero['image_focus'] = self::enum($hero['image_focus'] ?? 'center', ['left','center','right'], 'center');
        $hero['image_focus_mobile'] = self::enum($hero['image_focus_mobile'] ?? 'center', ['top','center','bottom'], 'center');
        $hero['readability'] = self::enum($hero['readability'] ?? 'soft', ['none','soft','strong','light'], 'soft');
        $hero['use_global_style'] = !empty($hero['use_global_style']);
        $hero['use_global_banner_style'] = $hero['use_global_style'];
        $hero['use_global_button_style'] = $hero['use_global_style'];
        $hero['active'] = $hero['status'] === 'active';

        return $hero;
    }

    public static function normalizeHeroes(array $heroes): array {
        $normalized = [];
        $activeCount = 0;
        foreach ($heroes as $hero) {
            if (!is_array($hero)) continue;
            $item = self::normalizeHero($hero);
            if ($item['status'] === 'active') {
                if ($activeCount >= 3 || empty($item['image_id'])) {
                    $item['status'] = 'inactive';
                    $item['active'] = false;
                } else {
                    $activeCount++;
                }
            }
            $normalized[] = $item;
            if (count($normalized) >= 3) break;
        }
        return $normalized;
    }

    public static function resolveHero(array $hero, array $theme): array {
        $hero = self::normalizeHero($hero);
        $theme = self::normalizeTheme($theme);

        $resolved = $hero;
        $resolved['text_color'] = '#ffffff';
        $resolved['font_family'] = 'inherit';

        if ($theme['enabled'] && $theme['apply_hero'] && !empty($hero['use_global_style'])) {
            $resolved['text_color'] = $theme['text_color'];
            $resolved['font_family'] = $theme['font_family'];
            $resolved['button_style'] = $theme['button_radius'];
            $resolved['button_text_color'] = $theme['button_text_color'];
            $resolved['button_bg_color'] = $theme['button_bg_color'];
            $resolved['button_hover_text_color'] = $theme['button_hover_text_color'];
            $resolved['button_hover_bg_color'] = $theme['button_hover_bg_color'];
        }

        return $resolved;
    }

    public static function resolveTopBar(array $topBar, array $theme): array {
        $topBar = self::normalizeTopBar($topBar);
        $theme = self::normalizeTheme($theme);

        if ($theme['enabled'] && $theme['apply_topbar']) {
            if (empty($topBar['background_color'])) {
                $topBar['background_color'] = $theme['primary_color'];
            }
            if (empty($topBar['text_color'])) {
                $topBar['text_color'] = $theme['text_color'];
            }
        }

        return $topBar;
    }

    public static function buttonRadiusByStyle(string $style): string {
        if ($style === 'sharp') return '0px';
        if ($style === 'square') return '8px';
        if ($style === 'rounded') return '16px';
        return '999px';
    }

    private static function enum($value, array $allowed, string $default): string {
        $value = sanitize_text_field((string) $value);
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private static function hex($value, string $fallback): string {
        $value = sanitize_hex_color((string) $value);
        return $value ?: $fallback;
    }
}
