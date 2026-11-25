@extends('layouts.app')
@section('title', 'Novo usuário')

@section('nav-leading')
    <a href="{{ route('users.index') }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="22" :stroke="2.4" /><span>Usuários</span></a>
@endsection

@section('nav-trailing')
    <button type="submit" form="usuario-form" class="nav-button is-strong pressable">Criar</button>
@endsection

@section('content')
<header class="large-title" data-large-title>
    <h1>Novo usuário</h1>
    <p>Crie um acesso ao hangar e escolha o papel da pessoa.</p>
</header>
<form id="usuario-form" action="{{ route('users.store') }}" method="post" class="form" data-form>
    @csrf
    @include('users._form', ['user' => null, 'userRoles' => []])
    <div class="form-submit">
        <button type="submit" class="button is-filled is-large pressable" data-submit><x-icon name="plus" :size="18" :stroke="2.4" /><span>Criar usuário</span></button>
    </div>
</form>
@endsection
