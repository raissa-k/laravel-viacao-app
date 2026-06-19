<?php

declare(strict_types=1);

test('empty state renderizado corretamente', function () {
    $this->blade('<x-empty-state />')
        ->assertSee('Nada encontrado');
});
