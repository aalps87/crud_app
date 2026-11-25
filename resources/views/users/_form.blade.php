<section class="group-section">
    <div class="group-header"><span>Conta</span></div>
    <div class="group form-group">
        <label class="field @error('name') is-invalid @enderror">
            <span class="field-label">Nome</span>
            <input type="text" name="name" value="{{ old('name', $user?->name) }}" placeholder="Ana Souza" maxlength="250" required autocomplete="off">
            @error('name')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <label class="field @error('email') is-invalid @enderror">
            <span class="field-label">E-mail</span>
            <input type="email" name="email" value="{{ old('email', $user?->email) }}" placeholder="nome@aeris.com" maxlength="250" required autocomplete="off" spellcheck="false">
            @error('email')<small class="field-error">{{ $message }}</small>@enderror
        </label>
    </div>
</section>

<section class="group-section">
    <div class="group-header"><span>Senha</span></div>
    <div class="group form-group">
        <label class="field @error('password') is-invalid @enderror">
            <span class="field-label">{{ $user ? 'Nova senha' : 'Senha' }}</span>
            <input type="password" name="password" minlength="8" @required(! $user) placeholder="{{ $user ? 'Manter a atual' : 'Obrigatória' }}" autocomplete="new-password">
            @error('password')<small class="field-error">{{ $message }}</small>@enderror
        </label>
        <label class="field">
            <span class="field-label">Confirmar</span>
            <input type="password" name="password_confirmation" minlength="8" @required(! $user) placeholder="Repita a senha" autocomplete="new-password">
        </label>
    </div>
    <p class="group-footer">Pelo menos 8 caracteres.@if ($user) Deixe em branco para manter a senha atual.@endif</p>
</section>

<section class="group-section">
    <div class="group-header"><span>Papéis</span></div>
    @php($selected = old('roles', $userRoles))
    <div class="group form-group">
        @forelse ($roles as $role)
            <label class="field field-switch">
                <span class="row-icon tint-orange"><x-icon name="shield" :size="15" :stroke="2.2" /></span>
                <span class="field-label">{{ $role }}</span>
                <input type="checkbox" name="roles[]" value="{{ $role }}" class="switch" role="switch" @checked(in_array($role, $selected, true))>
            </label>
        @empty
            <p class="field field-note">Nenhum papel disponível.</p>
        @endforelse
    </div>
    <p class="group-footer">O papel define o que a pessoa pode ver e alterar.</p>
    @error('roles')<small class="field-error">{{ $message }}</small>@enderror
</section>
