# FlashSite Design 1.0.0

## Editor simplificado de banners
- Renomeia o módulo principal para **Banners do Site**.
- Atualiza a linguagem do painel para usuários finais, evitando referência a Elementor ou termos técnicos desnecessários.
- Limita o gerenciamento a até 3 banners, com publicação/ocultação simples por checkbox.
- Substitui a lógica de status técnico por `Mostrar no site`.

## Modelos visuais
- Adiciona três modelos guiados:
  - Modelo A: texto + imagem lateral.
  - Modelo B: imagem de fundo + texto sobreposto.
  - Modelo C: texto centralizado.
- Adiciona controles visuais compactos para:
  - posição do texto no computador;
  - posição do texto no celular;
  - foco da imagem no computador;
  - foco da imagem no celular;
  - lado da imagem no modelo lateral.

## Usabilidade
- Reorganiza cada banner em blocos didáticos: texto, modelo, imagens, posição/leitura e ajustes avançados.
- Oculta controles técnicos em **Ajustes avançados**.
- Adiciona limite visual e operacional para evitar criação de mais de 3 banners.
- Mantém versões separadas de imagem para computador e celular.

## Front-end
- O renderizador passa a aceitar classes de modelo, lado da imagem, legibilidade e foco de imagem.
- Adiciona suporte visual inicial ao modelo lateral sem alterar o shortcode `[flashsite_hero_banners]`.
- Permite até 3 banners ativos no carrossel.

## Compatibilidade
- Mantém o mesmo slug, opções principais, shortcode e estrutura de dados base.
- Atualiza a versão para 1.0.0 como evolução direta da versão 0.9.2.1.
