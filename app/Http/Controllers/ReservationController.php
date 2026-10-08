<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\CreateReservation;
use App\Actions\Reservations\DeleteReservation;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use App\Services\Rental\RentalPriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function __construct(private RentalPriceCalculator $calculator) {}

    public function index(): Response
    {
        $today = Carbon::today();

        $reservations = Reservation::query()
            ->with([
                'machine:id,name,daily_price',
                'customer:id,name',
            ])
            ->orderByRaw('CASE WHEN returned_at IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw(
                'CASE
                    WHEN returned_at IS NULL AND return_date < ? THEN 0
                    WHEN returned_at IS NULL AND pickup_date > ? THEN 2
                    WHEN returned_at IS NULL THEN 1
                    ELSE 3
                END',
                [$today->toDateString(), $today->toDateString()],
            )
            ->orderByDesc('pickup_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Reservation $reservation): array => $this->toListItem($reservation, $today));

        return Inertia::render('reserveringen/index', [
            'reservations' => $reservations,
            'today' => $today->toDateString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('reservering', [
            'machines' => Machine::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name', 'daily_price']),
            'customers' => Customer::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(['id', 'name']),
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        CreateReservation $createReservation,
    ): RedirectResponse {
        $createReservation->handle($request->safe()->only([
            'machine_id',
            'customer_id',
            'pickup_date',
            'return_date',
        ]));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Reservering opgeslagen',
        ]);

        return to_route('home');
    }

    public function destroy(
        Reservation $reservation,
        DeleteReservation $deleteReservation,
    ): RedirectResponse {
        $deleteReservation->handle($reservation);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Reservering verwijderd',
        ]);

        return to_route('reserveringen.index');
    }

    /**
     * @return array{
     *     id: int,
     *     machine_id: int,
     *     machine_name: string|null,
     *     customer_id: int,
     *     customer_name: string|null,
     *     pickup_date: string,
     *     return_date: string,
     *     returned_at: string|null,
     *     is_returned: bool,
     *     is_overdue: bool,
     *     is_planned: bool,
     *     is_early_stop: bool,
     *     can_stop: bool,
     *     can_delete: bool,
     *     pricing: array{
     *         rental_amount: string,
     *         late_fee_amount: string,
     *         total_amount: string,
     *         late_workdays: int,
     *         volume_discount_applied: bool,
     *         is_final: bool
     *     }
     * }
     */
    private function toListItem(Reservation $reservation, Carbon $today): array
    {
        $isReturned = $reservation->returned_at !== null;
        $isPlanned = ! $isReturned && $reservation->pickup_date->gt($today);
        $isOverdue = ! $isReturned && $reservation->return_date->lt($today);
        $canStop = ! $isReturned && $reservation->pickup_date->lte($today);
        $isEarlyStop = $canStop && $reservation->return_date->gt($today);

        return [
            'id' => $reservation->id,
            'machine_id' => $reservation->machine_id,
            'machine_name' => $reservation->machine?->name,
            'customer_id' => $reservation->customer_id,
            'customer_name' => $reservation->customer?->name,
            'pickup_date' => $reservation->pickup_date->toDateString(),
            'return_date' => $reservation->return_date->toDateString(),
            'returned_at' => $reservation->returned_at?->toIso8601String(),
            'is_returned' => $isReturned,
            'is_overdue' => $isOverdue,
            'is_planned' => $isPlanned,
            'is_early_stop' => $isEarlyStop,
            'can_stop' => $canStop,
            'can_delete' => $isPlanned,
            'pricing' => $this->pricingFor($reservation, $today, $canStop, $isPlanned),
        ];
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
    private function pricingFor(
        Reservation $reservation,
        Carbon $today,
        bool $canStop,
        bool $isPlanned,
    ): array {
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

        $breakdown = $canStop && ! $isPlanned
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
