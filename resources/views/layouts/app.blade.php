<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0B1A21">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', 'Hangar') — AERIS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet" />
    <script>
        document.documentElement.classList.add('js');
        // Direção da transição entre páginas (push/pop com mola). Precisa rodar antes da primeira pintura.
        window.addEventListener('pagereveal', (event) => {
            if (!event.viewTransition) return;
            let dir = sessionStorage.getItem('vt-dir') || 'forward';
            sessionStorage.removeItem('vt-dir');
            const act = window.navigation?.activation;
            if (act?.navigationType === 'traverse' && act.from && act.entry) {
                dir = act.from.index > act.entry.index ? 'back' : 'forward';
            } else if (act?.navigationType === 'reload') {
                dir = 'fade';
            }
            if (matchMedia('(prefers-reduced-motion: reduce)').matches) dir = 'fade';
            event.viewTransition.types.add(dir);
        });
    </script>
    @vite(['resources/css/hangar.css', 'resources/js/hangar.js'])
</head>
<body class="hangar">
    @php
        $canAeronaves = auth()->user()->canAny(['ver-aeronave', 'criar-aeronave', 'editar-aeronave', 'excluir-aeronave']);
        $canUsers = auth()->user()->canAny(['criar-usuario', 'editar-usuario', 'excluir-usuario']);
        $canRoles = auth()->user()->canAny(['criar-papel', 'editar-papel', 'excluir-papel']);
        $initials = mb_strtoupper(collect(explode(' ', auth()->user()->name))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->join(''));
        $roleLabel = auth()->user()->getRoleNames()->join(', ') ?: 'Sem papel';
        $nav = array_filter([
            $canAeronaves ? ['route' => 'aeronaves.index', 'match' => 'aeronaves.*', 'label' => 'Hangar', 'icon' => 'plane'] : null,
            $canUsers ? ['route' => 'users.index', 'match' => 'users.*', 'label' => 'Usuários', 'icon' => 'users'] : null,
            $canRoles ? ['route' => 'roles.index', 'match' => 'roles.*', 'label' => 'Papéis', 'icon' => 'shield'] : null,
        ]);
    @endphp

    <header class="topbar" data-navbar>
        <a href="{{ url('/') }}" class="brand pressable" data-vt="fade" aria-label="AERIS, voltar para o site">
            <span class="brand-mark"><x-icon name="plane" :size="15" :stroke="2.2" /></span>
            <span class="brand-word">AERIS</span>
            <span class="brand-sub mono">Hangar</span>
        </a>
        <span class="topbar-title" aria-hidden="true">@yield('title', 'Hangar')</span>

        <nav class="topnav" aria-label="Navegação principal">
            @foreach ($nav as $item)
                @php($active = request()->routeIs($item['match']))
                <a href="{{ route($item['route']) }}" class="topnav-link pressable {{ $active ? 'is-active' : '' }}" data-vt="fade" @if($active) aria-current="page" @endif>
                    @if ($active)<span class="topnav-pill" aria-hidden="true"></span>@endif
                    <span class="topnav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <button type="button" class="account pressable" data-account-sheet data-name="{{ auth()->user()->name }}" data-role="{{ $roleLabel }}" aria-label="Conta de {{ auth()->user()->name }}">
            <span class="account-text"><strong>{{ auth()->user()->name }}</strong><small class="mono">{{ $roleLabel }}</small></span>
            <span class="avatar avatar-sm" data-hue="{{ auth()->id() % 6 }}">{{ $initials }}</span>
        </button>
        <form action="{{ route('logout') }}" method="post" data-logout-form hidden>@csrf</form>
    </header>

    <main class="content" id="conteudo">
        @if (View::hasSection('nav-leading') || View::hasSection('nav-trailing'))
            <div class="toolbar">
                <div class="toolbar-leading">@yield('nav-leading')</div>
                <div class="toolbar-trailing">@yield('nav-trailing')</div>
            </div>
        @endif
        @yield('content')
    </main>

    <nav class="dock" aria-label="Navegação">
        @foreach ($nav as $item)
            @php($active = request()->routeIs($item['match']))
            <a href="{{ route($item['route']) }}" class="dock-item pressable {{ $active ? 'is-active' : '' }}" data-vt="fade" @if($active) aria-current="page" @endif>
                @if ($active)<span class="dock-pill" aria-hidden="true"></span>@endif
                <x-icon :name="$item['icon']" :size="21" :stroke="1.9" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        <button type="button" class="dock-item pressable" data-account-sheet data-name="{{ auth()->user()->name }}" data-role="{{ $roleLabel }}">
            <span class="avatar avatar-xs" data-hue="{{ auth()->id() % 6 }}">{{ $initials }}</span><span>Conta</span>
        </button>
    </nav>

    <div class="banner-stack" aria-live="polite">
        @if (session('success'))
            <div class="banner" role="status" data-banner>
                <span class="banner-icon"><x-icon name="check" :size="14" :stroke="3" /></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
    </div>

    <dialog class="sheet" data-sheet aria-labelledby="sheet-title">
        <div class="sheet-panel">
            <div class="sheet-group">
                <div class="sheet-header"><strong id="sheet-title" data-sheet-title></strong><p data-sheet-message></p></div>
                <div class="sheet-actions" data-sheet-actions></div>
            </div>
            <button type="button" class="sheet-cancel pressable" data-sheet-cancel>Cancelar</button>
        </div>
    </dialog>
</body>
</html>
