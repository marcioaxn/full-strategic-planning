<?php

namespace Tests;

use Database\Seeders\PerfilAcessoSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Os perfis de acesso são dado de referência. Os quatro originais nascem
     * na migration de 2014; o perfil Consulta nasce pelo PerfilAcessoSeeder —
     * o mesmo que a implantação roda. Sem isto, todo teste que vincula alguém
     * ao perfil Consulta quebraria pela chave estrangeira.
     */
    protected bool $seed = true;

    protected string $seeder = PerfilAcessoSeeder::class;
}
