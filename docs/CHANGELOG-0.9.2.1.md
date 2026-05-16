# FlashSite Design — CHANGELOG 0.9.2.1

## Correção crítica
- Corrigido fatal error no admin causado por chamada ao método inexistente `getThemeSettings()` em `FlashSite\\Design\\Plugin`.
- Restaurado o carregamento das páginas **Campanhas (Hero)**, **Barra de Aviso** e **Estilo Padrão**.

## Ajuste aplicado
- Reintroduzido método interno `getThemeSettings()` com leitura compatível de:
  - `fsd_theme_settings`
  - `flashsite_design_theme_mode` (legado)
- Normalização central via `DesignEngine::normalizeTheme()`.

## Impacto
- Não altera o escopo funcional da 0.9.2.
- Atua como hotfix de estabilização para integração e testes reais.
