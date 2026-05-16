<?php
namespace FlashSite\Design;
if (!defined('ABSPATH')) exit;

final class HeroRenderer {
    public static function render(array $heroes, array $heroSettings, array $theme): string {
        $active = [];
        foreach ($heroes as $hero) {
            if (!is_array($hero)) continue;
            $hero = DesignEngine::resolveHero($hero, $theme);
            if (($hero['status'] ?? 'draft') !== 'active' || empty($hero['image_id'])) continue;
            $active[] = $hero;
        }
        if (empty($active)) return '';

        $uid = 'fsd-hero-' . wp_generate_uuid4();
        ob_start(); ?>
        <div class="fsd-hero" id="<?php echo esc_attr($uid); ?>" data-fsd-hero>
            <div class="fsd-hero__slides">
                <?php foreach ($active as $i => $hero):
                    $desktop = wp_get_attachment_image_url(absint($hero['image_id']), 'full');
                    if (!$desktop) continue;
                    $mobile = !empty($hero['mobile_image_id']) ? wp_get_attachment_image_url(absint($hero['mobile_image_id']), 'full') : '';
                    $slide_style = sprintf('--fsd-radius-tl:%dpx;--fsd-radius-tr:%dpx;--fsd-radius-br:%dpx;--fsd-radius-bl:%dpx;--fsd-bg-position:%s;--fsd-bg-position-mobile:%s;',
                        absint($hero['radius_top_left']),
                        absint($hero['radius_top_right']),
                        absint($hero['radius_bottom_right']),
                        absint($hero['radius_bottom_left']),
                        esc_attr(self::desktopFocus((string)($hero['image_focus'] ?? 'center'))),
                        esc_attr(self::mobileFocus((string)($hero['image_focus_mobile'] ?? 'center')))
                    );
                    $overlay_style = sprintf('--fsd-pad-top:%dpx;--fsd-pad-right:%dpx;--fsd-pad-bottom:%dpx;--fsd-pad-left:%dpx;--fsd-pad-top-mobile:%dpx;--fsd-pad-right-mobile:%dpx;--fsd-pad-bottom-mobile:%dpx;--fsd-pad-left-mobile:%dpx;--fsd-backdrop:%s;--fsd-content-max:%dpx;--fsd-content-max-mobile:%d%%;--fsd-button-color:%s;--fsd-button-bg:%s;--fsd-button-hover-color:%s;--fsd-button-hover-bg:%s;--fsd-button-radius:%s;--fsd-text-color:%s;--fsd-font-family:%s;',
                        absint($hero['padding_top']),
                        absint($hero['padding_right']),
                        absint($hero['padding_bottom']),
                        absint($hero['padding_left']),
                        absint($hero['padding_top_mobile']),
                        absint($hero['padding_right_mobile']),
                        absint($hero['padding_bottom_mobile']),
                        absint($hero['padding_left_mobile']),
                        esc_attr((string) $hero['backdrop_color']),
                        absint($hero['content_max_width']),
                        absint($hero['content_max_width_mobile']),
                        esc_attr((string) $hero['button_text_color']),
                        esc_attr((string) $hero['button_bg_color']),
                        esc_attr((string) $hero['button_hover_text_color']),
                        esc_attr((string) $hero['button_hover_bg_color']),
                        esc_attr(DesignEngine::buttonRadiusByStyle((string) ($hero['button_style'] ?? 'pill'))),
                        esc_attr((string) ($hero['text_color'] ?? '#ffffff')),
                        esc_attr((string) ($hero['font_family'] ?? 'inherit'))
                    );
                    $slide_classes = array_filter([
                        'fsd-hero__slide',
                        sanitize_html_class((string) $hero['transition']),
                        $i === 0 ? 'is-active' : '',
                        'is-layout-' . sanitize_html_class((string) ($hero['layout_model'] ?? 'background')),
                        'is-image-' . sanitize_html_class((string) ($hero['layout_side'] ?? 'right')),
                        'is-readability-' . sanitize_html_class((string) ($hero['readability'] ?? 'soft')),
                    ]);
                    ?>
                    <article class="<?php echo esc_attr(implode(' ', $slide_classes)); ?>" style="<?php echo esc_attr($slide_style); ?>" data-index="<?php echo esc_attr((string) $i); ?>" data-autoplay="<?php echo !empty($heroSettings['autoplay']) ? '1' : '0'; ?>" data-delay="<?php echo esc_attr((string) absint($hero['delay'])); ?>" data-mobile-image="<?php echo esc_url($mobile ?: $desktop); ?>">
                        <div class="fsd-hero__media">
                            <div class="fsd-hero__image" style="background-image:url('<?php echo esc_url($desktop); ?>');"></div>
                        </div>
                        <div class="fsd-hero__overlay">
                            <div class="fsd-hero__align is-h-<?php echo esc_attr(sanitize_html_class((string) $hero['text_position'])); ?> is-v-<?php echo esc_attr(sanitize_html_class((string) $hero['vertical_position'])); ?> is-hm-<?php echo esc_attr(sanitize_html_class((string) $hero['text_position_mobile'])); ?> is-vm-<?php echo esc_attr(sanitize_html_class((string) $hero['vertical_position_mobile'])); ?>">
                                <div class="fsd-hero__content is-backdrop-<?php echo esc_attr(sanitize_html_class((string) $hero['backdrop_type'])); ?>" style="<?php echo esc_attr($overlay_style); ?>">
                                    <?php if (!empty($hero['title'])): ?><h2 class="fsd-hero__title"><?php echo esc_html((string) $hero['title']); ?></h2><?php endif; ?>
                                    <?php if (!empty($hero['subtitle'])): ?><p class="fsd-hero__subtitle"><?php echo esc_html((string) $hero['subtitle']); ?></p><?php endif; ?>
                                    <?php if (!empty($hero['button_text']) && !empty($hero['button_url'])): ?><a class="fsd-hero__button" href="<?php echo esc_url((string) $hero['button_url']); ?>"><?php echo esc_html((string) $hero['button_text']); ?></a><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (count($active) > 1): ?><div class="fsd-hero__dots"><?php foreach ($active as $i => $_): ?><button type="button" class="fsd-hero__dot <?php echo $i === 0 ? 'is-active' : ''; ?>" data-target="<?php echo esc_attr((string) $i); ?>"><span class="screen-reader-text"><?php echo esc_html(sprintf(__('Ir para slide %d','flashsite-design'), $i + 1)); ?></span></button><?php endforeach; ?></div><?php endif; ?>
        </div>
        <?php return (string) ob_get_clean();
    }

    private static function desktopFocus(string $focus): string {
        if ($focus === 'left') return 'left center';
        if ($focus === 'right') return 'right center';
        return 'center center';
    }

    private static function mobileFocus(string $focus): string {
        if ($focus === 'top') return 'center top';
        if ($focus === 'bottom') return 'center bottom';
        return 'center center';
    }
}
