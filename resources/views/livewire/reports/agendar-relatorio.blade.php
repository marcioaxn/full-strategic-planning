<div>
    <x-dialog-modal wire:model.live="showModal">
        <x-slot name="title">
            <i class="bi bi-calendar-check me-2"></i>{{ __('Agendar Relatório') }}
        </x-slot>

        <x-slot name="content">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label small fw-bold text-muted text-uppercase">{{ __('Tipo de Relatório') }}</label>
                    <div class="p-2 bg-light rounded border">
                        <span class="fw-bold text-primary">{{ ucfirst($tipoRelatorio) }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="frequencia" class="form-label small fw-bold text-muted text-uppercase">{{ __('Frequência') }}</label>
                    <select id="frequencia" wire:model="frequencia" class="form-select">
                        <option value="diario">{{ __('Diário') }}</option>
                        <option value="semanal">{{ __('Semanal') }}</option>
                        <option value="mensal">{{ __('Mensal') }}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="dataInicio" class="form-label small fw-bold text-muted text-uppercase">{{ __('Próxima Execução') }}</label>
                    <input type="datetime-local" id="dataInicio" wire:model="dataInicio" class="form-control">
                </div>

                <div class="col-12">
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('O relatório será gerado automaticamente e ficará disponível no seu histórico.') }}
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-button class="ms-2" wire:click="salvar">
                {{ __('Agendar') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>