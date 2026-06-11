<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Badge extends Component
{
    public function __construct(
        public string $rotulo,//dado obrigatorio se nao chmar quebra a pagina
        public string $tipo = 'convencional' //ele recebe convencional por padrao caso nada seja informado,se for blz,se nao usa esse
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.badge');//pega a rota do arquivo blade e manda ele exibir,junto com todos os dados que o construct catou
    }
}
//em sintee a classe recebe o bruto e o render aponta onde ela seraá lapidada!



//precisa ter o tipo:
//convencional(borda azul e fonte azul)
//executivo(amarelo pastel de fundo e amarelo na fonte),
//Semi-leito(,
//Leito
//OLHAR MENSAGEM DISCORD RAISSA COM FGS E BGS(FOREGROUND E BACKGROUND)COM VARIAVEIS !!!
