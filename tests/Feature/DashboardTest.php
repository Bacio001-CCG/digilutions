<?php

test('de landing toont de vandaag-pagina', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('vandaag')
            ->has('reservations')
            ->has('today'));
});

test('dashboard en vandaag wijzen naar dezelfde pagina', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('vandaag'));

    $this->get(route('vandaag'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('vandaag'));
});
