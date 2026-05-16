jQuery(function($){
  let mediaFrame = null;

  function buttonRadiusByStyle(style) {
    if (style === 'sharp') return '0px';
    if (style === 'square') return '8px';
    if (style === 'rounded') return '16px';
    return '999px';
  }

  function setPreviewBackground(card, device) {
    const preview = card.find('.fsd-preview-hero');
    if (!preview.length) return;
    const bg = device === 'mobile' ? (preview.attr('data-mobile-bg') || preview.attr('data-desktop-bg') || '') : (preview.attr('data-desktop-bg') || '');
    preview.css('--fsd-preview-bg', bg ? 'url(' + bg + ')' : 'linear-gradient(135deg,#4b5563,#111827)');
  }

  function syncDeviceMode(card, device) {
    card.attr('data-active-device', device);
    card.find('[data-device-only]').each(function(){
      const section = $(this);
      const visible = section.attr('data-device-only') === device;
      section.toggleClass('is-hidden-by-device', !visible).attr('aria-hidden', visible ? 'false' : 'true');
    });
    card.find('[data-preview-stage]').attr('data-device', device);
    setPreviewBackground(card, device);
  }

  function syncPreviewBackdrop(content, type, value){
    content.removeClass('is-backdrop-none is-backdrop-solid is-backdrop-blur is-backdrop-gradient').addClass('is-backdrop-' + type);
    content.css('--fsd-preview-backdrop', value || 'rgba(0,0,0,0.45)');
  }


  function syncBackdropValue(card){
    const type = card.find('input[name$="[backdrop_type]"]:checked').val() || 'none';
    const builder = card.find('.fsd-backdrop-builder');
    if(!builder.length) return;
    builder.removeClass('is-type-none is-type-solid is-type-blur is-type-gradient').addClass('is-type-' + type);
    const hidden = builder.find('.fsd-backdrop-value');
    let value = hidden.val() || 'rgba(0,0,0,0.45)';
    if(type === 'solid'){
      value = builder.find('.fsd-backdrop-solid-text').val() || '#000000';
    } else if(type === 'gradient'){
      const start = builder.find('.fsd-backdrop-gradient-start-text').val() || '#000000';
      const end = builder.find('.fsd-backdrop-gradient-end-text').val() || '#4b5563';
      const dir = card.find('select[name$="[backdrop_direction_ui]"]').val() || '180deg';
      value = 'linear-gradient(' + dir + ', ' + start + ' 0%, ' + end + ' 100%)';
    } else if(type === 'none'){
      value = 'transparent';
    }
    hidden.val(value);
  }

  function focusToPosition(value, mobile){
    if (mobile) {
      if (value === 'top') return 'center top';
      if (value === 'bottom') return 'center bottom';
      return 'center center';
    }
    if (value === 'left') return 'left center';
    if (value === 'right') return 'right center';
    return 'center center';
  }

  function readabilityBackdrop(value){
    if (value === 'none') return {type:'none', color:'transparent'};
    if (value === 'strong') return {type:'solid', color:'rgba(0,0,0,0.62)'};
    if (value === 'light') return {type:'solid', color:'rgba(255,255,255,0.76)'};
    return {type:'solid', color:'rgba(0,0,0,0.42)'};
  }

  function getCardValue(card, suffix, fallback){
    const checked = card.find('input[name$="[' + suffix + ']"]:checked').val();
    const fieldValue = card.find('input[name$="[' + suffix + ']"], select[name$="[' + suffix + ']"], textarea[name$="[' + suffix + ']"]').first().val();
    return checked || fieldValue || fallback;
  }

  function syncPositionMatrix(matrix){
    const checked = matrix.find('input[type="radio"]:checked');
    if (!checked.length) return;
    matrix.closest('.fsd-position-matrix-field').find('.fsd-position-axis[data-axis="h"]').val(checked.data('h')).trigger('input');
    matrix.closest('.fsd-position-matrix-field').find('.fsd-position-axis[data-axis="v"]').val(checked.data('v')).trigger('input');
  }

  function syncModelVisibility(card){
    const layoutField = card.find('input[name$="[layout_model]"]');
    const layout = card.find('input[name$="[layout_model]"]:checked').val() || layoutField.val() || 'background';
    card.find('.fsd-model-dependent--split').toggle(layout === 'split');
  }

  function syncCardPreview(card) {
    const preview = card.find('.fsd-preview-hero');
    const content = card.find('.fsd-preview-hero__content');
    if (!preview.length || !content.length) return;
    const form = card.closest('.fsd-hero-admin-form');
    const title = $.trim(card.find('input[name$="[title]"]').val() || '') || 'Your Headline Here';
    const subtitle = $.trim(card.find('textarea[name$="[subtitle]"]').val() || '') || "This is a subtitle describing your banner's primary offer or message.";
    const button = $.trim(card.find('input[name$="[button_text]"]').val() || '') || 'Action Button';
    let h = getCardValue(card, 'text_position', 'left');
    const v = getCardValue(card, 'vertical_position', 'center');
    const hm = getCardValue(card, 'text_position_mobile', 'center');
    const vm = getCardValue(card, 'vertical_position_mobile', 'center');
    const layout = getCardValue(card, 'layout_model', 'background');
    const side = getCardValue(card, 'layout_side', 'right');
    const focus = getCardValue(card, 'image_focus', 'center');
    const focusMobile = getCardValue(card, 'image_focus_mobile', 'center');
    const readability = getCardValue(card, 'readability', 'soft');
    const useGlobalStyle = card.find('input[name$="[use_global_style]"]').is(':checked') || card.find('input[name$="[use_global_button_style]"]').is(':checked') || card.find('input[name$="[use_global_banner_style]"]').is(':checked');
    const useGlobalButton = useGlobalStyle && form.data('themeEnabled') == 1;
    const useGlobalBanner = useGlobalStyle && form.data('themeEnabled') == 1;
    card.toggleClass('is-using-global-button', !!useGlobalButton);
    const btnStyle = useGlobalButton ? (form.data('themeButtonStyle') || 'pill') : (card.find('input[name$="[button_style]"]:checked').val() || 'pill');
    const txt = useGlobalButton ? (form.data('themeButtonColor') || '#ffffff') : (card.find('input[name$="[button_text_color]"]').val() || '#ffffff');
    const bg = useGlobalButton ? (form.data('themeButtonBg') || '#8224e3') : (card.find('input[name$="[button_bg_color]"]').val() || '#8224e3');
    const txtH = useGlobalButton ? (form.data('themeButtonHoverColor') || '#ffffff') : (card.find('input[name$="[button_hover_text_color]"]').val() || '#ffffff');
    const bgH = useGlobalButton ? (form.data('themeButtonHoverBg') || '#5b21b6') : (card.find('input[name$="[button_hover_bg_color]"]').val() || '#5b21b6');
    const textColor = useGlobalBanner ? (form.data('themeTextColor') || '#ffffff') : '#ffffff';
    const fontFamily = useGlobalBanner ? (form.data('themeFont') || 'inherit') : 'inherit';
    const radius = parseInt(card.find('input[name$="[radius_top_left]"]').val() || '16', 10);
    const desktopTop = parseInt(card.find('input[name$="[padding_top]"]').val() || '36', 10);
    const desktopRight = parseInt(card.find('input[name$="[padding_right]"]').val() || '36', 10);
    const desktopBottom = parseInt(card.find('input[name$="[padding_bottom]"]').val() || '36', 10);
    const desktopLeft = parseInt(card.find('input[name$="[padding_left]"]').val() || '36', 10);
    const mobileTop = parseInt(card.find('input[name$="[padding_top_mobile]"]').val() || '24', 10);
    const mobileRight = parseInt(card.find('input[name$="[padding_right_mobile]"]').val() || '20', 10);
    const mobileBottom = parseInt(card.find('input[name$="[padding_bottom_mobile]"]').val() || '24', 10);
    const mobileLeft = parseInt(card.find('input[name$="[padding_left_mobile]"]').val() || '20', 10);
    const desktopWidth = parseInt(card.find('input[name$="[content_max_width]"]').val() || '760', 10);
    const mobileWidth = parseInt(card.find('input[name$="[content_max_width_mobile]"]').val() || '100', 10);
    syncBackdropValue(card);
    const readabilityData = readabilityBackdrop(readability);
    const backdropType = readabilityData.type;
    const backdropColor = readabilityData.color;
    preview.removeClass('is-h-left is-h-center is-h-right is-v-top is-v-center is-v-bottom is-hm-left is-hm-center is-hm-right is-vm-top is-vm-center is-vm-bottom is-layout-split is-layout-background is-layout-centered is-image-left is-image-right is-readability-none is-readability-soft is-readability-strong is-readability-light')
      .addClass('is-h-' + h + ' is-v-' + v + ' is-hm-' + hm + ' is-vm-' + vm + ' is-layout-' + layout + ' is-image-' + side + ' is-readability-' + readability);
    preview.css('--fsd-preview-bg-position', focusToPosition(focus, false));
    preview.css('--fsd-preview-bg-position-mobile', focusToPosition(focusMobile, true));
    preview.css('--fsd-preview-button-color', txt);
    preview.css('--fsd-preview-button-bg', bg);
    preview.css('--fsd-preview-button-hover-color', txtH);
    preview.css('--fsd-preview-button-hover-bg', bgH);
    preview.css('--fsd-preview-button-radius', buttonRadiusByStyle(btnStyle));
    preview.css('--fsd-preview-text-color', textColor);
    preview.css('--fsd-preview-font-family', fontFamily);
    preview.css('--fsd-preview-radius', Math.max(0, radius) + 'px');
    preview.css('--fsd-preview-content-width', Math.max(180, Math.min(320, Math.round(desktopWidth * 0.34))) + 'px');
    preview.css('--fsd-preview-content-width-mobile', Math.max(180, Math.min(260, Math.round(mobileWidth * 2.1))) + 'px');
    preview.css('--fsd-preview-pad-top', Math.max(10, Math.min(48, Math.round(desktopTop * 0.45))) + 'px');
    preview.css('--fsd-preview-pad-right', Math.max(10, Math.min(48, Math.round(desktopRight * 0.45))) + 'px');
    preview.css('--fsd-preview-pad-bottom', Math.max(10, Math.min(48, Math.round(desktopBottom * 0.45))) + 'px');
    preview.css('--fsd-preview-pad-left', Math.max(10, Math.min(48, Math.round(desktopLeft * 0.45))) + 'px');
    preview.css('--fsd-preview-pad-top-mobile', Math.max(8, Math.min(42, Math.round(mobileTop * 0.55))) + 'px');
    preview.css('--fsd-preview-pad-right-mobile', Math.max(8, Math.min(42, Math.round(mobileRight * 0.55))) + 'px');
    preview.css('--fsd-preview-pad-bottom-mobile', Math.max(8, Math.min(42, Math.round(mobileBottom * 0.55))) + 'px');
    preview.css('--fsd-preview-pad-left-mobile', Math.max(8, Math.min(42, Math.round(mobileLeft * 0.55))) + 'px');
    card.find('.fsd-preview-title').text(title);
    card.find('.fsd-preview-subtitle').text(subtitle);
    card.find('.fsd-preview-button').text(button);
    syncPreviewBackdrop(content, backdropType, backdropColor);
    syncDeviceMode(card, card.attr('data-active-device') || 'desktop');
  }

  function refreshCardTitle(card) {
    const title = $.trim(card.find('input[name$="[title]"]').val() || '');
    card.find('.fsd-hero-card__title').text(title || 'Novo banner');
  }

  function updateCardIndexes() {
    $('[data-hero-list] [data-hero-card]').each(function(index){
      const card = $(this);
      card.attr('data-index', index);
      card.find('.fsd-card__eyebrow').text('Banner ' + (index + 1));
      card.find('[name]').each(function(){
        const name = $(this).attr('name');
        if (name) $(this).attr('name', name.replace(/hero\[\d+\]/g, 'hero[' + index + ']'));
      });
    });
  }

  const heroPresets = {
    'balanced-left': { text_position:'left', vertical_position:'center', text_position_mobile:'center', vertical_position_mobile:'center', padding_top:36, padding_right:36, padding_bottom:36, padding_left:36, padding_top_mobile:24, padding_right_mobile:20, padding_bottom_mobile:24, padding_left_mobile:20, radius_top_left:32, radius_top_right:32, radius_bottom_right:32, radius_bottom_left:32, backdrop_type:'none', backdrop_color:'rgba(0,0,0,0.45)', transition:'fade', delay:5000, content_max_width:680, content_max_width_mobile:100, button_style:'pill', button_text_color:'#ffffff', button_bg_color:'#8224e3', button_hover_text_color:'#ffffff', button_hover_bg_color:'#5b21b6' },
    'balanced-right': { text_position:'right', vertical_position:'center', text_position_mobile:'center', vertical_position_mobile:'center', padding_top:36, padding_right:48, padding_bottom:36, padding_left:36, padding_top_mobile:24, padding_right_mobile:20, padding_bottom_mobile:24, padding_left_mobile:20, radius_top_left:32, radius_top_right:32, radius_bottom_right:32, radius_bottom_left:32, backdrop_type:'none', backdrop_color:'rgba(0,0,0,0.45)', transition:'fade', delay:5000, content_max_width:680, content_max_width_mobile:100, button_style:'pill', button_text_color:'#ffffff', button_bg_color:'#8224e3', button_hover_text_color:'#ffffff', button_hover_bg_color:'#5b21b6' },
    'center-focus': { text_position:'center', vertical_position:'center', text_position_mobile:'center', vertical_position_mobile:'center', padding_top:48, padding_right:48, padding_bottom:48, padding_left:48, padding_top_mobile:28, padding_right_mobile:22, padding_bottom_mobile:28, padding_left_mobile:22, radius_top_left:28, radius_top_right:28, radius_bottom_right:28, radius_bottom_left:28, backdrop_type:'gradient', backdrop_color:'linear-gradient(180deg, rgba(0,0,0,0.14) 0%, rgba(0,0,0,0.32) 100%)', transition:'fade', delay:5000, content_max_width:760, content_max_width_mobile:100, button_style:'pill', button_text_color:'#ffffff', button_bg_color:'#005891', button_hover_text_color:'#ffffff', button_hover_bg_color:'#0a3f66' },
    'campaign-cta': { text_position:'right', vertical_position:'center', text_position_mobile:'center', vertical_position_mobile:'center', padding_top:48, padding_right:56, padding_bottom:48, padding_left:40, padding_top_mobile:24, padding_right_mobile:20, padding_bottom_mobile:24, padding_left_mobile:20, radius_top_left:36, radius_top_right:36, radius_bottom_right:36, radius_bottom_left:36, backdrop_type:'solid', backdrop_color:'rgba(0,0,0,0.26)', transition:'zoom', delay:4500, content_max_width:560, content_max_width_mobile:100, button_style:'rounded', button_text_color:'#ffffff', button_bg_color:'#8224e3', button_hover_text_color:'#ffffff', button_hover_bg_color:'#5b21b6' }
  };

  function setCardField(card, suffix, value) {
    const field = card.find('[name$="[' + suffix + ']"]');
    if (!field.length) return;
    const type = field.first().attr('type');
    if (type === 'radio') field.filter('[value="' + value + '"]').prop('checked', true).trigger('change');
    else if (type === 'checkbox') field.prop('checked', !!value).trigger('change');
    else field.val(value).trigger('input').trigger('change');
  }

  function applyHeroPreset(card, presetKey) {
    const preset = heroPresets[presetKey];
    if (!preset) return;
    Object.keys(preset).forEach(function(key){ setCardField(card, key, preset[key]); });
    syncRadiusMaster(card);
    syncBackdropValue(card);
    card.find('.fsd-position-matrix').each(function(){ syncPositionMatrix($(this)); });
    syncModelVisibility(card);
    syncQuadMaster(card, 'desktop-spacing');
    syncQuadMaster(card, 'mobile-spacing');
  }

  function createCardFromTemplate() {
    const list = $('[data-hero-list]');
    if(list.find('[data-hero-card]').length >= 3){ alert('Você pode configurar no máximo 3 banners.'); return $(); }
    const index = list.find('[data-hero-card]').length;
    const position = index + 1;
    const template = $('#tmpl-fsd-hero-card').html().replace(/__INDEX__/g, index).replace(/__POSITION__/g, position);
    const card = $(template);
    list.append(card);
    $(document).on('change','input[name$="[backdrop_type]"], select[name$="[backdrop_direction_ui]"]',function(){
    const card=$(this).closest('[data-hero-card]');
    syncBackdropValue(card);
    syncCardPreview(card);
  });

  updateCardIndexes();
    initializeCard(card);
    return card;
  }

  function syncQuadMaster(card, group){
    const master = card.find('.fsd-quad-master[data-target-group="'+group+'"]');
    const linked = card.find('.fsd-link-quad[data-target-group="'+group+'"]').is(':checked');
    const inputs = card.find('[data-quad-group="'+group+'"]');
    const hiddenTop = card.find('[data-quad-hidden="'+group+'-top"]');
    if(!master.length) return;
    if(linked){ inputs.val(master.val()); }
    hiddenTop.val(master.val());
  }

  function syncRadiusMaster(card){
    const vals = [
      card.find('input[name$="[radius_top_left]"]').val(),
      card.find('input[name$="[radius_top_right]"]').val(),
      card.find('input[name$="[radius_bottom_right]"]').val(),
      card.find('input[name$="[radius_bottom_left]"]').val()
    ];
    const unified = vals.every(function(v){ return v === vals[0]; });
    if(unified){ card.find('.fsd-radius-master').val(vals[0]); }
  }

  function initializeCard(card){
    refreshCardTitle(card);
    card.attr('data-active-device', 'desktop');
    syncRadiusMaster(card);
    syncBackdropValue(card);
    card.find('.fsd-position-matrix').each(function(){ syncPositionMatrix($(this)); });
    syncModelVisibility(card);
    syncQuadMaster(card, 'desktop-spacing');
    syncQuadMaster(card, 'mobile-spacing');
    syncCardPreview(card);
  }

  $(document).on('change', '.fsd-position-matrix input[type="radio"]', function(){
    const matrix = $(this).closest('.fsd-position-matrix');
    syncPositionMatrix(matrix);
    syncCardPreview($(this).closest('[data-hero-card]'));
  });

  $(document).on('click','.fsd-open-media',function(e){
    e.preventDefault();
    const field=$(this).closest('.fsd-media-field');
    if(mediaFrame){mediaFrame.off('select');}
    mediaFrame=wp.media({title:'Selecionar imagem',button:{text:'Usar imagem'},multiple:false,library:{type:'image'}});
    mediaFrame.on('select',function(){
      const attachment=mediaFrame.state().get('selection').first().toJSON();
      field.find('.fsd-media-id').val(attachment.id);
      field.find('.fsd-media-preview').html('<img src="'+attachment.url+'" alt="">');
      const card = field.closest('[data-hero-card]');
      const preview = card.find('.fsd-preview-hero');
      if (field.find('.fsd-media-id').attr('name').indexOf('[mobile_image_id]') !== -1) preview.attr('data-mobile-bg', attachment.url);
      else preview.attr('data-desktop-bg', attachment.url);
      syncCardPreview(card);
    });
    mediaFrame.open();
  });

  $(document).on('click','.fsd-clear-media',function(e){
    e.preventDefault();
    const field=$(this).closest('.fsd-media-field');
    field.find('.fsd-media-id').val('0');
    field.find('.fsd-media-preview').html('<span>Sem imagem selecionada</span>');
    const card = field.closest('[data-hero-card]');
    const preview = card.find('.fsd-preview-hero');
    if (field.find('.fsd-media-id').attr('name').indexOf('[mobile_image_id]') !== -1) preview.attr('data-mobile-bg', '');
    else preview.attr('data-desktop-bg', '');
    syncCardPreview(card);
  });

  $(document).on('input change','.fsd-color-input',function(){
    const targetName=$(this).data('target-name');
    if(targetName){
      $(this).closest('label').find('input[name="'+targetName+'"]').val($(this).val()).trigger('input');
    } else {
      $(this).siblings('.fsd-color-text').val($(this).val()).trigger('input');
    }
  });

  $(document).on('input','.fsd-color-text',function(){
    const value=$(this).val();
    if(/^#[0-9a-fA-F]{6}$/.test(value)) $(this).siblings('.fsd-color-input').val(value);
    const card=$(this).closest('[data-hero-card]');
    syncBackdropValue(card);
    syncCardPreview(card);
  });

  $(document).on('click','.fsd-preview-toggle',function(e){
    e.preventDefault();
    const toggle=$(this), card=toggle.closest('[data-hero-card]');
    toggle.addClass('is-active').siblings().removeClass('is-active');
    syncDeviceMode(card, toggle.data('device'));
  });

  $(document).on('click','.fsd-style-tab',function(e){
    e.preventDefault();
    const tab=$(this), card=tab.closest('[data-hero-card]');
    tab.addClass('is-active').siblings().removeClass('is-active');
    card.find('[data-style-panel]').removeClass('is-active');
    card.find('[data-style-panel="'+tab.data('style-target')+'"]').addClass('is-active');
  });

  $(document).on('input','.fsd-radius-master',function(){
    const card=$(this).closest('[data-hero-card]');
    if(card.find('.fsd-link-radius').is(':checked')){
      const v=$(this).val();
      ['radius_top_left','radius_top_right','radius_bottom_right','radius_bottom_left'].forEach(function(name){ card.find('input[name$="['+name+']"]').val(v); });
    }
    syncCardPreview(card);
  });

  $(document).on('change','.fsd-link-radius',function(){
    const card=$(this).closest('[data-hero-card]');
    if($(this).is(':checked')) card.find('.fsd-radius-master').trigger('input');
  });

  $(document).on('input','.fsd-quad-master',function(){
    const card=$(this).closest('[data-hero-card]');
    syncQuadMaster(card, $(this).data('target-group'));
    syncCardPreview(card);
  });

  $(document).on('change','.fsd-link-quad',function(){
    const card=$(this).closest('[data-hero-card]');
    syncQuadMaster(card, $(this).data('target-group'));
    syncCardPreview(card);
  });

  $(document).on('input','[data-quad-group]',function(){
    const card=$(this).closest('[data-hero-card]');
    const group=$(this).data('quad-group');
    if(!card.find('.fsd-link-quad[data-target-group="'+group+'"]').is(':checked') && /top/.test($(this).attr('name') || '')) {
      card.find('.fsd-quad-master[data-target-group="'+group+'"]').val($(this).val());
    }
    syncCardPreview(card);
  });

  $(document).on('click','.fsd-apply-preset',function(e){
    e.preventDefault();
    if(!window.confirm('Aplicar este preset irá substituir os principais ajustes atuais deste banner. Deseja continuar?')) return;
    const card=$(this).closest('[data-hero-card]');
    applyHeroPreset(card,$(this).data('preset'));
    syncCardPreview(card);
  });

  $(document).on('click','.fsd-add-banner',function(e){
    e.preventDefault();
    const card=createCardFromTemplate();
    if(card && card.length) $('html, body').animate({scrollTop: card.offset().top - 40}, 250);
  });

  $(document).on('click','.fsd-remove-card',function(e){
    e.preventDefault();
    const list=$('[data-hero-list]');
    if(list.find('[data-hero-card]').length<=1){ alert('O Hero precisa de pelo menos um banner configurável.'); return; }
    $(this).closest('[data-hero-card]').remove();
    updateCardIndexes();
  });

  $(document).on('click','.fsd-duplicate-card',function(e){
    e.preventDefault();
    const list=$('[data-hero-list]');
    if(list.find('[data-hero-card]').length >= 3){ alert('Você pode configurar no máximo 3 banners.'); return; }
    const source=$(this).closest('[data-hero-card]');
    const clone=source.clone(true,true);
    source.after(clone);
    updateCardIndexes();
    initializeCard(clone);
  });

  $(document).on('click','.fsd-move-card',function(e){
    e.preventDefault();
    const card=$(this).closest('[data-hero-card]');
    const direction=$(this).data('direction');
    if(direction==='up'){ const prev=card.prev('[data-hero-card]'); if(prev.length) prev.before(card); }
    else { const next=card.next('[data-hero-card]'); if(next.length) next.after(card); }
    updateCardIndexes();
  });

  $(document).on('toggle','.fsd-editor-section',function(){
    $(this).toggleClass('is-open', this.open);
  });

  $(document).on('input change','[data-hero-card] input, [data-hero-card] textarea, [data-hero-card] select',function(){
    const card=$(this).closest('[data-hero-card]');
    refreshCardTitle(card);
    syncCardPreview(card);
  });

  updateCardIndexes();
  $('[data-hero-card]').each(function(){ initializeCard($(this)); });


  function initTopBarLivePreview() {
    if (!window.FSDPreview || typeof window.FSDPreview.bindTopBar !== 'function') return;
    const form = $('form').has('button[name="flashsite_design_save_topbar"]');
    if (!form.length) return;
    window.FSDPreview.bindTopBar(form);
  }

  function initThemeLivePreview() {
    if (!window.FSDPreview || typeof window.FSDPreview.bindTheme !== 'function') return;
    const form = $('form').has('button[name="flashsite_design_save_theme_mode"]');
    if (!form.length) return;
    window.FSDPreview.bindTheme(form);
  }

  initTopBarLivePreview();
  initThemeLivePreview();

});

