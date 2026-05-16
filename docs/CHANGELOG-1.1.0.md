# FlashSite Design 1.1.0

## Alterações
- Adiciona `FSD_UpdateChecker` (`includes/class-fsd-update-checker.php`) para verificação automática de actualizações via WP Admin.
- O WP Admin passa a exibir o aviso nativo de "Actualização Disponível" quando uma versão mais recente estiver publicada no GitHub Releases.
- A verificação respeita a transient cache nativa do WordPress: no máximo uma chamada HTTP a cada 12 horas por instalação.
- Falhas de rede ou endpoint indisponível são tratadas silenciosamente, sem erros no frontend.
- Adiciona ficheiro `update-server/flashsite-design.json` ao repositório como manifesto de versão servido via `raw.githubusercontent.com`.

## Validação
- `php -l` executado em todos os arquivos PHP do plugin.
- Confirmado que `FLASHSITE_DESIGN_VERSION` e o header `Version:` estão em sync em `1.1.0`.
