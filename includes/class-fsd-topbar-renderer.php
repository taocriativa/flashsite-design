<?php
namespace FlashSite\Design;
if (!defined('ABSPATH')) exit;

final class TopBarRenderer {
    public static function render(array $topBar, array $theme): string {
        $topBar = DesignEngine::resolveTopBar($topBar, $theme);
        if (empty($topBar['enabled']) || $topBar['text'] === '') {
            return '';
        }
        $style = sprintf('background:%s;color:%s;', esc_attr($topBar['background_color']), esc_attr($topBar['text_color']));
        ob_start();
        echo '<div class="fsd-topbar" style="' . $style . '"><div class="fsd-topbar__inner"><span class="fsd-topbar__message">' . esc_html((string) $topBar['text']) . '</span>';
        if (!empty($topBar['link'])) {
            echo ' <a class="fsd-topbar__link" href="' . esc_url((string) $topBar['link']) . '">' . esc_html__('Saber mais', 'flashsite-design') . '</a>';
        }
        echo '</div></div>';
        return (string) ob_get_clean();
    }
}
