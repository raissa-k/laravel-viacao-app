<?php

declare(strict_types=1);

namespace App\Enums;

enum Categoria: string
{
    case Convencional = 'convencional';
    case Executivo    = 'executivo';
    case Semileito    = 'semileito';
    case Leito        = 'leito';

    /**  rótulo legível para exibição na view.
         ex: Categoria::Executivo->rotulo() => 'Executivo' */
    public function rotulo(): string
    {
        return match ($this) {
            self::Convencional => 'Convencional',
            self::Executivo    => 'Executivo',
            self::Semileito    => 'Semileito',
            self::Leito        => 'Leito',
        };
    }

    /**  tipo de badge para uso no componente <x-badge> nas views.*/
    public function tipoBadge(): string
    {
        return match ($this) {
            self::Convencional => 'convencional',
            self::Executivo    => 'executivo',
            self::Semileito    => 'semileito',
            self::Leito        => 'leito',
        };
    }
}
