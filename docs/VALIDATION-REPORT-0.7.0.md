# FlashSite Design 0.7.0 — Validation Report

## Verificações executadas
- PHP lint em `flashsite-design.php`
- PHP lint em `includes/class-flashsite-design-plugin.php`
- Node syntax check em `assets/admin/js/fsd-admin.js`
- revisão estrutural do CSS admin
- empacotamento limpo do ZIP final

## Riscos monitorizados
- manutenção de compatibilidade com nomes de campos existentes
- não alteração do renderer/frontend do Hero
- isolamento do refactor à camada administrativa

## Pontos para teste manual no WordPress
- comportamento do preview sticky em resoluções menores
- troca Desktop/Tablet ↔ Mobile por banner
- sincronização do preview com backdrop e botão
- raio unificado + modo avançado
- espaçamento unificado por device
- duplicar, mover, remover e adicionar banners sem perda de estado
