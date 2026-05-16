# FlashSite Design 0.8.3 — Validation Report

## Pré-testes executados
- lint PHP em `flashsite-design.php`
- lint PHP em `includes/class-flashsite-design-plugin.php`
- revisão manual do fluxo de persistência do Hero após mudança do autoplay para nível global
- revisão manual do scoping de assets admin

## Validação esperada em ambiente WordPress
1. abrir Hero, Top Bar e Tema Visual e confirmar alinhamento lateral comum
2. verificar se os notices aparecem abaixo do header em todas as telas
3. validar se o autoplay global do Hero grava e reflete no frontend
4. confirmar que banners antigos continuam editáveis sem erro
5. confirmar que Top Bar e Tema Visual permanecem salvando normalmente
6. confirmar que assets do plugin não carregam noutras telas do admin

## Risco residual conhecido
- a classe principal continua concentrando múltiplas responsabilidades
- ainda pode haver ajustes finos de densidade e altura dos cards do Hero após teste prático
- integração Elementor ainda precisa hardening dedicado em rodada posterior
