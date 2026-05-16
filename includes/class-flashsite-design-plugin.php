<?php
namespace FlashSite\Design;
if (!defined('ABSPATH')) exit;

final class Plugin {
    private static ?Plugin $instance = null;
    public static function instance(): Plugin {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function boot(): void {
        add_action('admin_notices', [$this,'maybeRenderCoreNotice']);
        if (!$this->isCoreCompatible()) return;

        $this->maybeRunMigrations();
        add_action('admin_menu', [$this,'registerAdminMenu']);
        add_action('admin_init', [$this,'handleAdminPost']);
        add_action('admin_enqueue_scripts', [$this,'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [$this,'registerFrontendAssets']);
        add_action('wp_head', [$this,'renderThemeCssVariables']);
        add_action('wp_body_open', [$this,'renderTopBar']);
        add_shortcode('flashsite_hero_banners', [$this,'renderHeroShortcode']);
        add_shortcode('flashsite_top_bar', [$this,'renderTopBarShortcode']);
        add_action('elementor/editor/before_enqueue_scripts', [$this,'enqueueElementorAssets']);
        add_action('elementor/editor/before_enqueue_styles', [$this,'enqueueElementorAssets']);
        add_action('elementor/preview/enqueue_scripts', [$this,'enqueueElementorAssets']);
        add_action('elementor/preview/enqueue_styles', [$this,'enqueueElementorAssets']);
    }

    public function isCoreCompatible(): bool {
        return function_exists('flashsite_core_version') && version_compare((string) flashsite_core_version(), '2.0.1', '>=');
    }

    public function maybeRenderCoreNotice(): void {
        if ($this->isCoreCompatible()) return;
        echo '<div class="notice notice-error"><p>' . esc_html__('FlashSite Design requer FlashSite Core 2.0.1 ou superior ativo.', 'flashsite-design') . '</p></div>';
    }

    private function getHeroes(): array {
        $heroes = get_option(FLASHSITE_DESIGN_OPTION_HERO, null);
        if ($heroes === null || $heroes === false) {
            $heroes = get_option('flashsite_design_hero_banners', []);
        }
        $normalized = DesignEngine::normalizeHeroes(is_array($heroes) ? $heroes : []);
        foreach ($normalized as &$hero) {
            $hero['active'] = ($hero['status'] ?? 'draft') === 'active';
        }
        unset($hero);
        return $normalized;
    }

    private function getTopBarSettings(): array {
        $topBar = get_option(FLASHSITE_DESIGN_OPTION_TOPBAR, null);
        if ($topBar === null || $topBar === false) {
            $topBar = get_option('flashsite_design_top_bar', []);
        }
        $normalized = DesignEngine::normalizeTopBar(is_array($topBar) ? $topBar : []);
        $normalized['message'] = $normalized['text'];
        $normalized['background'] = $normalized['background_color'];
        $normalized['color'] = $normalized['text_color'];
        $normalized['link_text'] = !empty($normalized['link']) ? __('Saber mais', 'flashsite-design') : '';
        $normalized['link_url'] = $normalized['link'];
        return $normalized;
    }

    private function getThemeSettings(): array {
        $theme = get_option(FLASHSITE_DESIGN_OPTION_THEME, null);
        if ($theme === null || $theme === false) {
            $theme = get_option('flashsite_design_theme_mode', []);
        }
        return DesignEngine::normalizeTheme(is_array($theme) ? $theme : []);
    }

    private function defaultsHeroItem(): array {
        return DesignEngine::defaultHero();
    }

    private function maybeRunMigrations(): void {
        if (get_option('flashsite_design_version') === FLASHSITE_DESIGN_VERSION) return;

        $legacyHeroes = get_option('flashsite_design_hero_banners', []);
        $heroes = get_option(FLASHSITE_DESIGN_OPTION_HERO, []);
        if (empty($heroes) && !empty($legacyHeroes)) {
            $heroes = $legacyHeroes;
        }
        update_option(FLASHSITE_DESIGN_OPTION_HERO, DesignEngine::normalizeHeroes(is_array($heroes) ? $heroes : []), false);

        $legacyTheme = get_option('flashsite_design_theme_mode', []);
        $theme = get_option(FLASHSITE_DESIGN_OPTION_THEME, []);
        if (empty($theme) && !empty($legacyTheme)) {
            $theme = $legacyTheme;
        }
        update_option(FLASHSITE_DESIGN_OPTION_THEME, DesignEngine::normalizeTheme(is_array($theme) ? $theme : []), false);

        $legacyTopBar = get_option('flashsite_design_top_bar', []);
        $topBar = get_option(FLASHSITE_DESIGN_OPTION_TOPBAR, []);
        if (empty($topBar) && !empty($legacyTopBar)) {
            $topBar = $legacyTopBar;
        }
        update_option(FLASHSITE_DESIGN_OPTION_TOPBAR, DesignEngine::normalizeTopBar(is_array($topBar) ? $topBar : []), false);

        $heroSettings = (array) get_option('flashsite_design_hero_settings', []);
        $heroSettings = array_merge(['autoplay' => true], $heroSettings);
        update_option('flashsite_design_hero_settings', $heroSettings, false);
        update_option('flashsite_design_version', FLASHSITE_DESIGN_VERSION, false);
    }

    public function registerAdminMenu(): void {
        add_menu_page(__('FlashSite Design','flashsite-design'), __('FlashSite Design','flashsite-design'), 'manage_options', 'flashsite-design', [$this,'renderHeroAdminPage'], 'dashicons-art', 58);
        add_submenu_page('flashsite-design', __('Banners do Site','flashsite-design'), __('Banners do Site','flashsite-design'), 'manage_options', 'flashsite-design', [$this,'renderHeroAdminPage']);
        add_submenu_page('flashsite-design', __('Barra de Aviso','flashsite-design'), __('Barra de Aviso','flashsite-design'), 'manage_options', 'flashsite-design-top-bar', [$this,'renderTopBarAdminPage']);
        add_submenu_page('flashsite-design', __('Estilo Visual','flashsite-design'), __('Estilo Visual','flashsite-design'), 'manage_options', 'flashsite-design-theme-mode', [$this,'renderThemeModeAdminPage']);
    }

    public function enqueueAdminAssets(string $hook = ''): void {
        $allowed = [
            'toplevel_page_flashsite-design',
            'flashsite-design_page_flashsite-design-top-bar',
            'flashsite-design_page_flashsite-design-theme-mode',
        ];
        if (!in_array($hook, $allowed, true)) return;
        wp_enqueue_media();
        wp_enqueue_style('flashsite-design-admin', FLASHSITE_DESIGN_URL . 'assets/admin/css/fsd-admin.css', [], FLASHSITE_DESIGN_VERSION);
        wp_enqueue_script('flashsite-design-admin', FLASHSITE_DESIGN_URL . 'assets/admin/js/fsd-admin.js', ['jquery'], FLASHSITE_DESIGN_VERSION, true);
        wp_enqueue_script('flashsite-design-preview-engine', FLASHSITE_DESIGN_URL . 'assets/admin/js/fsd-preview-engine.js', ['jquery','flashsite-design-admin'], FLASHSITE_DESIGN_VERSION, true);
        wp_localize_script('flashsite-design-preview-engine', 'fsdPreviewConfig', ['version' => FLASHSITE_DESIGN_VERSION]);
    }

    public function registerFrontendAssets(): void {
        wp_register_style('flashsite-design-hero', FLASHSITE_DESIGN_URL . 'assets/frontend/css/fsd-hero.css', [], FLASHSITE_DESIGN_VERSION);
        wp_register_script('flashsite-design-hero', FLASHSITE_DESIGN_URL . 'assets/frontend/js/fsd-hero.js', [], FLASHSITE_DESIGN_VERSION, true);
        wp_register_style('flashsite-design-topbar', FLASHSITE_DESIGN_URL . 'assets/frontend/css/fsd-topbar.css', [], FLASHSITE_DESIGN_VERSION);
        wp_register_style('flashsite-design-theme', FLASHSITE_DESIGN_URL . 'assets/frontend/css/fsd-theme.css', [], FLASHSITE_DESIGN_VERSION);
    }

    public function enqueueElementorAssets(): void {
        $this->registerFrontendAssets();
        wp_enqueue_style('flashsite-design-hero');
        wp_enqueue_script('flashsite-design-hero');
        wp_enqueue_style('flashsite-design-topbar');
        wp_enqueue_style('flashsite-design-theme');
    }

    private function enum($value, array $allowed, string $default): string {
        $value = sanitize_text_field((string) $value);
        return in_array($value, $allowed, true) ? $value : $default;
    }
    private function intBetween($value, int $min, int $max): int {
        $value = absint($value);
        return max($min, min($max, $value));
    }
    private function floatBetween($value, float $min, float $max): float {
        $value = (float) $value;
        return max($min, min($max, $value));
    }

    public function handleAdminPost(): void {
        if (!is_admin() || !current_user_can('manage_options')) return;
        if (isset($_POST['flashsite_design_save_hero'])) {
            check_admin_referer('flashsite_design_save_hero');
            $raw = wp_unslash($_POST['hero'] ?? []);
            $items = [];
            $activeCount = 0;
            if (is_array($raw)) {
                foreach ($raw as $row) {
                    if (!is_array($row)) continue;
                    $base = DesignEngine::defaultHero();
                    $base['title'] = sanitize_text_field($row['title'] ?? '');
                    $base['subtitle'] = sanitize_textarea_field($row['subtitle'] ?? '');
                    $base['button_text'] = sanitize_text_field($row['button_text'] ?? '');
                    $base['button_url'] = esc_url_raw($row['button_url'] ?? '');
                    $base['image_id'] = absint($row['image_id'] ?? 0);
                    $base['mobile_image_id'] = absint($row['mobile_image_id'] ?? 0);
                    $base['status'] = !empty($row['active']) ? 'active' : 'inactive';
                    $base['layout_model'] = $this->enum($row['layout_model'] ?? 'background', ['split','background','centered'], 'background');
                    $base['layout_side'] = $this->enum($row['layout_side'] ?? 'right', ['left','right'], 'right');
                    $base['image_focus'] = $this->enum($row['image_focus'] ?? 'center', ['left','center','right'], 'center');
                    $base['image_focus_mobile'] = $this->enum($row['image_focus_mobile'] ?? 'center', ['top','center','bottom'], 'center');
                    $base['readability'] = $this->enum($row['readability'] ?? 'soft', ['none','soft','strong','light'], 'soft');
                    $base['text_position'] = $this->enum($row['text_position'] ?? 'left', ['left','center','right'], 'left');
                    $base['vertical_position'] = $this->enum($row['vertical_position'] ?? 'bottom', ['top','center','bottom'], 'bottom');
                    $base['text_position_mobile'] = $this->enum($row['text_position_mobile'] ?? 'center', ['left','center','right'], 'center');
                    $base['vertical_position_mobile'] = $this->enum($row['vertical_position_mobile'] ?? 'center', ['top','center','bottom'], 'center');
                    foreach (['radius_top_left','radius_top_right','radius_bottom_right','radius_bottom_left'] as $field) {
                        $base[$field] = $this->intBetween($row[$field] ?? 16, 0, 120);
                    }
                    foreach (['padding_top','padding_right','padding_bottom','padding_left'] as $field) {
                        $base[$field] = $this->intBetween($row[$field] ?? 36, 0, 240);
                    }
                    foreach (['padding_top_mobile','padding_right_mobile','padding_bottom_mobile','padding_left_mobile'] as $field) {
                        $base[$field] = $this->intBetween($row[$field] ?? 24, 0, 240);
                    }
                    $base['backdrop_type'] = $this->enum($row['backdrop_type'] ?? 'none', ['none','solid','blur','gradient'], 'none');
                    $base['backdrop_color'] = sanitize_text_field($row['backdrop_color'] ?? 'rgba(0,0,0,0.45)');
                    if ($base['readability'] === 'none') { $base['backdrop_type'] = 'none'; $base['backdrop_color'] = 'transparent'; }
                    if ($base['readability'] === 'soft') { $base['backdrop_type'] = 'solid'; $base['backdrop_color'] = 'rgba(0,0,0,0.42)'; }
                    if ($base['readability'] === 'strong') { $base['backdrop_type'] = 'solid'; $base['backdrop_color'] = 'rgba(0,0,0,0.62)'; }
                    if ($base['readability'] === 'light') { $base['backdrop_type'] = 'solid'; $base['backdrop_color'] = 'rgba(255,255,255,0.76)'; }
                    $base['transition'] = $this->enum($row['transition'] ?? 'fade', ['fade','slide','zoom'], 'fade');
                    $base['delay'] = $this->intBetween($row['delay'] ?? 5000, 2000, 15000);
                    $base['overlay_opacity'] = $this->floatBetween($row['overlay_opacity'] ?? 0.35, 0.0, 1.0);
                    $base['content_max_width'] = $this->intBetween($row['content_max_width'] ?? 760, 320, 1200);
                    $base['content_max_width_mobile'] = $this->intBetween($row['content_max_width_mobile'] ?? 100, 60, 100);
                    $base['use_global_style'] = !empty($row['use_global_style']) || !empty($row['use_global_banner_style']) || !empty($row['use_global_button_style']);
                    $base['use_global_banner_style'] = $base['use_global_style'];
                    $base['use_global_button_style'] = $base['use_global_style'];
                    $base['button_style'] = $this->enum($row['button_style'] ?? 'pill', ['pill','rounded','square','sharp'], 'pill');
                    $base['button_text_color'] = sanitize_hex_color($row['button_text_color'] ?? '#ffffff') ?: '#ffffff';
                    $base['button_bg_color'] = sanitize_hex_color($row['button_bg_color'] ?? '#8224e3') ?: '#8224e3';
                    $base['button_hover_text_color'] = sanitize_hex_color($row['button_hover_text_color'] ?? '#ffffff') ?: '#ffffff';
                    $base['button_hover_bg_color'] = sanitize_hex_color($row['button_hover_bg_color'] ?? '#5b21b6') ?: '#5b21b6';
                    $hasMinimumContent = ($base['title'] !== '') || !empty($base['image_id']);
                    if (!$hasMinimumContent) continue;
                    if ($base['status'] === 'active') {
                        if ($activeCount >= 3 || empty($base['image_id'])) {
                            $base['status'] = 'inactive';
                        } else {
                            $activeCount++;
                        }
                    }
                    $base['active'] = $base['status'] === 'active';
                    $items[] = DesignEngine::normalizeHero($base);
                    if (count($items) >= 3) break;
                }
            }
            update_option(FLASHSITE_DESIGN_OPTION_HERO, DesignEngine::normalizeHeroes($items), false);
            update_option('flashsite_design_hero_settings', ['autoplay' => !empty($_POST['hero_global_autoplay'])], false);
            wp_safe_redirect(add_query_arg(['page'=>'flashsite-design','updated'=>'1'], admin_url('admin.php'))); exit;
        }
        if (isset($_POST['flashsite_design_save_topbar'])) {
            check_admin_referer('flashsite_design_save_topbar');
            update_option(FLASHSITE_DESIGN_OPTION_TOPBAR, DesignEngine::normalizeTopBar([
                'enabled' => !empty($_POST['enabled']),
                'text' => sanitize_text_field(wp_unslash($_POST['text'] ?? ($_POST['message'] ?? ''))),
                'link' => esc_url_raw(wp_unslash($_POST['link'] ?? ($_POST['link_url'] ?? ''))),
                'background_color' => sanitize_hex_color(wp_unslash($_POST['background_color'] ?? ($_POST['background'] ?? '#111111'))) ?: '#111111',
                'text_color' => sanitize_hex_color(wp_unslash($_POST['text_color'] ?? ($_POST['color'] ?? '#ffffff'))) ?: '#ffffff',
            ]), false);
            wp_safe_redirect(add_query_arg(['page'=>'flashsite-design-top-bar','updated'=>'1'], admin_url('admin.php'))); exit;
        }
        if (isset($_POST['flashsite_design_save_theme_mode'])) {
            check_admin_referer('flashsite_design_save_theme_mode');
            update_option(FLASHSITE_DESIGN_OPTION_THEME, DesignEngine::normalizeTheme([
                'enabled' => !empty($_POST['enabled']),
                'primary_color' => sanitize_hex_color(wp_unslash($_POST['primary_color'] ?? ($_POST['primary'] ?? '#8224e3'))) ?: '#8224e3',
                'secondary_color' => sanitize_hex_color(wp_unslash($_POST['secondary_color'] ?? ($_POST['secondary'] ?? '#5f6672'))) ?: '#5f6672',
                'text_color' => sanitize_hex_color(wp_unslash($_POST['text_color'] ?? '#ffffff')) ?: '#ffffff',
                'font_family' => sanitize_text_field(wp_unslash($_POST['font_family'] ?? 'inherit')),
                'button_radius' => $this->enum(wp_unslash($_POST['button_radius'] ?? ($_POST['button_style'] ?? 'pill')), ['pill','rounded','square','sharp'], 'pill'),
                'button_text_color' => sanitize_hex_color(wp_unslash($_POST['button_text_color'] ?? '#ffffff')) ?: '#ffffff',
                'button_bg_color' => sanitize_hex_color(wp_unslash($_POST['button_bg_color'] ?? '#8224e3')) ?: '#8224e3',
                'button_hover_text_color' => sanitize_hex_color(wp_unslash($_POST['button_hover_text_color'] ?? '#ffffff')) ?: '#ffffff',
                'button_hover_bg_color' => sanitize_hex_color(wp_unslash($_POST['button_hover_bg_color'] ?? '#5b21b6')) ?: '#5b21b6',
                'apply_hero' => !empty($_POST['apply_hero']),
                'apply_topbar' => !empty($_POST['apply_topbar']),
            ]), false);
            wp_safe_redirect(add_query_arg(['page'=>'flashsite-design-theme-mode','updated'=>'1'], admin_url('admin.php'))); exit;
        }
    }

    private function mediaField(string $name, string $label, int $attachmentId): void {
        $preview = $attachmentId ? wp_get_attachment_image_url($attachmentId, 'medium') : '';
        echo '<div class="fsd-media-field"><strong>' . esc_html($label) . '</strong>';
        echo '<input type="hidden" class="fsd-media-id" name="' . esc_attr($name) . '" value="' . esc_attr((string)$attachmentId) . '">';
        echo '<div class="fsd-media-preview">' . ($preview ? '<img src="' . esc_url($preview) . '" alt="">' : '<span>' . esc_html__('Sem imagem selecionada','flashsite-design') . '</span>') . '</div>';
        echo '<p><button type="button" class="button fsd-open-media">' . esc_html__('Selecionar imagem','flashsite-design') . '</button> <button type="button" class="button fsd-clear-media">' . esc_html__('Remover','flashsite-design') . '</button></p></div>';
    }
    private function textField(string $name, string $label, string $value): void {
        echo '<p><label><strong>' . esc_html($label) . '</strong><br><input class="regular-text fsd-smart-input" data-name="' . esc_attr($name) . '" type="text" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '"></label></p>';
    }
    private function textareaField(string $name, string $label, string $value): void {
        echo '<p><label><strong>' . esc_html($label) . '</strong><br><textarea class="large-text" rows="4" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea></label></p>';
    }
    private function numberField(string $name, string $label, $value, $min, $max, $step='1'): void {
        echo '<p><label><strong>' . esc_html($label) . '</strong><br><input class="small-text" type="number" step="' . esc_attr((string)$step) . '" min="' . esc_attr((string)$min) . '" max="' . esc_attr((string)$max) . '" name="' . esc_attr($name) . '" value="' . esc_attr((string)$value) . '"></label></p>';
    }
    private function selectField(string $name, string $label, string $value, array $options): void {
        echo '<p><label><strong>' . esc_html($label) . '</strong><br><select name="' . esc_attr($name) . '">';
        foreach ($options as $k => $v) echo '<option value="' . esc_attr((string)$k) . '"' . selected($value, (string)$k, false) . '>' . esc_html((string)$v) . '</option>';
        echo '</select></label></p>';
    }
    private function colorField(string $name, string $label, string $value, string $fallback = '#000000'): void {
        $safe = sanitize_hex_color($value) ?: $fallback;
        echo '<p><label><strong>' . esc_html($label) . '</strong><span class="fsd-field-help">Use o seletor visual ou introduza um valor hexadecimal válido.</span><span class="fsd-color-field">';
        echo '<input class="fsd-color-input" type="color" value="' . esc_attr($safe) . '" data-target-name="' . esc_attr($name) . '">';
        echo '<input class="regular-text fsd-smart-input fsd-color-text" data-name="' . esc_attr($name) . '" type="text" name="' . esc_attr($name) . '" value="' . esc_attr($safe) . '">';
        echo '</span></label></p>';
    }
    private function segmentedField(string $name, string $label, string $value, array $options): void {
        echo '<div class="fsd-field-block"><strong>' . esc_html($label) . '</strong><span class="fsd-field-help">Escolha visual rápida para reduzir erro de configuração.</span><div class="fsd-segmented" role="group">';
        foreach ($options as $key => $optionLabel) {
            $id = sanitize_html_class($name . '-' . $key);
            echo '<label class="fsd-segmented__item">';
            echo '<input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr((string)$key) . '"' . checked($value, (string)$key, false) . '>';
            echo '<span>' . esc_html((string)$optionLabel) . '</span>';
            echo '</label>';
        }
        echo '</div></div>';
    }


    private function iconChoiceField(string $name, string $label, string $value, array $options, string $variant = 'horizontal'): void {
        echo '<div class="fsd-field-block fsd-icon-choice-field fsd-icon-choice-field--' . esc_attr($variant) . '"><strong>' . esc_html($label) . '</strong><div class="fsd-icon-choice" role="radiogroup">';
        foreach ($options as $key => $optionLabel) {
            echo '<label class="fsd-icon-choice__item">';
            echo '<input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr((string)$key) . '"' . checked($value, (string)$key, false) . '>';
            echo '<span class="fsd-icon-choice__body"><span class="fsd-icon-choice__icon is-' . esc_attr(sanitize_html_class((string)$key)) . '" aria-hidden="true"><i></i></span><span class="fsd-icon-choice__label">' . esc_html((string)$optionLabel) . '</span></span>';
            echo '</label>';
        }
        echo '</div></div>';
    }

    private function positionMatrixField(string $matrixName, string $label, string $horizontalName, string $verticalName, string $horizontalValue, string $verticalValue, string $device = 'desktop'): void {
        $horizontalValue = in_array($horizontalValue, ['left','center','right'], true) ? $horizontalValue : 'center';
        $verticalValue = in_array($verticalValue, ['top','center','bottom'], true) ? $verticalValue : 'center';
        $positions = [
            ['left','top','Superior esquerda'], ['center','top','Superior centro'], ['right','top','Superior direita'],
            ['left','center','Meio esquerda'], ['center','center','Centro'], ['right','center','Meio direita'],
            ['left','bottom','Inferior esquerda'], ['center','bottom','Inferior centro'], ['right','bottom','Inferior direita'],
        ];
        echo '<div class="fsd-field-block fsd-position-matrix-field fsd-position-matrix-field--' . esc_attr($device) . '">';
        echo '<strong>' . esc_html($label) . '</strong>';
        echo '<span class="fsd-field-help">Selecione no quadro onde o texto deve aparecer.</span>';
        echo '<input type="hidden" class="fsd-position-axis" data-axis="h" name="' . esc_attr($horizontalName) . '" value="' . esc_attr($horizontalValue) . '">';
        echo '<input type="hidden" class="fsd-position-axis" data-axis="v" name="' . esc_attr($verticalName) . '" value="' . esc_attr($verticalValue) . '">';
        echo '<div class="fsd-position-matrix" role="radiogroup" aria-label="' . esc_attr($label) . '">';
        foreach ($positions as $position) {
            [$h, $v, $positionLabel] = $position;
            $value = $h . ':' . $v;
            $checked = ($h === $horizontalValue && $v === $verticalValue);
            echo '<label class="fsd-position-matrix__cell" title="' . esc_attr($positionLabel) . '">';
            echo '<input type="radio" name="' . esc_attr($matrixName) . '" value="' . esc_attr($value) . '" data-h="' . esc_attr($h) . '" data-v="' . esc_attr($v) . '"' . checked($checked, true, false) . '>';
            echo '<span><i></i></span>';
            echo '</label>';
        }
        echo '</div></div>';
    }

    private function modelChoiceField(int $i, string $value): void {
        $models = [
            'split' => ['A', 'Texto + imagem lateral', 'Para apresentação institucional com imagem ao lado.'],
            'background' => ['B', 'Imagem de fundo + texto', 'Para campanhas com foto inteira e chamada sobreposta.'],
            'centered' => ['C', 'Texto centralizado', 'Para mensagem direta, lançamento ou aviso principal.'],
        ];
        echo '<div class="fsd-model-choice" role="radiogroup">';
        foreach ($models as $key => $model) {
            echo '<label class="fsd-model-choice__item">';
            echo '<input type="radio" name="hero[' . esc_attr((string)$i) . '][layout_model]" value="' . esc_attr((string)$key) . '"' . checked($value, (string)$key, false) . '>';
            echo '<span class="fsd-model-choice__body"><span class="fsd-model-choice__thumb is-' . esc_attr((string)$key) . '"><i></i></span><span class="fsd-model-choice__copy"><strong>Modelo ' . esc_html($model[0]) . '</strong><em>' . esc_html($model[1]) . '</em><small>' . esc_html($model[2]) . '</small></span></span>';
            echo '</label>';
        }
        echo '</div>';
    }

    private function hiddenHeroField(int $i, string $field, $value): void {
        echo '<input type="hidden" name="hero[' . esc_attr((string)$i) . '][' . esc_attr($field) . ']" value="' . esc_attr((string)$value) . '">';
    }

    private function renderAdminNoticeStack(array $messages = []): void {
        if (empty($messages)) return;
        echo '<div class="fsd-admin-notices">';
        foreach ($messages as $message) {
            echo '<div class="notice notice-success fsd-admin-notice"><p>' . esc_html($message) . '</p></div>';
        }
        echo '</div>';
    }

    private function normalizeModuleTitle(string $title): string {
        return str_replace(' — ', ' / ', $title);
    }

    private function renderModuleHeader(string $title, string $description, array $meta = [], array $actions = [], array $inlineNotices = []): void {
        $logoUrl = FLASHSITE_DESIGN_URL . 'assets/admin/img/flashsite-logo.png';
        echo '<div class="fsd-page-head fsd-module-head">';
        echo '<div class="fsd-module-head__brandmark">';
        echo '<img class="fsd-module-head__logo-image" src="' . esc_url($logoUrl) . '" alt="' . esc_attr__('FlashSite', 'flashsite-design') . '">';
        echo '<div class="screen-reader-text">FlashSite</div>';
        echo '</div>';
        echo '<div class="fsd-page-head__brand fsd-module-head__content"><div class="fsd-page-head__eyebrow">FlashSite Platform</div><h1>' . esc_html($this->normalizeModuleTitle($title)) . '</h1>';
        if (!empty($inlineNotices)) {
            echo '<div class="fsd-module-head__notices">';
            foreach ($inlineNotices as $message) {
                echo '<div class="notice notice-success fsd-admin-notice fsd-admin-notice--inline"><p>' . esc_html($message) . '</p></div>';
            }
            echo '</div>';
        }
        echo '<p class="description">' . esc_html($description) . '</p></div>';
        echo '<div class="fsd-page-head__meta fsd-module-head__meta">';
        foreach ($meta as $label => $value) {
            echo '<div class="fsd-chip"><span>' . esc_html((string)$label) . '</span><strong>' . wp_kses_post((string)$value) . '</strong></div>';
        }
        foreach ($actions as $action) {
            echo '<a class="button fsd-chip-action" href="' . esc_url((string)($action['url'] ?? '#')) . '">' . esc_html((string)($action['label'] ?? 'Ação')) . '</a>';
        }
        echo '</div></div>';
    }

    private function renderTopBarPreview(array $topbar): void {
        $background = esc_attr((string)($topbar['background'] ?? '#111111'));
        $color = esc_attr((string)($topbar['color'] ?? '#ffffff'));
        $message = !empty($topbar['message']) ? esc_html((string)$topbar['message']) : 'Mensagem promocional da Top Bar';
        $link = !empty($topbar['link_text']) ? esc_html((string)$topbar['link_text']) : 'Saber mais';
        echo '<div class="fsd-topbar-preview"><div class="fsd-topbar-preview__bar" style="background:' . $background . ';color:' . $color . '"><span>' . $message . '</span>';
        if (!empty($topbar['link_url']) || !empty($topbar['link_text'])) echo '<a href="#" style="color:' . $color . '">' . $link . '</a>';
        echo '</div></div>';
    }
    private function sectionIntro(string $title, string $description = ''): void {
        echo '<div class="fsd-section-head"><h3 class="fsd-section-title">' . esc_html($title) . '</h3>';
        if ($description !== '') {
            echo '<p class="fsd-section-description">' . esc_html($description) . '</p>';
        }
        echo '</div>';
    }

    private function extractColorValue(string $value, string $fallback = '#000000'): string {
        if (preg_match('/(#[0-9a-fA-F]{6})/', $value, $m)) return $m[1];
        return $fallback;
    }

    private function extractGradientColors(string $value): array {
        preg_match_all('/(#[0-9a-fA-F]{6})/', $value, $m);
        $colors = $m[1] ?? [];
        return [ $colors[0] ?? '#000000', $colors[1] ?? '#4b5563' ];
    }

    private function extractGradientDirection(string $value): string {
        if (preg_match('/linear-gradient\(([^,]+),/', $value, $m)) return trim($m[1]);
        return '180deg';
    }


    private function resolveHeroStyle(array $hero): array {
        return DesignEngine::resolveHero($hero, $this->getThemeSettings());
    }

    private function renderHeroPreviewCanvas(int $i, array $hero): void {
        $hero = $this->resolveHeroStyle($hero);
        $desktop = !empty($hero['image_id']) ? (wp_get_attachment_image_url((int) $hero['image_id'], 'large') ?: '') : '';
        $mobile = !empty($hero['mobile_image_id']) ? (wp_get_attachment_image_url((int) $hero['mobile_image_id'], 'medium') ?: '') : $desktop;
        $buttonRadius = $hero['button_style'] === 'sharp' ? '0px' : ($hero['button_style'] === 'square' ? '8px' : ($hero['button_style'] === 'rounded' ? '16px' : '999px'));
        $bannerRadius = max(0, min(120, (int) $hero['radius_top_left']));
        $desktopContentWidth = max(180, min(320, (int) round(((int) $hero['content_max_width']) * 0.34)));
        $mobileContentWidth = max(180, min(260, (int) round(((int) $hero['content_max_width_mobile']) * 2.1)));
        echo '<aside class="fsd-editor-preview">';
        echo '<div class="fsd-preview-card">';
        echo '<div class="fsd-preview-card__header"><div class="fsd-preview-card__headcopy"><strong>Preview ao vivo</strong><span>Visualização orientativa para validar o banner sem sair do painel.</span></div><div class="fsd-preview-switch" role="tablist"><button type="button" class="button fsd-preview-toggle is-active" data-device="desktop">Desktop/Tablet</button><button type="button" class="button fsd-preview-toggle" data-device="mobile">Mobile</button></div></div>';
        echo '<div class="fsd-preview-stage" data-preview-stage data-device="desktop">';
        echo '<div class="fsd-preview-hero is-layout-' . esc_attr(sanitize_html_class((string) ($hero['layout_model'] ?? 'background'))) . ' is-image-' . esc_attr(sanitize_html_class((string) ($hero['layout_side'] ?? 'right'))) . ' is-readability-' . esc_attr(sanitize_html_class((string) ($hero['readability'] ?? 'soft'))) . ' is-h-' . esc_attr(sanitize_html_class((string) $hero['text_position'])) . ' is-v-' . esc_attr(sanitize_html_class((string) $hero['vertical_position'])) . ' is-hm-' . esc_attr(sanitize_html_class((string) $hero['text_position_mobile'])) . ' is-vm-' . esc_attr(sanitize_html_class((string) $hero['vertical_position_mobile'])) . '" data-desktop-bg="' . esc_url($desktop) . '" data-mobile-bg="' . esc_url($mobile) . '" style="--fsd-preview-bg:url(' . esc_url($desktop) . ');--fsd-preview-bg-position:' . esc_attr($hero['image_focus'] === 'left' ? 'left center' : ($hero['image_focus'] === 'right' ? 'right center' : 'center center')) . ';--fsd-preview-bg-position-mobile:' . esc_attr($hero['image_focus_mobile'] === 'top' ? 'center top' : ($hero['image_focus_mobile'] === 'bottom' ? 'center bottom' : 'center center')) . ';--fsd-preview-button-color:' . esc_attr((string) $hero['button_text_color']) . ';--fsd-preview-button-bg:' . esc_attr((string) $hero['button_bg_color']) . ';--fsd-preview-button-hover-color:' . esc_attr((string) $hero['button_hover_text_color']) . ';--fsd-preview-button-hover-bg:' . esc_attr((string) $hero['button_hover_bg_color']) . ';--fsd-preview-button-radius:' . esc_attr($buttonRadius) . ';--fsd-preview-text-color:' . esc_attr((string) ($hero['text_color'] ?? '#ffffff')) . ';--fsd-preview-font-family:' . esc_attr((string) ($hero['font_family'] ?? 'inherit')) . ';--fsd-preview-radius:' . esc_attr($bannerRadius . 'px') . ';--fsd-preview-content-width:' . esc_attr($desktopContentWidth . 'px') . ';--fsd-preview-content-width-mobile:' . esc_attr($mobileContentWidth . 'px') . ';--fsd-preview-pad-top:' . esc_attr(max(10, min(48, (int) round(((int) $hero['padding_top']) * 0.45))) . 'px') . ';--fsd-preview-pad-right:' . esc_attr(max(10, min(48, (int) round(((int) $hero['padding_right']) * 0.45))) . 'px') . ';--fsd-preview-pad-bottom:' . esc_attr(max(10, min(48, (int) round(((int) $hero['padding_bottom']) * 0.45))) . 'px') . ';--fsd-preview-pad-left:' . esc_attr(max(10, min(48, (int) round(((int) $hero['padding_left']) * 0.45))) . 'px') . ';--fsd-preview-pad-top-mobile:' . esc_attr(max(8, min(42, (int) round(((int) $hero['padding_top_mobile']) * 0.55))) . 'px') . ';--fsd-preview-pad-right-mobile:' . esc_attr(max(8, min(42, (int) round(((int) $hero['padding_right_mobile']) * 0.55))) . 'px') . ';--fsd-preview-pad-bottom-mobile:' . esc_attr(max(8, min(42, (int) round(((int) $hero['padding_bottom_mobile']) * 0.55))) . 'px') . ';--fsd-preview-pad-left-mobile:' . esc_attr(max(8, min(42, (int) round(((int) $hero['padding_left_mobile']) * 0.55))) . 'px') . ';">';
        echo '<div class="fsd-preview-hero__inner"><div class="fsd-preview-hero__content is-backdrop-' . esc_attr(sanitize_html_class((string) $hero['backdrop_type'])) . '" style="--fsd-preview-backdrop:' . esc_attr((string) $hero['backdrop_color']) . '">';
        echo '<h4 class="fsd-preview-title">' . esc_html($hero['title'] !== '' ? (string) $hero['title'] : 'Your Headline Here') . '</h4>';
        echo '<p class="fsd-preview-subtitle">' . esc_html($hero['subtitle'] !== '' ? (string) $hero['subtitle'] : "This is a subtitle describing your banner's primary offer or message.") . '</p>';
        echo '<span class="fsd-preview-button">' . esc_html($hero['button_text'] !== '' ? (string) $hero['button_text'] : 'Action Button') . '</span>';
        echo '</div></div></div>';
        echo '<div class="fsd-preview-sidepanel">';
        echo '<div class="fsd-preview-sidepanel__group"><h4>Estilo</h4><p>Backdrop, botão e leitura geral.</p></div>';
        echo '<div class="fsd-preview-sidepanel__group"><h4>Comportamento</h4><p>Transição: ' . esc_html((string) $hero['transition']) . ' · Delay: ' . esc_html((string) $hero['delay']) . 'ms</p></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</aside>';
    }

    private function renderThemePreviewCard(array $theme): void {
        $buttonRadius = ($theme['button_radius'] ?? 'pill') === 'sharp' ? '0px' : ((($theme['button_radius'] ?? 'pill') === 'square') ? '8px' : ((($theme['button_radius'] ?? 'pill') === 'rounded') ? '16px' : '999px'));
        echo '<aside class="fsd-editor-preview fsd-theme-preview">';
        echo '<div class="fsd-preview-card fsd-preview-card--theme">';
        echo '<div class="fsd-preview-card__header"><div class="fsd-preview-card__headcopy"><strong>Preview do sistema</strong><span>Leitura rápida da tipografia, botão global e paleta ativa.</span></div></div>';
        echo '<div class="fsd-preview-stage fsd-preview-stage--theme">';
        echo '<div class="fsd-theme-preview-hero" style="--fsd-theme-primary:' . esc_attr((string)($theme['primary_color'] ?? '#8224e3')) . ';--fsd-theme-secondary:' . esc_attr((string)($theme['secondary_color'] ?? '#5f6672')) . ';--fsd-theme-accent:' . esc_attr((string)($theme['secondary_color'] ?? '#ff4d6d')) . ';--fsd-theme-text:' . esc_attr((string)($theme['text_color'] ?? '#ffffff')) . ';--fsd-theme-font:' . esc_attr((string)($theme['font_family'] ?? 'inherit')) . ';--fsd-theme-button-color:' . esc_attr((string)($theme['button_text_color'] ?? '#ffffff')) . ';--fsd-theme-button-bg:' . esc_attr((string)($theme['button_bg_color'] ?? '#8224e3')) . ';--fsd-theme-button-radius:' . esc_attr($buttonRadius) . ';">';
        echo '<div class="fsd-theme-preview-hero__badge">Tema ' . (!empty($theme['enabled']) ? 'ativo' : 'em rascunho') . '</div>';
        echo '<h3>FlashSite Design System</h3>';
        echo '<p>Tipografia, cor de texto e botão global prontos para herança no Hero e na Top Bar.</p>';
        echo '<span class="fsd-theme-preview-button">Botão global</span>';
        echo '</div>';
        echo '<div class="fsd-theme-palette">';
        foreach ([['Principal',$theme['primary_color'] ?? '#8224e3'],['Apoio',$theme['secondary_color'] ?? '#5f6672'],['Acento',$theme['secondary_color'] ?? '#ff4d6d'],['Texto',$theme['text_color'] ?? '#ffffff']] as $swatch) {
            echo '<div class="fsd-theme-palette__item"><span class="fsd-theme-palette__swatch" style="background:' . esc_attr((string)$swatch[1]) . '"></span><strong>' . esc_html((string)$swatch[0]) . '</strong><small>' . esc_html((string)$swatch[1]) . '</small></div>';
        }
        echo '</div>';
        echo '<div class="fsd-preview-sidepanel">';
        echo '<div class="fsd-preview-sidepanel__group"><h4>Tipografia</h4><p>' . esc_html((string)($theme['font_family'] ?? 'inherit')) . '</p></div>';
        echo '<div class="fsd-preview-sidepanel__group"><h4>Aplicação</h4><p>Padrão: ' . (!empty($theme['enabled']) ? 'ativo' : 'inativo') . ' · Hero: ' . (!empty($theme['apply_hero']) ? 'sim' : 'não') . '</p></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</aside>';
    }

    private function renderHeroBannerCard(int $i, array $hero): void {
        $hero = array_merge($this->defaultsHeroItem(), (array) $hero);
        $hero = DesignEngine::normalizeHero($hero);
        $isActive = ($hero['status'] ?? 'inactive') === 'active';
        $modelLabels = [
            'split' => 'Modelo A · Texto + imagem lateral',
            'background' => 'Modelo B · Imagem de fundo + texto',
            'centered' => 'Modelo C · Texto centralizado',
        ];
        $statusLabel = $isActive ? 'Publicado' : 'Oculto';
        echo '<div class="fsd-card fsd-hero-card fsd-simplified-card" data-hero-card data-index="' . esc_attr((string) $i) . '">';
        echo '<div class="fsd-card__header fsd-card__header--simplified"><div>';
        echo '<div class="fsd-card__eyebrow">Banner ' . esc_html((string) ($i + 1)) . '</div>';
        echo '<h2 class="fsd-hero-card__title">' . esc_html($hero['title'] !== '' ? (string) $hero['title'] : 'Novo banner') . '</h2>';
        echo '<p class="description">' . esc_html(($modelLabels[$hero['layout_model']] ?? $modelLabels['background']) . ' · ' . $statusLabel) . '</p>';
        echo '</div>';
        echo '<div class="fsd-card__tools"><label class="fsd-publish-toggle"><input type="checkbox" name="hero[' . esc_attr((string)$i) . '][active]" value="1"' . checked($isActive, true, false) . '><span>Mostrar no site</span></label>';
        echo '<div class="fsd-card__actions"><button type="button" class="button fsd-move-card" data-direction="up">↑</button><button type="button" class="button fsd-move-card" data-direction="down">↓</button><button type="button" class="button fsd-duplicate-card">Duplicar</button><button type="button" class="button fsd-remove-card">Remover</button></div></div>';
        echo '</div>';

        echo '<div class="fsd-editor-shell">';
        echo '<div class="fsd-editor-main">';

        echo '<details class="fsd-editor-section is-open" open><summary><span>1. Texto do banner</span><small>Mensagem, botão e link principal.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-grid fsd-grid--2">';
        $this->textField("hero[$i][title]", 'Título', (string)$hero['title']);
        $this->textField("hero[$i][button_text]", 'Texto do botão', (string)$hero['button_text']);
        echo '</div>';
        $this->textareaField("hero[$i][subtitle]", 'Subtítulo', (string)$hero['subtitle']);
        $this->textField("hero[$i][button_url]", 'Link do botão', (string)$hero['button_url']);
        echo '</div></details>';

        $this->hiddenHeroField($i, 'layout_model', (string)$hero['layout_model']);

        echo '<details class="fsd-editor-section is-open" open><summary><span>2. Imagens</span><small>Uma imagem para Desktop/Tablet e outra para Mobile.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-grid fsd-grid--2">';
        $this->mediaField("hero[$i][image_id]", 'Imagem para Desktop/Tablet', (int)$hero['image_id']);
        $this->mediaField("hero[$i][mobile_image_id]", 'Imagem para Mobile', (int)$hero['mobile_image_id']);
        echo '</div><p class="description">Use uma imagem horizontal no Desktop/Tablet e uma imagem mais vertical ou aproximada no Mobile.</p></div></details>';

        echo '<details class="fsd-editor-section is-open" open><summary><span>3. Posição e leitura</span><small>Ajuste a posição do texto conforme a visualização ativa.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-device-position-layout">';
        echo '<div class="fsd-device-panel fsd-device-panel--desktop" data-device-only="desktop"><div class="fsd-device-panel__header"><strong>Desktop/Tablet</strong><span>Usado nas visualizações largas.</span></div>';
        $this->positionMatrixField("hero[$i][position_matrix_desktop]", 'Posição do texto no Desktop/Tablet', "hero[$i][text_position]", "hero[$i][vertical_position]", (string)$hero['text_position'], (string)$hero['vertical_position'], 'desktop');
        $this->iconChoiceField("hero[$i][image_focus]", 'Foco da imagem no Desktop/Tablet', (string)$hero['image_focus'], ['left'=>'Esquerda','center'=>'Centro','right'=>'Direita'], 'horizontal');
        echo '</div>';
        echo '<div class="fsd-device-panel fsd-device-panel--mobile" data-device-only="mobile"><div class="fsd-device-panel__header"><strong>Mobile</strong><span>Usado na visualização Mobile.</span></div>';
        $this->positionMatrixField("hero[$i][position_matrix_mobile]", 'Posição do texto no Mobile', "hero[$i][text_position_mobile]", "hero[$i][vertical_position_mobile]", (string)$hero['text_position_mobile'], (string)$hero['vertical_position_mobile'], 'mobile');
        $this->iconChoiceField("hero[$i][image_focus_mobile]", 'Foco da imagem no Mobile', (string)$hero['image_focus_mobile'], ['top'=>'Topo','center'=>'Centro','bottom'=>'Base'], 'vertical');
        echo '</div>';
        echo '</div>';
        $this->hiddenHeroField($i, 'layout_side', (string)$hero['layout_side']);
        $this->segmentedField("hero[$i][readability]", 'Melhorar leitura do texto', (string)$hero['readability'], ['none'=>'Desligado','soft'=>'Suave','strong'=>'Forte','light'=>'Claro']);
        echo '</div></details>';

        echo '<details class="fsd-editor-section is-open fsd-button-section" open><summary><span>4. Botão</span><small>Use o padrão do site ou personalize somente este banner.</small></summary><div class="fsd-editor-section__body">';
        echo '<label class="fsd-toggle-card fsd-global-button-toggle"><input type="checkbox" name="hero[' . esc_attr((string)$i) . '][use_global_style]" value="1"' . checked(!empty($hero['use_global_style']), true, false) . '> <span>Usar botão padrão do FlashSite Design</span></label>';
        echo '<div class="fsd-grid fsd-grid--3 fsd-banner-button-controls">';
        $this->segmentedField("hero[$i][button_style]", 'Formato do botão', (string)$hero['button_style'], ['sharp'=>'Reto','square'=>'Leve','rounded'=>'Arredondado','pill'=>'Pill']);
        $this->colorField("hero[$i][button_text_color]", 'Cor do texto', (string)$hero['button_text_color'], '#ffffff');
        $this->colorField("hero[$i][button_bg_color]", 'Cor do botão', (string)$hero['button_bg_color'], '#8224e3');
        echo '</div></div></details>';

        echo '<details class="fsd-editor-section fsd-advanced-section"><summary><span>Ajustes avançados</span><small>Use apenas quando o suporte precisar corrigir espaçamento ou acabamento.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-grid fsd-grid--2">';
        $this->numberField("hero[$i][content_max_width]", 'Largura máxima do texto', (int)$hero['content_max_width'], 320, 1200);
        $this->numberField("hero[$i][content_max_width_mobile]", 'Largura do texto no Mobile (%)', (int)$hero['content_max_width_mobile'], 60, 100);
        echo '</div>';
        echo '<div class="fsd-grid fsd-grid--4">';
        $this->numberField("hero[$i][padding_top]", 'Respiro topo', (int)$hero['padding_top'], 0, 240);
        $this->numberField("hero[$i][padding_right]", 'Respiro direita', (int)$hero['padding_right'], 0, 240);
        $this->numberField("hero[$i][padding_bottom]", 'Respiro base', (int)$hero['padding_bottom'], 0, 240);
        $this->numberField("hero[$i][padding_left]", 'Respiro esquerda', (int)$hero['padding_left'], 0, 240);
        echo '</div>';
        echo '<div class="fsd-grid fsd-grid--4">';
        $this->numberField("hero[$i][radius_top_left]", 'Canto sup. esq.', (int)$hero['radius_top_left'], 0, 120);
        $this->numberField("hero[$i][radius_top_right]", 'Canto sup. dir.', (int)$hero['radius_top_right'], 0, 120);
        $this->numberField("hero[$i][radius_bottom_right]", 'Canto inf. dir.', (int)$hero['radius_bottom_right'], 0, 120);
        $this->numberField("hero[$i][radius_bottom_left]", 'Canto inf. esq.', (int)$hero['radius_bottom_left'], 0, 120);
        echo '</div>';
        $this->hiddenHeroField($i, 'padding_top_mobile', (int)$hero['padding_top_mobile']);
        $this->hiddenHeroField($i, 'padding_right_mobile', (int)$hero['padding_right_mobile']);
        $this->hiddenHeroField($i, 'padding_bottom_mobile', (int)$hero['padding_bottom_mobile']);
        $this->hiddenHeroField($i, 'padding_left_mobile', (int)$hero['padding_left_mobile']);
        $this->hiddenHeroField($i, 'button_hover_text_color', (string)$hero['button_hover_text_color']);
        $this->hiddenHeroField($i, 'button_hover_bg_color', (string)$hero['button_hover_bg_color']);
        $this->hiddenHeroField($i, 'backdrop_color', (string)$hero['backdrop_color']);
        echo '</div></details>';

        $this->hiddenHeroField($i, 'transition', (string)$hero['transition']);
        $this->hiddenHeroField($i, 'delay', (int)$hero['delay']);
        $this->hiddenHeroField($i, 'overlay_opacity', (string)$hero['overlay_opacity']);

        echo '</div>';
        $this->renderHeroPreviewCanvas($i, $hero);
        echo '</div>';
        echo '</div>';
    }

    public function renderHeroAdminPage(): void {
        $heroes = $this->getHeroes();
        $theme = $this->getThemeSettings();
        $heroSettings = array_merge(['autoplay' => true], (array) get_option('flashsite_design_hero_settings', []));
        if (!is_array($heroes) || empty($heroes)) $heroes = [$this->defaultsHeroItem()];
        echo '<div class="wrap flashsite-design-admin"><div class="fsd-shell">';
        $notices = [];
        if (isset($_GET['updated'])) $notices[] = 'Campanhas Hero guardadas com sucesso.';
        $this->renderModuleHeader(
            'FlashSite Design — Banners do Site',
            'Atualize os banners principais do seu site sem alterar a estrutura das páginas. Você pode publicar até 3 banners, com textos, botões e imagens próprias para Desktop/Tablet e Mobile.',
            [
                'Shortcode' => '<code>[flashsite_hero_banners]</code>',
                'Limite' => esc_html(count($heroes)) . '/3 banners'
            ],
            [],
            $notices
        );
        echo '<form method="post" class="fsd-hero-admin-form" data-theme-enabled="' . (!empty($theme['enabled']) ? '1' : '0') . '" data-theme-font="' . esc_attr((string)($theme['font_family'] ?? 'inherit')) . '" data-theme-text-color="' . esc_attr((string)($theme['text_color'] ?? '#ffffff')) . '" data-theme-button-style="' . esc_attr((string)($theme['button_style'] ?? 'pill')) . '" data-theme-button-color="' . esc_attr((string)($theme['button_text_color'] ?? '#ffffff')) . '" data-theme-button-bg="' . esc_attr((string)($theme['button_bg_color'] ?? '#8224e3')) . '" data-theme-button-hover-color="' . esc_attr((string)($theme['button_hover_text_color'] ?? '#ffffff')) . '" data-theme-button-hover-bg="' . esc_attr((string)($theme['button_hover_bg_color'] ?? '#5b21b6')) . '">';
        wp_nonce_field('flashsite_design_save_hero');
        echo '<div class="fsd-toolbar fsd-toolbar--hero"><div class="fsd-toolbar__info"><strong>Banners principais</strong><span>Adicione, duplique, reordene ou oculte banners em um editor simplificado.</span></div><div class="fsd-toolbar__cluster"><label class="fsd-toggle-card"><input type="checkbox" name="hero_global_autoplay" value="1"' . checked(!empty($heroSettings['autoplay']), true, false) . '> <span>Autoplay global</span></label><div class="fsd-toolbar__actions"><button type="button" class="button button-secondary fsd-add-banner">Novo banner</button></div></div></div>';
        echo '<div class="fsd-toolbar-note">O autoplay vale para todos os banners publicados. O plugin permite no máximo 3 banners para manter a navegação simples e rápida.</div>';
        echo '<div class="fsd-hero-card-list" data-hero-list>';
        foreach ($heroes as $i => $hero) {
            $this->renderHeroBannerCard((int) $i, (array) $hero);
        }
        echo '</div>';
        echo '<script type="text/template" id="tmpl-fsd-hero-card">';
        ob_start();
        $this->renderHeroBannerCard(9999, $this->defaultsHeroItem());
        echo str_replace(['hero[9999]', 'Banner 10000'], ['hero[__INDEX__]', 'Banner __POSITION__'], (string) ob_get_clean());
        echo '</script>';
        echo '<div class="fsd-sticky-actions"><button type="submit" name="flashsite_design_save_hero" class="button button-primary button-hero">Guardar banners</button></div>';
        echo '</form></div></div>';
    }

    public function renderTopBarAdminPage(): void {
        $topbar = $this->getTopBarSettings();
        echo '<div class="wrap flashsite-design-admin"><div class="fsd-shell">';
        $notices = [];
        if (isset($_GET['updated'])) $notices[] = 'Barra de Aviso guardada com sucesso.';
        $this->renderModuleHeader(
            'FlashSite Design — Barra de Aviso',
            'Componente utilitário para mensagens sazonais, campanhas e avisos rápidos sem tocar na estrutura do site entregue.',
            ['Estado' => (!empty($topbar['enabled']) ? 'Ativa' : 'Inativa')],
            [],
            $notices
        );
        echo '<form method="post">';
        wp_nonce_field('flashsite_design_save_topbar');
        echo '<div class="fsd-card fsd-topbar-card"><div class="fsd-card__header"><div><h2>Configuração da Barra de Aviso</h2><p class="description">Mensagem curta, link opcional e estilo simples para ações pontuais.</p></div><div class="fsd-card__toggles"><label><input type="checkbox" name="enabled" value="1"' . checked(!empty($topbar['enabled']), true, false) . '> Ativar Barra de Aviso</label></div></div>';
        echo '<div class="fsd-editor-main fsd-editor-main--single">';
        echo '<details class="fsd-editor-section is-open" open><summary><span>Preview</span><small>Leitura rápida da barra antes de guardar.</small></summary><div class="fsd-editor-section__body">';
        $this->renderTopBarPreview((array)$topbar);
        echo '</div></details>';
        echo '<details class="fsd-editor-section is-open" open><summary><span>Conteúdo</span><small>Mensagem principal e link opcional.</small></summary><div class="fsd-editor-section__body">';
        $this->textField('text', 'Mensagem', (string)$topbar['text']);
        echo '<div class="fsd-grid fsd-grid--2">';
        $this->textField('link', 'URL do link', (string)$topbar['link']);
        echo '<p class="description">A Barra de Aviso usa apenas um link opcional e mantém o texto do CTA fixo para reduzir erro operacional.</p>';
        echo '</div></div></details>';
        echo '<details class="fsd-editor-section" open><summary><span>Estilo</span><small>Cores essenciais da Top Bar.</small></summary><div class="fsd-editor-section__body"><div class="fsd-grid fsd-grid--2">';
        $this->colorField('background_color', 'Cor de fundo', (string)$topbar['background_color'], '#111111');
        $this->colorField('text_color', 'Cor do texto', (string)$topbar['text_color'], '#ffffff');
        echo '</div></div></details>';
        echo '</div></div><div class="fsd-sticky-actions"><button type="submit" name="flashsite_design_save_topbar" class="button button-primary button-hero">Guardar Barra de Aviso</button></div></form></div></div>';
    }

    public function renderThemeModeAdminPage(): void {
        $theme = $this->getThemeSettings();
        echo '<div class="wrap flashsite-design-admin"><div class="fsd-shell">';
        $notices = [];
        if (isset($_GET['updated'])) $notices[] = 'Estilo Padrão guardado com sucesso.';
        $this->renderModuleHeader(
            'FlashSite Design — Estilo Padrão',
            'Design System base do plugin: paleta, tipografia e botão global reutilizáveis no Hero e na Top Bar.',
            ['Estado' => (!empty($theme['enabled']) ? 'Ativo' : 'Inativo')],
            [],
            $notices
        );
        echo '<form method="post">';
        wp_nonce_field('flashsite_design_save_theme_mode');
        echo '<div class="fsd-editor-shell fsd-editor-shell--theme fsd-editor-shell--theme-simple">';
        echo '<div class="fsd-editor-main">';
        echo '<div class="fsd-card fsd-theme-simple-card"><div class="fsd-card__header"><div><h2>Estilo Visual Padrão</h2><p class="description">Defina apenas o botão padrão que os banners podem herdar. Cada banner ainda pode ter cor própria quando necessário.</p></div><div class="fsd-card__toggles"><label><input type="checkbox" name="enabled" value="1"' . checked(!empty($theme['enabled']), true, false) . '> Ativar padrão</label></div></div>';
        echo '<details class="fsd-editor-section is-open" open><summary><span>Botão padrão</span><small>Formato e cores reutilizáveis nos banners.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-grid fsd-grid--3">';
        $this->segmentedField('button_radius', 'Formato do botão', (string)$theme['button_radius'], ['sharp'=>'Reto','square'=>'Leve','rounded'=>'Arredondado','pill'=>'Pill']);
        $this->colorField('button_text_color', 'Cor do texto', (string)$theme['button_text_color'], '#ffffff');
        $this->colorField('button_bg_color', 'Cor do botão', (string)$theme['button_bg_color'], '#8224e3');
        echo '</div>';
        echo '<div class="fsd-note-card fsd-note-card--inline"><strong>Uso nos banners</strong><span>Na edição de cada banner, deixe “Usar botão padrão” ligado para herdar estas definições. Desligue apenas quando aquele banner precisar de outra cor.</span></div>';
        echo '</div></details>';
        echo '<details class="fsd-editor-section fsd-advanced-section"><summary><span>Configurações avançadas</span><small>Itens preservados para compatibilidade.</small></summary><div class="fsd-editor-section__body">';
        echo '<div class="fsd-grid fsd-grid--2">';
        $this->colorField('primary_color', 'Cor principal', (string)$theme['primary_color'], '#8224e3');
        $this->colorField('secondary_color', 'Cor de apoio', (string)$theme['secondary_color'], '#5f6672');
        $this->colorField('text_color', 'Cor de texto global', (string)$theme['text_color'], '#ffffff');
        $this->selectField('font_family', 'Família tipográfica', (string)$theme['font_family'], ['inherit'=>'Site / Herdar','Inter, Arial, sans-serif'=>'Sans moderna','Georgia, serif'=>'Serif clássica']);
        $this->colorField('button_hover_text_color', 'Cor texto hover', (string)$theme['button_hover_text_color'], '#ffffff');
        $this->colorField('button_hover_bg_color', 'Cor fundo hover', (string)$theme['button_hover_bg_color'], '#5b21b6');
        echo '</div>';
        echo '<div class="fsd-choices-list">';
        echo '<label><input type="checkbox" name="apply_hero" value="1"' . checked(!empty($theme['apply_hero']), true, false) . '> Aplicar no Hero</label>';
        echo '<label><input type="checkbox" name="apply_topbar" value="1"' . checked(!empty($theme['apply_topbar']), true, false) . '> Aplicar na Barra de Aviso</label>';
        echo '</div></div></details>';
        echo '</div>';
        echo '</div>';
        $this->renderThemePreviewCard($theme);
        echo '</div>';
        echo '<div class="fsd-sticky-actions"><button type="submit" name="flashsite_design_save_theme_mode" class="button button-primary button-hero">Guardar Estilo Padrão</button></div></form></div></div>';
    }

    public function renderThemeCssVariables(): void {
        $theme = $this->getThemeSettings();
        if (empty($theme['enabled'])) return;
        $this->registerFrontendAssets();
        wp_enqueue_style('flashsite-design-theme');
        echo '<style id="flashsite-design-theme-vars">:root{--fsd-primary:' . esc_html($theme['primary_color']) . ';--fsd-secondary:' . esc_html($theme['secondary_color']) . ';--fsd-accent:' . esc_html($theme['secondary_color']) . ';--fsd-theme-text:' . esc_html($theme['text_color']) . ';--fsd-theme-font:' . esc_html($theme['font_family']) . ';--fsd-theme-button-color:' . esc_html($theme['button_text_color']) . ';--fsd-theme-button-bg:' . esc_html($theme['button_bg_color']) . ';--fsd-theme-button-hover-color:' . esc_html($theme['button_hover_text_color']) . ';--fsd-theme-button-hover-bg:' . esc_html($theme['button_hover_bg_color']) . ';--fsd-theme-button-radius:' . esc_html(DesignEngine::buttonRadiusByStyle((string) $theme['button_radius'])) . ';}</style>';
    }

    public function renderHeroShortcode(): string {
        $heroes = $this->getHeroes();
        if (empty($heroes)) return '';
        $heroSettings = array_merge(['autoplay' => true], (array) get_option('flashsite_design_hero_settings', []));
        $this->registerFrontendAssets();
        wp_enqueue_style('flashsite-design-hero');
        wp_enqueue_style('flashsite-design-theme');
        wp_enqueue_script('flashsite-design-hero');
        return HeroRenderer::render($heroes, $heroSettings, $this->getThemeSettings());
    }

    public function renderTopBar(): void {
        $topBar = $this->getTopBarSettings();
        $markup = TopBarRenderer::render($topBar, $this->getThemeSettings());
        if ($markup === '') return;
        $this->registerFrontendAssets();
        wp_enqueue_style('flashsite-design-topbar');
        wp_enqueue_style('flashsite-design-theme');
        echo $markup;
    }

    public function renderTopBarShortcode(): string { ob_start(); $this->renderTopBar(); return (string) ob_get_clean(); }
}
