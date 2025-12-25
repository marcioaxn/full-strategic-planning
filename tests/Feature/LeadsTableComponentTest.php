<?php

namespace Tests\Feature;

use App\Livewire\LeadsTable;
use App\Models\Lead;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class LeadsTableComponentTest extends TestCase
{
    protected static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('pgsql', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PostgreSQL driver not available.');
        }

        if (! static::$migrated) {
            Artisan::call('migrate:fresh', [
                '--database' => 'pgsql',
                '--force' => true,
            ]);

            static::$migrated = true;
        }

        DB::connection('pgsql')->beginTransaction();

        Lead::factory()
            ->count(5)
            ->sequence(
                fn ($sequence) => [
                    'name' => 'Lead ' . $sequence->index,
                    'entity' => ['Comercial', 'PMO', 'Marketing', 'Financeiro', 'Suporte'][$sequence->index % 5],
                    'status' => ['new', 'in_progress', 'won', 'lost', 'new'][$sequence->index % 5],
                ]
            )
            ->create();
    }

    protected function tearDown(): void
    {
        DB::connection('pgsql')->rollBack();

        parent::tearDown();
    }

    public function testItFiltersBySearchAndStatus(): void
    {
        Livewire::test(LeadsTable::class)
            ->set('search', 'Lead 1')
            ->assertSee('Lead 1')
            ->assertDontSee('Lead 0')
            ->set('status', 'new')
            ->assertSee('Lead 0')
            ->assertDontSee('Lead 2');
    }

    public function testItCanResetFilters(): void
    {
        Livewire::test(LeadsTable::class)
            ->set('search', 'Lead 3')
            ->set('status', 'lost')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('status', 'all');
    }

    public function testItCreatesANewLead(): void
    {
        Livewire::test(LeadsTable::class)
            ->call('create')
            ->set('form.name', 'Novo Lead')
            ->set('form.entity', 'Inovacao')
            ->set('form.status', 'new')
            ->set('form.email', 'novo@example.com')
            ->set('form.phone', '+5511999999999')
            ->set('form.notes', 'Lead criado via teste')
            ->call('save')
            ->assertSet('showFormModal', false)
            ->assertDispatched('saved');

        $this->assertDatabaseHas('leads', [
            'name' => 'Novo Lead',
            'entity' => 'Inovacao',
            'status' => 'new',
            'email' => 'novo@example.com',
        ], 'pgsql');
    }

    public function testItUpdatesAnExistingLead(): void
    {
        $lead = Lead::first();

        Livewire::test(LeadsTable::class)
            ->call('edit', $lead->id)
            ->set('form.name', 'Lead Atualizado')
            ->set('form.status', 'won')
            ->call('save')
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'name' => 'Lead Atualizado',
            'status' => 'won',
        ], 'pgsql');
    }

    public function testItDeletesALead(): void
    {
        $lead = Lead::first();

        Livewire::test(LeadsTable::class)
            ->call('confirmDelete', $lead->id)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('leads', [
            'id' => $lead->id,
        ], 'pgsql');
    }
}
