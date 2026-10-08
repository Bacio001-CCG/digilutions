<?php

use App\Services\Rental\WorkdayCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

beforeEach(function (): void {
    $this->calendar = new WorkdayCalendar;
});

it('herkent weekdagen als werkdag en weekend niet', function (): void {
    expect($this->calendar->isWorkday(Carbon::parse('2026-10-05')))->toBeTrue()
        ->and($this->calendar->isWorkday(Carbon::parse('2026-10-10')))->toBeFalse()
        ->and($this->calendar->isWorkday(Carbon::parse('2026-10-11')))->toBeFalse();
});

it('telt maandag tot en met dinsdag als twee werkdagen', function (): void {
    expect($this->calendar->countWorkdaysInclusive(
        Carbon::parse('2026-10-05'),
        Carbon::parse('2026-10-06'),
    ))->toBe(2);
});

it('telt één dag als één werkdag', function (): void {
    expect($this->calendar->countWorkdaysInclusive(
        Carbon::parse('2026-10-05'),
        Carbon::parse('2026-10-05'),
    ))->toBe(1);
});

it('slaat weekend over bij werkdagentelling', function (): void {
    expect($this->calendar->countWorkdaysInclusive(
        Carbon::parse('2026-10-09'),
        Carbon::parse('2026-10-12'),
    ))->toBe(2);
});

it('telt een volle werkweek als vijf werkdagen', function (): void {
    expect($this->calendar->countWorkdaysInclusive(
        Carbon::parse('2026-10-05'),
        Carbon::parse('2026-10-09'),
    ))->toBe(5);
});

it('werkt met immutable Carbon-datums', function (): void {
    expect($this->calendar->countWorkdaysInclusive(
        CarbonImmutable::parse('2026-10-05'),
        CarbonImmutable::parse('2026-10-06'),
    ))->toBe(2);
});

it('gooit een fout als de einddatum vóór de startdatum ligt', function (): void {
    $this->calendar->countWorkdaysInclusive(
        Carbon::parse('2026-10-06'),
        Carbon::parse('2026-10-05'),
    );
})->throws(InvalidArgumentException::class);

it('bepaalt de keuringsdag als eerstvolgende werkdag na terugkomst', function (): void {
    expect($this->calendar->nextWorkdayAfter(Carbon::parse('2026-10-08'))->toDateString())
        ->toBe('2026-10-09')
        ->and($this->calendar->nextWorkdayAfter(Carbon::parse('2026-10-09'))->toDateString())
        ->toBe('2026-10-12')
        ->and($this->calendar->nextWorkdayAfter(CarbonImmutable::parse('2026-10-09'))->toDateString())
        ->toBe('2026-10-12');
});
