<?php

namespace App\Services\Rental;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonthlyIncomeChart
{
    /**
     * @return array{
     *     month_label: string,
     *     total: string,
     *     days: list<array{date: string, label: string, amount: string}>
     * }
     */
    public function forMonth(Carbon $today): array
    {
        $start = $today->copy()->startOfMonth()->startOfDay();
        $end = $today->copy()->endOfMonth()->endOfDay();

        /** @var Collection<string, string> $totalsByDay */
        $totalsByDay = Reservation::query()
            ->whereNotNull('returned_at')
            ->whereNotNull('charged_amount')
            ->whereBetween('returned_at', [$start, $end])
            ->get(['returned_at', 'charged_amount'])
            ->groupBy(fn (Reservation $reservation): string => $reservation->returned_at->toDateString())
            ->map(fn (Collection $group): string => number_format(
                $group->sum(fn (Reservation $reservation): float => (float) $reservation->charged_amount),
                2,
                '.',
                '',
            ));

        $days = [];
        $cursor = $start->copy()->startOfDay();
        $monthEnd = $today->copy()->endOfMonth()->startOfDay();
        $total = 0.0;

        while ($cursor->lte($monthEnd)) {
            $date = $cursor->toDateString();
            $amount = $totalsByDay->get($date, '0.00');
            $total += (float) $amount;

            $days[] = [
                'date' => $date,
                'label' => (string) $cursor->day,
                'amount' => $amount,
            ];

            $cursor->addDay();
        }

        return [
            'month_label' => $today->copy()->locale('nl')->isoFormat('MMMM YYYY'),
            'total' => number_format($total, 2, '.', ''),
            'days' => $days,
        ];
    }
}
