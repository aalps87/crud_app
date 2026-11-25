@extends('layouts.app')
@section('title', 'Hangar')

@section('content')
@php
    $filtros = ['todas' => 'Todas', 'publicas' => 'Públicas', 'privadas' => 'Privadas'];
    $canEdit = auth()->user()->can('editar-aeronave');
    $canDelete = auth()->user()->can('excluir-aeronave');
    $destaque = ($busca === '' && $aeronaves->onFirstPage()) ? $aeronaves->first(fn ($a) => $a->hasImage()) : null;
    $pad = fn ($n) => str_pad($n, 2, '0', STR_PAD_LEFT);
@endphp

<header class="page-head" data-large-title>
    <div>
        <p class="overline mono">Inventário de frota</p>
        <h1>O <em>hangar</em></h1>
    </div>
    <div class="page-head-side">
        <dl class="tally mono" aria-label="Resumo da frota">
            <div><dt>Total</dt><dd>{{ $pad($contagens['todas']) }}</dd></div>
            <div><dt>Públicas</dt><dd>{{ $pad($contagens['publicas']) }}</dd></div>
            <div><dt>Privadas</dt><dd>{{ $pad($contagens['privadas']) }}</dd></div>
        </dl>
        @can('criar-aeronave')
            <a href="{{ route('aeronaves.create') }}" class="button is-accent pressable"><x-icon name="plus" :size="17" :stroke="2.4" /><span>Nova aeronave</span></a>
        @endcan
    </div>
</header>

@if ($destaque)
    @php($idade = (int) $destaque->data_fab->diffInYears(now()))
    <a href="{{ route('aeronaves.show', $destaque) }}" class="feature pressable-soft" aria-label="Abrir ficha de {{ $destaque->prefixo }}">
        <span class="feature-media photo" style="view-transition-name: aeronave-{{ $destaque->id }}">
            <img src="{{ asset($destaque->foto) }}" alt="" data-parallax fetchpriority="high">
        </span>
        <span class="feature-body">
            <span class="feature-tag mono"><i class="pulse" aria-hidden="true"></i>Em destaque · Registro {{ str_pad($destaque->id, 4, '0', STR_PAD_LEFT) }}</span>
            <strong class="feature-prefix">{{ $destaque->prefixo }}</strong>
            <em class="feature-model">{{ $destaque->modelo }}</em>
            <span class="feature-specs mono">
                <span><small>Tripulação</small>{{ $pad($destaque->tripulacao) }}</span>
                <span><small>Fabricação</small>{{ $destaque->data_fab->format('Y') }}</span>
                <span><small>Idade</small>{{ $idade < 1 ? '<1' : $idade }} {{ $idade === 1 ? 'ano' : 'anos' }}</span>
                <span><small>Status</small>{{ $destaque->is_public ? 'Pública' : 'Privada' }}</span>
            </span>
            <span class="feature-cta">Abrir ficha <x-icon name="chevron-right" :size="16" :stroke="2.4" /></span>
        </span>
    </a>
@endif

<div class="controls">
    <nav class="segmented" aria-label="Filtrar por visibilidade">
        @foreach ($filtros as $key => $label)
            <a href="{{ route('aeronaves.index', array_filter(['filtro' => $key === 'todas' ? null : $key, 'busca' => $busca ?: null])) }}"
               class="segment pressable {{ $filtro === $key ? 'is-selected' : '' }}" data-vt="fade"
               @if($filtro === $key) aria-current="true" @endif>
                @if ($filtro === $key)<span class="segment-pill" aria-hidden="true"></span>@endif
                <span class="segment-label">{{ $label }}</span>
                <span class="segment-count mono">{{ $contagens[$key] }}</span>
            </a>
        @endforeach
    </nav>

    <form class="search-field" method="get" action="{{ route('aeronaves.index') }}" role="search">
        @if ($filtro !== 'todas')<input type="hidden" name="filtro" value="{{ $filtro }}">@endif
        <x-icon name="search" :size="16" :stroke="2.2" class="search-glyph" />
        <input type="search" name="busca" value="{{ $busca }}" placeholder="Buscar prefixo ou modelo" aria-label="Buscar aeronaves" enterkeyhint="search" autocomplete="off">
        @if ($busca !== '')
            <a href="{{ route('aeronaves.index', array_filter(['filtro' => $filtro === 'todas' ? null : $filtro])) }}" class="search-clear" aria-label="Limpar busca" data-vt="fade"><x-icon name="x" :size="11" :stroke="3.2" /></a>
        @endif
    </form>
</div>

<section aria-labelledby="galeria-titulo">
    <div class="section-rail mono">
        <h2 id="galeria-titulo">{{ $busca !== '' ? 'Resultados para “'.$busca.'”' : $filtros[$filtro] }}</h2>
        @if ($aeronaves->total() > 0)<span>{{ $pad($aeronaves->firstItem()) }}–{{ $pad($aeronaves->lastItem()) }} / {{ $pad($aeronaves->total()) }}</span>@endif
    </div>

    @if ($aeronaves->isEmpty())
        <div class="empty-state">
            <span class="empty-orbit" aria-hidden="true"><x-icon :name="$busca !== '' ? 'search' : 'plane'" :size="34" :stroke="1.5" /></span>
            @if ($busca !== '')
                <h2>Nada no <em>radar</em></h2>
                <p>Nenhuma aeronave com “{{ $busca }}” no prefixo ou no modelo.</p>
            @elseif ($filtro !== 'todas')
                <h2>Nenhuma aeronave {{ $filtro === 'publicas' ? 'pública' : 'privada' }}</h2>
                <p>Mude a visibilidade de uma aeronave ao editá-la.</p>
            @else
                <h2>Hangar <em>vazio</em></h2>
                <p>Cadastre a primeira aeronave para começar o inventário.</p>
                @can('criar-aeronave')<a href="{{ route('aeronaves.create') }}" class="button is-accent pressable"><x-icon name="plus" :size="17" :stroke="2.4" />Cadastrar aeronave</a>@endcan
            @endif
        </div>
    @else
        <ul class="gallery" role="list">
            @foreach ($aeronaves as $aeronave)
                <li class="craft" style="--i: {{ $loop->index }}">
                    <a href="{{ route('aeronaves.show', $aeronave) }}" class="craft-link pressable">
                        <span class="craft-media photo" @if(!$destaque || $destaque->id !== $aeronave->id) style="view-transition-name: aeronave-{{ $aeronave->id }}" @endif>
                            @if ($aeronave->hasImage())
                                <img src="{{ asset($aeronave->foto) }}" alt="" loading="lazy">
                            @else
                                <span class="photo-missing"><x-icon name="plane" :size="40" :stroke="1.3" /><small class="mono">Sem foto</small></span>
                            @endif
                        </span>
                        <span class="craft-top">
                            <span class="chip {{ $aeronave->is_public ? 'is-public' : 'is-private' }}"><i aria-hidden="true"></i>{{ $aeronave->is_public ? 'Pública' : 'Privada' }}</span>
                        </span>
                        <span class="craft-body">
                            <span class="craft-index mono">{{ str_pad($aeronave->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <strong class="craft-prefix">{{ $aeronave->prefixo }}</strong>
                            <em class="craft-model">{{ $aeronave->modelo }}</em>
                            <span class="craft-meta mono">{{ $pad($aeronave->tripulacao) }} trip. · {{ $aeronave->data_fab->format('Y') }}</span>
                        </span>
                    </a>
                    <button type="button" class="craft-more pressable" aria-label="Ações para {{ $aeronave->prefixo }}" aria-haspopup="menu" aria-controls="menu-aeronave-{{ $aeronave->id }}" data-menu-trigger><x-icon name="ellipsis" :size="18" /></button>
                    <div class="menu" id="menu-aeronave-{{ $aeronave->id }}" role="menu" hidden>
                        <a role="menuitem" href="{{ route('aeronaves.show', $aeronave) }}" class="menu-item"><span>Ver ficha</span><x-icon name="eye" :size="17" /></a>
                        @if ($canEdit)<a role="menuitem" href="{{ route('aeronaves.edit', $aeronave) }}" class="menu-item"><span>Editar</span><x-icon name="pencil" :size="17" /></a>@endif
                        @if ($canDelete)<button role="menuitem" type="submit" form="excluir-{{ $aeronave->id }}" class="menu-item is-destructive"><span>Excluir</span><x-icon name="trash" :size="17" /></button>@endif
                    </div>
                </li>
            @endforeach
        </ul>
        {{ $aeronaves->links('pagination.ios') }}
    @endif

    @if ($canDelete)
        @foreach ($aeronaves as $aeronave)
            <form id="excluir-{{ $aeronave->id }}" action="{{ route('aeronaves.destroy', $aeronave) }}" method="post" hidden
                  data-delete-form="A aeronave {{ $aeronave->prefixo }} e a foto dela serão apagadas. Não dá para desfazer."
                  data-delete-title="Excluir {{ $aeronave->prefixo }}?">@csrf @method('DELETE')</form>
        @endforeach
    @endif
</section>
@endsection
