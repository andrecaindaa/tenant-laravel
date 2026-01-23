@extends('layouts.app')

@section('title', 'Criar Nova Empresa')

@section('styles')
<style>
    .create-container {
        max-width: 500px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    input[type="text"] {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    input[type="text"]:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .btn-submit {
        width: 100%;
        padding: 0.875rem;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn-submit:hover {
        background: #5a67d8;
    }
    .btn-submit:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }
    .help-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    .slug-preview {
        margin-top: 0.5rem;
        padding: 0.75rem;
        background: #f3f4f6;
        border-radius: 6px;
        font-family: monospace;
        color: #4b5563;
    }
    .plan-selection {
        margin-top: 2rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 8px;
        border: 2px dashed #cbd5e0;
    }
    .plan-selection h3 {
        margin-bottom: 1rem;
        color: #374151;
    }
    .plan-options {
        display: grid;
        gap: 1rem;
    }
    .plan-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .plan-option:hover {
        border-color: #667eea;
    }
    .plan-option.selected {
        border-color: #667eea;
        background: #eef2ff;
    }
    .plan-radio {
        accent-color: #667eea;
        width: 18px;
        height: 18px;
    }
    .plan-info h4 {
        margin: 0;
        color: #374151;
    }
    .plan-info p {
        margin: 0.25rem 0 0 0;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .back-link {
        display: inline-block;
        margin-top: 1.5rem;
        color: #6b7280;
        text-decoration: none;
    }
    .back-link:hover {
        color: #374151;
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="create-container">
    <h1 class="text-2xl font-bold mb-6">Criar Nova Empresa</h1>

    <form method="POST" action="{{ route('tenants.store') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nome da Empresa *</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   placeholder="Ex: Minha Empresa Lda"
                   oninput="updateSlugPreview(this.value)">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="help-text">Este será o nome visível no sistema.</p>
        </div>

        <div class="slug-preview">
            Slug gerado: <span id="slugPreview">minha-empresa-lda</span>
        </div>

        <div class="form-group">
            <label for="description">Descrição (opcional)</label>
            <textarea id="description"
                      name="description"
                      rows="3"
                      placeholder="Descreva brevemente a sua empresa..."
                      class="w-full p-3 border border-gray-300 rounded-lg">{{ old('description') }}</textarea>
        </div>

        <div class="plan-selection">
            <h3 class="text-lg font-semibold">Plano Inicial</h3>
            <p class="text-gray-600 mb-4">Todos os novos tenants começam com o plano Free. Pode fazer upgrade mais tarde.</p>

            @php
                $freePlan = \App\Models\Plan::where('slug', 'free')->first();
            @endphp

            @if($freePlan)
                <div class="plan-option selected">
                    <input type="radio"
                           id="plan_free"
                           name="plan_id"
                           value="{{ $freePlan->id }}"
                           class="plan-radio"
                           checked>
                    <div class="plan-info">
                        <h4>{{ $freePlan->name }}</h4>
                        <p>{{ $freePlan->description }}</p>
                        <p class="mt-2"><strong>Limites:</strong>
                            @foreach($freePlan->limits as $key => $limit)
                                {{ ucfirst($key) }}: {{ $limit }}
                                @if(!$loop->last) • @endif
                            @endforeach
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <button type="submit" class="btn-submit">
            Criar Empresa
        </button>

        <a href="{{ route('select-tenant') }}" class="back-link">
            ← Voltar para seleção de empresas
        </a>
    </form>
</div>

<script>
function updateSlugPreview(name) {
    if (!name.trim()) {
        document.getElementById('slugPreview').textContent = '';
        return;
    }

    // Converter para slug
    let slug = name
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Remover acentos
        .replace(/[^\w\s]/g, '') // Remover caracteres especiais
        .replace(/\s+/g, '-') // Substituir espaços por hífens
        .replace(/-+/g, '-') // Remover hífens duplicados
        .trim();

    // Adicionar timestamp para ser único
    const timestamp = Date.now().toString().slice(-4);
    slug = slug + '-' + timestamp;

    document.getElementById('slugPreview').textContent = slug;
}

// Inicializar com valor do campo
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    if (nameInput.value) {
        updateSlugPreview(nameInput.value);
    }
});
</script>
@endsection
