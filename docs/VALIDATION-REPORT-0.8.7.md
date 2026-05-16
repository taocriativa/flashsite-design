# FlashSite Design 0.8.7 — Validation Report

## Pré-validação executada
- `php -l` em `flashsite-design.php`
- `php -l` em `includes/class-flashsite-design-plugin.php`
- revisão do empacotamento com raiz `flashsite-design/`
- revisão manual do fluxo do Hero no admin

## Pontos para validação prática
1. Hero inicia mais leve visualmente, com menos scroll inicial.
2. Estilo abre por defeito e fica mais acessível.
3. Layout e Espaçamento interno deixam de poluir a leitura inicial do card.
4. Chip de shortcode no header deixa de quebrar de forma agressiva.
5. Shell global continua consistente entre Hero, Top Bar e Tema Visual.

## Risco conhecido
A classe principal continua concentrada. Não foi tratada nesta rodada.
