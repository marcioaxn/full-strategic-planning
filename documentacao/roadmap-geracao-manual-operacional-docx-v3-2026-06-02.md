# Roadmap - Geracao do Manual Operacional PEI em DOCX v3

## Contexto

O arquivo `documentacao/manual-operacional-planejamento-estrategico-v1.md` foi substituido por uma nova versao textual do Manual Operacional. A solicitacao atual e gerar um novo DOCX, mantendo o mesmo padrao anterior de capa, sumario classico na segunda pagina, estilos de titulo reconhecidos pelo Word e acabamento academico, com nome final `documentacao/manual-operacional-planejamento-estrategico-v3.docx`.

## Diagnostico

- A nova fonte Markdown existe e possui cabecalhos estruturados para capitulos e secoes.
- O Markdown contem um sumario manual, adequado para leitura em Markdown, mas inadequado para o DOCX final porque o Word deve usar um sumario automatico baseado em estilos de titulo.
- Ha alteracoes previas nao relacionadas no repositorio; a intervencao deve ficar restrita ao novo DOCX, a este roadmap e ao registro em `gemini/interventions.txt`.

## Plano de Execucao

1. Usar `documentacao/manual-operacional-planejamento-estrategico-v1.md` como fonte de verdade.
2. Criar uma fonte temporaria removendo apenas o bloco de sumario manual e rebaixando a hierarquia para que o titulo principal fique reservado a capa.
3. Gerar o DOCX v3 via Pandoc com sumario automatico.
4. Aplicar pos-processamento OOXML para capa institucional, margens, estilos academicos, estilos `Heading/Titulo`, sumario e rodape com numeracao.
5. Atualizar os campos do Word para preencher o sumario com paginas.
6. Validar o DOCX por extracao de texto e inspecao dos estilos internos.

## Rollback

Como o arquivo v3 sera novo, o rollback consiste em remover `documentacao/manual-operacional-planejamento-estrategico-v3.docx` e, se necessario, remover este roadmap e a linha correspondente em `gemini/interventions.txt`.
