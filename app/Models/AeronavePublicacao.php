<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AeronavePublicacao extends Model
{
    protected $table = 'aeronave_publicacoes';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'aeronave_id';

    protected $fillable = ['publico'];

    protected function casts(): array
    {
        return ['publico' => 'boolean'];
    }
}
