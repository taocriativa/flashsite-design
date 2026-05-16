# FlashSite Design 0.7.3 — Changelog

## Objetivo
Refinar a coerência do editor Hero sem tocar na base consolidada do frontend.

## Ajustes
- reordenação do editor para manter **Estilo** como secção fixa do banner, junto de **Conteúdo**
- **Layout** e **Espaçamento interno** permanecem como secções responsivas por dispositivo
- preview sticky com `top` maior para evitar o efeito de “colar” ao topo no scroll
- preview desktop com canvas mais panorâmico e proporção mais próxima do banner real
- preview admin passa a refletir melhor:
  - border radius do banner
  - padding desktop/mobile
  - largura máxima do conteúdo
- aviso de sucesso do guardado movido para fora do cabeçalho principal do módulo

## Compatibilidade
- sem alteração de schema
- sem alteração de shortcode
- sem alteração de renderer frontend
