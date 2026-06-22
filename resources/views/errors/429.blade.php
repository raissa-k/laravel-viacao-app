@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too Many Requests'))


<head>
    <meta charset="UTF-8">
    <meta  name="viewport" content="width=device-width">
    <title>Página não encontrada</title>
</head>
<body>
<div>
    <h1>429</h1>
    <p>Oops!página não encontrada</p>
    <p> a página que você está porcurando foi movida ou não existe</p>
    <a href="{{ route('viacoes.index') }}"> Voltar ao início </a>
</div>
</body>
