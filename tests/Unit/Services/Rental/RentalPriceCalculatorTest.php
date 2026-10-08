<?php

use App\Services\Rental\RentalPriceCalculator;
use App\Services\Rental\WorkdayCalendar;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

beforeEach(function (): void {
    $this->calculator = new RentalPriceCalculator(new WorkdayCalendar);
});

describe('offerte bij reserveren', function (): void {
    it('rekent maandag ophalen en dinsdag terugbrengen als twee betaalde dagen', function (): void {
        expect($this->calculator->calculate(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-06'),
        ))->toBe('200.00');
    });

    it('past vijf halen vier betalen toe op vijf werkdagen', function (): void {
        $breakdown = $this->calculator->estimateForOnTimeReturn(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-09'),
        );

        expect($breakdown->volumeDiscountApplied)->toBeTrue()
            ->and($breakdown->billableWorkdays)->toBe(4)
            ->and($breakdown->totalAmount)->toBe('400.00');
    });

    it('telt weekenddagen niet mee in de prijs', function (): void {
        expect($this->calculator->calculate(
            '95.00',
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-12'),
        ))->toBe('190.00');
    });
});

describe('volumekorting', function (): void {
    it('past 5-halen-4-betalen toe per volle set van vijf werkdagen', function (int $workdays, int $billable): void {
        expect($this->calculator->applyVolumeDiscount($workdays))->toBe($billable);
    })->with([
        'geen dagen' => [0, 0],
        'één dag' => [1, 1],
        'vier dagen zonder set' => [4, 4],
        'één volle set' => [5, 4],
        'zes dagen' => [6, 5],
        'negen dagen' => [9, 8],
        'twee volle sets' => [10, 8],
        'drie volle sets' => [15, 12],
    ]);
});

describe('eindafrekening', function (): void {
    it('past volumekorting toe bij op tijd inleveren', function (): void {
        $breakdown = $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-09'),
        );

        expect($breakdown->volumeDiscountApplied)->toBeTrue()
            ->and($breakdown->billableWorkdays)->toBe(4)
            ->and($breakdown->lateWorkdays)->toBe(0)
            ->and($breakdown->lateFeeAmount)->toBe('0.00')
            ->and($breakdown->totalAmount)->toBe('400.00');
    });

    it('geeft geen volumekorting bij vroeg inleveren en rekent de geplande periode volledig', function (): void {
        $breakdown = $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-07'),
        );

        expect($breakdown->volumeDiscountApplied)->toBeFalse()
            ->and($breakdown->plannedWorkdays)->toBe(5)
            ->and($breakdown->billableWorkdays)->toBe(5)
            ->and($breakdown->lateWorkdays)->toBe(0)
            ->and($breakdown->totalAmount)->toBe('500.00');
    });

    it('geeft geen volumekorting bij te laat inleveren na vier geplande werkdagen', function (): void {
        $breakdown = $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-08'),
            Carbon::parse('2026-10-12'),
        );

        expect($breakdown->volumeDiscountApplied)->toBeFalse()
            ->and($breakdown->plannedWorkdays)->toBe(4)
            ->and($breakdown->billableWorkdays)->toBe(4)
            ->and($breakdown->lateWorkdays)->toBe(2)
            ->and($breakdown->rentalAmount)->toBe('400.00')
            ->and($breakdown->lateFeeAmount)->toBe('200.00')
            ->and($breakdown->totalAmount)->toBe('600.00');
    });

    it('verwijdert de volumekorting als een langere huur te laat wordt teruggebracht', function (): void {
        $breakdown = $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-12'),
        );

        expect($breakdown->volumeDiscountApplied)->toBeFalse()
            ->and($breakdown->billableWorkdays)->toBe(5)
            ->and($breakdown->lateWorkdays)->toBe(1)
            ->and($breakdown->rentalAmount)->toBe('500.00')
            ->and($breakdown->lateFeeAmount)->toBe('100.00')
            ->and($breakdown->totalAmount)->toBe('600.00');
    });

    it('rekent boete alleen over late werkdagen en niet over weekend', function (): void {
        $breakdown = $this->calculator->settle(
            '50.00',
            Carbon::parse('2026-10-08'),
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-12'),
        );

        expect($breakdown->lateWorkdays)->toBe(1)
            ->and($breakdown->lateFeeAmount)->toBe('50.00')
            ->and($breakdown->totalAmount)->toBe('150.00');
    });
});

describe('ongeldige periodes', function (): void {
    it('gooit een fout als de geplande terugkomst vóór ophalen ligt', function (): void {
        $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-06'),
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-05'),
        );
    })->throws(InvalidArgumentException::class);

    it('gooit een fout als de werkelijke retour vóór ophalen ligt', function (): void {
        $this->calculator->settle(
            '100.00',
            Carbon::parse('2026-10-05'),
            Carbon::parse('2026-10-09'),
            Carbon::parse('2026-10-04'),
        );
    })->throws(InvalidArgumentException::class);
});
