<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\Rental\MonthlyIncomeChart;
use App\Services\Rental\RentalPriceCalculator;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class TodayController extends Controller
{
    public function __construct(
        private RentalPriceCalculator $calculator,
        private MonthlyIncomeChart $monthlyIncomeChart,
    ) {}

    public function index(): Response
    {
        $today = Carbon::today();

        $reservations = Reservation::query()
            ->with([
                'machine:id,name,daily_price',
                'customer:id,name',
            ])
            ->where(function ($query) use ($today): void {
                $query->whereDate('pickup_date', $today)
                    ->orWhereDate('return_date', $today)
                    ->orWhere(function ($overdue) use ($today): void {
                        $overdue->whereDate('return_date', '<', $today)
                            ->whereNull('returned_at');
                    });
            })
            ->orderByRaw('CASE WHEN return_date < ? AND returned_at IS NULL THEN 0 ELSE 1 END', [
                $today->toDateString(),
            ])
            ->orderBy('pickup_date')
            ->orderBy('id')
            ->get()
            ->map(function (Reservation $reservation) use ($today): array {
                $pricing = $this->pricingFor($reservation, $today);

                return [
                    'id' => $reservation->id,
                    'machine_name' => $reservation->machine?->name,
                    'customer_id' => $reservation->customer_id,
                    'customer_name' => $reservation->customer?->name,
                    'pickup_date' => $reservation->pickup_date->toDateString(),
                    'return_date' => $reservation->return_date->toDateString(),
                    'returned_at' => $reservation->returned_at?->toIso8601String(),
                    'is_overdue' => $reservation->returned_at === null
                        && $reservation->return_date->lt($today),
                    'is_pickup_today' => $reservation->pickup_date->equalTo($today),
                    'is_return_today' => $reservation->return_date->equalTo($today)
                        && $reservation->returned_at === null,
                    'pricing' => $pricing,
                ];
            });

        return Inertia::render('vandaag', [
            'reservations' => $reservations,
            'today' => $today->toDateString(),
            'monthly_income' => $this->monthlyIncomeChart->forMonth($today),
        ]);
    }

    /**
     * @return array{
     *     rental_amount: string,
     *     late_fee_amount: string,
     *     total_amount: string,
     *     late_workdays: int,
     *     volume_discount_applied: bool,
     *     is_final: bool
     * }
     */
    private function pricingFor(Reservation $reservation, Carbon $today): array
    {
        if (
            $reservation->returned_at !== null
            && $reservation->charged_amount !== null
        ) {
            return [
                'rental_amount' => number_format(
                    (float) $reservation->charged_amount - (float) ($reservation->late_fee_amount ?? 0),
                    2,
                    '.',
                    '',
                ),
                'late_fee_amount' => (string) ($reservation->late_fee_amount ?? '0.00'),
                'total_amount' => (string) $reservation->charged_amount,
                'late_workdays' => 0,
                'volume_discount_applied' => (bool) $reservation->volume_discount_applied,
                'is_final' => true,
            ];
        }

        $dailyPrice = $reservation->machine?->daily_price ?? '0';

        $breakdown = $reservation->return_date->lt($today)
            ? $this->calculator->settle(
                $dailyPrice,
                $reservation->pickup_date,
                $reservation->return_date,
                $today,
            )
            : $this->calculator->estimateForOnTimeReturn(
                $dailyPrice,
                $reservation->pickup_date,
                $reservation->return_date,
            );

        return [
            'rental_amount' => $breakdown->rentalAmount,
            'late_fee_amount' => $breakdown->lateFeeAmount,
            'total_amount' => $breakdown->totalAmount,
            'late_workdays' => $breakdown->lateWorkdays,
            'volume_discount_applied' => $breakdown->volumeDiscountApplied,
            'is_final' => false,
        ];
    }
}
