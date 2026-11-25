@php
    $selected = array_map('intval', old('permissions', $rolePermissions));
    $grupos = [
        'aeronave' => ['label' => 'Aeronaves', 'icon' => 'plane', 'tint' => 'blue'],
        'usuario' => ['label' => 'Usuários', 'icon' => 'users', 'tint' => 'indigo'],
        'papel' => ['label' => 'Papéis', 'icon' => 'shield', 'tint' => 'orange'],
    ];
    $acoes = ['ver' => 'Ver', 'criar' => 'Criar', 'editar' => 'Editar', 'excluir' => 'Excluir'];
@endphp
<section class="group-section">
    <div class="group-header"><span>Nome</span></div>
    <div class="group form-group">
        <label class="field @error('name') is-invalid @enderror">
            <span class="field-label">Papel</span>
            <input type="text" name="name" value="{{ old('name', $role?->name) }}" placeholder="Gerente de Frota" maxlength="250" required autocomplete="off">
            @error('name')<small class="field-error">{{ $message }}</small>@enderror
        </label>
    </div>
</section>

@foreach ($permissions->groupBy(fn ($permission) => \Illuminate\Support\Str::after($permission->name, '-')) as $group => $items)
    @php($g = $grupos[$group] ?? ['label' => ucfirst($group), 'icon' => 'key', 'tint' => 'gray'])
    <section class="group-section">
        <div class="group-header"><span>{{ $g['label'] }}</span></div>
        <div class="group form-group">
            @foreach ($items as $permission)
                @php($acao = \Illuminate\Support\Str::before($permission->name, '-'))
                <label class="field field-switch">
                    <span class="row-icon tint-{{ $g['tint'] }}"><x-icon :name="$g['icon']" :size="15" :stroke="2.2" /></span>
                    <span class="field-label">{{ $acoes[$acao] ?? ucfirst($acao) }} {{ mb_strtolower($g['label']) }}<small class="mono">{{ $permission->name }}</small></span>
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="switch" role="switch" @checked(in_array($permission->id, $selected, true))>
                </label>
            @endforeach
        </div>
    </section>
@endforeach
@error('permissions')<small class="field-error">{{ $message }}</small>@enderror
