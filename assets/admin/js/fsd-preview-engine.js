jQuery(function($){
  function buttonRadiusByStyle(style){
    if (style === 'sharp') return '0px';
    if (style === 'square') return '8px';
    if (style === 'rounded') return '16px';
    return '999px';
  }

  window.FSDPreview = window.FSDPreview || {
    buttonRadiusByStyle: buttonRadiusByStyle,
    update: function(type, payload){
      if (type === 'topbar') this.applyTopBar(payload);
      if (type === 'theme') this.applyTheme(payload);
    },
    applyTopBar: function(payload){
      if (!payload || !payload.form || !payload.bar) return;
      const form = payload.form, bar = payload.bar;
      const enabled = form.find('input[name="enabled"]').is(':checked');
      const message = $.trim(form.find('input[name="text"], input[name="message"]').first().val() || '') || 'Mensagem promocional da Top Bar';
      const linkText = 'Saber mais';
      const linkUrl = $.trim(form.find('input[name="link"], input[name="link_url"]').first().val() || '#') || '#';
      const bg = $.trim(form.find('input[name="background_color"], input[name="background"]').first().val() || '') || '#111111';
      const color = $.trim(form.find('input[name="text_color"], input[name="color"]').first().val() || '') || '#ffffff';
      bar.css({background:bg, color:color, opacity: enabled ? 1 : 0.58});
      const messageNode = bar.find('span').first();
      messageNode.text(message);
      let link = bar.find('a');
      if (!link.length) { bar.append(' <a href="#"></a>'); link = bar.find('a'); }
      if (linkText.length) { link.text(linkText).attr('href', linkUrl).css('color', color).show(); } else { link.hide(); }
      const statusStrong = $('.fsd-module-head__meta .fsd-chip strong').first();
      if (statusStrong.length) statusStrong.text(enabled ? 'Ativa' : 'Inativa');
    },
    applyTheme: function(payload){
      if (!payload || !payload.form || !payload.preview || !payload.previewCard) return;
      const form = payload.form, preview = payload.preview, previewCard = payload.previewCard;
      const val = function(name, fallback){ return $.trim(form.find('[name="' + name + '"]').val() || '') || fallback; };
      const checked = function(name){ return form.find('[name="' + name + '"]').is(':checked'); };
      const enabled = checked('enabled');
      const primary = val('primary_color', val('primary', '#8224e3'));
      const secondary = val('secondary_color', val('secondary', '#5f6672'));
      const accent = secondary;
      const textColor = val('text_color', '#ffffff');
      const fontFamily = val('font_family', 'inherit');
      const buttonStyle = form.find('input[name="button_radius"]:checked, input[name="button_style"]:checked').first().val() || 'pill';
      const buttonTextColor = val('button_text_color', '#ffffff');
      const buttonBgColor = val('button_bg_color', '#8224e3');
      const buttonHoverTextColor = val('button_hover_text_color', '#ffffff');
      const buttonHoverBgColor = val('button_hover_bg_color', '#5b21b6');
      const applyButtons = checked('apply_hero');
      const applyHero = checked('apply_hero');
      const applyTopbar = checked('apply_topbar');
      preview.css({
        '--fsd-theme-primary': primary,
        '--fsd-theme-secondary': secondary,
        '--fsd-theme-accent': accent,
        '--fsd-theme-text': textColor,
        '--fsd-theme-font': fontFamily,
        '--fsd-theme-button-color': buttonTextColor,
        '--fsd-theme-button-bg': buttonBgColor,
        '--fsd-theme-button-radius': buttonRadiusByStyle(buttonStyle),
        opacity: enabled ? 1 : 0.72
      });
      preview.find('.fsd-theme-preview-hero__badge').text(enabled ? 'Tema ativo' : 'Tema em rascunho');
      const button = preview.find('.fsd-theme-preview-button');
      button.css({color: buttonTextColor, background: buttonBgColor, borderRadius: buttonRadiusByStyle(buttonStyle)});
      button.off('.fsdPreviewHover')
        .on('mouseenter.fsdPreviewHover', function(){ $(this).css({color: buttonHoverTextColor, background: buttonHoverBgColor}); })
        .on('mouseleave.fsdPreviewHover', function(){ $(this).css({color: buttonTextColor, background: buttonBgColor}); });
      const paletteItems = previewCard.find('.fsd-theme-palette__item');
      const colors = [primary, secondary, accent, textColor];
      paletteItems.each(function(index){
        const item = $(this); item.find('.fsd-theme-palette__swatch').css('background', colors[index]); item.find('small').text(colors[index]);
      });
      const sideGroups = previewCard.find('.fsd-preview-sidepanel__group');
      sideGroups.eq(0).find('p').text(fontFamily);
      sideGroups.eq(1).find('p').text('Botões: ' + (applyButtons ? 'sim' : 'não') + ' · Hero: ' + (applyHero ? 'sim' : 'não') + ' · Top Bar: ' + (applyTopbar ? 'sim' : 'não'));
      const statusStrong = $('.fsd-module-head__meta .fsd-chip strong').first();
      if (statusStrong.length) statusStrong.text(enabled ? 'Ativo' : 'Inativo');
    },
    bindTopBar: function(form){
      if (!form || !form.length) return;
      const bar = form.find('.fsd-topbar-preview__bar');
      if (!bar.length) return;
      const update = ()=> this.applyTopBar({form: form, bar: bar});
      form.off('.fsdTopBarEngine').on('input.fsdTopBarEngine change.fsdTopBarEngine', 'input, select, textarea', update);
      update();
    },
    bindTheme: function(form){
      if (!form || !form.length) return;
      const preview = form.find('.fsd-theme-preview-hero');
      const previewCard = form.find('.fsd-preview-card--theme');
      if (!preview.length || !previewCard.length) return;
      const update = ()=> this.applyTheme({form: form, preview: preview, previewCard: previewCard});
      form.off('.fsdThemeEngine').on('input.fsdThemeEngine change.fsdThemeEngine', 'input, select, textarea', update);
      update();
    }
  };

  const topBarForm = $('form').has('button[name="flashsite_design_save_topbar"]');
  if (topBarForm.length) window.FSDPreview.bindTopBar(topBarForm);
  const themeForm = $('form').has('button[name="flashsite_design_save_theme_mode"]');
  if (themeForm.length) window.FSDPreview.bindTheme(themeForm);
});
