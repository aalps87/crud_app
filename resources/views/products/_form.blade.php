@php($hasImage = $aeronave?->hasImage())
<aside class="form-photo">
    <div class="photo-well @error('image') is-invalid @enderror" data-image-upload>
        <input id="aeronave-image" type="file" name="image" accept="image/jpeg,image/png" class="sr-only" data-image-input>
        <label for="aeronave-image" class="photo-drop pressable-soft {{ $hasImage ? 'has-image' : '' }}" data-photo-drop>
            <img src="{{ $hasImage ? asset($aeronave->foto) : '' }}" alt="{{ $hasImage ? 'Foto atual de '.$aeronave->modelo : '' }}" data-image-preview @unless($hasImage) hidden @endunless>
            <span class="photo-empty">
                <span class="empty-orbit" aria-hidden="true"><x-icon name="image" :size="30" :stroke="1.5" /></span>
                <strong>Solte a foto aqui</strong>
                <small class="mono">ou clique para escolher</small>
            </span>
            <span class="photo-corners" aria-hidden="true"></span>
        </label>
        <div class="photo-meta">
            <span><strong data-file-name>{{ $hasImage ? 'Foto atual' : 'Nenhuma foto' }}</strong><small class="mono" data-file-status>JPEG ou PNG, até 5 MB</small></span>
            <label for="aeronave-image" class="button is-ghost pressable"><x-icon name="image" :size="16" :stroke="2.1" /><span>{{ $hasImage ? 'Substituir imagem' : 'Selecionar imagem' }}</span></label>
        </div>
        @error('image')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</aside>

<div class="form-fields">
    <section class="group-section">
        <div class="group-header"><span>01 · Identificação</span></div>
        <div class="group form-group">
            <label class="field @error('prefixo') is-invalid @enderror">
                <span class="field-label">Prefixo</span>
                <input type="text" name="prefixo" value="{{ old('prefixo', $aeronave?->prefixo) }}" placeholder="PT-ABC" maxlength="10" required autocapitalize="characters" autocomplete="off" spellcheck="false" data-uppercase class="is-display">
                @error('prefixo')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label class="field @error('modelo') is-invalid @enderror">
                <span class="field-label">Modelo</span>
                <input type="text" name="modelo" value="{{ old('modelo', $aeronave?->modelo) }}" placeholder="Cessna 172" maxlength="30" required autocomplete="off">
                @error('modelo')<small class="field-error">{{ $message }}</small>@enderror
            </label>
        </div>
    </section>

    <section class="group-section">
        <div class="group-header"><span>02 · Operação</span></div>
        <div class="group form-group is-pair">
            <div class="field @error('tripulacao') is-invalid @enderror">
                <label class="field-label" for="tripulacao">Tripulação</label>
                <div class="stepper" data-stepper>
                    <button type="button" class="pressable" data-step="-1" aria-label="Diminuir tripulação"><x-icon name="minus" :size="15" :stroke="2.6" /></button>
                    <input id="tripulacao" type="text" name="tripulacao" value="{{ old('tripulacao', $aeronave?->tripulacao ?? 1) }}" inputmode="numeric" pattern="[1-9][0-9]*" minlength="1" maxlength="4" min="1" max="1000" required data-positive-integer class="num">
                    <button type="button" class="pressable" data-step="1" aria-label="Aumentar tripulação"><x-icon name="plus" :size="15" :stroke="2.6" /></button>
                </div>
                @error('tripulacao')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <label class="field @error('data_fab') is-invalid @enderror">
                <span class="field-label">Fabricação</span>
                <input type="date" name="data_fab" value="{{ old('data_fab', $aeronave?->data_fab?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="num">
                @error('data_fab')<small class="field-error">{{ $message }}</small>@enderror
            </label>
        </div>
    </section>

    <section class="group-section">
        <div class="group-header"><span>03 · Visibilidade</span></div>
        <div class="group form-group">
            <input type="hidden" name="is_public" value="0">
            <label class="field field-switch">
                <span class="row-icon"><x-icon name="globe" :size="15" :stroke="2.2" /></span>
                <span class="field-label">Mostrar na landing page<small>Aeronaves públicas aparecem para qualquer visitante.</small></span>
                <input type="checkbox" name="is_public" value="1" class="switch" role="switch" @checked((bool) old('is_public', $aeronave?->is_public ?? false))>
            </label>
        </div>
        @error('is_public')<small class="field-error">{{ $message }}</small>@enderror
    </section>
</div>
