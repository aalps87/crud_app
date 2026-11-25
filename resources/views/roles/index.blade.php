@extends('layouts.app')
@section('title', 'Papéis')

@section('nav-trailing')
    @can('criar-papel')
        <a href="{{ route('roles.create') }}" class="nav-button is-filled pressable" aria-label="Novo papel"><x-icon name="plus" :size="18" :stroke="2.4" /><span class="nav-button-label">Novo papel</span></a>
    @endcan
@endsection

@section('content')
@php
    $me = auth()->user();
    $canEdit = $me->can('editar-papel');
    $canDelete = $me->can('excluir-papel');
@endphp
<header class="large-title" data-large-title>
    <h1>Papéis</h1>
    <p>O que cada perfil pode fazer dentro do hangar</p>
</header>

<div class="search-field">
    <x-icon name="search" :size="17" :stroke="2.2" class="search-glyph" />
    <input type="search" placeholder="Buscar papel ou permissão" aria-label="Buscar papéis" autocomplete="off" data-live-search="#lista-papeis">
</div>

<section class="group-section">
    <div class="group-header">
        <span>Perfis</span>
        @if ($roles->total() > 0)<span class="num">{{ $roles->firstItem() }}–{{ $roles->lastItem() }} de {{ $roles->total() }}</span>@endif
    </div>

    @if ($roles->isEmpty())
        <div class="group empty-state">
            <span class="empty-glyph tint-orange"><x-icon name="shield" :size="30" :stroke="1.7" /></span>
            <h2>Nenhum papel</h2>
            <p>Crie o primeiro papel para organizar o acesso.</p>
        </div>
    @else
        <ul class="group list" role="list" id="lista-papeis">
            @foreach ($roles as $role)
                @php
                    $isSuper = $role->name === 'Super Admin';
                    $rowEdit = ! $isSuper && $canEdit;
                    $rowDelete = ! $isSuper && $canDelete && ! $me->hasRole($role->name);
                    $perms = $role->permissions->count();
                @endphp
                <li class="row" @if($rowEdit || $rowDelete) data-swipe @endif style="--i: {{ $loop->index }}" data-search-text="{{ mb_strtolower($role->name.' '.$role->permissions->pluck('name')->join(' ')) }}">
                    @if ($rowEdit || $rowDelete)
                        <div class="swipe-actions" aria-hidden="true">
                            @if ($rowEdit)<a href="{{ route('roles.edit', $role) }}" class="swipe-action is-edit" tabindex="-1"><x-icon name="pencil" :size="19" /><span>Editar</span></a>@endif
                            @if ($rowDelete)<button type="submit" form="excluir-papel-{{ $role->id }}" class="swipe-action is-delete" tabindex="-1"><x-icon name="trash" :size="19" /><span>Excluir</span></button>@endif
                        </div>
                    @endif
                    <div class="row-front">
                        @if ($rowEdit)<a href="{{ route('roles.edit', $role) }}" class="row-link">@else<div class="row-link">@endif
                            <span class="thumb is-icon {{ $isSuper ? 'tint-orange' : 'tint-indigo' }}"><x-icon :name="$isSuper ? 'key' : 'shield'" :size="21" :stroke="2" /></span>
                            <span class="row-text">
                                <strong>{{ $role->name }}</strong>
                                <span>
                                    @if ($isSuper)
                                        Acesso total
                                    @else
                                        {{ $perms }} {{ $perms === 1 ? 'permissão' : 'permissões' }}
                                    @endif
                                </span>
                                <small class="num">{{ $role->users_count }} {{ $role->users_count === 1 ? 'usuário' : 'usuários' }}</small>
                            </span>
                            @if ($isSuper)
                                <span class="chip is-strong"><x-icon name="lock" :size="12" :stroke="2.4" />Fixo</span>
                            @else
                                <x-icon name="chevron-right" :size="16" :stroke="2.4" class="row-chevron" />
                            @endif
                        @if ($rowEdit)</a>@else</div>@endif
                        @if ($rowEdit || $rowDelete)
                            <button type="button" class="row-more pressable" aria-label="Ações para {{ $role->name }}" aria-haspopup="menu" aria-controls="menu-papel-{{ $role->id }}" data-menu-trigger><x-icon name="ellipsis" :size="20" /></button>
                        @endif
                    </div>
                    @if ($rowEdit || $rowDelete)
                        <div class="menu" id="menu-papel-{{ $role->id }}" role="menu" hidden>
                            @if ($rowEdit)<a role="menuitem" href="{{ route('roles.edit', $role) }}" class="menu-item"><span>Editar</span><x-icon name="pencil" :size="18" /></a>@endif
                            @if ($rowDelete)<button role="menuitem" type="submit" form="excluir-papel-{{ $role->id }}" class="menu-item is-destructive"><span>Excluir</span><x-icon name="trash" :size="18" /></button>@endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="group empty-state is-search-empty" hidden data-live-empty="#lista-papeis">
            <span class="empty-glyph tint-blue"><x-icon name="search" :size="30" :stroke="1.7" /></span>
            <h2>Nada nesta página</h2>
            <p>Nenhum papel desta página combina com a busca.</p>
        </div>
        {{ $roles->links('pagination.ios') }}
    @endif

    @foreach ($roles as $role)
        @if ($role->name !== 'Super Admin' && $canDelete && ! $me->hasRole($role->name))
            <form id="excluir-papel-{{ $role->id }}" action="{{ route('roles.destroy', $role) }}" method="post" hidden
                  data-delete-form="Quem tem o papel {{ $role->name }} perde essas permissões na hora. Não dá para desfazer."
                  data-delete-title="Excluir {{ $role->name }}?">@csrf @method('DELETE')</form>
        @endif
    @endforeach
</section>
@endsection
