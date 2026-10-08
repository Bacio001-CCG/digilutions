<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteReservationRequest;
use App\Models\Machine;
use App\Services\Rental\RentalPriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ReservationQuoteController extends Controller
{
    public function __invoke(
        QuoteReservationRequest $request,
        RentalPriceCalculator $calculator,
    ): JsonResponse {
        $machine = Machine::query()->findOrFail($request->integer('machine_id'));

        $breakdown = $calculator->estimateForOnTimeReturn(
            $machine->daily_price,
            Carbon::parse((string) $request->input('pickup_date'))->startOfDay(),
            Carbon::parse((string) $request->input('return_date'))->startOfDay(),
        );

        return response()->json([
            'amount' => $breakdown->totalAmount,
            'currency' => 'EUR',
            'machine_id' => $machine->id,
            'daily_price' => $machine->daily_price,
            'breakdown' => $breakdown->toArray(),
            'note' => 'Offerte bij op tijd inleveren. Geen volumekorting bij vroeg of te laat; te laat geeft boete per werkdag.',
        ]);
    }
}
