@extends('layouts.app')
@section('title', 'Nova aeronave')

@section('nav-leading')
    <a href="{{ route('aeronaves.index') }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="18" :stroke="2.4" /><span>Hangar</span></a>
@endsection

@section('content')
<header class="page-head is-compact" data-large-title>
    <div>
        <p class="overline mono">Cadastro</p>
        <h1>Nova <em>aeronave</em></h1>
        <p class="page-lede">Preencha os dados técnicos. A foto pode ficar para depois.</p>
    </div>
</header>
<form id="aeronave-form" action="{{ route('aeronaves.store') }}" method="post" enctype="multipart/form-data" class="form is-split" data-form>
    @csrf
    @include('products._form', ['aeronave' => null])
    <div class="form-submit">
        <button type="submit" class="button is-accent is-large pressable" data-submit><x-icon name="plus" :size="18" :stroke="2.4" /><span>Adicionar ao hangar</span></button>
    </div>
</form>
@endsection
