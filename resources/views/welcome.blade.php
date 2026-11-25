<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e7edf0">
    <meta name="description" content="AERIS. Um novo olhar para a aviação. Explore o hangar e encontre cada aeronave em seu horizonte.">
    <title>AERIS — Um novo horizonte.</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/aeris-flight.png') }}">
    <script>try{if(!matchMedia('(prefers-reduced-motion: reduce)').matches&&localStorage.getItem('aeris-motion')!=='paused')document.documentElement.classList.add('js-intro')}catch(e){}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php $authMode = $errors->register->any() || request()->boolean('cadastro') ? 'register' : 'login'; @endphp
<body class="flight-page">
    <a href="#visao" class="skip-flight">Pular para o conteúdo</a>
    <header class="flight-nav" data-nav>
        <a class="flight-brand" href="#inicio" aria-label="Aeris, início"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M3 25 16 4l13 21h-8l-5-9-5 9Z" fill="currentColor"/><path d="M11 28h10" stroke="currentColor" stroke-width="3"/></svg>AERIS<span>AVIATION SYSTEM</span></a>
        <nav aria-label="Navegação principal"><a href="#visao">A visão</a><a href="#hangar">O hangar</a><a href="#operacao">A operação</a></nav>
        @auth
            <a class="flight-access" href="{{ route('aeronaves.index') }}">Meu hangar <span>↗</span></a>
        @else
            <button class="flight-access js-open-login" type="button">Acessar hangar <span>↗</span></button>
        @endauth
    </header>
    <main>
        <section class="flight-hero" id="inicio" aria-labelledby="flight-title">
            <div class="flight-scene"><img src="{{ asset('images/aeris-flight.png') }}" alt="Jato branco em voo sobre uma cadeia de montanhas entre nuvens" fetchpriority="high" width="1536" height="1024"></div>
            <div class="flight-wash"></div>
            <div class="hero-dim" aria-hidden="true"></div>
            <div class="hero-shutter hero-shutter-top" aria-hidden="true"></div>
            <div class="hero-shutter hero-shutter-bottom" aria-hidden="true"></div>
            <div class="hero-horizon" aria-hidden="true"></div>
            <div class="hero-heading"><p class="flight-label"><span class="orange-dot"></span> PERSPECTIVA É TUDO.</p><h1 id="flight-title"><span class="hl"><span>Um novo</span></span><span class="hl"><span>horizonte.</span></span><sup>↗</sup></h1></div>
            <div class="hero-intro"><p>O fascínio de voar.<br>A clareza de ter tudo à vista.</p><a class="round-link" href="#hangar"><span>Explore o hangar</span><i aria-hidden="true">↗</i></a></div>
            <div class="hero-caption"><span class="crosshair" aria-hidden="true">+</span><div>AERIS / ESTUDO DE VOO<small>Uma perspectiva acima do comum.</small><span class="hero-alt">ALT <b data-alt>41.000</b> FT · CRZ</span></div></div>
            <div class="hero-baseline"><a href="#visao"><span class="scroll-stem" aria-hidden="true"></span> ROLE PARA EXPLORAR</a><span>AVIAÇÃO, EM OUTRA PERSPECTIVA</span><button type="button" data-motion-toggle aria-pressed="false">Pausar movimento <span aria-hidden="true">Ⅱ</span></button></div>
        </section>
        <section class="flight-vision" id="visao" data-route>
            <div class="vision-stage">
                <svg class="route" aria-hidden="true" focusable="false"><path class="route-fill"/><g class="route-plane"><path d="M14 0 L4 -2 L-2 -11 L-6 -11 L-2 -2 L-9 -2 L-12 -6 L-14 -6 L-12 0 L-14 6 L-12 6 L-9 2 L-2 2 L-6 11 L-2 11 L4 2 Z"/></g></svg>
                <div class="section-rail"><span>01 / A VISÃO</span><span>DO CÉU AO SOLO</span><span>↓</span></div>
                <h2 class="vision-heading"><span class="vision-a" data-words>Paixão pelo voo.</span> <span class="vision-b" data-words>Precisão em <em>cada detalhe.</em></span></h2>
                <p class="vision-copy" data-words>Por trás de cada aeronave, existe uma história. Um modelo, uma tripulação, um primeiro voo. O AERIS reúne o essencial para você conhecer o que está no hangar — e cuidar do que vem a seguir.</p>
                <div class="route-runout" data-words="junto">UM PONTO DE PARTIDA.<br>INFINITAS PERSPECTIVAS.</div>
            </div>
        </section>
        <section class="flight-hangar" id="hangar" aria-labelledby="hangar-title">
            <div class="section-rail"><span>02 / O HANGAR</span><span><i class="orange-dot"></i> INVENTÁRIO PÚBLICO</span><span>↘</span></div>
            <div class="hangar-heading" data-rise><h2 id="hangar-title">Em solo.<br><em>Em destaque.</em></h2><p>Conheça as aeronaves do nosso inventário público. Cada prefixo, uma identidade.</p></div>
            <div class="hangar-board">
                @if($publicAeronaves->isNotEmpty())
                    @php $total = str_pad($publicAeronaves->count(), 2, '0', STR_PAD_LEFT); @endphp
                    <div class="gate" data-gate>
                        <div class="gate-sticky">
                            <div class="board-header"><span>AERONAVES PUBLICADAS · ROLE PARA EMBARCAR</span><span class="gate-count" aria-hidden="true"><b data-gate-current>01</b> / {{ $total }}</span></div>
                            <div class="gate-viewport" data-gate-viewport>
                                <ol class="gate-rail" data-gate-rail>
                                    @foreach($publicAeronaves as $aeronave)
                                        @php $idx = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT); @endphp
                                        <li class="pass" tabindex="0" aria-label="Aeronave {{ $aeronave->prefixo }}, {{ $aeronave->modelo }}">
                                            <div class="pass-main">
                                                <figure class="pass-window">
                                                    @if($aeronave->hasImage())<img src="{{ asset($aeronave->foto) }}" alt="{{ $aeronave->modelo }} em solo" loading="lazy" decoding="async">@else<span class="pass-window-empty" aria-hidden="true">✈</span>@endif
                                                    <span class="pass-shade" aria-hidden="true"></span>
                                                    <span class="pass-top"><span>AERIS · REGISTRO {{ $idx }}</span><span class="pass-stamp">EMBARQUE</span></span>
                                                    @if($credit = (config('fotos')[$aeronave->foto] ?? null))<figcaption>{{ $credit }} · Wikimedia Commons</figcaption>@endif
                                                </figure>
                                                <div class="pass-body"><strong class="pass-prefix">@foreach(mb_str_split($aeronave->prefixo) as $ch)<span class="ch" style="--i:{{ $loop->index }}">{{ $ch }}</span>@endforeach</strong>
                                                <span class="pass-model">{{ $aeronave->modelo }}</span>
                                                <dl class="pass-data"><div><dt>Tripulação</dt><dd>{{ $aeronave->tripulacao }} pessoas</dd></div><div><dt>Fabricação</dt><dd>{{ $aeronave->data_fab?->format('d/m/Y') }}</dd></div></dl></div>
                                            </div>
                                            <div class="pass-stub" aria-hidden="true"><span>PORTÃO</span><b>{{ $idx }}</b><em>{{ $aeronave->prefixo }}</em><i class="pass-code"></i></div>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                            <div class="gate-progress" aria-hidden="true"><i data-gate-bar></i></div>
                        </div>
                    </div>
                @else
                    <div class="board-header"><span>AERONAVES PUBLICADAS</span><span>00 EM EXIBIÇÃO</span></div>
                    <div class="hangar-empty"><div class="empty-orbit" aria-hidden="true"><span>↗</span></div><div><span class="flight-label">ESPAÇO PARA NOVAS HISTÓRIAS</span><h3>O próximo voo<br>começa aqui.</h3><p>Nenhuma aeronave pública no momento.<br>Os registros liberados aparecerão neste hangar.</p></div></div>
                @endif
                <div class="board-footer"><span>Somente aeronaves com publicação autorizada.</span><a href="#operacao">Conheça a operação <span>↗</span></a></div>
            </div>
        </section>
        <section class="flight-operations" id="operacao" data-ops>
            <div class="section-rail"><span>03 / A OPERAÇÃO</span><span>CLAREZA EM CADA MOVIMENTO</span><span>↓</span></div>
            <div class="ops-grid">
                <div class="ops-aside">
                    <h2 data-rise>O voo encanta.<br>O cuidado <em>sustenta.</em></h2>
                    <div class="ops-instrument" aria-hidden="true">
                        <svg viewBox="0 0 240 240" class="ops-dial">
                            <g class="ops-card">
                                <circle cx="120" cy="120" r="104" class="ops-ticks"/>
                                <circle cx="120" cy="120" r="104" class="ops-ticks-major"/>
                                <text x="120" y="38">N</text><text x="206" y="125">E</text><text x="120" y="212">S</text><text x="34" y="125">W</text>
                            </g>
                            <path class="ops-lubber" d="M120 4 l-6 12 h12 Z"/>
                            <g class="ops-glyph" transform="translate(120 120) rotate(-90) scale(2.2)"><path d="M14 0 L4 -2 L-2 -11 L-6 -11 L-2 -2 L-9 -2 L-12 -6 L-14 -6 L-12 0 L-14 6 L-12 6 L-9 2 L-2 2 L-6 11 L-2 11 L4 2 Z"/></g>
                        </svg>
                        <div class="ops-readout"><span>HDG</span><strong data-ops-hdg>000</strong><span data-ops-step>01 / 03</span></div>
                    </div>
                    <p class="ops-lead">Do primeiro cadastro à próxima atualização. Um lugar para manter cada detalhe em ordem.</p>
                </div>
                <ol class="ops-steps">
                    <li class="ops-step is-active" data-hdg="000">
                        <div class="ops-step-head"><span class="ops-num">01</span><span class="ops-wpt">WPT · IDNT</span></div>
                        <h3>Dar identidade.</h3>
                        <p>Cadastre prefixo, modelo, tripulação e data de fabricação. Adicione uma foto para reconhecer cada aeronave à primeira vista.</p>
                        <ul><li>Prefixo</li><li>Modelo</li><li>Tripulação</li><li>Foto</li></ul>
                    </li>
                    <li class="ops-step" data-hdg="120">
                        <div class="ops-step-head"><span class="ops-num">02</span><span class="ops-wpt">WPT · VIEW</span></div>
                        <h3>Ter perspectiva.</h3>
                        <p>Consulte o inventário, encontre um registro e veja as informações da aeronave reunidas em uma leitura direta.</p>
                        <ul><li>Inventário</li><li>Busca</li><li>Ficha completa</li></ul>
                    </li>
                    <li class="ops-step" data-hdg="240">
                        <div class="ops-step-head"><span class="ops-num">03</span><span class="ops-wpt">WPT · CARE</span></div>
                        <h3>Cuidar do percurso.</h3>
                        <p>Atualize informações, escolha quais aeronaves aparecem no hangar público e remova registros quando necessário.</p>
                        <ul><li>Editar</li><li>Publicar</li><li>Arquivar</li></ul>
                    </li>
                </ol>
            </div>
        </section>
        @php
            // Pista em perspectiva: luzes de borda e faixas centrais, mais espaçadas perto de quem olha.
            $hw = fn ($y) => 60 + 560 * ($y / 420);
            $lights = [];
            for ($k = 0; $k < 11; $k++) {
                $s = 1 - $k / 10;              // 1 = perto, 0 = horizonte
                $y = 6 + 414 * $s * $s;
                $lights[] = ['y' => $y, 'x' => $hw($y), 'r' => 1.2 + 5.2 * ($y / 420), 'i' => $k];
            }
            $dashes = [];
            for ($k = 0; $k < 7; $k++) {
                $a = ($k + .15) / 7; $b = ($k + .6) / 7;
                $ya = 6 + 414 * $a * $a; $yb = 6 + 414 * $b * $b;
                $dashes[] = [$ya, $yb, 1 + 7 * ($ya / 420), 1 + 7 * ($yb / 420)];
            }
        @endphp
        <section class="flight-finale" data-rise-scope>
            <svg class="runway" viewBox="0 0 1000 420" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false" data-rise>
                <defs><radialGradient id="rwy-glow" cx="50%" cy="0%" r="60%"><stop offset="0" stop-color="#ee552d" stop-opacity=".35"/><stop offset="1" stop-color="#ee552d" stop-opacity="0"/></radialGradient></defs>
                <rect x="0" y="-40" width="1000" height="300" fill="url(#rwy-glow)"/>
                <path class="rwy-deck" d="M440 0 H560 L1120 420 H-120 Z"/>
                @foreach ($dashes as [$ya, $yb, $wa, $wb])
                    <path class="rwy-dash" d="M{{ 500 - $wa }} {{ $ya }} H{{ 500 + $wa }} L{{ 500 + $wb }} {{ $yb }} H{{ 500 - $wb }} Z"/>
                @endforeach
                @foreach ($lights as $l)
                    <circle class="rwy-light" style="--i:{{ $l['i'] }}" cx="{{ 500 - $l['x'] }}" cy="{{ $l['y'] }}" r="{{ $l['r'] }}"/>
                    <circle class="rwy-light" style="--i:{{ $l['i'] }}" cx="{{ 500 + $l['x'] }}" cy="{{ $l['y'] }}" r="{{ $l['r'] }}"/>
                @endforeach
                <text class="rwy-number" x="500" y="402">09</text>
            </svg>
            <div class="finale-top"><p class="flight-label"><span class="orange-dot"></span> SEU PRÓXIMO PONTO DE PARTIDA</p><span class="finale-clear">LIBERADO PARA DECOLAGEM</span></div>
            <h2 data-rise>Assuma<br>o comando<span>.</span></h2>
            @auth<a href="{{ route('aeronaves.index') }}" class="finale-button"><span>Entrar no hangar</span><i aria-hidden="true">↗</i></a>@else<button type="button" class="finale-button js-open-login"><span>Entrar no hangar</span><i aria-hidden="true">↗</i></button>@endauth
            <div class="finale-bottom"><span>SEU HANGAR. SUA PERSPECTIVA.</span><span>RWY 09 · AERIS / EST. {{ date('Y') }}</span></div>
        </section>
    </main>
    <footer class="flight-footer"><a class="flight-brand" href="#inicio">AERIS <span>AVIATION SYSTEM</span></a><p>Um projeto acadêmico. Uma paixão pela aviação.</p><a href="#inicio">Voltar ao topo ↑</a></footer>
    <dialog class="login-dialog" data-login-dialog data-open-on-load="{{ $errors->any() || $errors->register->any() || request()->boolean('login') || request()->boolean('cadastro') ? 'true' : 'false' }}" aria-labelledby="login-title">
        <div class="login-shell">
            <button class="dialog-close js-close-login" type="button" aria-label="Fechar login">×</button>
            <div class="login-aside">
                <span class="login-coordinate">23° 33′ S / AERIS OS</span>
                <div><p class="kicker mint">Área operacional</p><h2>Bem-vindo<br>ao hangar.</h2><p>Acesse o painel para gerenciar o inventário aeronáutico.</p></div>
                <div class="login-status"><i></i> Conexão segura</div>
            </div>
            <div class="login-form-wrap">
                <div class="auth-switch" role="tablist" aria-label="Acesso">
                    <button type="button" role="tab" class="js-auth-switch" data-auth-target="login" aria-selected="{{ $authMode === 'login' ? 'true' : 'false' }}">Entrar</button>
                    <button type="button" role="tab" class="js-auth-switch" data-auth-target="register" aria-selected="{{ $authMode === 'register' ? 'true' : 'false' }}">Criar conta</button>
                </div>
                <div class="auth-panel" data-auth-panel="login" @if($authMode !== 'login') hidden @endif>
                    <div class="login-form-head"><span>IDENTIFICAÇÃO / 01</span><h3 id="login-title">Entrar no sistema</h3><p>Use seu e-mail e senha para continuar.</p></div>
                    <form class="login-form" action="{{ route('login') }}" method="post" data-login-form>
                        @csrf
                        <label><span>E-mail</span><input type="email" name="email" value="{{ $authMode === 'login' ? old('email') : '' }}" autocomplete="email" placeholder="nome@aeris.com" required class="@error('email') is-invalid @enderror">@error('email')<small class="login-error">{{ $message }}</small>@enderror</label>
                        <label><span>Senha</span><span class="password-field"><input type="password" name="password" autocomplete="current-password" placeholder="••••••••" minlength="4" required><button type="button" class="toggle-password" aria-label="Mostrar senha">VER</button></span></label>
                        <button class="login-submit" type="submit"><span>Acessar hangar</span><i>→</i></button>
                    </form>
                    <p class="login-hint">Ainda não tem conta? <button type="button" class="link-button js-auth-switch" data-auth-target="register">Crie uma agora</button>.</p>
                </div>
                <div class="auth-panel" data-auth-panel="register" @if($authMode !== 'register') hidden @endif>
                    <div class="login-form-head"><span>NOVO TRIPULANTE / 02</span><h3>Criar conta</h3><p>Contas novas entram como <strong>Usuário</strong> e podem consultar o inventário.</p></div>
                    <form class="login-form" action="{{ route('register') }}" method="post" data-login-form>
                        @csrf
                        <label><span>Nome</span><input type="text" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Seu nome" maxlength="250" required class="@error('name', 'register') is-invalid @enderror">@error('name', 'register')<small class="login-error">{{ $message }}</small>@enderror</label>
                        <label><span>E-mail</span><input type="email" name="email" value="{{ $authMode === 'register' ? old('email') : '' }}" autocomplete="email" placeholder="nome@exemplo.com" required class="@error('email', 'register') is-invalid @enderror">@error('email', 'register')<small class="login-error">{{ $message }}</small>@enderror</label>
                        <div class="auth-row">
                            <label><span>Senha</span><span class="password-field"><input type="password" name="password" autocomplete="new-password" placeholder="mín. 8 caracteres" minlength="8" required class="@error('password', 'register') is-invalid @enderror"><button type="button" class="toggle-password" aria-label="Mostrar senha">VER</button></span>@error('password', 'register')<small class="login-error">{{ $message }}</small>@enderror</label>
                            <label><span>Confirmar senha</span><input type="password" name="password_confirmation" autocomplete="new-password" placeholder="repita a senha" minlength="8" required></label>
                        </div>
                        <button class="login-submit" type="submit"><span>Criar minha conta</span><i>→</i></button>
                    </form>
                    <p class="login-hint">Já tem conta? <button type="button" class="link-button js-auth-switch" data-auth-target="login">Entrar</button>.</p>
                </div>
            </div>
        </div>
    </dialog>
</body>
</html>
