# FlashSite Design 0.8.3 — Rodada 1

## Objetivo
Consolidar shell administrativo, reduzir inconsistências visuais entre módulos e iniciar a revisão estrutural do Hero sem alterar o contrato funcional do plugin.

## Alterações aplicadas
- criação de shell administrativo unificado para Hero, Top Bar e Tema Visual
- reposicionamento consistente de notices abaixo do header
- padronização adicional de header, grid e sticky actions
- redução de densidade visual com ajustes de spacing, cards e inputs
- melhoria da proporção do preview desktop/mobile do Hero
- autoplay movido para configuração global do módulo Hero
- remoção do autoplay por banner
- enqueue de assets administrativos limitado às páginas do plugin
- ajuste de nomenclatura de Tema Visual para reduzir ambiguidade

## Mantido
- storage principal de Hero, Top Bar e Tema Visual
- shortcode do Hero
- shortcode da Top Bar
- compatibilidade com FlashSite Core
- compatibilidade com frontend existente

## Observação
Esta versão continua focada em consolidação administrativa. Não inclui ainda decomposição completa da classe monolítica nem hardening específico de bridge com Elementor.
