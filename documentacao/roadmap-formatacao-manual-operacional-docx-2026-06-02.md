# Roadmap - Formatacao academica do Manual Operacional PEI (DOCX)

## Contexto

O arquivo `documentacao/manual-operacional-planejamento-estrategico-v1.docx` deve receber uma pagina de capa, sumario classico na segunda pagina, formatacao academica e estilos de titulo aplicados para permitir geracao/atualizacao de sumario no Word.

## Diagnostico

- O documento DOCX existe e possui estrutura Office valida.
- Ha um arquivo Markdown homonimo (`manual-operacional-planejamento-estrategico-v1.md`) com a estrutura textual do manual em cabecalhos, o que permite regenerar o DOCX com estilos sem alterar o conteudo validado.
- O repositorio possui alteracoes previas nao relacionadas; a intervencao deve se limitar ao manual, ao backup, a este roadmap e ao registro em `gemini/interventions.txt`.

## Plano de Execucao

1. Criar backup preventivo do DOCX original antes de qualquer sobrescrita.
2. Construir uma versao intermediaria do Markdown sem o sumario manual, preservando os demais capitulos.
3. Gerar DOCX com capa, quebra de pagina, sumario automatico classico na segunda pagina e estilos `Title`, `Heading 1`, `Heading 2` e `Heading 3`.
4. Aplicar ajustes de estilo academico no pacote OOXML: margens, fonte, espacamento, hierarquia visual, numeracao de paginas e campos de sumario.
5. Validar a integridade do DOCX resultante e confirmar a presenca dos estilos de titulo e do sumario.

## Rollback

Restaurar o arquivo original a partir do backup preventivo `documentacao/manual-operacional-planejamento-estrategico-v1.original-2026-06-02-1710.bak.docx`, substituindo `documentacao/manual-operacional-planejamento-estrategico-v1.docx` pela copia correspondente.
