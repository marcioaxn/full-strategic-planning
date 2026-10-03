<?php

namespace App\Services\Reports\RelatorioGestao;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Writer\Word2007;

/**
 * Escreve o Relatório de Gestão em .docx.
 *
 * Consome a MESMA estrutura que alimenta o PDF (EstruturaRelatorioGestao).
 * Renderizador que monta o próprio conteúdo divergiria do PDF na primeira
 * manutenção, e o cliente receberia dois documentos que não dizem o mesmo.
 *
 * ⚠️ PDF e DOCX não são pixel a pixel iguais, e não há como serem: são motores
 * de layout diferentes. O que se garante é mesmo conteúdo, mesma estrutura,
 * mesma paleta e mesma hierarquia tipográfica.
 *
 * O DOCX existe para ser EDITADO: é nele que a unidade completa as seções que
 * vêm de Tesouro Gerencial, SIAPE e Comprasnet. Se fosse só para ler, o PDF
 * bastaria.
 */
class RenderizadorDocx
{
    /** Paleta medida no modelo oficial (amostragem de pixel da página 27). */
    private const VERDE = '54B347';

    private const TEXTO = '2C2E35';

    private const CINZA = '95969A';

    private const CINZA_ESCURO = '595959';

    private const AMARELO = 'EDC009';

    private const AZUL = '3550A0';

    private const VERMELHO = 'FF361E';

    private const VERDE_CLARO = 'F4FBF3';

    public function gerar(array $dados): string
    {
        $doc = new PhpWord;
        $doc->getSettings()->setThemeFontLang(new Language(Language::PT_BR));

        $this->declararEstilos($doc);

        $secao = $doc->addSection([
            'marginTop' => Converter::cmToTwip(2.5),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft' => Converter::cmToTwip(2.5),
            'marginRight' => Converter::cmToTwip(2.5),
        ]);

        $this->cabecalhoERodape($secao, $dados);
        $this->capa($secao, $dados);
        $this->sumario($secao, $dados);

        foreach ($dados['capitulos'] as $i => $capitulo) {
            if ($i > 0) {
                $secao->addPageBreak();
            }

            $secao->addText(
                $capitulo['numero'].'. '.$capitulo['titulo'],
                'capitulo',
                'semEspaco'
            );

            foreach ($capitulo['secoes'] as $sub) {
                $secao->addTextBreak(1);
                $secao->addText($sub['numero'].' '.$sub['titulo'], 'secao', 'semEspaco');

                $sub['externa']
                    ? $this->secaoExterna($secao, $sub)
                    : $this->conteudo($secao, $sub, $dados);
            }
        }

        $caminho = tempnam(sys_get_temp_dir(), 'rg_').'.docx';
        (new Word2007($doc))->save($caminho);

        return $caminho;
    }

    // ------------------------------------------------------------- estilos

    private function declararEstilos(PhpWord $doc): void
    {
        $doc->setDefaultFontName('Calibri');
        $doc->setDefaultFontSize(10.5);

        $doc->addFontStyle('capitulo', ['size' => 18, 'bold' => true, 'color' => self::VERDE]);
        $doc->addFontStyle('secao', ['size' => 12.5, 'bold' => true, 'color' => self::TEXTO]);
        $doc->addFontStyle('corpo', ['size' => 10.5, 'color' => self::TEXTO]);
        $doc->addFontStyle('nota', ['size' => 9, 'color' => self::CINZA_ESCURO, 'italic' => true]);
        $doc->addFontStyle('rodape', ['size' => 8.5, 'color' => self::CINZA]);
        $doc->addFontStyle('cabecalho', ['size' => 9, 'color' => self::VERDE]);

        $doc->addFontStyle('capaOrgao', ['size' => 14, 'color' => self::VERDE, 'allCaps' => true]);
        $doc->addFontStyle('capaTitulo', ['size' => 34, 'bold' => true, 'color' => self::TEXTO]);
        $doc->addFontStyle('capaAno', ['size' => 34, 'bold' => true, 'color' => self::VERDE]);

        $doc->addFontStyle('thead', ['size' => 9, 'bold' => true, 'color' => 'FFFFFF']);
        $doc->addFontStyle('tcell', ['size' => 9, 'color' => self::TEXTO]);

        $doc->addFontStyle('rotuloMissao', ['size' => 8.5, 'bold' => true, 'allCaps' => true, 'color' => 'A88800']);
        $doc->addFontStyle('rotuloVisao', ['size' => 8.5, 'bold' => true, 'allCaps' => true, 'color' => self::AZUL]);
        $doc->addFontStyle('rotuloValores', ['size' => 8.5, 'bold' => true, 'allCaps' => true, 'color' => '2F7A28']);
        $doc->addFontStyle('rotuloFinalistico', ['size' => 8.5, 'bold' => true, 'allCaps' => true, 'color' => self::VERMELHO]);
        $doc->addFontStyle('rotuloSuporte', ['size' => 8.5, 'bold' => true, 'allCaps' => true, 'color' => self::TEXTO]);

        $doc->addParagraphStyle('semEspaco', ['spaceAfter' => 60]);
        $doc->addParagraphStyle('justificado', ['alignment' => Jc::BOTH, 'spaceAfter' => 120]);
        $doc->addParagraphStyle('centro', ['alignment' => Jc::CENTER]);

        $doc->addTableStyle('rgTabela', [
            'borderColor' => 'CACACC',
            'borderSize' => 6,
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
    }

    // ------------------------------------------------------------ elementos

    private function cabecalhoERodape(Section $secao, array $dados): void
    {
        $cabecalho = $secao->addHeader();
        $tabela = $cabecalho->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $tabela->addRow();
        $tabela->addCell(3333)->addText($dados['capa']['orgao'], 'cabecalho');
        $tabela->addCell(3333)->addText('Relatório de Gestão '.$dados['ano'], 'cabecalho', 'centro');
        $tabela->addCell(3333)->addText($dados['capa']['sigla'], 'cabecalho', ['alignment' => Jc::END]);

        $rodape = $secao->addFooter();
        $linha = $rodape->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $linha->addRow();
        $linha->addCell(3333)->addText('Sistema de Planejamento Estratégico Integrado', 'rodape');
        $celulaPagina = $linha->addCell(3333);
        $celulaPagina->addPreserveText('PÁGINA {PAGE}', 'rodape', 'centro');
        $linha->addCell(3333)->addText('Emitido em '.$dados['capa']['emitido_em'], 'rodape', ['alignment' => Jc::END]);
    }

    private function capa(Section $secao, array $dados): void
    {
        $secao->addTextBreak(6);
        $secao->addText($dados['capa']['orgao'], 'capaOrgao', 'semEspaco');
        $secao->addText('Relatório de Gestão', 'capaTitulo', 'semEspaco');
        $secao->addText((string) $dados['capa']['ano'], 'capaAno', 'semEspaco');
        $secao->addTextBreak(1);

        if ($dados['capa']['ciclo']) {
            $secao->addText('Ciclo: '.$dados['capa']['ciclo'], 'corpo', 'semEspaco');
        }

        $secao->addText('Documento emitido em '.$dados['capa']['emitido_em'].'.', 'corpo', 'semEspaco');
        $secao->addTextBreak(1);

        $secao->addText(
            $dados['variante'] === EstruturaRelatorioGestao::VARIANTE_REPLICA
                ? 'Versão rascunho — estrutura completa do modelo. As seções alimentadas por sistemas '
                  .'externos aparecem marcadas, com a indicação da fonte que as preenche. '
                  .'Complete-as antes de publicar.'
                : 'Este documento apresenta as seções para as quais há informação registrada no '
                  .'Planejamento Estratégico Integrado.',
            'nota',
            'justificado'
        );

        $secao->addPageBreak();
    }

    private function sumario(Section $secao, array $dados): void
    {
        $secao->addText('Sumário', 'capitulo', 'semEspaco');
        $secao->addTextBreak(1);

        foreach ($dados['capitulos'] as $capitulo) {
            $secao->addText(
                $capitulo['numero'].'. '.$capitulo['titulo'],
                ['size' => 11, 'bold' => true, 'color' => self::VERDE],
                'semEspaco'
            );

            foreach ($capitulo['secoes'] as $sub) {
                $texto = '    '.$sub['numero'].' '.$sub['titulo'];

                if ($sub['externa']) {
                    $texto .= '  — a preencher pela unidade';
                }

                $secao->addText($texto, 'corpo', 'semEspaco');
            }

            $secao->addTextBreak(1);
        }

        $secao->addPageBreak();
    }

    private function secaoExterna(Section $secao, array $sub): void
    {
        $secao->addText(
            'Seção a preencher pela unidade. Esta informação não é produzida pelo '
            .'Planejamento Estratégico Integrado.',
            'nota',
            'justificado'
        );
        $secao->addText('Fonte: '.$sub['fonte'], 'nota', 'semEspaco');
    }

    private function conteudo(Section $secao, array $sub, array $dados): void
    {
        match ($sub['tipo']) {
            'estrategia' => $this->estrategia($secao, $sub, $dados),
            'resultados' => $this->resultados($secao, $sub, $dados),
            'grandes-numeros' => $this->grandesNumeros($secao, $sub),
            'cadeia-valor' => $this->cadeiaDeValor($secao, $sub),
            'ambiente-externo' => $this->ambienteExterno($secao, $sub),
            'riscos' => $this->riscos($secao, $sub),
            'organograma' => $this->organograma($secao, $sub),
            default => null,
        };
    }

    private function estrategia(Section $secao, array $sub, array $dados): void
    {
        $d = $sub['dados'];

        $vazio = ! $d['identidade']?->dsc_missao
            && ! $d['identidade']?->dsc_visao
            && $d['valores']->isEmpty()
            && $d['objetivos_finalisticos']->isEmpty()
            && $d['objetivos_suporte']->isEmpty();

        if ($vazio) {
            $this->semRegistro($secao, 'Missão, visão, valores e objetivos ainda não foram cadastrados neste ciclo.');

            return;
        }

        $secao->addText(
            'O Planejamento Estratégico Integrado'
            .($dados['capa']['ciclo'] ? ' '.$dados['capa']['ciclo'] : '')
            .' orienta a atuação da organização no período. Missão, visão, valores e '
            .'objetivos estratégicos abaixo são os que estavam vigentes no exercício de '
            .$dados['ano'].'.',
            'corpo',
            'justificado'
        );

        if ($d['identidade']?->dsc_missao) {
            $this->faixa($secao, 'Missão', $d['identidade']->dsc_missao, 'rotuloMissao', self::AMARELO);
        }

        if ($d['identidade']?->dsc_visao) {
            $this->faixa($secao, 'Visão', $d['identidade']->dsc_visao, 'rotuloVisao', self::AZUL);
        }

        if ($d['valores']->isNotEmpty()) {
            $texto = $d['valores']
                ->map(fn ($v) => $v->nom_valor.($v->dsc_valor ? ' — '.$v->dsc_valor : ''))
                ->implode("\n");

            $this->faixa($secao, 'Valores', $texto, 'rotuloValores', self::VERDE, self::VERDE_CLARO);
        }

        if ($d['objetivos_finalisticos']->isNotEmpty()) {
            $this->faixa(
                $secao,
                'Objetivos Finalísticos',
                $d['objetivos_finalisticos']->pluck('nom_objetivo')->implode("\n"),
                'rotuloFinalistico',
                self::VERMELHO
            );
        }

        if ($d['objetivos_suporte']->isNotEmpty()) {
            $this->faixa(
                $secao,
                'Objetivos de Suporte',
                $d['objetivos_suporte']->pluck('nom_objetivo')->implode("\n"),
                'rotuloSuporte',
                self::TEXTO
            );
        }

        if ($d['temas']->isNotEmpty()) {
            $secao->addText(
                'Temas norteadores: '.$d['temas']->pluck('nom_tema_norteador')->implode(' · '),
                'corpo',
                'justificado'
            );
        }
    }

    private function faixa(Section $secao, string $rotulo, string $texto, string $estiloRotulo, string $cor, ?string $fundo = null): void
    {
        $tabela = $secao->addTable([
            'width' => 100 * 50,
            'unit' => 'pct',
            'borderColor' => $cor,
            'borderSize' => 8,
            'cellMargin' => 100,
        ]);

        $tabela->addRow();
        $celula = $tabela->addCell(null, $fundo ? ['bgColor' => $fundo] : []);
        $celula->addText($rotulo, $estiloRotulo, 'semEspaco');

        foreach (explode("\n", $texto) as $linha) {
            $celula->addText($linha, 'corpo', 'semEspaco');
        }

        $secao->addTextBreak(1);
    }

    private function resultados(Section $secao, array $sub, array $dados): void
    {
        if (($sub['dados']['tabela_2_2_1'] ?? []) === [] && ($sub['dados']['tabela_2_2_2'] ?? []) === []) {
            $this->semRegistro($secao, 'Nenhum objetivo estratégico foi cadastrado neste ciclo, e por isso não há resultados a apurar.');

            return;
        }

        $secao->addText(
            'A seguir são apresentados os objetivos estratégicos do ciclo, sua descrição e as '
            .'principais iniciativas vinculadas. Na sequência, os resultados apurados no '
            .'exercício de '.$dados['ano'].'.',
            'corpo',
            'justificado'
        );

        $t1 = $sub['dados']['tabela_2_2_1'] ?? [];

        if ($t1 !== []) {
            $secao->addText('Tabela 2.2.1 — Informações sobre o Planejamento Estratégico Integrado', 'corpo', 'semEspaco');

            $tabela = $this->tabelaComCabecalho($secao, [
                'Identificador', 'Objetivo Estratégico', 'Descrição (resumida)', 'Principais Iniciativas',
            ]);

            foreach ($t1 as $linha) {
                $tabela->addRow();
                $tabela->addCell(1200)->addText((string) $linha['identificador'], 'tcell', 'centro');
                $tabela->addCell(2600)->addText($linha['objetivo'], 'tcell');
                $tabela->addCell(3200)->addText($linha['descricao'] ?: '—', 'tcell');
                $this->celulaLista($tabela->addCell(3000), $linha['iniciativas'], 'Em revisão');
            }

            $secao->addTextBreak(1);
        }

        $t2 = $sub['dados']['tabela_2_2_2'] ?? [];

        if ($t2 !== []) {
            $secao->addText('Tabela 2.2.2 — Resultados, Objetivos Estratégicos e Prioridades da Gestão', 'corpo', 'semEspaco');

            $tabela = $this->tabelaComCabecalho($secao, ['Objetivo', 'Iniciativas', 'Resultados']);

            foreach ($t2 as $linha) {
                $tabela->addRow();
                $tabela->addCell(2800)->addText($linha['objetivo'], 'tcell');
                $this->celulaLista($tabela->addCell(3400), $linha['iniciativas'], 'Sem iniciativas vinculadas no período.');
                $this->celulaLista(
                    $tabela->addCell(3800),
                    array_column($linha['resultados'], 'texto'),
                    'Sem indicadores com evolução lançada no exercício.'
                );
            }

            $secao->addTextBreak(1);
        }
    }

    private function grandesNumeros(Section $secao, array $sub): void
    {
        $numeros = $sub['dados']['numeros'] ?? [];

        if ($numeros === []) {
            $this->semRegistro($secao, 'Ainda não há objetivos, iniciativas ou indicadores suficientes para compor os números do exercício.');

            return;
        }

        $tabela = $secao->addTable(['width' => 100 * 50, 'unit' => 'pct', 'borderColor' => 'D3EED1', 'borderSize' => 6, 'cellMargin' => 100]);
        $tabela->addRow();

        foreach ($numeros as $numero) {
            $celula = $tabela->addCell(null, ['bgColor' => self::VERDE_CLARO]);
            $celula->addText($numero['valor'], ['size' => 17, 'bold' => true, 'color' => self::VERDE], 'centro');
            $celula->addText($numero['rotulo'], ['size' => 8, 'color' => self::CINZA_ESCURO, 'allCaps' => true], 'centro');
        }

        $secao->addTextBreak(1);
    }

    private function cadeiaDeValor(Section $secao, array $sub): void
    {
        $grupos = $sub['dados']['grupos'] ?? [];

        if ($grupos === []) {
            $this->semRegistro($secao, 'A cadeia de valor ainda não foi cadastrada neste ciclo do Planejamento Estratégico Integrado.');

            return;
        }

        foreach ($grupos as $grupo) {
            $secao->addText($grupo['tipo'], ['size' => 10.5, 'bold' => true, 'color' => self::TEXTO], 'semEspaco');

            $tabela = $this->tabelaComCabecalho($secao, ['Atividade', 'Processos']);

            foreach ($grupo['itens'] as $atividade) {
                $tabela->addRow();
                $tabela->addCell(4500)->addText($atividade->dsc_atividade, 'tcell');
                $this->celulaLista(
                    $tabela->addCell(5500),
                    $atividade->processos->pluck('dsc_transformacao')->all(),
                    'Processos não detalhados.'
                );
            }

            $secao->addTextBreak(1);
        }
    }

    private function ambienteExterno(Section $secao, array $sub): void
    {
        $vazio = ($sub['dados']['swot'] ?? collect())->isEmpty()
            && ($sub['dados']['pestel'] ?? collect())->isEmpty();

        if ($vazio) {
            $this->semRegistro($secao, 'Nenhuma análise SWOT ou PESTEL foi registrada neste ciclo.');

            return;
        }

        foreach (['swot' => 'Matriz SWOT', 'pestel' => 'Análise PESTEL'] as $chave => $titulo) {
            $grupos = $sub['dados'][$chave] ?? collect();

            if ($grupos->isEmpty()) {
                continue;
            }

            $secao->addText($titulo, ['size' => 10.5, 'bold' => true, 'color' => self::TEXTO], 'semEspaco');

            $tabela = $this->tabelaComCabecalho($secao, ['Categoria', 'Itens identificados']);

            foreach ($grupos as $categoria => $itens) {
                $tabela->addRow();
                $tabela->addCell(2600)->addText((string) $categoria, 'tcell', 'centro');
                $this->celulaLista($tabela->addCell(7400), $itens->pluck('dsc_item')->all(), '—');
            }

            $secao->addTextBreak(1);
        }
    }

    private function riscos(Section $secao, array $sub): void
    {
        $riscos = $sub['dados']['riscos'] ?? collect();

        if ($riscos->isEmpty()) {
            $this->semRegistro($secao, 'Nenhum risco foi registrado para este ciclo no módulo de Gestão de Riscos.');

            return;
        }

        $secao->addText(
            'Riscos identificados e monitorados no ciclo, ordenados pelo nível de exposição '
            .'(probabilidade × impacto).',
            'corpo',
            'justificado'
        );

        $tabela = $this->tabelaComCabecalho($secao, ['Risco', 'Categoria', 'Nível', 'Resposta', 'Status']);

        foreach ($riscos as $risco) {
            $tabela->addRow();
            $tabela->addCell(3000)->addText($risco->dsc_titulo, 'tcell');
            $tabela->addCell(1600)->addText($risco->dsc_categoria, 'tcell', 'centro');
            $tabela->addCell(1200)->addText((string) $risco->num_nivel_risco, 'tcell', 'centro');
            $tabela->addCell(2000)->addText($risco->dsc_estrategia_resposta ?: '—', 'tcell', 'centro');
            $tabela->addCell(2200)->addText($risco->dsc_status, 'tcell', 'centro');
        }

        $secao->addTextBreak(1);
    }

    private function organograma(Section $secao, array $sub): void
    {
        $raiz = $sub['dados']['raiz'] ?? null;

        if ($raiz) {
            $secao->addText(
                $raiz->nom_organizacao.($raiz->sgl_organizacao ? ' ('.$raiz->sgl_organizacao.')' : ''),
                'corpo',
                'justificado'
            );
        }

        $unidades = $sub['dados']['unidades'] ?? collect();

        if ($unidades->isEmpty()) {
            if (! $raiz) {
                $this->semRegistro($secao, 'Nenhuma organização foi selecionada para este relatório.');
            }

            return;
        }

        $tabela = $this->tabelaComCabecalho($secao, ['Sigla', 'Unidade']);

        foreach ($unidades as $unidade) {
            $tabela->addRow();
            $tabela->addCell(1800)->addText($unidade->sgl_organizacao, 'tcell', 'centro');
            $tabela->addCell(8200)->addText($unidade->nom_organizacao, 'tcell');
        }

        $secao->addTextBreak(1);
    }

    /**
     * O que o PDF diz quando a seção existe no modelo mas o ciclo não a
     * preencheu. Título seguido de nada não informa; esta frase informa.
     */
    private function semRegistro(Section $secao, string $mensagem): void
    {
        $secao->addText($mensagem, 'nota', 'justificado');
    }

    /** Cabeçalho escuro com texto branco, como no modelo (p. 28). */
    private function tabelaComCabecalho(Section $secao, array $colunas)
    {
        $tabela = $secao->addTable('rgTabela');
        $tabela->addRow(null, ['tblHeader' => true]);

        foreach ($colunas as $coluna) {
            $tabela->addCell(null, ['bgColor' => self::TEXTO])->addText($coluna, 'thead', 'centro');
        }

        return $tabela;
    }

    private function celulaLista($celula, array $itens, string $vazio): void
    {
        if ($itens === []) {
            $celula->addText($vazio, ['size' => 9, 'color' => self::CINZA, 'italic' => true]);

            return;
        }

        foreach ($itens as $item) {
            $celula->addListItem((string) $item, 0, 'tcell');
        }
    }
}
