<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    // TelescopeServiceProvider é registrado condicionalmente em AppServiceProvider::register() apenas no ambiente local.
    // Não adicione aqui, isso o carregaria em todos os ambientes.
];
