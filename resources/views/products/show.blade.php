@extends('layouts.app')
@section('title', $aeronave->prefixo)

@section('content')
@php
    $idade = (int) $aeronave->data_fab->diffInYears(now());
    $registro = str_pad($aeronave->id, 4, '0', STR_PAD_LEFT);
@endphp
<section class="detail-hero">
    <div class="detail-media photo" style="view-transition-name: aeronave-{{ $aeronave->id }}">
        @if ($aeronave->hasImage())
            <img src="{{ asset($aeronave->foto) }}" alt="{{ $aeronave->modelo }}, prefixo {{ $aeronave->prefixo }}" data-parallax fetchpriority="high">
        @else
            <span class="photo-missing"><x-icon name="plane" :size="64" :stroke="1.2" /><small class="mono">Sem foto cadastrada</small></span>
        @endif
    </div>
    <a href="{{ route('aeronaves.index') }}" class="nav-back is-glass pressable" data-vt="back"><x-icon name="chevron-left" :size="18" :stroke="2.4" /><span>Hangar</span></a>
    <header class="detail-title" data-large-title>
        <span class="chip {{ $aeronave->is_public ? 'is-public' : 'is-private' }}"><i aria-hidden="true"></i>{{ $aeronave->is_public ? 'Visível na landing page' : 'Privada, só no hangar' }}</span>
        <h1>{{ $aeronave->prefixo }}</h1>
        <p><em>{{ $aeronave->modelo }}</em></p>
    </header>
</section>

<div class="detail-body">
    <article class="pass" aria-label="Ficha técnica">
        <div class="pass-main">
            <header class="pass-head mono"><span>Ficha técnica</span><span>AERIS · {{ $registro }}</span></header>
            <dl class="pass-grid">
                <div><dt class="mono">Prefixo</dt><dd>{{ $aeronave->prefixo }}</dd></div>
                <div><dt class="mono">Modelo</dt><dd>{{ $aeronave->modelo }}</dd></div>
                <div><dt class="mono">Tripulação</dt><dd class="num">{{ $aeronave->tripulacao }} <small>{{ $aeronave->tripulacao === 1 ? 'pessoa' : 'pessoas' }}</small></dd></div>
                <div><dt class="mono">Fabricação</dt><dd class="num">{{ $aeronave->data_fab->format('d/m/Y') }} <small>{{ $idade < 1 ? 'menos de 1 ano' : $idade.' '.($idade === 1 ? 'ano' : 'anos') }}</small></dd></div>
            </dl>
        </div>
        <aside class="pass-stub">
            <span class="mono">Registro nº</span>
            <strong class="num">{{ $registro }}</strong>
            <span class="pass-barcode" aria-hidden="true"></span>
        </aside>
    </article>

    <div class="detail-actions">
        @can('editar-aeronave')
            <a href="{{ route('aeronaves.edit', $aeronave) }}" class="button is-accent is-large pressable"><x-icon name="pencil" :size="17" /><span>Editar</span></a>
        @endcan
        @if ($aeronave->is_public)
            <a href="{{ url('/') }}" class="button is-ghost is-large pressable" data-vt="fade"><x-icon name="globe" :size="17" /><span>Ver na landing</span></a>
        @endif
        @can('excluir-aeronave')
            <button type="submit" form="excluir-aeronave" class="button is-danger is-large pressable"><x-icon name="trash" :size="17" /><span>Excluir</span></button>
        @endcan
    </div>
</div>

@can('excluir-aeronave')
    <form id="excluir-aeronave" action="{{ route('aeronaves.destroy', $aeronave) }}" method="post" hidden
          data-delete-form="A aeronave {{ $aeronave->prefixo }} e a foto dela serão apagadas. Não dá para desfazer."
          data-delete-title="Excluir {{ $aeronave->prefixo }}?">@csrf @method('DELETE')</form>
@endcan
@endsection
