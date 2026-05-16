# FlashSite Design 0.4.3

## Estabilização do Hero
- remove altura mínima fixa do Hero e das camadas internas
- separa media, alinhamento e conteúdo no renderer
- garante herança consistente de alinhamento entre título, subtítulo e botão
- mantém backdrop estritamente opcional, sem máscara automática por defeito
- remove referência residual a `banner_max_width`
- retira o campo administrativo de `overlay_opacity`, que já não fazia parte do comportamento final

## Objetivo
Fechar a linha de regressões de layout antes de qualquer refactor maior de UX do painel.
