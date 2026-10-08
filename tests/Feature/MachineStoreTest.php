<?php

use App\Models\Machine;

it('slaat een machine op', function (): void {
    $response = $this->post(route('machines.store'), [
        'name' => 'Hoogwerker 16m',
        'daily_price' => '185.50',
    ]);

    $response->assertRedirect(route('machines.index'));

    expect(Machine::query()->where([
        'name' => 'Hoogwerker 16m',
        'daily_price' => '185.50',
    ])->exists())->toBeTrue();
});

it('weigert een machine zonder naam of dagprijs', function (): void {
    $response = $this->from(route('machines.create'))
        ->post(route('machines.store'), [
            'name' => '',
            'daily_price' => '',
        ]);

    $response->assertRedirect(route('machines.create'));
    $response->assertSessionHasErrors(['name', 'daily_price']);
    expect(Machine::query()->count())->toBe(0);
});

it('weigert een negatieve dagprijs', function (): void {
    $response = $this->from(route('machines.create'))
        ->post(route('machines.store'), [
            'name' => 'Schaarlift',
            'daily_price' => '-10',
        ]);

    $response->assertSessionHasErrors('daily_price');
    expect(Machine::query()->count())->toBe(0);
});
