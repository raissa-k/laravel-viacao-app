<?php


declare(strict_types=1);

use App\DTOs\TerminalDTO;
use App\Models\Cidade;

test('deve instanciar TerminalDTO com sucesso priorizando o model CidadeLocal e aplicando Title Case', function () {
    $cidadeLocal = new Cidade([
        'nome' => 'Curitiba',
        'uf'   => 'PR'
    ]);

    $dadosBrutos = [
        'id'            => 12,
        'nome'          => 'RODOVIÁRIA DE CURITIBA',
        'endereco'      => 'av. presidente affonso camargo, 330',
        'telefone'      => '(41) 3320-3000',
        'funcionamento' => '24h',
        'servicos'      => ['Alimentação', 'Achados e Perdidos'],
        'cidade'        => 'São Paulo',
        'uf'            => 'SP',
    ];

    $dto         = TerminalDTO::fromArray($dadosBrutos, $cidadeLocal);

    expect($dto->id)->toBe(12)
        ->and($dto->nome)->toBe('Rodoviária De Curitiba')
        ->and($dto->endereco)->toBe('Av. Presidente Affonso Camargo, 330')
        ->and($dto->telefone)->toBe('(41) 3320-3000')
        ->and($dto->funcionamento)->toBe('24h')
        ->and($dto->servicos)->toBe(['Alimentação', 'Achados e Perdidos'])
        ->and($dto->cidade)->toBe('Curitiba')
        ->and($dto->uf)->toBe('PR');
});

test('deve fazer fallback para os dados da API com normalização se o model CidadeLocal for nulo', function () {
    $dadosBrutos = [
        'id'       => 5,
        'nome'     => 'terminal central',
        'endereco' => 'praça rui barbosa, s/n',
        'cidade'   => 'ponta grossa',
        'uf'       => 'pr',
    ];

    $dto         = TerminalDTO::fromArray($dadosBrutos, null);

    expect($dto->cidade)->toBe('Ponta Grossa')
        ->and($dto->uf)->toBe('PR');
});

test('deve aplicar fallbacks críticos absolutos se o model for nulo e a API omitir os campos', function () {
    $dadosBrutos = [];

    $dto         = TerminalDTO::fromArray($dadosBrutos, null);

    expect($dto->id)->toBe(0)
        ->and($dto->nome)->toBe('')
        ->and($dto->endereco)->toBe('')
        ->and($dto->telefone)->toBe('')
        ->and($dto->funcionamento)->toBe('')
        ->and($dto->servicos)->toBe([])
        ->and($dto->cidade)->toBe('Sem Cidade')
        ->and($dto->uf)->toBe('--');
});
