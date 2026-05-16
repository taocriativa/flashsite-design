# Validation Report 0.9.2

## Escopo validado
Validação prévia estrutural e de integração para a versão 0.9.2 do FlashSite Design.

## Verificações executadas
- lint PHP em todos os arquivos do plugin;
- revisão de compatibilidade das novas chaves de options com fallback para chaves legadas;
- revisão de handlers admin com `check_admin_referer` e `manage_options`;
- revisão de fluxo Hero para garantir no máximo 1 item com status `active`;
- revisão de persistência da Barra de Aviso e Estilo Padrão com normalização por engine;
- revisão de uninstall para cobertura de chaves novas e antigas.

## Resultado
- sem erros de sintaxe PHP;
- sem dependências novas externas;
- sem quebra intencional dos shortcodes existentes;
- migração prevista para instalações que ainda persistem dados em `flashsite_design_*`.

## Riscos residuais conhecidos
- o preview administrativo continua dependente de JS e pode exigir validação prática em ambiente WordPress + Elementor;
- a mudança de defaults para herança do estilo padrão no Hero deve ser confirmada em teste visual de integração;
- a consolidação das labels administrativas exige verificação final de UX em ambiente real.

## Recomendação
Executar teste prático em instalação de staging com:
1. upgrade direto da 0.9.1 para 0.9.2;
2. criação/edição de campanha Hero;
3. alternância de estados `draft/active/inactive`;
4. validação de unicidade da campanha ativa;
5. validação de render frontend e preview Elementor;
6. validação da Barra de Aviso ativa/inativa.
