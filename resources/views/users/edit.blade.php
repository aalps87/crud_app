@extends('layouts.app')
@section('title', $user->name)

@section('nav-leading')
    <a href="{{ route('users.index') }}" class="nav-back pressable" data-vt="back"><x-icon name="chevron-left" :size="22" :stroke="2.4" /><span>Usuários</span></a>
@endsection

@section('nav-trailing')
    <button type="submit" form="usuario-form" class="nav-button is-strong pressable">Salvar</button>
@endsection

@section('content')
@php
    $parts = preg_split('/\s+/', trim($user->name));
    $ini = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1).(count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
@endphp
<header class="profile-head" data-large-title>
    <span class="avatar avatar-xl" data-hue="{{ $user->id % 6 }}" aria-hidden="true">{{ $ini }}</span>
    <h1>{{ $user->name }}</h1>
    <p>{{ $user->email }}</p>
</header>
<form id="usuario-form" action="{{ route('users.update', $user) }}" method="post" class="form" data-form>
    @csrf @method('PUT')
    @include('users._form')
    <div class="form-submit">
        <button type="submit" class="button is-filled is-large pressable" data-submit><x-icon name="check" :size="18" :stroke="2.6" /><span>Salvar alterações</span></button>
    </div>
</form>
@endsection
