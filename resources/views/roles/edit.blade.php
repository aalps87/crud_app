@extends('layouts.app')
@section('title', $role->name)

@section('nav-leading')
    <a href="{{ route('roles.index') }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="22" :stroke="2.4" /><span>Papéis</span></a>
@endsection

@section('nav-trailing')
    <button type="submit" form="papel-form" class="nav-button is-strong pressable">Salvar</button>
@endsection

@section('content')
<header class="large-title" data-large-title>
    <h1>{{ $role->name }}</h1>
    <p>As mudanças valem na hora para todos com este papel.</p>
</header>
<form id="papel-form" action="{{ route('roles.update', $role) }}" method="post" class="form" data-form>
    @csrf @method('PUT')
    @include('roles._form')
    <div class="form-submit">
        <button type="submit" class="button is-filled is-large pressable" data-submit><x-icon name="check" :size="18" :stroke="2.6" /><span>Salvar alterações</span></button>
    </div>
</form>
@endsection
