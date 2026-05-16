# FlashSite Design 0.9.2

## Objetivo
Consolidar o FlashSite Design como painel operacional controlado para manutenção de campanhas Hero e Barra de Aviso, sem ampliar o escopo funcional do plugin.

## Alterações principais
- introdução de uma Design Engine mais central para normalização de tema, Top Bar e Hero;
- criação de helpers de preview e render dedicados para reduzir acoplamento no arquivo principal;
- normalização de options para:
  - `fsd_hero_banners`
  - `fsd_top_bar`
  - `fsd_theme_settings`
- migração automática a partir das chaves legadas `flashsite_design_*`;
- Hero passa a operar com estados `draft`, `active` e `inactive`;
- garantia de apenas 1 campanha Hero ativa por vez;
- filtro de persistência para evitar salvar campanhas sem conteúdo mínimo (`title` ou `image_id`);
- consolidação do conceito de `use_global_style` para herança do estilo padrão;
- simplificação da Barra de Aviso para texto, link opcional e duas cores essenciais;
- atualização de labels administrativas para:
  - Campanhas (Hero)
  - Barra de Aviso
  - Estilo Padrão
- manutenção da política conservadora de uninstall, agora cobrindo chaves novas e legadas.

## Compatibilidade
- mantém exigência de FlashSite Core 2.0.1+;
- preserva leitura de dados legados durante migração;
- mantém shortcodes existentes:
  - `[flashsite_hero_banners]`
  - `[flashsite_top_bar]`

## Observações
Esta versão não introduz novos módulos visuais. O foco é robustez operacional, consistência e redução de conflito para uso pós-entrega pelo cliente final.
