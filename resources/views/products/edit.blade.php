@extends('layouts.app')
@section('title', 'Editar '.$aeronave->prefixo)

@section('nav-leading')
    <a href="{{ route('aeronaves.show', $aeronave) }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="18" :stroke="2.4" /><span>{{ $aeronave->prefixo }}</span></a>
@endsection

@section('content')
<header class="page-head is-compact" data-large-title>
    <div>
        <p class="overline mono">Edição</p>
        <h1>Editar <em>{{ $aeronave->prefixo }}</em></h1>
        <p class="page-lede">{{ $aeronave->modelo }}</p>
    </div>
</header>
<form id="aeronave-form" action="{{ route('aeronaves.update', $aeronave) }}" method="post" enctype="multipart/form-data" class="form is-split" data-form>
    @csrf @method('PUT')
    @include('products._form', ['aeronave' => $aeronave])
    <div class="form-submit">
        <button type="submit" class="button is-accent is-large pressable" data-submit><x-icon name="check" :size="18" :stroke="2.6" /><span>Salvar alterações</span></button>
    </div>
</form>
@endsection
