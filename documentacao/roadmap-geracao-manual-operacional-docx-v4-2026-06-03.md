# Roadmap - Geracao do Manual Operacional PEI em DOCX v4

## Contexto

O arquivo `documentacao/manual-operacional-planejamento-estrategico-v1.md` recebeu uma nova versao textual, declarada como versao 3.0. A solicitacao e gerar um novo documento Word com capa, sumario classico na segunda pagina, estilos de titulo reconhecidos pelo Word e acabamento academico, sob o nome `documentacao/manual-operacional-planejamento-estrategico-v4.docx`.

## Diagnostico

- A fonte Markdown atual possui capitulos e secoes estruturados em cabecalhos.
- O Markdown inclui um sumario manual que deve ser substituido, no DOCX, por sumario automatico baseado em estilos de titulo.
- O arquivo DOCX v3 existente foi modificado posteriormente e deve ser preservado integralmente.
- Existem alteracoes nao relacionadas na arvore de trabalho; a intervencao deve ficar restrita ao DOCX v4, a este roadmap e ao registro em `gemini/interventions.txt`.

## Plano de Execucao

1. Usar o Markdown atual como fonte de verdade, removendo apenas o sumario manual na fonte temporaria.
2. Gerar o corpo do DOCX com sumario automatico e estilos de titulo.
3. Inserir capa institucional antes do sumario, garantindo que o sumario inicie na segunda pagina.
4. Aplicar margens, tipografia academica, hierarquia visual e rodape numerado.
5. Atualizar os campos pelo Microsoft Word para preencher as paginas do sumario.
6. Validar o resultado por extracao de texto e inspecao OOXML.

## Rollback

Como o DOCX v4 e um arquivo novo, o rollback consiste em remover `documentacao/manual-operacional-planejamento-estrategico-v4.docx` e, se necessario, remover este roadmap e a linha correspondente em `gemini/interventions.txt`.
