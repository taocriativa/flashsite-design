# FlashSite Design 0.4.3 — Validation Report

## Pré-testes executados
- PHP lint em todos os ficheiros PHP
- inspeção estrutural do renderer do Hero
- verificação de assets frontend do Hero
- empacotamento limpo do plugin

## Pontos validados
- versão do plugin atualizada para 0.4.3
- Hero sem `min-height` imposto pelo plugin
- separação entre `.fsd-hero__media`, `.fsd-hero__align` e `.fsd-hero__content`
- alinhamento horizontal/vertical aplicado na camada correta
- ausência de overlay automático quando `backdrop_type = none`
- remoção da referência residual a `banner_max_width`
- remoção do campo administrativo de `overlay_opacity`

## Observação
Os pré-testes aqui realizados são estruturais e estáticos. A validação final visual continua a depender de teste real em WordPress/Elementor com conteúdo do projeto.
