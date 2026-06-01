# Ícones da Agenda 2030 (ODS)

Este diretório armazena os ícones oficiais dos **18** Objetivos de Desenvolvimento
Sustentável (ODS) usados pelo componente `<x-ods-badge>` — os 17 da ONU mais o
**ODS 18 (Igualdade Étnico-Racial)**, adição nacional brasileira.

Os ícones atuais foram obtidos em **pt-BR** do portal oficial do Governo Federal
**https://odsbrasil.gov.br/** (padrão de origem: `/content/ods/N.png`).

## Como obter/atualizar os ícones

1. Portal oficial brasileiro (pt-BR, 18 ODS): **https://odsbrasil.gov.br/**
   (alternativa ONU, 17 ODS em inglês: https://www.un.org/sustainabledevelopment/news/communications-material/)

2. Baixe os ícones individuais dos ODS.

3. Renomeie cada arquivo seguindo **exatamente** este padrão e coloque-os aqui:

```
public/img/ods/
  ods-01.png   → ODS 1  · Erradicação da Pobreza
  ods-02.png   → ODS 2  · Fome Zero
  ods-03.png   → ODS 3  · Saúde e Bem-Estar
  ods-04.png   → ODS 4  · Educação de Qualidade
  ods-05.png   → ODS 5  · Igualdade de Gênero
  ods-06.png   → ODS 6  · Água Potável e Saneamento
  ods-07.png   → ODS 7  · Energia Limpa e Acessível
  ods-08.png   → ODS 8  · Trabalho Decente e Crescimento Econômico
  ods-09.png   → ODS 9  · Indústria, Inovação e Infraestrutura
  ods-10.png   → ODS 10 · Redução das Desigualdades
  ods-11.png   → ODS 11 · Cidades e Comunidades Sustentáveis
  ods-12.png   → ODS 12 · Consumo e Produção Responsáveis
  ods-13.png   → ODS 13 · Ação Contra a Mudança Global do Clima
  ods-14.png   → ODS 14 · Vida na Água
  ods-15.png   → ODS 15 · Vida Terrestre
  ods-16.png   → ODS 16 · Paz, Justiça e Instituições Eficazes
  ods-17.png   → ODS 17 · Parcerias e Meios de Implementação
  ods-18.png   → ODS 18 · Igualdade Étnico-Racial (adição nacional brasileira)
```

## Comportamento sem os ícones

**O sistema funciona normalmente mesmo sem os arquivos.** O componente
`<x-ods-badge>` detecta automaticamente a ausência do `.png` e exibe um
**badge colorido** com o número do ODS, usando a cor oficial da ONU como
fallback. Assim que os arquivos forem adicionados, os ícones reais passam
a ser exibidos sem necessidade de qualquer alteração no código.

## Recomendações de formato

- Formato: **PNG** com fundo (a versão quadrada colorida oficial).
- Dimensão sugerida: **128×128 px** ou superior (são redimensionados via CSS).
- Mantenha proporção quadrada (1:1).
