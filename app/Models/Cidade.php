<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cidade extends Model
{
    use HasFactory;

    protected $table = 'cidades';

    protected $fillable = ['api_id', 'nome', 'uf'];

    public function viacoes(): HasMany
    {
        return $this->hasMany(Viacao::class);
    }
}
