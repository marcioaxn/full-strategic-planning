@props(['comentario', 'level' => 1])

<div class="notion-comment-wrapper {{ $level > 1 ? 'ms-4 border-start ps-3 mt-2' : 'mb-3' }}">
    <div class="d-flex align-items-start gap-2">
        <div class="notion-card-avatar flex-shrink-0">
            {{ strtoupper(substr($comentario->usuario->name ?? 'U', 0, 2)) }}
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-semibold small">{{ $comentario->usuario->name ?? 'Usuário' }}</span>
                <span class="text-muted small" style="font-size: 0.65rem;">{{ $comentario->created_at->diffForHumans() }}</span>
            </div>
            <p class="mb-1 small text-dark">{{ $comentario->dsc_comentario }}</p>
            
            {{-- Ações do comentário --}}
            <div class="d-flex gap-3 mt-1">
                {{-- Botão Responder (até o nível 2, para que a resposta seja nível 3) --}}
                @if($level < 3)
                    <button 
                        wire:click="setRespondendo('{{ $comentario->cod_comentario }}')" 
                        class="btn btn-sm btn-link p-0 text-muted text-decoration-none small"
                        style="font-size: 0.7rem;"
                    >
                        <i class="bi bi-reply me-1"></i>Responder
                    </button>
                @endif

                @if($comentario->cod_usuario === auth()->id())
                    <button 
                        wire:click="excluirComentario('{{ $comentario->cod_comentario }}')"
                        class="btn btn-sm btn-link p-0 text-danger text-decoration-none small"
                        style="font-size: 0.7rem;"
                    >
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                @endif
            </div>

            {{-- Form de Resposta (Inline) --}}
            @if($respondendoComentarioId === $comentario->cod_comentario)
                <div class="mt-2" x-data="{ resposta: '', sub: false }">
                    <textarea 
                        x-model="resposta"
                        class="form-control form-control-sm bg-light"
                        rows="2"
                        placeholder="Sua resposta..."
                        autofocus
                    ></textarea>
                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-light" wire:click="setRespondendo(null)">Cancelar</button>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-primary"
                            x-bind:disabled="!resposta.trim() || sub"
                            @click="sub = true; $wire.dispatch('adicionar-comentario', { entregaId: '{{ $comentario->cod_entrega }}', conteudo: resposta, comentarioPaiId: '{{ $comentario->cod_comentario }}' }); resposta = ''; sub = false;"
                        >
                            Responder
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Respostas (Recursivo) --}}
    @if($comentario->respostas->count() > 0)
        <div class="notion-comment-replies">
            @foreach($comentario->respostas as $resposta)
                @include('livewire.entregas.partials.comentario-item', ['comentario' => $resposta, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
