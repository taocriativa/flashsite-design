# Validation Report — FlashSite Design 1.0.0

## Validações executadas
- `php -l` executado em todos os arquivos PHP do plugin.
- `node --check` executado nos arquivos JS administrativos.
- Estrutura do plugin preservada com mesmo diretório `flashsite-design`.
- Shortcode `[flashsite_hero_banners]` preservado.

## Resultado
- Nenhum erro de sintaxe PHP detectado.
- Nenhum erro de sintaxe JS detectado.

## Pontos para teste manual no WordPress
1. Substituir a versão anterior pelo ZIP 1.0.0.
2. Confirmar que o WordPress reconhece como atualização do FlashSite Design.
3. Validar abertura do menu **FlashSite Design > Banners do Site**.
4. Criar até 3 banners e confirmar bloqueio ao tentar adicionar/duplicar além do limite.
5. Publicar 2 ou 3 banners e validar dots/carrossel no front-end.
6. Testar Modelo A, B e C no desktop e no mobile.
7. Validar foco da imagem em computador e celular.
8. Validar se o texto fica legível com as opções Desligado, Suave, Forte e Claro.
9. Confirmar que a Barra de Aviso e o Estilo Visual continuam funcionando.

## Observação
A validação foi feita em ambiente de arquivos, sem execução dentro de uma instalação WordPress real. O teste visual final deve ser feito no painel do WordPress com imagens reais e shortcode aplicado em página Elementor/WordPress.
