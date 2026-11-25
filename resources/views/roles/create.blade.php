@extends('layouts.app')
@section('title', 'Novo papel')

@section('nav-leading')
    <a href="{{ route('roles.index') }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="22" :stroke="2.4" /><span>Papéis</span></a>
@endsection

@section('nav-trailing')
    <button type="submit" form="papel-form" class="nav-button is-strong pressable">Criar</button>
@endsection

@section('content')
<header class="large-title" data-large-title>
    <h1>Novo papel</h1>
    <p>Dê um nome e ligue o que este perfil pode fazer.</p>
</header>
<form id="papel-form" action="{{ route('roles.store') }}" method="post" class="form" data-form>
    @csrf
    @include('roles._form', ['role' => null, 'rolePermissions' => []])
    <div class="form-submit">
        <button type="submit" class="button is-filled is-large pressable" data-submit><x-icon name="plus" :size="18" :stroke="2.4" /><span>Criar papel</span></button>
    </div>
</form>
@endsection
