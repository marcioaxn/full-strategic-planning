<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda QUAL relatório gerou cada registro do histórico.
 *
 * 🔴 POR QUE ISTO É NECESSÁRIO
 *
 * A tela "Histórico de Relatórios Gerados" oferecia um botão "Download" para
 * todo registro. Só que o relatório que o cliente baixa clicando na tela não
 * guarda arquivo nenhum — ele é transmitido direto para o navegador, e o
 * registro nasce com `dsc_caminho_arquivo` vazio. O botão prometia um arquivo
 * que não existe, e o clique devolvia "o arquivo não está mais disponível".
 *
 * O próprio texto da tela já dizia a verdade — "o relatório é gerado na hora,
 * sempre com os dados atualizados; aqui ficam o registro e os filtros" — e o
 * botão dizia o contrário. Interface que promete o que não entrega.
 *
 * Com a rota gravada, o histórico oferece o que realmente pode fazer: GERAR DE
 * NOVO, com os mesmos filtros. Quem tem arquivo guardado (relatório agendado)
 * continua com o download.
 *
 * Coluna anulável e sem valor padrão: os registros que já existem no cliente
 * seguem válidos, apenas sem o botão de regerar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pei.tab_relatorios_gerados', function (Blueprint $table) {
            $table->string('dsc_rota', 120)->nullable()->after('dsc_tipo_relatorio');
        });
    }

    public function down(): void
    {
        Schema::table('pei.tab_relatorios_gerados', function (Blueprint $table) {
            $table->dropColumn('dsc_rota');
        });
    }
};
