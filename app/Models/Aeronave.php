<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\File;

class Aeronave extends Model
{
    public $timestamps = false;

    protected $fillable = ['prefixo', 'modelo', 'tripulacao', 'data_fab', 'foto'];

    protected $appends = ['is_public'];

    protected function casts(): array
    {
        return ['data_fab' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Aeronave $aeronave) => $aeronave->deleteImageFile());
    }

    public function publicacao(): HasOne
    {
        return $this->hasOne(AeronavePublicacao::class);
    }

    public function scopePublicas(Builder $query): Builder
    {
        return $query->whereHas('publicacao', fn (Builder $query) => $query->where('publico', true));
    }

    public function getIsPublicAttribute(): bool
    {
        return (bool) $this->publicacao?->publico;
    }

    public function deleteImageFile(?string $path = null): void
    {
        $relativePath = $path ?? $this->foto;

        if ($relativePath && str_starts_with($relativePath, 'assets/aeronaves/')) {
            File::delete(public_path($relativePath));
        }
    }

    public function hasImage(): bool
    {
        return $this->foto
            && str_starts_with($this->foto, 'assets/aeronaves/')
            && File::exists(public_path($this->foto));
    }
}
