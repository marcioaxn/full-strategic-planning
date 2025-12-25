<?php

namespace App\Livewire;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LeadsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public array $form = [
        'name' => '',
        'entity' => '',
        'status' => 'new',
        'email' => '',
        'phone' => '',
        'notes' => '',
    ];

    public bool $showFormModal = false;
    public bool $showDeleteModal = false;

    public ?Lead $editing = null;

    public ?string $flashMessage = null;
    public string $flashStyle = 'success';

    protected string $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    protected function rules(): array
    {
        $statusKeys = array_keys(Lead::STATUSES);

        return [
            'form.name' => ['required', 'string', 'max:255'],
            'form.entity' => ['nullable', 'string', 'max:255'],
            'form.status' => ['required', 'in:' . implode(',', $statusKeys)],
            'form.email' => ['nullable', 'email', 'max:255'],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.notes' => ['nullable', 'string'],
        ];
    }

    public function updatingSearch(string $value): void
    {
        Log::info('LeadsTable updating search', [
            'previous' => $this->search,
            'incoming' => $value,
        ]);

        $this->resetPage();
    }

    public function updatingStatus(string $value): void
    {
        Log::info('LeadsTable updating status', [
            'previous' => $this->status,
            'incoming' => $value,
        ]);

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        Log::info('LeadsTable resetFilters called', [
            'search_before' => $this->search,
            'status_before' => $this->status,
        ]);

        $this->search = '';
        $this->status = 'all';
        $this->resetPage();
    }

    public function getStatusesProperty(): array
    {
        return ['all' => __('All')] + Lead::STATUSES;
    }

    public function getStatusOptionsProperty(): array
    {
        return Lead::STATUSES;
    }

    protected function baseQuery(): Builder
    {
        $query = Lead::query();
        $search = trim($this->search);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($search !== '') {
            $this->applySearchFilter($query, $search);
        }

        if ($search !== '' || $this->status !== 'all') {
            Log::debug('LeadsTable filters in effect', [
                'search' => $search,
                'status' => $this->status,
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
        }

        return $query;
    }

    protected function paginatedLeads(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->orderByDesc('updated_at')
            ->paginate(10);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function edit(int $leadId): void
    {
        $this->editing = Lead::findOrFail($leadId);
        $this->form = $this->editing->only(array_keys($this->form));
        $this->showFormModal = true;
        $this->resetValidation();
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate()['form'];

        if ($this->editing) {
            $this->editing->update($data);
            $message = __('Lead updated successfully.');
        } else {
            $this->editing = Lead::create($data);
            $message = __('Lead created successfully.');
        }

        $this->notify($message);
        $this->dispatch('saved');
        $this->closeFormModal();
        $this->resetPage();
    }


    public function confirmDelete(int $leadId): void
    {
        $this->editing = Lead::findOrFail($leadId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->editing = null;
    }

    public function delete(): void
    {
        if ($this->editing) {
            $this->editing->delete();
            $this->notify(__('Lead deleted successfully.'), 'warning');
        }

        $this->dispatch('deleted');
        $this->cancelDelete();
        $this->resetForm();
        $this->resetPage();
    }

    public function exportCsv()
    {
        $fileName = 'leads-' . now()->format('Ymd-His') . '.csv';
        $leads = $this->baseQuery()->orderBy('name')->cursor();

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Name',
                'Team',
                'Status',
                'Email',
                'Phone',
                'Updated at',
            ]);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->name,
                    $lead->entity,
                    $lead->status_label,
                    $lead->email,
                    $lead->phone,
                    optional($lead->updated_at)->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        Log::info('LeadsTable render', [
            'search' => $this->search,
            'status' => $this->status,
            'page' => $this->getPage(),
        ]);

        return view('livewire.leads-table', [
            'leads' => $this->paginatedLeads(),
            'statuses' => $this->statuses,
        ]);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'entity' => '',
            'status' => 'new',
            'email' => '',
            'phone' => '',
            'notes' => '',
        ];

        $this->editing = null;
    }

    protected function notify(string $message, string $style = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashStyle = $style;
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $columns = ['name', 'entity', 'email', 'phone', 'notes'];
        $driver = $query->getModel()->getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $like = '%' . $search . '%';

            $query->where(function (Builder $subQuery) use ($columns, $like) {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $subQuery->{$method}($column, 'ilike', $like);
                }
            });

            return;
        }

        $normalized = Str::lower($search);
        $like = '%' . $normalized . '%';

        $query->where(function (Builder $subQuery) use ($columns, $like) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $subQuery->{$method}('LOWER(' . $column . ') LIKE ?', [$like]);
            }
        });
    }
}
