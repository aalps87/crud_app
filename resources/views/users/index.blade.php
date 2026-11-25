@extends('layouts.app')
@section('title', 'Usuários')

@section('nav-trailing')
    @can('criar-usuario')
        <a href="{{ route('users.create') }}" class="nav-button is-filled pressable" aria-label="Novo usuário"><x-icon name="plus" :size="18" :stroke="2.4" /><span class="nav-button-label">Novo usuário</span></a>
    @endcan
@endsection

@section('content')
@php
    $me = auth()->user();
    $canEdit = $me->can('editar-usuario');
    $canDelete = $me->can('excluir-usuario');
@endphp
<header class="large-title" data-large-title>
    <h1>Usuários</h1>
    <p>{{ $users->total() }} {{ $users->total() === 1 ? 'conta pode' : 'contas podem' }} entrar no hangar</p>
</header>

<div class="search-field">
    <x-icon name="search" :size="17" :stroke="2.2" class="search-glyph" />
    <input type="search" placeholder="Buscar nome, e-mail ou papel" aria-label="Buscar usuários" autocomplete="off" data-live-search="#lista-usuarios">
</div>

<section class="group-section">
    <div class="group-header">
        <span>Contas</span>
        @if ($users->total() > 0)<span class="num">{{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }}</span>@endif
    </div>

    @if ($users->isEmpty())
        <div class="group empty-state">
            <span class="empty-glyph tint-indigo"><x-icon name="users" :size="30" :stroke="1.7" /></span>
            <h2>Nenhum usuário</h2>
            <p>Cadastre a primeira conta do sistema.</p>
        </div>
    @else
        <ul class="group list" role="list" id="lista-usuarios">
            @foreach ($users as $user)
                @php
                    $isSuperAdmin = $user->hasRole('Super Admin');
                    $isMe = $user->is($me);
                    $rowEdit = $canEdit && (! $isSuperAdmin || $isMe);
                    $rowDelete = $canDelete && ! $isSuperAdmin && ! $isMe;
                    $parts = preg_split('/\s+/', trim($user->name));
                    $ini = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1).(count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
                    $roleNames = $user->getRoleNames();
                @endphp
                <li class="row" @if($rowEdit || $rowDelete) data-swipe @endif style="--i: {{ $loop->index }}" data-search-text="{{ mb_strtolower($user->name.' '.$user->email.' '.$roleNames->join(' ')) }}">
                    @if ($rowEdit || $rowDelete)
                        <div class="swipe-actions" aria-hidden="true">
                            @if ($rowEdit)<a href="{{ route('users.edit', $user) }}" class="swipe-action is-edit" tabindex="-1"><x-icon name="pencil" :size="19" /><span>Editar</span></a>@endif
                            @if ($rowDelete)<button type="submit" form="excluir-usuario-{{ $user->id }}" class="swipe-action is-delete" tabindex="-1"><x-icon name="trash" :size="19" /><span>Excluir</span></button>@endif
                        </div>
                    @endif
                    <div class="row-front">
                        @if ($rowEdit)<a href="{{ route('users.edit', $user) }}" class="row-link">@else<div class="row-link">@endif
                            <span class="avatar" data-hue="{{ $user->id % 6 }}" aria-hidden="true">{{ $ini }}</span>
                            <span class="row-text">
                                <strong>{{ $user->name }}@if ($isMe) <span class="chip is-you">Você</span>@endif</strong>
                                <span>{{ $user->email }}</span>
                                <span class="chip-list">
                                    @forelse ($roleNames as $role)
                                        <span class="chip {{ $role === 'Super Admin' ? 'is-strong' : 'is-role' }}">{{ $role }}</span>
                                    @empty
                                        <span class="chip is-private">Sem papel</span>
                                    @endforelse
                                </span>
                            </span>
                            @if ($rowEdit)<x-icon name="chevron-right" :size="16" :stroke="2.4" class="row-chevron" />@endif
                        @if ($rowEdit)</a>@else</div>@endif
                        @if ($rowEdit || $rowDelete)
                            <button type="button" class="row-more pressable" aria-label="Ações para {{ $user->name }}" aria-haspopup="menu" aria-controls="menu-usuario-{{ $user->id }}" data-menu-trigger><x-icon name="ellipsis" :size="20" /></button>
                        @endif
                    </div>
                    @if ($rowEdit || $rowDelete)
                        <div class="menu" id="menu-usuario-{{ $user->id }}" role="menu" hidden>
                            @if ($rowEdit)<a role="menuitem" href="{{ route('users.edit', $user) }}" class="menu-item"><span>Editar</span><x-icon name="pencil" :size="18" /></a>@endif
                            @if ($rowDelete)<button role="menuitem" type="submit" form="excluir-usuario-{{ $user->id }}" class="menu-item is-destructive"><span>Excluir</span><x-icon name="trash" :size="18" /></button>@endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="group empty-state is-search-empty" hidden data-live-empty="#lista-usuarios">
            <span class="empty-glyph tint-blue"><x-icon name="search" :size="30" :stroke="1.7" /></span>
            <h2>Nada nesta página</h2>
            <p>Nenhuma conta desta página combina com a busca.</p>
        </div>
        {{ $users->links('pagination.ios') }}
    @endif

    @foreach ($users as $user)
        @if ($canDelete && ! $user->hasRole('Super Admin') && ! $user->is($me))
            <form id="excluir-usuario-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="post" hidden
                  data-delete-form="{{ $user->name }} perde o acesso ao hangar na hora. Não dá para desfazer."
                  data-delete-title="Excluir {{ $user->name }}?">@csrf @method('DELETE')</form>
        @endif
    @endforeach
</section>
@endsection
