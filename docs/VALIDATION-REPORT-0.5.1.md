# FlashSite Design 0.5.1 — Validation Report

## Validações executadas
- lint PHP nos ficheiros principais do plugin
- revisão estrutural do painel Hero Banners
- validação da lógica JS de adição, duplicação, remoção e reordenação
- validação da reindexação dos names `hero[n][campo]`
- empacotamento final do ZIP

## Risco conhecido
A gestão de banners adicionada nesta versão é administrativa e depende de JavaScript no wp-admin. Sem JS, o formulário continua funcional para edição e gravação dos banners já existentes, mas sem as novas ações dinâmicas.
